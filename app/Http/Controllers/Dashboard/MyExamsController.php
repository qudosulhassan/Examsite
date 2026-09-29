<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\User;
use App\Models\UserExam;
use App\Models\ActivityLog;
use App\Services\AccessManager;
use Illuminate\Support\Facades\Storage;

class MyExamsController extends Controller
{
    /**
     * Display listing of purchased PDF guides.
     */
    public function index()
    {
        $user = auth()->user();

        $this->materializePackagePdfAccess($user);

        $purchasedExams = UserExam::where('user_id', $user->id)
            ->where('access_type', 'pdf')
            ->with('exam.vendor')
            ->orderBy('purchased_at', 'desc')
            ->get();

        return view('dashboard.my-exams', compact('purchasedExams'));
    }

    /**
     * A package purchase (e.g. "Microsoft Ultimate Package") only creates a
     * UserPackage row, not per-exam UserExam rows. This list, and the download
     * route it links to, are both built around UserExam - so for a user with
     * active PDF-enabled package/subscription access, create the missing
     * UserExam rows for exams they're now entitled to but haven't touched yet.
     * This is a no-op query for the vast majority of users who never bought a
     * package (single early exists() check), and idempotent for repeat visits.
     */
    protected function materializePackagePdfAccess(User $user): void
    {
        $hasBroadAccess = $user->subscriptions()->where('status', 'active')->exists()
            || $user->userPackages()
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->whereHas('package', fn ($q) => $q->where('includes_pdf', true))
                ->exists();

        if (!$hasBroadAccess) {
            return;
        }

        $examIds = app(AccessManager::class)->getAccessibleExamIds($user, 'pdf');
        $examIds = $examIds ?? Exam::where('is_active', true)->pluck('id')->all();

        if (empty($examIds)) {
            return;
        }

        $existingIds = UserExam::where('user_id', $user->id)
            ->where('access_type', 'pdf')
            ->whereIn('exam_id', $examIds)
            ->pluck('exam_id')
            ->all();

        foreach (array_diff($examIds, $existingIds) as $examId) {
            UserExam::create([
                'user_id' => $user->id,
                'exam_id' => $examId,
                'access_type' => 'pdf',
                'download_count' => 0,
                'max_downloads' => 3,
                'purchased_at' => now(),
            ]);
        }
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
