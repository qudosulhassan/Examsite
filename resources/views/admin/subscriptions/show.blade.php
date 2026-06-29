@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Subscription Details</h1>
        <a href="{{ route('admin.subscriptions.index') }}" class="text-sm text-navy hover:underline">
            ← Back to Subscriptions
        </a>
    </div>

    <!-- Details Card -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm space-y-6">
        <div class="flex justify-between items-start border-b border-gray-150 pb-4">
            <div>
                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Subscription Status</span>
                <span class="inline-block mt-1 px-3 py-0.5 rounded-full text-xs font-bold uppercase {{ $subscription->status === 'active' ? 'bg-green-100 text-green-700' : ($subscription->status === 'cancelled' ? 'bg-gray-100 text-gray-600' : 'bg-red-100 text-red-700') }}">
                    {{ $subscription->status }}
                </span>
            </div>
            
            @if($subscription->status === 'active')
                <form action="{{ route('admin.subscriptions.destroy', $subscription->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this subscription?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-2 px-4 rounded shadow transition">
                        Terminate Subscription Access
                    </button>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
            <div>
                <h4 class="font-bold text-navy mb-2">Subscriber Profile</h4>
                <p class="text-gray-700 font-medium">{{ $subscription->user->name }}</p>
                <p class="text-gray-500 text-xs font-semibold">{{ $subscription->user->email }}</p>
            </div>
            <div>
                <h4 class="font-bold text-navy mb-2">Plan Config</h4>
                <p class="text-gray-700 font-medium">Plan Name: {{ ucfirst($subscription->plan_name) }}</p>
                <p class="text-gray-500 text-xs font-semibold">Billing Interval: {{ ucfirst($subscription->billing_cycle) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm pt-4 border-t border-gray-150">
            <div>
                <h4 class="font-bold text-navy mb-2">Billing Reference</h4>
                <p class="text-gray-700 font-medium">Price: ${{ number_format($subscription->amount, 2) }} USD</p>
                @if($subscription->stripe_subscription_id)
                    <p class="text-gray-400 text-xs font-mono">Stripe Sub ID: {{ $subscription->stripe_subscription_id }}</p>
                    <p class="text-gray-400 text-xs font-mono">Stripe Cus ID: {{ $subscription->stripe_customer_id }}</p>
                @endif
                @if($subscription->paypal_subscription_id)
                    <p class="text-gray-400 text-xs font-mono">PayPal Sub ID: {{ $subscription->paypal_subscription_id }}</p>
                @endif
            </div>
            <div>
                <h4 class="font-bold text-navy mb-2">Duration Boundaries</h4>
                <p class="text-gray-700 text-xs font-semibold">Start Date: {{ $subscription->current_period_start ? $subscription->current_period_start->format('F d, Y') : 'N/A' }}</p>
                <p class="text-gray-700 text-xs font-semibold mt-1">End Date: {{ $subscription->current_period_end ? $subscription->current_period_end->format('F d, Y') : 'N/A' }}</p>
                @if($subscription->cancelled_at)
                    <p class="text-red-500 text-xs font-semibold mt-2">Cancelled Date: {{ $subscription->cancelled_at->format('F d, Y h:i A') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
