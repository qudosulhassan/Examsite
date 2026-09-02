@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="candidateEditComponent()">
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
                <span>Edit Candidate #{{ $item->source_index }}</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-navy text-white">
                    {{ $item->normalized_data['topic'] ?? 'Topic 1' }}
                </span>
            </h1>
        </div>

        <!-- Mode Navigation Buttons -->
        <div class="flex items-center space-x-2">
            <a href="{{ route('admin.questions.import-item-preview', $item->id) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded shadow-sm transition">
                👁 Learner Preview
            </a>
            <a href="{{ route('admin.questions.import-item-review', $item->id) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded shadow-sm transition">
                🔍 Admin Review
            </a>
            <span class="px-3.5 py-1.5 rounded text-xs font-bold bg-cyan text-navy shadow-sm">
                ✏ Edit Candidate
            </span>
            <a href="{{ route('admin.questions.import-review', $batch->uuid) }}" class="px-3.5 py-1.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold rounded shadow-sm transition">
                &larr; Back to Batch
            </a>
        </div>
    </div>

    <!-- Edit Form Card -->
    <form @submit.prevent="saveCandidate()" class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm space-y-6">
        @csrf
        @method('PUT')

        <!-- Success / Error Alert -->
        <template x-if="successMessage">
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded text-xs text-emerald-800 font-bold" x-text="successMessage"></div>
        </template>
        <template x-if="errorMessage">
            <div class="p-3 bg-rose-50 border border-rose-200 rounded text-xs text-rose-800 font-bold" x-text="errorMessage"></div>
        </template>

        <!-- Top Metadata Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Target Exam <span class="text-rose-500">*</span></label>
                <select x-model="form.exam_id" class="w-full border-gray-300 rounded text-xs p-2 focus:border-cyan focus:ring-cyan">
                    <option value="">-- Select Target Exam --</option>
                    @foreach($exams as $ex)
                        <option value="{{ $ex->id }}">{{ $ex->exam_code }} — {{ $ex->exam_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Topic / Domain</label>
                <input type="text" x-model="form.topic" class="w-full border-gray-300 rounded text-xs p-2 focus:border-cyan focus:ring-cyan">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Question Type <span class="text-rose-500">*</span></label>
                <select x-model="form.question_type" class="w-full border-gray-300 rounded text-xs p-2 focus:border-cyan focus:ring-cyan">
                    <option value="single_choice">Single Choice</option>
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="hotspot">Hotspot / Dropdown</option>
                    <option value="drag_drop">Drag & Drop</option>
                    <option value="yes_no">Yes / No</option>
                    <option value="case_study">Case Study</option>
                    <option value="simulation">Simulation</option>
                </select>
            </div>
        </div>

        <!-- Question Prompt -->
        <div>
            <label class="block text-xs font-bold text-gray-700 mb-1">Question Prompt Text <span class="text-rose-500">*</span></label>
            <textarea x-model="form.question_text" rows="5" class="w-full border-gray-300 rounded text-xs p-3 font-mono focus:border-cyan focus:ring-cyan"></textarea>
        </div>

        <!-- MCQ Options Section -->
        <div x-show="form.question_type === 'single_choice' || form.question_type === 'multiple_choice' || form.question_type === 'yes_no'" class="space-y-3 border-t pt-4">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold text-gray-700 uppercase">Answer Options & Correct Answer(s)</h4>
                <button type="button" @click="addOption()" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded transition">
                    + Add Option
                </button>
            </div>

            <div class="space-y-2">
                <template x-for="(opt, idx) in form.options" :key="idx">
                    <div class="flex items-center space-x-3 p-2.5 border border-gray-200 rounded-lg bg-gray-50">
                        <label class="flex items-center space-x-1.5 cursor-pointer">
                            <input type="checkbox" :value="opt.key" :checked="form.correct_answers.includes(opt.key)" @change="toggleCorrectAnswer(opt.key)" class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                            <span class="font-bold text-xs w-6 text-center" x-text="opt.key"></span>
                        </label>
                        <input type="text" x-model="opt.text" placeholder="Option text..." class="flex-grow border-gray-300 rounded text-xs p-2 focus:border-cyan focus:ring-cyan">
                        <button type="button" @click="removeOption(idx)" class="text-rose-500 hover:text-rose-700 text-xs font-bold px-2">&times;</button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Hotspot Boxes Editor -->
        <div x-show="form.question_type === 'hotspot'" class="space-y-3 border-t pt-4">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold text-gray-700 uppercase">Hotspot Dropdown Boxes</h4>
                <button type="button" @click="addHotspotBox()" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded transition">
                    + Add Box
                </button>
            </div>

            <div class="space-y-2">
                <template x-for="(box, bIdx) in form.answer_area.boxes" :key="bIdx">
                    <div class="p-3 border border-gray-200 rounded-lg bg-gray-50 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-gray-700" x-text="'Box #' + (bIdx + 1)"></span>
                            <button type="button" @click="removeHotspotBox(bIdx)" class="text-rose-500 hover:text-rose-700 text-xs font-bold">Remove</button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input type="text" x-model="box.label" placeholder="Label (e.g. Box 1)" class="border-gray-300 rounded text-xs p-2">
                            <input type="text" x-model="box.correct" placeholder="Correct Selection Text" class="border-gray-300 rounded text-xs p-2 font-bold text-emerald-700">
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Drag & Drop Sequence Steps Editor -->
        <div x-show="form.question_type === 'drag_drop'" class="space-y-3 border-t pt-4">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold text-gray-700 uppercase">Drag & Drop Sequence Steps</h4>
                <button type="button" @click="addDragStep()" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded transition">
                    + Add Step
                </button>
            </div>

            <div class="space-y-2">
                <template x-for="(step, sIdx) in form.answer_area.steps" :key="sIdx">
                    <div class="flex items-center space-x-2 p-2.5 border border-gray-200 rounded-lg bg-gray-50">
                        <span class="font-bold text-cyan text-xs w-16" x-text="'Step ' + (sIdx + 1) + ':'"></span>
                        <input type="text" x-model="step.text" placeholder="Action description..." class="flex-grow border-gray-300 rounded text-xs p-2">
                        <button type="button" @click="removeDragStep(sIdx)" class="text-rose-500 hover:text-rose-700 text-xs font-bold px-2">&times;</button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Explanation -->
        <div class="border-t pt-4">
            <label class="block text-xs font-bold text-gray-700 mb-1">Explanation & Rationale</label>
            <textarea x-model="form.explanation" rows="4" class="w-full border-gray-300 rounded text-xs p-3 focus:border-cyan focus:ring-cyan"></textarea>
        </div>

        <!-- References -->
        <div class="border-t pt-4 space-y-2">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-bold text-gray-700">Documentation References</label>
                <button type="button" @click="addReference()" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded transition">
                    + Add URL
                </button>
            </div>
            <div class="space-y-2">
                <template x-for="(ref, rIdx) in form.references" :key="rIdx">
                    <div class="flex items-center space-x-2">
                        <input type="url" x-model="ref.url" placeholder="https://..." class="flex-grow border-gray-300 rounded text-xs p-2 font-mono">
                        <button type="button" @click="removeReference(rIdx)" class="text-rose-500 hover:text-rose-700 text-xs font-bold px-2">&times;</button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end space-x-3 pt-4 border-t">
            <a href="{{ route('admin.questions.import-review', $batch->uuid) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded text-xs font-bold hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" :disabled="isSaving" class="px-6 py-2 bg-navy text-white text-xs font-bold rounded shadow hover:bg-opacity-95 disabled:opacity-50">
                <span x-text="isSaving ? 'Saving...' : 'Save Changes'"></span>
            </button>
        </div>
    </form>
</div>

<script>
function candidateEditComponent() {
    const rawItem = @json($item);
    const data = rawItem.normalized_data || {};

    return {
        isSaving: false,
        successMessage: '',
        errorMessage: '',
        form: {
            id: rawItem.id,
            exam_id: data.exam_id || '{{ $batch->default_exam_id ?? '' }}',
            topic: data.topic || 'Topic 1',
            question_type: data.question_type || 'single_choice',
            question_text: data.question_text || '',
            instructions: data.instructions || '',
            options: JSON.parse(JSON.stringify(data.options || [])),
            correct_answers: JSON.parse(JSON.stringify(data.correct_answers || [])),
            answer_area: {
                boxes: JSON.parse(JSON.stringify(data.answer_area?.boxes || [])),
                steps: JSON.parse(JSON.stringify(data.answer_area?.steps || []))
            },
            explanation: data.explanation || '',
            references: JSON.parse(JSON.stringify(data.references || []))
        },

        addOption() {
            const nextKey = String.fromCharCode(65 + this.form.options.length);
            this.form.options.push({ key: nextKey, text: '' });
        },

        removeOption(index) {
            const removed = this.form.options[index];
            this.form.options.splice(index, 1);
            this.form.correct_answers = this.form.correct_answers.filter(k => k !== removed.key);
            this.form.options.forEach((opt, idx) => {
                const newKey = String.fromCharCode(65 + idx);
                const oldKey = opt.key;
                opt.key = newKey;
                const caIdx = this.form.correct_answers.indexOf(oldKey);
                if (caIdx !== -1) {
                    this.form.correct_answers[caIdx] = newKey;
                }
            });
        },

        toggleCorrectAnswer(key) {
            if (this.form.question_type === 'single_choice' || this.form.question_type === 'yes_no') {
                this.form.correct_answers = [key];
            } else {
                if (this.form.correct_answers.includes(key)) {
                    this.form.correct_answers = this.form.correct_answers.filter(k => k !== key);
                } else {
                    this.form.correct_answers.push(key);
                }
            }
        },

        addHotspotBox() {
            const boxNum = this.form.answer_area.boxes.length + 1;
            this.form.answer_area.boxes.push({
                box_number: boxNum,
                label: 'Box ' + boxNum,
                correct: ''
            });
        },

        removeHotspotBox(index) {
            this.form.answer_area.boxes.splice(index, 1);
        },

        addDragStep() {
            const stepNum = this.form.answer_area.steps.length + 1;
            this.form.answer_area.steps.push({
                step_number: stepNum,
                label: 'Step ' + stepNum,
                text: ''
            });
        },

        removeDragStep(index) {
            this.form.answer_area.steps.splice(index, 1);
        },

        addReference() {
            this.form.references.push({ title: 'Documentation', url: '' });
        },

        removeReference(index) {
            this.form.references.splice(index, 1);
        },

        async saveCandidate() {
            this.isSaving = true;
            this.successMessage = '';
            this.errorMessage = '';

            try {
                const res = await fetch(`/admin/questions/import/item/${this.form.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });
                const data = await res.json();
                if (data.success) {
                    this.successMessage = data.message || 'Saved successfully!';
                } else {
                    this.errorMessage = data.message || 'Failed to save changes.';
                }
            } catch (err) {
                this.errorMessage = 'Network error: ' + err.message;
            } finally {
                this.isSaving = false;
            }
        }
    };
}
</script>
@endsection
