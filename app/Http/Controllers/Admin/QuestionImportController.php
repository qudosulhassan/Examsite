<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionImportBatch;
use App\Models\QuestionImportItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionImportController extends Controller
{
    /**
     * Display the initial upload screen.
     */
    public function showImportForm()
    {
        $exams = Exam::orderBy('exam_code')->get();
        return view('admin.questions.import', compact('exams'));
    }

    /**
     * Display the dedicated PDF upload screen.
     */
    public function showPdfImportForm()
    {
        $exams = Exam::orderBy('exam_code')->get();
        return view('admin.questions.import-pdf', compact('exams'));
    }

    /**
     * Process uploaded PDF document through extraction pipeline.
     */
    public function processPdfUpload(Request $request)
    {
        $file = $request->file('pdf_file');

        // Check if no file was uploaded or upload failed at PHP level
        if (!$file) {
            $rawError = $_FILES['pdf_file']['error'] ?? null;
            if ($rawError === UPLOAD_ERR_INI_SIZE || $rawError === UPLOAD_ERR_FORM_SIZE) {
                $maxIni = ini_get('upload_max_filesize');
                return back()->withErrors(['pdf_file' => "The uploaded PDF exceeds server upload limit ({$maxIni}). Please upload a smaller file or increase upload_max_filesize."])->withInput();
            }
            return back()->withErrors(['pdf_file' => 'Please select or drag-and-drop a PDF file to upload.'])->withInput();
        }

        if (!$file->isValid()) {
            $error = $file->getError();
            $maxIni = ini_get('upload_max_filesize');
            $msg = match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "The uploaded PDF exceeds PHP upload size limit ({$maxIni}).",
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_FILE => 'No PDF file was received.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server error: Missing temporary upload directory.',
                UPLOAD_ERR_CANT_WRITE => 'Server error: Failed to write uploaded file to disk.',
                default => 'File upload error (Code: ' . $error . ').',
            };
            return back()->withErrors(['pdf_file' => $msg])->withInput();
        }

        // Validate extension safely
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['pdf', 'txt'])) {
            return back()->withErrors(['pdf_file' => 'The uploaded file must be a PDF document (.pdf).'])->withInput();
        }

        $filename = $file->getClientOriginalName();
        $filePath = $file->getRealPath();

        $options = [
            'auto_detect_topics' => $request->input('topic_strategy') !== 'manual',
            'manual_topic' => $request->input('manual_topic'),
            'run_ocr' => $request->boolean('opt_run_ocr', true),
            'extract_text' => $request->boolean('opt_extract_text', true),
            'detect_duplicates' => $request->boolean('opt_detect_duplicates', true),
            'detect_questions' => $request->boolean('opt_detect_questions', true),
            'detect_options' => $request->boolean('opt_detect_options', true),
            'detect_answers' => $request->boolean('opt_detect_answers', true),
            'detect_explanations' => $request->boolean('opt_detect_explanations', true),
            'detect_references' => $request->boolean('opt_detect_references', true),
            'extract_images' => $request->boolean('opt_extract_images', true),
            'run_full_validation' => $request->boolean('opt_run_validation', true),
            'require_review' => $request->boolean('opt_require_review', true),
        ];

        try {
            $defaultExamId = $request->input('default_exam_id') ? (int)$request->input('default_exam_id') : null;
            $batch = \App\Services\QuestionImport\PdfImportService::processPdf(
                $filePath,
                $filename,
                $defaultExamId,
                $options
            );

            return redirect()->route('admin.questions.import-review', $batch->uuid)
                ->with('success', "PDF document processed: {$batch->total_questions} question candidates detected and ready for review.");
        } catch (\Exception $e) {
            return back()->withErrors(['pdf_file' => 'PDF Processing Error: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Process uploaded JSON file, validate syntax, create batch and items.
     */
    public function processUpload(Request $request)
    {
        // Check for PHP upload limit errors before validation
        if ($request->hasFile('json_file') && !$request->file('json_file')->isValid()) {
            $error = $request->file('json_file')->getError();
            $maxIni = ini_get('upload_max_filesize');
            $postMax = ini_get('post_max_size');
            $msg = match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "The uploaded JSON exceeds PHP upload size limit (upload_max_filesize: {$maxIni}, post_max_size: {$postMax}).",
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_FILE => 'No JSON file was received.',
                default => 'File upload error (Code: ' . $error . ').',
            };
            return back()->withErrors(['json_file' => $msg])->withInput();
        }

        $request->validate([
            'json_file' => 'required|file|mimes:json,txt|max:51200', // max 50MB
            'default_exam_id' => 'nullable|exists:exams,id',
        ]);

        $file = $request->file('json_file');
        $filename = $file->getClientOriginalName();
        $content = file_get_contents($file->getRealPath());

        // Validate UTF-8
        if (!mb_check_encoding($content, 'UTF-8')) {
            return back()->withErrors(['json_file' => 'File must be valid UTF-8 encoded.'])->withInput();
        }

        // Validate JSON Syntax
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMsg = json_last_error_msg();
            $lines = explode("\n", $content);
            $errorLine = 1;
            for ($i = 1; $i <= count($lines); $i++) {
                $sub = implode("\n", array_slice($lines, 0, $i));
                json_decode($sub);
                if (json_last_error() !== JSON_ERROR_NONE && $i < count($lines)) {
                    $errorLine = $i;
                }
            }
            return back()->withErrors([
                'json_file' => "Invalid JSON File. Line: {$errorLine} — {$errorMsg}."
            ])->withInput();
        }

        $questionsList = $data;
        if (is_array($data) && isset($data['questions']) && is_array($data['questions'])) {
            $questionsList = $data['questions'];
        }

        if (!is_array($questionsList) || empty($questionsList)) {
            return back()->withErrors(['json_file' => 'JSON must contain a non-empty array of questions.'])->withInput();
        }

        // Detect Format (V1, V2, Mixed)
        $v1Count = 0;
        $v2Count = 0;
        foreach ($questionsList as $item) {
            if (isset($item['option_a']) || isset($item['correct_option'])) {
                $v1Count++;
            } elseif (isset($item['question_type']) || isset($item['options']) || isset($item['correct_answers'])) {
                $v2Count++;
            } else {
                $v1Count++;
            }
        }

        $formatDetected = 'Universal Question Format V2';
        if ($v1Count > 0 && $v2Count === 0) {
            $formatDetected = 'Legacy Question Format V1';
        } elseif ($v1Count > 0 && $v2Count > 0) {
            $formatDetected = 'Mixed (V1 + V2)';
        }

        $detectDuplicates = $request->boolean('opt_detect_duplicates', true);
        $importAsDraft = $request->boolean('opt_import_as_draft', true);
        $defaultExamId = $request->input('default_exam_id') ? (int)$request->input('default_exam_id') : null;

        // Generate Batch UUID (e.g. IMP-2026-001)
        $year = date('Y');
        $batchCount = QuestionImportBatch::whereYear('created_at', $year)->count() + 1;
        $uuid = sprintf('IMP-%s-%03d', $year, $batchCount);

        $batch = QuestionImportBatch::create([
            'uuid' => $uuid,
            'filename' => $filename,
            'source_type' => 'json_import',
            'format_detected' => $formatDetected,
            'default_exam_id' => $defaultExamId,
            'status' => 'ready_for_review',
            'options' => [
                'normalize_legacy' => $request->boolean('opt_normalize_legacy', true),
                'detect_duplicates' => $detectDuplicates,
                'run_full_validation' => $request->boolean('opt_run_validation', true),
                'import_as_draft' => $importAsDraft,
                'require_review' => $request->boolean('opt_require_review', true),
            ],
            'total_questions' => count($questionsList),
            'created_by' => auth()->id(),
        ]);

        // Process & Normalize each question
        $validCount = 0;
        $warningCount = 0;
        $errorCount = 0;
        $duplicateCount = 0;

        foreach ($questionsList as $index => $rawItem) {
            $errors = [];
            $warnings = [];

            // Resolve exam priority: 1. item exam_id, 2. item exam_code, 3. default_exam_id
            $resolvedExamId = null;
            if (!empty($rawItem['exam_id'])) {
                $resolvedExamId = (int)$rawItem['exam_id'];
            } elseif (!empty($rawItem['exam_code'])) {
                $examModel = Exam::where('exam_code', trim($rawItem['exam_code']))->first();
                if ($examModel) {
                    $resolvedExamId = $examModel->id;
                } else {
                    $errors[] = "Exam code '{$rawItem['exam_code']}' could not be resolved to a valid exam.";
                }
            } else {
                $resolvedExamId = $defaultExamId;
            }

            $rawItem['exam_id'] = $resolvedExamId;
            $universal = Question::convertToUniversalModel($rawItem);
            $universal['source_type'] = 'json_import';
            $universal['source_reference'] = [
                'filename' => $filename,
                'import_batch_id' => $uuid,
            ];
            if ($importAsDraft) {
                $universal['status'] = 'draft';
                $universal['is_active'] = false;
            }

            // Validation Checks
            if (empty($universal['exam_id'])) {
                $errors[] = 'Target Certification Exam is required and could not be resolved.';
            } else {
                if (!Exam::where('id', $universal['exam_id'])->exists()) {
                    $errors[] = "Target Exam ID '{$universal['exam_id']}' does not exist.";
                }
            }

            if (empty($universal['question_text'])) {
                $errors[] = 'Question text is required.';
            }

            if (empty($universal['topic'])) {
                $warnings[] = 'Topic is missing (defaults to General).';
                $universal['topic'] = 'General';
            }

            $type = $universal['question_type'] ?? 'single_choice';
            switch ($type) {
                case 'single_choice':
                    if (count($universal['options']) < 2) {
                        $errors[] = 'Single choice questions must have at least 2 options.';
                    }
                    if (count($universal['correct_answers']) !== 1) {
                        $errors[] = 'Single choice questions must have exactly 1 correct answer.';
                    } else {
                        $ans = $universal['correct_answers'][0];
                        $keys = collect($universal['options'])->pluck('key')->toArray();
                        if (!in_array($ans, $keys)) {
                            $errors[] = 'Correct answer "' . $ans . '" does not exist in available options.';
                        }
                    }
                    break;
                case 'multiple_choice':
                    if (count($universal['options']) < 2) {
                        $errors[] = 'Multiple choice questions must have at least 2 options.';
                    }
                    if (count($universal['correct_answers']) < 2) {
                        $errors[] = 'Multiple choice questions must have at least 2 correct answers selected.';
                    }
                    $keys = collect($universal['options'])->pluck('key')->toArray();
                    foreach ($universal['correct_answers'] as $ans) {
                        if (!in_array($ans, $keys)) {
                            $errors[] = 'Correct answer "' . $ans . '" does not exist in available options.';
                        }
                    }
                    break;
                case 'yes_no':
                    if (count($universal['options']) !== 2) {
                        $errors[] = 'Yes/No questions must contain exactly 2 options.';
                    }
                    if (count($universal['correct_answers']) !== 1) {
                        $errors[] = 'Yes/No questions must have exactly 1 correct answer choice.';
                    }
                    break;
                case 'drag_drop':
                    if (count($universal['drag_items']) < 2) {
                        $errors[] = 'Drag & Drop questions must have at least 2 sequenced items.';
                    }
                    $itemIds = collect($universal['drag_items'])->pluck('id')->toArray();
                    if (count(array_unique($itemIds)) !== count($itemIds)) {
                        $errors[] = 'Every item in drag_items must have a unique ID.';
                    }
                    if (count($universal['correct_order']) < 2) {
                        $errors[] = 'correct_order sequence is missing or too short.';
                    } else {
                        foreach ($universal['correct_order'] as $idVal) {
                            if (!in_array($idVal, $itemIds)) {
                                $errors[] = "Sequence order ID '{$idVal}' does not exist in drag_items.";
                            }
                        }
                    }
                    break;
                case 'hotspot':
                    if (count($universal['hotspot_answers']) < 1) {
                        $errors[] = 'Hotspot questions must contain at least 1 dropdown box configuration.';
                    }
                    foreach ($universal['hotspot_answers'] as $bIdx => $box) {
                        if (empty($box['label'])) {
                            $errors[] = "Hotspot box index {$bIdx} has empty label.";
                        }
                        if (empty($box['options']) || !is_array($box['options'])) {
                            $errors[] = "Hotspot box index {$bIdx} is missing dropdown choices list.";
                        } else {
                            if (empty($box['correct_answer'])) {
                                $errors[] = "Hotspot box index {$bIdx} is missing a selected correct answer.";
                            } elseif (!in_array($box['correct_answer'], $box['options'])) {
                                $errors[] = "Hotspot box correct answer '{$box['correct_answer']}' is not in dropdown options.";
                            }
                        }
                    }
                    break;
                case 'matching':
                    if (count($universal['matching_pairs']) < 1) {
                        $errors[] = 'Matching questions must contain at least 1 pair.';
                    }
                    foreach ($universal['matching_pairs'] as $pIdx => $pair) {
                        if (empty($pair['left']) || empty($pair['right'])) {
                            $errors[] = "Matching pair index {$pIdx} has empty left/right elements.";
                        }
                    }
                    break;
            }

            if (empty($universal['explanation'])) {
                $warnings[] = 'No answer explanation provided.';
            }

            // Duplicate Detection
            $dupResult = ['status' => 'none', 'question_id' => null, 'similarity' => 0];
            if ($detectDuplicates && !empty($universal['question_text'])) {
                $dupResult = self::checkDuplicate($universal['question_text'], $universal['exam_id']);
                if ($dupResult['status'] !== 'none') {
                    $warnings[] = "Possible Duplicate: {$dupResult['similarity']}% match with existing Question #{$dupResult['question_id']}.";
                }
            }

            // Determine Item Status
            $itemValidationStatus = 'valid';
            $reviewStatus = 'approved';

            if (count($errors) > 0) {
                $itemValidationStatus = 'error';
                $reviewStatus = 'needs_fix';
                $errorCount++;
            } elseif ($dupResult['status'] !== 'none') {
                $itemValidationStatus = 'duplicate';
                $reviewStatus = 'pending';
                $duplicateCount++;
                $warningCount++;
            } elseif (count($warnings) > 0) {
                $itemValidationStatus = 'warning';
                $reviewStatus = 'approved';
                $warningCount++;
                $validCount++;
            } else {
                $itemValidationStatus = 'valid';
                $reviewStatus = 'approved';
                $validCount++;
            }

            QuestionImportItem::create([
                'batch_id' => $batch->id,
                'source_index' => $index + 1,
                'raw_data' => $rawItem,
                'normalized_data' => $universal,
                'validation_status' => $itemValidationStatus,
                'validation_errors' => $errors,
                'validation_warnings' => $warnings,
                'duplicate_status' => $dupResult['status'],
                'duplicate_question_id' => $dupResult['question_id'],
                'similarity_score' => $dupResult['similarity'],
                'review_status' => $reviewStatus,
            ]);
        }

        $batch->update([
            'valid_count' => $validCount,
            'warning_count' => $warningCount,
            'error_count' => $errorCount,
            'duplicate_count' => $duplicateCount,
        ]);

        return redirect()->route('admin.questions.import-review', $batch->uuid)
            ->with('success', 'JSON file processed and ready for review.');
    }

    /**
     * Show the Import Summary & Review Screen.
     */
    public function showReview(string $uuid)
    {
        $batch = QuestionImportBatch::with(['defaultExam', 'creator'])->where('uuid', $uuid)->firstOrFail();
        $exams = Exam::orderBy('exam_code')->get();
        $items = $batch->items()->orderBy('source_index')->get();

        return view('admin.questions.import-review', compact('batch', 'exams', 'items'));
    }

    /**
     * Get single import item data via AJAX.
     */
    public function getItem(int $id)
    {
        $item = QuestionImportItem::with(['duplicateQuestion', 'batch'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'item' => $item,
        ]);
    }

    /**
     * Display standalone Learner Preview for an import candidate item.
     */
    public function previewItem(int $id)
    {
        $item = QuestionImportItem::with(['duplicateQuestion', 'batch'])->findOrFail($id);
        $batch = $item->batch;

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => $item,
                'mode' => 'learner_preview',
            ]);
        }

        return view('admin.questions.import-candidate-preview', compact('item', 'batch'));
    }

    /**
     * Display standalone Admin Review for an import candidate item.
     */
    public function reviewItem(int $id)
    {
        $item = QuestionImportItem::with(['duplicateQuestion', 'batch'])->findOrFail($id);
        $batch = $item->batch;

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => $item,
                'mode' => 'admin_review',
            ]);
        }

        return view('admin.questions.import-candidate-review', compact('item', 'batch'));
    }

    /**
     * Display standalone Candidate Edit form for an import candidate item.
     */
    public function editItem(int $id)
    {
        $item = QuestionImportItem::with(['duplicateQuestion', 'batch'])->findOrFail($id);
        $batch = $item->batch;
        $exams = Exam::orderBy('exam_code')->get();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => $item,
                'mode' => 'edit',
            ]);
        }

        return view('admin.questions.import-candidate-edit', compact('item', 'batch', 'exams'));
    }

    /**
     * Alias for candidate preview with batch UUID in route.
     */
    public function previewCandidate(string $uuid, int $id)
    {
        return $this->previewItem($id);
    }

    /**
     * Alias for candidate review with batch UUID in route.
     */
    public function reviewCandidate(string $uuid, int $id)
    {
        return $this->reviewItem($id);
    }

    /**
     * Alias for candidate edit with batch UUID in route.
     */
    public function editCandidate(string $uuid, int $id)
    {
        return $this->editItem($id);
    }

    /**
     * Update an item's normalized question data, re-validate, and update review status.
     */
    public function updateItem(Request $request, int $id)
    {
        $item = QuestionImportItem::findOrFail($id);
        $input = $request->all();

        // Preserve and sanitize nested structure
        $existingNorm = $item->normalized_data ?? [];
        $universal = Question::convertToUniversalModel($input);
        
        $universal['source_type'] = $existingNorm['source_type'] ?? 'pdf_import';
        $universal['source_reference'] = $existingNorm['source_reference'] ?? [];
        $universal['debug_info'] = $existingNorm['debug_info'] ?? ($item->raw_data['debug_info'] ?? []);
        $universal['question_exhibits'] = $input['question_exhibits'] ?? ($existingNorm['question_exhibits'] ?? []);
        $universal['answer_exhibits'] = $input['answer_exhibits'] ?? ($existingNorm['answer_exhibits'] ?? []);
        $universal['exhibits'] = $universal['question_exhibits'];

        // If answer area was passed in input (e.g. for Hotspot or Drag & Drop)
        if (!empty($input['answer_area'])) {
            $universal['answer_area'] = $input['answer_area'];
        } elseif (!empty($existingNorm['answer_area'])) {
            $universal['answer_area'] = $existingNorm['answer_area'];
        }

        $errors = [];
        $warnings = [];
        $warningCodes = [];

        if (empty($universal['exam_id'])) {
            $warnings[] = 'Target Certification Exam is not assigned.';
            $warningCodes[] = 'MISSING_EXAM_ASSIGNMENT';
        }

        if (empty($universal['question_text'])) {
            $errors[] = 'Question text is required.';
            $warningCodes[] = 'MISSING_QUESTION_TEXT';
        }

        if (empty($universal['topic'])) {
            $universal['topic'] = 'Topic 1';
        }

        $type = $universal['question_type'] ?? 'single_choice';
        switch ($type) {
            case 'single_choice':
                if (count($universal['options']) < 2) {
                    $errors[] = 'Single choice questions must have at least 2 options.';
                    $warningCodes[] = 'INSUFFICIENT_OPTIONS';
                }
                if (count($universal['correct_answers']) !== 1) {
                    $errors[] = 'Single choice questions must have exactly 1 correct answer.';
                    $warningCodes[] = 'INVALID_ANSWER_COUNT';
                } else {
                    $ans = $universal['correct_answers'][0];
                    $keys = collect($universal['options'])->pluck('key')->toArray();
                    if (!in_array($ans, $keys, true)) {
                        $errors[] = 'Correct answer "' . $ans . '" does not exist in available options.';
                        $warningCodes[] = 'INVALID_ANSWER_LETTER';
                    }
                }
                break;
            case 'multiple_choice':
                if (count($universal['options']) < 2) {
                    $errors[] = 'Multiple choice questions must have at least 2 options.';
                    $warningCodes[] = 'INSUFFICIENT_OPTIONS';
                }
                if (count($universal['correct_answers']) < 2) {
                    $warnings[] = 'Multiple choice questions usually have at least 2 correct answers selected.';
                }
                $keys = collect($universal['options'])->pluck('key')->toArray();
                foreach ($universal['correct_answers'] as $ans) {
                    if (!in_array($ans, $keys, true)) {
                        $errors[] = 'Correct answer "' . $ans . '" does not exist in available options.';
                        $warningCodes[] = 'INVALID_ANSWER_LETTER';
                    }
                }
                break;
            case 'hotspot':
                if (empty($universal['answer_area']['boxes']) && empty($universal['question_exhibits'])) {
                    $warnings[] = 'Hotspot answer dropdown boxes or visual exhibits should be configured.';
                    $warningCodes[] = 'HOTSPOT_AREA_UNPARSED';
                }
                break;
            case 'drag_drop':
                if (empty($universal['answer_area']['steps']) && empty($universal['question_exhibits'])) {
                    $warnings[] = 'Drag & drop sequence actions should be configured.';
                    $warningCodes[] = 'DRAG_STEPS_UNPARSED';
                }
                break;
        }

        if (empty($universal['explanation'])) {
            $warnings[] = 'No answer explanation provided.';
        }

        // Duplicate Check
        $dupResult = ['status' => 'none', 'question_id' => null, 'similarity' => 0];
        if (!empty($universal['question_text']) && !empty($universal['exam_id'])) {
            $dupResult = self::checkDuplicate($universal['question_text'], $universal['exam_id']);
            if ($dupResult['status'] !== 'none') {
                $warnings[] = "Possible Duplicate: {$dupResult['similarity']}% similarity with Question #{$dupResult['question_id']}.";
                $warningCodes[] = 'POSSIBLE_DUPLICATE';
            }
        }

        // Run Source Consistency Check for PDF imports
        $isPdfSource = ($universal['source_type'] ?? '') === 'pdf_import' || !empty($universal['debug_info']['raw_text_block']);
        $consistencyResult = ['readiness_status' => 'READY', 'field_statuses' => [], 'discrepancies' => []];
        if ($isPdfSource) {
            $candidateDataForValidation = array_merge($universal, [
                'debug_info' => $universal['debug_info'],
                'source_metadata' => [
                    'page_start' => $universal['source_reference']['page_start'] ?? 1,
                    'page_end' => $universal['source_reference']['page_end'] ?? 1,
                ],
            ]);
            $consistencyResult = \App\Services\QuestionImport\Pdf\PdfSourceConsistencyValidator::validateCandidate($candidateDataForValidation);
        }

        $universal['field_statuses'] = $consistencyResult['field_statuses'];
        $universal['discrepancies'] = $consistencyResult['discrepancies'];

        $validationStatus = 'valid';
        $reviewStatus = 'approved';
        $readinessStatus = 'READY';

        if (count($errors) > 0 || $consistencyResult['readiness_status'] === 'FAILED') {
            $validationStatus = 'error';
            $reviewStatus = 'needs_fix';
            $readinessStatus = 'FAILED';
        } elseif ($dupResult['status'] !== 'none') {
            $validationStatus = 'duplicate';
            $reviewStatus = 'pending';
            $readinessStatus = 'REVIEW_REQUIRED';
        } elseif (count($warnings) > 0 || ($isPdfSource && $consistencyResult['readiness_status'] === 'REVIEW_REQUIRED')) {
            $validationStatus = 'warning';
            $reviewStatus = 'pending';
            $readinessStatus = 'REVIEW_REQUIRED';
        }

        $universal['readiness_status'] = $readinessStatus;
        $universal['validation_warnings'] = array_values(array_unique($warnings));
        $universal['warning_codes'] = array_values(array_unique($warningCodes));

        $sanitizedUniversal = \App\Services\QuestionImport\Utf8Sanitizer::clean($universal);
        $sanitizedErrors = \App\Services\QuestionImport\Utf8Sanitizer::clean($errors);
        $sanitizedWarnings = \App\Services\QuestionImport\Utf8Sanitizer::clean($warnings);

        $item->update([
            'normalized_data' => $sanitizedUniversal,
            'validation_status' => $validationStatus,
            'validation_errors' => $sanitizedErrors,
            'validation_warnings' => $sanitizedWarnings,
            'duplicate_status' => $dupResult['status'],
            'duplicate_question_id' => $dupResult['question_id'],
            'similarity_score' => $dupResult['similarity'],
            'review_status' => $reviewStatus,
        ]);

        $item->batch->recalculateCounts();

        if ($request->header('Accept') === 'text/html' && !$request->ajax() && !$request->expectsJson()) {
            return redirect()->route('admin.questions.import-review', $item->batch->uuid ?? '')
                ->with('success', "Candidate #{$item->source_index} updated successfully.");
        }

        return response()->json([
            'success' => true,
            'message' => 'Candidate question updated and re-validated successfully.',
            'item' => $item->fresh(),
        ]);
    }

    /**
     * Update review status of an import item (e.g. approve, reject, skip).
     */
    public function updateItemReviewStatus(Request $request, int $id)
    {
        $request->validate([
            'review_status' => 'required|in:approved,rejected,needs_fix,pending',
        ]);

        $item = QuestionImportItem::findOrFail($id);
        $item->update(['review_status' => $request->review_status]);

        return response()->json([
            'success' => true,
            'item' => $item,
        ]);
    }

    /**
     * Import approved/selected questions into the live database.
     */
    public function importSelected(Request $request, string $uuid)
    {
        $batch = QuestionImportBatch::where('uuid', $uuid)->firstOrFail();
        $itemIds = $request->input('item_ids', []);
        $replaceDuplicates = $request->boolean('replace_duplicates', false);

        if (empty($itemIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No questions selected for import.'
            ], 422);
        }

        $items = $batch->items()->whereIn('id', $itemIds)->get();
        $importedCount = 0;
        $errorsOccurred = 0;

        DB::transaction(function () use ($items, $replaceDuplicates, &$importedCount, &$errorsOccurred) {
            foreach ($items as $item) {
                // Skip errors
                if ($item->validation_status === 'error' && empty($item->normalized_data['question_text'])) {
                    $errorsOccurred++;
                    continue;
                }

                $data = $item->normalized_data;
                $existingQuestion = null;

                if ($replaceDuplicates && $item->duplicate_question_id) {
                    $existingQuestion = Question::find($item->duplicate_question_id);
                }

                $savedQuestion = Question::saveFromUniversalModel($data, $existingQuestion);
                $item->update([
                    'imported_question_id' => $savedQuestion->id,
                    'review_status' => 'approved',
                ]);
                $importedCount++;
            }
        });

        $batch->update([
            'imported_count' => $importedCount,
            'status' => $errorsOccurred > 0 ? 'completed_with_errors' : 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'imported_count' => $importedCount,
            'message' => "Successfully imported {$importedCount} questions into the question bank.",
        ]);
    }

    /**
     * Download error report for a batch.
     */
    public function downloadErrorReport(string $uuid)
    {
        $batch = QuestionImportBatch::where('uuid', $uuid)->firstOrFail();
        $items = $batch->items()->where(function ($q) {
            $q->where('validation_status', 'error')
              ->orWhere('validation_status', 'warning')
              ->orWhere('validation_status', 'duplicate');
        })->orderBy('source_index')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"error-report-{$uuid}.csv\"",
        ];

        return new StreamedResponse(function () use ($items, $batch) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Item #', 'Status', 'Question Text', 'Exam ID', 'Topic', 'Issues / Errors', 'Duplicate Match']);

            foreach ($items as $item) {
                $issues = array_merge($item->validation_errors ?? [], $item->validation_warnings ?? []);
                fputcsv($handle, [
                    $item->source_index,
                    strtoupper($item->validation_status),
                    $item->normalized_data['question_text'] ?? ($item->raw_data['question_text'] ?? 'N/A'),
                    $item->normalized_data['exam_id'] ?? 'N/A',
                    $item->normalized_data['topic'] ?? 'N/A',
                    implode(' | ', $issues),
                    $item->duplicate_question_id ? "Question #{$item->duplicate_question_id} ({$item->similarity_score}%)" : 'None',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Cancel/Delete an import batch.
     */
    public function cancelBatch(string $uuid)
    {
        $batch = QuestionImportBatch::where('uuid', $uuid)->firstOrFail();
        $batch->delete();

        return redirect()->route('admin.questions.import-history')
            ->with('success', "Import batch {$uuid} has been cancelled and deleted.");
    }

    /**
     * View import history list.
     */
    public function history()
    {
        $batches = QuestionImportBatch::with(['defaultExam', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.questions.history', compact('batches'));
    }

    /**
     * Helper to perform multi-level duplicate detection.
     */
    public static function checkDuplicate(string $text, ?int $examId): array
    {
        $normalized = preg_replace('/[^\w\s]/', '', strtolower(trim($text)));
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $hash = md5($normalized);

        $query = Question::query();
        if ($examId) {
            $query->where('exam_id', $examId);
        }

        // Level 1: Exact Hash Match
        $exact = (clone $query)->where('question_hash', $hash)->first();
        if ($exact) {
            return [
                'status' => 'exact',
                'question_id' => $exact->id,
                'similarity' => 100,
            ];
        }

        // Level 2/3: Normalized text similarity
        $candidates = (clone $query)->select('id', 'question_text', 'question_hash')->limit(150)->get();
        foreach ($candidates as $cand) {
            $candNormalized = preg_replace('/[^\w\s]/', '', strtolower(trim($cand->question_text)));
            $candNormalized = preg_replace('/\s+/', ' ', $candNormalized);

            similar_text($normalized, $candNormalized, $percent);
            if ($percent >= 85) {
                return [
                    'status' => 'similar',
                    'question_id' => $cand->id,
                    'similarity' => (int)round($percent),
                ];
            }
        }

        return [
            'status' => 'none',
            'question_id' => null,
            'similarity' => 0,
        ];
    }
}
