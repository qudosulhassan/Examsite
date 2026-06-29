<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Subscription;
use App\Models\UserExam;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin panel dashboard statistics.
     */
    public function index()
    {
        // Revenue calculations
        $revenueToday = Order::where('payment_status', 'paid')
            ->whereDate('created_at', now()->toDateString())
            ->sum('total_amount');

        $revenueMonth = Order::where('payment_status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $revenueYear = Order::where('payment_status', 'paid')
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        // General counts
        $totalUsers = User::count();
        $newUsers30Days = User::where('created_at', '>=', now()->subDays(30))->count();
        $activeSubscriptions = Subscription::where('status', 'active')->count();

        // Top 10 selling exams (mocked query for simplicity or resolved via join)
        $topExams = DB::table('order_items')
            ->join('exams', 'order_items.exam_id', '=', 'exams.id')
            ->select('exams.exam_code', 'exams.exam_name', DB::raw('count(order_items.id) as sales_count'), DB::raw('sum(order_items.price) as total_revenue'))
            ->groupBy('exams.id', 'exams.exam_code', 'exams.exam_name')
            ->orderBy('sales_count', 'desc')
            ->limit(10)
            ->get();

        // Recent 10 orders
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'revenueToday',
            'revenueMonth',
            'revenueYear',
            'totalUsers',
            'newUsers30Days',
            'activeSubscriptions',
            'topExams',
            'recentOrders'
        ));
    }
}
