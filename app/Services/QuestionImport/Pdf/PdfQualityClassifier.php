<?php

namespace App\Services\QuestionImport\Pdf;

class PdfQualityClassifier
{
    /**
     * Classify PDF document structure and layout quality based on page analysis.
     *
     * @param array $structuredPages
     * @return array ['classification' => '...', 'quality_score' => 88, 'quality_tier' => 'GOOD', 'details' => [...]]
     */
    public static function classifyDocument(array $structuredPages): array
    {
        $totalPages = count($structuredPages);
        if ($totalPages === 0) {
            return [
                'classification' => 'UNKNOWN',
                'quality_score' => 0,
                'quality_tier' => 'POOR',
                'details' => ['reason' => 'Empty document'],
            ];
        }

        $nativeTextPages = 0;
        $ocrPages = 0;
        $multiColumnPages = 0;
        $failedPages = 0;
        $totalTextLength = 0;

        foreach ($structuredPages as $page) {
            $textLen = strlen(trim($page['text'] ?? ''));
            $totalTextLength += $textLen;

            if (!empty($page['ocr_used'])) {
                $ocrPages++;
            } elseif ($textLen > 60) {
                $nativeTextPages++;
            } else {
                $failedPages++;
            }

            if (($page['layout_detected'] ?? '') === 'multi_column') {
                $multiColumnPages++;
            }
        }

        // Determine Classification
        $nativeRatio = $nativeTextPages / $totalPages;
        $ocrRatio = $ocrPages / $totalPages;
        $multiColumnRatio = $multiColumnPages / $totalPages;

        if ($multiColumnRatio > 0.3) {
            $classification = 'COMPLEX_LAYOUT';
        } elseif ($nativeRatio >= 0.75) {
            $classification = 'TEXT_BASED';
        } elseif ($ocrRatio >= 0.75) {
            $classification = 'SCANNED';
        } elseif ($nativeRatio > 0.2 && $ocrRatio > 0.2) {
            $classification = 'HYBRID';
        } else {
            $classification = 'UNKNOWN';
        }

        return [
            'classification' => $classification,
            'native_text_pages' => $nativeTextPages,
            'ocr_pages' => $ocrPages,
            'failed_pages' => $failedPages,
            'multi_column_pages' => $multiColumnPages,
            'total_text_length' => $totalTextLength,
        ];
    }

    /**
     * Calculate aggregate batch extraction quality score (0 - 100).
     *
     * @param int $pageCount
     * @param int $questionsDetected
     * @param float $averageConfidence
     * @param int $warningCount
     * @param int $errorCount
     * @param int $ocrPages
     * @return array ['score' => 88, 'tier' => 'GOOD']
     */
    public static function calculateQualityScore(
        int $pageCount,
        int $questionsDetected,
        float $averageConfidence,
        int $warningCount,
        int $errorCount,
        int $ocrPages = 0
    ): array {
        if ($questionsDetected === 0) {
            return ['score' => 0, 'tier' => 'POOR'];
        }

        // Base score starts at average confidence
        $score = $averageConfidence;

        // Deduction for errors (each error deducts 3 points, capped at -25)
        $errorDeduction = min(25, $errorCount * 3);
        $score -= $errorDeduction;

        // Small deduction for excessive warnings (each warning above question count * 0.5 deducts 1 point)
        if ($warningCount > ($questionsDetected * 0.5)) {
            $warningDeduction = min(15, ($warningCount - ($questionsDetected * 0.5)) * 0.5);
            $score -= $warningDeduction;
        }

        // Bonus for high question yield per page
        $yieldRatio = $questionsDetected / max(1, $pageCount);
        if ($yieldRatio >= 1.0) {
            $score += 5;
        }

        $score = (int) max(5, min(100, round($score)));

        $tier = 'POOR';
        if ($score >= 90) {
            $tier = 'EXCELLENT';
        } elseif ($score >= 75) {
            $tier = 'GOOD';
        } elseif ($score >= 60) {
            $tier = 'REVIEW RECOMMENDED';
        }

        return [
            'score' => $score,
            'tier' => $tier,
        ];
    }
}
