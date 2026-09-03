@extends('layouts.admin')

@section('content')
@php
    $qData    = $question->question_data ?? [];
    $boxes    = $qData['boxes'] ?? $qData['hotspot_answers'] ?? [];
    $dragItems= $qData['drag_items'] ?? [];
    $mediaItems = $question->media ?? collect();
    $selectionLimit = $qData['selection_limit'] ?? 1;
    $instructions = $question->instructions ?: ($qData['instructions'] ?? null);
@endphp

<div class="max-w-4xl mx-auto space-y-6">

    {{-- Top Bar --}}
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
            <span class="px-3.5 py-1.5 rounded text-xs font-bold bg-cyan text-navy shadow-sm">👁 Learner Preview</span>
            <a href="{{ route('admin.questions.show', $question->id) }}"
               class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-lg shadow-sm transition">
                🔍 Admin Detail
            </a>
            <a href="{{ route('admin.questions.edit', $question->id) }}"
               class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-lg shadow-sm transition">
                ✏ Edit
            </a>
            <a href="{{ route('admin.questions.index', ['exam_id' => $question->exam_id]) }}"
               class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded-lg transition">
                &larr; Back
            </a>
        </div>
    </div>

    {{-- Notice --}}
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 flex items-center justify-between shadow-sm">
        <span><strong>Learner Candidate Simulation:</strong> Correct answers and explanations are hidden.</span>
        <span class="text-xs uppercase font-extrabold text-blue-900 bg-blue-100 px-2.5 py-1 rounded">
            {{ str_replace('_', ' ', $question->question_type ?? 'single_choice') }}
        </span>
    </div>

    {{-- ===== QUESTION CARD (Master HTML Structure) ===== --}}
    <article class="question-card">

        {{-- Question Top --}}
        <div class="question-top">
            <div>
                <span class="question-number">Question #{{ $question->id }}</span>
                @php
                    $bClass = 'multiple-choice';
                    $bLabel = 'Multiple Choice';
                    if ($question->question_type === 'hotspot') { $bClass='hotspot'; $bLabel='Hotspot'; }
                    elseif ($question->question_type === 'drag_drop') { $bClass='drag-drop'; $bLabel='Drag & Drop'; }
                    elseif ($question->question_type === 'multiple_choice') { $bClass='multiple-choice'; $bLabel='Multiple Choice'; }
                    elseif ($question->question_type === 'yes_no') { $bLabel='Yes / No'; }
                @endphp
                <span class="type-badge {{ $bClass }}">{{ $bLabel }}</span>
            </div>
            <span class="status">Learner Preview</span>
        </div>

        {{-- Question Content --}}
        <div class="question-content">

            {{-- Question Text --}}
            <div class="prose max-w-none" style="margin:0 0 11px;">
                {!! nl2br(e($question->question_text)) !!}
            </div>

            {{-- Instructions --}}
            @if($instructions)
                <div style="margin:10px 0 14px;padding:10px 13px;background:#fff4db;border:1px solid #f5cc6b;border-radius:10px;font-size:13px;font-weight:600;color:#7a4f00;">
                    ℹ {{ $instructions }}
                </div>
            @endif

            {{-- Multi-select badge --}}
            @if($question->question_type === 'multiple_choice' && $selectionLimit > 1)
                <div style="margin:8px 0 14px;display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:#eaf2fa;border:1px solid #b3d4ef;border-radius:999px;font-size:12px;font-weight:800;color:#31526f;">
                    ✓ Select {{ $selectionLimit }} options
                </div>
            @endif

            {{-- Exhibits --}}
            @if($mediaItems->isNotEmpty())
                <div class="exhibits">
                    @foreach($mediaItems as $m)
                        <figure>
                            <img src="{{ $m->media_url }}"
                                 alt="{{ $m->alt_text ?? ($m->caption ?? 'Exhibit') }}"
                                 loading="lazy">
                            @if($m->caption)
                                <figcaption>{{ $m->caption }}</figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>
            @endif

            {{-- Single / Multiple Choice Options --}}
            @if(in_array($question->question_type, ['single_choice', 'multiple_choice', 'yes_no']) && $question->options->isNotEmpty())
                <div class="options">
                    @foreach($question->options as $opt)
                        <label class="option">
                            @if($question->question_type === 'multiple_choice')
                                <input type="checkbox" name="preview_choice[]" value="{{ $opt->option_key }}">
                            @else
                                <input type="radio" name="preview_choice" value="{{ $opt->option_key }}">
                            @endif
                            <span class="option-letter">{{ $opt->option_key }}</span>
                            <span class="option-text">{!! $opt->option_text !!}</span>
                        </label>
                    @endforeach
                </div>
            @endif

            {{-- Hotspot Dropdowns --}}
            @if($question->question_type === 'hotspot' && !empty($boxes))
                <div class="special-answer">
                    <div class="special-title">Answer Area Selection</div>
                    @foreach($boxes as $bIdx => $box)
                        @php
                            $label   = $box['label']   ?? ('Dropdown ' . ($bIdx + 1));
                            $choices = $box['options'] ?? [];
                        @endphp
                        <div style="margin-bottom:12px;">
                            <label style="display:block;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#4b5563;margin-bottom:5px;">
                                {{ $label }}
                            </label>
                            <select class="engine-search" style="width:auto;min-width:220px;font-weight:600;">
                                <option value="">[ Select Answer... ]</option>
                                @foreach($choices as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Drag & Drop --}}
            @if($question->question_type === 'drag_drop' && !empty($dragItems))
                <div class="special-answer">
                    <div class="special-title">Sequencing Order — Arrange items in the correct order</div>
                    @foreach($dragItems as $dIdx => $item)
                        <div style="display:flex;align-items:center;gap:10px;padding:10px 13px;background:#fff;border:1px solid var(--border);border-radius:10px;margin-bottom:8px;">
                            <span class="option-letter">{{ $dIdx + 1 }}</span>
                            <span style="font-size:14px;font-weight:600;color:var(--text);">{{ $item['text'] ?? $item }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>{{-- end .question-content --}}

        {{-- Action Buttons (Preview mode — disabled, display only) --}}
        <div class="question-actions">
            <button type="button" class="engine-btn primary" disabled style="opacity:.5;cursor:default;">Check Answer</button>
            <button type="button" class="engine-btn" disabled style="opacity:.5;cursor:default;">Reveal Answer</button>
            <button type="button" class="engine-btn ghost" disabled style="opacity:.5;cursor:default;">Reset</button>
        </div>

    </article>

</div>
@endsection
