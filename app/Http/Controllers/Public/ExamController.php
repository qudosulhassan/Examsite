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
        $exam = Exam::where('is_active', true)
            ->where(function($q) use ($slug) {
                $q->where('slug', $slug)
                  ->orWhere('exam_code', $slug)
                  ->orWhere('exam_code', strtoupper($slug))
                  ->orWhere('slug', strtolower($slug));
            })
            ->with('vendor')
            ->first();

        if (!$exam) {
            $slugClean = str_replace('-', '', strtolower($slug));
            $exam = Exam::where('is_active', true)
                ->where(function($q) use ($slugClean) {
                    $q->whereRaw("LOWER(REPLACE(exam_code, '-', '')) = ?", [$slugClean])
                      ->orWhereRaw("LOWER(REPLACE(slug, '-', '')) = ?", [$slugClean]);
                })
                ->with('vendor')
                ->first();
        }

        if (!$exam) {
            abort(404);
        }

        // Canonical URL redirect if slug doesn't match official slug
        if ($slug !== $exam->slug) {
            return redirect()->route('exams.show', $exam->slug, 301);
        }

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
