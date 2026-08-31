@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-10 py-16 relative z-10">
    <!-- Breadcrumbs -->
    <nav class="flex text-[11px] font-black uppercase tracking-widest text-gray-400 space-x-3 mb-4">
        <a href="{{ url('/dashboard') }}" class="hover:text-cyan transition-colors">Dashboard</a>
        <span class="text-gray-600">/</span>
        <a href="{{ route('dashboard.test-engine') }}" class="hover:text-cyan transition-colors">Test Engine</a>
        <span class="text-gray-600">/</span>
        <span class="text-cyan drop-shadow-[0_0_8px_rgba(0,212,170,0.5)]">Results #{{ $attempt->id }}</span>
    </nav>

    <!-- Score Overview Banner -->
    <div class="bg-gradient-to-br from-[#07101E] via-navy to-[#0F172A] border border-white/10 rounded-3xl p-10 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-center space-y-8 relative overflow-hidden">
        <!-- Abstract light orbs -->
        <div class="absolute -top-32 -left-32 w-64 h-64 bg-cyan/20 rounded-full mix-blend-screen filter blur-[80px] opacity-70"></div>
        <div class="absolute -bottom-32 -right-32 w-64 h-64 bg-blue-500/20 rounded-full mix-blend-screen filter blur-[80px] opacity-70"></div>
        
        <div class="relative z-10">
            @if($attempt->passed)
                <div class="inline-flex items-center justify-center h-24 w-24 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 text-white mb-6 text-4xl shadow-[0_0_30px_rgba(16,185,129,0.4)] border-4 border-green-200/20">
                    ✓
                </div>
                <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-emerald-300 drop-shadow-sm uppercase tracking-tight">Congratulations! You Passed.</h1>
            @else
                <div class="inline-flex items-center justify-center h-24 w-24 rounded-full bg-gradient-to-br from-orange to-red-500 text-white mb-6 text-4xl shadow-[0_0_30px_rgba(249,115,22,0.4)] border-4 border-red-200/20">
                    ✗
                </div>
                <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-orange to-red-400 drop-shadow-sm uppercase tracking-tight">Exam Failed. Try Again.</h1>
            @endif
            <p class="text-sm font-bold text-gray-400 mt-4 uppercase tracking-widest"><span class="text-cyan">{{ $exam->exam_code }}</span> - {{ $exam->exam_name }} ({{ ucfirst($attempt->mode) }} Attempt)</p>
        </div>

        <!-- Graphical Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 items-center max-w-4xl mx-auto pt-10 border-t border-white/10 relative z-10">
            
            <!-- Circular Chart -->
            <div class="flex justify-center md:col-span-1">
                <div class="relative w-48 h-48">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        <!-- Background circle -->
                        <circle cx="50" cy="50" r="40" fill="transparent" stroke="rgba(255,255,255,0.05)" stroke-width="8" />
                        <!-- Progress circle -->
                        @php
                            $circumference = 2 * pi() * 40;
                            $offset = $circumference - ($attempt->score_percentage / 100) * $circumference;
                            $strokeColor = $attempt->passed ? 'url(#greenGradient)' : 'url(#redGradient)';
                        @endphp
                        <defs>
                            <linearGradient id="greenGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#34D399" />
                                <stop offset="100%" stop-color="#059669" />
                            </linearGradient>
                            <linearGradient id="redGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#FB923C" />
                                <stop offset="100%" stop-color="#DC2626" />
                            </linearGradient>
                        </defs>
                        <circle cx="50" cy="50" r="40" fill="transparent" stroke="{{ $strokeColor }}" stroke-width="8" 
                                stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}" stroke-linecap="round" class="drop-shadow-[0_0_10px_rgba(0,212,170,0.5)]" style="transition: stroke-dashoffset 1.5s ease-out;" />
                    </svg>
                    <!-- Center text -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-4xl font-black text-white drop-shadow-md">{{ number_format($attempt->score_percentage, 0) }}%</span>
                        <span class="text-[10px] text-cyan font-black uppercase tracking-widest mt-1">Score</span>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="md:col-span-2 grid grid-cols-2 md:grid-cols-3 gap-y-8 gap-x-6 text-left">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 shadow-inner">
                    <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Passing Score</span>
                    <span class="text-2xl font-black text-white mt-1 drop-shadow-sm">{{ $exam->passing_score }}%</span>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 shadow-inner">
                    <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Questions</span>
                    <span class="text-2xl font-black text-white mt-1 drop-shadow-sm">{{ $attempt->total_questions }}</span>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 shadow-inner">
                    <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Time Spent</span>
                    @php
                        $mins = floor($attempt->time_taken_seconds / 60);
                        $secs = $attempt->time_taken_seconds % 60;
                    @endphp
                    <span class="text-2xl font-black text-white mt-1 drop-shadow-sm">{{ $mins }}m {{ $secs }}s</span>
                </div>
                <div class="bg-green-500/10 border border-green-500/20 rounded-2xl p-4 shadow-[0_0_15px_rgba(16,185,129,0.1)]">
                    <span class="block text-[10px] font-black text-green-400 uppercase tracking-widest">Correct</span>
                    <span class="text-2xl font-black text-green-400 mt-1 drop-shadow-sm">{{ $attempt->correct }}</span>
                </div>
                <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4 shadow-[0_0_15px_rgba(239,68,68,0.1)]">
                    <span class="block text-[10px] font-black text-red-400 uppercase tracking-widest">Incorrect</span>
                    <span class="text-2xl font-black text-red-400 mt-1 drop-shadow-sm">{{ $attempt->total_questions - $attempt->correct - $attempt->skipped }}</span>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 shadow-inner">
                    <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Skipped</span>
                    <span class="text-2xl font-black text-gray-400 mt-1 drop-shadow-sm">{{ $attempt->skipped }}</span>
                </div>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="pt-10 flex flex-wrap justify-center gap-6 relative z-10">
            <a href="{{ route('dashboard.test-engine.lobby', $exam->slug) }}" class="bg-gradient-to-r from-orange to-red-500 hover:from-orange hover:to-red-600 text-white text-xs font-black uppercase tracking-widest py-3 px-8 rounded-xl shadow-[0_4px_15px_rgba(249,115,22,0.4)] transition-all transform hover:-translate-y-0.5">
                Retake Exam Simulator
            </a>
            <a href="{{ route('dashboard.test-engine') }}" class="bg-white/10 border border-white/20 text-white hover:bg-white/20 text-xs font-black uppercase tracking-widest py-3 px-8 rounded-xl transition-all">
                Choose Another Exam
            </a>
        </div>
    </div>

    <!-- Question Answers Review Panel -->
    <div class="bg-white rounded-3xl p-8 md:p-10 shadow-[0_10px_40px_rgba(0,0,0,0.04)] border border-gray-100 space-y-8">
        <h3 class="text-xl font-black text-navy border-b border-gray-100 pb-4 uppercase tracking-widest flex items-center space-x-3">
            <span class="w-2 h-8 bg-cyan rounded-full block"></span>
            <span>Detailed Answers Review</span>
        </h3>
        
        <div class="space-y-12">
            @foreach($answers as $index => $answer)
                <div class="space-y-6 pt-6 {{ !$loop->first ? 'border-t border-gray-100' : '' }}">
                    <div class="flex justify-between items-center">
                        <span class="font-black text-navy uppercase tracking-widest text-lg">Question <span class="text-cyan">{{ $index + 1 }}</span></span>
                        <span class="px-4 py-1.5 rounded-lg font-black uppercase tracking-widest text-[10px] border shadow-sm
                            {{ $answer->is_correct ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700' }}">
                            {{ $answer->is_correct ? 'Correct' : 'Incorrect' }}
                        </span>
                    </div>

                    <!-- Question Text -->
                    <div class="prose max-w-none">
                        <p class="text-lg font-bold text-navy leading-relaxed">{!! $answer->question->question_text !!}</p>
                    </div>

                    <!-- Choice Options list -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Option A -->
                        <div class="p-5 rounded-xl border-2 transition-colors flex items-start space-x-3 
                            {{ $answer->selected_option === 'A' && $answer->question->correct_option !== 'A' ? 'border-red-300 bg-red-50' : '' }}
                            {{ $answer->question->correct_option === 'A' ? 'border-green-400 bg-green-50 shadow-[0_4px_15px_rgba(16,185,129,0.1)]' : 'border-gray-100' }}
                            {{ $answer->selected_option !== 'A' && $answer->question->correct_option !== 'A' ? 'bg-white' : '' }}">
                            <div class="font-black {{ $answer->question->correct_option === 'A' ? 'text-green-600' : 'text-gray-400' }} text-sm mt-0.5">A.</div>
                            <div class="text-navy font-medium">{{ $answer->question->option_a }}</div>
                        </div>

                        <!-- Option B -->
                        <div class="p-5 rounded-xl border-2 transition-colors flex items-start space-x-3 
                            {{ $answer->selected_option === 'B' && $answer->question->correct_option !== 'B' ? 'border-red-300 bg-red-50' : '' }}
                            {{ $answer->question->correct_option === 'B' ? 'border-green-400 bg-green-50 shadow-[0_4px_15px_rgba(16,185,129,0.1)]' : 'border-gray-100' }}
                            {{ $answer->selected_option !== 'B' && $answer->question->correct_option !== 'B' ? 'bg-white' : '' }}">
                            <div class="font-black {{ $answer->question->correct_option === 'B' ? 'text-green-600' : 'text-gray-400' }} text-sm mt-0.5">B.</div>
                            <div class="text-navy font-medium">{{ $answer->question->option_b }}</div>
                        </div>

                        <!-- Option C -->
                        @if(!empty($answer->question->option_c))
                            <div class="p-5 rounded-xl border-2 transition-colors flex items-start space-x-3 
                                {{ $answer->selected_option === 'C' && $answer->question->correct_option !== 'C' ? 'border-red-300 bg-red-50' : '' }}
                                {{ $answer->question->correct_option === 'C' ? 'border-green-400 bg-green-50 shadow-[0_4px_15px_rgba(16,185,129,0.1)]' : 'border-gray-100' }}
                                {{ $answer->selected_option !== 'C' && $answer->question->correct_option !== 'C' ? 'bg-white' : '' }}">
                                <div class="font-black {{ $answer->question->correct_option === 'C' ? 'text-green-600' : 'text-gray-400' }} text-sm mt-0.5">C.</div>
                                <div class="text-navy font-medium">{{ $answer->question->option_c }}</div>
                            </div>
                        @endif

                        <!-- Option D -->
                        @if(!empty($answer->question->option_d))
                            <div class="p-5 rounded-xl border-2 transition-colors flex items-start space-x-3 
                                {{ $answer->selected_option === 'D' && $answer->question->correct_option !== 'D' ? 'border-red-300 bg-red-50' : '' }}
                                {{ $answer->question->correct_option === 'D' ? 'border-green-400 bg-green-50 shadow-[0_4px_15px_rgba(16,185,129,0.1)]' : 'border-gray-100' }}
                                {{ $answer->selected_option !== 'D' && $answer->question->correct_option !== 'D' ? 'bg-white' : '' }}">
                                <div class="font-black {{ $answer->question->correct_option === 'D' ? 'text-green-600' : 'text-gray-400' }} text-sm mt-0.5">D.</div>
                                <div class="text-navy font-medium">{{ $answer->question->option_d }}</div>
                            </div>
                        @endif
                    </div>

                    <!-- Selected choice vs Correct -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-8 space-y-2 sm:space-y-0 text-sm p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div>
                            <span class="font-black text-gray-500 uppercase tracking-widest text-[10px] mr-2">Your Choice:</span> 
                            <span class="font-black text-lg {{ $answer->is_correct ? 'text-green-600' : 'text-red-500' }}">{{ $answer->selected_option ?? 'Skipped' }}</span>
                        </div>
                        <div>
                            <span class="font-black text-gray-500 uppercase tracking-widest text-[10px] mr-2">Correct Answer:</span> 
                            <span class="font-black text-lg text-green-600">{{ $answer->question->correct_option }}</span>
                        </div>
                    </div>

                    <!-- Explanation -->
                    <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-2xl text-sm leading-relaxed shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1.5 h-full bg-green-500"></div>
                        <strong class="block text-navy font-black uppercase tracking-widest text-[11px] mb-3">Explanation</strong>
                        <div class="prose max-w-none text-gray-700 font-medium">
                            {!! $answer->question->explanation !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
