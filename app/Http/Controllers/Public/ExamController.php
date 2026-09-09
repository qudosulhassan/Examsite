<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Vendor;
use App\Models\Question;
use App\Models\Review;
use App\Models\Redirect;

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
            ->with('vendor')
            ->first();

        if (!$exam) {
            abort(404);
        }

        $canonicalVendor = $exam->vendor ? $exam->vendor->slug : $vendorModel->slug;
        $canonicalSlug = $exam->slug;

        // 3. Canonical 301 URL redirect if URL does not match canonical /exams/{vendor}/{slug}
        if ($vendor !== $canonicalVendor || $slug !== $canonicalSlug) {
            return redirect()->route('exams.show', [
                'vendor' => $canonicalVendor,
                'slug' => $canonicalSlug,
            ], 301);
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

