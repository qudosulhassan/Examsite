<?php

namespace App\Services\QuestionImport\Pdf;

class PdfPageAnalyzer
{
    /**
     * Analyze and structure PDF pages with topic heading detection and page-level diagnostics.
     *
     * @param string $filePath
     * @param array $options
     * @return array ['pages' => [...], 'diagnostics' => [...]]
     */
    public static function analyzePages(string $filePath, array $options = []): array
    {
        $rawPages = PdfTextExtractor::extractPages($filePath);
        $structuredPages = [];
        $pageDiagnostics = [];
        $currentTopic = 'General';

        $runOcr = $options['run_ocr'] ?? true;
        $manualTopic = $options['manual_topic'] ?? null;
        $autoDetectTopics = $options['auto_detect_topics'] ?? true;

        if (!empty($manualTopic)) {
            $currentTopic = $manualTopic;
        }

        foreach ($rawPages as $page) {
            $text = $page['text'];
            $ocrUsed = false;
            $ocrConfidence = 100;
            $extractionMethod = 'native_text';
            $extractionStatus = 'success';
            $warnings = [];

            // Check if OCR fallback is needed or text is suspicious
            if ($runOcr && PdfOcrService::shouldRunOcr($text, $page['images'])) {
                $ocrResult = PdfOcrService::extractOcrText($filePath);
                if (!empty($ocrResult['text'])) {
                    $text = $ocrResult['text'];
                    $ocrUsed = true;
                    $ocrConfidence = $ocrResult['confidence'] ?? 80;
                    $extractionMethod = 'ocr';
                    $extractionStatus = 'ocr_required';
                    if ($ocrConfidence < 70) {
                        $warnings[] = 'Low OCR confidence on this page.';
                    }
                } else {
                    $extractionStatus = (strlen($text) > 0) ? 'partial' : 'failed';
                    $warnings[] = 'Visual content present but OCR could not extract text.';
                }
            }

            // Multi-column warning
            if (($page['layout_detected'] ?? '') === 'multi_column') {
                $warnings[] = 'Multi-column layout detected and unfolded.';
            }

            // Chapter / Topic Heading Detection
            if ($autoDetectTopics && empty($manualTopic)) {
                $lines = explode("\n", $text);
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    if (preg_match('/^(?:CHAPTER|Chapter|DOMAIN|Domain|TOPIC|Topic|MODULE|Module|SECTION|Section)\s*(\d+)[:\.\s\-]+([^\n\r]{3,80})$/i', $trimmed, $matches)) {
                        $currentTopic = trim($matches[2]);
                        break;
                    }
                }
            }

            $pageData = [
                'page_number' => $page['page_number'],
                'text' => $text,
                'current_topic' => $currentTopic,
                'images' => $page['images'],
                'ocr_used' => $ocrUsed,
                'ocr_confidence' => $ocrConfidence,
                'extraction_method' => $extractionMethod,
                'extraction_status' => $extractionStatus,
                'layout_detected' => $page['layout_detected'] ?? 'single_column',
                'native_text_length' => strlen($text),
                'warnings' => $warnings,
            ];

            $structuredPages[] = $pageData;
            $pageDiagnostics[] = [
                'page_number' => $page['page_number'],
                'native_text_length' => strlen($text),
                'image_count' => count($page['images']),
                'ocr_used' => $ocrUsed,
                'ocr_confidence' => $ocrConfidence,
                'extraction_method' => $extractionMethod,
                'extraction_status' => $extractionStatus,
                'layout_detected' => $page['layout_detected'] ?? 'single_column',
                'warnings' => $warnings,
            ];
        }

        return [
            'pages' => $structuredPages,
            'page_diagnostics' => $pageDiagnostics,
        ];
    }
}
