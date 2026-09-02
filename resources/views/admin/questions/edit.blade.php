@extends('layouts.admin')

@section('content')
@php
    $initialOptions = $question->options->map(fn($o) => [
        'key' => $o->option_key,
        'text' => $o->option_text,
        'has_new_image' => false,
    ])->toArray();

    if (empty($initialOptions)) {
        $initialOptions = [
            ['key' => 'A', 'text' => $question->option_a ?? '', 'has_new_image' => false],
            ['key' => 'B', 'text' => $question->option_b ?? '', 'has_new_image' => false],
            ['key' => 'C', 'text' => $question->option_c ?? '', 'has_new_image' => false],
            ['key' => 'D', 'text' => $question->option_d ?? '', 'has_new_image' => false],
        ];
        $initialOptions = array_values(array_filter($initialOptions, fn($o) => !empty($o['text'])));
        if (count($initialOptions) < 2) {
            $initialOptions = [
                ['key' => 'A', 'text' => '', 'has_new_image' => false],
                ['key' => 'B', 'text' => '', 'has_new_image' => false],
            ];
        }
    }

    $initialAnswers = $question->answers->pluck('answer_value')->toArray();
    if (empty($initialAnswers) && !empty($question->correct_option)) {
        $initialAnswers = str_contains($question->correct_option, ',')
            ? array_map('trim', explode(',', $question->correct_option))
            : str_split($question->correct_option);
    }

    $qData = $question->question_data ?? [];
    $initialDragItems = $qData['drag_items'] ?? [];
    $initialCorrectOrder = $qData['correct_order'] ?? [];
    $initialBoxes = $qData['boxes'] ?? $qData['hotspot_answers'] ?? [];
    $initialMatchingPairs = $qData['matching_pairs'] ?? [];

    foreach ($initialBoxes as &$b) {
        if (!isset($b['optionsText'])) {
            $b['optionsText'] = is_array($b['options'] ?? null) ? implode(', ', $b['options']) : '';
        }
        if (!isset($b['correct_answer']) && !empty($b['options'])) {
            $b['correct_answer'] = $b['options'][0];
        }
    }
    unset($b);

    $initialReferences = $question->references->map(fn($r) => [
        'title' => $r->title,
        'url' => $r->url,
    ])->toArray();
    if (empty($initialReferences)) {
        $initialReferences = [['title' => '', 'url' => '']];
    }
@endphp

<div class="max-w-5xl mx-auto space-y-8 pb-24"
     x-data="editQuestionManager()"
     x-init="initComponent()"
     x-cloak>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200">
        <div>
            <div class="flex items-center space-x-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.questions.index') }}" class="hover:underline">Questions</a>
                <span>&rsaquo;</span>
                <span class="text-gray-700 font-bold">Edit Question #{{ $question->id }}</span>
            </div>
            <h1 class="text-2xl font-bold text-navy">Edit Exam Question #{{ $question->id }}</h1>
            <p class="text-xs text-gray-500 mt-0.5">Modify question text, answer options, correct answer selections, or publishing status.</p>
        </div>
        <div class="flex items-center space-x-3">
            <button type="button" @click="showPreview = !showPreview"
                    class="px-3.5 py-2 border border-gray-300 bg-white hover:bg-gray-50 text-navy text-xs font-bold rounded-lg shadow-sm transition flex items-center space-x-2">
                <span>👁</span>
                <span x-text="showPreview ? 'Hide Candidate Preview' : 'Live Candidate Preview'"></span>
            </button>
            <a href="{{ route('admin.questions.index', ['exam_id' => $question->exam_id]) }}"
               class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition">
                &larr; Back to Listing
            </a>
        </div>
    </div>

    <!-- Master Form -->
    <form action="{{ route('admin.questions.update', $question->id) }}" method="POST" enctype="multipart/form-data" @submit="submitForm($event)" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- STEP 1 — QUESTION INFORMATION -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-6">
            <div class="flex items-center space-x-3 pb-3 border-b border-gray-150">
                <span class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-extrabold shadow-sm">1</span>
                <div>
                    <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Question Information</h3>
                    <p class="text-xs text-gray-500">Define the target exam, syllabus topic, question type, and publishing status.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Certification Exam -->
                <div>
                    <label for="exam_id" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Certification Exam *</label>
                    <select name="exam_id" id="exam_id" x-model="question.exam_id" required
                            class="w-full border-gray-300 rounded-lg text-sm px-3 py-2.5 focus:border-cyan focus:ring-cyan"
                            :class="errors.exam_id ? 'border-red-500 ring-1 ring-red-500' : ''">
                        <option value="">Select an Exam</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}">
                                {{ $exam->exam_code }} - {{ $exam->exam_name }}
                            </option>
                        @endforeach
                    </select>
                    <template x-if="errors.exam_id">
                        <p class="text-red-500 text-xs mt-1 font-semibold" x-text="errors.exam_id"></p>
                    </template>
                </div>

                <!-- Syllabus Topic -->
                <div>
                    <label for="topic" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Syllabus Topic / Chapter Name *</label>
                    <input type="text" name="topic" id="topic" x-model="question.topic" required
                           class="w-full border-gray-300 rounded-lg text-sm px-3 py-2.5 focus:border-cyan focus:ring-cyan"
                           :class="errors.topic ? 'border-red-500 ring-1 ring-red-500' : ''">
                    <template x-if="errors.topic">
                        <p class="text-red-500 text-xs mt-1 font-semibold" x-text="errors.topic"></p>
                    </template>
                </div>

                <!-- Question Type -->
                <div>
                    <label for="question_type" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Question Type *</label>
                    <select name="question_type" id="question_type" x-model="question.question_type" required
                            class="w-full border-gray-300 rounded-lg text-sm px-3 py-2.5 focus:border-cyan focus:ring-cyan">
                        <option value="single_choice">Single Choice (1 Correct Answer)</option>
                        <option value="multiple_choice">Multiple Choice (Multiple Correct Answers)</option>
                        <option value="yes_no">Yes / No</option>
                        <option value="drag_drop">Drag & Drop (Sequencing / Ordering)</option>
                        <option value="matching">Matching Pairs</option>
                        <option value="hotspot">Hotspot (Multiple Dropdowns)</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Publishing Status *</label>
                    <select name="status" id="status" x-model="question.status" required
                            class="w-full border-gray-300 rounded-lg text-sm px-3 py-2.5 focus:border-cyan focus:ring-cyan">
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

        <!-- STEP 2 — QUESTION PROMPT -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-6">
            <div class="flex items-center space-x-3 pb-3 border-b border-gray-150">
                <span class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-extrabold shadow-sm">2</span>
                <div>
                    <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Question Prompt</h3>
                    <p class="text-xs text-gray-500">Enter the primary question text and optional prompt image that candidates will see in the exam.</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="question_text" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Question Text *</label>
                    <textarea name="question_text" id="question_text" rows="5" x-model="question.question_text" required
                              class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-3 focus:border-cyan focus:ring-cyan leading-relaxed font-sans"
                              :class="errors.question_text ? 'border-red-500 ring-1 ring-red-500' : ''"></textarea>
                    <p class="text-[11px] text-gray-400 mt-1">This is the main question statement candidates will read in the test engine.</p>
                    <template x-if="errors.question_text">
                        <p class="text-red-500 text-xs mt-1 font-semibold" x-text="errors.question_text"></p>
                    </template>
                </div>

                <!-- Question Area Image Upload -->
                <div class="bg-gray-50/70 p-4 border border-gray-200 rounded-lg space-y-3">
                    <label for="question_image" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Upload Image / Diagram for Question Area (Optional)</label>
                    <input type="file" name="question_image" id="question_image" accept=".png,.jpg,.jpeg,.webp"
                           class="w-full border border-gray-300 rounded-lg text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-navy file:text-white hover:file:bg-opacity-90">
                    <p class="text-[11px] text-gray-500">Upload a new diagram or image to display directly within the question prompt.</p>
                </div>
            </div>
        </div>

        <!-- STEP 3 — INSTRUCTIONS (OPTIONAL) -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex items-center space-x-3 pb-3 border-b border-gray-150">
                <span class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-extrabold shadow-sm">3</span>
                <div>
                    <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Instructions (Optional)</h3>
                    <p class="text-xs text-gray-500">Add instructions candidates should read before selecting their answer.</p>
                </div>
            </div>

            <div>
                <label for="instructions" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Candidate Instructions</label>
                <input type="text" name="instructions" id="instructions" x-model="question.instructions" placeholder="e.g. Select one answer."
                       class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-2.5 focus:border-cyan focus:ring-cyan">
            </div>
        </div>

        <!-- STEP 4 — ANSWER OPTIONS (PRIMARY / MOST IMPORTANT) -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-6">
            <div class="flex justify-between items-start pb-3 border-b border-gray-150">
                <div class="flex items-center space-x-3">
                    <span class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-extrabold shadow-sm">4</span>
                    <div>
                        <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Answer Options & Correct Answer</h3>
                        <p class="text-xs text-gray-500">Configure the choices shown to candidates and mark which choice(s) are correct for grading.</p>
                    </div>
                </div>
            </div>

            <!-- Single Choice / Multiple Choice Options UI -->
            <div x-show="question.question_type === 'single_choice' || question.question_type === 'multiple_choice'" class="space-y-5">
                <div class="bg-cyan/5 border border-cyan/20 p-3.5 rounded-lg flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-cyan font-bold text-sm">💡</span>
                        <p class="text-xs font-semibold text-gray-700" x-text="question.question_type === 'single_choice' ? 'Select the single correct radio answer below. The correct answer will be highlighted in green.' : 'Check all answers below that are considered correct.'"></p>
                    </div>
                    <button type="button" @click="addOption()" class="px-3 py-1.5 bg-navy text-white text-xs font-bold rounded-md hover:bg-opacity-90 transition shadow-sm">+ Add Option</button>
                </div>

                <template x-if="errors.options">
                    <p class="text-red-500 text-xs font-semibold" x-text="errors.options"></p>
                </template>
                <template x-if="errors.correct_answers">
                    <p class="text-red-500 text-xs font-semibold" x-text="errors.correct_answers"></p>
                </template>

                <div class="space-y-4">
                    <template x-for="(opt, idx) in question.options" :key="opt.key">
                        <div class="p-4 rounded-xl border transition duration-150 space-y-3"
                             :class="isOptionCorrect(opt.key) ? 'border-emerald-500 bg-emerald-50/70 shadow-sm ring-1 ring-emerald-500' : 'border-gray-250 bg-white hover:border-gray-300'">
                            
                            <div class="flex items-center space-x-3">
                                <!-- Option Letter Badge -->
                                <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-extrabold shrink-0"
                                      :class="isOptionCorrect(opt.key) ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 border border-gray-300'"
                                      x-text="opt.key"></span>

                                <input type="hidden" :name="'options['+idx+'][key]'" :value="opt.key">

                                <!-- Option Text Input -->
                                <input type="text" :name="'options['+idx+'][text]'" x-model="opt.text" placeholder="Enter option text..."
                                       class="flex-grow border-gray-300 rounded-lg text-sm px-3.5 py-2 focus:border-cyan focus:ring-cyan">

                                <!-- Option Image Upload Button -->
                                <div class="shrink-0">
                                    <label :for="'opt_img_' + idx" title="Upload image for option choice"
                                           class="cursor-pointer text-xs font-semibold text-gray-700 hover:text-navy flex items-center space-x-1.5 bg-white border border-gray-300 hover:bg-gray-100 rounded-lg px-3 py-2 transition shadow-sm">
                                        <span>📷 Add Image</span>
                                        <input type="file" :id="'opt_img_' + idx" :name="'option_image_' + idx" accept=".png,.jpg,.jpeg,.webp" class="hidden" @change="opt.has_new_image = true">
                                    </label>
                                </div>

                                <!-- Correct Answer Radio/Checkbox Selector -->
                                <div class="flex items-center pl-2 border-l border-gray-200">
                                    <template x-if="question.question_type === 'single_choice'">
                                        <label class="flex items-center space-x-2 cursor-pointer py-1 px-2.5 rounded-lg transition"
                                               :class="isOptionCorrect(opt.key) ? 'bg-emerald-100/80 text-emerald-800' : 'hover:bg-gray-100'">
                                            <input type="radio" name="correct_option" :value="opt.key"
                                                   x-model="question.selected_correct"
                                                   @change="question.correct_answers = [question.selected_correct]"
                                                   class="rounded-full border-gray-300 text-emerald-600 focus:ring-emerald-500 h-5 w-5">
                                            <span class="text-xs font-bold uppercase" x-text="isOptionCorrect(opt.key) ? 'Correct' : 'Mark Correct'"></span>
                                        </label>
                                    </template>

                                    <template x-if="question.question_type === 'multiple_choice'">
                                        <label class="flex items-center space-x-2 cursor-pointer py-1 px-2.5 rounded-lg transition"
                                               :class="isOptionCorrect(opt.key) ? 'bg-emerald-100/80 text-emerald-800' : 'hover:bg-gray-100'">
                                            <input type="checkbox" name="correct_answers[]" :value="opt.key"
                                                   :checked="question.correct_answers.includes(opt.key)"
                                                   @change="toggleMultipleChoice(opt.key, $event.target.checked)"
                                                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-5 w-5">
                                            <span class="text-xs font-bold uppercase" x-text="isOptionCorrect(opt.key) ? 'Correct' : 'Mark Correct'"></span>
                                        </label>
                                    </template>
                                </div>

                                <!-- Remove Option -->
                                <button type="button" @click="removeOption(idx)" :disabled="question.options.length <= 2"
                                        :class="question.options.length <= 2 ? 'text-gray-300 cursor-not-allowed' : 'text-red-500 hover:text-red-700'"
                                        class="p-1 font-bold text-sm">✕</button>
                            </div>

                            <!-- Option Image Indicator / Preview -->
                            <div x-show="opt.has_new_image" class="pl-11">
                                <span class="text-xs font-bold text-emerald-600 flex items-center space-x-1 bg-emerald-100/60 px-2.5 py-1 rounded w-max">
                                    <span>✓</span>
                                    <span>Option Image File Selected</span>
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Yes / No Choice -->
            <div x-show="question.question_type === 'yes_no'" class="space-y-4">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Correct Response *</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="(opt, idx) in question.options" :key="opt.key">
                        <label class="flex items-center space-x-4 p-4 border rounded-xl cursor-pointer transition"
                               :class="isOptionCorrect(opt.key) ? 'border-emerald-500 bg-emerald-50/70 ring-1 ring-emerald-500' : 'border-gray-200 bg-white hover:border-gray-300'">
                            <input type="hidden" :name="'options['+idx+'][key]'" :value="opt.key">
                            <input type="hidden" :name="'options['+idx+'][text]'" :value="opt.text">
                            <input type="radio" name="correct_option" :value="opt.key"
                                   x-model="question.selected_correct"
                                   @change="question.correct_answers = [question.selected_correct]" required
                                   class="rounded-full border-gray-300 text-emerald-600 focus:ring-emerald-500 h-5 w-5">
                            <span class="text-base font-bold text-gray-800" x-text="opt.text"></span>
                            <div class="flex-grow"></div>
                            <span x-show="isOptionCorrect(opt.key)" class="text-xs font-extrabold text-emerald-600 bg-emerald-100 px-2.5 py-1 rounded-full uppercase">✓ Correct</span>
                        </label>
                    </template>
                </div>
            </div>

            <!-- Drag & Drop (Ordering) -->
            <div x-show="question.question_type === 'drag_drop'" class="space-y-4">
                <div class="flex justify-between items-center">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Drag Items (Correct Sequence Order)</label>
                    <button type="button" @click="addDragItem()" class="text-xs text-cyan font-bold hover:underline">+ Add Sequence Item</button>
                </div>
                <div class="space-y-3">
                    <template x-for="(item, idx) in question.question_data.drag_items" :key="idx">
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <span class="text-xs font-bold text-gray-500 w-6 text-center" x-text="idx + 1"></span>
                            <input type="text" x-model="item.text" @input="syncDragOrder()" placeholder="Item text..."
                                   class="flex-grow border-gray-300 rounded text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan">
                            <button type="button" @click="removeDragItem(idx)" class="text-red-500 hover:text-red-700 text-xs font-bold">✕</button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Matching Pairs -->
            <div x-show="question.question_type === 'matching'" class="space-y-4">
                <div class="flex justify-between items-center">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">Matching Pairs (Left Item ➔ Right Target)</label>
                    <button type="button" @click="addMatchingPair()" class="text-xs text-cyan font-bold hover:underline">+ Add Matching Pair</button>
                </div>
                <div class="space-y-3">
                    <template x-for="(pair, idx) in question.question_data.matching_pairs" :key="idx">
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <input type="text" x-model="pair.left" placeholder="Left Term / Prompt"
                                   class="w-1/2 border-gray-300 rounded text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan">
                            <span class="text-gray-400 font-bold">➔</span>
                            <input type="text" x-model="pair.right" placeholder="Right Match / Definition"
                                   class="w-1/2 border-gray-300 rounded text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan">
                            <button type="button" @click="removeMatchingPair(idx)" class="text-red-500 hover:text-red-700 text-xs font-bold">✕</button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- HOTSPOT / MULTIPLE DROPDOWNS CONFIGURATION -->
            <div x-show="question.question_type === 'hotspot'" class="space-y-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Dropdown Selection Boxes Configuration</h4>
                        <p class="text-xs text-gray-500">Create the dropdowns shown to candidates and define the correct choice for each dropdown.</p>
                    </div>
                    <button type="button" @click="addHotspotBox()" class="px-3 py-1.5 bg-navy text-white text-xs font-bold rounded-md hover:bg-opacity-90 shadow-sm">+ Add Dropdown Box</button>
                </div>

                <template x-if="errors.hotspot">
                    <p class="text-red-500 text-xs font-semibold" x-text="errors.hotspot"></p>
                </template>

                <div class="space-y-4">
                    <template x-for="(box, idx) in question.question_data.boxes" :key="idx">
                        <div class="p-5 border border-gray-200 bg-gray-50/60 rounded-xl space-y-4 shadow-sm">
                            <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                                <span class="text-xs font-extrabold uppercase text-navy flex items-center space-x-2">
                                    <span class="w-6 h-6 rounded-full bg-navy text-white flex items-center justify-center text-[10px]" x-text="idx + 1"></span>
                                    <span x-text="'Dropdown #' + (idx + 1)"></span>
                                </span>
                                <button type="button" @click="removeHotspotBox(idx)" :disabled="question.question_data.boxes.length <= 1"
                                        class="text-xs text-red-500 hover:text-red-700 font-bold disabled:text-gray-300">Remove Box</button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Box Label -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Dropdown Label *</label>
                                    <input type="text" x-model="box.label" placeholder="e.g. Select Azure Service"
                                           class="w-full border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                                </div>

                                <!-- Available Choices -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Available Choices (Comma-Separated) *</label>
                                    <input type="text" x-model="box.optionsText" @input="updateHotspotOptions(idx)" placeholder="Option 1, Option 2, Option 3"
                                           class="w-full border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                                </div>

                                <!-- CORRECT ANSWER SELECTOR (CLEAR & PROMINENT) -->
                                <div>
                                    <label class="block text-xs font-bold text-emerald-700 uppercase tracking-wider mb-1">✓ Correct Answer *</label>
                                    <select x-model="box.correct_answer" required
                                            class="w-full border-emerald-500 bg-emerald-50/80 rounded-lg text-sm px-3 py-2 focus:border-emerald-600 focus:ring-emerald-500 font-bold text-emerald-900">
                                        <option value="">-- Select Correct Answer --</option>
                                        <template x-for="choice in box.options" :key="choice">
                                            <option :value="choice" x-text="choice"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- STEP 5 — ANSWER EXPLANATION -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex items-center space-x-3 pb-3 border-b border-gray-150">
                <span class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-extrabold shadow-sm">5</span>
                <div>
                    <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Answer Explanation</h3>
                    <p class="text-xs text-gray-500">Explain why the correct answer is correct. This detailed rationale is shown to candidates after answering.</p>
                </div>
            </div>

            <div>
                <textarea name="explanation" id="explanation" rows="4" x-model="question.explanation"
                          placeholder="Reference guide documentation explanation, architectural principles, and rationale..."
                          class="w-full border-gray-300 rounded-lg text-sm px-3.5 py-3 focus:border-cyan focus:ring-cyan leading-relaxed"></textarea>
            </div>
        </div>

        <!-- STEP 6 — REFERENCES -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-gray-150">
                <div class="flex items-center space-x-3">
                    <span class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-extrabold shadow-sm">6</span>
                    <div>
                        <h3 class="text-sm font-bold text-navy uppercase tracking-wide">References</h3>
                        <p class="text-xs text-gray-500">Add official documentation or URL sources that support this question.</p>
                    </div>
                </div>
                <button type="button" @click="addReference()" class="text-xs text-cyan hover:underline font-bold">+ Add Reference</button>
            </div>

            <template x-if="errors.references">
                <p class="text-red-500 text-xs font-semibold" x-text="errors.references"></p>
            </template>

            <div class="space-y-3">
                <template x-for="(ref, idx) in question.references" :key="idx">
                    <div class="flex items-center space-x-3 bg-gray-50 p-2.5 border border-gray-200 rounded-lg">
                        <input type="text" :name="'references['+idx+'][title]'" x-model="ref.title" placeholder="Reference Title (e.g. MS Docs / Cisco Guide)"
                               class="w-1/2 border-gray-300 rounded-md text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan">
                        <input type="text" :name="'references['+idx+'][url]'" x-model="ref.url" placeholder="URL (e.g. https://docs.microsoft.com/...)"
                               class="w-1/2 border-gray-300 rounded-md text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan">
                        <button type="button" @click="removeReference(idx)" class="text-red-500 hover:text-red-700 text-sm font-bold px-2">✕</button>
                    </div>
                </template>
            </div>
        </div>

        <!-- STEP 7 — EXHIBITS / MEDIA ATTACHMENTS -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex items-center space-x-3 pb-3 border-b border-gray-150">
                <span class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-extrabold shadow-sm">7</span>
                <div>
                    <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Exhibit / Media Attachments</h3>
                    <p class="text-xs text-gray-500">Attach supplementary exhibit images or downloadable figures.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="media_file" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Upload Exhibit File</label>
                    <input type="file" name="media_file" id="media_file" accept=".png,.jpg,.jpeg,.webp"
                           class="w-full border border-gray-300 rounded-lg text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-navy hover:file:bg-gray-200">
                </div>
                <div>
                    <label for="media_caption" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Exhibit Caption</label>
                    <input type="text" name="media_caption" id="media_caption" placeholder="e.g. Network topology exhibit diagram"
                           class="w-full border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-cyan focus:ring-cyan">
                </div>
            </div>
        </div>

        <!-- STEP 8 — PUBLISHING & ACTIVE STATUS -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center text-xs font-extrabold shadow-sm">8</span>
                <div>
                    <h3 class="text-sm font-bold text-navy uppercase tracking-wide">Publishing Status</h3>
                    <p class="text-xs text-gray-500">Active questions are immediately available in test engine practice sessions.</p>
                </div>
            </div>
            <div class="flex items-center space-x-3 bg-gray-50 border border-gray-200 px-4 py-2.5 rounded-lg">
                <input type="checkbox" name="is_active" id="is_active" value="1" x-model="question.is_active"
                       class="rounded border-gray-300 text-cyan focus:ring-cyan h-5 w-5">
                <label for="is_active" class="text-xs font-bold text-gray-800 cursor-pointer">Mark as Active</label>
            </div>
        </div>

        <!-- LIVE CANDIDATE PREVIEW DRAWER / CARD -->
        <div x-show="showPreview" transition class="bg-slate-900 text-white rounded-2xl p-6 shadow-xl space-y-6 border border-slate-800">
            <div class="flex justify-between items-center pb-4 border-b border-slate-800">
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-1 rounded bg-cyan/20 text-cyan text-xs font-extrabold uppercase tracking-wider">Candidate Test Engine View</span>
                    <span class="text-xs text-slate-400 font-medium">Real-time Learner Experience Preview</span>
                </div>
                <button type="button" @click="showPreview = false" class="text-slate-400 hover:text-white text-xs font-bold">✕ Close Preview</button>
            </div>

            <!-- Preview Question Prompt -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-cyan uppercase tracking-wide">Question Prompt</h4>
                <div class="text-base font-medium leading-relaxed bg-slate-800/80 p-4 rounded-xl border border-slate-700/80 whitespace-pre-line"
                     x-text="question.question_text || 'Your question prompt will appear here...'"></div>
            </div>

            <!-- Preview Instructions -->
            <div x-show="question.instructions" class="text-xs italic text-amber-300/90 bg-amber-950/40 p-3 rounded-lg border border-amber-800/40">
                <span>📌 Instructions: </span>
                <span x-text="question.instructions"></span>
            </div>

            <!-- Preview Answer Choices -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wide">Answer Choices</h4>
                
                <div x-show="question.question_type === 'single_choice' || question.question_type === 'multiple_choice' || question.question_type === 'yes_no'" class="space-y-2.5">
                    <template x-for="opt in question.options" :key="opt.key">
                        <div class="flex items-center space-x-3 p-3.5 rounded-xl border transition"
                             :class="isOptionCorrect(opt.key) ? 'border-emerald-500 bg-emerald-950/40 text-emerald-200' : 'border-slate-800 bg-slate-800/50 text-slate-300'">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-extrabold"
                                  :class="isOptionCorrect(opt.key) ? 'bg-emerald-500 text-white' : 'bg-slate-700 text-slate-300'"
                                  x-text="opt.key"></span>
                            <span class="text-sm font-medium flex-grow" x-text="opt.text || ('Option ' + opt.key)"></span>
                            <span x-show="isOptionCorrect(opt.key)" class="text-xs font-extrabold text-emerald-400 bg-emerald-900/60 px-2.5 py-1 rounded-full uppercase">✓ Correct Answer</span>
                        </div>
                    </template>
                </div>

                <!-- Hotspot Preview -->
                <div x-show="question.question_type === 'hotspot'" class="space-y-3">
                    <template x-for="(box, bIdx) in question.question_data.boxes" :key="bIdx">
                        <div class="p-4 bg-slate-800/80 border border-slate-700 rounded-xl space-y-2">
                            <span class="text-xs font-bold text-cyan block" x-text="box.label || ('Dropdown #' + (bIdx + 1))"></span>
                            <div class="flex items-center space-x-3">
                                <span class="text-xs text-slate-400">Selected Answer:</span>
                                <span class="text-xs font-extrabold px-3 py-1 bg-emerald-900/80 text-emerald-300 border border-emerald-700 rounded-md"
                                      x-text="box.correct_answer || 'No correct answer selected yet'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Preview Explanation -->
            <div x-show="question.explanation" class="space-y-2 pt-2 border-t border-slate-800">
                <h4 class="text-xs font-bold text-emerald-400 uppercase tracking-wide">Answer Explanation</h4>
                <div class="text-xs text-slate-300 bg-slate-800/40 p-3.5 rounded-xl border border-slate-700/50 leading-relaxed whitespace-pre-line"
                     x-text="question.explanation"></div>
            </div>
        </div>

        <!-- STICKY ACTION BAR -->
        <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur border-t border-gray-200 p-4 shadow-lg z-40">
            <div class="max-w-5xl mx-auto flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.questions.index', ['exam_id' => $question->exam_id]) }}"
                       class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition">
                        Cancel
                    </a>
                    <button type="button" @click="showPreview = !showPreview"
                            class="px-4 py-2.5 border border-gray-300 bg-white hover:bg-gray-50 text-navy text-xs font-bold rounded-lg shadow-sm transition flex items-center space-x-2">
                        <span>👁</span>
                        <span x-text="showPreview ? 'Hide Preview' : 'Live Preview'"></span>
                    </button>
                </div>

                <button type="submit" :disabled="saving"
                        class="bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-2.5 px-8 rounded-lg shadow-md transition disabled:opacity-50 flex items-center space-x-2">
                    <span x-text="saving ? 'Updating Question...' : 'Update Question'"></span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function editQuestionManager() {
    return {
        showPreview: false,
        question: {
            exam_id: @json(old('exam_id', (string)$question->exam_id)),
            topic: @json(old('topic', (string)($question->topic ?? 'General'))),
            question_type: @json(old('question_type', (string)($question->question_type ?? 'single_choice'))),
            status: @json(old('status', (string)($question->status ?? 'draft'))),
            question_text: @json(old('question_text', (string)($question->question_text ?? ''))),
            instructions: @json(old('instructions', (string)($question->instructions ?? ''))),
            options: @json($initialOptions),
            selected_correct: Array.isArray(@json($initialAnswers)) && @json($initialAnswers).length ? @json($initialAnswers)[0] : 'A',
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

        isOptionCorrect(key) {
            if (this.question.question_type === 'single_choice' || this.question.question_type === 'yes_no') {
                return this.question.selected_correct === key;
            }
            return this.question.correct_answers.includes(key);
        },

        toggleMultipleChoice(key, isChecked) {
            if (isChecked) {
                if (!this.question.correct_answers.includes(key)) {
                    this.question.correct_answers.push(key);
                }
            } else {
                this.question.correct_answers = this.question.correct_answers.filter(k => k !== key);
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
            this.question.options.push({ key: nextKey, text: '', has_new_image: false });
        },

        removeOption(index) {
            if (this.question.options.length <= 2) {
                return;
            }
            const removed = this.question.options[index];
            this.question.options.splice(index, 1);
            if (this.question.selected_correct === removed.key) {
                this.question.selected_correct = this.question.options[0]?.key || 'A';
                this.question.correct_answers = [this.question.selected_correct];
            }
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

        syncDragOrder() {
            this.question.question_data.correct_order = this.question.question_data.drag_items.map(i => i.id);
        },

        addHotspotBox() {
            const nextIndex = (this.question.question_data.boxes ? this.question.question_data.boxes.length : 0) + 1;
            if (!this.question.question_data.boxes) {
                this.question.question_data.boxes = [];
            }
            this.question.question_data.boxes.push({
                id: 'box_' + nextIndex,
                label: 'Box ' + nextIndex,
                optionsText: 'Option 1, Option 2',
                options: ['Option 1', 'Option 2'],
                correct_answer: 'Option 1'
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
                box.correct_answer = box.options[0] || '';
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
            if (this.question.question_type === 'yes_no') {
                this.question.options = [
                    { key: 'A', text: 'Yes', has_new_image: false },
                    { key: 'B', text: 'No', has_new_image: false }
                ];
                this.question.selected_correct = 'A';
                this.question.correct_answers = ['A'];
            } else if (this.question.question_type === 'single_choice') {
                if (!this.question.selected_correct) {
                    this.question.selected_correct = 'A';
                }
                this.question.correct_answers = [this.question.selected_correct];
            }
        },

        validateForm() {
            this.errors = {};

            if (!this.question.exam_id || !String(this.question.exam_id).trim()) {
                this.errors.exam_id = 'Certification Exam is required.';
            }
            if (!this.question.topic || !String(this.question.topic).trim()) {
                this.errors.topic = 'Syllabus Topic / Chapter Name is required.';
            }
            if (!this.question.question_text || !String(this.question.question_text).trim()) {
                this.errors.question_text = 'Question Text is required.';
            }

            if (this.question.question_type === 'single_choice') {
                if (this.question.options.length < 2) {
                    this.errors.options = 'Single Choice requires at least 2 options.';
                }
                const emptyOpt = this.question.options.some(o => !o.text || !String(o.text).trim());
                if (emptyOpt) {
                    this.errors.options = 'All option text fields must be filled.';
                }
                if (!this.question.selected_correct) {
                    this.errors.correct_answers = 'Exactly 1 correct answer must be selected.';
                }
            } else if (this.question.question_type === 'multiple_choice') {
                if (this.question.options.length < 2) {
                    this.errors.options = 'Multiple Choice requires at least 2 options.';
                }
                const emptyOpt = this.question.options.some(o => !o.text || !String(o.text).trim());
                if (emptyOpt) {
                    this.errors.options = 'All option text fields must be filled.';
                }
                if (this.question.correct_answers.length < 2) {
                    this.errors.correct_answers = 'Multiple Choice requires at least 2 correct answers selected.';
                }
            } else if (this.question.question_type === 'yes_no') {
                if (!this.question.selected_correct) {
                    this.errors.correct_answers = 'Please select the correct response.';
                }
            } else if (this.question.question_type === 'hotspot') {
                for (let b of this.question.question_data.boxes) {
                    if (!b.label.trim()) {
                        this.errors.hotspot = 'All dropdown boxes must have a label.';
                        break;
                    }
                    if (b.options.length < 1) {
                        this.errors.hotspot = 'Each dropdown box must have choices provided.';
                        break;
                    }
                    if (!b.correct_answer) {
                        this.errors.hotspot = 'Each dropdown box must have a selected correct answer.';
                        break;
                    }
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
            if (this.question.question_type === 'single_choice' || this.question.question_type === 'yes_no') {
                if (this.question.selected_correct) {
                    this.question.correct_answers = [this.question.selected_correct];
                }
            }

            if (!this.validateForm()) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return false;
            }

            this.question.references = this.question.references.filter(r => r.title.trim() || r.url.trim());
            this.saving = true;
        }
    };
}
</script>
@endsection
