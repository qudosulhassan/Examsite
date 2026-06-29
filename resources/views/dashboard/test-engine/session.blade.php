@extends('layouts.public')

@section('title', "Practice Session - {$exam->exam_code}")

@section('styles')
<style>
    /* Clean layout overrides for exam mode */
    body > header, body > footer { display: none !important; }
    body { background-color: #F3F4F6; }
</style>
@endsection

@section('content')
<div class="h-screen flex flex-col justify-between" 
     x-data="{
         activeIndex: 0,
         total: {{ $attempt->total_questions }},
         mode: '{{ $attempt->mode }}',
         timeRemaining: {{ $attempt->mode === 'exam' ? ($attempt->total_questions * 120) : 999999 }}, // 2 minutes per question in exam mode
         timerString: '00:00:00',
         timerVisible: true,
         answers: {
             @foreach($answers as $index => $ans)
                 {{ $index }}: {
                     id: {{ $ans->question_id }},
                     selected: '{{ $ans->selected_option ?? '' }}',
                     flagged: {{ $ans->is_flagged ? 'true' : 'false' }},
                     correct: '{{ $ans->question->correct_option }}',
                     revealed: false
                 },
             @endforeach
         },
         init() {
             if (this.mode === 'exam') {
                 this.startTimer();
             }
         },
         startTimer() {
             const tick = () => {
                 if (this.timeRemaining <= 0) {
                     document.getElementById('submit-exam-form').submit();
                     return;
                 }
                 this.timeRemaining--;
                 
                 let hours = Math.floor(this.timeRemaining / 3600);
                 let minutes = Math.floor((this.timeRemaining % 3600) / 60);
                 let seconds = this.timeRemaining % 60;
                 
                 this.timerString = 
                     String(hours).padStart(2, '0') + ':' + 
                     String(minutes).padStart(2, '0') + ':' + 
                     String(seconds).padStart(2, '0');
                 
                 setTimeout(tick, 1000);
             };
             tick();
         },
         saveAnswer(index, option) {
             // For multi-select, option is handled in toggleCheckbox
             this.answers[index].selected = option;
             this.ajaxSave(index);
         },
         toggleCheckbox(index, option) {
             let current = this.answers[index].selected ? this.answers[index].selected.split(',') : [];
             current = current.map(c => c.trim()).filter(Boolean);
             
             let pos = current.indexOf(option);
             if (pos > -1) {
                 current.splice(pos, 1);
             } else {
                 current.push(option);
             }
             
             this.answers[index].selected = current.join(',');
             this.ajaxSave(index);
         },
         toggleFlag(index) {
             this.answers[index].flagged = !this.answers[index].flagged;
             
             fetch('{{ route('dashboard.test-engine.flag') }}', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                 },
                 body: JSON.stringify({
                     attempt_id: {{ $attempt->id }},
                     question_id: this.answers[index].id
                 })
             });
         },
         ajaxSave(index) {
             fetch('{{ route('dashboard.test-engine.answer') }}', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                 },
                 body: JSON.stringify({
                     attempt_id: {{ $attempt->id }},
                     question_id: this.answers[index].id,
                     selected_option: this.answers[index].selected,
                     time_spent: 0 // Tracked on backend or simplified here
                 })
             });
         }
     }">

    <!-- Simulator Header -->
    <header class="bg-navy text-white h-16 flex items-center justify-between px-6 shadow-md shrink-0">
        <div class="flex items-center space-x-4">
            <span class="text-lg font-bold text-white tracking-wide">Exams<span class="text-cyan">Ninja</span></span>
            <span class="text-xs bg-gray-800 text-gray-400 font-semibold px-2 py-0.5 rounded border border-gray-700">{{ $exam->exam_code }}</span>
        </div>

        <!-- Timer / Mode description -->
        <div class="flex items-center space-x-6 text-sm font-semibold">
            <span class="text-gray-400 font-bold uppercase tracking-wider text-xs">Mode: <span class="text-cyan" x-text="mode === 'practice' ? 'Practice' : (mode === 'exam' ? 'Exam Mode' : 'Review')"></span></span>
            
            <div x-show="mode === 'exam'" class="flex items-center space-x-2 border-l border-gray-700 pl-6">
                <span class="text-xs text-gray-400 uppercase tracking-wider">Timer</span>
                <span class="font-mono text-cyan text-base" x-text="timerString"></span>
                <button @click="timerVisible = !timerVisible" class="text-gray-500 hover:text-white transition text-xs focus:outline-none">
                    <span x-text="timerVisible ? 'Hide' : 'Show'"></span>
                </button>
            </div>
        </div>

        <!-- Submit Button -->
        <form id="submit-exam-form" action="{{ route('dashboard.test-engine.submit', $attempt->id) }}" method="POST">
            @csrf
            <input type="hidden" name="time_taken" :value="(activeIndex * 10)"> <!-- Simulating duration -->
            <button type="submit" class="bg-orange hover-bg-orange text-white text-xs font-bold px-5 py-2.5 rounded shadow transition focus:outline-none">
                Submit Simulator Exam
            </button>
        </form>
    </header>

    <!-- Main Workspace -->
    <div class="flex-grow flex overflow-hidden">
        
        <!-- Left Panel: Grid Navigation (25%) -->
        <aside class="hidden md:flex md:w-64 bg-white border-r border-gray-200 flex-col shrink-0 overflow-y-auto p-5">
            <h3 class="font-bold text-navy text-xs uppercase tracking-wider mb-4 border-b border-gray-150 pb-2">Question Navigator</h3>
            
            <!-- Grid list -->
            <div class="grid grid-cols-5 gap-2">
                <template x-for="i in Array.from({length: total}, (_, idx) => idx)">
                    <button @click="activeIndex = i"
                            :class="[
                                activeIndex === i ? 'ring-2 ring-cyan ring-offset-2' : '',
                                answers[i].flagged ? 'bg-orange text-white border-orange' : 
                                (answers[i].selected ? 'bg-cyan bg-opacity-25 text-navy border-cyan border-opacity-50' : 'bg-gray-100 text-gray-600 border-gray-200')
                            ]"
                            class="h-9 w-9 rounded text-xs font-bold border transition flex items-center justify-center focus:outline-none">
                        <span x-text="i + 1"></span>
                    </button>
                </template>
            </div>

            <!-- Legends -->
            <div class="mt-8 border-t border-gray-150 pt-4 space-y-2 text-xs text-gray-500 font-semibold">
                <div class="flex items-center space-x-2">
                    <span class="h-4 w-4 bg-gray-100 border border-gray-200 rounded"></span>
                    <span>Unanswered</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="h-4 w-4 bg-cyan bg-opacity-25 border border-cyan border-opacity-50 rounded"></span>
                    <span>Answered</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="h-4 w-4 bg-orange rounded"></span>
                    <span>Flagged for Review</span>
                </div>
            </div>
        </aside>

        <!-- Main Body Workspace (75%) -->
        <div class="flex-1 flex flex-col overflow-y-auto p-6 md:p-8">
            <template x-for="(ans, index) in answers" :key="index">
                <div x-show="activeIndex == index" class="space-y-6 max-w-4xl mx-auto w-full">
                    
                    <!-- Progress Card -->
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex items-center justify-between">
                        <span class="text-xs font-bold text-navy">Question <span x-text="index + 1"></span> of <span x-text="total"></span></span>
                        <div class="w-1/2 bg-gray-150 h-2 rounded-full overflow-hidden">
                            <div class="bg-cyan h-full rounded-full transition-all duration-300" :style="'width: ' + (((index + 1) / total) * 100) + '%'"></div>
                        </div>
                    </div>

                    <!-- Question Container -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 md:p-8 shadow-sm space-y-6">
                        <!-- Heading -->
                        <div class="flex justify-between items-start pb-4 border-b border-gray-100">
                            <div>
                                <span class="text-xs font-bold text-cyan" x-text="'Exam topic: ' + answers[index].correct.slice(0, 15)"></span>
                            </div>
                            <!-- Flag trigger -->
                            <button @click="toggleFlag(index)" 
                                    :class="answers[index].flagged ? 'text-orange font-bold' : 'text-gray-400 hover:text-navy'"
                                    class="text-xs font-semibold focus:outline-none flex items-center space-x-1">
                                <span class="text-sm">⚑</span>
                                <span x-text="answers[index].flagged ? 'Flagged' : 'Flag for Review'"></span>
                            </button>
                        </div>

                        <!-- Question Text -->
                        <p class="text-base font-bold text-navy leading-relaxed">
                            @if(count($answers) > 0)
                                @foreach($answers as $idx => $ans)
                                    <span x-show="activeIndex == {{ $idx }}">{!! $ans->question->question_text !!}</span>
                                @endforeach
                            @endif
                        </p>

                        <!-- Answer Choice Options list -->
                        <div class="space-y-3 pt-2">
                            @foreach($answers as $idx => $ans)
                                <div x-show="activeIndex == {{ $idx }}" class="space-y-3">
                                    <!-- Option A -->
                                    <label class="flex items-center space-x-3 p-3.5 rounded border border-gray-200 hover:bg-gray-50 cursor-pointer transition"
                                           :class="answers[{{ $idx }}].selected === 'A' ? 'border-cyan bg-cyan bg-opacity-5' : ''">
                                        <input type="radio" 
                                               name="q_{{ $idx }}" 
                                               value="A" 
                                               @change="saveAnswer({{ $idx }}, 'A')"
                                               :checked="answers[{{ $idx }}].selected === 'A'"
                                               class="text-cyan focus:ring-cyan h-4 w-4">
                                        <span><strong>A.</strong> {{ $ans->question->option_a }}</span>
                                    </label>

                                    <!-- Option B -->
                                    <label class="flex items-center space-x-3 p-3.5 rounded border border-gray-200 hover:bg-gray-50 cursor-pointer transition"
                                           :class="answers[{{ $idx }}].selected === 'B' ? 'border-cyan bg-cyan bg-opacity-5' : ''">
                                        <input type="radio" 
                                               name="q_{{ $idx }}" 
                                               value="B" 
                                               @change="saveAnswer({{ $idx }}, 'B')"
                                               :checked="answers[{{ $idx }}].selected === 'B'"
                                               class="text-cyan focus:ring-cyan h-4 w-4">
                                        <span><strong>B.</strong> {{ $ans->question->option_b }}</span>
                                    </label>

                                    <!-- Option C -->
                                    @if(!empty($ans->question->option_c))
                                        <label class="flex items-center space-x-3 p-3.5 rounded border border-gray-200 hover:bg-gray-50 cursor-pointer transition"
                                               :class="answers[{{ $idx }}].selected === 'C' ? 'border-cyan bg-cyan bg-opacity-5' : ''">
                                            <input type="radio" 
                                                   name="q_{{ $idx }}" 
                                                   value="C" 
                                                   @change="saveAnswer({{ $idx }}, 'C')"
                                                   :checked="answers[{{ $idx }}].selected === 'C'"
                                                   class="text-cyan focus:ring-cyan h-4 w-4">
                                            <span><strong>C.</strong> {{ $ans->question->option_c }}</span>
                                        </label>
                                    @endif

                                    <!-- Option D -->
                                    @if(!empty($ans->question->option_d))
                                        <label class="flex items-center space-x-3 p-3.5 rounded border border-gray-200 hover:bg-gray-50 cursor-pointer transition"
                                               :class="answers[{{ $idx }}].selected === 'D' ? 'border-cyan bg-cyan bg-opacity-5' : ''">
                                            <input type="radio" 
                                                   name="q_{{ $idx }}" 
                                                   value="D" 
                                                   @change="saveAnswer({{ $idx }}, 'D')"
                                                   :checked="answers[{{ $idx }}].selected === 'D'"
                                                   class="text-cyan focus:ring-cyan h-4 w-4">
                                            <span><strong>D.</strong> {{ $ans->question->option_d }}</span>
                                        </label>
                                    @endif

                                    <!-- Show Explanation trigger (Practice Mode Only) -->
                                    <template x-if="mode === 'practice'">
                                        <div class="pt-6 border-t border-gray-100">
                                            <button @click="answers[{{ $idx }}].revealed = !answers[{{ $idx }}].revealed" 
                                                    class="text-xs font-bold text-cyan hover:text-navy transition focus:outline-none">
                                                <span x-text="answers[{{ $idx }}].revealed ? 'Hide Correct Answer' : 'Show Answer & Explanation'"></span>
                                            </button>
                                            
                                            <!-- Explanation Details -->
                                            <div x-show="answers[{{ $idx }}].revealed" class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg text-sm leading-relaxed" style="display: none;">
                                                <p class="font-bold text-navy mb-2">Correct Answer: <span class="text-green-600" x-text="answers[{{ $idx }}].correct"></span></p>
                                                <p class="text-gray-600">{!! $ans->question->explanation !!}</p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </template>
        </div>

    </div>

    <!-- Navigation Footer Workspace -->
    <footer class="bg-white border-t border-gray-200 h-16 flex items-center justify-between px-6 shadow-inner shrink-0">
        <button @click="activeIndex = Math.max(0, activeIndex - 1)" 
                :disabled="activeIndex === 0" 
                class="border border-gray-300 hover:border-cyan text-navy hover:bg-gray-50 text-xs font-bold py-2 px-4 rounded transition focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed">
            &larr; Previous Question
        </button>

        <button @click="activeIndex = Math.min(total - 1, activeIndex + 1)" 
                :disabled="activeIndex === total - 1" 
                class="bg-cyan hover-bg-cyan text-navy text-xs font-bold py-2 px-6 rounded shadow transition focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed">
            Next Question &rarr;
        </button>
    </footer>
</div>
@endsection
