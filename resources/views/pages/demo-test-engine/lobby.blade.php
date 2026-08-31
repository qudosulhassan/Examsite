@extends('layouts.public')

@section('content')
<div class="relative bg-gradient-to-br from-[#07101E] via-navy to-[#0F172A] min-h-[calc(100vh-100px)] py-12 px-4 sm:px-6 overflow-hidden">
    <!-- Abstract glowing orbs -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-cyan rounded-full mix-blend-screen filter blur-[150px] opacity-10 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-10 pointer-events-none"></div>
    <!-- Abstract Tech Lines -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 64px 64px;"></div>

    <div class="relative z-10 max-w-4xl mx-auto space-y-8">
        <!-- Breadcrumbs -->
        <nav class="flex text-[11px] font-bold uppercase tracking-widest text-gray-400 space-x-3 mb-8">
            <a href="{{ url('/') }}" class="hover:text-cyan transition-colors">Home</a>
            <span class="text-gray-600">/</span>
            <a href="{{ route('public.test-engine') }}" class="hover:text-cyan transition-colors">Test Engine</a>
            <span class="text-gray-600">/</span>
            <span class="text-cyan">{{ $exam->exam_code }} Demo Lobby</span>
        </nav>

        <!-- Lobby Box -->
        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 md:p-12 shadow-[0_20px_50px_rgba(0,0,0,0.3)] space-y-10">
            <div>
                <span class="bg-cyan/10 text-cyan font-black text-[10px] px-3 py-1.5 rounded-lg uppercase tracking-widest border border-cyan/20">{{ $exam->exam_code }}</span>
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-black text-white mt-6 leading-tight">{{ $exam->exam_name }} <br/><span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan to-blue-500">Simulator Lobby</span></h1>
                <p class="text-base text-gray-400 mt-4 font-medium max-w-2xl">Configure your testing session parameters before starting.</p>
            </div>

            <form action="{{ route('public.demo-test-engine.start', $exam->id) }}" method="POST" class="space-y-10">
                @csrf

                <!-- Mode Selection -->
                <div class="space-y-4">
                    <label class="block text-sm font-black text-white uppercase tracking-widest">Select Simulator Mode</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Practice Mode -->
                        <label class="relative group cursor-pointer">
                            <input type="radio" name="mode" value="practice" checked class="peer sr-only">
                            <div class="h-full bg-white/5 border border-white/10 rounded-2xl p-6 transition-all duration-300 peer-checked:bg-cyan/10 peer-checked:border-cyan hover:border-white/30 peer-checked:shadow-[0_0_20px_rgba(0,212,170,0.2)]">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-[13px] font-black text-white uppercase tracking-wider group-hover:text-cyan transition-colors peer-checked:text-cyan">Practice Mode</span>
                                    <div class="w-5 h-5 rounded-full border-2 border-gray-500 peer-checked:border-cyan flex items-center justify-center transition-colors">
                                        <div class="w-2.5 h-2.5 rounded-full bg-cyan opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 font-medium leading-relaxed">See correct answers and explanations immediately after answering each question. Great for learning.</p>
                            </div>
                        </label>

                        <!-- Exam Mode -->
                        <label class="relative group cursor-pointer">
                            <input type="radio" name="mode" value="exam" class="peer sr-only">
                            <div class="h-full bg-white/5 border border-white/10 rounded-2xl p-6 transition-all duration-300 peer-checked:bg-cyan/10 peer-checked:border-cyan hover:border-white/30 peer-checked:shadow-[0_0_20px_rgba(0,212,170,0.2)]">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-[13px] font-black text-white uppercase tracking-wider group-hover:text-cyan transition-colors peer-checked:text-cyan">Exam Mode</span>
                                    <div class="w-5 h-5 rounded-full border-2 border-gray-500 peer-checked:border-cyan flex items-center justify-center transition-colors">
                                        <div class="w-2.5 h-2.5 rounded-full bg-cyan opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 font-medium leading-relaxed">Simulates real exam constraints. Timed, answers and explanations are hidden until final submission.</p>
                            </div>
                        </label>

                        <!-- Review Mode -->
                        <label class="relative group cursor-pointer {{ !$lastAttempt ? 'opacity-50 cursor-not-allowed' : '' }}">
                            <input type="radio" name="mode" value="review" {{ !$lastAttempt ? 'disabled' : '' }} class="peer sr-only">
                            <div class="h-full bg-white/5 border border-white/10 rounded-2xl p-6 transition-all duration-300 peer-checked:bg-cyan/10 peer-checked:border-cyan hover:border-white/30 peer-checked:shadow-[0_0_20px_rgba(0,212,170,0.2)]">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-[13px] font-black text-white uppercase tracking-wider group-hover:text-cyan transition-colors peer-checked:text-cyan">Review Mode</span>
                                    <div class="w-5 h-5 rounded-full border-2 border-gray-500 peer-checked:border-cyan flex items-center justify-center transition-colors">
                                        <div class="w-2.5 h-2.5 rounded-full bg-cyan opacity-0 peer-checked:opacity-100 transition-opacity"></div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 font-medium leading-relaxed">Re-attempt incorrect or flagged questions from your previous attempt. Helps build focus on weak spots.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="space-y-4" x-data="{ count: {{ min($questionCount, 10) }} }">
                    <label class="block text-sm font-black text-white uppercase tracking-widest">Number of Questions</label>
                    <div class="flex items-center space-x-6 bg-white/5 p-6 rounded-2xl border border-white/10">
                        <input type="range" name="count" min="5" max="{{ min($questionCount, 10) }}" x-model="count" class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-cyan">
                        <span class="text-3xl font-black text-cyan w-16 text-center" x-text="count"></span>
                    </div>
                    <div class="flex justify-between text-[10px] font-bold uppercase tracking-wider text-gray-500 px-2">
                        <span>Min: 5 Questions</span>
                        <span>Max: {{ min($questionCount, 10) }} (Free Demo Limit)</span>
                    </div>
                </div>

                <!-- CTA -->
                <div class="pt-8 border-t border-white/10 flex flex-col md:flex-row justify-between items-center gap-6">
                    <a href="{{ route('public.test-engine') }}" class="text-[11px] font-bold text-gray-400 hover:text-cyan transition-colors uppercase tracking-widest">
                        &larr; Back to Details
                    </a>
                    <button type="submit" class="w-full md:w-auto bg-gradient-to-r from-cyan to-blue-500 hover:from-cyan hover:to-blue-600 text-navy font-black py-4 px-10 rounded-xl shadow-[0_10px_30px_rgba(0,212,170,0.3)] transition-all transform hover:-translate-y-1 text-sm uppercase tracking-wider">
                        Start Simulator Session
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
