<?php

namespace App\Services\QuestionImport\Pdf;

use App\Services\QuestionImport\Utf8Sanitizer;

class PdfTextExtractor
{
    /**
     * Extract structured pages, text streams, font mappings, and layout metadata from any PDF file.
     *
     * @param string $filePath
     * @return array Array of pages with page_number, text, images, layout_detected, extraction_method, quality_tier, and confidence
     */
    public static function extractPages(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("PDF file not found at: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false || strlen($content) === 0) {
            throw new \RuntimeException("Unable to read PDF file contents.");
        }

        if (!str_contains(substr($content, 0, 1024), '%PDF-')) {
            throw new \RuntimeException("File does not contain a valid PDF header.");
        }

        // 1. Parse all indirect objects with full bodies
        $objects = self::parseAllPdfObjects($content);

        // 2. Build Font Dictionary with ToUnicode CMaps
        $fontObjs = self::extractFontObjectsWithCMaps($objects);

        // 3. Resolve all leaf /Type /Page objects in document order
        $leafPageIds = self::resolvePageObjectReferences($objects);

        $pages = [];

        if (!empty($leafPageIds)) {
            foreach ($leafPageIds as $pageIndex => $objId) {
                $pageNumber = $pageIndex + 1;
                $pageObj = $objects[$objId] ?? null;

                $pageText = '';
                $images = [];
                $layout = 'single_column';

                if ($pageObj) {
                    $rawText = self::decodePageObjectStream($pageObj, $objects, $fontObjs);
                    $images = self::detectImagesInPageObject($pageObj);
                    $unfolded = self::unfoldMultiColumnText($rawText, $layout);
                    $pageText = self::normalizeExtractedText($unfolded);
                }

                $qualityAssessment = self::assessPageTextQuality($pageText);

                $pages[] = [
                    'page_number' => $pageNumber,
                    'text' => trim($pageText),
                    'images' => $images,
                    'layout_detected' => $layout,
                    'ocr_used' => false,
                    'ocr_confidence' => 100,
                    'extraction_method' => 'native_text',
                    'native_text_length' => strlen(trim($pageText)),
                    'quality_tier' => $qualityAssessment['tier'],
                    'quality_score' => $qualityAssessment['score'],
                    'quality_signals' => $qualityAssessment['signals'],
                    'current_topic' => '',
                ];
            }
        }

        // Fallback: If page tree resolution yielded no text, try stream fallback
        $totalTextLength = array_sum(array_column($pages, 'native_text_length'));
        if (empty($pages) || $totalTextLength < 30) {
            $fallbackPages = self::fallbackStreamExtraction($objects, $fontObjs);
            if (!empty($fallbackPages)) {
                $pages = $fallbackPages;
            }
        }

        if (empty($pages)) {
            $pages[] = [
                'page_number' => 1,
                'text' => '',
                'images' => [],
                'layout_detected' => 'single_column',
                'ocr_used' => false,
                'ocr_confidence' => 100,
                'extraction_method' => 'native_text',
                'native_text_length' => 0,
                'quality_tier' => 'EMPTY',
                'quality_score' => 0,
                'quality_signals' => ['empty_document'],
                'current_topic' => '',
            ];
        }

        return $pages;
    }

    /**
     * Parse all indirect objects in the PDF into an indexed associative array.
     */
    public static function parseAllPdfObjects(string $pdfContent): array
    {
        $objects = [];

        // Match objects: <num> <gen> obj ... endobj
        $pattern = '/(?:^|\n)(\d+)\s+(\d+)\s+obj\s*(.*?)\s*endobj/s';
        if (preg_match_all($pattern, $pdfContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $objNum = (int) $match[1];
                $genNum = (int) $match[2];
                $body = $match[3];

                $stream = '';
                if (preg_match('/stream[\r\n]+(.*?)[\r\n]+endstream/s', $body, $streamMatch)) {
                    $stream = $streamMatch[1];
                }

                $objects[$objNum] = [
                    'obj' => $objNum,
                    'gen' => $genNum,
                    'body' => $body,
                    'stream' => $stream,
                ];
            }
        }

        return $objects;
    }

    /**
     * Extract and parse /ToUnicode CMaps for all Font objects.
     */
    public static function extractFontObjectsWithCMaps(array $objects): array
    {
        $fontObjs = [];

        foreach ($objects as $id => $obj) {
            $body = $obj['body'];
            if (preg_match('/\/Type\s*\/Font(?![a-zA-Z])/i', $body)) {
                $toUnicodeId = null;
                if (preg_match('/\/ToUnicode\s+(\d+)\s+\d+\s+R/', $body, $tuMatch)) {
                    $toUnicodeId = (int) $tuMatch[1];
                }

                $cMap = [];
                if ($toUnicodeId && isset($objects[$toUnicodeId])) {
                    $decomp = self::decompressStream($objects[$toUnicodeId]['stream']);
                    $cMap = self::parseCMapStream($decomp);
                }

                $fontObjs[$id] = [
                    'toUnicodeId' => $toUnicodeId,
                    'cMap' => $cMap,
                ];
            }
        }

        return $fontObjs;
    }

    /**
     * Parse Adobe Type 2 UCS CMap stream (beginbfchar, beginbfrange standard, beginbfrange array).
     */
    public static function parseCMapStream(string $stream): array
    {
        $map = [];
        if (empty($stream)) {
            return $map;
        }

        // 1. beginbfchar: <0003> <0020>
        if (preg_match_all('/<([0-9a-fA-F]+)>\s*<([0-9a-fA-F]+)>/', $stream, $charMatches, PREG_SET_ORDER)) {
            foreach ($charMatches as $m) {
                $code = hexdec($m[1]);
                $utf8 = self::hexToUtf8($m[2]);
                if ($utf8 !== '') {
                    $map[$code] = $utf8;
                }
            }
        }

        // 2. beginbfrange standard: <0048> <004C> <0065>
        if (preg_match_all('/<([0-9a-fA-F]+)>\s*<([0-9a-fA-F]+)>\s*<([0-9a-fA-F]+)>/', $stream, $rangeMatches, PREG_SET_ORDER)) {
            foreach ($rangeMatches as $m) {
                $startCode = hexdec($m[1]);
                $endCode = hexdec($m[2]);
                $startUnicode = hexdec($m[3]);
                $count = $endCode - $startCode;
                for ($i = 0; $i <= $count; $i++) {
                    $destHex = sprintf('%04X', $startUnicode + $i);
                    $utf8 = self::hexToUtf8($destHex);
                    if ($utf8 !== '') {
                        $map[$startCode + $i] = $utf8;
                    }
                }
            }
        }

        // 3. beginbfrange with destination array: <0001> <0003> [ <0041> <0042> <0043> ]
        if (preg_match_all('/<([0-9a-fA-F]+)>\s*<([0-9a-fA-F]+)>\s*\[(.*?)\]/s', $stream, $arrRangeMatches, PREG_SET_ORDER)) {
            foreach ($arrRangeMatches as $m) {
                $startCode = hexdec($m[1]);
                $rawArr = $m[3];
                if (preg_match_all('/<([0-9a-fA-F]+)>/', $rawArr, $destMatches)) {
                    foreach ($destMatches[1] as $idx => $hex) {
                        $code = $startCode + $idx;
                        $utf8 = self::hexToUtf8($hex);
                        if ($utf8 !== '') {
                            $map[$code] = $utf8;
                        }
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Convert Hex string (UTF-16BE or code point) to valid UTF-8 character.
     */
    public static function hexToUtf8(string $hex): string
    {
        $len = strlen($hex);
        if ($len % 2 !== 0) {
            $hex = '0' . $hex;
        }

        if (strlen($hex) === 4) {
            $codePoint = hexdec($hex);
            return mb_chr($codePoint, 'UTF-8') ?: '';
        }

        $bin = @hex2bin($hex);
        if ($bin === false) {
            return '';
        }

        $converted = @mb_convert_encoding($bin, 'UTF-8', 'UTF-16BE');
        return $converted ?: $bin;
    }

    /**
     * Resolve all /Type /Page indirect object IDs in sequential document tree order.
     */
    public static function resolvePageObjectReferences(array $objects): array
    {
        // Find Catalog -> Pages Root
        $catalogObj = null;
        foreach ($objects as $obj) {
            if (preg_match('/\/Type\s*\/Catalog(?![a-zA-Z])/i', $obj['body'])) {
                $catalogObj = $obj;
                break;
            }
        }

        if ($catalogObj && preg_match('/\/Pages\s+(\d+)\s+\d+\s+R/', $catalogObj['body'], $pm)) {
            $pagesRootId = (int) $pm[1];
            $leafs = self::traversePagesTree($pagesRootId, $objects);
            if (!empty($leafs)) {
                return $leafs;
            }
        }

        // Fallback: locate all /Type /Page objects directly
        $pageIds = [];
        foreach ($objects as $id => $obj) {
            if (preg_match('/\/Type\s*\/Page(?![a-zA-Z])/i', $obj['body'])) {
                $pageIds[] = $id;
            }
        }

        return array_values(array_unique($pageIds));
    }

    /**
     * Recursively traverse /Type /Pages tree to extract leaf /Type /Page IDs in order.
     */
    private static function traversePagesTree(int $nodeId, array $objects): array
    {
        $node = $objects[$nodeId] ?? null;
        if (!$node) {
            return [];
        }

        $body = $node['body'];
        if (preg_match('/\/Type\s*\/Page(?![a-zA-Z])/i', $body)) {
            return [$nodeId];
        }

        if (preg_match('/\/Type\s*\/Pages(?![a-zA-Z])/i', $body)) {
            $leafs = [];
            if (preg_match('/\/Kids\s*\[(.*?)\]/s', $body, $km)) {
                if (preg_match_all('/(\d+)\s+\d+\s+R/', $km[1], $rm)) {
                    foreach ($rm[1] as $childId) {
                        $leafs = array_merge($leafs, self::traversePagesTree((int) $childId, $objects));
                    }
                }
            }
            return $leafs;
        }

        return [];
    }

    /**
     * Decode a single page object's /Contents streams using font-mapped CMaps.
     */
    public static function decodePageObjectStream(array $pageObj, array $objects, array $fontObjs): string
    {
        $body = $pageObj['body'];

        // 1. Extract Font resource map: /Font <</F5 5 0 R /F6 6 0 R ... >>
        $pageFontMap = [];
        $resDict = $body;
        if (preg_match('/\/Resources\s+(\d+)\s+\d+\s+R/', $body, $resRef)) {
            $resObjId = (int) $resRef[1];
            if (isset($objects[$resObjId])) {
                $resDict = $objects[$resObjId]['body'];
            }
        }

        if (preg_match('/\/Font\s*<<(.*?)>>/s', $resDict, $fm)) {
            if (preg_match_all('/\/([A-Za-z0-9_\-]+)\s+(\d+)\s+\d+\s+R/', $fm[1], $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $pageFontMap[$m[1]] = (int) $m[2];
                }
            }
        }

        // 2. Extract /Contents references
        $contentObjIds = [];
        if (preg_match('/\/Contents\s*\[(.*?)\]/s', $body, $arrMatch)) {
            if (preg_match_all('/(\d+)\s+\d+\s+R/', $arrMatch[1], $refMatches)) {
                $contentObjIds = array_map('intval', $refMatches[1]);
            }
        } elseif (preg_match('/\/Contents\s+(\d+)\s+\d+\s+R/', $body, $singleMatch)) {
            $contentObjIds = [(int) $singleMatch[1]];
        }

        $rawStreamText = '';
        foreach ($contentObjIds as $cid) {
            if (isset($objects[$cid]) && !empty($objects[$cid]['stream'])) {
                $rawStreamText .= self::decompressStream($objects[$cid]['stream']) . "\n";
            }
        }

        if (empty($rawStreamText) && !empty($pageObj['stream'])) {
            $rawStreamText = self::decompressStream($pageObj['stream']);
        }

        // 3. Decode stream operators with Font / CMap tracking
        $text = '';
        $currentCMap = [];
        $activeActualText = null;

        $pattern = '/(?:\/Span\s*<<\s*\/ActualText\s*<([0-9a-fA-F]+)>\s*>>\s*BDC|EMC|\/([A-Za-z0-9_\-]+)\s+(\d+(?:\.\d+)?)\s+Tf|\[(.*?)\]\s*TJ|\((.*?)\)\s*(?:Tj|\'|\")|<([0-9a-fA-F]+)>\s*(?:Tj|\'|\")|T\*|ET|(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s+Td)/s';

        if (preg_match_all($pattern, $rawStreamText, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $full = $m[0];

                // Marked Content /ActualText
                if (!empty($m[1])) {
                    $hex = $m[1];
                    if (str_starts_with(strtoupper($hex), 'FEFF')) {
                        $hex = substr($hex, 4);
                    }
                    $activeActualText = self::hexToUtf8($hex);
                    continue;
                }

                if (str_starts_with($full, 'EMC')) {
                    $activeActualText = null;
                    continue;
                }

                // Font switch: /F6 16 Tf
                if (!empty($m[2]) && isset($m[3])) {
                    $alias = $m[2];
                    $fid = $pageFontMap[$alias] ?? null;
                    if ($fid && isset($fontObjs[$fid])) {
                        $currentCMap = $fontObjs[$fid]['cMap'];
                    }
                    continue;
                }

                // TJ Array: [(str) 10 (str)] TJ
                if (isset($m[4]) && $m[4] !== '') {
                    if ($activeActualText !== null) {
                        $text .= $activeActualText . ' ';
                    } else {
                        $tj = $m[4];
                        if (preg_match_all('/(?:\((.*?)\)|<([0-9a-fA-F]+)>|(-?\d+(?:\.\d+)?))/s', $tj, $parts, PREG_SET_ORDER)) {
                            foreach ($parts as $p) {
                                if (isset($p[1]) && $p[1] !== '') {
                                    $text .= self::decodeStrWithCMap($p[1], $currentCMap);
                                } elseif (isset($p[2]) && $p[2] !== '') {
                                    $text .= self::decodeHexWithCMap($p[2], $currentCMap);
                                } elseif (isset($p[3]) && is_numeric($p[3])) {
                                    if ((float) $p[3] < -120) {
                                        $text .= ' ';
                                    }
                                }
                            }
                        }
                    }
                    continue;
                }

                // Single string (str) Tj
                if (isset($m[5]) && $m[5] !== '') {
                    if ($activeActualText !== null) {
                        $text .= $activeActualText;
                    } else {
                        $text .= self::decodeStrWithCMap($m[5], $currentCMap);
                    }
                    continue;
                }

                // Hex string <hex> Tj
                if (isset($m[6]) && $m[6] !== '') {
                    if ($activeActualText !== null) {
                        $text .= $activeActualText;
                    } else {
                        $text .= self::decodeHexWithCMap($m[6], $currentCMap);
                    }
                    continue;
                }

                // Td translation: vertical move (abs(dy) > 4) -> newline; horizontal move -> space
                if (isset($m[7]) && isset($m[8]) && is_numeric($m[7]) && is_numeric($m[8])) {
                    $dx = (float) $m[7];
                    $dy = (float) $m[8];
                    if (abs($dy) > 4) {
                        $text .= "\n";
                    } elseif ($dx > 4 && abs($dy) <= 4) {
                        $text .= ' ';
                    }
                    continue;
                }

                if (str_contains($full, 'T*') || str_contains($full, 'ET')) {
                    $text .= "\n";
                }
            }
        }

        return $text;
    }

    /**
     * Decode string bytes using active font CMap (handles 2-byte Type0 CIDs and 1-byte fallbacks).
     */
    public static function decodeStrWithCMap(string $str, array $cMap): string
    {
        $unescaped = self::unescapePdfString($str);
        if (empty($cMap)) {
            return $unescaped;
        }

        $out = '';
        $len = strlen($unescaped);

        if ($len >= 2 && $len % 2 === 0 && ord($unescaped[0]) === 0) {
            for ($i = 0; $i < $len; $i += 2) {
                $code = (ord($unescaped[$i]) << 8) | ord($unescaped[$i + 1]);
                $out .= $cMap[$code] ?? chr($code & 0xFF);
            }
            return $out;
        }

        for ($i = 0; $i < $len; $i++) {
            $code = ord($unescaped[$i]);
            $out .= $cMap[$code] ?? $unescaped[$i];
        }

        return $out;
    }

    /**
     * Decode hex chunks using active font CMap (handles 4-hex digit 2-byte CIDs).
     */
    public static function decodeHexWithCMap(string $hex, array $cMap): string
    {
        $out = '';
        $len = strlen($hex);

        for ($i = 0; $i < $len; $i += 4) {
            $chunk = substr($hex, $i, 4);
            $code = hexdec($chunk);
            if (isset($cMap[$code])) {
                $out .= $cMap[$code];
            } else {
                $b1 = hexdec(substr($chunk, 0, 2));
                $b2 = hexdec(substr($chunk, 2, 2));
                $out .= ($cMap[$b1] ?? chr($b1)) . ($cMap[$b2] ?? chr($b2));
            }
        }

        return $out;
    }

    /**
     * Robust stream decompressor handling FlateDecode, zlib, and deflate variations.
     */
    public static function decompressStream(string $stream): string
    {
        if (empty($stream)) {
            return '';
        }

        // Strategy 1: Standard zlib uncompress
        $data = @gzuncompress($stream);
        if ($data !== false) {
            return $data;
        }

        // Strategy 2: Raw deflate inflate
        $data = @gzinflate($stream);
        if ($data !== false) {
            return $data;
        }

        // Strategy 3: Strip 2-byte zlib header and inflate
        if (strlen($stream) > 2) {
            $data = @gzinflate(substr($stream, 2));
            if ($data !== false) {
                return $data;
            }
        }

        // Strategy 4: zlib_decode
        if (function_exists('zlib_decode')) {
            $data = @zlib_decode($stream);
            if ($data !== false) {
                return $data;
            }
        }

        return $stream;
    }

    /**
     * Detect images referenced in page object.
     */
    public static function detectImagesInPageObject(array $pageObj): array
    {
        $images = [];
        $body = $pageObj['body'];
        if (preg_match_all('/\/([A-Za-z0-9_\-]+)\s+Do(?![a-zA-Z])/s', $body, $matches)) {
            $images = array_unique($matches[1]);
        }
        return $images;
    }

    /**
     * Assess quality tier of extracted page text (GOOD, DEGRADED, CORRUPTED, EMPTY).
     */
    public static function assessPageTextQuality(string $text): array
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return ['tier' => 'EMPTY', 'score' => 0, 'signals' => ['empty_page']];
        }

        $length = strlen($trimmed);
        if ($length < 30) {
            return ['tier' => 'DEGRADED', 'score' => 40, 'signals' => ['sparse_text']];
        }

        $signals = [];
        $score = 100;

        // Check ratio of alphabetic and numeric chars vs strange non-ASCII control symbols
        $alphaCount = preg_match_all('/[a-zA-Z0-9\s.,?!:;\'"()\-\[\]]/', $trimmed);
        $ratio = $alphaCount / max(1, $length);

        if ($ratio < 0.70) {
            $score -= 40;
            $signals[] = 'excessive_unusual_characters';
        }

        // Check for broken single-character word fragments (e.g. "a b c d e f")
        $words = preg_split('/\s+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY);
        $singleCharWords = 0;
        foreach ($words as $w) {
            if (strlen($w) === 1 && !in_array(strtoupper($w), ['A', 'I'])) {
                $singleCharWords++;
            }
        }

        $singleCharRatio = $singleCharWords / max(1, count($words));
        if ($singleCharRatio > 0.35) {
            $score -= 30;
            $signals[] = 'fragmented_characters';
        }

        $tier = match (true) {
            $score >= 80 => 'GOOD',
            $score >= 50 => 'DEGRADED',
            default => 'CORRUPTED',
        };

        return [
            'tier' => $tier,
            'score' => max(0, min(100, $score)),
            'signals' => $signals,
        ];
    }

    /**
     * Detect and unfold multi-column text lines into sequential column text.
     */
    public static function unfoldMultiColumnText(string $rawText, string &$layout): string
    {
        $lines = explode("\n", $rawText);
        $multiColumnLineCount = 0;
        $totalNonEmptyLines = 0;
        $leftColumn = [];
        $rightColumn = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;
            $totalNonEmptyLines++;

            if (preg_match('/^([^\s].{5,50}?)[ \t]{4,}([^\s].{5,50}?)$/', $trimmed, $m)) {
                $multiColumnLineCount++;
                $leftColumn[] = trim($m[1]);
                $rightColumn[] = trim($m[2]);
            } else {
                $leftColumn[] = $trimmed;
            }
        }

        if ($totalNonEmptyLines > 4 && ($multiColumnLineCount / $totalNonEmptyLines) >= 0.35) {
            $layout = 'multi_column';
            return implode("\n", $leftColumn) . "\n" . implode("\n", $rightColumn);
        }

        return $rawText;
    }

    /**
     * Unescape standard PDF string literals.
     */
    public static function unescapePdfString(string $str): string
    {
        $str = str_replace(
            ['\\n', '\\r', '\\t', '\\(', '\\)', '\\\\'],
            ["\n", "\r", "\t", '(', ')', '\\'],
            $str
        );

        $str = preg_replace_callback('/\\\\([0-7]{1,3})/', function ($m) {
            return chr(octdec($m[1]));
        }, $str);

        return Utf8Sanitizer::cleanString($str);
    }

    /**
     * Clean and normalize multi-line extracted text (drop-caps, spacing, font quirks).
     */
    public static function normalizeExtractedText(string $text): string
    {
        $text = Utf8Sanitizer::cleanString($text);

        // Normalize drop-cap first letters: "Q uestion" -> "Question", "T opic" -> "Topic", "Y ou" -> "You"
        $text = preg_replace('/\b([A-Z])\s+([a-z]{2,})\b/', '$1$2', $text);

        // Clean font kerning spaces and font artifacts
        $text = preg_replace('/(?<=[a-z0-9\)])B(?=[a-z0-9])/s', ' ', $text);
        $text = preg_replace('/(?<=[a-z0-9\)])B(?=[A-Z])/s', ' ', $text);
        $text = preg_replace('/(?<=[a-z0-9])B\b/s', '', $text);
        $text = preg_replace('/[\x{00A0}\x{200B}\x{FEFF}\x{202F}\x{3000}]/u', ' ', $text);

        // Standardize common question and answer delimiters
        $text = preg_replace('/Correct\s*,?\s*nswer\s*\*?/i', 'Correct Answer:', $text);
        $text = preg_replace('/Topic\s*7\s*(\d+)/i', 'Topic $1', $text);
        $text = preg_replace('/(?:Question|estion)\s*B?\s*#?\s*(\d+)/i', 'Question #$1', $text);

        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = explode("\n", $text);
        $cleanLines = [];
        foreach ($lines as $line) {
            $line = trim(preg_replace('/[ \t]+/', ' ', $line));
            if ($line !== '') {
                $cleanLines[] = $line;
            }
        }
        return implode("\n", $cleanLines);
    }

    /**
     * Fallback stream extraction for unstructured PDFs.
     */
    private static function fallbackStreamExtraction(array $objects, array $fontObjs): array
    {
        $allText = '';
        foreach ($objects as $obj) {
            if (!empty($obj['stream'])) {
                $decompressed = self::decompressStream($obj['stream']);
                if (str_contains($decompressed, 'BT') || str_contains($decompressed, 'Tj') || str_contains($decompressed, 'TJ')) {
                    $pageObj = ['body' => $obj['body'], 'stream' => $obj['stream']];
                    $allText .= "\n" . self::decodePageObjectStream($pageObj, $objects, $fontObjs);
                }
            }
        }

        $allText = self::normalizeExtractedText($allText);
        if (empty($allText)) return [];

        return [
            [
                'page_number' => 1,
                'text' => $allText,
                'images' => [],
                'layout_detected' => 'single_column',
                'ocr_used' => false,
                'ocr_confidence' => 100,
                'extraction_method' => 'native_text',
                'native_text_length' => strlen($allText),
                'quality_tier' => 'GOOD',
                'quality_score' => 85,
                'quality_signals' => [],
                'current_topic' => '',
            ]
        ];
    }
}
