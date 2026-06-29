@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Manage Subscriptions</h1>
    </div>

    <!-- Subscriptions Table -->
    <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-150">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Plan Name</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Cycle</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Expiry</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-150 text-xs">
                @forelse($subscriptions as $sub)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-700">{{ $sub->user->name }}</div>
                            <div class="text-[10px] text-gray-400">{{ $sub->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 font-bold text-navy">
                            {{ ucfirst($sub->plan_name) }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-700">
                            ${{ number_format($sub->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 uppercase text-[10px] font-bold text-gray-400">
                            {{ $sub->billing_cycle }}
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-semibold">
                            {{ $sub->current_period_end ? $sub->current_period_end->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $sub->status === 'active' ? 'bg-green-100 text-green-700' : ($sub->status === 'cancelled' ? 'bg-gray-100 text-gray-600' : 'bg-red-100 text-red-700') }}">
                                {{ $sub->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold space-x-2">
                            <a href="{{ route('admin.subscriptions.show', $sub->id) }}" class="text-cyan hover:underline">View</a>
                            @if($sub->status === 'active')
                                <form action="{{ route('admin.subscriptions.destroy', $sub->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to cancel this subscription?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                            No subscription records found in database.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Pagination -->
        @if($subscriptions->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-150">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
