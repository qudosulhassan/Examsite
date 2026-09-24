@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="{
        copied: false, uploading: false,
        examId: '{{ old('exam_id', request('exam_id')) }}',
        info: @js($examInfo),
        template: @js($prompt),
        get prompt() {
            const e = this.info[this.examId];
            return this.template
                .replaceAll('{EXAM_CODE}', e ? e.code : '[EXAM CODE]')
                .replaceAll('{EXAM_NAME}', e ? e.name : '[EXAM NAME]')
                .replaceAll('{VENDOR}', e && e.vendor ? e.vendor : '[VENDOR]');
        }
    }">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 font-sora">Import Exam File</h1>
            <p class="text-xs text-gray-500 mt-1">Turn a PDF or Word exam into an online practice test — free, using your Claude plan.</p>
        </div>
        <a href="{{ route('admin.questions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold py-2 px-4 rounded shadow transition">← All Questions</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-4">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    {{-- Step 1 --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 space-y-4">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-navy text-cyan font-black flex items-center justify-center text-sm">1</span>
            <h2 class="text-base font-bold text-gray-800">Convert the PDF / Word file in Claude</h2>
        </div>
        <ol class="list-decimal pl-6 text-sm text-gray-700 space-y-1.5">
            <li>Choose the exam below — its code, name and vendor are filled into the prompt for you.</li>
            <li>Open <a href="https://claude.ai/new" target="_blank" rel="noopener" class="text-cyan font-bold underline">claude.ai</a> and start a new chat.</li>
            <li>Attach <b>your exam PDF or DOCX</b> and the <b>template file</b>
                (<a href="{{ route('admin.questions.import.template') }}" class="text-cyan font-bold underline">download exam-template.html</a>).</li>
            <li>Paste the prompt below and send it.</li>
            <li>Claude gives you an HTML practice test. Download it and open it in your browser to check it works.
                For very large exams Claude may do it in parts — upload each part below using <b>“Add to existing questions”</b>.</li>
        </ol>

        <div>
            <label for="prompt_exam" class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Exam for the prompt</label>
            <select id="prompt_exam" x-model="examId" class="w-full border-gray-300 rounded text-sm px-3 py-2.5 focus:border-cyan focus:ring-cyan">
                <option value="">— Select exam —</option>
                @foreach($exams as $exam)
                    <option value="{{ $exam->id }}">{{ $exam->exam_code }} — {{ $exam->exam_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="relative">
            <textarea id="claude-prompt" readonly rows="12" :value="prompt" class="w-full font-mono text-xs border-gray-300 rounded bg-gray-50 p-3"></textarea>
            <button type="button"
                    @click="navigator.clipboard.writeText(prompt).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                    class="absolute top-2 right-2 bg-navy text-white text-xs font-bold py-1.5 px-3 rounded shadow">
                <span x-text="copied ? '✓ Copied' : 'Copy prompt'"></span>
            </button>
            <p x-show="!examId" class="text-xs text-amber-600 mt-1">Select the exam first so the prompt has the right exam code.</p>
        </div>
    </div>

    {{-- Step 2 --}}
    <form action="{{ route('admin.questions.import.preview') }}" method="POST" enctype="multipart/form-data" @submit="uploading = true"
          class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 space-y-5">
        @csrf
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-navy text-cyan font-black flex items-center justify-center text-sm">2</span>
            <h2 class="text-base font-bold text-gray-800">Upload the file Claude made</h2>
        </div>

        <div>
            <label for="exam_id" class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Exam</label>
            <select name="exam_id" id="exam_id" required x-model="examId" class="w-full border-gray-300 rounded text-sm px-3 py-2.5 focus:border-cyan focus:ring-cyan">
                <option value="">— Select the exam these questions belong to —</option>
                @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" @selected(old('exam_id', request('exam_id')) == $exam->id)>
                        {{ $exam->exam_code }} — {{ $exam->exam_name }} ({{ $exam->questions_count }} questions now)
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Exam not listed? Create it first under <a href="{{ route('admin.exams.create') }}" class="text-cyan underline">Exams → Add</a>.</p>
        </div>

        <div>
            <label for="exam_file" class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Practice-test file (.html or .json)</label>
            <input type="file" name="exam_file" id="exam_file" accept=".html,.htm,.json" required
                   class="block w-full text-sm text-gray-700 border border-gray-300 rounded p-2 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-navy file:text-white file:text-xs file:font-bold">
            <p class="text-xs text-gray-400 mt-1">Nothing is saved yet — the next page shows a check of every question before you import.</p>
        </div>

        <button type="submit" :disabled="uploading" class="bg-orange hover:bg-opacity-90 text-white text-sm font-bold py-2.5 px-6 rounded shadow transition disabled:opacity-60">
            <span x-show="!uploading">Check file →</span>
            <span x-show="uploading" x-cloak>Reading file…</span>
        </button>
    </form>
</div>
@endsection
