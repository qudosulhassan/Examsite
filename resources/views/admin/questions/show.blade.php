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
                <span>Question #{{ $question->id }}</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-navy text-white">
                    {{ $question->exam->exam_code ?? 'Exam' }}
                </span>
                <span class="text-xs px-2 py-0.5 rounded font-bold {{ $question->status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ ucfirst($question->status) }}
                </span>
            </h1>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.questions.preview', $question->id) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded shadow-sm transition">
                👁 Learner Preview
            </a>
            <a href="{{ route('admin.questions.edit', $question->id) }}" class="px-3.5 py-1.5 bg-navy text-white hover:bg-opacity-95 text-xs font-bold rounded shadow-sm transition">
                ✏ Edit Question
            </a>
            <a href="{{ route('admin.questions.index', ['exam_id' => $question->exam_id]) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded shadow-sm transition">
                &larr; Back to Listing
            </a>
        </div>
    </div>

    <!-- Question Details Card -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm space-y-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div>
                <span class="text-gray-400 font-bold block uppercase text-[10px]">Certification Exam</span>
                <span class="font-bold text-gray-800">{{ $question->exam->exam_name ?? '—' }} ({{ $question->exam->exam_code ?? '' }})</span>
            </div>
            <div>
                <span class="text-gray-400 font-bold block uppercase text-[10px]">Topic / Domain</span>
                <span class="font-bold text-navy">{{ $question->topic ?: 'General' }}</span>
            </div>
            <div>
                <span class="text-gray-400 font-bold block uppercase text-[10px]">Question Type</span>
                <span class="font-bold text-cyan">{{ str_replace('_', ' ', strtoupper($question->question_type ?? 'single_choice')) }}</span>
            </div>
            <div>
                <span class="text-gray-400 font-bold block uppercase text-[10px]">Source Type</span>
                <span class="font-bold text-gray-700">{{ strtoupper($question->source_type ?? 'manual') }}</span>
            </div>
        </div>

        <!-- Prompt -->
        <div class="space-y-2">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Question Prompt</h4>
            <div class="text-sm font-medium text-gray-900 leading-relaxed bg-gray-50 p-4 border border-gray-200 rounded-lg whitespace-pre-line">
                {{ $question->question_text }}
            </div>
        </div>

        <!-- Media Exhibits -->
        @if($question->media && $question->media->isNotEmpty())
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Visual Exhibit(s)</h4>
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

        <!-- Options & Answers -->
        @php
            $correctKeys = $question->answers->pluck('answer_value')->toArray();
        @endphp
        @if($question->options && $question->options->isNotEmpty())
            <div class="space-y-2.5">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Options & Solutions</h4>
                <div class="space-y-2">
                    @foreach($question->options as $opt)
                        @php $isCorrect = in_array($opt->option_key, $correctKeys, true); @endphp
                        <div class="flex items-center space-x-3 p-3.5 border rounded-lg {{ $isCorrect ? 'border-emerald-500 bg-emerald-50/60 font-semibold' : 'border-gray-200 bg-white' }}">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $isCorrect ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-600' }}">
                                {{ $opt->option_key }}
                            </span>
                            <span class="text-sm text-gray-800 flex-grow">{{ $opt->option_text }}</span>
                            @if($isCorrect)
                                <span class="text-xs font-bold text-emerald-600">✓ Correct</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Explanation -->
        <div class="space-y-2">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Explanation & Rationale</h4>
            <div class="text-xs text-gray-700 leading-relaxed bg-gray-50 p-3.5 border border-gray-200 rounded-lg whitespace-pre-line">
                {{ $question->explanation ?: 'Explanation not available' }}
            </div>
        </div>

        <!-- References -->
        @if($question->references && $question->references->isNotEmpty())
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Documentation References</h4>
                <ul class="list-disc pl-5 text-xs text-navy space-y-1">
                    @foreach($question->references as $ref)
                        <li>
                            <a href="{{ $ref->url }}" target="_blank" class="hover:underline font-medium text-cyan">
                                {{ $ref->url ?: $ref->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
@endsection
