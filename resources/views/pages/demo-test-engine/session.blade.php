@extends('layouts.public')

@section('title', "{$exam->exam_code} — Practice Test Engine")

@section('styles')
<style>
    /* Full height overrides for engine layout */
    body > header, body > footer { display: none !important; }
    body { background-color: var(--bg); }
</style>
@endsection

@section('content')
<script>
    window.demoTestEngineAnswers = {
        @foreach($answers as $index => $ans)
            {{ $index }}: {
                id: {{ $ans->question_id }},
                type: @json($ans->question->question_type),
                selected: @json($ans->selected_option ?? ''),
                flagged: {{ $ans->is_flagged ? 'true' : 'false' }},
                correct: @json($attempt->mode === 'exam' ? null : $ans->question->correct_option),
                options: @json($ans->question->options->map(fn($o) => ['key' => $o->option_key, 'text' => $o->option_text])),
                drag_items: @json($ans->question->question_data['drag_items'] ?? []),
                matching_pairs: @json($ans->question->question_data['matching_pairs'] ?? []),
                hotspot_answers: @json($ans->question->question_data['hotspot_answers'] ?? []),
                selection_limit: @json($ans->question->question_data['selection_limit'] ?? 1),
                checked: false,
                is_correct: false,
                revealed: false
            },
        @endforeach
    };
</script>

<div class="min-h-screen flex flex-col justify-between" 
     x-data="{
         activeIndex: 0,
         searchQuery: '',
         total: {{ $attempt->total_questions }},
         mode: '{{ $attempt->mode }}',
         timeRemaining: {{ $attempt->mode === 'exam' ? ($attempt->total_questions * 120) : 999999 }},
         timerString: '00:00:00',
         answers: window.demoTestEngineAnswers,
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
             this.answers[index].selected = option;
             this.ajaxSave(index);
         },
         toggleCheckbox(index, option) {
             let current = (this.answers[index].selected || '').split(',');
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
             
             fetch('{{ route('public.demo-test-engine.flag') }}', {
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
         checkQuestion(index) {
             let selected = this.answers[index].selected || '';
             let correct = this.answers[index].correct || '';
             
             if (this.answers[index].type === 'single_choice' || this.answers[index].type === 'yes_no') {
                 this.answers[index].is_correct = (selected.trim() === correct.trim());
             } else if (this.answers[index].type === 'multiple_choice') {
                 let sArr = selected.split(',').map(s => s.trim()).filter(Boolean).sort();
                 let cArr = correct.split(',').map(c => c.trim()).filter(Boolean).sort();
                 this.answers[index].is_correct = (JSON.stringify(sArr) === JSON.stringify(cArr));
             } else {
                 this.answers[index].is_correct = true;
             }
             
             this.answers[index].checked = true;
             this.answers[index].revealed = true;
         },
         ajaxSave(index) {
             fetch('{{ route('public.demo-test-engine.answer') }}', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
                 },
                 body: JSON.stringify({
                     attempt_id: {{ $attempt->id }},
                     question_id: this.answers[index].id,
                     selected_option: this.answers[index].selected,
                     time_spent: 0
                 })
             });
         }
     }">

    <!-- Master Header -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-[#d9e1ea]">
        <div class="max-w-[1500px] mx-auto px-5 py-3.5 flex items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <a href="{{ url('/') }}" class="text-xl font-black text-navy uppercase tracking-widest">
                    Exams<span class="text-accent">Ninja</span>
                </a>
                <div class="h-5 w-px bg-gray-300"></div>
                <div class="font-extrabold text-base text-navy">
                    {{ $exam->exam_code }} Practice Test
                    <small class="block text-[11px] font-semibold text-gray-500 uppercase tracking-widest">Responsive Question Bank</small>
                </div>
            </div>

            <!-- Header Tools & Progress -->
            <div class="flex items-center space-x-6">
                <!-- Progress Bar -->
                <div class="hidden md:block w-56">
                    <div class="flex justify-between text-xs font-semibold text-gray-500 mb-1">
                        <span>Progress</span>
                        <span x-text="(activeIndex + 1) + ' / ' + total"></span>
                    </div>
                    <div class="h-2 bg-[#eef3f8] rounded-full overflow-hidden">
                        <div class="h-full bg-accent transition-all duration-300" :style="'width: ' + (((activeIndex + 1) / total) * 100) + '%'"></div>
                    </div>
                </div>

                <div x-show="mode === 'exam'" class="flex items-center space-x-2 font-mono text-base font-extrabold text-accent bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200">
                    <span class="text-xs uppercase font-bold text-gray-500 mr-1">Time:</span>
                    <span x-text="timerString"></span>
                </div>

                <!-- Submit Form -->
                <form id="submit-exam-form" action="{{ route('public.demo-test-engine.submit', $attempt->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="time_taken" :value="(activeIndex * 10)">
                    <button type="submit" class="engine-btn primary">
                        Submit Exam
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Master Layout Grid -->
    <div class="engine-layout">
        
        <!-- Left 270px Sidebar Navigator -->
        <aside class="engine-sidebar">
            <h3>Question Navigator</h3>
            
            <input class="engine-search" 
                   type="search" 
                   x-model="searchQuery" 
                   placeholder="Search questions...">
            
            <!-- Grid Navigation Buttons -->
            <div class="qnav">
                <template x-for="i in Array.from({length: total}, (_, idx) => idx)">
                    <button type="button"
                            @click="activeIndex = i"
                            x-show="!searchQuery || String(i + 1).includes(searchQuery)"
                            :class="[
                                activeIndex === i ? 'active' : '',
                                answers[i].flagged ? 'flagged' : 
                                (answers[i].checked ? (answers[i].is_correct ? 'correct' : 'wrong') : '')
                            ]">
                        <span x-text="i + 1"></span>
                    </button>
                </template>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="main">
            
            <!-- Hero Card -->
            <section class="hero">
                <h1>{{ $exam->exam_code }} — Practice Test</h1>
                <p>Converted into a self-contained, fully responsive test interface. Questions are presented <strong>one at a time</strong>.</p>
                <div class="stats">
                    <span class="stat">{{ $attempt->total_questions }} total questions</span>
                    <span class="stat">MCQ + Hotspot + Drag & Drop</span>
                    <span class="stat">Sequential test mode</span>
                </div>
            </section>

            <!-- Question Cards -->
            <section id="questions">
                @foreach($answers as $idx => $ans)
                    <div x-show="activeIndex == {{ $idx }}" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                        <x-test-engine.question-card 
                            :ans="$ans" 
                            :index="$idx" 
                            :total="$attempt->total_questions" 
                            :mode="$attempt->mode" />
                    </div>
                @endforeach
            </section>
        </main>

    </div>

    <!-- Master Footer -->
    <footer class="footer">
        &copy; {{ date('Y') }} ExamsNinja — Master Interactive Test Engine. All rights reserved.
    </footer>
</div>
@endsection
