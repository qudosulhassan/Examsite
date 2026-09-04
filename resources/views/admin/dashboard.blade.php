@extends('layouts.admin')

@section('content')
<div class="space-y-8" x-data="{
    refreshing: false,
    chartMetric: '{{ $chartMetric }}',
    chartPeriod: '{{ $chartPeriod }}',
    lastUpdated: '{{ now()->format('h:i:s A') }}',
    refreshDashboard() {
        this.refreshing = true;
        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.lastUpdated = data.updated_at;
                // Soft reload page with current query parameters to update all tables
                window.location.reload();
            }
        })
        .catch(() => {
            window.location.reload();
        })
        .finally(() => {
            setTimeout(() => { this.refreshing = false; }, 600);
        });
    }
}">

    <!-- ================= 1. EXECUTIVE HEADER & GLOBAL FILTER ================= -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-2 border-b border-gray-200">
        <div>
            <div class="flex items-center space-x-2 text-xs text-gray-400 font-semibold mb-1">
                <span class="text-cyan font-bold">Admin Console</span>
                <span>/</span>
                <span class="text-navy font-bold">Executive Dashboard</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-black text-navy tracking-tight">
                    {{ $greeting }}, {{ auth()->user()->name }}
                </h1>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                    Live Data
                </span>
            </div>
            <p class="text-xs text-gray-400 mt-1 flex items-center gap-2">
                <span>{{ now()->format('l, F j, Y') }}</span>
                <span>&bull;</span>
                <span>Last updated: <strong class="text-gray-600 font-mono" x-text="lastUpdated">{{ now()->format('h:i:s A') }}</strong></span>
            </p>
        </div>

        <!-- Global Date Range Selector & Refresh Action -->
        <div class="flex flex-wrap items-center gap-2.5">
            <form action="{{ route('admin.dashboard') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <!-- Retain current chart switches if present -->
                <input type="hidden" name="chart_period" value="{{ $chartPeriod }}">
                <input type="hidden" name="chart_metric" :value="chartMetric">

                <div class="relative min-w-[170px]">
                    <select name="date_range" onchange="this.form.submit()"
                            style="width: 100%; height: 40px; padding-left: 36px; padding-right: 32px; background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; font-size: 12px; font-weight: 700; color: #0A1628; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"
                            onfocus="this.style.borderColor='#00D4AA';"
                            onblur="this.style.borderColor='#E2E8F0';">
                        <option value="today" {{ $dateRange === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ $dateRange === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="7days" {{ $dateRange === '7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30days" {{ $dateRange === '30days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="this_month" {{ $dateRange === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ $dateRange === 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ $dateRange === 'this_year' ? 'selected' : '' }}>This Year</option>
                    </select>
                    <!-- Calendar Icon -->
                    <div style="position: absolute; top: 0; bottom: 0; left: 12px; display: flex; align-items: center; pointer-events: none; color: #64748B;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <!-- Chevron -->
                    <div style="position: absolute; top: 0; bottom: 0; right: 12px; display: flex; align-items: center; pointer-events: none; color: #94A3B8;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </form>

            <!-- Refresh Button with Spinner -->
            <button type="button" @click="refreshDashboard()" :disabled="refreshing"
                    class="inline-flex items-center px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 text-xs font-bold rounded-xl shadow-sm transition-all hover:border-gray-300">
                <svg class="w-4 h-4 mr-1.5 text-gray-500 transition-transform" :class="refreshing ? 'animate-spin text-cyan' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span x-text="refreshing ? 'Refreshing...' : 'Refresh Data'"></span>
            </button>
        </div>
    </div>

    <!-- ================= 2. CORE SAAS KPI METRICS GRID ================= -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        
        <!-- CARD 1: Total Revenue -->
        @if($canViewFinance)
            <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Net Revenue</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                        💰
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-navy">${{ number_format($currNetRevenue, 2) }}</div>
                
                <div class="mt-2 flex items-center text-[11px]">
                    @if(!empty($revenueChange))
                        <span class="inline-flex items-center font-bold {{ $revenueChange['direction'] === 'up' ? 'text-emerald-600' : 'text-rose-500' }} mr-1">
                            {{ $revenueChange['direction'] === 'up' ? '↑' : '↓' }} {{ $revenueChange['formatted'] }}
                        </span>
                        <span class="text-gray-400 text-[10px]">vs prior</span>
                    @else
                        <span class="text-gray-400 text-[10px]">{{ $rangeLabel }}</span>
                    @endif
                </div>
            </div>
        @else
            <!-- Content Metric fallback if finance hidden -->
            <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Certifications</span>
                    <div class="w-8 h-8 rounded-xl bg-cyan/10 text-cyan flex items-center justify-center font-bold text-sm">
                        📜
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-navy">{{ number_format($totalExams) }}</div>
                <div class="text-[10px] text-gray-400 mt-2 font-semibold">{{ $activeExams }} active exams</div>
            </div>
        @endif

        <!-- CARD 2: Orders Count -->
        @if($canViewFinance)
            <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Orders</span>
                    <div class="w-8 h-8 rounded-xl bg-navy/5 text-navy flex items-center justify-center font-bold text-sm">
                        📦
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-navy">{{ number_format($currOrdersCount) }}</div>
                
                <div class="mt-2 flex items-center text-[11px]">
                    @if(!empty($ordersChange))
                        <span class="inline-flex items-center font-bold {{ $ordersChange['direction'] === 'up' ? 'text-emerald-600' : 'text-rose-500' }} mr-1">
                            {{ $ordersChange['direction'] === 'up' ? '↑' : '↓' }} {{ $ordersChange['formatted'] }}
                        </span>
                        <span class="text-gray-400 text-[10px]">vs prior</span>
                    @else
                        <span class="text-gray-400 text-[10px]">{{ $totalOrdersAllTime }} all-time</span>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Vendors</span>
                    <div class="w-8 h-8 rounded-xl bg-navy/5 text-navy flex items-center justify-center font-bold text-sm">
                        🏢
                    </div>
                </div>
                <div class="text-xl sm:text-2xl font-black text-navy">{{ number_format(\App\Models\Vendor::count()) }}</div>
                <div class="text-[10px] text-gray-400 mt-2 font-semibold">Supported Providers</div>
            </div>
        @endif

        <!-- CARD 3: Platform Users -->
        <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Users</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                    👥
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black text-navy">{{ number_format($totalUsers) }}</div>
            
            <div class="mt-2 flex items-center text-[11px]">
                @if(!empty($usersChange))
                    <span class="inline-flex items-center font-bold {{ $usersChange['direction'] === 'up' ? 'text-emerald-600' : 'text-rose-500' }} mr-1">
                        {{ $usersChange['direction'] === 'up' ? '↑' : '↓' }} {{ $usersChange['formatted'] }}
                    </span>
                    <span class="text-gray-400 text-[10px]">+{{ $currNewUsers }} in period</span>
                @else
                    <span class="text-gray-400 text-[10px]">+{{ $currNewUsers }} in period</span>
                @endif
            </div>
        </div>

        <!-- CARD 4: Active Subscriptions -->
        <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Subscriptions</span>
                <div class="w-8 h-8 rounded-xl bg-cyan/10 text-cyan flex items-center justify-center font-bold text-sm">
                    ⚡
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black text-navy">{{ number_format($activeSubscriptions) }}</div>
            <div class="mt-2 text-[10px] text-gray-400 font-semibold">Active Member Plans</div>
        </div>

        <!-- CARD 5: Exam Questions -->
        <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Exam Questions</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                    ❓
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black text-navy">{{ number_format($totalQuestions) }}</div>
            <div class="mt-2 text-[10px] text-gray-400 font-semibold">{{ $questionsWithExplanation }} with explanations</div>
        </div>

        <!-- CARD 6: Active Exams -->
        <div class="bg-white rounded-2xl border border-gray-150 p-4 sm:p-5 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Active Exams</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm">
                    🎓
                </div>
            </div>
            <div class="text-xl sm:text-2xl font-black text-navy">{{ number_format($activeExams) }}</div>
            <div class="mt-2 text-[10px] text-gray-400 font-semibold">of {{ $totalExams }} total catalog</div>
        </div>

    </div>

    <!-- ================= 3. REVENUE & ORDERS OVERVIEW CHART ================= -->
    @if($canViewFinance)
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-100">
                <div>
                    <div class="flex items-center space-x-3">
                        <h3 class="text-base font-extrabold text-navy tracking-tight">Revenue & Sales Trajectory</h3>
                        <!-- Metric Switcher Pills: Revenue vs Orders -->
                        <div class="inline-flex p-0.5 bg-gray-100 rounded-lg text-[11px] font-bold">
                            <button type="button" @click="chartMetric = 'revenue'"
                                    :class="chartMetric === 'revenue' ? 'bg-white text-navy shadow-sm font-black' : 'text-gray-500 hover:text-navy'"
                                    class="px-2.5 py-1 rounded-md transition-all">
                                Revenue ($)
                            </button>
                            <button type="button" @click="chartMetric = 'orders'"
                                    :class="chartMetric === 'orders' ? 'bg-white text-navy shadow-sm font-black' : 'text-gray-500 hover:text-navy'"
                                    class="px-2.5 py-1 rounded-md transition-all">
                                Orders Count
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">
                        Period Revenue: <span class="font-bold text-emerald-600">${{ number_format($chartData['totalRevenue'], 2) }}</span>
                        &nbsp;&bull;&nbsp; Volume: <span class="font-bold text-navy">{{ $chartData['totalCount'] }} paid orders</span>
                    </p>
                </div>

                <!-- Time Horizon Switcher -->
                <div class="inline-flex p-1 bg-gray-100 rounded-xl text-xs font-bold space-x-1">
                    @foreach([
                        '7days' => '7D',
                        '30days' => '30D',
                        '3months' => '3M',
                        '6months' => '6M',
                        '12months' => '12M'
                    ] as $key => $label)
                        <a href="{{ request()->fullUrlWithQuery(['chart_period' => $key]) }}"
                           class="px-3 py-1.5 rounded-lg transition-all {{ $chartPeriod === $key ? 'bg-white text-navy shadow-sm font-black' : 'text-gray-500 hover:text-navy' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Visualization Area (Responsive Pure SVG / CSS Bar Chart) -->
            <div class="mt-6">
                @if(empty($chartData['points']) || ($chartData['totalRevenue'] == 0 && $chartData['totalCount'] == 0))
                    <div class="py-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <div class="text-sm font-bold text-gray-600">No transactions recorded for this timeframe</div>
                        <div class="text-xs text-gray-400 mt-0.5">Select a broader range or check back as customers purchase mock packages.</div>
                    </div>
                @else
                    <div class="space-y-2">
                        <!-- Bars Container -->
                        <div class="h-44 w-full flex items-end gap-1 sm:gap-2 pt-6 pb-2 px-2 overflow-x-auto border-b border-gray-100">
                            @foreach($chartData['points'] as $pt)
                                @php
                                    $revHeight = $chartData['maxRevenue'] > 0 ? max(4, round(($pt['revenue'] / $chartData['maxRevenue']) * 100)) : 4;
                                    $ordHeight = $chartData['maxOrders'] > 0 ? max(4, round(($pt['orders'] / $chartData['maxOrders']) * 100)) : 4;
                                @endphp
                                <div class="flex-1 min-w-[24px] max-w-[48px] flex flex-col items-center group relative h-full justify-end">
                                    <!-- Tooltip -->
                                    <div class="absolute bottom-full mb-2 hidden group-hover:flex flex-col items-center pointer-events-none z-20">
                                        <div class="bg-navy text-white text-[11px] rounded-lg py-1.5 px-2.5 shadow-xl whitespace-nowrap text-center">
                                            <div class="font-bold text-cyan">${{ number_format($pt['revenue'], 2) }}</div>
                                            <div class="text-[9px] text-gray-300">{{ $pt['orders'] }} {{ Str::plural('order', $pt['orders']) }} &bull; {{ $pt['label'] }}</div>
                                        </div>
                                        <div class="w-2 h-2 bg-navy transform rotate-45 -mt-1"></div>
                                    </div>

                                    <!-- Bar Column -->
                                    <div class="w-full bg-cyan/20 hover:bg-cyan rounded-t-md transition-all duration-300 relative group-hover:scale-y-105 origin-bottom cursor-pointer"
                                         :style="chartMetric === 'revenue' ? 'height: {{ $revHeight }}%' : 'height: {{ $ordHeight }}%'">
                                        @if($pt['revenue'] > 0)
                                            <div class="absolute inset-x-0 top-0 h-1 bg-cyan rounded-t-md"></div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Labels Row -->
                        <div class="w-full flex justify-between px-2 text-[10px] font-bold text-gray-400 overflow-x-auto">
                            @php
                                $totalPts = count($chartData['points']);
                                $step = max(1, (int)ceil($totalPts / 10));
                            @endphp
                            @foreach($chartData['points'] as $idx => $pt)
                                @if($idx % $step === 0 || $idx === $totalPts - 1)
                                    <span>{{ $pt['label'] }}</span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- ================= 4. SALES PERFORMANCE & ORDER DISTRIBUTION ================= -->
    @if($canViewFinance)
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left: Sales Performance Metrics (7 cols) -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-150">
                    <div>
                        <h3 class="font-extrabold text-navy text-sm">Sales Performance</h3>
                        <p class="text-[11px] text-gray-400 mt-0.5">Real-time checkout conversions and average transaction metrics</p>
                    </div>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                        {{ $rangeLabel }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Sales</div>
                        <div class="text-xl font-black text-navy mt-1">${{ number_format($currNetRevenue, 2) }}</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">{{ $currOrdersCount }} orders</div>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Avg Order Value (AOV)</div>
                        <div class="text-xl font-black text-navy mt-1">${{ number_format($averageOrderValue, 2) }}</div>
                        <div class="text-[10px] text-emerald-600 font-bold mt-0.5">Per paid checkout</div>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Paid / Completed</div>
                        <div class="text-xl font-black text-emerald-600 mt-1">{{ $totalPaidCount }}</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">Captured in full</div>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pending Orders</div>
                        <div class="text-xl font-black text-amber-600 mt-1">{{ $totalPendingCount }}</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">Awaiting capture</div>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Refunded Orders</div>
                        <div class="text-xl font-black text-purple-600 mt-1">{{ $totalRefundedCount }}</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">Full & partial refunds</div>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Failed / Cancelled</div>
                        <div class="text-xl font-black text-rose-600 mt-1">{{ $totalFailedCount }}</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">Voided transactions</div>
                    </div>
                </div>
            </div>

            <!-- Right: Order Status Distribution (5 cols) -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 space-y-4">
                <div class="pb-3 border-b border-gray-150">
                    <h3 class="font-extrabold text-navy text-sm">Order Status Distribution</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Lifetime order lifecycle proportions ({{ $totalOrdersAllTime }} total orders)</p>
                </div>

                <div class="space-y-3.5 pt-1">
                    @foreach($orderDistribution as $dist)
                        <div>
                            <div class="flex justify-between items-center text-xs font-bold mb-1">
                                <span class="text-navy flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $dist['bg'] }}"></span>
                                    {{ $dist['label'] }}
                                </span>
                                <span class="text-gray-700">
                                    {{ $dist['count'] }} <span class="text-gray-400 font-normal">({{ $dist['percentage'] }}%)</span>
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="{{ $dist['bg'] }} h-2.5 rounded-full transition-all duration-500" style="width: {{ max(1, $dist['percentage']) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    @endif

    <!-- ================= 5. RECENT ORDERS TABLE ================= -->
    @if($canViewFinance)
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-150 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/50">
                <div>
                    <h3 class="font-extrabold text-navy text-sm">Recent Customer Orders</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Showing latest transactions placed on ExamTopicsBase</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-cyan hover:underline inline-flex items-center gap-1">
                    <span>View All Orders</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-150 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3.5 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Order No</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Customer</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Product / Exam</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Gateway</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-5 py-3.5 text-right text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-150">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <!-- Order No -->
                                <td class="px-5 py-4 font-mono font-bold">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="text-navy hover:text-cyan">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>

                                <!-- Customer -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-navy/10 text-navy font-bold flex items-center justify-center text-xs uppercase flex-shrink-0">
                                            {{ $order->user ? strtoupper(substr($order->user->name, 0, 2)) : 'GU' }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-gray-800 truncate">
                                                {{ $order->user ? $order->user->name : ($order->billing_name ?: 'Customer') }}
                                            </div>
                                            <div class="text-[10px] text-gray-400 truncate">
                                                {{ $order->user ? $order->user->email : $order->billing_email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Product / Exam Preview -->
                                <td class="px-5 py-4">
                                    @php
                                        $firstItem = $order->items->first();
                                        $extraCount = $order->items->count() - 1;
                                    @endphp
                                    @if($firstItem)
                                        <div class="font-semibold text-navy truncate max-w-[180px]">
                                            @if($firstItem->exam)
                                                <span class="font-mono text-cyan text-[10px] mr-1">[{{ $firstItem->exam->exam_code }}]</span>
                                                {{ $firstItem->exam->exam_name }}
                                            @else
                                                <span>{{ $firstItem->plan_name }} Access</span>
                                            @endif
                                        </div>
                                        @if($extraCount > 0)
                                            <span class="text-[9px] font-bold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">
                                                +{{ $extraCount }} more
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 italic">Exam Study Package</span>
                                    @endif
                                </td>

                                <!-- Amount -->
                                <td class="px-5 py-4 font-mono font-extrabold text-navy text-sm">
                                    ${{ number_format($order->total_amount, 2) }}
                                </td>

                                <!-- Payment Gateway -->
                                <td class="px-5 py-4 uppercase text-[10px] font-bold text-gray-600">
                                    <div class="flex items-center space-x-1.5">
                                        @if(strtolower($order->payment_method) === 'stripe')
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            <span>Stripe</span>
                                        @elseif(strtolower($order->payment_method) === 'paypal')
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            <span>PayPal</span>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                            <span>{{ $order->payment_method ?: 'Direct' }}</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Status Badge -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase
                                        {{ in_array($order->payment_status, ['paid', 'completed']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : (in_array($order->payment_status, ['refunded', 'partially_refunded']) ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                        {{ str_replace('_', ' ', $order->payment_status) }}
                                    </span>
                                </td>

                                <!-- Date -->
                                <td class="px-5 py-4 text-gray-500 whitespace-nowrap text-[11px]">
                                    {{ $order->created_at->format('M d, Y') }}
                                </td>

                                <!-- Action -->
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order->id) }}"
                                       class="px-2.5 py-1 bg-navy/5 hover:bg-navy text-navy hover:text-white font-bold rounded-lg text-xs transition">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-400">
                                    No customer orders placed yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- ================= 6. TOP EXAMS & TOP VENDORS ================= -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Top Selling Exams -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-150">
                <div>
                    <h3 class="font-extrabold text-navy text-sm">Top Selling Exams</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Highest volume and revenue generators from actual sales</p>
                </div>
                <a href="{{ route('admin.exams.index') }}" class="text-xs font-bold text-cyan hover:underline">
                    Manage Exams →
                </a>
            </div>

            <div class="divide-y divide-gray-150 text-xs">
                @forelse($topSellingExams as $index => $exam)
                    <div class="py-3 flex items-center justify-between hover:bg-gray-50/60 transition rounded-xl px-2">
                        <div class="flex items-center space-x-3 min-w-0">
                            <span class="w-6 h-6 rounded-lg bg-navy text-white text-[10px] font-black flex items-center justify-center flex-shrink-0">
                                {{ $index + 1 }}
                            </span>
                            <div class="min-w-0">
                                <div class="font-bold text-navy truncate">
                                    <span class="text-cyan font-mono mr-1">[{{ $exam->exam_code }}]</span>
                                    {{ $exam->exam_name }}
                                </div>
                                <div class="text-[10px] text-gray-400 mt-0.5 font-semibold">
                                    Provider: {{ $exam->vendor_name ?: 'General' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 ml-3">
                            <span class="font-bold text-navy block font-mono">{{ $exam->sales_count }} {{ Str::plural('sale', $exam->sales_count) }}</span>
                            <span class="text-[11px] text-emerald-600 font-extrabold font-mono">${{ number_format($exam->total_revenue, 2) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-gray-400">
                        <p class="text-xs">No exam sales recorded in this period yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Top Vendors -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-150">
                <div>
                    <h3 class="font-extrabold text-navy text-sm">Top Vendors</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Ranked by purchased certifications and catalog coverage</p>
                </div>
                <a href="{{ route('admin.vendors.index') }}" class="text-xs font-bold text-cyan hover:underline">
                    Manage Vendors →
                </a>
            </div>

            <div class="divide-y divide-gray-150 text-xs">
                @forelse($topVendors as $index => $vendor)
                    <div class="py-3 flex items-center justify-between hover:bg-gray-50/60 transition rounded-xl px-2">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-xl bg-cyan/10 text-cyan font-black flex items-center justify-center text-xs uppercase flex-shrink-0">
                                {{ strtoupper(substr($vendor->name, 0, 2)) }}
                            </div>
                            <div>
                                <span class="font-bold text-navy block text-sm">{{ $vendor->name }}</span>
                                <span class="text-[10px] text-gray-400">{{ $vendor->exams_sold }} distinct {{ Str::plural('exam', $vendor->exams_sold) }} sold</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-navy block font-mono">{{ $vendor->sales_count }} sales</span>
                            <span class="text-[11px] text-emerald-600 font-extrabold font-mono">${{ number_format($vendor->total_revenue, 2) }}</span>
                        </div>
                    </div>
                @empty
                    @foreach($catalogVendors as $index => $vendor)
                        <div class="py-3 flex items-center justify-between hover:bg-gray-50/60 transition rounded-xl px-2">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-xl bg-navy/10 text-navy font-bold flex items-center justify-center text-xs uppercase flex-shrink-0">
                                    {{ strtoupper(substr($vendor->name, 0, 2)) }}
                                </div>
                                <div>
                                    <span class="font-bold text-navy block text-sm">{{ $vendor->name }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $vendor->exams_count }} exams available in catalog</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-bold text-gray-500">Catalog Active</span>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </div>

    </div>

    <!-- ================= 7. USER & SUBSCRIPTION OVERVIEW ================= -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- User Analytics Overview -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-150">
                <div>
                    <h3 class="font-extrabold text-navy text-sm">User & Account Analytics</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Platform account distribution and security status</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-cyan hover:underline">
                    Users Directory →
                </a>
            </div>

            <div class="grid grid-cols-3 gap-3 pt-1">
                <div class="p-3.5 bg-emerald-50/40 border border-emerald-100 rounded-xl">
                    <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider">Active Users</div>
                    <div class="text-xl font-black text-emerald-600 mt-1">{{ number_format($userStatusCounts['active']) }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">In good standing</div>
                </div>

                <div class="p-3.5 bg-blue-50/40 border border-blue-100 rounded-xl">
                    <div class="text-[10px] font-bold text-blue-700 uppercase tracking-wider">Students</div>
                    <div class="text-xl font-black text-blue-600 mt-1">{{ number_format($studentsCount) }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Learners enrolled</div>
                </div>

                <div class="p-3.5 bg-navy/5 border border-navy/10 rounded-xl">
                    <div class="text-[10px] font-bold text-navy uppercase tracking-wider">Staff & Admin</div>
                    <div class="text-xl font-black text-navy mt-1">{{ number_format($adminStaffCount) }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Operator access</div>
                </div>
            </div>

            @if($userStatusCounts['suspended'] > 0)
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-2 text-amber-800 font-bold">
                        <span>⚠️</span>
                        <span>{{ $userStatusCounts['suspended'] }} accounts are currently suspended</span>
                    </div>
                    <a href="{{ route('admin.users.index', ['status' => 'suspended']) }}" class="text-cyan hover:underline font-bold text-[11px]">
                        Review Suspensions →
                    </a>
                </div>
            @endif
        </div>

        <!-- Subscription Overview -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-150">
                <div>
                    <h3 class="font-extrabold text-navy text-sm">Subscription Lifecycle</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Recurring membership plans and renewal statuses</p>
                </div>
                <a href="{{ url('/admin/subscriptions') }}" class="text-xs font-bold text-cyan hover:underline">
                    Subscriptions →
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1">
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Active</div>
                    <div class="text-lg font-black text-emerald-600 mt-0.5">{{ $subscriptionBreakdown['active'] }}</div>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">In Trial</div>
                    <div class="text-lg font-black text-cyan mt-0.5">{{ $subscriptionBreakdown['trial'] }}</div>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Expired</div>
                    <div class="text-lg font-black text-gray-500 mt-0.5">{{ $subscriptionBreakdown['expired'] }}</div>
                </div>
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Cancelled</div>
                    <div class="text-lg font-black text-rose-500 mt-0.5">{{ $subscriptionBreakdown['cancelled'] }}</div>
                </div>
            </div>

            @if($activeSubscriptions === 0)
                <div class="p-4 bg-gray-50 rounded-xl text-center text-xs text-gray-400">
                    No active recurring subscriptions in database. Most purchases are processed as lifetime certification packages.
                </div>
            @endif
        </div>

    </div>

    <!-- ================= 8. CONTENT & QUESTION ANALYTICS ================= -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-gray-150">
            <div>
                <h3 class="font-extrabold text-navy text-sm">Exam Content & Question Health</h3>
                <p class="text-[11px] text-gray-400 mt-0.5">Quality metrics for question explanations, answer keys, and catalog freshness</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.questions.create') }}" class="px-3 py-1.5 bg-cyan text-navy font-black text-xs rounded-xl shadow-sm hover:opacity-90 transition">
                    + Add Question
                </a>
                <a href="{{ route('admin.questions.import-form') }}" class="px-3 py-1.5 bg-navy text-white font-bold text-xs rounded-xl shadow-sm hover:opacity-90 transition">
                    Import Questions
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-150">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">With Explanations</div>
                <div class="text-xl font-black text-navy mt-1">
                    {{ number_format($questionsWithExplanation) }}
                    <span class="text-xs font-semibold text-emerald-600">({{ $totalQuestions > 0 ? round(($questionsWithExplanation / $totalQuestions) * 100, 1) : 0 }}%)</span>
                </div>
                <div class="text-[10px] text-gray-400 mt-0.5">Pass rate booster</div>
            </div>

            <div class="p-4 bg-gray-50 rounded-xl border border-gray-150">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">With Answer Key</div>
                <div class="text-xl font-black text-navy mt-1">
                    {{ number_format($questionsWithCorrectOption) }}
                    <span class="text-xs font-semibold text-cyan">({{ $totalQuestions > 0 ? round(($questionsWithCorrectOption / $totalQuestions) * 100, 1) : 0 }}%)</span>
                </div>
                <div class="text-[10px] text-gray-400 mt-0.5">Evaluated questions</div>
            </div>

            <div class="p-4 bg-gray-50 rounded-xl border border-gray-150">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">With Diagrams / Images</div>
                <div class="text-xl font-black text-navy mt-1">{{ number_format($questionsWithImages) }}</div>
                <div class="text-[10px] text-gray-400 mt-0.5">Exhibit items</div>
            </div>

            <div class="p-4 bg-gray-50 rounded-xl border border-gray-150">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Published Catalog</div>
                <div class="text-xl font-black text-emerald-600 mt-1">{{ number_format($activeExams) }}</div>
                <div class="text-[10px] text-gray-400 mt-0.5">100% active exams</div>
            </div>
        </div>

        <!-- Recently Updated Exams Table -->
        <div>
            <div class="text-xs font-bold text-navy uppercase tracking-wider mb-2">Recently Updated Exams</div>
            <div class="border border-gray-150 rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-150 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-bold text-gray-400 uppercase text-[10px]">Exam Code</th>
                            <th class="px-4 py-2.5 text-left font-bold text-gray-400 uppercase text-[10px]">Exam Name</th>
                            <th class="px-4 py-2.5 text-left font-bold text-gray-400 uppercase text-[10px]">Provider</th>
                            <th class="px-4 py-2.5 text-center font-bold text-gray-400 uppercase text-[10px]">Questions</th>
                            <th class="px-4 py-2.5 text-right font-bold text-gray-400 uppercase text-[10px]">Last Modified</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-150 bg-white">
                        @foreach($recentlyUpdatedExams as $rx)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-4 py-3 font-mono font-bold text-cyan">
                                    <a href="{{ route('admin.exams.edit', $rx->id) }}" class="hover:underline">
                                        {{ $rx->exam_code }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-800">
                                    {{ Str::limit($rx->exam_name, 45) }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 font-medium">
                                    {{ $rx->vendor ? $rx->vendor->name : 'N/A' }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold font-mono text-navy">
                                    {{ $rx->questions_count }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-400 text-[11px]">
                                    {{ $rx->updated_at ? $rx->updated_at->diffForHumans() : 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= 9. RECENT ACTIVITY & SYSTEM ALERTS ================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Recent Activity Feed (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 space-y-4">
            <div class="pb-3 border-b border-gray-150 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-navy text-sm">Recent Platform Activity</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Real-time orders, customer registrations, and administrative events</p>
                </div>
                <span class="text-xs font-bold text-cyan bg-cyan/10 px-2 py-0.5 rounded-lg">Real Audit</span>
            </div>

            <div class="space-y-4 relative pl-5 border-l-2 border-gray-150">
                @forelse($recentActivities as $act)
                    <div class="relative">
                        <div class="absolute -left-[27px] top-0.5 w-6 h-6 rounded-full bg-navy/5 text-navy flex items-center justify-center text-xs">
                            {{ $act['icon'] }}
                        </div>
                        <div class="flex items-baseline justify-between">
                            <div class="text-xs font-bold text-navy">{{ $act['title'] }}</div>
                            <div class="text-[10px] text-gray-400 font-semibold">{{ $act['time'] }}</div>
                        </div>
                        <div class="text-[11px] text-gray-600 mt-0.5">{{ $act['description'] }}</div>
                    </div>
                @empty
                    <div class="py-6 text-center text-gray-400 text-xs">
                        No activity records logged yet.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- System Alerts & Warnings (5 cols) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 space-y-4">
            <div class="pb-3 border-b border-gray-150 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-navy text-sm">System Alerts & Action Items</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Automated monitoring of critical platform exceptions</p>
                </div>
                <span class="w-2.5 h-2.5 rounded-full {{ count($systemAlerts) > 0 ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
            </div>

            <div class="space-y-3">
                @forelse($systemAlerts as $alert)
                    @php
                        $alertStyles = match($alert['type']) {
                            'error' => 'bg-rose-50 border-rose-200 text-rose-900',
                            'warning' => 'bg-amber-50 border-amber-200 text-amber-900',
                            default => 'bg-blue-50 border-blue-200 text-blue-900',
                        };
                        $btnStyles = match($alert['type']) {
                            'error' => 'bg-rose-600 hover:bg-rose-700 text-white',
                            'warning' => 'bg-amber-600 hover:bg-amber-700 text-white',
                            default => 'bg-blue-600 hover:bg-blue-700 text-white',
                        };
                    @endphp
                    <div class="p-3.5 rounded-xl border {{ $alertStyles }} space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div class="font-bold text-xs">{{ $alert['title'] }}</div>
                        </div>
                        <p class="text-[11px] opacity-90 leading-relaxed">{{ $alert['description'] }}</p>
                        @if(!empty($alert['action_url']))
                            <div class="pt-1">
                                <a href="{{ $alert['action_url'] }}"
                                   class="inline-block px-3 py-1 rounded-lg text-[10px] font-extrabold {{ $btnStyles }} transition shadow-sm">
                                    {{ $alert['action_label'] }} &rarr;
                                </a>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-8 text-center text-emerald-600 space-y-1">
                        <div class="text-2xl">✓</div>
                        <div class="text-xs font-bold">All Systems Operational</div>
                        <div class="text-[10px] text-gray-400">No failed payments, zero-question exams, or pending alerts detected.</div>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- ================= 10. PAYMENT GATEWAYS & RECENT CUSTOMERS ================= -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Payment Methods Breakdown (5 cols) -->
        @if($canViewFinance)
            <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 space-y-4">
                <div class="pb-3 border-b border-gray-150">
                    <h3 class="font-extrabold text-navy text-sm">Payment Gateways</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Revenue generated by connected checkout processors</p>
                </div>

                <div class="space-y-3.5">
                    @forelse($paymentMethodsAnalytics as $pm)
                        <div>
                            <div class="flex justify-between items-center text-xs font-bold mb-1">
                                <span class="text-navy flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $pm['name'] === 'STRIPE' ? 'bg-indigo-500' : 'bg-blue-500' }}"></span>
                                    {{ $pm['name'] }}
                                </span>
                                <span class="font-mono text-gray-800">
                                    ${{ number_format($pm['revenue'], 2) }}
                                    <span class="text-gray-400 font-normal">({{ $pm['percentage'] }}%)</span>
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="{{ $pm['name'] === 'STRIPE' ? 'bg-indigo-500' : 'bg-blue-500' }} h-2 rounded-full" style="width: {{ $pm['percentage'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-gray-400 text-xs">
                            No payment gateway transactions recorded yet.
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        <!-- Recent Customers Table (7 cols or 12 cols if no finance) -->
        <div class="{{ $canViewFinance ? 'lg:col-span-7' : 'lg:col-span-12' }} bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-150">
                <div>
                    <h3 class="font-extrabold text-navy text-sm">New Registered Customers</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">Recently created student and candidate accounts</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-cyan hover:underline">
                    All Customers →
                </a>
            </div>

            <div class="divide-y divide-gray-150 text-xs">
                @forelse($recentCustomers as $cust)
                    <div class="py-2.5 flex items-center justify-between hover:bg-gray-50/60 transition px-2 rounded-xl">
                        <div class="flex items-center space-x-3 min-w-0">
                            <!-- Initials Avatar fallback (never broken image) -->
                            <div class="w-8 h-8 rounded-xl bg-navy/10 text-navy font-black flex items-center justify-center text-xs uppercase flex-shrink-0">
                                {{ strtoupper(substr($cust->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('admin.users.show', $cust->id) }}" class="font-bold text-navy hover:text-cyan truncate block">
                                    {{ $cust->name }}
                                </a>
                                <span class="text-[10px] text-gray-400 truncate block">{{ $cust->email }}</span>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 ml-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase {{ $cust->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-700' }}">
                                {{ $cust->status }}
                            </span>
                            <div class="text-[10px] text-gray-400 mt-0.5">{{ $cust->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-gray-400 text-xs">
                        No customer accounts registered yet.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- ================= 11. QUICK ACTIONS TOOLBAR ================= -->
    <div class="bg-gradient-to-r from-navy to-slate-900 rounded-3xl p-6 shadow-xl text-white">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-800">
            <div>
                <h3 class="font-extrabold text-base tracking-tight text-white">Administrative Quick Actions</h3>
                <p class="text-xs text-gray-400 mt-0.5">Direct permission-checked shortcuts to core workflows</p>
            </div>
            <span class="text-[10px] font-bold text-cyan font-mono uppercase tracking-wider">Verified RBAC Access</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 pt-4">
            @can('create-users')
                <a href="{{ route('admin.users.create') }}" class="p-3.5 bg-white/5 hover:bg-white/10 rounded-2xl border border-white/10 transition-all text-center group">
                    <div class="text-lg group-hover:scale-110 transition-transform">👤</div>
                    <div class="text-xs font-extrabold text-white mt-1">+ Add User</div>
                    <div class="text-[9px] text-gray-400 mt-0.5">Platform account</div>
                </a>
            @endcan

            @can('create-exams')
                <a href="{{ route('admin.exams.create') }}" class="p-3.5 bg-white/5 hover:bg-white/10 rounded-2xl border border-white/10 transition-all text-center group">
                    <div class="text-lg group-hover:scale-110 transition-transform">📜</div>
                    <div class="text-xs font-extrabold text-white mt-1">+ Add Exam</div>
                    <div class="text-[9px] text-gray-400 mt-0.5">Certification code</div>
                </a>
            @endcan

            @can('create-questions')
                <a href="{{ route('admin.questions.create') }}" class="p-3.5 bg-white/5 hover:bg-white/10 rounded-2xl border border-white/10 transition-all text-center group">
                    <div class="text-lg group-hover:scale-110 transition-transform">❓</div>
                    <div class="text-xs font-extrabold text-white mt-1">+ Add Question</div>
                    <div class="text-[9px] text-gray-400 mt-0.5">Single / multiple</div>
                </a>
            @endcan

            <a href="{{ route('admin.packages.create') }}" class="p-3.5 bg-white/5 hover:bg-white/10 rounded-2xl border border-white/10 transition-all text-center group">
                <div class="text-lg group-hover:scale-110 transition-transform">📦</div>
                <div class="text-xs font-extrabold text-white mt-1">+ Create Bundle</div>
                <div class="text-[9px] text-gray-400 mt-0.5">Vendor package</div>
            </a>

            @can('manage-coupons')
                <a href="{{ route('admin.coupons.create') }}" class="p-3.5 bg-white/5 hover:bg-white/10 rounded-2xl border border-white/10 transition-all text-center group">
                    <div class="text-lg group-hover:scale-110 transition-transform">🏷️</div>
                    <div class="text-xs font-extrabold text-white mt-1">+ Create Coupon</div>
                    <div class="text-[9px] text-gray-400 mt-0.5">Promo discount</div>
                </a>
            @endcan

            @can('create-posts')
                <a href="{{ route('admin.blog.create') }}" class="p-3.5 bg-white/5 hover:bg-white/10 rounded-2xl border border-white/10 transition-all text-center group">
                    <div class="text-lg group-hover:scale-110 transition-transform">✍️</div>
                    <div class="text-xs font-extrabold text-white mt-1">+ Write Blog</div>
                    <div class="text-[9px] text-gray-400 mt-0.5">Articles & news</div>
                </a>
            @endcan
        </div>
    </div>

</div>
@endsection

