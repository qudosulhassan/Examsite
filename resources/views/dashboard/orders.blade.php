@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <div class="pb-4 border-b border-gray-200 dark:border-gray-700">
        <h1 class="text-2xl font-bold tracking-tight text-navy dark:text-white">Billing & Orders</h1>
        <p class="text-sm text-gray-500">Track your invoices, order history, and manage active web test engine subscription plans.</p>
    </div>

    <!-- Active Subscriptions Block -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm">
        <h3 class="text-lg font-bold text-navy dark:text-white mb-6">Active Subscription</h3>
        
        @php
            // Pull subscription from controller variables or check user relationship
            $activeSub = auth()->user()->subscriptions()->where('status', 'active')->first();
        @endphp

        @if($activeSub)
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 p-4 bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="space-y-1">
                    <span class="bg-cyan bg-opacity-15 text-navy dark:text-cyan text-xs font-bold px-2 py-0.5 rounded border border-cyan border-opacity-35">{{ $activeSub->plan_name }} Plan</span>
                    <h4 class="text-base font-bold text-navy dark:text-white pt-1">
                        ${{ number_format($activeSub->amount, 2) }} <span class="text-xs text-gray-400">/ {{ $activeSub->billing_cycle }}</span>
                    </h4>
                    <p class="text-xs text-gray-500">Next renewal date: {{ $activeSub->current_period_end ? $activeSub->current_period_end->format('M d, Y') : 'N/A' }}</p>
                </div>
                
                <div class="flex items-center space-x-3 w-full md:w-auto">
                    <!-- Stripe Customer Portal link -->
                    <a href="#" class="w-full md:w-auto border border-gray-300 text-navy hover:bg-gray-100 text-xs font-bold py-2.5 px-4 rounded text-center transition">
                        Manage Payment Card
                    </a>
                    <button class="w-full md:w-auto text-red-500 hover:text-red-700 text-xs font-bold py-2.5 px-4 rounded border border-red-200 hover:bg-red-50 text-center transition">
                        Cancel Plan
                    </button>
                </div>
            </div>
        @else
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 p-4 bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div>
                    <h4 class="text-sm font-bold text-navy dark:text-white">No active subscription plan found.</h4>
                    <p class="text-xs text-gray-500 mt-1">Unlock browser-based exam simulator access for all 3,500+ certification guides.</p>
                </div>
                <a href="{{ url('/pricing') }}" class="bg-orange hover:bg-opacity-95 text-white text-xs font-bold py-2.5 px-6 rounded shadow transition">
                    View Pricing Plans
                </a>
            </div>
        @endif
    </div>

    <!-- Orders History Table -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm">
        <h3 class="text-lg font-bold text-navy dark:text-white mb-6">Order History</h3>
        
        @if(count($orders) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-left text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-750 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-3">Order Number</th>
                            <th scope="col" class="px-6 py-3">Purchased Date</th>
                            <th scope="col" class="px-6 py-3">Items Included</th>
                            <th scope="col" class="px-6 py-3">Total Amount</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3 text-right">Invoice</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-navy dark:text-gray-200">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-navy dark:text-white">{{ $order->order_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                    {{ $order->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-xs font-medium">
                                    <ul class="space-y-0.5">
                                        @foreach($order->items as $item)
                                            <li>
                                                @if($item->item_type === 'subscription')
                                                    Subscription ({{ $item->plan_name }})
                                                @elseif($item->exam)
                                                    {{ $item->exam->exam_code }} ({{ strtoupper($item->item_type) }})
                                                @else
                                                    Study Guide Package
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-navy dark:text-white">${{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider 
                                        {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $order->payment_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                    <a href="{{ route('dashboard.orders.invoice', $order->id) }}" class="text-cyan hover:underline font-bold">
                                        Download PDF
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-10 text-gray-400 text-sm">
                No orders placed yet. Purchase a guide package to see invoices here.
            </div>
        @endif
    </div>
</div>
@endsection
