@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header banner -->
    <div class="bg-navy rounded-lg p-6 text-white shadow-sm flex flex-col md:flex-row justify-between items-center gap-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight mb-2">Welcome back, {{ auth()->user()->name }}!</h1>
            <p class="text-sm text-gray-300">Access your study guides, practice tests, and track your progress all in one place.</p>
        </div>
        @if(!$activeSubscription)
            <a href="{{ url('/pricing') }}" class="bg-orange hover:bg-opacity-95 text-white font-bold text-sm px-6 py-2.5 rounded shadow transition">
                Upgrade to Pro Plan
            </a>
        @else
            <div class="bg-gray-800 border border-gray-700 px-4 py-2 rounded text-xs">
                <span class="block text-gray-400 font-bold uppercase tracking-wider">Active Plan</span>
                <span class="text-sm font-bold text-cyan">{{ $activeSubscription->plan_name }} ({{ ucfirst($activeSubscription->billing_cycle) }})</span>
            </div>
        @endif
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Purchased -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Exams Purchased</div>
            <div class="flex items-baseline space-x-2">
                <span class="text-3xl font-extrabold text-navy dark:text-white">{{ $purchasedCount }}</span>
                <span class="text-xs text-gray-500">Guides</span>
            </div>
        </div>

        <!-- Active Sub -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Subscription status</div>
            <div class="flex items-baseline space-x-2">
                @if($activeSubscription)
                    <span class="text-lg font-bold text-green-600 dark:text-green-400">ACTIVE ({{ $activeSubscription->plan_name }})</span>
                @else
                    <span class="text-lg font-bold text-gray-500">NO SUBSCRIPTION</span>
                @endif
            </div>
        </div>

        <!-- Downloads Remaining -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Downloads Remaining</div>
            <div class="flex items-baseline space-x-2">
                <span class="text-3xl font-extrabold text-navy dark:text-white">{{ $downloadsRemaining }}</span>
                <span class="text-xs text-gray-500">PDFs</span>
            </div>
        </div>

        <!-- Tests Taken -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm">
            <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Attempts Completed</div>
            <div class="flex items-baseline space-x-2">
                <span class="text-3xl font-extrabold text-navy dark:text-white">{{ $testsTakenCount }}</span>
                <span class="text-xs text-gray-500">Sessions</span>
            </div>
        </div>
    </div>

    <!-- Main Grid Details (Recent activity + Promo card) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Activity Log Feed (70%) -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm space-y-6">
            <h3 class="text-lg font-bold text-navy dark:text-white border-b border-gray-150 dark:border-gray-700 pb-3">Recent Account Activity</h3>
            @if(count($activities) > 0)
                <div class="flow-root">
                    <ul class="-mb-8">
                        @foreach($activities as $index => $activity)
                            <li>
                                <div class="relative pb-8">
                                    @if($index !== count($activities) - 1)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-navy dark:bg-gray-750 flex items-center justify-center text-xs font-bold text-cyan">
                                                ★
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-xs font-bold text-navy dark:text-white">{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</p>
                                                <p class="text-sm text-gray-500 mt-0.5">{{ $activity->description }}</p>
                                            </div>
                                            <div class="text-right text-xs whitespace-nowrap text-gray-400">
                                                <time>{{ $activity->created_at->diffForHumans() }}</time>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="text-center py-10 text-gray-400 text-sm">
                    No recent activity logs. Take a practice exam or download a PDF guide to begin!
                </div>
            @endif
        </div>

        <!-- Right: Action Callouts (30%) -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-navy text-white rounded-lg p-6 shadow-sm border border-gray-800 text-center space-y-4">
                <h3 class="text-lg font-bold">Interactive Test Engine</h3>
                <p class="text-xs text-gray-300 leading-relaxed">Study in Practice, Exam, or Review modes using our browser-based simulator. No downloads required.</p>
                <a href="{{ url('/dashboard/test-engine') }}" class="inline-block bg-cyan hover-bg-cyan text-navy font-bold text-xs px-6 py-2.5 rounded shadow transition w-full">
                    Launch Test Engine
                </a>
            </div>
            
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm space-y-3">
                <h4 class="text-sm font-bold text-navy dark:text-white">Pass Guarantee</h4>
                <p class="text-xs text-gray-500 leading-relaxed">Every guide comes with a 100% money-back guarantee. If you fail, submit your score sheet for a full refund immediately.</p>
            </div>
        </div>

    </div>
</div>
@endsection
