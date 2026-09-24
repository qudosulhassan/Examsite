<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Exam;
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
        $exams = Exam::withCount('questions')->orderBy('exam_code')->get();

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
}
