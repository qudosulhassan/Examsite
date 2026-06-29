@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    <!-- Breadcrumbs -->
    <nav class="flex text-xs text-gray-400 space-x-2">
        <a href="{{ url('/dashboard') }}" class="hover:text-cyan">Dashboard</a>
        <span>/</span>
        <a href="{{ route('dashboard.test-engine') }}" class="hover:text-cyan">Test Engine</a>
        <span>/</span>
        <span class="text-gray-500 font-semibold">{{ $exam->exam_code }} Lobby</span>
    </nav>

    <!-- Lobby Box -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-8 shadow-sm space-y-6">
        <div>
            <span class="bg-cyan bg-opacity-15 text-navy dark:text-cyan font-bold text-xs px-2.5 py-0.5 rounded border border-cyan border-opacity-30">{{ $exam->exam_code }}</span>
            <h1 class="text-2xl font-extrabold text-navy dark:text-white mt-2">{{ $exam->exam_name }} Simulator Lobby</h1>
            <p class="text-sm text-gray-500 mt-1">Configure your testing session parameters before starting.</p>
        </div>

        <form action="{{ route('dashboard.test-engine.start', $exam->id) }}" method="POST" class="space-y-6">
            @csrf

            <!-- Mode Selection -->
            <div class="space-y-3">
                <label class="block text-sm font-bold text-navy dark:text-white uppercase tracking-wider">Select Simulator Mode</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Practice Mode -->
                    <label class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex flex-col justify-between cursor-pointer hover:border-cyan transition">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-green-600">Practice Mode</span>
                            <input type="radio" name="mode" value="practice" checked class="text-cyan focus:ring-cyan h-4 w-4">
                        </div>
                        <p class="text-xs text-gray-400">See correct answers and explanations immediately after answering each question. Great for learning.</p>
                    </label>

                    <!-- Exam Mode -->
                    <label class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex flex-col justify-between cursor-pointer hover:border-cyan transition">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-blue-600">Exam Mode</span>
                            <input type="radio" name="mode" value="exam" class="text-cyan focus:ring-cyan h-4 w-4">
                        </div>
                        <p class="text-xs text-gray-400">Simulates real exam constraints. Timed, answers and explanations are hidden until final submission.</p>
                    </label>

                    <!-- Review Mode -->
                    <label class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex flex-col justify-between cursor-pointer hover:border-cyan transition {{ !$lastAttempt ? 'opacity-50 cursor-not-allowed' : '' }}">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-orange">Review Mode</span>
                            <input type="radio" name="mode" value="review" {{ !$lastAttempt ? 'disabled' : '' }} class="text-cyan focus:ring-cyan h-4 w-4">
                        </div>
                        <p class="text-xs text-gray-400">Re-attempt incorrect or flagged questions from your previous attempt. Helps build focus on weak spots.</p>
                    </label>
                </div>
            </div>

            <!-- Length Selection -->
            <div class="space-y-3" x-data="{ count: 10 }">
                <label class="block text-sm font-bold text-navy dark:text-white uppercase tracking-wider">Number of Questions</label>
                <div class="flex items-center space-x-6">
                    <input type="range" name="count" min="5" max="{{ min($questionCount, 100) }}" x-model="count" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-cyan">
                    <span class="text-lg font-bold text-navy dark:text-white w-12 text-center" x-text="count"></span>
                </div>
                <div class="flex justify-between text-[10px] text-gray-400">
                    <span>Min: 5 Questions</span>
                    <span>Max: {{ min($questionCount, 100) }} (Total Available: {{ $questionCount }})</span>
                </div>
            </div>

            <!-- CTA -->
            <div class="pt-4 border-t border-gray-150 dark:border-gray-700 flex justify-between items-center">
                <a href="{{ route('dashboard.test-engine') }}" class="text-xs font-bold text-gray-400 hover:text-navy dark:hover:text-white transition">
                    &larr; Back to Selection
                </a>
                <button type="submit" class="bg-orange hover-bg-orange text-white font-bold py-3 px-8 rounded shadow transition text-sm">
                    Start Simulator Session
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
