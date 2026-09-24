@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.questions.import-history') }}" class="hover:underline">Import History</a>
                <span>&rsaquo;</span>
                <a href="{{ route('admin.questions.import-review', $batch->uuid) }}" class="hover:underline">Batch {{ $batch->uuid }}</a>
                <span>&rsaquo;</span>
                <span class="text-gray-700 font-bold">Candidate #{{ $item->source_index }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center space-x-3">
                <span>Learner Preview: Candidate #{{ $item->source_index }}</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-navy text-white">
                    {{ $item->normalized_data['topic'] ?? 'Topic 1' }}
                </span>
            </h1>
        </div>

        <!-- Mode Navigation Buttons -->
        <div class="flex items-center space-x-2">
            <span class="px-3.5 py-1.5 rounded text-xs font-bold bg-cyan text-navy shadow-sm">
                👁 Learner Preview
            </span>
            <a href="{{ route('admin.questions.import-item-review', $item->id) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded shadow-sm transition">
                🔍 Admin Review
            </a>
            <a href="{{ route('admin.questions.import-item-edit', $item->id) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded shadow-sm transition">
                ✏ Edit Candidate
            </a>
            <a href="{{ route('admin.questions.import-review', $batch->uuid) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded shadow-sm transition">
                &larr; Back to Batch
            </a>
        </div>
    </div>

    @php
        $norm = $item->normalized_data ?? [];
        $type = $norm['question_type'] ?? 'single_choice';
        $options = $norm['options'] ?? [];
        $answerArea = $norm['answer_area'] ?? [];
        $qExhibits = $norm['question_exhibits'] ?? [];
    @endphp

    <!-- Learner View Simulation Notice -->
    <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800 flex items-center justify-between shadow-sm">
        <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span><strong>Learner View Simulation:</strong> Question prompt and choices are displayed exactly as a test-taker would experience them. Correct answers and explanations are protected.</span>
        </div>
        <span class="text-xs uppercase font-bold text-gray-500">{{ str_replace('_', ' ', $type) }}</span>
    </div>

    <!-- Main Question Box -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm space-y-6">
        
        <!-- Question Prompt -->
        <div class="text-sm font-medium text-gray-900 leading-relaxed bg-gray-50 p-4 border border-gray-200 rounded-lg">
            <div class="whitespace-pre-line">{{ $norm['question_text'] ?? 'Question text not available.' }}</div>
        </div>

        @if(!empty($norm['instructions']))
            <div class="text-xs text-gray-500 italic bg-gray-100 p-2.5 rounded border border-gray-200">
                <strong>Instructions:</strong> {{ $norm['instructions'] }}
            </div>
        @endif

        <!-- Question Exhibits (Learner-Facing) -->
        @if(!empty($qExhibits))
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Question Exhibit(s)</h4>
                <div class="grid grid-cols-1 gap-4">
                    @foreach($qExhibits as $img)
                        <div class="border border-gray-200 rounded-lg p-2.5 bg-gray-50 text-center">
                            <img src="{{ $img['url'] ?? '' }}" alt="{{ $img['caption'] ?? 'Exhibit' }}" class="max-h-96 mx-auto rounded shadow-sm">
                            @if(!empty($img['caption']))
                                <span class="text-[11px] text-gray-500 block mt-1.5">{{ $img['caption'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Single / Multiple Choice Options -->
        @if(in_array($type, ['single_choice', 'multiple_choice', 'yes_no']) && !empty($options))
            <div class="space-y-2.5">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Select Your Answer:</h4>
                <div class="space-y-2">
                    @foreach($options as $opt)
                        <div class="flex items-center space-x-3 p-3.5 border border-gray-200 rounded-lg bg-white hover:border-cyan hover:bg-cyan/5 cursor-pointer transition">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold bg-gray-100 text-gray-700">
                                {{ $opt['key'] }}
                            </span>
                            <span class="text-sm text-gray-800 flex-grow">{{ $opt['text'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Hotspot Dropdowns -->
        @if($type === 'hotspot' && !empty($answerArea['boxes']))
            <div class="space-y-3 p-5 bg-gray-50 border border-gray-200 rounded-lg">
                <h4 class="text-xs font-bold text-gray-500 uppercase">Answer Area (Select from dropdowns)</h4>
                <div class="space-y-3">
                    @foreach($answerArea['boxes'] as $box)
                        <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg">
                            <span class="font-bold text-xs text-gray-700">{{ $box['label'] ?? ('Box ' . ($loop->index + 1)) }}:</span>
                            <select class="text-xs border-gray-300 rounded focus:border-cyan w-64">
                                <option value="">-- Select Option --</option>
                                <option>{{ $box['correct'] ?? 'Option 1' }}</option>
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Drag & Drop Sequence Steps -->
        @if($type === 'drag_drop' && !empty($answerArea['steps']))
            <div class="space-y-3 p-5 bg-gray-50 border border-gray-200 rounded-lg">
                <h4 class="text-xs font-bold text-gray-500 uppercase">Answer Area (Select and Place in Order)</h4>
                <div class="space-y-2.5">
                    @foreach($answerArea['steps'] as $step)
                        <div class="p-3 bg-white border border-dashed border-gray-300 rounded-lg flex items-center space-x-3 text-xs">
                            <span class="font-bold text-cyan">{{ $step['label'] ?? ('Step ' . ($loop->index + 1)) }}:</span>
                            <span class="text-gray-700">{{ $step['text'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
