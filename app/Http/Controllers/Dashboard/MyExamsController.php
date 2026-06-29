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

        // Check download limits
        if (!$userExam->canDownload()) {
            ActivityLog::log(auth()->id(), 'download_blocked', "Exceeded max download attempts for {$exam->exam_code} PDF.");
            return back()->with('error', 'You have reached the maximum download limit (3 attempts) for this study guide. Please contact support to request additional downloads.');
        }

        // Increment download counter
        $userExam->increment('download_count');

        // Log action
        ActivityLog::log(auth()->id(), 'download_pdf', "Downloaded {$exam->exam_code} study guide PDF. Attempt: {$userExam->download_count}");

        // Generate temporary URL from Cloudflare R2 bucket (expires in 15 minutes)
        try {
            $downloadUrl = Storage::disk('r2')->temporaryUrl(
                'full/' . $exam->full_pdf_filename,
                now()->addMinutes(15)
            );
        } catch (\Exception $e) {
            // Fallback mock download URL for local development if R2 config is placeholder
            $downloadUrl = url('/storage/full/' . $exam->full_pdf_filename);
        }

        return redirect($downloadUrl);
    }
}
