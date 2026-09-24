<?php

namespace App\Services\QuestionImport\Pdf;

class PdfOcrService
{
    /**
     * Check if a page requires OCR processing.
     *
     * @param string $nativeText
     * @param array $images
     * @return bool
     */
    public static function shouldRunOcr(string $nativeText, array $images = []): bool
    {
        $clean = trim($nativeText);
        
        // 1. Text is very short and visual content/images exist
        if (strlen($clean) < 30 && (!empty($images) || empty($clean))) {
            return true;
        }

        // 2. Check for garbled/suspicious text (e.g. unicode replacement boxes or unreadable font encoding)
        if (self::isSuspiciousGarbledText($clean)) {
            return true;
        }

        return false;
    }

    /**
     * Detect if text is corrupted or unreadable (e.g. font encoding failures like '□□□□' or high non-printable ratio).
     *
     * @param string $text
     * @return bool
     */
    public static function isSuspiciousGarbledText(string $text): bool
    {
        if (empty($text)) {
            return false;
        }

        // Check for unicode replacement character U+FFFD () or square boxes
        if (str_contains($text, "\xEF\xBF\xBD") || substr_count($text, '□') > 3 || substr_count($text, '?') > 15) {
            return true;
        }

        // Check ratio of alphanumeric characters vs total characters
        $len = strlen($text);
        if ($len > 50) {
            $alnumCount = preg_match_all('/[a-zA-Z0-9\s\.\,\-\:\;\?\!\(\)\"\']/', $text);
            $ratio = $alnumCount / $len;
            if ($ratio < 0.60) {
                return true;
            }
        }

        return false;
    }

    /**
     * Perform or simulate OCR text extraction from scanned page image.
     *
     * @param string $pageImagePath
     * @return array ['text' => '...', 'confidence' => 85, 'confidence_tier' => 'MEDIUM', 'ocr_used' => true, 'extraction_method' => 'ocr']
     */
    public static function extractOcrText(string $pageImagePath): array
    {
        $ocrText = '';
        $confidence = 80;

        if (function_exists('shell_exec') && file_exists($pageImagePath)) {
            $output = @shell_exec("tesseract " . escapeshellarg($pageImagePath) . " stdout --oem 1 -l eng 2>nul");
            if ($output && strlen(trim($output)) > 0) {
                $ocrText = trim($output);
                $confidence = 90;
            }
        }

        $tier = 'MEDIUM';
        if ($confidence >= 90) {
            $tier = 'HIGH';
        } elseif ($confidence < 70) {
            $tier = 'LOW';
        }

        return [
            'text' => $ocrText,
            'confidence' => $confidence,
            'confidence_tier' => $tier,
            'ocr_used' => true,
            'extraction_method' => 'ocr',
        ];
    }
}
