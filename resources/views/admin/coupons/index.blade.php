@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Manage Coupons & Discounts</h1>
        <a href="{{ route('admin.coupons.create') }}" class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2 px-4 rounded shadow transition">
            + Create New Coupon
        </a>
    </div>

    <!-- Coupons Table -->
    <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-150">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Coupon Code</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Discount</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Min Spend</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Uses</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Applicable To</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Expiry</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-150 text-xs">
                @forelse($coupons as $coupon)
                    <tr>
                        <td class="px-6 py-4 font-bold text-navy uppercase">
                            {{ $coupon->code }}
                        </td>
                        <td class="px-6 py-4 text-gray-700 font-bold">
                            {{ $coupon->discount_type === 'percentage' ? $coupon->discount_value . '%' : '$' . number_format($coupon->discount_value, 2) }}
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-semibold">
                            ${{ number_format($coupon->min_order_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-semibold">
                            {{ $coupon->used_count }} / {{ $coupon->max_uses ?: '∞' }}
                        </td>
                        <td class="px-6 py-4 text-gray-400 uppercase text-[9px] font-bold">
                            {{ str_replace('_', ' ', $coupon->applicable_to) }}
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-semibold">
                            {{ $coupon->expires_at ? $coupon->expires_at->format('M d, Y') : 'Never' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $coupon->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2 font-bold">
                            <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="text-cyan hover:underline">Edit</a>
                            <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this coupon?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-400">
                            No discount coupons found in database.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        @if($coupons->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-150">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
