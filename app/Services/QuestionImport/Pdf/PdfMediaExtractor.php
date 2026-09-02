<?php

namespace App\Services\QuestionImport\Pdf;

use Illuminate\Support\Facades\File;

class PdfMediaExtractor
{
    /**
     * Extract all embedded visual assets (JPEG, FlateDecode PNG/Bitmaps) from PDF objects and organize by page.
     *
     * @param array $objects
     * @param array $leafPages
     * @param string $batchUuid
     * @return array Map of [pageNumber => [mediaRecords...]]
     */
    public static function extractAllMedia(array $objects, array $leafPages, string $batchUuid): array
    {
        $mediaByPage = [];
        $storageDir = function_exists('public_path') && app()->bound('path.public')
            ? public_path("storage/question-imports/{$batchUuid}")
            : (defined('LARAVEL_PUBLIC_PATH') ? LARAVEL_PUBLIC_PATH . "/storage/question-imports/{$batchUuid}" : base_path("public/storage/question-imports/{$batchUuid}"));

        if (!File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true, true);
        }

        foreach ($leafPages as $idx => $pId) {
            $pNum = $idx + 1;
            $pageObj = $objects[$pId] ?? null;
            if (!$pageObj) continue;

            $body = $pageObj['body'] ?? '';
            $resDict = $body;
            if (preg_match('/\/Resources\s+(\d+)\s+\d+\s+R/', $body, $resRef)) {
                $resObjId = (int)$resRef[1];
                if (isset($objects[$resObjId])) {
                    $resDict = $objects[$resObjId]['body'];
                }
            }

            $pageMedia = [];

            if (preg_match('/\/XObject\s*<<(.*?)>>/s', $resDict, $xm)) {
                if (preg_match_all('/\/([A-Za-z0-9_\-]+)\s+(\d+)\s+\d+\s+R/', $xm[1], $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $mIdx => $m) {
                        $imgName = $m[1];
                        $imgObjId = (int)$m[2];
                        $imgObj = $objects[$imgObjId] ?? null;
                        if (!$imgObj || !preg_match('/\/Subtype\s*\/Image(?![a-zA-Z])/i', $imgObj['body'])) {
                            continue;
                        }

                        $width = 0; $height = 0;
                        if (preg_match('/\/Width\s+(\d+)/', $imgObj['body'], $wm)) $width = (int)$wm[1];
                        if (preg_match('/\/Height\s+(\d+)/', $imgObj['body'], $hm)) $height = (int)$hm[1];
                        
                        $stream = $imgObj['stream'] ?? '';
                        if (empty($stream)) continue;

                        $filter = '';
                        if (preg_match('/\/Filter\s*\/([A-Za-z0-9]+)/', $imgObj['body'], $fm)) $filter = $fm[1];

                        $filename = "page_{$pNum}_img_{$mIdx}_{$width}x{$height}";
                        $url = "/storage/question-imports/{$batchUuid}/{$filename}";
                        $saved = false;

                        if ($filter === 'DCTDecode') {
                            $filePath = "{$storageDir}/{$filename}.jpg";
                            File::put($filePath, $stream);
                            $url .= '.jpg';
                            $saved = true;
                        } elseif ($filter === 'FlateDecode' && function_exists('imagecreate')) {
                            $decomp = PdfTextExtractor::decompressStream($stream);
                            $colorSpace = 'DeviceRGB';
                            if (preg_match('/\/ColorSpace\s*\/([A-Za-z0-9]+)/', $imgObj['body'], $csm)) {
                                $colorSpace = $csm[1];
                            }

                            if ($width > 0 && $height > 0 && strlen($decomp) >= ($width * $height)) {
                                $im = imagecreatetruecolor($width, $height);
                                $isRgb = ($colorSpace === 'DeviceRGB' || strlen($decomp) >= ($width * $height * 3));
                                
                                $pos = 0;
                                for ($y = 0; $y < $height; $y++) {
                                    for ($x = 0; $x < $width; $x++) {
                                        if ($pos >= strlen($decomp)) break 2;
                                        if ($isRgb) {
                                            $r = ord($decomp[$pos]);
                                            $g = ord($decomp[$pos + 1]);
                                            $b = ord($decomp[$pos + 2]);
                                            $pos += 3;
                                        } else {
                                            $r = $g = $b = ord($decomp[$pos]);
                                            $pos += 1;
                                        }
                                        $col = imagecolorallocate($im, $r, $g, $b);
                                        imagesetpixel($im, $x, $y, $col);
                                    }
                                }
                                $filePath = "{$storageDir}/{$filename}.png";
                                imagepng($im, $filePath);
                                imagedestroy($im);
                                $url .= '.png';
                                $saved = true;
                            }
                        }

                        if ($saved) {
                            $pageMedia[] = [
                                'type' => 'image',
                                'asset_id' => "img_p{$pNum}_{$mIdx}",
                                'obj_id' => $imgObjId,
                                'url' => $url,
                                'width' => $width,
                                'height' => $height,
                                'caption' => "Visual Exhibit (Page {$pNum})",
                                'source_page' => $pNum,
                                'sort_order' => $mIdx + 1,
                            ];
                        }
                    }
                }
            }

            if (!empty($pageMedia)) {
                $mediaByPage[$pNum] = $pageMedia;
            }
        }

        return $mediaByPage;
    }

    /**
     * Helper to extract exhibits for a single page (backward-compatible).
     */
    public static function extractExhibits(string $pdfContent, int $pageNumber, string $batchUuid): array
    {
        $objects = PdfTextExtractor::parseAllPdfObjects($pdfContent);
        $leafPages = PdfTextExtractor::resolvePageObjectReferences($objects);
        $all = self::extractAllMedia($objects, $leafPages, $batchUuid);
        return $all[$pageNumber] ?? [];
    }
}
