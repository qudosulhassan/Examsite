<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Exam;
use Illuminate\Http\Request;

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
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'correct_option' => 'required|string|max:5',
        ]);

        Question::create($request->all());

        return redirect()->route('admin.questions.index', ['exam_id' => $request->exam_id])
            ->with('success', 'Question created successfully.');
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

        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'correct_option' => 'required|string|max:5',
        ]);

        $question->update($request->all());

        return redirect()->route('admin.questions.index', ['exam_id' => $request->exam_id])
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(int $id)
    {
        $question = Question::findOrFail($id);
        $examId = $question->exam_id;
        $question->delete();
        
        return redirect()->route('admin.questions.index', ['exam_id' => $examId])
            ->with('success', 'Question deleted successfully.');
    }
}
