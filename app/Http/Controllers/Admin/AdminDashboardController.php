<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Refund;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Vendor;
use App\Models\Coupon;
use App\Models\OrderTimeline;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * Display the upgraded enterprise SaaS admin dashboard.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Date Range Filter Determination
        $dateRange = $request->get('date_range', '30days');
        $customFrom = $request->get('date_from');
        $customTo = $request->get('date_to');

        [$startDate, $endDate, $prevStartDate, $prevEndDate, $rangeLabel] = $this->resolveDateRanges(
            $dateRange,
            $customFrom,
            $customTo
        );

        // 2. Permission Flags (Server-Side Enforcement)
        $canViewFinance = $user && ($user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->can('view-orders') || $user->can('view-reports'));
        $canViewUsers = $user && ($user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->can('view-users'));
        $canViewContent = $user && ($user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->can('view-exams') || $user->can('view-questions'));

        // 3. Key Performance Indicators (Current vs Previous Period)
        $kpi = [];

        // A. Net Revenue
        if ($canViewFinance) {
            $currGross = (float)Order::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('payment_status', ['paid', 'completed', 'partially_refunded', 'refunded'])
                ->sum('total_amount');
            $currRefunds = (float)Order::whereBetween('created_at', [$startDate, $endDate])->sum('refunded_amount')
                + (float)Refund::whereBetween('created_at', [$startDate, $endDate])->where('status', 'completed')->sum('amount');
            $currNetRevenue = max(0.0, $currGross - $currRefunds);

            $prevGross = (float)Order::whereBetween('created_at', [$prevStartDate, $prevEndDate])
                ->whereIn('payment_status', ['paid', 'completed', 'partially_refunded', 'refunded'])
                ->sum('total_amount');
            $prevRefunds = (float)Order::whereBetween('created_at', [$prevStartDate, $prevEndDate])->sum('refunded_amount')
                + (float)Refund::whereBetween('created_at', [$prevStartDate, $prevEndDate])->where('status', 'completed')->sum('amount');
            $prevNetRevenue = max(0.0, $prevGross - $prevRefunds);

            $revenueChange = $this->calculatePercentageChange($currNetRevenue, $prevNetRevenue);

            // B. Orders
            $currOrdersCount = Order::whereBetween('created_at', [$startDate, $endDate])->count();
            $prevOrdersCount = Order::whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();
            $ordersChange = $this->calculatePercentageChange($currOrdersCount, $prevOrdersCount);

            // Lifetime Revenue fallback for summary
            $lifetimeRevenue = (float)Order::whereIn('payment_status', ['paid', 'completed'])->sum('total_amount');
            $totalOrdersAllTime = Order::count();
        } else {
            $currNetRevenue = 0;
            $revenueChange = null;
            $currOrdersCount = 0;
            $ordersChange = null;
            $lifetimeRevenue = 0;
            $totalOrdersAllTime = 0;
        }

        // C. Users
        $totalUsers = User::count();
        $currNewUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $prevNewUsers = User::whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();
        $usersChange = $this->calculatePercentageChange($currNewUsers, $prevNewUsers);

        // D. Active Subscriptions
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $currSubsCreated = Subscription::whereBetween('created_at', [$startDate, $endDate])->count();
        $prevSubsCreated = Subscription::whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();
        $subsChange = $this->calculatePercentageChange($currSubsCreated, $prevSubsCreated);

        // E. Content (Exams & Questions)
        $totalExams = Exam::count();
        $activeExams = Exam::where('is_active', 1)->count();
        $totalQuestions = Question::count();
        $questionsWithExplanation = Question::whereNotNull('explanation')->where('explanation', '!=', '')->count();
        $questionsWithCorrectOption = Question::whereNotNull('correct_option')->where('correct_option', '!=', '')->count();
        $questionsWithImages = Question::whereNotNull('image_filename')->where('image_filename', '!=', '')->count();

        // 4. Revenue & Orders Overview Timeseries Chart
        $chartPeriod = $request->get('chart_period', '30days');
        $chartMetric = $request->get('chart_metric', 'revenue'); // 'revenue' or 'orders'
        $chartData = $this->buildTimeseriesChartData($chartPeriod, $canViewFinance);

        // 5. Sales Performance & Order Status Distribution
        if ($canViewFinance) {
            $totalPaidCount = Order::whereIn('payment_status', ['paid', 'completed'])->count();
            $totalPendingCount = Order::whereIn('payment_status', ['pending', 'processing'])->count();
            $totalRefundedCount = Order::whereIn('payment_status', ['refunded', 'partially_refunded'])->count();
            $totalFailedCount = Order::whereIn('payment_status', ['failed', 'cancelled'])->count();

            $totalFilteredOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
            $filteredPaidOrders = Order::whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('payment_status', ['paid', 'completed'])
                ->get();
            $filteredPaidCount = $filteredPaidOrders->count();
            $filteredRevenue = (float)$filteredPaidOrders->sum('total_amount');
            $averageOrderValue = $filteredPaidCount > 0 ? round($filteredRevenue / $filteredPaidCount, 2) : 0.00;

            // Status Distribution Percentages
            $orderDistribution = [
                'paid' => [
                    'label' => 'Paid / Completed',
                    'count' => $totalPaidCount,
                    'percentage' => $totalOrdersAllTime > 0 ? round(($totalPaidCount / $totalOrdersAllTime) * 100, 1) : 0,
                    'color' => '#00D4AA',
                    'bg' => 'bg-cyan',
                ],
                'pending' => [
                    'label' => 'Pending / Processing',
                    'count' => $totalPendingCount,
                    'percentage' => $totalOrdersAllTime > 0 ? round(($totalPendingCount / $totalOrdersAllTime) * 100, 1) : 0,
                    'color' => '#F59E0B',
                    'bg' => 'bg-amber-500',
                ],
                'refunded' => [
                    'label' => 'Refunded',
                    'count' => $totalRefundedCount,
                    'percentage' => $totalOrdersAllTime > 0 ? round(($totalRefundedCount / $totalOrdersAllTime) * 100, 1) : 0,
                    'color' => '#8B5CF6',
                    'bg' => 'bg-purple-500',
                ],
                'failed' => [
                    'label' => 'Cancelled / Failed',
                    'count' => $totalFailedCount,
                    'percentage' => $totalOrdersAllTime > 0 ? round(($totalFailedCount / $totalOrdersAllTime) * 100, 1) : 0,
                    'color' => '#EF4444',
                    'bg' => 'bg-rose-500',
                ],
            ];
        } else {
            $totalPaidCount = $totalPendingCount = $totalRefundedCount = $totalFailedCount = 0;
            $averageOrderValue = 0.00;
            $orderDistribution = [];
        }

        // 6. Recent Orders (Latest 8-10 Orders)
        $recentOrders = $canViewFinance
            ? Order::with(['user', 'items.exam'])
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get()
            : collect();

        // 7. Top Selling Exams & Top Vendors
        $topSellingExams = DB::table('order_items')
            ->join('exams', 'order_items.exam_id', '=', 'exams.id')
            ->leftJoin('vendors', 'exams.vendor_id', '=', 'vendors.id')
            ->select(
                'exams.id',
                'exams.exam_code',
                'exams.exam_name',
                'vendors.name as vendor_name',
                DB::raw('count(order_items.id) as sales_count'),
                DB::raw('sum(order_items.price) as total_revenue')
            )
            ->groupBy('exams.id', 'exams.exam_code', 'exams.exam_name', 'vendors.name')
            ->orderBy('sales_count', 'desc')
            ->limit(5)
            ->get();

        $topVendors = DB::table('order_items')
            ->join('exams', 'order_items.exam_id', '=', 'exams.id')
            ->join('vendors', 'exams.vendor_id', '=', 'vendors.id')
            ->select(
                'vendors.id',
                'vendors.name',
                DB::raw('count(order_items.id) as sales_count'),
                DB::raw('sum(order_items.price) as total_revenue'),
                DB::raw('count(distinct exams.id) as exams_sold')
            )
            ->groupBy('vendors.id', 'vendors.name')
            ->orderBy('sales_count', 'desc')
            ->limit(5)
            ->get();

        // Fallback for top vendors if order_items has few vendors: augment with top vendors by exam count
        if ($topVendors->count() < 3) {
            $existingVendorIds = $topVendors->pluck('id')->toArray();
            $catalogVendors = Vendor::withCount('exams')
                ->whereNotIn('id', $existingVendorIds)
                ->orderBy('exams_count', 'desc')
                ->limit(5 - $topVendors->count())
                ->get();
        } else {
            $catalogVendors = collect();
        }

        // 8. User Overview (Role Breakdown & Status)
        $userStatusCounts = [
            'active' => User::where('status', 'active')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
            'deactivated' => User::where('status', 'deactivated')->count(),
        ];
        $studentsCount = User::role('Student')->count();
        $adminStaffCount = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Super Admin', 'Admin', 'Staff', 'Moderator']);
        })->count();

        // 9. Subscription Lifecycle Breakdown
        $subscriptionBreakdown = [
            'active' => Subscription::where('status', 'active')->count(),
            'trial' => Subscription::where('status', 'trial')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
            'cancelled' => Subscription::where('status', 'cancelled')->count(),
            'pending' => Subscription::where('status', 'pending')->count(),
        ];

        // 10. Content: Recently Updated Exams
        $recentlyUpdatedExams = Exam::with('vendor')
            ->withCount('questions')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // 11. Payment Gateway Revenue Distribution
        $paymentMethodsAnalytics = [];
        if ($canViewFinance) {
            $gateways = Order::select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as revenue'))
                ->whereIn('payment_status', ['paid', 'completed'])
                ->groupBy('payment_method')
                ->get();

            $totalGatewayRevenue = (float)$gateways->sum('revenue');
            foreach ($gateways as $gw) {
                $rev = (float)$gw->revenue;
                $pct = $totalGatewayRevenue > 0 ? round(($rev / $totalGatewayRevenue) * 100, 1) : 0;
                $paymentMethodsAnalytics[] = [
                    'name' => strtoupper($gw->payment_method ?: 'Direct'),
                    'count' => $gw->count,
                    'revenue' => $rev,
                    'percentage' => $pct,
                ];
            }
        }

        // 12. Recent Customers (Latest 5 Registered Users)
        $recentCustomers = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 13. System Alerts & Warnings (Real Actionable Conditions)
        $systemAlerts = [];
        if ($canViewFinance) {
            if ($totalFailedCount > 0) {
                $systemAlerts[] = [
                    'type' => 'error',
                    'title' => "{$totalFailedCount} Failed / Cancelled Orders",
                    'description' => 'Payment attempts failed or orders voided. Inspect gateway logs.',
                    'action_url' => route('admin.orders.index', ['status' => 'failed']),
                    'action_label' => 'Inspect Orders',
                ];
            }
            if ($totalPendingCount > 0) {
                $systemAlerts[] = [
                    'type' => 'warning',
                    'title' => "{$totalPendingCount} Pending Orders Awaiting Capture",
                    'description' => 'Transactions awaiting settlement or customer checkout completion.',
                    'action_url' => route('admin.orders.index', ['status' => 'pending']),
                    'action_label' => 'Review Pending',
                ];
            }
        }

        // Content Alerts
        $examsWithoutQuestions = Exam::where('is_active', 1)->doesntHave('questions')->count();
        if ($examsWithoutQuestions > 0) {
            $systemAlerts[] = [
                'type' => 'info',
                'title' => "{$examsWithoutQuestions} Active Exams Have 0 Questions",
                'description' => 'Active exams are published but contain no questions for candidates.',
                'action_url' => route('admin.exams.index'),
                'action_label' => 'Manage Exams',
            ];
        }

        $missingExplanations = Question::where(function($q) {
            $q->whereNull('explanation')->orWhere('explanation', '');
        })->count();
        if ($missingExplanations > 0) {
            $systemAlerts[] = [
                'type' => 'info',
                'title' => "{$missingExplanations} Questions Missing Explanations",
                'description' => 'Adding explanations increases candidate satisfaction and pass rates.',
                'action_url' => route('admin.questions.index'),
                'action_label' => 'Review Questions',
            ];
        }

        // 14. Real Recent Activity Stream (Order Timelines & User Signups)
        $recentActivities = $this->buildActivityFeed();

        // 15. Dynamic Greeting
        $hour = now()->hour;
        $greeting = match(true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        // If AJAX request (e.g. Refresh Data), return JSON payload
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'kpi' => [
                    'netRevenue' => number_format($currNetRevenue, 2),
                    'revenueChange' => $revenueChange,
                    'ordersCount' => number_format($currOrdersCount),
                    'ordersChange' => $ordersChange,
                    'usersCount' => number_format($totalUsers),
                    'usersChange' => $usersChange,
                    'activeSubscriptions' => number_format($activeSubscriptions),
                    'subsChange' => $subsChange,
                    'activeExams' => number_format($activeExams),
                    'totalQuestions' => number_format($totalQuestions),
                ],
                'chart' => $chartData,
                'updated_at' => now()->format('h:i:s A'),
            ]);
        }

        return view('admin.dashboard', compact(
            'greeting',
            'user',
            'dateRange',
            'rangeLabel',
            'startDate',
            'endDate',
            'canViewFinance',
            'canViewUsers',
            'canViewContent',
            // KPIs
            'currNetRevenue',
            'revenueChange',
            'currOrdersCount',
            'ordersChange',
            'totalOrdersAllTime',
            'lifetimeRevenue',
            'totalUsers',
            'currNewUsers',
            'usersChange',
            'activeSubscriptions',
            'subsChange',
            'activeExams',
            'totalExams',
            'totalQuestions',
            'questionsWithExplanation',
            'questionsWithCorrectOption',
            'questionsWithImages',
            // Chart & Performance
            'chartPeriod',
            'chartMetric',
            'chartData',
            'averageOrderValue',
            'totalPaidCount',
            'totalPendingCount',
            'totalRefundedCount',
            'totalFailedCount',
            'orderDistribution',
            // Tables & Feeds
            'recentOrders',
            'topSellingExams',
            'topVendors',
            'catalogVendors',
            'userStatusCounts',
            'studentsCount',
            'adminStaffCount',
            'subscriptionBreakdown',
            'recentlyUpdatedExams',
            'paymentMethodsAnalytics',
            'recentCustomers',
            'systemAlerts',
            'recentActivities'
        ));
    }

    /**
     * Resolve start, end, and prior comparison date ranges.
     */
    protected function resolveDateRanges(string $preset, ?string $customFrom, ?string $customTo): array
    {
        $now = now();

        switch ($preset) {
            case 'today':
                $start = today()->startOfDay();
                $end = today()->endOfDay();
                $prevStart = today()->subDay()->startOfDay();
                $prevEnd = today()->subDay()->endOfDay();
                $label = 'Today';
                break;
            case 'yesterday':
                $start = today()->subDay()->startOfDay();
                $end = today()->subDay()->endOfDay();
                $prevStart = today()->subDays(2)->startOfDay();
                $prevEnd = today()->subDays(2)->endOfDay();
                $label = 'Yesterday';
                break;
            case '7days':
                $start = now()->subDays(6)->startOfDay();
                $end = now()->endOfDay();
                $prevStart = now()->subDays(13)->startOfDay();
                $prevEnd = now()->subDays(7)->endOfDay();
                $label = 'Last 7 Days';
                break;
            case '30days':
                $start = now()->subDays(29)->startOfDay();
                $end = now()->endOfDay();
                $prevStart = now()->subDays(59)->startOfDay();
                $prevEnd = now()->subDays(30)->endOfDay();
                $label = 'Last 30 Days';
                break;
            case 'this_month':
                $start = now()->startOfMonth();
                $end = now()->endOfDay();
                $prevStart = now()->subMonth()->startOfMonth();
                $prevEnd = now()->subMonth()->endOfMonth();
                $label = 'This Month (' . now()->format('M Y') . ')';
                break;
            case 'last_month':
                $start = now()->subMonth()->startOfMonth();
                $end = now()->subMonth()->endOfMonth();
                $prevStart = now()->subMonths(2)->startOfMonth();
                $prevEnd = now()->subMonths(2)->endOfMonth();
                $label = 'Last Month (' . now()->subMonth()->format('M Y') . ')';
                break;
            case 'this_year':
                $start = now()->startOfYear();
                $end = now()->endOfDay();
                $prevStart = now()->subYear()->startOfYear();
                $prevEnd = now()->subYear()->endOfYear();
                $label = 'This Year (' . now()->year . ')';
                break;
            case 'custom':
                $start = $customFrom ? Carbon::parse($customFrom)->startOfDay() : now()->subDays(29)->startOfDay();
                $end = $customTo ? Carbon::parse($customTo)->endOfDay() : now()->endOfDay();
                $durationDays = max(1, $start->diffInDays($end));
                $prevStart = (clone $start)->subDays($durationDays + 1)->startOfDay();
                $prevEnd = (clone $start)->subDay()->endOfDay();
                $label = $start->format('M d, Y') . ' — ' . $end->format('M d, Y');
                break;
            default:
                $start = now()->subDays(29)->startOfDay();
                $end = now()->endOfDay();
                $prevStart = now()->subDays(59)->startOfDay();
                $prevEnd = now()->subDays(30)->endOfDay();
                $label = 'Last 30 Days';
                break;
        }

        return [$start, $end, $prevStart, $prevEnd, $label];
    }

    /**
     * Compute safe percentage delta between current and previous numbers.
     */
    protected function calculatePercentageChange(float|int $current, float|int $previous): ?array
    {
        if ($previous == 0) {
            if ($current > 0) {
                return ['direction' => 'up', 'value' => 100.0, 'formatted' => '+100%'];
            }
            return null; // Omit if no historical comparison is possible
        }

        $diff = $current - $previous;
        $pct = round(($diff / $previous) * 100, 1);

        return [
            'direction' => $pct >= 0 ? 'up' : 'down',
            'value' => abs($pct),
            'formatted' => ($pct >= 0 ? '+' : '') . $pct . '%',
        ];
    }

    /**
     * Build time series points for revenue and orders volume charts.
     */
    protected function buildTimeseriesChartData(string $period, bool $canViewFinance): array
    {
        if (!$canViewFinance) {
            return ['points' => [], 'maxRevenue' => 100, 'maxOrders' => 10, 'totalRevenue' => 0, 'totalCount' => 0];
        }

        $startDate = match ($period) {
            '7days' => now()->subDays(6)->startOfDay(),
            '30days' => now()->subDays(29)->startOfDay(),
            '3months' => now()->subMonths(3)->startOfDay(),
            '6months' => now()->subMonths(6)->startOfDay(),
            '12months' => now()->subMonths(12)->startOfDay(),
            default => now()->subDays(29)->startOfDay(),
        };

        $orders = Order::where('created_at', '>=', $startDate)
            ->whereIn('payment_status', ['paid', 'completed', 'partially_refunded'])
            ->get();

        $points = [];
        $current = clone $startDate;
        $now = now();

        if (in_array($period, ['3months', '6months', '12months'])) {
            // Group weekly or monthly
            while ($current <= $now) {
                $weekEnd = (clone $current)->addDays(6)->endOfDay();
                $label = $current->format('M d');
                $matching = $orders->filter(fn($o) => $o->created_at >= $current && $o->created_at <= $weekEnd);
                $points[] = [
                    'label' => $label,
                    'revenue' => round($matching->sum('total_amount'), 2),
                    'orders' => $matching->count(),
                ];
                $current->addDays(7);
            }
        } else {
            // Group daily
            while ($current <= $now) {
                $dateKey = $current->format('Y-m-d');
                $label = $current->format('M d');
                $matching = $orders->filter(fn($o) => $o->created_at->format('Y-m-d') === $dateKey);
                $points[] = [
                    'label' => $label,
                    'revenue' => round($matching->sum('total_amount'), 2),
                    'orders' => $matching->count(),
                ];
                $current->addDay();
            }
        }

        $maxRevenue = !empty($points) ? max(array_column($points, 'revenue')) : 100;
        $maxOrders = !empty($points) ? max(array_column($points, 'orders')) : 5;

        return [
            'points' => $points,
            'maxRevenue' => max(10, $maxRevenue),
            'maxOrders' => max(1, $maxOrders),
            'totalRevenue' => round($orders->sum('total_amount'), 2),
            'totalCount' => $orders->count(),
        ];
    }

    /**
     * Build unified real-time activity feed.
     */
    protected function buildActivityFeed(): array
    {
        $activities = [];

        // 1. Order Timelines (Real transactional & admin actions)
        $timelines = OrderTimeline::with(['order.user', 'performer'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        foreach ($timelines as $tl) {
            $icon = match($tl->event) {
                'order_created' => '📦',
                'payment_completed' => '💰',
                'refund_processed' => '↩',
                'status_updated' => '⚙',
                'notes_updated' => '📝',
                'confirmation_resent' => '✉',
                default => '⚡',
            };

            $activities[] = [
                'icon' => $icon,
                'title' => strtoupper(str_replace('_', ' ', $tl->event)),
                'description' => $tl->description,
                'user' => $tl->performer ? $tl->performer->name : ($tl->order && $tl->order->user ? $tl->order->user->name : 'System'),
                'time' => $tl->created_at->diffForHumans(),
                'timestamp' => $tl->created_at->timestamp,
            ];
        }

        // 2. Recent User Signups
        $newUsers = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($newUsers as $u) {
            $activities[] = [
                'icon' => '👤',
                'title' => 'USER REGISTERED',
                'description' => "{$u->name} created a new platform account ({$u->email})",
                'user' => $u->name,
                'time' => $u->created_at->diffForHumans(),
                'timestamp' => $u->created_at->timestamp,
            ];
        }

        // Sort unified activities chronologically descending
        usort($activities, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return array_slice($activities, 0, 8);
    }
}

