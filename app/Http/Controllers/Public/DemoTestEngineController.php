<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\TestAnswer;
use Illuminate\Http\Request;

class DemoTestEngineController extends Controller
{
    /**
     * Display the configuration lobby for a specific exam.
     */
    public function lobby(string $examSlug)
    {
        $exam = Exam::where('slug', $examSlug)->where('is_active', true)->firstOrFail();
        
        // Demo allows exactly 10 questions max, or whatever is available if < 10
        $availableCount = Question::where('exam_id', $exam->id)->where('is_active', true)->count();
        $questionCount = min($availableCount, 10);

        // Get guest's previous attempt for review mode check
        $lastAttempt = TestAttempt::where('session_id', session()->getId())
            ->where('exam_id', $exam->id)
            ->orderBy('created_at', 'desc')
            ->first();

        return view('pages.demo-test-engine.lobby', compact('exam', 'questionCount', 'lastAttempt'));
    }

    /**
     * Create a new test attempt session and redirect.
     */
    public function startAttempt(Request $request, int $examId)
    {
        $exam = Exam::findOrFail($examId);
        
        $request->validate([
            'mode' => 'required|in:practice,exam,review',
            'count' => 'required|integer|max:10', // Demo limit
        ]);

        $mode = $request->mode;
        // Enforce demo limit regardless of what was submitted
        $count = min($request->count, 10);

        // Fetch questions
        $questionsQuery = Question::where('exam_id', $exam->id)->where('is_active', true);

        if ($mode === 'review') {
            // Review mode: fetch incorrect or flagged questions from user's last attempt
            $lastAttempt = TestAttempt::where('session_id', session()->getId())
                ->where('exam_id', $exam->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastAttempt) {
                $questionIds = TestAnswer::where('attempt_id', $lastAttempt->id)
                    ->where(function($q) {
                        $q->where('is_correct', false)
                          ->orWhere('is_flagged', true);
                    })
                    ->pluck('question_id')
                    ->toArray();

                $questionsQuery->whereIn('id', $questionIds);
            }
        }

        // Fetch random questions up to count limit
        $questions = $questionsQuery->inRandomOrder()->limit($count)->get();

        if ($questions->isEmpty()) {
            return back()->with('status', 'No questions available for this exam attempt parameters.');
        }

        // Create TestAttempt (Guest)
        $attempt = TestAttempt::create([
            'user_id' => auth()->check() ? auth()->id() : null, // Support if logged in too, but primarily guest
            'session_id' => session()->getId(),
            'exam_id' => $exam->id,
            'mode' => $mode,
            'total_questions' => $questions->count(),
            'answered' => 0,
            'correct' => 0,
            'skipped' => 0,
            'score_percentage' => 0.00,
            'passed' => false,
            'time_taken_seconds' => 0,
        ]);

        // Create answer placeholders
        foreach ($questions as $question) {
            TestAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'selected_option' => null,
                'is_correct' => false,
                'is_flagged' => false,
            ]);
        }

        return redirect()->route('public.demo-test-engine.session', $attempt->id);
    }

    /**
     * Render the active timed exam session UI.
     */
    public function session(int $attemptId)
    {
        $attempt = TestAttempt::where('id', $attemptId)
            ->where('session_id', session()->getId())
            ->whereNull('completed_at')
            ->firstOrFail();

        $exam = Exam::findOrFail($attempt->exam_id);

        // Load all answers with their associated questions
        $answers = TestAnswer::where('attempt_id', $attempt->id)
            ->with('question')
            ->get();

        return view('pages.demo-test-engine.session', compact('attempt', 'exam', 'answers'));
    }

    /**
     * Save answer choice from frontend (AJAX).
     */
    public function saveAnswerAjax(Request $request)
    {
        $request->validate([
            'attempt_id' => 'required|exists:test_attempts,id',
            'question_id' => 'required|exists:questions,id',
            'selected_option' => 'nullable|string',
            'time_spent' => 'nullable|integer',
        ]);

        $attempt = TestAttempt::where('id', $request->attempt_id)
            ->where('session_id', session()->getId())
            ->whereNull('completed_at')
            ->first();

        if (!$attempt) {
            return response()->json(['error' => 'Invalid attempt session.'], 403);
        }

        $answer = TestAnswer::where('attempt_id', $attempt->id)
            ->where('question_id', $request->question_id)
            ->first();

        if (!$answer) {
            return response()->json(['error' => 'Question answer placeholder not found.'], 404);
        }

        $question = Question::findOrFail($request->question_id);
        $selected = $request->selected_option;

        // Check correctness (trim arrays to handle multi-select spacing)
        $isCorrect = false;
        if (!empty($selected)) {
            $selectedArr = array_map('trim', explode(',', $selected));
            $correctArr = array_map('trim', explode(',', $question->correct_option));
            sort($selectedArr);
            sort($correctArr);
            $isCorrect = ($selectedArr === $correctArr);
        }

        // Save progress
        $answer->update([
            'selected_option' => $selected,
            'is_correct' => $isCorrect,
            'time_spent_seconds' => $answer->time_spent_seconds + ($request->time_spent ?? 0),
        ]);

        // Auto calculate running metrics
        $answeredCount = TestAnswer::where('attempt_id', $attempt->id)->whereNotNull('selected_option')->count();
        $attempt->update([
            'answered' => $answeredCount,
        ]);

        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'correct_option' => $question->correct_option,
            'explanation' => $question->explanation,
        ]);
    }

    /**
     * Toggle flag on question (AJAX).
     */
    public function toggleFlagAjax(Request $request)
    {
        $request->validate([
            'attempt_id' => 'required|exists:test_attempts,id',
            'question_id' => 'required|exists:questions,id',
        ]);

        $answer = TestAnswer::where('attempt_id', $request->attempt_id)
            ->whereHas('attempt', function($a) {
                $a->where('session_id', session()->getId())->whereNull('completed_at');
            })
            ->where('question_id', $request->question_id)
            ->firstOrFail();

        $answer->update([
            'is_flagged' => !$answer->is_flagged,
        ]);

        return response()->json(['success' => true, 'is_flagged' => $answer->is_flagged]);
    }

    /**
     * Submit and grade the exam attempt session.
     */
    public function submitAttempt(Request $request, int $attemptId)
    {
        $attempt = TestAttempt::where('id', $attemptId)
            ->where('session_id', session()->getId())
            ->whereNull('completed_at')
            ->firstOrFail();

        $exam = Exam::findOrFail($attempt->exam_id);

        // Grade all answers
        $answers = TestAnswer::where('attempt_id', $attempt->id)->get();
        
        $total = $answers->count();
        $correct = $answers->where('is_correct', true)->count();
        $answered = $answers->whereNotNull('selected_option')->count();
        $skipped = $total - $answered;
        
        $percentage = ($total > 0) ? ($correct / $total) * 100 : 0.00;
        $passed = ($percentage >= $exam->passing_score);

        // Update attempt
        $attempt->update([
            'answered' => $answered,
            'correct' => $correct,
            'skipped' => $skipped,
            'score_percentage' => $percentage,
            'passed' => $passed,
            'time_taken_seconds' => $request->time_taken ?? 0,
            'completed_at' => now(),
        ]);

        return redirect()->route('public.demo-test-engine.results', $attempt->id);
    }

    /**
     * Display the score results of a completed attempt.
     */
    public function results(int $attemptId)
    {
        $attempt = TestAttempt::where('id', $attemptId)
            ->where('session_id', session()->getId())
            ->whereNotNull('completed_at')
            ->firstOrFail();

        $exam = Exam::findOrFail($attempt->exam_id);

        // Load all graded questions
        $answers = TestAnswer::where('attempt_id', $attempt->id)
            ->with('question')
            ->get();

        return view('pages.demo-test-engine.results', compact('attempt', 'exam', 'answers'));
    }
}
