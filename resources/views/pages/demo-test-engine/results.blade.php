@extends('layouts.public')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 py-12 px-4 sm:px-6">
    <!-- Breadcrumbs -->
    <nav class="flex text-xs text-gray-400 space-x-2">
        <a href="{{ url('/') }}" class="hover:text-cyan">Home</a>
        <span>/</span>
        <a href="{{ route('public.test-engine') }}" class="hover:text-cyan">Test Engine</a>
        <span>/</span>
        <span class="text-gray-500 font-semibold">Demo Results #{{ $attempt->id }}</span>
    </nav>

    <!-- Score Overview Banner -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-8 shadow-sm text-center space-y-6">
        <div>
            @if($attempt->passed)
                <div class="inline-flex items-center justify-center h-20 w-20 rounded-full bg-green-100 text-green-600 mb-4 text-3xl font-bold">
                    ✓
                </div>
                <h1 class="text-3xl font-extrabold text-green-600 dark:text-green-400">Congratulations! You Passed.</h1>
            @else
                <div class="inline-flex items-center justify-center h-20 w-20 rounded-full bg-red-100 text-red-600 mb-4 text-3xl font-bold">
                    ✗
                </div>
                <h1 class="text-3xl font-extrabold text-red-600 dark:text-red-400">Exam Failed. Try Again.</h1>
            @endif
            <p class="text-sm text-gray-500 mt-2">{{ $exam->exam_code }} - {{ $exam->exam_name }} ({{ ucfirst($attempt->mode) }} Attempt)</p>
        </div>

        <!-- Graphical Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center max-w-4xl mx-auto pt-6 border-t border-gray-150 dark:border-gray-700">
            
            <!-- Circular Chart -->
            <div class="flex justify-center md:col-span-1">
                <div class="relative w-40 h-40">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        <!-- Background circle -->
                        <circle cx="50" cy="50" r="40" fill="transparent" stroke="#e5e7eb" stroke-width="10" />
                        <!-- Progress circle -->
                        @php
                            $circumference = 2 * pi() * 40;
                            $offset = $circumference - ($attempt->score_percentage / 100) * $circumference;
                            $strokeColor = $attempt->passed ? '#10B981' : '#EF4444'; // green-500 or red-500
                        @endphp
                        <circle cx="50" cy="50" r="40" fill="transparent" stroke="{{ $strokeColor }}" stroke-width="10" 
                                stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}" stroke-linecap="round" style="transition: stroke-dashoffset 1s ease-out;" />
                    </svg>
                    <!-- Center text -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-3xl font-extrabold {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">{{ number_format($attempt->score_percentage, 0) }}%</span>
                        <span class="text-xs text-gray-400 font-semibold uppercase mt-1">Score</span>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="md:col-span-2 grid grid-cols-2 md:grid-cols-3 gap-y-6 gap-x-4 text-left">
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase">Passing Score</span>
                    <span class="text-2xl font-extrabold text-navy dark:text-white mt-1">{{ $exam->passing_score }}%</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase">Total Questions</span>
                    <span class="text-2xl font-extrabold text-navy dark:text-white mt-1">{{ $attempt->total_questions }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase">Time Spent</span>
                    @php
                        $mins = floor($attempt->time_taken_seconds / 60);
                        $secs = $attempt->time_taken_seconds % 60;
                    @endphp
                    <span class="text-2xl font-extrabold text-navy dark:text-white mt-1">{{ $mins }}m {{ $secs }}s</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-green-500 uppercase">Correct</span>
                    <span class="text-2xl font-extrabold text-green-600 mt-1">{{ $attempt->correct }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-red-400 uppercase">Incorrect</span>
                    <span class="text-2xl font-extrabold text-red-500 mt-1">{{ $attempt->total_questions - $attempt->correct - $attempt->skipped }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase">Skipped</span>
                    <span class="text-2xl font-extrabold text-gray-500 mt-1">{{ $attempt->skipped }}</span>
                </div>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="pt-6 border-t border-gray-150 dark:border-gray-700 flex flex-wrap justify-center gap-4">
            <a href="{{ route('public.demo-test-engine.lobby', $exam->slug) }}" class="bg-orange hover-bg-orange text-white text-sm font-bold py-2.5 px-6 rounded shadow transition">
                Retake Demo Exam
            </a>
            <a href="{{ route('public.test-engine') }}" class="border border-gray-300 text-navy hover:bg-gray-50 text-sm font-bold py-2.5 px-6 rounded transition">
                Choose Another Exam
            </a>
        </div>
    </div>

    <!-- Question Answers Review Panel -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 shadow-sm space-y-6">
        <h3 class="text-lg font-bold text-navy dark:text-white border-b border-gray-150 dark:border-gray-700 pb-3">Detailed Answers Review</h3>
        
        <div class="space-y-8 divide-y divide-gray-150 dark:divide-gray-700">
            @foreach($answers as $index => $answer)
                <div class="space-y-4 pt-6 first:pt-0">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-gray-400">QUESTION {{ $index + 1 }}</span>
                        <span class="px-2 py-0.5 rounded font-bold uppercase tracking-wider 
                            {{ $answer->is_correct ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $answer->is_correct ? 'Correct' : 'Incorrect' }}
                        </span>
                    </div>

                    <!-- Question Text -->
                    <p class="text-sm font-bold leading-relaxed">{!! $answer->question->question_text !!}</p>

                    <!-- Choice Options list -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded border {{ $answer->selected_option === 'A' ? 'border-cyan bg-cyan bg-opacity-5' : 'border-gray-150' }} 
                            {{ $answer->question->correct_option === 'A' ? 'bg-green-50 border-green-300 text-green-900 font-bold' : '' }}">
                            <strong>A.</strong> {{ $answer->question->option_a }}
                        </div>
                        <div class="p-3 rounded border {{ $answer->selected_option === 'B' ? 'border-cyan bg-cyan bg-opacity-5' : 'border-gray-150' }}
                            {{ $answer->question->correct_option === 'B' ? 'bg-green-50 border-green-300 text-green-900 font-bold' : '' }}">
                            <strong>B.</strong> {{ $answer->question->option_b }}
                        </div>
                        @if(!empty($answer->question->option_c))
                            <div class="p-3 rounded border {{ $answer->selected_option === 'C' ? 'border-cyan bg-cyan bg-opacity-5' : 'border-gray-150' }}
                                {{ $answer->question->correct_option === 'C' ? 'bg-green-50 border-green-300 text-green-900 font-bold' : '' }}">
                                <strong>C.</strong> {{ $answer->question->option_c }}
                            </div>
                        @endif
                        @if(!empty($answer->question->option_d))
                            <div class="p-3 rounded border {{ $answer->selected_option === 'D' ? 'border-cyan bg-cyan bg-opacity-5' : 'border-gray-150' }}
                                {{ $answer->question->correct_option === 'D' ? 'bg-green-50 border-green-300 text-green-900 font-bold' : '' }}">
                                <strong>D.</strong> {{ $answer->question->option_d }}
                            </div>
                        @endif
                    </div>

                    <!-- Selected choice vs Correct -->
                    <div class="text-xs space-y-1">
                        <p class="text-gray-500">Your choice: <span class="font-bold {{ $answer->is_correct ? 'text-green-600' : 'text-red-500' }}">{{ $answer->selected_option ?? 'Skipped' }}</span></p>
                        <p class="text-gray-500">Correct option: <span class="font-bold text-green-600">{{ $answer->question->correct_option }}</span></p>
                    </div>

                    <!-- Explanation -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700 rounded-lg text-xs leading-relaxed text-gray-600 dark:text-gray-300">
                        <strong class="block text-navy dark:text-white mb-1">Explanation:</strong>
                        {!! $answer->question->explanation !!}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
