<?php

namespace App\Services\QuestionImport\Pdf;

class PdfSourceConsistencyValidator
{
    /**
     * Validate extracted question candidate against raw source evidence.
     *
     * @param array $candidate
     * @return array Validation result with discrepancy list, field statuses, and updated readiness
     */
    public static function validateCandidate(array $candidate): array
    {
        $discrepancies = [];
        $fieldStatuses = [
            'question_text_status' => 'verified',
            'type_status' => 'verified',
            'options_status' => 'verified',
            'answer_status' => 'verified',
            'explanation_status' => 'verified',
            'reference_status' => 'verified',
            'exhibit_status' => 'verified',
            'source_mapping_status' => 'verified',
        ];

        $rawText = $candidate['debug_info']['raw_text_block'] ?? '';
        $rawUpper = strtoupper($rawText);
        $type = $candidate['question_type'] ?? 'unknown';
        $options = $candidate['options'] ?? [];
        $correctAnswers = $candidate['correct_answers'] ?? [];
        $answerArea = $candidate['answer_area'] ?? [];
        $questionExhibits = $candidate['question_exhibits'] ?? [];
        $answerExhibits = $candidate['answer_exhibits'] ?? [];

        // 1. Question Text Verification
        if (empty(trim($candidate['question_text'] ?? ''))) {
            $discrepancies[] = [
                'field' => 'question_text',
                'code' => 'MISSING_QUESTION_TEXT',
                'severity' => 'critical',
                'message' => 'Question prompt text could not be extracted from source block.',
            ];
            $fieldStatuses['question_text_status'] = 'failed';
        } elseif (strlen(trim($candidate['question_text'])) < 15) {
            $discrepancies[] = [
                'field' => 'question_text',
                'code' => 'SHORT_QUESTION_TEXT',
                'severity' => 'warning',
                'message' => 'Extracted question prompt is suspiciously short.',
            ];
            $fieldStatuses['question_text_status'] = 'review';
        }

        // 2. Question Type Consistency
        if (str_contains($rawUpper, 'HOTSPOT') && $type !== 'hotspot' && $type !== 'yes_no') {
            $discrepancies[] = [
                'field' => 'question_type',
                'code' => 'TYPE_MISMATCH_HOTSPOT',
                'severity' => 'warning',
                'message' => "Source contains explicit 'HOTSPOT' marker but was classified as '{$type}'.",
            ];
            $fieldStatuses['type_status'] = 'review';
        }

        if (str_contains($rawUpper, 'DRAG DROP') && $type !== 'drag_drop') {
            $discrepancies[] = [
                'field' => 'question_type',
                'code' => 'TYPE_MISMATCH_DRAG_DROP',
                'severity' => 'warning',
                'message' => "Source contains explicit 'DRAG DROP' marker but was classified as '{$type}'.",
            ];
            $fieldStatuses['type_status'] = 'review';
        }

        // 3. Option Set Completeness Verification (for Choice Questions)
        if (in_array($type, ['single_choice', 'multiple_choice', 'yes_no'])) {
            $optKeys = array_column($options, 'key');
            
            // Match line-anchored option markers (e.g. "ɣ\nA. Azure..." or "\nB. ")
            preg_match_all('/^[ \t]*(?:[^\w\s\(\[\n]+\s*)?([A-H])[\.\:\)]\s+/mu', $rawText, $rawOptMatches);
            $rawLetters = array_values(array_unique($rawOptMatches[1] ?? []));

            if (!empty($rawLetters) && count($rawLetters) > count($optKeys) && count($rawLetters) >= 2) {
                $missingLetters = array_diff($rawLetters, $optKeys);
                $discrepancies[] = [
                    'field' => 'options',
                    'code' => 'MISSING_SOURCE_OPTIONS',
                    'severity' => 'critical',
                    'message' => 'Source contains options (' . implode(',', $rawLetters) . ') but parser only extracted (' . implode(',', $optKeys) . '). Missing: ' . implode(',', $missingLetters),
                    'source' => implode(', ', $rawLetters),
                    'extracted' => implode(', ', $optKeys),
                    'difference' => 'Missing ' . implode(', ', $missingLetters),
                ];
                $fieldStatuses['options_status'] = 'failed';
            } elseif (count($options) < 2 && $type !== 'yes_no') {
                $discrepancies[] = [
                    'field' => 'options',
                    'code' => 'INSUFFICIENT_OPTIONS',
                    'severity' => 'critical',
                    'message' => 'Less than 2 options were extracted for multiple-choice question.',
                ];
                $fieldStatuses['options_status'] = 'failed';
            }
        } else {
            $fieldStatuses['options_status'] = 'not_applicable';
        }

        // 4. Correct Answer Lossless Verification
        $answerPattern = '/(?:Correct\s*Answers?|Answers?|Key)\s*[:\*]?\s*([A-Ha-h](?:[ \t,and&]*[A-Ha-h])*)\s*(?:\r?\n|$)/is';
        if (preg_match($answerPattern, $rawText, $rawAnsM)) {
            $sourceRawAns = trim($rawAnsM[1]);
            $expectedLetters = PdfQuestionDetector::normalizeAnswerLetters($sourceRawAns);

            // Compare parsed answers against source evidence
            sort($expectedLetters);
            $actualAnswers = $correctAnswers;
            sort($actualAnswers);

            if ($expectedLetters !== $actualAnswers) {
                $missingFromActual = array_diff($expectedLetters, $actualAnswers);
                $extraInActual = array_diff($actualAnswers, $expectedLetters);
                
                $diffMsg = [];
                if (!empty($missingFromActual)) $diffMsg[] = 'Missing: ' . implode(', ', $missingFromActual);
                if (!empty($extraInActual)) $diffMsg[] = 'Extra: ' . implode(', ', $extraInActual);

                $discrepancies[] = [
                    'field' => 'correct_answers',
                    'code' => 'CRITICAL_ANSWER_MISMATCH',
                    'severity' => 'critical',
                    'message' => "Answer discrepancy: Source states '{$sourceRawAns}' (" . implode(',', $expectedLetters) . ") but parsed was (" . implode(',', $actualAnswers) . ").",
                    'source' => $sourceRawAns . ' [' . implode(', ', $expectedLetters) . ']',
                    'extracted' => implode(', ', $actualAnswers),
                    'difference' => implode('; ', $diffMsg),
                ];
                $fieldStatuses['answer_status'] = 'failed';
            }

            // Verify answer letters exist in available options
            if (!empty($options)) {
                $optKeys = array_column($options, 'key');
                foreach ($actualAnswers as $ansKey) {
                    if (!in_array($ansKey, $optKeys, true)) {
                        $discrepancies[] = [
                            'field' => 'correct_answers',
                            'code' => 'INVALID_ANSWER_LETTER',
                            'severity' => 'critical',
                            'message' => "Parsed answer letter '{$ansKey}' does not exist in available options (" . implode(',', $optKeys) . ").",
                        ];
                        $fieldStatuses['answer_status'] = 'failed';
                    }
                }
            }
        } elseif (in_array($type, ['single_choice', 'multiple_choice'])) {
            $discrepancies[] = [
                'field' => 'correct_answers',
                'code' => 'MISSING_EXPLICIT_ANSWER',
                'severity' => 'warning',
                'message' => 'No explicit answer statement detected in source text block.',
            ];
            $fieldStatuses['answer_status'] = 'review';
        }

        // 5. Hotspot & Drag-Drop Structure Verification
        if ($type === 'hotspot') {
            if (empty($answerArea['boxes']) && empty($questionExhibits)) {
                $discrepancies[] = [
                    'field' => 'answer_area',
                    'code' => 'HOTSPOT_AREA_UNPARSED',
                    'severity' => 'warning',
                    'message' => 'Hotspot question does not have structured dropdown boxes or prompt exhibits.',
                ];
                $fieldStatuses['answer_status'] = 'review';
            }
        }

        if ($type === 'drag_drop') {
            if (empty($answerArea['steps']) && empty($questionExhibits)) {
                $discrepancies[] = [
                    'field' => 'answer_area',
                    'code' => 'DRAG_STEPS_UNPARSED',
                    'severity' => 'warning',
                    'message' => 'Drag & Drop question does not have structured sequence steps or prompt exhibits.',
                ];
                $fieldStatuses['answer_status'] = 'review';
            }
        }

        // 6. Answer Leakage Protection
        $hasLeak = false;
        foreach ($questionExhibits as $qEx) {
            foreach ($answerExhibits as $aEx) {
                if (($qEx['url'] ?? '') === ($aEx['url'] ?? '') || ($qEx['obj_id'] ?? 1) === ($aEx['obj_id'] ?? 2)) {
                    $hasLeak = true;
                }
            }
        }
        if ($hasLeak) {
            $discrepancies[] = [
                'field' => 'exhibits',
                'code' => 'POSSIBLE_ANSWER_LEAK_IN_EXHIBIT',
                'severity' => 'critical',
                'message' => 'Learner-facing question exhibit contains answer-level solution screenshot.',
            ];
            $fieldStatuses['exhibit_status'] = 'failed';
        }

        // 7. Source Range Verification
        $start = $candidate['source_metadata']['page_start'] ?? 1;
        $end = $candidate['source_metadata']['page_end'] ?? 1;
        if (($end - $start) >= 4) {
            $discrepancies[] = [
                'field' => 'source_pages',
                'code' => 'SUSPICIOUS_LARGE_PAGE_SPAN',
                'severity' => 'warning',
                'message' => "Question spans {$start}–{$end} pages. Verify boundary delimiters.",
            ];
            $fieldStatuses['source_mapping_status'] = 'review';
        }

        // 8. Overall Readiness Resolution
        $hasCritical = false;
        $hasWarning = false;

        foreach ($discrepancies as $d) {
            if ($d['severity'] === 'critical') $hasCritical = true;
            if ($d['severity'] === 'warning') $hasWarning = true;
        }

        $readiness = 'READY';
        if ($hasCritical) {
            $readiness = 'FAILED';
        } elseif ($hasWarning || in_array('review', $fieldStatuses, true)) {
            $readiness = 'REVIEW_REQUIRED';
        }

        return [
            'readiness_status' => $readiness,
            'discrepancies' => $discrepancies,
            'field_statuses' => $fieldStatuses,
            'is_lossless_verified' => !$hasCritical,
        ];
    }
}
