@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.questions.index') }}" class="hover:underline">Questions</a>
                <span>&rsaquo;</span>
                <span class="text-gray-700 font-bold">Question #{{ $question->id }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center space-x-3">
                <span>Learner Preview: Question #{{ $question->id }}</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-navy text-white">
                    {{ $question->exam->exam_code ?? 'Exam' }}
                </span>
            </h1>
        </div>

        <div class="flex items-center space-x-2">
            <span class="px-3.5 py-1.5 rounded text-xs font-bold bg-cyan text-navy shadow-sm">
                👁 Learner Preview
            </span>
            <a href="{{ route('admin.questions.show', $question->id) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded shadow-sm transition">
                🔍 Admin Detail
            </a>
            <a href="{{ route('admin.questions.edit', $question->id) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded shadow-sm transition">
                ✏ Edit Question
            </a>
            <a href="{{ route('admin.questions.index', ['exam_id' => $question->exam_id]) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded shadow-sm transition">
                &larr; Back to Listing
            </a>
        </div>
    </div>

    <!-- Learner View Simulation Notice -->
    <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800 flex items-center justify-between shadow-sm">
        <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span><strong>Learner View Simulation:</strong> Answers, solutions, and explanations are strictly hidden.</span>
        </div>
        <span class="text-xs uppercase font-bold text-gray-500">{{ str_replace('_', ' ', $question->question_type ?? 'single_choice') }}</span>
    </div>

    <!-- Question Card -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm space-y-6">
        <!-- Prompt -->
        <div class="text-sm font-medium text-gray-900 leading-relaxed bg-gray-50 p-4 border border-gray-200 rounded-lg whitespace-pre-line">
            {{ $question->question_text }}
        </div>

        @if(!empty($question->instructions))
            <div class="text-xs text-gray-500 italic bg-gray-100 p-2.5 rounded border border-gray-200">
                <strong>Instructions:</strong> {{ $question->instructions }}
            </div>
        @endif

        <!-- Media Exhibits -->
        @if($question->media && $question->media->isNotEmpty())
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Question Exhibit(s)</h4>
                <div class="grid grid-cols-1 gap-4">
                    @foreach($question->media as $m)
                        <div class="border border-gray-200 rounded-lg p-2.5 bg-gray-50 text-center">
                            <img src="{{ $m->media_url }}" alt="{{ $m->caption ?? 'Exhibit' }}" class="max-h-96 mx-auto rounded shadow-sm">
                            <span class="text-[11px] text-gray-500 block mt-1.5">{{ $m->caption ?? 'Exhibit' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Options -->
        @if($question->options && $question->options->isNotEmpty())
            <div class="space-y-2.5">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Select Your Answer:</h4>
                <div class="space-y-2">
                    @foreach($question->options as $opt)
                        <div class="flex items-center space-x-3 p-3.5 border border-gray-200 rounded-lg bg-white hover:border-cyan hover:bg-cyan/5 cursor-pointer transition">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold bg-gray-100 text-gray-700">
                                {{ $opt->option_key }}
                            </span>
                            <span class="text-sm text-gray-800 flex-grow">{{ $opt->option_text }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
