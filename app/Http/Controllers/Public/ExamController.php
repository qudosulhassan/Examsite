<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Review;

class ExamController extends Controller
{
    /**
     * Display the specific exam page details.
     */
    public function show(string $slug)
    {
        $exam = Exam::where('slug', $slug)->where('is_active', true)->with('vendor')->firstOrFail();

        // Get first 3 questions for preview without correct answers or explanations exposed
        $sampleQuestions = Question::where('exam_id', $exam->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->limit(3)
            ->get();

        // Get approved customer reviews
        $reviews = Review::where('exam_id', $exam->id)
            ->where('is_approved', true)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.exams.show', compact('exam', 'sampleQuestions', 'reviews'));
    }
}
