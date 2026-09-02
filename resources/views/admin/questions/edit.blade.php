@extends('layouts.admin')

@php
    $initialOptions = $question->options->isNotEmpty() 
        ? $question->options->map(fn($o) => ['key' => $o->option_key, 'text' => $o->option_text])->values()
        : [['key' => 'A', 'text' => ''], ['key' => 'B', 'text' => ''], ['key' => 'C', 'text' => ''], ['key' => 'D', 'text' => '']];
    $initialAnswers = $question->answers->pluck('answer_value')->values();
    $initialDragItems = $question->question_data['drag_items'] ?? [['id' => 'item_1', 'text' => ''], ['id' => 'item_2', 'text' => '']];
    $initialCorrectOrder = $question->question_data['correct_order'] ?? ['item_1', 'item_2'];
    $rawBoxes = $question->question_data['boxes'] ?? $question->question_data['hotspot_answers'] ?? [['id' => 'box_1', 'label' => 'Box 1', 'options' => [], 'correct_answer' => '']];
    $initialBoxes = collect($rawBoxes)->map(function($h) {
        $opts = $h['options'] ?? [];
        return array_merge($h, ['optionsText' => is_array($opts) ? implode(',', $opts) : '']);
    })->values();
    $initialMatchingPairs = $question->question_data['matching_pairs'] ?? [['left' => '', 'right' => '']];
    $initialReferences = $question->references->isNotEmpty()
        ? $question->references->map(fn($r) => ['title' => $r->title, 'url' => $r->url])->values()
        : [['title' => '', 'url' => '']];
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-6"
     x-data="editQuestionManager()"
     x-init="initComponent()"
     x-cloak>

    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Edit Question #{{ $question->id }}</h1>
        <a href="{{ route('admin.questions.index', ['exam_id' => $question->exam_id]) }}" class="text-sm text-navy hover:underline">
            ← Back to Listing
        </a>
    </div>

    <!-- Edit Form -->
    <div class="bg-white border border-gray-250 rounded-lg p-6 shadow-sm">
        <form action="{{ route('admin.questions.update', $question->id) }}" method="POST" enctype="multipart/form-data" @submit="submitForm($event)" class="space-y-8">
            @csrf
            @method('PUT')
            
            <!-- SECTION A — QUESTION INFORMATION -->
            <div class="border-b border-gray-150 pb-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Section A: Question Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Exam Select -->
                    <div>
                        <label for="exam_id" class="block text-xs font-bold text-gray-400 uppercase mb-2">Certification Exam *</label>
                        <select name="exam_id" id="exam_id" x-model="question.exam_id" required
                                class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan"
                                :class="errors.exam_id ? 'border-red-500 ring-1 ring-red-500' : ''">
                            @foreach($exams as $exam)
                                <option value="{{ $exam->id }}">
                                    {{ $exam->exam_code }} - {{ $exam->exam_name }}
                                </option>
                            @endforeach
                        </select>
                        <template x-if="errors.exam_id">
                            <p class="text-red-500 text-xs mt-1 font-semibold" x-text="errors.exam_id"></p>
                        </template>
                        @error('exam_id')
                            <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Syllabus Topic -->
                    <div>
                        <label for="topic" class="block text-xs font-bold text-gray-400 uppercase mb-2">Syllabus Topic / Chapter Name *</label>
                        <input type="text" name="topic" id="topic" x-model="question.topic" required
                               class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan"
                               :class="errors.topic ? 'border-red-500 ring-1 ring-red-500' : ''">
                        <template x-if="errors.topic">
                            <p class="text-red-500 text-xs mt-1 font-semibold" x-text="errors.topic"></p>
                        </template>
                        @error('topic')
                            <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Question Type -->
                    <div>
                        <label for="question_type" class="block text-xs font-bold text-gray-400 uppercase mb-2">Question Type *</label>
                        <select name="question_type" id="question_type" x-model="question.question_type" required
                                class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                            <option value="single_choice">Single Choice</option>
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="yes_no">Yes / No</option>
                            <option value="drag_drop">Drag & Drop (Ordering)</option>
                            <option value="matching">Matching</option>
                            <option value="hotspot">Hotspot (Multiple Dropdowns)</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-xs font-bold text-gray-400 uppercase mb-2">Status *</label>
                        <select name="status" id="status" x-model="question.status" required
                                class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                            <option value="draft">Draft</option>
                            <option value="pending_review">Pending Review</option>
                            <option value="approved">Approved</option>
                            <option value="published">Published</option>
                            <option value="rejected">Rejected</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- SECTION B — QUESTION CONTENT -->
            <div class="border-b border-gray-150 pb-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Section B: Question Content</h3>
                <div>
                    <label for="question_text" class="block text-xs font-bold text-gray-400 uppercase mb-2">Question Text *</label>
                    <textarea name="question_text" id="question_text" rows="5" x-model="question.question_text" required
                              class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan"
                              :class="errors.question_text ? 'border-red-500 ring-1 ring-red-500' : ''"></textarea>
                    <template x-if="errors.question_text">
                        <p class="text-red-500 text-xs mt-1 font-semibold" x-text="errors.question_text"></p>
                    </template>
                    @error('question_text')
                        <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- SECTION C — INSTRUCTIONS -->
            <div class="border-b border-gray-150 pb-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Section C: Question Instructions (Optional)</h3>
                <div>
                    <label for="instructions" class="block text-xs font-bold text-gray-400 uppercase mb-2">Instructions</label>
                    <input type="text" name="instructions" id="instructions" x-model="question.instructions"
                           class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                </div>
            </div>

            <!-- SECTION D — DYNAMIC OPTIONS -->
            <div class="border-b border-gray-150 pb-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Section D: Options / Sequence Config</h3>

                <!-- Single / Multiple Choice Dynamic Fields -->
                <div x-show="question.question_type === 'single_choice' || question.question_type === 'multiple_choice'" class="space-y-4">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase">Answer Choices (Mark correct answer)</label>
                        <button type="button" @click="addOption()" class="text-xs text-cyan hover:underline font-bold">+ Add Option</button>
                    </div>
                    
                    <template x-if="errors.options">
                        <p class="text-red-500 text-xs font-semibold" x-text="errors.options"></p>
                    </template>
                    <template x-if="errors.correct_answers">
                        <p class="text-red-500 text-xs font-semibold" x-text="errors.correct_answers"></p>
                    </template>

                    <div class="space-y-3">
                        <template x-for="(opt, idx) in question.options" :key="opt.key">
                            <div class="flex items-center space-x-3">
                                <span class="text-sm font-bold text-gray-500 w-5 text-center" x-text="opt.key"></span>
                                <input type="hidden" :name="'options['+idx+'][key]'" :value="opt.key">
                                <input type="text" :name="'options['+idx+'][text]'" x-model="opt.text" required placeholder="Option Choice"
                                       class="flex-grow border-gray-300 rounded text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan">
                                
                                <!-- Correct Selector -->
                                <div class="flex items-center pl-2">
                                     <!-- Single Choice Radio -->
                                     <template x-if="question.question_type === 'single_choice'">
                                         <input type="radio" name="correct_option" :value="opt.key"
                                                :checked="question.correct_answers.includes(opt.key)"
                                                @change="question.correct_answers = [opt.key]"
                                                class="rounded-full border-gray-300 text-cyan focus:ring-cyan h-5 w-5 cursor-pointer">
                                     </template>
                                     <!-- Multiple Choice Checkbox -->
                                     <template x-if="question.question_type === 'multiple_choice'">
                                         <input type="checkbox" name="correct_answers[]" :value="opt.key"
                                                :checked="question.correct_answers.includes(opt.key)"
                                                @change="if ($event.target.checked) { if (!question.correct_answers.includes(opt.key)) question.correct_answers.push(opt.key) } else { question.correct_answers = question.correct_answers.filter(k => k !== opt.key) }"
                                                class="rounded border-gray-300 text-cyan focus:ring-cyan h-5 w-5 cursor-pointer">
                                     </template>
                                 </div>
                                 <button type="button" @click="removeOption(idx)" :disabled="question.options.length <= 2"
                                         :class="question.options.length <= 2 ? 'text-gray-300 cursor-not-allowed' : 'text-red-500 hover:text-red-700'"
                                         class="text-sm px-1 font-bold">✕</button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Yes / No Choice -->
                <div x-show="question.question_type === 'yes_no'" class="space-y-4">
                    <label class="block text-xs font-bold text-gray-400 uppercase">Correct Response</label>
                    <div class="space-y-3">
                        <template x-for="(opt, idx) in question.options" :key="opt.key">
                            <div class="flex items-center space-x-4 p-3 border border-gray-200 rounded">
                                <input type="hidden" :name="'options['+idx+'][key]'" :value="opt.key">
                                <input type="hidden" :name="'options['+idx+'][text]'" :value="opt.text">
                                <span class="text-sm font-bold text-gray-700" x-text="opt.text"></span>
                                <div class="flex-grow"></div>
                                <input type="radio" name="correct_option" :value="opt.key"
                                       :checked="question.correct_answers.includes(opt.key)"
                                       @change="question.correct_answers = [opt.key]" required
                                       class="rounded-full border-gray-300 text-cyan focus:ring-cyan h-5 w-5 cursor-pointer">
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Drag & Drop / Ordering -->
                <div x-show="question.question_type === 'drag_drop'" class="space-y-4">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase">Sequence Items (Ordered Top to Bottom)</label>
                        <button type="button" @click="addDragItem()" class="text-xs text-cyan hover:underline font-bold">+ Add Item</button>
                    </div>
                    
                    <template x-if="errors.drag_items">
                        <p class="text-red-500 text-xs font-semibold" x-text="errors.drag_items"></p>
                    </template>

                    <div class="space-y-3">
                        <template x-for="(item, idx) in question.question_data.drag_items" :key="item.id">
                            <div class="flex items-center space-x-3 bg-gray-50 p-2.5 border border-gray-200 rounded">
                                <span class="text-xs font-bold text-gray-400" x-text="idx + 1"></span>
                                <input type="hidden" :name="'drag_items['+idx+'][id]'" :value="item.id">
                                <input type="hidden" name="correct_order[]" :value="item.id">
                                <input type="text" :name="'drag_items['+idx+'][text]'" x-model="item.text" placeholder="Drag Item Text"
                                       class="flex-grow border-gray-300 rounded text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan bg-white">
                                <div class="flex items-center space-x-1">
                                    <button type="button" @click="moveDragItem(idx, 'up')" x-show="idx > 0" class="text-gray-500 hover:text-navy text-xs font-bold px-1">▲</button>
                                    <button type="button" @click="moveDragItem(idx, 'down')" x-show="idx < question.question_data.drag_items.length - 1" class="text-gray-500 hover:text-navy text-xs font-bold px-1">▼</button>
                                </div>
                                <button type="button" @click="removeDragItem(idx)" :disabled="question.question_data.drag_items.length <= 2"
                                        :class="question.question_data.drag_items.length <= 2 ? 'text-gray-300 cursor-not-allowed' : 'text-red-500 hover:text-red-700'"
                                        class="text-sm pl-2 font-bold">✕</button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Matching Pairs -->
                <div x-show="question.question_type === 'matching'" class="space-y-4">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase">Matching Definitions</label>
                        <button type="button" @click="addMatchingPair()" class="text-xs text-cyan hover:underline font-bold">+ Add Pair</button>
                    </div>
                    
                    <template x-if="errors.matching">
                        <p class="text-red-500 text-xs font-semibold" x-text="errors.matching"></p>
                    </template>

                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-4 text-xs font-bold text-gray-400 uppercase">
                            <div>Left Item</div>
                            <div>Correct Match</div>
                        </div>
                        <template x-for="(pair, idx) in question.question_data.matching_pairs" :key="idx">
                            <div class="flex items-center space-x-3">
                                <input type="text" :name="'matching_pairs['+idx+'][left]'" x-model="pair.left" placeholder="Left Text"
                                       class="w-1/2 border-gray-300 rounded text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan">
                                <input type="text" :name="'matching_pairs['+idx+'][right]'" x-model="pair.right" placeholder="Correct Match Text"
                                       class="w-1/2 border-gray-300 rounded text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan">
                                <button type="button" @click="removeMatchingPair(idx)" :disabled="question.question_data.matching_pairs.length <= 1"
                                        :class="question.question_data.matching_pairs.length <= 1 ? 'text-gray-300 cursor-not-allowed' : 'text-red-500 hover:text-red-700'"
                                        class="text-sm font-bold">✕</button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Hotspot Multiple Dropdowns -->
                <div x-show="question.question_type === 'hotspot'" class="space-y-6">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase">Dynamic Selection Boxes</label>
                        <button type="button" @click="addHotspotBox()" class="text-xs text-cyan hover:underline font-bold">+ Add Box</button>
                    </div>
                    
                    <template x-if="errors.hotspot">
                        <p class="text-red-500 text-xs font-semibold" x-text="errors.hotspot"></p>
                    </template>

                    <div class="space-y-4">
                        <template x-for="(box, idx) in question.question_data.boxes" :key="box.id">
                            <div class="bg-gray-50 border border-gray-250 p-4 rounded-lg space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-gray-700 uppercase" x-text="'Box #' + (idx + 1)"></span>
                                    <button type="button" @click="removeHotspotBox(idx)" :disabled="question.question_data.boxes.length <= 1"
                                            :class="question.question_data.boxes.length <= 1 ? 'text-gray-300 cursor-not-allowed' : 'text-red-500 hover:text-red-700'"
                                            class="text-xs font-bold">Remove Box</button>
                                </div>
                                <input type="hidden" :name="'boxes['+idx+'][id]'" :value="box.id">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Label *</label>
                                        <input type="text" :name="'boxes['+idx+'][label]'" x-model="box.label" placeholder="e.g. Choose subnet"
                                               class="w-full border-gray-300 rounded text-sm px-3 py-1 focus:border-cyan focus:ring-cyan bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Dropdown Choices (comma-separated) *</label>
                                        <input type="text" x-model="box.optionsText" @input="updateHotspotOptions(idx)" placeholder="Option 1, Option 2, Option 3"
                                               class="w-full border-gray-300 rounded text-sm px-3 py-1 focus:border-cyan focus:ring-cyan bg-white">
                                        <input type="hidden" :name="'boxes['+idx+'][options]'" :value="box.options.join(',')">
                                    </div>
                                </div>
                                
                                <div x-show="box.options.length > 0">
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Correct Answer Select *</label>
                                    <select :name="'boxes['+idx+'][correct_answer]'" x-model="box.correct_answer"
                                            class="w-full border-gray-300 rounded text-sm px-3 py-1 focus:border-cyan focus:ring-cyan bg-white">
                                        <option value="">Select correct answer</option>
                                        <template x-for="opt in box.options" :key="opt">
                                            <option :value="opt" x-text="opt" :selected="box.correct_answer === opt"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- EXPLANATION SECTION -->
            <div class="border-b border-gray-150 pb-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Section E: Explanation</h3>
                <div>
                    <label for="explanation" class="block text-xs font-bold text-gray-400 uppercase mb-2">Answer Explanation</label>
                    <textarea name="explanation" id="explanation" rows="3" x-model="question.explanation" placeholder="Reference guide documentation explanation..."
                              class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan"></textarea>
                </div>
            </div>

            <!-- REFERENCES SECTION -->
            <div class="border-b border-gray-150 pb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-gray-700 uppercase">Section F: References</h3>
                    <button type="button" @click="addReference()" class="text-xs text-cyan hover:underline font-bold">+ Add Reference</button>
                </div>
                
                <template x-if="errors.references">
                    <p class="text-red-500 text-xs font-semibold mb-2" x-text="errors.references"></p>
                </template>

                <div class="space-y-3">
                    <template x-for="(ref, idx) in question.references" :key="idx">
                        <div class="flex items-center space-x-3">
                            <input type="text" :name="'references['+idx+'][title]'" x-model="ref.title" placeholder="Reference Title"
                                   class="w-1/2 border-gray-300 rounded text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan">
                            <input type="text" :name="'references['+idx+'][url]'" x-model="ref.url" placeholder="URL"
                                   class="w-1/2 border-gray-300 rounded text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan">
                            <button type="button" @click="removeReference(idx)" class="text-red-500 hover:text-red-700 text-sm font-bold">✕</button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- MEDIA / EXHIBITS -->
            <div class="border-b border-gray-150 pb-6">
                <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Section G: Exhibits / Media</h3>
                
                @if($question->media->isNotEmpty())
                    <div class="mb-4 space-y-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase">Active Exhibits</label>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach($question->media as $med)
                                <div class="flex items-center space-x-3 bg-gray-50 p-2.5 border border-gray-200 rounded">
                                    <img src="{{ $med->media_url }}" alt="{{ $med->alt_text }}" class="h-12 w-12 object-cover rounded">
                                    <div class="text-xs">
                                        <div class="font-bold text-gray-700">{{ $med->caption }}</div>
                                        <div class="text-gray-400 font-mono">{{ basename($med->media_url) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="media_file" class="block text-xs font-bold text-gray-400 uppercase mb-2">Upload New Image (Replaces existing)</label>
                        <input type="file" name="media_file" id="media_file" accept=".png,.jpg,.jpeg,.webp"
                               class="w-full border border-gray-300 rounded text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-navy hover:file:bg-gray-200">
                    </div>
                    <div>
                        <label for="media_caption" class="block text-xs font-bold text-gray-400 uppercase mb-2">New Exhibit Caption</label>
                        <input type="text" name="media_caption" id="media_caption" placeholder="e.g. New network layout diagram"
                               class="w-full border-gray-300 rounded text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                    </div>
                </div>
            </div>

            <!-- Active Status Checkbox -->
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" x-model="question.is_active"
                       class="rounded border-gray-300 text-cyan focus:ring-cyan h-4 w-4">
                <label for="is_active" class="ml-2 text-sm font-semibold text-gray-700">Mark as Active (Show in test engine sessions)</label>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 flex justify-end space-x-3">
                <a href="{{ route('admin.questions.index', ['exam_id' => $question->exam_id]) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-bold py-2.5 px-6 rounded transition">
                    Cancel
                </a>
                <button type="submit" :disabled="saving"
                        class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2.5 px-6 rounded shadow transition disabled:opacity-50 flex items-center space-x-2">
                    <span x-text="saving ? 'Saving...' : 'Update Question'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editQuestionManager() {
    return {
        question: {
            exam_id: @json(old('exam_id', (string)$question->exam_id)),
            topic: @json(old('topic', (string)($question->topic ?? 'General'))),
            question_type: @json(old('question_type', (string)($question->question_type ?? 'single_choice'))),
            status: @json(old('status', (string)($question->status ?? 'draft'))),
            question_text: @json(old('question_text', (string)($question->question_text ?? ''))),
            instructions: @json(old('instructions', (string)($question->instructions ?? ''))),
            options: @json($initialOptions),
            correct_answers: @json($initialAnswers),
            question_data: {
                drag_items: @json($initialDragItems),
                correct_order: @json($initialCorrectOrder),
                boxes: @json($initialBoxes),
                matching_pairs: @json($initialMatchingPairs)
            },
            explanation: @json(old('explanation', (string)($question->explanation ?? ''))),
            references: @json($initialReferences),
            media: @json($question->media),
            is_active: {{ $question->is_active ? 'true' : 'false' }},
            source_type: @json((string)($question->source_type ?? 'manual'))
        },
        errors: {},
        saving: false,

        initComponent() {
            this.$watch('question.question_type', () => this.watchType());
            if (this.question.question_data.drag_items && this.question.question_data.drag_items.length) {
                this.syncDragOrder();
            }
        },

        getNextOptionKey(options) {
            const index = options.length;
            if (index < 26) {
                return String.fromCharCode(65 + index);
            }
            const a = Math.floor(index / 26) - 1;
            const b = index % 26;
            return String.fromCharCode(65 + a) + String.fromCharCode(65 + b);
        },

        addOption() {
            const nextKey = this.getNextOptionKey(this.question.options);
            this.question.options.push({ key: nextKey, text: '' });
        },

        removeOption(index) {
            if (this.question.options.length <= 2) {
                return;
            }
            const removed = this.question.options[index];
            this.question.options.splice(index, 1);
            
            this.question.correct_answers = this.question.correct_answers.filter(k => k !== removed.key);

            this.question.options.forEach((opt, idx) => {
                const oldKey = opt.key;
                const newKey = String.fromCharCode(65 + idx);
                if (oldKey !== newKey) {
                    opt.key = newKey;
                    const caIdx = this.question.correct_answers.indexOf(oldKey);
                    if (caIdx !== -1) {
                        this.question.correct_answers[caIdx] = newKey;
                    }
                }
            });
        },

        addDragItem() {
            const nextId = 'item_' + (this.question.question_data.drag_items.length + 1);
            this.question.question_data.drag_items.push({ id: nextId, text: '' });
            this.syncDragOrder();
        },

        removeDragItem(index) {
            if (this.question.question_data.drag_items.length <= 2) {
                return;
            }
            this.question.question_data.drag_items.splice(index, 1);
            this.syncDragOrder();
        },

        moveDragItem(index, direction) {
            const items = this.question.question_data.drag_items;
            if (direction === 'up' && index > 0) {
                const temp = items[index];
                items[index] = items[index - 1];
                items[index - 1] = temp;
            } else if (direction === 'down' && index < items.length - 1) {
                const temp = items[index];
                items[index] = items[index + 1];
                items[index + 1] = temp;
            }
            this.syncDragOrder();
        },

        syncDragOrder() {
            this.question.question_data.correct_order = this.question.question_data.drag_items.map(item => item.id);
        },

        addHotspotBox() {
            const nextId = 'box_' + (this.question.question_data.boxes.length + 1);
            this.question.question_data.boxes.push({
                id: nextId,
                label: 'Box ' + (this.question.question_data.boxes.length + 1),
                optionsText: '',
                options: [],
                correct_answer: ''
            });
        },

        removeHotspotBox(index) {
            if (this.question.question_data.boxes.length <= 1) {
                return;
            }
            this.question.question_data.boxes.splice(index, 1);
        },

        updateHotspotOptions(index) {
            const box = this.question.question_data.boxes[index];
            box.options = box.optionsText.split(',').map(s => s.trim()).filter(Boolean);
            if (!box.options.includes(box.correct_answer)) {
                box.correct_answer = '';
            }
        },

        addMatchingPair() {
            this.question.question_data.matching_pairs.push({ left: '', right: '' });
        },

        removeMatchingPair(index) {
            if (this.question.question_data.matching_pairs.length <= 1) {
                return;
            }
            this.question.question_data.matching_pairs.splice(index, 1);
        },

        addReference() {
            this.question.references.push({ title: '', url: '' });
        },

        removeReference(index) {
            this.question.references.splice(index, 1);
        },

        watchType() {
            this.errors = {};
            if (this.question.question_type === 'yes_no' && this.question.options.length !== 2) {
                this.question.options = [
                    { key: 'A', text: 'Yes' },
                    { key: 'B', text: 'No' }
                ];
                this.question.correct_answers = ['A'];
            } else if (this.question.question_type === 'single_choice') {
                if (this.question.correct_answers.length > 1) {
                    this.question.correct_answers = [this.question.correct_answers[0]];
                }
            }
        },

        validateForm() {
            this.errors = {};

            if (!this.question.exam_id) {
                this.errors.exam_id = 'Certification Exam is required.';
            }
            if (!this.question.topic || !this.question.topic.trim()) {
                this.errors.topic = 'Syllabus Topic / Chapter Name is required.';
            }
            if (!this.question.question_text || !this.question.question_text.trim()) {
                this.errors.question_text = 'Question Text is required.';
            }

            if (this.question.question_type === 'single_choice') {
                if (this.question.options.length < 2) {
                    this.errors.options = 'Single Choice requires at least 2 options.';
                }
                const emptyOpt = this.question.options.some(o => !o.text || !o.text.trim());
                if (emptyOpt) {
                    this.errors.options = 'All option text fields must be filled.';
                }
                if (this.question.correct_answers.length !== 1) {
                    this.errors.correct_answers = 'Exactly 1 correct answer must be selected.';
                }
            } else if (this.question.question_type === 'multiple_choice') {
                if (this.question.options.length < 2) {
                    this.errors.options = 'Multiple Choice requires at least 2 options.';
                }
                const emptyOpt = this.question.options.some(o => !o.text || !o.text.trim());
                if (emptyOpt) {
                    this.errors.options = 'All option text fields must be filled.';
                }
                if (this.question.correct_answers.length < 2) {
                    this.errors.correct_answers = 'Multiple Choice requires at least 2 correct answers selected.';
                }
            } else if (this.question.question_type === 'yes_no') {
                if (this.question.correct_answers.length !== 1) {
                    this.errors.correct_answers = 'Please select the correct response.';
                }
            } else if (this.question.question_type === 'drag_drop') {
                if (this.question.question_data.drag_items.length < 2) {
                    this.errors.drag_items = 'At least 2 sequenced items are required.';
                }
                const emptyItem = this.question.question_data.drag_items.some(i => !i.text || !i.text.trim());
                if (emptyItem) {
                    this.errors.drag_items = 'All drag & drop items must have text.';
                }
            } else if (this.question.question_type === 'hotspot') {
                if (this.question.question_data.boxes.length < 1) {
                    this.errors.hotspot = 'At least 1 dropdown box configuration is required.';
                }
                for (let b of this.question.question_data.boxes) {
                    if (!b.label.trim()) {
                        this.errors.hotspot = 'All boxes must have a label.';
                        break;
                    }
                    if (b.options.length < 1) {
                        this.errors.hotspot = 'Each box must have dropdown choices provided.';
                        break;
                    }
                    if (!b.correct_answer) {
                        this.errors.hotspot = 'Each box must have a selected correct answer.';
                        break;
                    }
                }
            } else if (this.question.question_type === 'matching') {
                if (this.question.question_data.matching_pairs.length < 1) {
                    this.errors.matching = 'At least 1 matching pair is required.';
                }
                const emptyPair = this.question.question_data.matching_pairs.some(p => !p.left.trim() || !p.right.trim());
                if (emptyPair) {
                    this.errors.matching = 'Both left and right matching text must be filled.';
                }
            }

            for (let ref of this.question.references) {
                if (ref.url && ref.url.trim()) {
                    try {
                        new URL(ref.url.trim());
                    } catch (_) {
                        this.errors.references = 'Invalid reference URL format: ' + ref.url;
                        break;
                    }
                }
            }

            return Object.keys(this.errors).length === 0;
        },

        submitForm(e) {
            if (!this.validateForm()) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return false;
            }

            this.question.references = this.question.references.filter(r => r.title.trim() || r.url.trim());
            console.log('Question Payload:', JSON.stringify(this.question, null, 2));

            this.saving = true;
        }
    };
}
</script>
@endsection
