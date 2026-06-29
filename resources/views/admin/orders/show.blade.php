@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Order Details: #{{ $order->order_number }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-navy hover:underline">
            ← Back to Orders
        </a>
    </div>

    <!-- Order Info Card -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm space-y-6">
        <div class="flex justify-between items-start border-b border-gray-150 pb-4">
            <div>
                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Order Status</span>
                <span class="inline-block mt-1 px-3 py-0.5 rounded-full text-xs font-bold uppercase {{ $order->payment_status === 'completed' ? 'bg-green-100 text-green-700' : ($order->payment_status === 'refunded' ? 'bg-gray-100 text-gray-700 border border-dashed border-gray-300' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ $order->payment_status }}
                </span>
            </div>
            
            @if($order->payment_status === 'completed')
                <form action="{{ route('admin.orders.refund', $order->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to refund this order? This will revoke all exam accesses linked to it.')">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-2 px-4 rounded shadow transition">
                        Process Refund & Revoke Access
                    </button>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div>
                <h4 class="font-bold text-navy mb-2">Customer Details</h4>
                <p class="text-gray-700 font-medium">{{ $order->user->name }}</p>
                <p class="text-gray-500 text-xs font-semibold">{{ $order->user->email }}</p>
            </div>
            <div>
                <h4 class="font-bold text-navy mb-2">Billing Information</h4>
                <p class="text-gray-700 font-medium">Billing Name: {{ $order->billing_name }}</p>
                <p class="text-gray-500 text-xs font-semibold">Billing Email: {{ $order->billing_email }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm pt-4 border-t border-gray-150">
            <div>
                <h4 class="font-bold text-navy mb-2">Payment Details</h4>
                <p class="text-gray-700 font-medium">Method: {{ strtoupper($order->payment_method) }}</p>
                @if($order->stripe_payment_intent_id)
                    <p class="text-gray-400 text-xs font-mono">Stripe ID: {{ $order->stripe_payment_intent_id }}</p>
                @endif
                @if($order->paypal_order_id)
                    <p class="text-gray-400 text-xs font-mono">PayPal ID: {{ $order->paypal_order_id }}</p>
                @endif
            </div>
            <div>
                <h4 class="font-bold text-navy mb-2">Created Date</h4>
                <p class="text-gray-700 font-medium">{{ $order->created_at->format('F d, Y h:i A') }}</p>
            </div>
        </div>

        <!-- Order Items -->
        <div class="border-t border-gray-150 pt-6">
            <h4 class="font-bold text-navy mb-4">Purchased Items</h4>
            <div class="border border-gray-200 rounded overflow-hidden">
                <table class="min-w-full divide-y divide-gray-150 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-bold text-gray-400 uppercase">Item</th>
                            <th class="px-6 py-3 text-left font-bold text-gray-400 uppercase">Type</th>
                            <th class="px-6 py-3 text-right font-bold text-gray-400 uppercase">Price</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-150">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-6 py-4 font-bold text-navy">
                                    @if($item->exam)
                                        {{ $item->exam->vendor->name }} {{ $item->exam->exam_code }} - {{ $item->exam->exam_name }}
                                    @else
                                        {{ $item->plan_name }} Plan
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-500 font-semibold uppercase text-[10px]">
                                    {{ $item->item_type }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-700">
                                    ${{ number_format($item->price, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        @if($order->discount_amount > 0)
                            <tr class="bg-gray-50">
                                <td colspan="2" class="px-6 py-3 text-right font-bold text-green-600">Discount</td>
                                <td class="px-6 py-3 text-right font-bold text-green-600">-${{ number_format($order->discount_amount, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="bg-gray-50">
                            <td colspan="2" class="px-6 py-3 text-right font-extrabold text-navy">Total Paid</td>
                            <td class="px-6 py-3 text-right font-extrabold text-navy text-sm">${{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
