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
                <span>Admin Review: Candidate #{{ $item->source_index }}</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold {{ ($item->normalized_data['readiness_status'] ?? 'READY') === 'READY' ? 'bg-emerald-600 text-white' : (($item->normalized_data['readiness_status'] ?? '') === 'REVIEW_REQUIRED' ? 'bg-amber-500 text-black' : 'bg-rose-600 text-white') }}">
                    {{ $item->normalized_data['readiness_status'] ?? 'READY' }}
                </span>
            </h1>
        </div>

        <!-- Mode Navigation Buttons -->
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.questions.import-item-preview', $item->id) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded shadow-sm transition">
                👁 Learner Preview
            </a>
            <span class="px-3.5 py-1.5 rounded text-xs font-bold bg-navy text-white shadow-sm">
                🔍 Admin Review
            </span>
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
        $srcRef = $norm['source_reference'] ?? [];
        $type = $norm['question_type'] ?? 'single_choice';
        $options = $norm['options'] ?? [];
        $correctAnswers = $norm['correct_answers'] ?? [];
        $answerArea = $norm['answer_area'] ?? [];
        $qExhibits = $norm['question_exhibits'] ?? [];
        $aExhibits = $norm['answer_exhibits'] ?? [];
        $fieldStatuses = $norm['field_statuses'] ?? [];
        $discrepancies = $norm['discrepancies'] ?? [];
        $rawText = $item->raw_data['debug_info']['raw_text_block'] ?? ($item->raw_data['debug_info']['raw_text'] ?? '');
    @endphp

    <!-- Source Metadata Card -->
    <div class="bg-navy text-white rounded-lg p-4 shadow-sm grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
        <div>
            <span class="text-gray-400 font-bold block uppercase text-[10px]">Source Range</span>
            <span class="font-bold text-white">Pages {{ $srcRef['page_start'] ?? 1 }}–{{ $srcRef['page_end'] ?? 1 }}</span>
        </div>
        <div>
            <span class="text-gray-400 font-bold block uppercase text-[10px]">Question Type</span>
            <span class="font-bold text-cyan">{{ str_replace('_', ' ', strtoupper($type)) }}</span>
        </div>
        <div>
            <span class="text-gray-400 font-bold block uppercase text-[10px]">Confidence Score</span>
            <span class="font-bold text-emerald-400">{{ $srcRef['confidence_score'] ?? 85 }}% ({{ $srcRef['confidence_level'] ?? 'HIGH' }})</span>
        </div>
        <div>
            <span class="text-gray-400 font-bold block uppercase text-[10px]">Topic / Domain</span>
            <span class="font-bold text-white">{{ $norm['topic'] ?? 'Topic 1' }} (Q#{{ $norm['local_question_number'] ?? $item->source_index }})</span>
        </div>
    </div>

    <!-- Field Status Verification Matrix -->
    @if(!empty($fieldStatuses))
        <div class="bg-white border border-gray-250 rounded-lg p-4 shadow-sm space-y-3">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Field-Level Verification Matrix</h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                @foreach($fieldStatuses as $field => $st)
                    <div class="p-2.5 rounded border flex items-center justify-between {{ $st === 'verified' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : ($st === 'review' ? 'bg-amber-50 border-amber-200 text-amber-800' : ($st === 'failed' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-gray-50 border-gray-200 text-gray-500')) }}">
                        <span class="font-bold capitalize">{{ str_replace(['_status', '_'], ['', ' '], $field) }}</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $st === 'verified' ? 'bg-emerald-200 text-emerald-900' : ($st === 'review' ? 'bg-amber-200 text-amber-900' : ($st === 'failed' ? 'bg-rose-200 text-rose-900' : 'bg-gray-200 text-gray-700')) }}">
                            {{ $st }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Discrepancies Alerts -->
    @if(!empty($discrepancies))
        <div class="space-y-3">
            <h4 class="text-xs font-bold text-rose-700 uppercase tracking-wide">Discrepancy Inspections</h4>
            @foreach($discrepancies as $disc)
                <div class="p-4 border rounded-lg space-y-2 {{ ($disc['severity'] ?? '') === 'critical' ? 'bg-rose-50 border-rose-300 text-rose-900' : 'bg-amber-50 border-amber-300 text-amber-900' }}">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span>{{ ($disc['severity'] ?? '') === 'critical' ? '❌ ' : '⚠ ' }}{{ $disc['message'] ?? '' }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] uppercase font-mono">{{ $disc['code'] ?? 'DISCREPANCY' }}</span>
                    </div>
                    @if(!empty($disc['source']) || !empty($disc['extracted']))
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-2 border-t text-xs font-mono {{ ($disc['severity'] ?? '') === 'critical' ? 'border-rose-200' : 'border-amber-200' }}">
                            <div class="bg-white/80 p-2 rounded">
                                <span class="text-gray-500 font-bold block text-[10px]">SOURCE EVIDENCE</span>
                                <span class="text-gray-800">{{ $disc['source'] ?? '—' }}</span>
                            </div>
                            <div class="bg-white/80 p-2 rounded">
                                <span class="text-gray-500 font-bold block text-[10px]">PARSED / EXTRACTED</span>
                                <span class="text-gray-800">{{ $disc['extracted'] ?? '—' }}</span>
                            </div>
                            <div class="bg-white/80 p-2 rounded">
                                <span class="text-gray-500 font-bold block text-[10px]">DIFFERENCE</span>
                                <span class="font-bold {{ ($disc['severity'] ?? '') === 'critical' ? 'text-rose-700' : 'text-amber-700' }}">{{ $disc['difference'] ?? 'Mismatch' }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <!-- Question Details Box -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm space-y-6">
        <!-- Question Prompt -->
        <div class="space-y-2">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Question Prompt</h4>
            <div class="text-sm font-medium text-gray-900 leading-relaxed bg-gray-50 p-4 border border-gray-200 rounded-lg whitespace-pre-line">
                {{ $norm['question_text'] ?? 'Question text not available.' }}
            </div>
        </div>

        <!-- Question Exhibits -->
        @if(!empty($qExhibits))
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Question Exhibit(s)</h4>
                <div class="grid grid-cols-1 gap-4">
                    @foreach($qExhibits as $img)
                        <div class="border border-gray-200 rounded-lg p-2.5 bg-gray-50 text-center">
                            <img src="{{ $img['url'] ?? '' }}" alt="{{ $img['caption'] ?? 'Exhibit' }}" class="max-h-96 mx-auto rounded shadow-sm">
                            <span class="text-[11px] text-gray-500 block mt-1.5">{{ $img['caption'] ?? 'Exhibit' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Options & Solutions -->
        @if(in_array($type, ['single_choice', 'multiple_choice', 'yes_no']) && !empty($options))
            <div class="space-y-2.5">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Options & Solutions</h4>
                <div class="space-y-2">
                    @foreach($options as $opt)
                        @php $isCorrect = in_array($opt['key'], $correctAnswers, true); @endphp
                        <div class="flex items-center space-x-3 p-3.5 border rounded-lg {{ $isCorrect ? 'border-emerald-500 bg-emerald-50/60 font-semibold' : 'border-gray-200 bg-white' }}">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $isCorrect ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-600' }}">
                                {{ $opt['key'] }}
                            </span>
                            <span class="text-sm text-gray-800 flex-grow">{{ $opt['text'] }}</span>
                            @if($isCorrect)
                                <span class="text-xs font-bold text-emerald-600">✓ Correct</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Hotspot Structured Solutions -->
        @if($type === 'hotspot' && !empty($answerArea['boxes']))
            <div class="space-y-3 p-4 bg-emerald-50/40 border border-emerald-200 rounded-lg">
                <h4 class="text-xs font-bold text-emerald-800 uppercase">Structured Hotspot Solutions</h4>
                <div class="space-y-2">
                    @foreach($answerArea['boxes'] as $box)
                        <div class="flex items-center space-x-2 text-xs">
                            <span class="font-bold text-emerald-700">{{ $box['label'] ?? ('Box ' . ($loop->index + 1)) }}:</span>
                            <span class="text-gray-800 font-semibold">{{ $box['correct'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Drag & Drop Structured Sequence -->
        @if($type === 'drag_drop' && !empty($answerArea['steps']))
            <div class="space-y-3 p-4 bg-emerald-50/40 border border-emerald-200 rounded-lg">
                <h4 class="text-xs font-bold text-emerald-800 uppercase">Structured Drag & Drop Sequence</h4>
                <div class="space-y-2">
                    @foreach($answerArea['steps'] as $step)
                        <div class="flex items-center space-x-2 text-xs">
                            <span class="font-bold text-emerald-700">{{ $step['label'] ?? ('Step ' . ($loop->index + 1)) }}:</span>
                            <span class="text-gray-800 font-semibold">{{ $step['text'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Answer-Only Exhibits -->
        @if(!empty($aExhibits))
            <div class="space-y-3 border-t pt-4">
                <h4 class="text-xs font-bold text-amber-600 uppercase">Protected Answer-Only Exhibit(s)</h4>
                <div class="grid grid-cols-1 gap-4">
                    @foreach($aExhibits as $img)
                        <div class="border border-amber-200 rounded-lg p-2.5 bg-amber-50/40 text-center">
                            <img src="{{ $img['url'] ?? '' }}" alt="{{ $img['caption'] ?? 'Answer Exhibit' }}" class="max-h-80 mx-auto rounded shadow-sm">
                            <span class="text-[11px] text-amber-700 block mt-1.5">Highlighted Solution Graphic</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Explanation -->
        <div class="space-y-2">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Explanation & Rationale</h4>
            <div class="text-xs text-gray-700 leading-relaxed bg-gray-50 p-3.5 border border-gray-200 rounded-lg whitespace-pre-line">
                {{ !empty($norm['explanation']) ? $norm['explanation'] : 'Explanation not available' }}
            </div>
        </div>

        <!-- References -->
        <div class="space-y-2">
            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Documentation References</h4>
            @if(!empty($norm['references']))
                <ul class="list-disc pl-5 text-xs text-navy space-y-1.5">
                    @foreach($norm['references'] as $ref)
                        <li>
                            <a href="{{ $ref['url'] ?? '#' }}" target="_blank" class="hover:underline font-medium text-cyan">
                                {{ $ref['url'] ?? ($ref['title'] ?? 'Documentation Link') }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-xs text-gray-400 italic">No references extracted</p>
            @endif
        </div>

        <!-- Raw Text Block -->
        @if(!empty($rawText))
            <div class="space-y-2 border-t pt-4">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Source Segment Raw Text Block</h4>
                <pre class="bg-gray-900 text-gray-100 p-4 rounded text-xs font-mono overflow-x-auto max-h-64 whitespace-pre-wrap">{{ $rawText }}</pre>
            </div>
        @endif
    </div>
</div>
@endsection
