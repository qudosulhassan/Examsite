@extends('layouts.admin')

@section('content')
<div class="space-y-8" x-data="{
    selectedOrders: [],
    selectAll: false,
    bulkActionModal: false,
    bulkActionType: '',
    toggleSelectAll() {
        if (this.selectAll) {
            this.selectedOrders = Array.from(document.querySelectorAll('.order-checkbox')).map(el => el.value);
        } else {
            this.selectedOrders = [];
        }
    }
}">

    <!-- Page Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-gray-400 font-semibold mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-navy transition">Dashboard</a>
                <span>/</span>
                <span class="text-cyan font-bold">Orders</span>
            </div>
            <h1 class="text-2xl font-extrabold text-navy tracking-tight">Order Management</h1>
            <p class="text-xs text-gray-500 mt-0.5">Track real-time purchases, manage refunds, monitor revenue flow, and inspect customer transactions.</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.orders.export', request()->query()) }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl shadow-sm hover:bg-gray-50 hover:border-gray-300 transition-all">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    <!-- 1. Real Database-driven Statistics (8 Cards) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 sm:gap-4">
        <!-- Total Orders -->
        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Orders</span>
                <div class="w-7 h-7 rounded-lg bg-navy/5 text-navy flex items-center justify-center font-bold text-xs">
                    📦
                </div>
            </div>
            <div class="text-xl font-black text-navy">{{ number_format($totalOrders) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-0.5">Lifetime</div>
        </div>

        <!-- Today's Orders -->
        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Today</span>
                <div class="w-7 h-7 rounded-lg bg-cyan/10 text-cyan flex items-center justify-center font-bold text-xs">
                    ⚡
                </div>
            </div>
            <div class="text-xl font-black text-navy">{{ number_format($todayOrders) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-0.5">New today</div>
        </div>

        <!-- This Month -->
        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">This Month</span>
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                    📅
                </div>
            </div>
            <div class="text-xl font-black text-navy">{{ number_format($thisMonthOrders) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-0.5">{{ now()->format('M Y') }}</div>
        </div>

        <!-- Net Revenue -->
        <div class="bg-white rounded-2xl border border-emerald-100 bg-emerald-50/20 p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Net Revenue</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                    💰
                </div>
            </div>
            <div class="text-xl font-black text-emerald-600">${{ number_format($netRevenue, 2) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-0.5">Excl. refunds</div>
        </div>

        <!-- Paid Orders -->
        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Completed</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">
                    ✓
                </div>
            </div>
            <div class="text-xl font-black text-emerald-600">{{ number_format($paidOrders) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-0.5">{{ $totalOrders > 0 ? round(($paidOrders/$totalOrders)*100, 1) : 0 }}% total</div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pending</span>
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">
                    ⏳
                </div>
            </div>
            <div class="text-xl font-black text-amber-600">{{ number_format($pendingOrders) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-0.5">Awaiting capture</div>
        </div>

        <!-- Refunded Orders -->
        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Refunded</span>
                <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs">
                    ↩
                </div>
            </div>
            <div class="text-xl font-black text-purple-600">{{ number_format($refundedOrders) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-0.5">Processed</div>
        </div>

        <!-- Failed / Cancelled -->
        <div class="bg-white rounded-2xl border border-gray-150 p-4 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Cancelled</span>
                <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xs">
                    ✕
                </div>
            </div>
            <div class="text-xl font-black text-rose-600">{{ number_format($failedOrders) }}</div>
            <div class="text-[10px] text-gray-400 font-semibold mt-0.5">Failed/Void</div>
        </div>
    </div>

    <!-- 2. Interactive Revenue & Order Trends Chart -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-100">
            <div>
                <h3 class="text-base font-extrabold text-navy tracking-tight">Revenue & Sales Trajectory</h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    Period Revenue: <span class="font-bold text-emerald-600">${{ number_format($chartData['totalRevenue'], 2) }}</span>
                    &nbsp;•&nbsp; Orders: <span class="font-bold text-navy">{{ $chartData['totalCount'] }}</span>
                </p>
            </div>

            <!-- Time Period Switcher -->
            <div class="inline-flex p-1 bg-gray-100 rounded-xl text-xs font-bold space-x-1">
                @foreach([
                    'today' => 'Today',
                    '7days' => '7 Days',
                    '30days' => '30 Days',
                    'this_month' => 'This Month',
                    'this_year' => 'This Year'
                ] as $key => $label)
                    <a href="{{ request()->fullUrlWithQuery(['chart_period' => $key]) }}"
                       class="px-3 py-1.5 rounded-lg transition-all {{ $chartPeriod === $key ? 'bg-white text-navy shadow-sm font-black' : 'text-gray-500 hover:text-navy' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Chart Visualization Area (Pure CSS & SVG Bars) -->
        <div class="mt-6">
            @if(empty($chartData['points']) || ($chartData['totalRevenue'] == 0 && $chartData['totalCount'] == 0))
                <div class="py-12 text-center text-gray-400">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <div class="text-sm font-bold text-gray-600">No completed transactions recorded for this period</div>
                    <div class="text-xs text-gray-400 mt-0.5">Revenue and order points will populate as customers purchase packages.</div>
                </div>
            @else
                <div class="space-y-2">
                    <!-- Bars Container -->
                    <div class="h-44 w-full flex items-end gap-1 sm:gap-2 pt-6 pb-2 px-2 overflow-x-auto border-b border-gray-100">
                        @foreach($chartData['points'] as $pt)
                            @php
                                $heightPercent = $chartData['maxRevenue'] > 0 ? max(4, round(($pt['revenue'] / $chartData['maxRevenue']) * 100)) : 4;
                            @endphp
                            <div class="flex-1 min-w-[28px] max-w-[50px] flex flex-col items-center group relative h-full justify-end">
                                <!-- Tooltip -->
                                <div class="absolute bottom-full mb-2 hidden group-hover:flex flex-col items-center pointer-events-none z-20">
                                    <div class="bg-navy text-white text-[11px] rounded-lg py-1.5 px-2.5 shadow-xl whitespace-nowrap text-center">
                                        <div class="font-bold text-cyan">${{ number_format($pt['revenue'], 2) }}</div>
                                        <div class="text-[9px] text-gray-300">{{ $pt['orders'] }} {{ Str::plural('order', $pt['orders']) }} • {{ $pt['label'] }}</div>
                                    </div>
                                    <div class="w-2 h-2 bg-navy transform rotate-45 -mt-1"></div>
                                </div>

                                <!-- Bar Column -->
                                <div class="w-full bg-cyan/20 hover:bg-cyan rounded-t-md transition-all duration-300 relative group-hover:scale-y-105 origin-bottom cursor-pointer"
                                     style="height: {{ $heightPercent }}%;">
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

    <!-- 3. Enterprise Search & Inline Filter Bar (Single Row Flexbox UI/UX) -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 sm:p-5">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="space-y-3.5">
            <!-- Retain chart_period when searching -->
            <input type="hidden" name="chart_period" value="{{ request('chart_period', $chartPeriod) }}">

            <!-- Main Inline Filter Flexbox -->
            <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                <!-- Primary Search Input (Flex-grow) -->
                <div style="flex: 1 1 300px; min-width: 240px; position: relative;">
                    <div style="position: absolute; top: 0; bottom: 0; left: 14px; display: flex; align-items: center; pointer-events: none; color: #94A3B8;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Order #, Customer, Stripe ID, Exam..."
                           style="width: 100%; height: 42px; padding-left: 42px; padding-right: 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; font-size: 13px; color: #0A1628; outline: none; transition: all 0.2s;"
                           onfocus="this.style.background='#FFFFFF'; this.style.borderColor='#00D4AA'; this.style.boxShadow='0 0 0 3px rgba(0, 212, 170, 0.15)';"
                           onblur="this.style.background='#F8FAFC'; this.style.borderColor='#E2E8F0'; this.style.boxShadow='none';">
                </div>

                <!-- Status Filter -->
                <div style="flex: 0 0 150px; min-width: 130px; position: relative;">
                    <select name="status"
                            style="width: 100%; height: 42px; padding-left: 14px; padding-right: 32px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; font-size: 13px; font-weight: 600; color: #0A1628; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; transition: all 0.2s;"
                            onfocus="this.style.background='#FFFFFF'; this.style.borderColor='#00D4AA';"
                            onblur="this.style.background='#F8FAFC'; this.style.borderColor='#E2E8F0';">
                        <option value="">All Statuses</option>
                        <option value="paid" {{ in_array(request('status'), ['paid', 'completed']) ? 'selected' : '' }}>Paid / Completed</option>
                        <option value="pending" {{ in_array(request('status'), ['pending', 'processing']) ? 'selected' : '' }}>Pending</option>
                        <option value="refunded" {{ in_array(request('status'), ['refunded', 'partially_refunded']) ? 'selected' : '' }}>Refunded</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <div style="position: absolute; top: 0; bottom: 0; right: 12px; display: flex; align-items: center; pointer-events: none; color: #94A3B8;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <!-- Payment Method Filter -->
                <div style="flex: 0 0 150px; min-width: 130px; position: relative;">
                    <select name="payment_method"
                            style="width: 100%; height: 42px; padding-left: 14px; padding-right: 32px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; font-size: 13px; font-weight: 600; color: #0A1628; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; transition: all 0.2s;"
                            onfocus="this.style.background='#FFFFFF'; this.style.borderColor='#00D4AA';"
                            onblur="this.style.background='#F8FAFC'; this.style.borderColor='#E2E8F0';">
                        <option value="">All Gateways</option>
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm }}" {{ request('payment_method') === $pm ? 'selected' : '' }}>{{ strtoupper($pm) }}</option>
                        @endforeach
                    </select>
                    <div style="position: absolute; top: 0; bottom: 0; right: 12px; display: flex; align-items: center; pointer-events: none; color: #94A3B8;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <!-- Date Range Preset -->
                <div style="flex: 0 0 160px; min-width: 130px; position: relative;">
                    <select name="date_range"
                            style="width: 100%; height: 42px; padding-left: 14px; padding-right: 32px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; font-size: 13px; font-weight: 600; color: #0A1628; outline: none; cursor: pointer; appearance: none; -webkit-appearance: none; transition: all 0.2s;"
                            onfocus="this.style.background='#FFFFFF'; this.style.borderColor='#00D4AA';"
                            onblur="this.style.background='#F8FAFC'; this.style.borderColor='#E2E8F0';">
                        <option value="">Date: Anytime</option>
                        <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request('date_range') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="7days" {{ request('date_range') === '7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30days" {{ request('date_range') === '30days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="this_month" {{ request('date_range') === 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="this_year" {{ request('date_range') === 'this_year' ? 'selected' : '' }}>This Year</option>
                    </select>
                    <div style="position: absolute; top: 0; bottom: 0; right: 12px; display: flex; align-items: center; pointer-events: none; color: #94A3B8;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                <!-- Action Buttons: Filter & Reset -->
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button type="submit"
                            style="height: 42px; padding: 0 18px; background: #00D4AA; color: #0A1628; font-weight: 800; font-size: 13px; border-radius: 12px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(0, 212, 170, 0.25); transition: all 0.2s;"
                            onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 16px rgba(0, 212, 170, 0.35)';"
                            onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 12px rgba(0, 212, 170, 0.25)';">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Filter
                    </button>

                    @if(request()->anyFilled(['search', 'status', 'payment_method', 'date_range', 'date_from', 'date_to', 'min_amount', 'max_amount']))
                        <a href="{{ route('admin.orders.index') }}"
                           style="height: 42px; padding: 0 14px; background: #F1F5F9; color: #64748B; font-weight: 700; font-size: 13px; border-radius: 12px; border: 1px solid #E2E8F0; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s;"
                           onmouseover="this.style.background='#E2E8F0'; this.style.color='#0A1628';"
                           onmouseout="this.style.background='#F1F5F9'; this.style.color='#64748B';">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Clear
                        </a>
                    @endif
                </div>
            </div>

            <!-- Quick Status Pill Badges Row -->
            <div class="flex flex-wrap items-center gap-2 pt-1">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mr-1">Quick Status:</span>
                <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}"
                   class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ !request('status') ? 'bg-navy text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    All ({{ $totalOrders }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'paid']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ in_array(request('status'), ['paid', 'completed']) ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    Paid / Completed ({{ $paidOrders }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ in_array(request('status'), ['pending', 'processing']) ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
                    Pending ({{ $pendingOrders }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'refunded']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ in_array(request('status'), ['refunded', 'partially_refunded']) ? 'bg-purple-600 text-white shadow-sm' : 'bg-purple-50 text-purple-700 hover:bg-purple-100' }}">
                    Refunded ({{ $refundedOrders }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'failed']) }}"
                   class="px-3 py-1 rounded-lg text-xs font-bold transition-all {{ request('status') === 'failed' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                    Failed ({{ $failedOrders }})
                </a>
            </div>
        </form>
    </div>

    <!-- 4. Bulk Actions Bar (Triggered when checkboxes are active) -->
    <div x-show="selectedOrders.length > 0" x-cloak
         class="bg-navy text-white px-5 py-3 rounded-2xl shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4 border border-cyan/30">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-xl bg-cyan/20 text-cyan flex items-center justify-center font-extrabold text-sm">
                <span x-text="selectedOrders.length"></span>
            </div>
            <div>
                <span class="font-bold text-sm">Orders Selected</span>
                <span class="text-xs text-gray-400 block sm:inline sm:ml-2">Apply bulk status changes or export selection</span>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <form action="{{ route('admin.orders.bulk-action') }}" method="POST" class="inline-flex space-x-2">
                @csrf
                <template x-for="id in selectedOrders" :key="id">
                    <input type="hidden" name="order_ids[]" :value="id">
                </template>

                <button type="submit" name="action" value="mark_completed" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition shadow">
                    ✓ Mark Completed
                </button>
                <button type="submit" name="action" value="mark_cancelled" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl transition shadow">
                    ✕ Mark Cancelled
                </button>
                <button type="submit" name="action" value="export_selected" class="px-3.5 py-1.5 bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold rounded-xl transition">
                    ⬇ Export Selected
                </button>
            </form>
            <button @click="selectedOrders = []; selectAll = false" class="text-xs text-gray-400 hover:text-white px-2 py-1">
                Deselect
            </button>
        </div>
    </div>

    <!-- 5. Upgraded Orders Data Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <!-- Table Toolbar -->
        <div class="p-4 border-b border-gray-150 flex flex-col sm:flex-row justify-between items-center gap-3 bg-gray-50/50">
            <div class="text-xs font-bold text-navy flex items-center gap-2">
                <span>Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders</span>
            </div>

            <!-- Per Page Selector -->
            <div class="flex items-center space-x-2 text-xs font-semibold text-gray-500">
                <span>Per Page:</span>
                @foreach([10, 25, 50, 100] as $size)
                    <a href="{{ request()->fullUrlWithQuery(['per_page' => $size]) }}"
                       class="px-2 py-1 rounded-lg transition {{ (int)request('per_page', 25) === $size ? 'bg-navy text-white font-bold' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }}">
                        {{ $size }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-150">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3.5 text-left w-10">
                            <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()"
                                   class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                        </th>
                        <th class="px-4 py-3.5 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Order No</th>
                        <th class="px-4 py-3.5 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Customer</th>
                        <th class="px-4 py-3.5 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Items Purchased</th>
                        <th class="px-4 py-3.5 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Financial Breakdown</th>
                        <th class="px-4 py-3.5 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Gateway</th>
                        <th class="px-4 py-3.5 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3.5 text-left text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3.5 text-right text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-150 text-xs">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <!-- Checkbox -->
                            <td class="px-4 py-4">
                                <input type="checkbox" :value="{{ $order->id }}" x-model="selectedOrders"
                                       class="order-checkbox rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                            </td>

                            <!-- Order Number -->
                            <td class="px-4 py-4 font-bold">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="text-navy hover:text-cyan font-mono text-sm transition-colors flex items-center gap-1.5 group">
                                    <span>#{{ $order->order_number }}</span>
                                    <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-cyan transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                @if($order->refunded_amount > 0)
                                    <span class="inline-block mt-0.5 text-[9px] font-bold text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded">
                                        Refunded ${{ number_format($order->refunded_amount, 2) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Customer Info with Avatar Initials -->
                            <td class="px-4 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-xl bg-navy/10 text-navy font-bold flex items-center justify-center text-xs uppercase flex-shrink-0">
                                        {{ $order->user ? strtoupper(substr($order->user->name, 0, 2)) : 'GU' }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-gray-800 truncate">
                                            @if($order->user)
                                                <a href="{{ route('admin.users.show', $order->user_id) }}" class="hover:underline hover:text-cyan">
                                                    {{ $order->user->name }}
                                                </a>
                                            @else
                                                <span class="text-gray-500">{{ $order->billing_name ?: 'Guest' }}</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-400 truncate">
                                            {{ $order->user ? $order->user->email : $order->billing_email }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Items Purchased Breakdown -->
                            <td class="px-4 py-4">
                                <div class="space-y-1">
                                    @php
                                        $itemsList = $order->items;
                                        $firstItem = $itemsList->first();
                                        $extraCount = $itemsList->count() - 1;
                                    @endphp

                                    @if($firstItem)
                                        <div class="font-bold text-navy text-xs truncate max-w-[200px]" title="{{ $firstItem->exam ? $firstItem->exam->exam_name : $firstItem->plan_name }}">
                                            @if($firstItem->exam)
                                                <span class="inline-block px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-mono mr-1">
                                                    {{ $firstItem->exam->exam_code }}
                                                </span>
                                                {{ $firstItem->exam->exam_name }}
                                            @else
                                                <span>{{ $firstItem->plan_name }} Plan</span>
                                            @endif
                                        </div>
                                        @if($extraCount > 0)
                                            <span class="inline-block text-[10px] font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
                                                +{{ $extraCount }} more {{ Str::plural('item', $extraCount) }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 italic">No item records</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Financial Breakdown -->
                            <td class="px-4 py-4">
                                <div class="font-extrabold text-navy text-sm">
                                    ${{ number_format($order->total_amount, 2) }}
                                </div>
                                <div class="text-[10px] text-gray-400 space-x-1">
                                    <span>Sub: ${{ number_format($order->subtotal, 2) }}</span>
                                    @if($order->discount_amount > 0)
                                        <span class="text-rose-500 font-bold">(-${{ number_format($order->discount_amount, 2) }})</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Payment Gateway -->
                            <td class="px-4 py-4">
                                <div class="flex items-center space-x-1.5">
                                    @if(strtolower($order->payment_method) === 'stripe')
                                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                        <span class="font-bold text-gray-700 uppercase text-[10px]">Stripe</span>
                                    @elseif(strtolower($order->payment_method) === 'paypal')
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        <span class="font-bold text-gray-700 uppercase text-[10px]">PayPal</span>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                        <span class="font-bold text-gray-700 uppercase text-[10px]">{{ $order->payment_method ?: 'Direct' }}</span>
                                    @endif
                                </div>
                                @if($order->stripe_payment_intent_id)
                                    <span class="text-[9px] text-gray-400 font-mono block truncate max-w-[100px]" title="{{ $order->stripe_payment_intent_id }}">
                                        {{ substr($order->stripe_payment_intent_id, 0, 14) }}...
                                    </span>
                                @endif
                            </td>

                            <!-- Status Badge -->
                            <td class="px-4 py-4">
                                @php
                                    $statusMap = [
                                        'completed' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => '●'],
                                        'paid'      => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'icon' => '●'],
                                        'pending'   => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'icon' => '⏳'],
                                        'processing'=> ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => '⚙'],
                                        'refunded'  => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'icon' => '↩'],
                                        'partially_refunded' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'icon' => '◐'],
                                        'failed'    => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'icon' => '✕'],
                                        'cancelled' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-300', 'icon' => '—'],
                                    ];
                                    $st = $statusMap[strtolower($order->payment_status)] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-200', 'icon' => '●'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $st['bg'] }} {{ $st['text'] }} {{ $st['border'] }}">
                                    <span class="mr-1 text-[9px]">{{ $st['icon'] }}</span>
                                    {{ strtoupper(str_replace('_', ' ', $order->payment_status)) }}
                                </span>
                            </td>

                            <!-- Date -->
                            <td class="px-4 py-4 whitespace-nowrap text-gray-500">
                                <div class="font-semibold text-gray-700">{{ $order->created_at->format('M d, Y') }}</div>
                                <div class="text-[10px] text-gray-400">{{ $order->created_at->format('h:i A') }}</div>
                            </td>

                            <!-- Action Menu -->
                            <td class="px-4 py-4 text-right">
                                <div class="inline-flex items-center space-x-1" x-data="{ open: false }">
                                    <a href="{{ route('admin.orders.show', $order->id) }}"
                                       class="px-2.5 py-1 bg-navy/5 hover:bg-navy text-navy hover:text-white font-bold rounded-lg text-xs transition">
                                        View
                                    </a>

                                    <div class="relative">
                                        <button @click="open = !open" @click.away="open = false"
                                                class="p-1 hover:bg-gray-200 rounded-lg text-gray-500 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                        </button>

                                        <div x-show="open" x-cloak
                                             class="absolute right-0 mt-2 w-44 bg-white rounded-xl shadow-xl border border-gray-150 py-1.5 z-30 text-left">
                                            <a href="{{ route('admin.orders.invoice', $order->id) }}" class="block px-3.5 py-1.5 text-xs text-gray-700 hover:bg-gray-50 font-medium">
                                                ⬇ Download PDF Invoice
                                            </a>
                                            <a href="{{ route('admin.orders.print', $order->id) }}" target="_blank" class="block px-3.5 py-1.5 text-xs text-gray-700 hover:bg-gray-50 font-medium">
                                                🖨 Print Invoice
                                            </a>
                                            <a href="{{ route('admin.orders.show', $order->id) }}" class="block px-3.5 py-1.5 text-xs text-purple-600 hover:bg-purple-50 font-bold">
                                                ↩ Process Refund
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <div class="text-sm font-bold text-gray-600">No orders match your filter criteria</div>
                                <div class="text-xs text-gray-400 mt-1">Try resetting filters or adjusting search keywords.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-150 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-xs text-gray-500">
                    Showing <span class="font-bold text-navy">{{ $orders->firstItem() }}</span> to <span class="font-bold text-navy">{{ $orders->lastItem() }}</span> of <span class="font-bold text-navy">{{ $orders->total() }}</span> results
                </div>
                <div>
                    {{ $orders->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
