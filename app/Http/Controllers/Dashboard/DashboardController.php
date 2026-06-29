<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\UserExam;
use App\Models\Subscription;
use App\Models\TestAttempt;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    /**
     * Display the student portal overview dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        // Stats queries
        $purchasedCount = UserExam::where('user_id', $user->id)
            ->where('access_type', 'pdf')
            ->count();

        $activeSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        // Calculate total remaining downloads across all purchased guides
        $totalDownloadsUsed = UserExam::where('user_id', $user->id)
            ->where('access_type', 'pdf')
            ->sum('download_count');

        $maxAllowedDownloads = UserExam::where('user_id', $user->id)
            ->where('access_type', 'pdf')
            ->sum('max_downloads');

        $downloadsRemaining = max(0, $maxAllowedDownloads - $totalDownloadsUsed);

        $testsTakenCount = TestAttempt::where('user_id', $user->id)->count();

        // Activity log feed
        $activities = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'purchasedCount',
            'activeSubscription',
            'downloadsRemaining',
            'testsTakenCount',
            'activities'
        ));
    }
}
