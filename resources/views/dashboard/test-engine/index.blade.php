@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-4 border-b border-gray-200 dark:border-gray-700">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-navy dark:text-white">IT Certification Test Simulator</h1>
            <p class="text-sm text-gray-500">Select any exam below to launch our browser-based interactive testing engine.</p>
        </div>
        @if(!$activeSub)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded text-xs text-yellow-800 flex items-center space-x-2">
                <span>⚠️ Limited Access.</span>
                <a href="{{ url('/pricing') }}" class="font-bold underline">Upgrade for Unlimited Access</a>
            </div>
        @endif
    </div>

    @if(count($exams) > 0)
        <!-- Exams Simulator Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($exams as $exam)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-md transition flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="bg-cyan bg-opacity-15 text-navy dark:text-cyan font-bold text-xs px-2.5 py-0.5 rounded border border-cyan border-opacity-30">{{ $exam->exam_code }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $exam->difficulty }}</span>
                        </div>
                        <h3 class="font-bold text-navy dark:text-white text-base mb-2 line-clamp-2 h-12">{{ $exam->exam_name }}</h3>
                        <p class="text-xs text-gray-400 font-semibold mb-4">{{ $exam->vendor ? $exam->vendor->name : '' }}</p>
                    </div>

                    <div class="pt-4 border-t border-gray-150 dark:border-gray-700 flex justify-between items-center">
                        <div class="text-xs text-gray-400">
                            <span>❓ {{ $exam->question_count }} Questions</span>
                        </div>
                        <a href="{{ route('dashboard.test-engine.lobby', $exam->slug) }}" class="bg-cyan hover-bg-cyan text-navy text-xs font-bold py-2 px-4 rounded shadow transition">
                            Configure Simulator
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-16 text-center text-gray-500">
            <p class="text-lg font-semibold mb-2">No simulator access available.</p>
            <p class="text-sm text-gray-400 mb-6 max-w-sm mx-auto">Purchase a single test engine guide or upgrade to a premium subscription plan to unlock access.</p>
            <a href="{{ url('/pricing') }}" class="bg-navy hover:bg-opacity-95 text-white font-bold text-xs px-6 py-3 rounded shadow transition">
                View Pricing & Plans
            </a>
        </div>
    @endif
</div>
@endsection
