<?php

namespace App\Services\QuestionImport;

use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionImportBatch;
use App\Models\QuestionImportItem;
use App\Services\QuestionImport\Pdf\PdfMediaExtractor;
use App\Services\QuestionImport\Pdf\PdfPageAnalyzer;
use App\Services\QuestionImport\Pdf\PdfQualityClassifier;
use App\Services\QuestionImport\Pdf\PdfQuestionDetector;

class PdfImportService
{
    /**
     * Process an uploaded PDF document and generate reviewable import batch, items, and production diagnostics.
     *
     * @param string $filePath
     * @param string $originalFilename
     * @param int|null $defaultExamId
     * @param array $options
     * @return QuestionImportBatch
     */
    public static function processPdf(
        string $filePath,
        string $originalFilename,
        ?int $defaultExamId = null,
        array $options = []
    ): QuestionImportBatch {
        // 1. Generate Batch UUID (e.g. IMP-2026-001)
        $year = date('Y');
        $batchCount = QuestionImportBatch::whereYear('created_at', $year)->count() + 1;
        $uuid = sprintf('IMP-%s-%03d', $year, $batchCount);

        // 2. Analyze and extract structured pages with page-level diagnostics and media
        $analysisResult = PdfPageAnalyzer::analyzePages($filePath, $options);
        $structuredPages = $analysisResult['pages'];
        $pageDiagnostics = $analysisResult['page_diagnostics'];

        // Extract media assets
        $rawPdf = file_get_contents($filePath);
        $pdfObjects = \App\Services\QuestionImport\Pdf\PdfTextExtractor::parseAllPdfObjects($rawPdf);
        $leafPages = \App\Services\QuestionImport\Pdf\PdfTextExtractor::resolvePageObjectReferences($pdfObjects);
        $mediaByPage = PdfMediaExtractor::extractAllMedia($pdfObjects, $leafPages, $uuid);

        foreach ($structuredPages as &$p) {
            $pNum = $p['page_number'];
            $p['images'] = $mediaByPage[$pNum] ?? ($p['images'] ?? []);
        }
        unset($p);

        // 3. Classify Document Layout & Quality
        $classificationInfo = PdfQualityClassifier::classifyDocument($structuredPages);

        // 4. Detect Question Candidates with Multi-Signal Type Intelligence and Source Verification
        $candidates = PdfQuestionDetector::detectQuestions($structuredPages, $originalFilename, $uuid, $options);

        // Calculate confidence and readiness counters
        $totalQuestions = count($candidates);
        $confidenceSum = 0;
        $highConfCount = 0;
        $mediumConfCount = 0;
        $lowConfCount = 0;

        foreach ($candidates as $c) {
            $conf = $c['source_metadata']['confidence_score'] ?? 80;
            $confidenceSum += $conf;
            if ($conf >= 85) $highConfCount++;
            elseif ($conf >= 65) $mediumConfCount++;
            else $lowConfCount++;
        }

        $avgConfidence = $totalQuestions > 0 ? round($confidenceSum / $totalQuestions, 1) : 0;

        $batch = QuestionImportBatch::create([
            'uuid' => $uuid,
            'filename' => Utf8Sanitizer::cleanString($originalFilename),
            'source_type' => 'pdf_import',
            'format_detected' => 'PDF Document Extraction (' . $classificationInfo['classification'] . ')',
            'default_exam_id' => $defaultExamId,
            'status' => 'ready_for_review',
            'options' => Utf8Sanitizer::clean(array_merge($options, [
                'page_count' => count($structuredPages),
                'page_diagnostics' => $pageDiagnostics,
                'classification' => $classificationInfo['classification'],
            ])),
            'total_questions' => $totalQuestions,
            'created_by' => auth()->id(),
        ]);

        $validCount = 0;
        $warningCount = 0;
        $errorCount = 0;
        $duplicateCount = 0;
        $readyCount = 0;
        $reviewRequiredCount = 0;
        $failedCount = 0;

        // Check target exam code vs document filename
        $defaultExamModel = $defaultExamId ? Exam::find($defaultExamId) : null;
        $docExamMismatchWarning = null;
        if ($defaultExamModel && preg_match('/([A-Za-z]{2,3}[\-\s]?\d{3})/i', $originalFilename, $codeM)) {
            $docCode = strtoupper(str_replace([' ', '-'], '', $codeM[1]));
            $examCode = strtoupper(str_replace([' ', '-'], '', $defaultExamModel->exam_code ?? ''));
            if ($examCode && $docCode !== $examCode) {
                $docExamMismatchWarning = "Document appears to be for {$codeM[1]}, but selected target exam is {$defaultExamModel->exam_code}.";
            }
        }

        foreach ($candidates as $index => $candidate) {
            $errors = [];
            $warnings = $candidate['validation_warnings'] ?? [];
            $warningCodes = $candidate['warning_codes'] ?? [];

            if ($docExamMismatchWarning) {
                $warnings[] = $docExamMismatchWarning;
                $warningCodes[] = 'EXAM_CODE_MISMATCH';
            }

            // Resolve target exam
            $resolvedExamId = null;
            if (!empty($candidate['exam_id'])) {
                $resolvedExamId = (int)$candidate['exam_id'];
            } elseif (!empty($candidate['exam_code'])) {
                $examModel = Exam::where('exam_code', trim($candidate['exam_code']))->first();
                if ($examModel) {
                    $resolvedExamId = $examModel->id;
                }
            } else {
                $resolvedExamId = $defaultExamId;
            }

            $candidate['exam_id'] = $resolvedExamId;
            $universal = Question::convertToUniversalModel($candidate);
            $universal['source_type'] = 'pdf_import';
            $universal['topic'] = $candidate['topic'] ?? 'Topic 1';
            $universal['local_question_number'] = $candidate['local_question_number'] ?? ($index + 1);
            $universal['section_heading'] = $candidate['section_heading'] ?? null;
            $universal['question_type'] = $candidate['question_type'] ?? 'single_choice';
            $universal['answer_area'] = $candidate['answer_area'] ?? [];
            $universal['content_blocks'] = $candidate['content_blocks'] ?? [];
            $universal['question_exhibits'] = $candidate['question_exhibits'] ?? [];
            $universal['answer_exhibits'] = $candidate['answer_exhibits'] ?? [];
            $universal['exhibits'] = $candidate['question_exhibits'] ?? [];
            $universal['incorrect_explanations'] = $candidate['incorrect_explanations'] ?? [];
            $universal['field_statuses'] = $candidate['field_statuses'] ?? [];
            $universal['discrepancies'] = $candidate['discrepancies'] ?? [];

            $universal['source_reference'] = [
                'filename' => $originalFilename,
                'import_batch_id' => $uuid,
                'page_start' => $candidate['source_metadata']['page_start'] ?? 1,
                'page_end' => $candidate['source_metadata']['page_end'] ?? 1,
                'confidence_score' => $candidate['source_metadata']['confidence_score'] ?? 80,
                'confidence_level' => $candidate['source_metadata']['confidence_level'] ?? 'MEDIUM',
                'question_boundary_confidence' => $candidate['source_metadata']['question_boundary_confidence'] ?? 80,
                'type_confidence' => $candidate['source_metadata']['type_confidence'] ?? 95,
                'type_detection_reason' => $candidate['source_metadata']['type_detection_reason'] ?? '',
                'extraction_method' => $candidate['source_metadata']['extraction_method'] ?? 'native_text',
                'ocr_used' => $candidate['source_metadata']['ocr_used'] ?? false,
                'ocr_confidence' => $candidate['source_metadata']['ocr_confidence'] ?? 100,
                'layout_detected' => $candidate['source_metadata']['layout_detected'] ?? 'single_column',
                'has_exhibit' => !empty($universal['question_exhibits']) || !empty($universal['answer_exhibits']),
                'exhibit_count' => count($universal['question_exhibits']) + count($universal['answer_exhibits']),
            ];
            $universal['debug_info'] = $candidate['debug_info'] ?? [];

            // Validation Checks
            if (empty($universal['exam_id'])) {
                $warnings[] = 'Certification Exam is not set (will use default or require selection during review).';
                $warningCodes[] = 'MISSING_EXAM_ASSIGNMENT';
            }

            if (empty($universal['question_text'])) {
                $errors[] = 'Question text could not be extracted.';
                $warningCodes[] = 'MISSING_QUESTION_TEXT';
            }

            // Duplicate Detection
            $dupResult = ['status' => 'none', 'question_id' => null, 'similarity' => 0];
            if (!empty($universal['question_text'])) {
                $dupResult = \App\Http\Controllers\Admin\QuestionImportController::checkDuplicate(
                    $universal['question_text'],
                    $universal['exam_id']
                );
                if ($dupResult['status'] !== 'none') {
                    $warnings[] = "Possible Duplicate: {$dupResult['similarity']}% similarity with Question #{$dupResult['question_id']}.";
                    $warningCodes[] = 'POSSIBLE_DUPLICATE';
                }
            }

            if (empty($universal['correct_answers']) && in_array($universal['question_type'], ['single_choice', 'multiple_choice'])) {
                $warnings[] = 'Correct answer could not be automatically detected in PDF.';
                $warningCodes[] = 'MISSING_CORRECT_ANSWER';
            }
            $start = $universal['source_reference']['page_start'] ?? 1;
            $end = $universal['source_reference']['page_end'] ?? 1;
            if ($start !== $end) {
                $warnings[] = "Multi-page question spanning pages {$start}–{$end}.";
            }

            // Deduplicate warnings & codes
            $warnings = array_values(array_unique($warnings));
            $warningCodes = array_values(array_unique($warningCodes));

            // Determine Readiness Status
            $candidateReadiness = $candidate['readiness_status'] ?? 'READY';
            $readinessStatus = 'READY';
            $itemStatus = 'valid';
            $reviewStatus = 'approved';

            if (count($errors) > 0 || $candidateReadiness === 'FAILED') {
                $readinessStatus = 'FAILED';
                $itemStatus = 'error';
                $reviewStatus = 'needs_fix';
                $errorCount++;
                $failedCount++;
            } elseif ($dupResult['status'] !== 'none') {
                $readinessStatus = 'REVIEW_REQUIRED';
                $itemStatus = 'duplicate';
                $reviewStatus = 'pending';
                $duplicateCount++;
                $warningCount++;
                $reviewRequiredCount++;
            } elseif ($candidateReadiness === 'REVIEW_REQUIRED' || ($universal['source_reference']['confidence_score'] ?? 80) < 65) {
                $readinessStatus = 'REVIEW_REQUIRED';
                $itemStatus = 'warning';
                $reviewStatus = 'pending';
                $warningCount++;
                $validCount++;
                $reviewRequiredCount++;
            } else {
                $readinessStatus = 'READY';
                $itemStatus = 'valid';
                $reviewStatus = 'approved';
                $validCount++;
                $readyCount++;
            }

            $universal['readiness_status'] = $readinessStatus;
            $universal['validation_warnings'] = $warnings;
            $universal['warning_codes'] = $warningCodes;
            $candidate['readiness_status'] = $readinessStatus;

            $sanitizedCandidate = Utf8Sanitizer::clean($candidate);
            $sanitizedUniversal = Utf8Sanitizer::clean($universal);
            $sanitizedErrors = Utf8Sanitizer::clean($errors);
            $sanitizedWarnings = Utf8Sanitizer::clean($warnings);

            QuestionImportItem::create([
                'batch_id' => $batch->id,
                'source_index' => $index + 1,
                'raw_data' => $sanitizedCandidate,
                'normalized_data' => $sanitizedUniversal,
                'validation_status' => $itemStatus,
                'validation_errors' => $sanitizedErrors,
                'validation_warnings' => $sanitizedWarnings,
                'duplicate_status' => $dupResult['status'],
                'duplicate_question_id' => $dupResult['question_id'],
                'similarity_score' => $dupResult['similarity'],
                'review_status' => $reviewStatus,
            ]);
        }

        // Calculate Aggregate Quality Score
        $qualityResult = PdfQualityClassifier::calculateQualityScore(
            count($structuredPages),
            $totalQuestions,
            $avgConfidence,
            $warningCount,
            $errorCount,
            $classificationInfo['ocr_pages']
        );

        $pdfDiagnostics = [
            'page_count' => count($structuredPages),
            'native_text_pages' => $classificationInfo['native_text_pages'],
            'ocr_pages' => $classificationInfo['ocr_pages'],
            'failed_pages' => $classificationInfo['failed_pages'],
            'multi_column_pages' => $classificationInfo['multi_column_pages'],
            'images_detected' => array_sum(array_map('count', $mediaByPage)),
            'questions_detected' => $totalQuestions,
            'average_confidence' => $avgConfidence,
            'high_confidence_count' => $highConfCount,
            'medium_confidence_count' => $mediumConfCount,
            'low_confidence_count' => $lowConfCount,
            'ready_count' => $readyCount,
            'review_required_count' => $reviewRequiredCount,
            'failed_count' => $failedCount,
            'quality_score' => $qualityResult['score'],
            'quality_tier' => $qualityResult['tier'],
            'document_classification' => $classificationInfo['classification'],
        ];

        $batch->update([
            'total_questions' => $totalQuestions,
            'valid_count' => $validCount,
            'warning_count' => $warningCount,
            'error_count' => $errorCount,
            'duplicate_count' => $duplicateCount,
            'options' => Utf8Sanitizer::clean(array_merge($batch->options ?? [], [
                'pdf_diagnostics' => $pdfDiagnostics,
                'page_diagnostics' => $pageDiagnostics,
            ])),
        ]);

        return $batch;
    }
}
