<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Vendor;
use App\Models\Question;
use App\Models\Review;
use App\Models\Redirect;

use App\Services\ThemeManager;

class ExamController extends Controller
{
    /**
     * Display the specific exam page details (/exams/{vendor}/{slug}).
     */
    public function show(string $vendor, string $slug)
    {
        $vendorClean = strtolower(trim($vendor));
        $slugClean = strtolower(trim($slug));

        // 1. Strictly resolve Vendor
        $vendorModel = Vendor::where('slug', $vendorClean)
            ->where('is_active', true)
            ->first();

        if (!$vendorModel) {
            abort(404);
        }

        // 2. Query Exam strictly belonging to this vendor
        $exam = Exam::where('is_active', true)
            ->where('vendor_id', $vendorModel->id)
            ->where(function ($q) use ($slugClean) {
                $q->where('slug', $slugClean)
                  ->orWhere('exam_code', $slugClean);
            })
            ->with(['vendor', 'overlays'])
            ->first();

        if (!$exam) {
            abort(404);
        }

        // Verify catalog visibility via SiteContext service
        $siteContext = app(\App\Services\SiteContext::class);
        if (!$siteContext->isExamVisible($exam)) {
            abort(404);
        }
        $overlay = $siteContext->getOverlayForExam($exam);

        $canonicalVendor = $exam->vendor ? $exam->vendor->slug : $vendorModel->slug;
        $canonicalSlug = $exam->slug;

        // 3. Canonical 301 URL redirect if URL does not match canonical /exams/{vendor}/{slug}
        if ($vendor !== $canonicalVendor || $slug !== $canonicalSlug) {
            return redirect()->route('exams.show', [
                'vendor' => $canonicalVendor,
                'slug' => $canonicalSlug,
            ], 301);
        }

        // Get the first 10 well-formed single-choice questions for the free preview.
        // The A-D radio card UI only supports classic single-choice questions, so
        // drag-drop/hotspot/multi-select types (and rows with a stray non A-D answer
        // key) are excluded here - otherwise they render as blank/unanswerable cards.
        $sampleQuestions = Question::where('exam_id', $exam->id)
            ->where('is_active', true)
            ->where('question_type', 'single_choice')
            ->with(['options', 'answers'])
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->filter(function ($question) {
                return $question->answers->count() === 1
                    && in_array($question->answers->first()->answer_value, ['A', 'B', 'C', 'D'], true);
            })
            ->take(10)
            ->values();

        // Get approved customer reviews
        $reviews = Review::where('exam_id', $exam->id)
            ->where('is_approved', true)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return app(ThemeManager::class)->view('exams.show', compact('exam', 'sampleQuestions', 'reviews', 'overlay'));
    }
}

