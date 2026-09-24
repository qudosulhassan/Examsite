<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Exam;
use App\Models\ImportHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QuestionAdminController extends Controller
{
    public function index(Request $request)
    {
        $examId = $request->get('exam_id');
        $questionsQuery = Question::with('exam');

        if ($examId) {
            $questionsQuery->where('exam_id', $examId);
        }

        $questions = $questionsQuery->orderBy('id', 'desc')->paginate(15);
        $exams = Exam::orderBy('exam_code')->get();

        return view('admin.questions.index', compact('questions', 'exams', 'examId'));
    }

    public function create()
    {
        $exams = Exam::orderBy('exam_code')->get();
        return view('admin.questions.create', compact('exams'));
    }

    public function store(Request $request)
    {
        $questionType = $request->input('question_type', 'single_choice');

        $rules = [
            'exam_id' => 'required|exists:exams,id',
            'question_text' => 'required|string',
            'topic' => 'nullable|string',
            'question_type' => 'nullable|string|in:single_choice,multiple_choice,yes_no,drag_drop,hotspot,matching',
            'instructions' => 'nullable|string',
            'explanation' => 'nullable|string',
            'status' => 'nullable|string|in:draft,pending_review,approved,published,rejected,archived',
        ];

        if (in_array($questionType, ['single_choice', 'multiple_choice', 'yes_no'])) {
            $rules['option_a'] = 'required_without:options|string';
            $rules['option_b'] = 'required_without:options|string';
            $rules['correct_option'] = 'required_without:correct_answers|string|max:5';
        }

        $request->validate($rules);

        $input = $request->all();
        $input['is_active'] = $request->has('is_active') ? true : false;
        $input['status'] = $request->input('status', 'draft');

        if ($request->hasFile('question_image')) {
            $file = $request->file('question_image');
            $filename = time() . '_q_' . $file->getClientOriginalName();
            $file->move(public_path('storage/questions'), $filename);
            $input['image_filename'] = $filename;
            $input['media'] = $input['media'] ?? [];
            $input['media'][] = [
                'type' => 'question_image',
                'url' => '/storage/questions/' . $filename,
                'caption' => 'Question Diagram',
                'alt' => 'Question Diagram',
                'sort_order' => 0,
            ];
        }

        if ($request->hasFile('answer_area_image')) {
            $file = $request->file('answer_area_image');
            $filename = time() . '_ans_area_' . $file->getClientOriginalName();
            $file->move(public_path('storage/questions'), $filename);
            $imgUrl = '/storage/questions/' . $filename;

            $input['question_data'] = $input['question_data'] ?? [];
            $input['question_data']['answer_area_image'] = $imgUrl;

            $input['media'] = $input['media'] ?? [];
            $input['media'][] = [
                'type' => 'answer_area',
                'url' => $imgUrl,
                'caption' => 'Answer Area Image',
                'alt' => 'Answer Area Image',
                'sort_order' => 2,
            ];
        }

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/questions'), $filename);
            $input['media'] = $input['media'] ?? [];
            $input['media'][] = [
                'type' => 'image',
                'url' => '/storage/questions/' . $filename,
                'caption' => $request->media_caption ?? 'Exhibit',
                'alt' => $request->media_caption ?? 'Exhibit',
                'sort_order' => 1,
            ];
        }

        // Process Option Images Upload (Section D)
        if (!empty($input['options']) && is_array($input['options'])) {
            foreach ($input['options'] as $idx => &$opt) {
                if ($request->hasFile("option_image_{$idx}")) {
                    $file = $request->file("option_image_{$idx}");
                    $filename = time() . '_opt_' . $idx . '_' . $file->getClientOriginalName();
                    $file->move(public_path('storage/questions'), $filename);
                    $imgUrl = '/storage/questions/' . $filename;
                    $opt['text'] = trim(($opt['text'] ?? '') . "\n" . '<img src="' . $imgUrl . '" alt="Option Image" class="max-h-48 rounded my-2 block shadow-sm">');
                }
            }
            unset($opt);
        }

        if (!empty($input['references']) && is_array($input['references'])) {
            $input['references'] = array_values(array_filter($input['references'], function ($ref) {
                return !empty($ref['title']) || !empty($ref['url']);
            }));
        }

        if (!empty($input['boxes']) && is_array($input['boxes'])) {
            $input['boxes'] = array_values($input['boxes']);
            foreach ($input['boxes'] as $bIdx => &$b) {
                $b['id'] = 'box_' . ($bIdx + 1);
                if (empty($b['options']) && !empty($b['optionsText'])) {
                    $b['options'] = array_map('trim', explode(',', $b['optionsText']));
                }
                if (isset($b['points'])) {
                    $b['points'] = (int)$b['points'];
                }
            }
            unset($b);
        }

        $universalData = Question::convertToUniversalModel($input);
        Question::saveFromUniversalModel($universalData);

        return redirect()->route('admin.questions.index', ['exam_id' => $request->exam_id])
            ->with('success', 'Question created successfully.');
    }

    public function show(int $id)
    {
        $question = Question::with(['exam', 'options', 'answers', 'references', 'media'])->findOrFail($id);
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'question' => $question,
            ]);
        }
        return view('admin.questions.show', compact('question'));
    }

    public function preview(int $id)
    {
        $question = Question::with(['exam', 'options', 'answers', 'references', 'media'])->findOrFail($id);
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'question' => $question,
                'mode' => 'learner_preview',
            ]);
        }
        return view('admin.questions.preview', compact('question'));
    }

    public function edit(int $id)
    {
        $question = Question::findOrFail($id);
        $exams = Exam::orderBy('exam_code')->get();
        return view('admin.questions.edit', compact('question', 'exams'));
    }

    public function update(Request $request, int $id)
    {
        $question = Question::findOrFail($id);
        $questionType = $request->input('question_type', $question->question_type ?? 'single_choice');

        $rules = [
            'exam_id' => 'required|exists:exams,id',
            'question_text' => 'required|string',
            'topic' => 'nullable|string',
            'question_type' => 'nullable|string|in:single_choice,multiple_choice,yes_no,drag_drop,hotspot,matching',
            'instructions' => 'nullable|string',
            'explanation' => 'nullable|string',
            'status' => 'nullable|string|in:draft,pending_review,approved,published,rejected,archived',
        ];

        if (in_array($questionType, ['single_choice', 'multiple_choice', 'yes_no'])) {
            $rules['option_a'] = 'required_without:options|string';
            $rules['option_b'] = 'required_without:options|string';
            $rules['correct_option'] = 'required_without:correct_answers|string|max:5';
        }

        $request->validate($rules);

        $input = $request->all();
        $input['is_active'] = $request->has('is_active') ? true : false;
        $input['status'] = $request->input('status', 'draft');

        if ($request->hasFile('question_image')) {
            $file = $request->file('question_image');
            $filename = time() . '_q_' . $file->getClientOriginalName();
            $file->move(public_path('storage/questions'), $filename);
            $input['image_filename'] = $filename;
            $input['media'] = $input['media'] ?? [];
            $input['media'][] = [
                'type' => 'question_image',
                'url' => '/storage/questions/' . $filename,
                'caption' => 'Question Diagram',
                'alt' => 'Question Diagram',
                'sort_order' => 0,
            ];
        }

        if ($request->hasFile('answer_area_image')) {
            $file = $request->file('answer_area_image');
            $filename = time() . '_ans_area_' . $file->getClientOriginalName();
            $file->move(public_path('storage/questions'), $filename);
            $imgUrl = '/storage/questions/' . $filename;

            $input['question_data'] = $input['question_data'] ?? [];
            $input['question_data']['answer_area_image'] = $imgUrl;

            $input['media'] = $input['media'] ?? [];
            $input['media'][] = [
                'type' => 'answer_area',
                'url' => $imgUrl,
                'caption' => 'Answer Area Image',
                'alt' => 'Answer Area Image',
                'sort_order' => 2,
            ];
        }

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/questions'), $filename);
            $input['media'] = $input['media'] ?? [];
            $input['media'][] = [
                'type' => 'image',
                'url' => '/storage/questions/' . $filename,
                'caption' => $request->media_caption ?? 'Exhibit',
                'alt' => $request->media_caption ?? 'Exhibit',
                'sort_order' => 1,
            ];
        } else if (empty($input['media'])) {
            // Preserve existing media
            $existingMedia = $question->media;
            if ($existingMedia->isNotEmpty()) {
                $input['media'] = $existingMedia->map(fn($m) => [
                    'type' => $m->media_type,
                    'url' => $m->media_url,
                    'caption' => $m->caption,
                    'alt' => $m->alt_text,
                    'sort_order' => $m->sort_order,
                ])->toArray();
            }
        }

        // Process Option Images Upload (Section D)
        if (!empty($input['options']) && is_array($input['options'])) {
            foreach ($input['options'] as $idx => &$opt) {
                if ($request->hasFile("option_image_{$idx}")) {
                    $file = $request->file("option_image_{$idx}");
                    $filename = time() . '_opt_' . $idx . '_' . $file->getClientOriginalName();
                    $file->move(public_path('storage/questions'), $filename);
                    $imgUrl = '/storage/questions/' . $filename;
                    $opt['text'] = trim(($opt['text'] ?? '') . "\n" . '<img src="' . $imgUrl . '" alt="Option Image" class="max-h-48 rounded my-2 block shadow-sm">');
                }
            }
            unset($opt);
        }

        if (!empty($input['references']) && is_array($input['references'])) {
            $input['references'] = array_values(array_filter($input['references'], function ($ref) {
                return !empty($ref['title']) || !empty($ref['url']);
            }));
        }

        if (!empty($input['boxes']) && is_array($input['boxes'])) {
            $input['boxes'] = array_values($input['boxes']);
            foreach ($input['boxes'] as $bIdx => &$b) {
                $b['id'] = 'box_' . ($bIdx + 1);
                if (empty($b['options']) && !empty($b['optionsText'])) {
                    $b['options'] = array_map('trim', explode(',', $b['optionsText']));
                }
                if (isset($b['points'])) {
                    $b['points'] = (int)$b['points'];
                }
            }
            unset($b);
        }

        $universalData = Question::convertToUniversalModel($input);
        Question::saveFromUniversalModel($universalData, $question);

        return redirect()->route('admin.questions.index', ['exam_id' => $request->exam_id])
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(int $id)
    {
        $question = Question::findOrFail($id);
        $examId = $question->exam_id;
        $question->delete();

        $exam = Exam::find($examId);
        if ($exam) {
            $exam->update(['question_count' => $exam->questions()->count()]);
        }
        
        return redirect()->route('admin.questions.index', ['exam_id' => $examId])
            ->with('success', 'Question deleted successfully.');
    }

    /**
     * Handle bulk actions (delete selected, delete all in exam, activate, deactivate).
     */
    public function bulkAction(Request $request)
    {
        $action = $request->input('action');

        if ($action === 'delete_all_exam') {
            $request->validate([
                'exam_id' => 'required|exists:exams,id',
            ]);

            $exam = Exam::findOrFail($request->exam_id);
            $deletedCount = Question::where('exam_id', $exam->id)->count();

            DB::transaction(function () use ($exam) {
                Question::where('exam_id', $exam->id)->chunkById(100, function ($questions) {
                    foreach ($questions as $question) {
                        $question->delete();
                    }
                });

                $exam->update(['question_count' => 0]);
            });

            return redirect()->route('admin.questions.index', ['exam_id' => $exam->id])
                ->with('success', "Successfully deleted all {$deletedCount} questions for {$exam->exam_code}.");
        }

        $request->validate([
            'action' => 'required|in:delete,activate,deactivate',
            'question_ids' => 'required|array|min:1',
            'question_ids.*' => 'exists:questions,id',
        ]);

        $questionIds = $request->input('question_ids', []);
        $affectedExamIds = Question::whereIn('id', $questionIds)->pluck('exam_id')->unique()->filter()->toArray();

        if ($action === 'delete') {
            DB::transaction(function () use ($questionIds, $affectedExamIds) {
                Question::whereIn('id', $questionIds)->chunkById(100, function ($questions) {
                    foreach ($questions as $question) {
                        $question->delete();
                    }
                });

                foreach ($affectedExamIds as $examId) {
                    $exam = Exam::find($examId);
                    if ($exam) {
                        $exam->update(['question_count' => $exam->questions()->count()]);
                    }
                }
            });

            $count = count($questionIds);
            return redirect()->back()->with('success', "Successfully deleted {$count} selected questions.");
        }

        if ($action === 'activate') {
            Question::whereIn('id', $questionIds)->update(['is_active' => true]);
            $count = count($questionIds);
            return redirect()->back()->with('success', "Successfully activated {$count} selected questions.");
        }

        if ($action === 'deactivate') {
            Question::whereIn('id', $questionIds)->update(['is_active' => false]);
            $count = count($questionIds);
            return redirect()->back()->with('success', "Successfully deactivated {$count} selected questions.");
        }

        return redirect()->back()->with('error', 'Invalid bulk action specified.');
    }

    public function showImportForm()
    {
        $exams = Exam::orderBy('exam_code')->get();
        return view('admin.questions.import', compact('exams'));
    }

    public function importJson(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'json_file' => 'required|file|mimes:json,txt',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $file = $request->file('json_file');
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['json_file' => 'Invalid JSON file: ' . json_last_error_msg()])->withInput();
        }

        // Support both flat questions list and wrapped questions array
        $questionsList = $data;
        if (is_array($data) && isset($data['questions']) && is_array($data['questions'])) {
            $questionsList = $data['questions'];
        }

        if (!is_array($questionsList)) {
            return back()->withErrors(['json_file' => 'JSON must contain an array of questions.'])->withInput();
        }

        // Validate each item (allowing legacy properties or universal properties)
        $validatedQuestions = [];
        foreach ($questionsList as $index => $item) {
            $validator = Validator::make($item, [
                'question_text' => 'required|string',
                'topic' => 'nullable|string',
                'question_type' => 'nullable|string',
                'option_a' => 'required_without:options|string',
                'option_b' => 'required_without:options|string',
                'options' => 'nullable|array',
                'correct_option' => 'required_without:correct_answers|string|max:5',
                'correct_answers' => 'nullable|array',
                'explanation' => 'nullable|string',
                'is_active' => 'nullable',
                'status' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $errors = implode(', ', $validator->errors()->all());
                return back()->withErrors(['json_file' => "Error at item index {$index}: {$errors}"])->withInput();
            }

            $validatedQuestions[] = $item;
        }

        if (empty($validatedQuestions)) {
            return back()->withErrors(['json_file' => 'No questions found in the JSON file.'])->withInput();
        }

        try {
            DB::transaction(function () use ($validatedQuestions, $exam) {
                foreach ($validatedQuestions as $qData) {
                    $qData['exam_id'] = $exam->id;
                    $qData['source_type'] = 'json_import';
                    $qData['source_reference'] = [
                        'filename' => request()->file('json_file')->getClientOriginalName(),
                        'import_batch_id' => uniqid('batch_'),
                    ];
                    
                    $universal = Question::convertToUniversalModel($qData);
                    Question::saveFromUniversalModel($universal);
                }
            });
        } catch (\Exception $e) {
            return back()->withErrors(['json_file' => 'Failed to save questions: ' . $e->getMessage()])->withInput();
        }

        return redirect()->route('admin.questions.index', ['exam_id' => $exam->id])
            ->with('success', count($validatedQuestions) . ' questions imported successfully.');
    }

    public function downloadSample()
    {
        $sampleData = [
            [
                'question_text' => 'Which cloud computing model offers cloud-based services like servers, storage, databases, and networking?',
                'option_a' => 'Infrastructure as a Service (IaaS)',
                'option_b' => 'Platform as a Service (PaaS)',
                'option_c' => 'Software as a Service (SaaS)',
                'option_d' => 'Functions as a Service (FaaS)',
                'correct_option' => 'A',
                'explanation' => 'IaaS provides fundamental compute, storage, and networking resources on demand.',
                'topic' => 'Cloud Concepts',
                'is_active' => true,
            ]
        ];

        return response()->json($sampleData, 200, [
            'Content-Disposition' => 'attachment; filename="sample-questions-import.json"',
        ], JSON_PRETTY_PRINT);
    }

    public function importHistory()
    {
        $histories = ImportHistory::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.questions.history', compact('histories'));
    }

    public function validateImport(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'json_file' => 'required|file',
        ]);

        $examId = (int)$request->exam_id;
        $file = $request->file('json_file');
        $filename = $file->getClientOriginalName();
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid JSON formatting: ' . json_last_error_msg()
            ], 422);
        }

        $questionsList = $data;
        if (is_array($data) && isset($data['questions']) && is_array($data['questions'])) {
            $questionsList = $data['questions'];
        }

        if (!is_array($questionsList)) {
            return response()->json([
                'success' => false,
                'message' => 'JSON must contain a list of questions.'
            ], 422);
        }

        $summary = [
            'filename' => $filename,
            'total' => count($questionsList),
            'valid_count' => 0,
            'warning_count' => 0,
            'error_count' => 0,
            'duplicate_count' => 0,
            'questions' => []
        ];

        foreach ($questionsList as $index => $item) {
            $errors = [];
            $warnings = [];
            
            $itemExamId = null;
            if (!empty($item['exam_id'])) {
                $itemExamId = (int)$item['exam_id'];
            } elseif (!empty($item['exam_code'])) {
                $resolved = Exam::where('exam_code', trim($item['exam_code']))->first();
                if ($resolved) {
                    $itemExamId = $resolved->id;
                } else {
                    $errors[] = "Exam code '" . $item['exam_code'] . "' could not be resolved to a valid exam.";
                }
            } else {
                $itemExamId = $examId;
            }

            $item['exam_id'] = $itemExamId;
            $universal = Question::convertToUniversalModel($item);

            if (empty($universal['exam_id'])) {
                $errors[] = 'Target exam is required or could not be resolved.';
            } else {
                $examExists = Exam::where('id', $universal['exam_id'])->exists();
                if (!$examExists) {
                    $errors[] = "Resolved Exam ID '{$universal['exam_id']}' does not exist in database.";
                }
            }

            if (empty($universal['question_text'])) {
                $errors[] = 'Question text is required.';
            }
            if (empty($universal['topic'])) {
                $warnings[] = 'Syllabus topic / chapter name is missing (defaults to General).';
                $universal['topic'] = 'General';
            }

            $hash = null;
            if (!empty($universal['question_text'])) {
                $normalized = preg_replace('/\s+/', ' ', strtolower(trim($universal['question_text'])));
                $hash = md5($normalized);
                
                $duplicateExists = Question::where('exam_id', $universal['exam_id'])->where('question_hash', $hash)->exists();
                if ($duplicateExists) {
                    $warnings[] = 'Possible Duplicate: An existing question matches this text in the database.';
                    $universal['is_duplicate'] = true;
                }
            }

            $type = $universal['question_type'];
            $supportedTypes = ['single_choice', 'multiple_choice', 'yes_no', 'drag_drop', 'matching', 'hotspot'];
            
            if (!in_array($type, $supportedTypes)) {
                $errors[] = "Question type '{$type}' is not supported.";
            } else {
                switch ($type) {
                    case 'single_choice':
                        if (count($universal['options']) < 2) {
                            $errors[] = 'Single choice questions must have at least 2 options.';
                        }
                        if (count($universal['correct_answers']) !== 1) {
                            $errors[] = 'Single choice questions must have exactly 1 correct answer choice.';
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
                    case 'matching':
                        if (count($universal['matching_pairs']) < 2) {
                            $errors[] = 'Matching questions must contain at least 2 matching pairs.';
                        }
                        foreach ($universal['matching_pairs'] as $pIdx => $pair) {
                            if (empty($pair['left']) || empty($pair['right'])) {
                                $errors[] = "Matching pair index {$pIdx} has empty left/right elements.";
                            }
                        }
                        break;
                    case 'hotspot':
                        if (count($universal['hotspot_answers']) < 1) {
                            $errors[] = 'Hotspot questions must contain at least 1 dropdown box configuration.';
                        }
                        foreach ($universal['hotspot_answers'] as $bIdx => $box) {
                            if (empty($box['id']) || empty($box['label'])) {
                                $errors[] = "Hotspot box index {$bIdx} has empty ID or label fields.";
                            }
                            if (empty($box['options']) || !is_array($box['options'])) {
                                $errors[] = "Hotspot box index {$bIdx} is missing dropdown choices list.";
                            } else {
                                if (empty($box['correct_answer'])) {
                                    $errors[] = "Hotspot box index {$bIdx} is missing a selected correct answer.";
                                } else {
                                    if (!in_array($box['correct_answer'], $box['options'])) {
                                        $errors[] = "Hotspot box correct answer '{$box['correct_answer']}' is not in dropdown options.";
                                    }
                                }
                            }
                        }
                        break;
                }
            }

            if (empty($universal['explanation'])) {
                $warnings[] = 'No answer explanation provided.';
            }

            if (!empty($universal['references'])) {
                foreach ($universal['references'] as $refIdx => $ref) {
                    if (empty($ref['title'])) {
                        $errors[] = "Reference link index {$refIdx} has empty title.";
                    }
                    if (!empty($ref['url']) && !filter_var($ref['url'], FILTER_VALIDATE_URL)) {
                        $warnings[] = "Reference URL '{$ref['url']}' is not a valid URL format.";
                    }
                }
            }

            $itemStatus = 'valid';
            if (count($errors) > 0) {
                $itemStatus = 'error';
                $summary['error_count']++;
            } elseif (isset($universal['is_duplicate'])) {
                $itemStatus = 'duplicate';
                $summary['duplicate_count']++;
                $summary['warning_count']++;
            } elseif (count($warnings) > 0) {
                $itemStatus = 'warning';
                $summary['warning_count']++;
            }

            if ($itemStatus === 'valid' || $itemStatus === 'warning') {
                $summary['valid_count']++;
            }

            $summary['questions'][] = [
                'index' => $index,
                'status' => $itemStatus,
                'errors' => $errors,
                'warnings' => $warnings,
                'original' => $item,
                'universal' => $universal
            ];
        }

        return response()->json([
            'success' => true,
            'summary' => $summary
        ]);
    }

    public function confirmImport(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'filename' => 'required|string',
            'questions' => 'required|array',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $questions = $request->questions;
        $importedCount = 0;

        try {
            DB::transaction(function () use ($questions, $exam, &$importedCount) {
                foreach ($questions as $qData) {
                    $qData['exam_id'] = $exam->id;
                    $qData['source_type'] = 'json_import';
                    
                    Question::saveFromUniversalModel($qData);
                    $importedCount++;
                }

                ImportHistory::create([
                    'filename' => request('filename'),
                    'source_type' => 'json_import',
                    'total_questions' => count($questions),
                    'imported_count' => $importedCount,
                    'error_count' => 0,
                    'user_id' => auth()->id(),
                    'status' => 'completed',
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error during save: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully imported {$importedCount} questions."
        ]);
    }
}
