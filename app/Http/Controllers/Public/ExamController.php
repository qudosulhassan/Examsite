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

        // 1. Resolve Vendor
        $vendorModel = Vendor::where('slug', $vendorClean)
            ->orWhereRaw("LOWER(REPLACE(slug, '-', '')) = ?", [str_replace('-', '', $vendorClean)])
            ->orWhereRaw("LOWER(name) = ?", [$vendorClean])
            ->first();

        // 2. Query Exam
        $examQuery = Exam::where('is_active', true)->with('vendor');

        if ($vendorModel) {
            $examQuery->where('vendor_id', $vendorModel->id);
        }

        $exam = (clone $examQuery)->where(function ($q) use ($slugClean) {
            $q->where('slug', $slugClean)
              ->orWhere('exam_code', $slugClean)
              ->orWhere('exam_code', strtoupper($slugClean))
              ->orWhere('slug', strtolower($slugClean));
        })->first();

        if (!$exam && $vendorModel) {
            // Also try with vendor prefix (e.g. microsoft-az-104)
            $prefixedSlug = $vendorModel->slug . '-' . $slugClean;
            $exam = (clone $examQuery)->where(function ($q) use ($prefixedSlug) {
                $q->where('slug', $prefixedSlug)
                  ->orWhere('exam_code', strtoupper($prefixedSlug));
            })->first();
        }

        if (!$exam) {
            // Fallback: match without dashes
            $strippedSlug = str_replace('-', '', $slugClean);
            $exam = (clone $examQuery)->where(function ($q) use ($strippedSlug) {
                $q->whereRaw("LOWER(REPLACE(exam_code, '-', '')) = ?", [$strippedSlug])
                  ->orWhereRaw("LOWER(REPLACE(slug, '-', '')) = ?", [$strippedSlug]);
            })->first();
        }

        // Global fallback if vendor slug in URL did not match exam's actual vendor
        if (!$exam) {
            $exam = Exam::where('is_active', true)
                ->where(function ($q) use ($slugClean) {
                    $q->where('slug', $slugClean)
                      ->orWhere('exam_code', $slugClean)
                      ->orWhere('exam_code', strtoupper($slugClean));
                })
                ->with('vendor')
                ->first();
        }

        if (!$exam) {
            abort(404);
        }

        $canonicalVendor = $exam->vendor ? $exam->vendor->slug : 'exam';
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

    /**
     * Legacy single-slug redirect (/exams/{slug} -> /exams/{vendor}/{slug}).
     */
    public function legacyShow(string $slug)
    {
        $slugClean = strtolower(trim($slug));

        // Check if there is an explicit redirect record
        $redirect = Redirect::where('old_url', 'exams/' . $slugClean)
            ->orWhere('old_url', 'exams/' . $slug)
            ->first();

        if ($redirect) {
            return redirect(url($redirect->new_url), 301);
        }

        // Otherwise resolve exam by slug or code
        $exam = Exam::where('is_active', true)
            ->where(function ($q) use ($slugClean) {
                $q->where('slug', $slugClean)
                  ->orWhere('exam_code', $slugClean)
                  ->orWhere('exam_code', strtoupper($slugClean))
                  ->orWhere('slug', strtolower($slugClean));
            })
            ->with('vendor')
            ->first();

        if (!$exam) {
            $strippedSlug = str_replace('-', '', $slugClean);
            $exam = Exam::where('is_active', true)
                ->where(function ($q) use ($strippedSlug) {
                    $q->whereRaw("LOWER(REPLACE(exam_code, '-', '')) = ?", [$strippedSlug])
                      ->orWhereRaw("LOWER(REPLACE(slug, '-', '')) = ?", [$strippedSlug]);
                })
                ->with('vendor')
                ->first();
        }

        if (!$exam) {
            abort(404);
        }

        $vendorSlug = $exam->vendor ? $exam->vendor->slug : 'exam';

        return redirect()->route('exams.show', [
            'vendor' => $vendorSlug,
            'slug' => $exam->slug,
        ], 301);
    }
}
