<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\TestAttempt;
use App\Models\TestAnswer;
use App\Models\UserExam;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Exception;

class TestEngineController extends Controller
{
    /**
     * Display the list of exams the student has engine access to.
     */
    public function index()
    {
        $user = auth()->user();
        $activeSub = $user->subscriptions()->where('status', 'active')->first();

        if ($activeSub) {
            // Subscription has access to all active exams
            $exams = Exam::where('is_active', true)->with('vendor')->orderBy('exam_code')->get();
        } else {
            // Get explicitly purchased engine exams
            $purchasedIds = UserExam::where('user_id', $user->id)
                ->where('access_type', 'engine')
                ->pluck('exam_id')
                ->toArray();

            $exams = Exam::whereIn('id', $purchasedIds)
                ->where('is_active', true)
                ->with('vendor')
                ->orderBy('exam_code')
                ->get();
        }

        return view('dashboard.test-engine.index', compact('exams', 'activeSub'));
    }

    /**
     * Display the configuration lobby for a specific exam.
     */
    public function lobby(string $examSlug)
    {
        $exam = Exam::where('slug', $examSlug)->where('is_active', true)->firstOrFail();
        
        // Verify access permissions
        if (!$this->checkEngineAccess($exam->id)) {
            return redirect()->route('dashboard.test-engine')
                ->with('status', 'You do not have active Test Engine access for this exam. Please upgrade your subscription or purchase single engine access.');
        }

        // Get count of available questions
        $questionCount = Question::where('exam_id', $exam->id)->where('is_active', true)->count();

        // Get user's previous attempt for review mode check
        $lastAttempt = TestAttempt::where('user_id', auth()->id())
            ->where('exam_id', $exam->id)
            ->orderBy('created_at', 'desc')
            ->first();

        return view('dashboard.test-engine.lobby', compact('exam', 'questionCount', 'lastAttempt'));
    }

    /**
     * Create a new test attempt session and redirect.
     */
    public function startAttempt(Request $request, int $examId)
    {
        $exam = Exam::findOrFail($examId);
        
        if (!$this->checkEngineAccess($examId)) {
            return redirect()->route('dashboard.test-engine')->with('status', 'Access denied.');
        }

        $request->validate([
            'mode' => 'required|in:practice,exam,review',
            'count' => 'required|integer|min:5',
        ]);

        $mode = $request->mode;
        $count = $request->count;

        // Fetch questions
        $questionsQuery = Question::where('exam_id', $exam->id)->where('is_active', true);

        if ($mode === 'review') {
            // Review mode: fetch incorrect or flagged questions from user's last attempt
            $lastAttempt = TestAttempt::where('user_id', auth()->id())
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

        // Create TestAttempt
        $attempt = TestAttempt::create([
            'user_id' => auth()->id(),
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

        // Log action
        ActivityLog::log(auth()->id(), 'start_exam', "Started {$mode} attempt on {$exam->exam_code} ({$questions->count()} qs).");

        return redirect()->route('dashboard.test-engine.session', $attempt->id);
    }

    /**
     * Render the active timed exam session UI.
     */
    public function session(int $attemptId)
    {
        $attempt = TestAttempt::where('id', $attemptId)
            ->where('user_id', auth()->id())
            ->whereNull('completed_at')
            ->firstOrFail();

        $exam = Exam::findOrFail($attempt->exam_id);

        // Load all answers with their associated questions
        $answers = TestAnswer::where('attempt_id', $attempt->id)
            ->with('question')
            ->get();

        return view('dashboard.test-engine.session', compact('attempt', 'exam', 'answers'));
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
            ->where('user_id', auth()->id())
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
            if ($question->question_type === 'hotspot') {
                $userBoxes = is_array($selected) ? $selected : json_decode($selected, true);
                if (is_array($userBoxes)) {
                    $allMatch = true;
                    $qBoxes = $question->question_data['boxes'] ?? [];
                    foreach ($qBoxes as $bIdx => $b) {
                        $boxKey = 'box_' . ($bIdx + 1);
                        $userVal = $userBoxes[$boxKey] ?? ($userBoxes[$b['label'] ?? ''] ?? null);
                        if (trim((string)$userVal) !== trim((string)($b['correct_answer'] ?? ''))) {
                            $allMatch = false;
                            break;
                        }
                    }
                    $isCorrect = $allMatch && count($qBoxes) > 0;
                }
            } else {
                $selectedArr = array_map('trim', explode(',', $selected));
                $correctArr = array_map('trim', explode(',', $question->correct_option));
                sort($selectedArr);
                sort($correctArr);
                $isCorrect = ($selectedArr === $correctArr);
            }
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
            'is_correct' => $attempt->mode === 'exam' ? null : $isCorrect,
            'correct_option' => $attempt->mode === 'exam' ? null : $question->correct_option,
            'explanation' => $attempt->mode === 'exam' ? null : $question->explanation,
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
                $a->where('user_id', auth()->id())->whereNull('completed_at');
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
            ->where('user_id', auth()->id())
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

        // Log action
        ActivityLog::log(auth()->id(), 'submit_exam', "Submitted {$attempt->mode} attempt on {$exam->exam_code}. Scored: " . number_format($percentage, 2) . "% (" . ($passed ? 'PASSED' : 'FAILED') . ")");

        return redirect()->route('dashboard.test-engine.results', $attempt->id);
    }

    /**
     * Display the score results of a completed attempt.
     */
    public function results(int $attemptId)
    {
        $attempt = TestAttempt::where('id', $attemptId)
            ->where('user_id', auth()->id())
            ->whereNotNull('completed_at')
            ->firstOrFail();

        $exam = Exam::findOrFail($attempt->exam_id);

        // Load all graded questions
        $answers = TestAnswer::where('attempt_id', $attempt->id)
            ->with('question')
            ->get();

        return view('dashboard.test-engine.results', compact('attempt', 'exam', 'answers'));
    }

    /**
     * Helper to verify user permission access to Test Engine.
     */
    private function checkEngineAccess(int $examId): bool
    {
        $user = auth()->user();
        
        // Subscription check
        if ($user->subscriptions()->where('status', 'active')->exists()) {
            return true;
        }

        // Single exam purchase check
        return UserExam::where('user_id', $user->id)
            ->where('exam_id', $examId)
            ->where('access_type', 'engine')
            ->exists();
    }
}
