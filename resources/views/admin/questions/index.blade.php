@extends('layouts.admin')

@section('content')
<div class="space-y-6" x-data="questionsManager()" x-cloak>
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 font-sora">Manage Certification Questions</h1>
            <p class="text-xs text-gray-500 mt-1">Total Questions in Bank: <strong>{{ $questions->total() }}</strong></p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.questions.import-pdf-form') }}" class="bg-navy hover:bg-opacity-90 text-cyan text-xs font-bold py-2 px-4 rounded shadow transition flex items-center space-x-1 border border-cyan/30">
                <svg class="w-4 h-4 text-cyan mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <span>Import PDF</span>
            </a>
            <a href="{{ route('admin.questions.import-form') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold py-2 px-4 rounded shadow transition">
                Import JSON
            </a>
            <a href="{{ route('admin.questions.create') }}" class="bg-orange hover:bg-opacity-90 text-white text-xs font-bold py-2 px-4 rounded shadow transition">
                + Add New Question
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-6">
            <a href="{{ route('admin.questions.index') }}" class="border-orange text-orange whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs">
                All Questions ({{ $questions->total() }})
            </a>
            <a href="{{ route('admin.questions.create') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs">
                + Add Question
            </a>
            <a href="{{ route('admin.questions.import-pdf-form') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs">
                Import PDF
            </a>
            <a href="{{ route('admin.questions.import-form') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs">
                Import JSON
            </a>
            <a href="{{ route('admin.questions.import-history') }}" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-3 px-1 border-b-2 font-bold text-xs">
                Import History
            </a>
        </nav>
    </div>

    <!-- Filter Bar & Exam Wide Actions -->
    <div class="bg-white p-4 border border-gray-250 rounded-lg shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
            <form action="{{ route('admin.questions.index') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3 flex-grow">
                <div class="flex-grow w-full sm:w-auto">
                    <label for="exam_id" class="block text-xs font-bold text-gray-400 uppercase mb-1.5">Filter by Certification Exam</label>
                    <select name="exam_id" id="exam_id" 
                            class="w-full border-gray-300 rounded text-xs px-3 py-2.5 focus:border-cyan focus:ring-cyan">
                        <option value="">-- All Certification Exams ({{ $exams->sum('questions_count') ?? '' }}) --</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" {{ $examId == $exam->id ? 'selected' : '' }}>
                                {{ $exam->exam_code }} — {{ $exam->exam_name }} ({{ $exam->questions_count ?? $exam->questions()->count() }} questions)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex space-x-2 w-full sm:w-auto">
                    <button type="submit" class="bg-navy text-white text-xs font-bold px-5 py-2.5 rounded hover:bg-opacity-95 transition">
                        Apply Filter
                    </button>
                    @if($examId)
                        <a href="{{ route('admin.questions.index') }}" class="bg-gray-100 text-gray-600 text-xs font-bold px-4 py-2.5 rounded hover:bg-gray-200 transition">
                            Clear
                        </a>
                    @endif
                </div>
            </form>

            <!-- Exam-Wide Bulk Delete Button (When filtered by specific exam) -->
            @if($examId && $questions->total() > 0)
                @php
                    $selectedExam = $exams->firstWhere('id', $examId);
                @endphp
                <div class="pt-2 lg:pt-0 border-t lg:border-t-0 border-gray-150 flex-shrink-0">
                    <button type="button" 
                            @click="confirmDeleteAllExam('{{ $examId }}', '{{ $selectedExam->exam_code ?? 'Exam' }}', {{ $questions->total() }})"
                            class="w-full sm:w-auto bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 text-xs font-bold px-4 py-2.5 rounded transition flex items-center justify-center space-x-1.5 shadow-sm">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        <span>Delete All {{ $questions->total() }} Questions in {{ $selectedExam->exam_code ?? '' }}</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- STICKY / FLOATING BULK ACTIONS TOOLBAR -->
    <div x-show="selectedIds.length > 0" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="bg-navy text-white p-4 rounded-lg shadow-xl border border-cyan/30 flex flex-wrap items-center justify-between gap-3">
        
        <div class="flex items-center space-x-3">
            <span class="w-3 h-3 rounded-full bg-cyan animate-pulse"></span>
            <span class="text-xs font-bold">
                <strong class="text-cyan text-sm" x-text="selectedIds.length"></strong> question(s) selected
            </span>
            <button type="button" @click="selectedIds = []" class="text-gray-400 hover:text-white text-xs underline">
                Deselect All
            </button>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <!-- Delete Selected -->
            <button type="button" 
                    @click="executeBulkAction('delete')" 
                    class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold px-3.5 py-1.5 rounded transition flex items-center space-x-1 shadow">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                <span>Delete Selected (<span x-text="selectedIds.length"></span>)</span>
            </button>

            <!-- Activate Selected -->
            <button type="button" 
                    @click="executeBulkAction('activate')" 
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3.5 py-1.5 rounded transition shadow">
                Activate Selected
            </button>

            <!-- Deactivate Selected -->
            <button type="button" 
                    @click="executeBulkAction('deactivate')" 
                    class="bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold px-3.5 py-1.5 rounded transition shadow">
                Deactivate Selected
            </button>
        </div>
    </div>

    <!-- Questions Table Card -->
    <div class="bg-white rounded-lg border border-gray-250 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-150">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-10 px-4 py-3 text-center">
                            <input type="checkbox" 
                                   @change="toggleSelectAllPage($event.target.checked)" 
                                   :checked="isPageFullySelected"
                                   class="rounded border-gray-300 text-orange focus:ring-orange cursor-pointer">
                        </th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Exam</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Question Text</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Topic</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Answer</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-150 text-xs">
                    @forelse($questions as $question)
                        <tr class="hover:bg-gray-50/80 transition" :class="selectedIds.includes({{ $question->id }}) ? 'bg-orange/5' : ''">
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" 
                                       :value="{{ $question->id }}" 
                                       x-model.number="selectedIds" 
                                       class="rounded border-gray-300 text-orange focus:ring-orange cursor-pointer">
                            </td>
                            <td class="px-4 py-4 font-bold text-navy whitespace-nowrap">
                                <a href="{{ route('admin.questions.index', ['exam_id' => $question->exam_id]) }}" class="hover:underline hover:text-cyan">
                                    {{ $question->exam->exam_code ?? 'N/A' }}
                                </a>
                            </td>
                            <td class="px-4 py-4 text-gray-700 max-w-md">
                                <div class="font-medium line-clamp-2 leading-relaxed">{{ $question->question_text }}</div>
                            </td>
                            <td class="px-4 py-4 text-gray-500 font-semibold whitespace-nowrap">
                                {{ $question->topic ?: 'General' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-700 border border-gray-200">
                                    {{ str_replace('_', ' ', $question->question_type ?? 'single_choice') }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @php
                                    $answers = $question->answers->pluck('answer_value')->toArray();
                                    $ansText = !empty($answers) ? implode(', ', $answers) : ($question->correct_option ?? '—');
                                @endphp
                                <span class="px-2 py-0.5 rounded bg-cyan/15 text-navy font-bold font-mono text-xs">
                                    {{ $ansText }}
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $question->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $question->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right space-x-2 font-bold whitespace-nowrap">
                                <a href="{{ route('admin.questions.show', $question->id) }}" class="text-navy hover:underline">View</a>
                                <a href="{{ route('admin.questions.preview', $question->id) }}" target="_blank" class="text-cyan hover:underline">Preview ↗</a>
                                <a href="{{ route('admin.questions.edit', $question->id) }}" class="text-emerald-600 hover:underline">Edit</a>
                                <form action="{{ route('admin.questions.destroy', $question->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this question?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <p class="text-sm font-medium">No certification questions found matching criteria.</p>
                                    <a href="{{ route('admin.questions.import-pdf-form') }}" class="inline-block text-xs font-bold text-cyan hover:underline">Import PDF Questions</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($questions->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-150 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-gray-500">
                    Showing <strong>{{ $questions->firstItem() }}</strong> to <strong>{{ $questions->lastItem() }}</strong> of <strong>{{ $questions->total() }}</strong> questions
                </div>
                <div>
                    {{ $questions->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Hidden Bulk Action Submission Form -->
    <form id="bulkActionForm" action="{{ route('admin.questions.bulk-action') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="action" id="bulkFormAction" value="">
        <input type="hidden" name="exam_id" id="bulkFormExamId" value="">
        <div id="bulkFormIdsContainer"></div>
    </form>
</div>

<script>
function questionsManager() {
    return {
        selectedIds: [],
        pageIds: @json($questions->pluck('id')->values()),

        get isPageFullySelected() {
            if (this.pageIds.length === 0) return false;
            return this.pageIds.every(id => this.selectedIds.includes(id));
        },

        toggleSelectAllPage(checked) {
            if (checked) {
                this.pageIds.forEach(id => {
                    if (!this.selectedIds.includes(id)) {
                        this.selectedIds.push(id);
                    }
                });
            } else {
                this.selectedIds = this.selectedIds.filter(id => !this.pageIds.includes(id));
            }
        },

        executeBulkAction(action) {
            if (this.selectedIds.length === 0) {
                alert('Please select at least one question.');
                return;
            }

            if (action === 'delete') {
                if (!confirm(`Are you sure you want to delete the ${this.selectedIds.length} selected questions? This action cannot be undone.`)) {
                    return;
                }
            }

            const form = document.getElementById('bulkActionForm');
            document.getElementById('bulkFormAction').value = action;
            document.getElementById('bulkFormExamId').value = '';

            const container = document.getElementById('bulkFormIdsContainer');
            container.innerHTML = '';
            this.selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'question_ids[]';
                input.value = id;
                container.appendChild(input);
            });

            form.submit();
        },

        confirmDeleteAllExam(examId, examCode, totalCount) {
            const prompt1 = confirm(`WARNING: Are you sure you want to permanently delete ALL ${totalCount} questions for exam ${examCode}?\n\nThis will remove all questions, options, answers, and exhibits for this exam.`);
            if (!prompt1) return;

            const form = document.getElementById('bulkActionForm');
            document.getElementById('bulkFormAction').value = 'delete_all_exam';
            document.getElementById('bulkFormExamId').value = examId;
            document.getElementById('bulkFormIdsContainer').innerHTML = '';
            form.submit();
        }
    };
}
</script>
@endsection
