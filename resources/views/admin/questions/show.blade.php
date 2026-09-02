@extends('layouts.admin')

@section('content')
@php
    $qData = $question->question_data ?? [];
    $qImage = $question->media->firstWhere('media_type', 'question_image')?->media_url 
              ?? ($question->image_filename ? '/storage/questions/' . $question->image_filename : null);
    
    $ansAreaImage = $qData['answer_area_image'] ?? null;
    if (!$ansAreaImage) {
        $ansAreaImage = $question->media->firstWhere('media_type', 'answer_area')?->media_url;
    }
    // Fallback if media exists but not typed as question_image
    if (!$qImage && !$ansAreaImage && $question->media->isNotEmpty()) {
        $qImage = $question->media->first()->media_url;
    }

    $boxes = $qData['boxes'] ?? $qData['hotspot_answers'] ?? [];
    $correctKeys = $question->answers->pluck('answer_value')->toArray();
@endphp

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
            <a href="{{ route('admin.questions.preview', $question->id) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-navy text-xs font-bold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                <span>👁</span>
                <span>Learner Preview</span>
            </a>
            <a href="{{ route('admin.questions.edit', $question->id) }}" class="px-3.5 py-1.5 bg-navy text-white hover:bg-opacity-95 text-xs font-bold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                <span>✏</span>
                <span>Edit Question</span>
            </a>
            <a href="{{ route('admin.questions.index', ['exam_id' => $question->exam_id]) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-lg transition">
                &larr; Back to Listing
            </a>
        </div>
    </div>

    <!-- Question Details Card -->
    <div class="bg-white border border-gray-250 rounded-xl p-6 shadow-sm space-y-6">
        <!-- Metadata Header -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs bg-gray-50 p-4 rounded-xl border border-gray-200">
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

        <!-- 1. QUESTION PROMPT -->
        <div class="space-y-2">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Question Prompt</h4>
            <div class="text-sm font-medium text-gray-900 leading-relaxed bg-gray-50 p-4 border border-gray-200 rounded-xl whitespace-pre-line">
                {{ $question->question_text }}
            </div>
        </div>

        @if(!empty($question->instructions))
            <div class="text-xs text-gray-600 bg-amber-50 border border-amber-200 p-3 rounded-lg">
                <span class="font-bold text-amber-800">Instructions: </span> {{ $question->instructions }}
            </div>
        @endif

        <!-- 2. QUESTION IMAGE / DIAGRAM (e.g. 234.jpg) -->
        @if($qImage)
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-navy uppercase tracking-wide flex items-center space-x-2">
                    <span>🖼 Question Image / Diagram</span>
                </h4>
                <div class="border border-gray-200 rounded-xl p-3 bg-gray-50 text-center">
                    <img src="{{ $qImage }}" alt="Question Diagram" class="max-h-96 mx-auto rounded shadow-sm">
                    <span class="text-[11px] text-gray-500 block mt-1.5 font-medium">Question Prompt Diagram</span>
                </div>
            </div>
        @endif

        <!-- 3. ANSWER AREA / HOTSPOT IMAGE (e.g. 235.jpg) -->
        @if($ansAreaImage && $question->question_type === 'hotspot')
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-cyan uppercase tracking-wide flex items-center space-x-2">
                    <span>🖼 Answer Area Image</span>
                </h4>
                <div class="border border-cyan/30 rounded-xl p-3 bg-cyan/5 text-center">
                    <img src="{{ $ansAreaImage }}" alt="Answer Area Image" class="max-h-96 mx-auto rounded shadow-sm border border-cyan/20">
                    <span class="text-[11px] text-navy block mt-1.5 font-bold">Candidate Answer Area Reference Diagram</span>
                </div>
            </div>
        @endif

        <!-- 4. HOTSPOT ANSWER BOXES & SOLUTIONS (ADMIN DETAIL MODE) -->
        @if($question->question_type === 'hotspot' && !empty($boxes))
            <div class="space-y-4 pt-2">
                <h4 class="text-xs font-bold text-navy uppercase tracking-wide">Answer Boxes & Correct Solutions</h4>
                
                <div class="space-y-4">
                    @foreach($boxes as $idx => $box)
                        @php
                            $label = $box['label'] ?? ('Box ' . ($idx + 1));
                            $choices = is_array($box['options'] ?? null) ? $box['options'] : array_map('trim', explode(',', $box['optionsText'] ?? ''));
                            $correct = $box['correct_answer'] ?? '';
                            $pts = $box['points'] ?? 1;
                            $boxExp = $box['explanation'] ?? '';
                        @endphp
                        <div class="p-5 border border-emerald-500/40 bg-emerald-50/50 rounded-xl space-y-3 shadow-sm">
                            <div class="flex justify-between items-center pb-2 border-b border-emerald-200">
                                <span class="text-xs font-extrabold text-navy uppercase tracking-wider">
                                    Box #{{ $idx + 1 }}: {{ $label }}
                                </span>
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-600 text-white">
                                    ✓ Correct Answer: {{ $correct }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div>
                                    <span class="text-gray-500 font-bold block mb-1">Available Choices:</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($choices as $c)
                                            <span class="px-2.5 py-1 rounded border text-xs font-bold {{ (string)$c === (string)$correct ? 'bg-emerald-500 text-white border-emerald-600' : 'bg-white text-gray-700 border-gray-300' }}">
                                                {{ $c }} {{ (string)$c === (string)$correct ? '✓' : '' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <span class="text-gray-500 font-bold block mb-1">Points:</span>
                                    <span class="font-bold text-navy">{{ $pts }} point(s)</span>
                                </div>
                            </div>

                            @if(!empty($boxExp))
                                <div class="text-xs text-gray-700 bg-white p-2.5 rounded border border-emerald-200">
                                    <span class="font-bold text-emerald-800">Box Rationale: </span> {{ $boxExp }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 5. STANDARD OPTIONS & SOLUTIONS (FOR CHOICE QUESTIONS) -->
        @if($question->question_type !== 'hotspot' && $question->options && $question->options->isNotEmpty())
            <div class="space-y-2.5">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Options & Solutions</h4>
                <div class="space-y-3">
                    @foreach($question->options as $opt)
                        @php $isCorrect = in_array($opt->option_key, $correctKeys, true); @endphp
                        <div class="p-4 border rounded-xl {{ $isCorrect ? 'border-emerald-500 bg-emerald-50/70 font-semibold ring-1 ring-emerald-500' : 'border-gray-200 bg-white' }}">
                            <div class="flex items-center space-x-3">
                                <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-extrabold shrink-0 {{ $isCorrect ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 border border-gray-300' }}">
                                    {{ $opt->option_key }}
                                </span>
                                <div class="text-sm text-gray-800 flex-grow prose max-w-none">
                                    {!! $opt->option_text !!}
                                </div>
                                @if($isCorrect)
                                    <span class="text-xs font-bold text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full uppercase shrink-0">✓ Correct Answer</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 6. EXPLANATION & RATIONALE -->
        <div class="space-y-2">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Answer Explanation & Rationale</h4>
            <div class="text-xs text-gray-800 leading-relaxed bg-gray-50 p-4 border border-gray-200 rounded-xl whitespace-pre-line">
                {{ $question->explanation ?: 'No overall explanation provided.' }}
            </div>
        </div>

        <!-- 7. REFERENCES -->
        @if($question->references && $question->references->isNotEmpty())
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Documentation References</h4>
                <ul class="list-disc pl-5 text-xs text-navy space-y-1">
                    @foreach($question->references as $ref)
                        <li>
                            <a href="{{ $ref->url }}" target="_blank" class="hover:underline font-medium text-cyan">
                                {{ $ref->title ?: $ref->url }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
@endsection
