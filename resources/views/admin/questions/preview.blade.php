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
    if (!$qImage && !$ansAreaImage && $question->media->isNotEmpty()) {
        $qImage = $question->media->first()->media_url;
    }

    $boxes = $qData['boxes'] ?? $qData['hotspot_answers'] ?? [];
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
            <a href="{{ route('admin.questions.show', $question->id) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                <span>🔍</span>
                <span>Admin Detail</span>
            </a>
            <a href="{{ route('admin.questions.edit', $question->id) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-lg shadow-sm transition flex items-center space-x-1.5">
                <span>✏</span>
                <span>Edit Question</span>
            </a>
            <a href="{{ route('admin.questions.index', ['exam_id' => $question->exam_id]) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-lg transition">
                &larr; Back to Listing
            </a>
        </div>
    </div>

    <!-- Learner View Simulation Notice -->
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 flex items-center justify-between shadow-sm">
        <div class="flex items-center space-x-2.5">
            <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span><strong>Learner Candidate Simulation:</strong> Correct answers, solution keys, and explanations are strictly hidden.</span>
        </div>
        <span class="text-xs uppercase font-extrabold text-blue-900 bg-blue-100 px-2.5 py-1 rounded">{{ str_replace('_', ' ', $question->question_type ?? 'single_choice') }}</span>
    </div>

    <!-- Candidate Question Card -->
    <div class="bg-white border border-gray-250 rounded-xl p-6 sm:p-8 shadow-sm space-y-6">
        <!-- Question Prompt -->
        <div class="text-base font-medium text-navy leading-relaxed bg-gray-50 p-5 border border-gray-200 rounded-xl whitespace-pre-line">
            {{ $question->question_text }}
        </div>

        @if(!empty($question->instructions))
            <div class="text-xs text-gray-600 bg-amber-50 border border-amber-200 p-3 rounded-lg">
                <span class="font-bold text-amber-800">Instructions: </span> {{ $question->instructions }}
            </div>
        @endif

        <!-- Question Prompt Diagram (e.g. 234.jpg) -->
        @if($qImage)
            <div class="space-y-2">
                <div class="border border-gray-200 rounded-xl p-3 bg-gray-50 text-center">
                    <img src="{{ $qImage }}" alt="Question Diagram" class="max-h-96 mx-auto rounded shadow-sm">
                </div>
            </div>
        @endif

        <!-- HOTSPOT CANDIDATE INTERACTIVE ANSWER AREA -->
        @if($question->question_type === 'hotspot')
            <div class="space-y-4 pt-2">
                <h4 class="text-xs font-bold text-navy uppercase tracking-wide">Answer Area</h4>
                
                <div class="p-6 bg-slate-50 border border-slate-200 rounded-2xl space-y-6">
                    <!-- Dropdown Selection Controls -->
                    @if(!empty($boxes))
                        <div class="space-y-4">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Select the appropriate options in the answer area:</label>
                            
                            <div class="space-y-3">
                                @foreach($boxes as $idx => $box)
                                    @php
                                        $label = $box['label'] ?? ('Box ' . ($idx + 1));
                                        $choices = is_array($box['options'] ?? null) ? $box['options'] : array_map('trim', explode(',', $box['optionsText'] ?? ''));
                                    @endphp
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-white border border-slate-200 rounded-xl gap-3 shadow-sm">
                                        <span class="text-sm font-bold text-slate-800">{{ $label }}:</span>
                                        <select class="border-slate-300 rounded-lg text-sm px-4 py-2.5 focus:border-cyan focus:ring-cyan text-slate-800 font-semibold sm:w-64 bg-slate-50">
                                            <option value="">[ Select ▼ ]</option>
                                            @foreach($choices as $c)
                                                <option value="{{ $c }}">{{ $c }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- CHOICE OPTIONS (SINGLE / MULTIPLE CHOICE) -->
        @if($question->question_type !== 'hotspot' && $question->options && $question->options->isNotEmpty())
            <div class="space-y-3 pt-2">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Select Your Answer:</h4>
                <div class="space-y-3">
                    @foreach($question->options as $opt)
                        <label class="flex items-center space-x-3 p-4 border border-gray-200 rounded-xl bg-white hover:border-cyan hover:bg-cyan/5 cursor-pointer transition">
                            @if($question->question_type === 'multiple_choice')
                                <input type="checkbox" name="preview_choice[]" class="rounded border-gray-300 text-cyan focus:ring-cyan h-5 w-5">
                            @else
                                <input type="radio" name="preview_choice" class="rounded-full border-gray-300 text-cyan focus:ring-cyan h-5 w-5">
                            @endif
                            <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-extrabold bg-gray-100 text-gray-700 shrink-0">
                                {{ $opt->option_key }}
                            </span>
                            <div class="text-sm text-gray-800 flex-grow prose max-w-none">
                                {!! $opt->option_text !!}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
