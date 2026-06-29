@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Manage Customer Orders</h1>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-150">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Order No</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Amount</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Method</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-150 text-xs">
                @forelse($orders as $order)
                    <tr>
                        <td class="px-6 py-4 font-bold text-navy">
                            {{ $order->order_number }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-700">{{ $order->user->name }}</div>
                            <div class="text-[10px] text-gray-400">{{ $order->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-700">
                            ${{ number_format($order->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 uppercase text-[10px] font-bold text-gray-400">
                            {{ $order->payment_method }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $order->payment_status === 'completed' ? 'bg-green-100 text-green-700' : ($order->payment_status === 'refunded' ? 'bg-gray-100 text-gray-700 border border-dashed border-gray-300' : 'bg-yellow-100 text-yellow-700') }}">
                                {{ $order->payment_status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="text-cyan hover:underline">View Details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            No orders found in database.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-150">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
