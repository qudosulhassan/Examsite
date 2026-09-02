<?php

namespace App\Services\QuestionImport\Pdf;

use App\Services\QuestionImport\Utf8Sanitizer;

class PdfQuestionDetector
{
    /**
     * Parse structured pages into Universal Question V2 candidate objects.
     *
     * @param array $structuredPages
     * @param string $filename
     * @param string $batchUuid
     * @param array $options
     * @return array
     */
    public static function detectQuestions(array $structuredPages, string $filename, string $batchUuid = 'default', array $options = []): array
    {
        $candidates = [];
        $combinedText = '';
        $pageMap = [];

        // Build continuous text stream with byte offset tracking
        foreach ($structuredPages as $page) {
            $pNum = $page['page_number'];
            $startPos = strlen($combinedText);
            $combinedText .= "\n" . $page['text'] . "\n";
            $endPos = strlen($combinedText);

            $pageMap[] = [
                'page_number' => $pNum,
                'start_pos' => $startPos,
                'end_pos' => $endPos,
                'topic' => $page['current_topic'] ?? 'Topic 1',
                'images' => $page['images'] ?? [],
                'ocr_used' => $page['ocr_used'] ?? false,
                'ocr_confidence' => $page['ocr_confidence'] ?? 100,
                'extraction_method' => $page['extraction_method'] ?? 'native_text',
                'layout_detected' => $page['layout_detected'] ?? 'single_column',
                'quality_tier' => $page['quality_tier'] ?? 'GOOD',
            ];
        }

        // Delimiter matching for certification question formats
        $delimiterPattern = '(?:(?:Question|QUESTION)\s*(?:#|NO\.?:?|\d+:?)\s*\d*|Topic\s*\d+[\s\-–,]+Question\s*#?\s*\d+|Case\s+Study\s*\d+[\s\-–,]+Question\s*#?\s*\d+|Q\d+[:\.\s]|Item\s+\d+[:\.\s])';
        $questionRegex = '/(?:^|\n)(' . $delimiterPattern . '[\s:\.\-–]?)(.*?)(?=(?:\n' . $delimiterPattern . '[\s:\.\-–]?)|\Z)/is';

        if (preg_match_all($questionRegex, $combinedText, $matches, PREG_OFFSET_CAPTURE)) {
            $totalMatches = count($matches[0]);

            foreach ($matches[0] as $idx => $match) {
                $rawChunk = $match[0];
                $offset = $match[1];
                $length = strlen($rawChunk);
                $endOffset = $offset + $length;

                $pageStart = 1;
                $pageEnd = 1;
                $pageTopic = 'Topic 1';
                $ocrUsed = false;
                $ocrConfidence = 100;
                $extractionMethod = 'native_text';
                $layoutDetected = 'single_column';
                $candidateImages = [];

                foreach ($pageMap as $pInfo) {
                    if ($offset >= $pInfo['start_pos'] && $offset <= $pInfo['end_pos']) {
                        $pageStart = $pInfo['page_number'];
                        $pageTopic = $pInfo['topic'];
                        $ocrUsed = $pInfo['ocr_used'];
                        $ocrConfidence = $pInfo['ocr_confidence'];
                        $extractionMethod = $pInfo['extraction_method'];
                        $layoutDetected = $pInfo['layout_detected'];
                    }
                    if ($endOffset >= $pInfo['start_pos']) {
                        $pageEnd = $pInfo['page_number'];
                    }
                }

                // Collect media on candidate pages
                for ($p = $pageStart; $p <= $pageEnd; $p++) {
                    $pData = $pageMap[$p - 1] ?? null;
                    if ($pData && !empty($pData['images'])) {
                        foreach ($pData['images'] as $img) {
                            $candidateImages[] = $img;
                        }
                    }
                }

                $topic = $pageTopic;
                $localQNum = $idx + 1;

                if (preg_match('/T\s*opic\s*(?:7\s*)?(\d+)/i', $rawChunk, $tM)) {
                    $topic = 'Topic ' . $tM[1];
                }
                if (preg_match('/Question\s*#?\s*(\d+)/i', $rawChunk, $qM)) {
                    $localQNum = (int)$qM[1];
                }

                $cleanChunk = trim($rawChunk);
                $hasNextBoundary = ($idx < ($totalMatches - 1));

                $candidate = self::parseQuestionBlock(
                    $cleanChunk,
                    $topic,
                    $localQNum,
                    $filename,
                    $pageStart,
                    $pageEnd,
                    $candidateImages,
                    $ocrUsed,
                    $ocrConfidence,
                    $extractionMethod,
                    $layoutDetected,
                    $offset,
                    $endOffset,
                    $hasNextBoundary
                );

                if (!empty($candidate['question_text'])) {
                    $candidates[] = $candidate;
                }
            }
        }

        // Fallback: If no structured numbering was found
        if (empty($candidates) && !empty($structuredPages)) {
            foreach ($structuredPages as $idx => $p) {
                $cleanText = trim($p['text']);
                if (strlen($cleanText) > 20) {
                    $candidate = self::parseQuestionBlock(
                        $cleanText,
                        $p['current_topic'] ?? 'Topic 1',
                        $idx + 1,
                        $filename,
                        $p['page_number'],
                        $p['page_number'],
                        $p['images'] ?? [],
                        $p['ocr_used'] ?? false,
                        $p['ocr_confidence'] ?? 100,
                        $p['extraction_method'] ?? 'native_text',
                        $p['layout_detected'] ?? 'single_column',
                        0,
                        strlen($cleanText),
                        false
                    );
                    if (!empty($candidate['question_text'])) {
                        $candidates[] = $candidate;
                    }
                }
            }
        }

        return $candidates;
    }

    /**
     * Parse a single question block with question-type intelligence, exhibit separation, structured answer areas, and source verification.
     */
    public static function parseQuestionBlock(
        string $rawText,
        string $topic,
        int $localQuestionNumber,
        string $filename,
        int $pageStart,
        int $pageEnd,
        array $candidateImages = [],
        bool $ocrUsed = false,
        int $ocrConfidence = 100,
        string $extractionMethod = 'native_text',
        string $layoutDetected = 'single_column',
        int $charStart = 0,
        int $charEnd = 0,
        bool $hasNextBoundary = true
    ): array {
        $markerPattern = '/^(?:(?:Question|QUESTION)\s*(?:#|NO\.?:?|\d+:?)\s*\d*|Topic\s*\d+[\s\-–,]+Question\s*#?\s*\d+|Case\s+Study\s*\d+[\s\-–,]+Question\s*#?\s*\d+|Q\d+[:\.\s]|Item\s+\d+[:\.\s])[\s:\.\-–]*/i';
        
        $hasQuestionMarker = (bool) preg_match($markerPattern, $rawText);
        $textWithoutNum = preg_replace($markerPattern, '', $rawText);
        $textWithoutNum = ltrim($textWithoutNum, ":.- \t\n\r");
        $textWithoutNum = trim($textWithoutNum);

        // 1. Extract References with line-wrap reconstruction
        $references = [];
        if (preg_match('/(?:^|\n)[ \t]*(?:References?|Documentation)\s*[:\-–]\s*(https?:\/\/[^\s]+.*)$/is', $textWithoutNum, $refMatches)) {
            $refText = trim($refMatches[1]);
            $textWithoutNum = trim(substr($textWithoutNum, 0, -strlen($refMatches[0])));
            $references = self::cleanReferenceUrls($refText);
        }

        // 2. Extract Section/Domain Headings
        $sectionHeading = null;
        if (preg_match('/(?:Implement\s+and\s+Manage\s+[^\n\r]+|Implement\s+and\s+Monitor\s+[^\n\r]+|Manage\s+Identity\s+and\s+Governance[^\n\r]*|Design\s+and\s+Implement\s+[^\n\r]+)$/im', $textWithoutNum, $shM)) {
            $sectionHeading = trim($shM[0]);
            $textWithoutNum = trim(substr($textWithoutNum, 0, -strlen($shM[0])));
        }

        // 3. Extract Structured Answer Area (Dropdown Boxes, Sequence Steps)
        $answerArea = self::extractAnswerArea($rawText);

        // 4. Correct Answer & Explanation (Lossless Letter Parsing)
        $correctAnswers = [];
        $explanation = '';
        $rawAnswerStatement = null;
        $incorrectExplanations = [];

        $answerPattern = '/(?:Correct\s*Answers?|Answers?|Key)\s*[:\*]?\s*([A-Ha-h](?:[ \t,and&]*[A-Ha-h])*)\s*(?:\r?\n|$)(.*)$/is';

        if (preg_match($answerPattern, $textWithoutNum, $ansMatches)) {
            $rawAnswerStatement = trim($ansMatches[0]);
            $rawAns = trim($ansMatches[1]);
            $afterAnswer = trim($ansMatches[2] ?? '');

            $textWithoutNum = trim(substr($textWithoutNum, 0, -strlen($ansMatches[0])));
            $correctAnswers = self::normalizeAnswerLetters($rawAns);
            $explanation = $afterAnswer;
        } elseif (preg_match('/(?:Correct\s*Answers?|Answers?|Key)[\s:\*]+(.*)$/is', $textWithoutNum, $ansMatches)) {
            $rawAnswerStatement = trim($ansMatches[0]);
            $explanation = trim($ansMatches[1] ?? '');
            $textWithoutNum = trim(substr($textWithoutNum, 0, -strlen($ansMatches[0])));
        }

        // Extract specific incorrect answer breakdowns from explanation if present
        if (!empty($explanation) && preg_match_all('/([A-H](?:,\s*[A-H])*)\s*[:\-–]\s*([^\n]+)/i', $explanation, $incMatches, PREG_SET_ORDER)) {
            foreach ($incMatches as $im) {
                $incorrectExplanations[] = [
                    'keys' => array_map('trim', explode(',', strtoupper($im[1]))),
                    'rationale' => trim($im[2]),
                ];
            }
        }

        // 5. Clean bullet prefixes and Topic headers from prompt
        $textWithoutNum = preg_replace('/^[ \t]*[^\w\s\(\[\n]+\s*$/m', '', $textWithoutNum);
        $textWithoutNum = preg_replace('/(?:^[ \t]*[^\w\s\(\[\n]+\s*|\n[ \t]*[^\w\s\(\[\n]+\s*)([A-H][\.\:\)])/u', "\n$1", $textWithoutNum);
        $textWithoutNum = preg_replace('/^T\s*opic\s*(?:7\s*)?\d+\s*/i', '', $textWithoutNum);

        // 6. Extract Options & Question Prompt
        $optionParseResult = PdfOptionParser::parseOptions($textWithoutNum);
        $questionPrompt = trim($optionParseResult['question_text']);
        $options = $optionParseResult['options'];

        // 7. Multi-Signal Question Type Detection
        $typeInfo = self::detectQuestionType($rawText, $options, $correctAnswers, $answerArea);
        $questionType = $typeInfo['type'];
        $typeConfidence = $typeInfo['confidence'];
        $typeDetectionReason = $typeInfo['reason'];

        // 8. Exhibit Separation (Question Exhibits vs Answer Exhibits)
        $questionExhibits = [];
        $answerExhibits = [];

        foreach ($candidateImages as $img) {
            $imgPage = $img['source_page'] ?? $pageStart;
            if ($imgPage === $pageStart || $pageStart === $pageEnd) {
                $questionExhibits[] = $img;
            } else {
                $answerExhibits[] = $img;
            }
        }

        if (count($candidateImages) > 1 && empty($answerExhibits) && str_contains(strtoupper($rawText), 'CORRECT ANSWER')) {
            $answerExhibits[] = array_pop($questionExhibits);
        }

        // 9. Code & ARM Template Detection
        $contentBlocks = [];
        if (preg_match('/\{[\s\r\n]*"\$schema":/i', $questionPrompt) || preg_match('/"(?:parameters|resources|variables)":\s*\{/i', $questionPrompt)) {
            $contentBlocks[] = [
                'type' => 'code',
                'language' => 'json',
                'content' => $questionPrompt,
            ];
        } else {
            $contentBlocks[] = [
                'type' => 'text',
                'content' => $questionPrompt,
            ];
        }

        // 10. Giant Block Check
        $isGiantBlock = ($pageEnd - $pageStart) >= 4;

        // 11. Confidence Scores
        $boundaryConfidence = 40;
        if ($hasQuestionMarker) $boundaryConfidence += 30;
        if (strlen($questionPrompt) >= 15) $boundaryConfidence += 15;
        if ($hasNextBoundary) $boundaryConfidence += 15;
        if ($isGiantBlock) $boundaryConfidence = max(20, $boundaryConfidence - 40);

        $optionConfidence = match ($questionType) {
            'single_choice', 'multiple_choice' => (count($options) >= 2 ? 95 : 30),
            'hotspot' => (!empty($answerArea['boxes']) || !empty($questionExhibits) ? 90 : 50),
            'drag_drop' => (!empty($answerArea['steps']) || !empty($questionExhibits) ? 90 : 50),
            default => 75,
        };

        $answerConfidence = match ($questionType) {
            'single_choice', 'multiple_choice' => (!empty($correctAnswers) ? 95 : 30),
            'hotspot' => (!empty($answerArea['boxes']) ? 95 : 60),
            'drag_drop' => (!empty($answerArea['steps']) ? 95 : 60),
            default => 60,
        };

        $textConfidence = strlen($questionPrompt) >= 20 ? 95 : 40;
        $exhibitConfidence = !empty($questionExhibits) ? 95 : 80;

        $overallConfidenceScore = (int) round(
            ($boundaryConfidence * 0.25) +
            ($textConfidence * 0.20) +
            ($typeConfidence * 100 * 0.20) +
            ($optionConfidence * 0.20) +
            ($answerConfidence * 0.15)
        );

        $confidenceLevel = match (true) {
            $overallConfidenceScore >= 85 => 'HIGH',
            $overallConfidenceScore >= 65 => 'MEDIUM',
            default => 'LOW',
        };

        // 12. Build Candidate Record
        $candidateData = [
            'exam_id' => null,
            'topic' => $topic ?: 'Topic 1',
            'local_question_number' => $localQuestionNumber,
            'section_heading' => $sectionHeading,
            'question_type' => $questionType,
            'status' => 'draft',
            'readiness_status' => 'READY',
            'question_text' => $questionPrompt,
            'instructions' => $questionType === 'multiple_choice' ? 'Select all that apply.' : '',
            'options' => $options,
            'correct_answers' => $correctAnswers,
            'answer_area' => $answerArea,
            'content_blocks' => $contentBlocks,
            'explanation' => $explanation,
            'incorrect_explanations' => $incorrectExplanations,
            'references' => $references,
            'question_exhibits' => $questionExhibits,
            'answer_exhibits' => $answerExhibits,
            'exhibits' => $questionExhibits,
            'source_type' => 'pdf_import',
            'validation_warnings' => [],
            'warning_codes' => [],
            'source_metadata' => [
                'filename' => $filename,
                'page_start' => $pageStart,
                'page_end' => $pageEnd,
                'confidence_score' => $overallConfidenceScore,
                'confidence_level' => $confidenceLevel,
                'question_boundary_confidence' => $boundaryConfidence,
                'type_confidence' => round($typeConfidence * 100, 1),
                'type_detection_reason' => $typeDetectionReason,
                'option_confidence' => $optionConfidence,
                'answer_confidence' => $answerConfidence,
                'text_confidence' => $textConfidence,
                'exhibit_confidence' => $exhibitConfidence,
                'extraction_method' => $extractionMethod,
                'ocr_used' => $ocrUsed,
                'ocr_confidence' => $ocrConfidence,
                'layout_detected' => $layoutDetected,
                'is_giant_block' => $isGiantBlock,
                'has_exhibit' => !empty($questionExhibits) || !empty($answerExhibits),
                'exhibit_count' => count($questionExhibits) + count($answerExhibits),
            ],
            'debug_info' => [
                'raw_text_block' => $rawText,
                'char_start' => $charStart,
                'char_end' => $charEnd,
                'has_question_marker' => $hasQuestionMarker,
                'raw_answer_statement' => $rawAnswerStatement,
                'answer_pattern_matched' => $rawAnswerStatement,
                'boundary_confidence' => $boundaryConfidence,
                'type_reason' => $typeDetectionReason,
            ],
        ];

        // 13. Source Consistency & Discrepancy Validation
        $consistencyResult = PdfSourceConsistencyValidator::validateCandidate($candidateData);
        $candidateData['readiness_status'] = $consistencyResult['readiness_status'];
        $candidateData['field_statuses'] = $consistencyResult['field_statuses'];
        $candidateData['discrepancies'] = $consistencyResult['discrepancies'];

        $warnings = [];
        $warningCodes = [];
        foreach ($consistencyResult['discrepancies'] as $disc) {
            $warnings[] = ($disc['severity'] === 'critical' ? '❌ ' : '⚠ ') . $disc['message'];
            $warningCodes[] = $disc['code'];
        }

        $candidateData['validation_warnings'] = $warnings;
        $candidateData['warning_codes'] = $warningCodes;

        return Utf8Sanitizer::clean($candidateData);
    }

    /**
     * Detect semantic question type based on multi-signal indicators.
     */
    public static function detectQuestionType(string $rawText, array $options, array $correctAnswers, array $answerArea = []): array
    {
        $rawUpper = strtoupper($rawText);

        // 1. Explicit Case Study / Testlet
        if (preg_match('/(?:^|\n)\s*(?:Case\s+study|Testlet)\s*[\-–:]/i', $rawText) || str_contains($rawUpper, 'CASE STUDY -') || str_contains($rawUpper, 'THIS IS A CASE STUDY')) {
            return [
                'type' => 'case_study',
                'confidence' => 0.99,
                'reason' => 'Explicit Case Study / Testlet header detected in document',
            ];
        }

        // 2. Yes / No Statement Matrix
        if (preg_match('/select\s+Yes\s+if\s+the\s+statement\s+is\s+true/i', $rawText) ||
            preg_match('/Yes\s+or\s+No/i', $rawText) ||
            (count($options) === 2 && strtolower($options[0]['text'] ?? '') === 'yes' && strtolower($options[1]['text'] ?? '') === 'no')) {
            return [
                'type' => 'yes_no',
                'confidence' => 0.95,
                'reason' => 'Yes/No statement evaluation instructions detected',
            ];
        }

        // 3. Explicit Hotspot / Dropdown Selection
        if (preg_match('/(?:^|\n)\s*HOTSPOT\s*[\-–:]/i', $rawText) ||
            str_contains($rawUpper, 'HOT AREA:') ||
            str_contains($rawUpper, 'HOT AREA') ||
            !empty($answerArea['boxes']) ||
            preg_match('/Box\s*1\s*:/i', $rawText) ||
            (preg_match('/select\s+the\s+appropriate\s+(?:options|settings)\s+in\s+the\s+answer\s+area/i', $rawText) && empty($options))) {
            return [
                'type' => 'hotspot',
                'confidence' => 0.98,
                'reason' => 'Explicit HOTSPOT marker / interactive dropdown areas detected',
            ];
        }

        // 4. Explicit Drag & Drop / Sequence
        if (preg_match('/(?:^|\n)\s*DRAG\s*(?:&|\+)?\s*DROP\s*[\-–:]/i', $rawText) ||
            str_contains($rawUpper, 'SELECT AND PLACE:') ||
            (str_contains($rawUpper, 'ACTIONS') && str_contains($rawUpper, 'ANSWER AREA') && !str_contains($rawUpper, 'HOTSPOT')) ||
            !empty($answerArea['steps']) ||
            preg_match('/Step\s*1\s*:/i', $rawText) ||
            preg_match('/and\s+arrange\s+them\s+in\s+the\s+correct\s+order/i', $rawText)) {
            return [
                'type' => 'drag_drop',
                'confidence' => 0.98,
                'reason' => 'Explicit DRAG & DROP marker / step sequence detected',
            ];
        }

        // 5. Multiple Choice / Multiple Response
        $hasMultiKeyword = (bool) preg_match('/(?:Which\s+(?:two|three|four)|Select\s+(?:two|three|four)|Select\s+all\s+that\s+apply|Each\s+correct\s+answer\s+presents\s+a\s+complete\s+solution|Each\s+correct\s+answer\s+presents\s+part\s+of\s+the\s+solution)/i', $rawText);
        
        if (count($correctAnswers) > 1 || $hasMultiKeyword) {
            return [
                'type' => 'multiple_choice',
                'confidence' => 0.95,
                'reason' => count($correctAnswers) > 1 
                    ? 'Multiple correct answers detected (' . implode(', ', $correctAnswers) . ')' 
                    : 'Multiple response directive in question prompt',
            ];
        }

        // 6. Simulation
        if (preg_match('/(?:^|\n)\s*SIMULATION\s*[\-–:]/i', $rawText)) {
            return [
                'type' => 'simulation',
                'confidence' => 0.95,
                'reason' => 'Explicit SIMULATION marker detected',
            ];
        }

        // 7. Single Choice
        if (count($options) >= 2) {
            return [
                'type' => 'single_choice',
                'confidence' => 0.95,
                'reason' => 'Standard multiple choice question with ' . count($options) . ' options',
            ];
        }

        return [
            'type' => 'unknown',
            'confidence' => 0.40,
            'reason' => 'Unstructured question format requires manual review',
        ];
    }

    /**
     * Extract structured answer areas (Box 1 / Box 2 dropdowns, Step 1 / Step 2 drag sequences).
     */
    public static function extractAnswerArea(string $rawText): array
    {
        $answerArea = [
            'type' => 'none',
            'boxes' => [],
            'steps' => [],
        ];

        // Hotspot dropdown boxes: "Box 1: Zone-redundant storage (ZRS)"
        if (preg_match_all('/(?:Box|Area|Dropdown)\s*(\d+)\s*[:\-–]\s*([^\n\r]+)/i', $rawText, $bm, PREG_SET_ORDER)) {
            $answerArea['type'] = 'dropdown_boxes';
            foreach ($bm as $m) {
                $answerArea['boxes'][] = [
                    'box_number' => (int) $m[1],
                    'label' => "Box {$m[1]}",
                    'correct' => trim($m[2]),
                ];
            }
        }

        // Drag & Drop sequence steps: "Step 1: Remove peering between Vnet1 and VNet2."
        if (preg_match_all('/(?:Step|Action)\s*(\d+)\s*[:\-–]\s*([^\n\r]+)/i', $rawText, $sm, PREG_SET_ORDER)) {
            $answerArea['type'] = 'sequence_steps';
            foreach ($sm as $m) {
                $answerArea['steps'][] = [
                    'step_number' => (int) $m[1],
                    'label' => "Step {$m[1]}",
                    'text' => trim($m[2]),
                ];
            }
        }

        return $answerArea;
    }

    /**
     * Normalize multi-letter answers (e.g. "ABE" -> ['A', 'B', 'E'], "A, C" -> ['A', 'C']).
     */
    public static function normalizeAnswerLetters(string $rawAns): array
    {
        $rawAns = trim($rawAns);
        if (empty($rawAns)) return [];

        $rawAns = str_ireplace(['and', '&'], ',', $rawAns);
        if (str_contains($rawAns, ',')) {
            $letters = array_map('trim', explode(',', $rawAns));
        } elseif (preg_match('/^[A-Ha-h\s]+$/', $rawAns)) {
            $letters = str_split(preg_replace('/[^A-Za-z]/', '', $rawAns));
        } else {
            $letters = [$rawAns];
        }

        return array_values(array_unique(array_filter(array_map('strtoupper', $letters))));
    }

    /**
     * Reconstruct and validate URLs split across PDF line breaks.
     */
    public static function cleanReferenceUrls(string $text): array
    {
        $references = [];
        $repairedText = preg_replace('/(https?:\/\/[^\s]+?)\n([a-zA-Z0-9_\-\.\/]+)/', '$1$2', $text);
        
        if (preg_match_all('/https?:\/\/[^\s\)\"\']+/i', $repairedText, $urlMatches)) {
            foreach (array_unique($urlMatches[0]) as $url) {
                $url = rtrim($url, '.,:;');
                $url = preg_replace('#(?<!:)//+#', '/', $url);
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $references[] = [
                        'title' => 'Reference Documentation',
                        'url' => $url,
                    ];
                }
            }
        }

        return $references;
    }
}
