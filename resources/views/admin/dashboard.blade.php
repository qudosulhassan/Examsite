@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Admin Dashboard</h1>
        <span class="text-sm text-gray-500">Live Statistics Feed</span>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue Today -->
        <div class="bg-white rounded-lg border border-gray-250 p-6 shadow-sm flex items-center justify-between">
            <div>
                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Revenue Today</span>
                <span class="text-2xl font-extrabold text-navy mt-1 block">${{ number_format($revenueToday, 2) }}</span>
            </div>
            <div class="h-12 w-12 rounded bg-green-50 text-green-500 flex items-center justify-center text-xl font-bold">
                💰
            </div>
        </div>

        <!-- Revenue Month -->
        <div class="bg-white rounded-lg border border-gray-250 p-6 shadow-sm flex items-center justify-between">
            <div>
                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Revenue This Month</span>
                <span class="text-2xl font-extrabold text-navy mt-1 block">${{ number_format($revenueMonth, 2) }}</span>
            </div>
            <div class="h-12 w-12 rounded bg-cyan bg-opacity-10 text-cyan flex items-center justify-center text-xl font-bold">
                📅
            </div>
        </div>

        <!-- Total Users -->
        <div class="bg-white rounded-lg border border-gray-250 p-6 shadow-sm flex items-center justify-between">
            <div>
                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Total Users</span>
                <span class="text-2xl font-extrabold text-navy mt-1 block">{{ $totalUsers }}</span>
                <span class="text-[10px] text-gray-400 font-semibold">+{{ $newUsers30Days }} in last 30 days</span>
            </div>
            <div class="h-12 w-12 rounded bg-indigo-50 text-indigo-500 flex items-center justify-center text-xl font-bold">
                👥
            </div>
        </div>

        <!-- Active Subscriptions -->
        <div class="bg-white rounded-lg border border-gray-250 p-6 shadow-sm flex items-center justify-between">
            <div>
                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Active Subscriptions</span>
                <span class="text-2xl font-extrabold text-navy mt-1 block">{{ $activeSubscriptions }}</span>
            </div>
            <div class="h-12 w-12 rounded bg-orange bg-opacity-10 text-orange flex items-center justify-center text-xl font-bold">
                🔄
            </div>
        </div>
    </div>

    <!-- Data Tables Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Recent Orders (2/3 width) -->
        <div class="lg:col-span-2 bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="text-sm font-bold text-navy">Recent Customer Orders</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-150">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Order No</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Method</th>
                                <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-150 text-xs">
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td class="px-6 py-4 font-bold text-navy">
                                        <a href="{{ url('/admin/orders/' . $order->id) }}" class="hover:text-cyan">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-700">{{ $order->user->name }}</div>
                                        <div class="text-[10px] text-gray-400">{{ $order->user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-700">
                                        ${{ number_format($order->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 uppercase text-[10px] font-bold text-gray-400">
                                        {{ $order->payment_method }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $order->payment_status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ $order->payment_status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                        No recent orders found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="border-t border-gray-150 bg-gray-50 px-6 py-3 text-right">
                <a href="{{ url('/admin/orders') }}" class="text-xs font-bold text-cyan hover:text-opacity-80 transition">
                    View All Orders →
                </a>
            </div>
        </div>

        <!-- Top Selling Exams (1/3 width) -->
        <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="text-sm font-bold text-navy">Top Certifications</h3>
                </div>
                <div class="divide-y divide-gray-150 text-xs">
                    @forelse($topExams as $index => $exam)
                        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                            <div class="flex items-center space-x-3">
                                <span class="h-6 w-6 rounded-full bg-navy text-white text-[10px] font-bold flex items-center justify-center">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <span class="font-bold text-navy block">{{ $exam->exam_code }}</span>
                                    <span class="text-[10px] text-gray-400">{{ Str::limit($exam->exam_name, 25) }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-navy block">{{ $exam->sales_count }} sales</span>
                                <span class="text-[10px] text-gray-400 font-semibold">${{ number_format($exam->total_revenue, 2) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-400">
                            No statistics available yet.
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="border-t border-gray-150 bg-gray-50 px-6 py-3 text-right">
                <a href="{{ url('/admin/exams') }}" class="text-xs font-bold text-cyan hover:text-opacity-80 transition">
                    Manage Exams →
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
