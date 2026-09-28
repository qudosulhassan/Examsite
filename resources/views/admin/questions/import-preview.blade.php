@extends('layouts.admin')

@section('content')
@php
    $typeLabels = ['multiple-choice' => 'Multiple Choice', 'multiple-select' => 'Multiple Select', 'hotspot' => 'Hotspot', 'drag-drop' => 'Drag & Drop'];
    $errorsById = collect($report['errors'])->groupBy('id');
    $warningsById = collect($report['warnings'])->groupBy('id');
    $importable = $report['stats']['questions'] - count($report['stats']['bad_ids']);
    $answerText = function (array $q) {
        $a = $q['answer'];
        if (!empty($a['correctAnswer'])) return implode(', ', $a['correctAnswer']);
        if ($q['ia'] && !empty($a['rowAnswers'])) return implode(' | ', $a['rowAnswers']);
        if (!empty($a['boxes'])) return collect($a['boxes'])->map(fn ($b) => "Box {$b['box']}: {$b['value']}")->implode(' | ');
        if (!empty($a['answerImages'])) return 'Answer image';
        return '—';
    };
@endphp
<div class="space-y-6" x-data="{ submitting: false, showWarnings: false }">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 font-sora">Check before import</h1>
            <p class="text-xs text-gray-500 mt-1">File: <b>{{ $fileName }}</b> → Exam: <b>{{ $exam->exam_code }}</b> — {{ $exam->exam_name }}</p>
        </div>
        <a href="{{ route('admin.questions.import', ['exam_id' => $exam->id]) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold py-2 px-4 rounded shadow transition">← Upload a different file</a>
    </div>

    @if($codeMismatch)
        <div class="bg-amber-50 border border-amber-300 text-amber-800 text-sm rounded-lg p-4">
            ⚠ This file says it is for <b>{{ $parsed['meta']['code'] }}</b>, but you chose <b>{{ $exam->exam_code }}</b>. Go back if that is the wrong exam.
        </div>
    @endif

    {{-- Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm"><div class="text-2xl font-black text-navy">{{ $report['stats']['questions'] }}</div><div class="text-xs text-gray-500 font-bold uppercase">Questions in file</div></div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm"><div class="text-2xl font-black text-green-600">{{ $importable }}</div><div class="text-xs text-gray-500 font-bold uppercase">Ready to import</div></div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm"><div class="text-2xl font-black {{ count($report['stats']['bad_ids']) ? 'text-red-600' : 'text-gray-400' }}">{{ count($report['stats']['bad_ids']) }}</div><div class="text-xs text-gray-500 font-bold uppercase">With errors (skipped)</div></div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm"><div class="text-2xl font-black text-navy">{{ $report['stats']['images'] }}</div><div class="text-xs text-gray-500 font-bold uppercase">Images</div></div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm text-sm text-gray-700 flex flex-wrap gap-2">
        @foreach($report['stats']['by_type'] as $type => $n)
            <span class="px-2.5 py-1 rounded bg-gray-100 font-bold text-xs">{{ $typeLabels[$type] ?? $type }}: {{ $n }}</span>
        @endforeach
        @foreach($report['stats']['topics'] as $topic => $n)
            <span class="px-2.5 py-1 rounded bg-cyan/10 text-navy text-xs">{{ $topic }}: {{ $n }}</span>
        @endforeach
    </div>

    @if(count($report['errors']))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 space-y-1">
            <h3 class="text-sm font-bold text-red-700">Errors — these questions will be skipped</h3>
            <p class="text-xs text-red-600 mb-2">Ask Claude to fix them (e.g. “Question 12: the correct answer E is not in the options — please fix”) and upload the new file, or import now without them.</p>
            @foreach($report['errors'] as $e)
                <p class="text-xs text-red-700"><b>Question {{ $e['id'] }}:</b> {{ $e['message'] }}</p>
            @endforeach
        </div>
    @endif

    @if(count($report['warnings']))
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <button type="button" @click="showWarnings = !showWarnings" class="text-sm font-bold text-amber-800">
                <span x-text="showWarnings ? '▾' : '▸'"></span> {{ count($report['warnings']) }} warnings (safe to import — worth a look)
            </button>
            <div x-show="showWarnings" x-cloak class="mt-2 space-y-1 max-h-80 overflow-auto">
                @foreach($report['warnings'] as $w)
                    <p class="text-xs text-amber-800"><b>Question {{ $w['id'] }}:</b> {{ $w['message'] }}</p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Confirm --}}
    <form action="{{ route('admin.questions.import.store') }}" method="POST" @submit="submitting = true"
          class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <h3 class="text-sm font-bold text-gray-800">How should these questions be added?</h3>
        <label class="flex items-start gap-3 text-sm">
            <input type="radio" name="mode" value="replace" class="mt-1" @checked($exam->questions_count > 0)>
            <span><b>Replace</b> — delete the {{ $exam->questions_count }} question(s) {{ $exam->exam_code }} has now, then import these.
                <span class="block text-xs text-gray-500">Use this for a new version of the exam. Students' old test attempts lose the deleted questions.</span></span>
        </label>
        <label class="flex items-start gap-3 text-sm">
            <input type="radio" name="mode" value="append" class="mt-1" @checked($exam->questions_count === 0)>
            <span><b>Add to existing questions</b>
                <span class="block text-xs text-gray-500">Use this for part 2, part 3… of a big exam that Claude converted in pieces.</span></span>
        </label>
        <button type="submit" :disabled="submitting || {{ $importable ? 'false' : 'true' }}"
                class="bg-orange hover:bg-opacity-90 text-white text-sm font-bold py-2.5 px-6 rounded shadow transition disabled:opacity-60">
            <span x-show="!submitting">Import {{ $importable }} questions</span>
            <span x-show="submitting" x-cloak>Importing… (this can take a minute)</span>
        </button>
    </form>

    {{-- Question list --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full text-xs">
            <thead class="bg-gray-50 text-gray-500 uppercase font-bold">
                <tr><th class="px-3 py-2 text-left">#</th><th class="px-3 py-2 text-left">Type</th><th class="px-3 py-2 text-left">Question</th><th class="px-3 py-2 text-left">Answer</th><th class="px-3 py-2 text-left">Check</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($parsed['questions'] as $q)
                    @php $qErr = $errorsById->get($q['id']); $qWarn = $warningsById->get($q['id']); @endphp
                    <tr class="{{ $qErr ? 'bg-red-50' : '' }}">
                        <td class="px-3 py-2 font-bold text-navy">{{ $q['id'] }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $typeLabels[$q['type']] ?? $q['type'] }}@if(count($q['images'])) <span title="Has exhibit image">🖼</span>@endif</td>
                        <td class="px-3 py-2 text-gray-700">{{ \Illuminate\Support\Str::limit(implode(' ', $q['question']), 140) }}</td>
                        <td class="px-3 py-2 font-mono text-green-700">{{ \Illuminate\Support\Str::limit($answerText($q), 60) }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            @if($qErr)<span class="text-red-600 font-bold" title="{{ $qErr->pluck('message')->implode(' ') }}">✗ Error</span>
                            @elseif($qWarn)<span class="text-amber-600 font-bold" title="{{ $qWarn->pluck('message')->implode(' ') }}">⚠ Warning</span>
                            @else<span class="text-green-600 font-bold">✓</span>@endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
