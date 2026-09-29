<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\UserExam;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Storage;

class MyExamsController extends Controller
{
    /**
     * Display listing of purchased PDF guides.
     */
    public function index()
    {
        $purchasedExams = UserExam::where('user_id', auth()->id())
            ->where('access_type', 'pdf')
            ->with('exam.vendor')
            ->orderBy('purchased_at', 'desc')
            ->get();

        return view('dashboard.my-exams', compact('purchasedExams'));
    }

    /**
     * Generate secure R2 signed URL and increment download counts.
     */
    public function download(int $id)
    {
        $userExam = UserExam::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $exam = $userExam->exam;

        if (!$exam || !$exam->full_pdf_filename) {
            return back()->with('error', 'Exam PDF file is not available for download.');
        }

        $path = 'full/' . $exam->full_pdf_filename;

        $hasR2 = false;
        try {
            $hasR2 = Storage::disk('r2')->exists($path);
        } catch (\Throwable $e) {
            $hasR2 = false;
        }
        $hasLocal = Storage::disk('local')->exists($path);

        if (!$hasR2 && !$hasLocal) {
            \Illuminate\Support\Facades\Log::error("Purchased PDF file missing from storage: exam {$exam->exam_code}, user #" . auth()->id() . ", UserExam #{$userExam->id}");
            return back()->with('error', 'This study guide file is temporarily unavailable. Our team has been notified - please contact support.');
        }

        // Check download limits (only once we know the file actually exists, so a
        // missing-file error never burns one of the customer's 3 attempts)
        if (!$userExam->canDownload()) {
            ActivityLog::log(auth()->id(), 'download_blocked', "Exceeded max download attempts for {$exam->exam_code} PDF.");
            return back()->with('error', 'You have reached the maximum download limit (3 attempts) for this study guide. Please contact support to request additional downloads.');
        }

        // Increment download counter
        $userExam->increment('download_count');

        // Log action
        ActivityLog::log(auth()->id(), 'download_pdf', "Downloaded {$exam->exam_code} study guide PDF. Attempt: {$userExam->download_count}");

        $downloadFilename = $exam->exam_code . '-Study-Guide.pdf';

        // Prefer a short-lived signed R2 URL when available.
        if ($hasR2) {
            try {
                $downloadUrl = Storage::disk('r2')->temporaryUrl($path, now()->addMinutes(15));
                return redirect($downloadUrl);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::info('R2 signed URL generation failed, falling back to private local storage: ' . $e->getMessage());
            }
        }

        // Stream directly from the private "local" disk (storage/app/private, never web-served)
        // through this authenticated, ownership-checked action. No public-URL fallback - that
        // would let anyone with the filename bypass the purchase and download-limit checks above.
        return Storage::disk('local')->download($path, $downloadFilename);
    }
}
