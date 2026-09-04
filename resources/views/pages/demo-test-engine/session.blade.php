@extends('layouts.public')

@section('title', "{$exam->exam_code} — Practice Test Engine")

@section('styles')
<style>
    body > header, body > footer { display: none !important; }
    body { background-color: var(--bg); }
</style>
@endsection

@section('content')
{{-- Inline answer state for Alpine.js --}}
<script>
window.demoTestEngineAnswers = {
    @foreach($answers as $index => $ans)
    {{ $index }}: {
        id:               {{ $ans->question_id }},
        type:             @json($ans->question->question_type),
        selected:         @json($ans->selected_option ?? ''),
        flagged:          {{ $ans->is_flagged ? 'true' : 'false' }},
        correct:          @json($attempt->mode === 'exam' ? null : $ans->question->correct_option),
        options:          @json($ans->question->options->map(fn($o) => ['key' => $o->option_key, 'text' => $o->option_text])),
        drag_items:       @json($ans->question->question_data['drag_items'] ?? []),
        matching_pairs:   @json($ans->question->question_data['matching_pairs'] ?? []),
        hotspot_answers:  @json($ans->question->question_data['hotspot_answers'] ?? $ans->question->question_data['boxes'] ?? []),
        selection_limit:  @json($ans->question->question_data['selection_limit'] ?? 1),
        search_text:      @json(mb_strtolower(strip_tags($ans->question->question_text ?? ''))),
        checked:   false,
        revealed:  false,
        is_correct: false,
        feedback:  ''
    },
    @endforeach
};
</script>

<div class="min-h-screen flex flex-col"
     x-data="examEngine()"
     x-init="init()"
     x-cloak>

    {{-- ===== STICKY HEADER ===== --}}
    <header class="sticky top-0 z-50" style="background:rgba(255,255,255,.96);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);">
        <div style="max-width:1500px;margin:auto;padding:14px 20px;display:flex;align-items:center;gap:16px;">

            {{-- Brand --}}
            <div style="font-weight:800;font-size:20px;white-space:nowrap;line-height:1.2;">
                {{ $exam->exam_code }} Practice Test
                <small style="display:block;color:var(--muted);font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;">Responsive Question Bank</small>
            </div>

            {{-- Header Tools --}}
            <div style="margin-left:auto;display:flex;align-items:center;gap:12px;">

                {{-- Progress --}}
                <div class="hidden md:block" style="width:220px;">
                    <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--muted);margin-bottom:5px;">
                        <span>Progress</span>
                        <span x-text="answeredCount() + ' / ' + total"></span>
                    </div>
                    <div style="height:7px;background:var(--surface2);border-radius:99px;overflow:hidden;">
                        <span style="display:block;height:100%;background:var(--accent);transition:width .25s;"
                              :style="'width:' + (answeredCount() / total * 100) + '%'"></span>
                    </div>
                </div>

                {{-- Exam Timer --}}
                <div x-show="mode === 'exam'"
                     style="display:flex;align-items:center;gap:6px;font-family:monospace;font-size:15px;font-weight:800;color:var(--accent);background:#eaf2fa;padding:7px 12px;border-radius:10px;border:1px solid #b3d4ef;">
                    <span style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;">Time:</span>
                    <span x-text="timerString"></span>
                </div>

                {{-- Top button --}}
                <button type="button" class="engine-btn"
                        @click="window.scrollTo({top:0,behavior:'smooth'})">Top</button>

                {{-- Submit Exam --}}
                <form id="submit-exam-form" action="{{ route('public.demo-test-engine.submit', $attempt->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="time_taken" :value="timeTaken">
                    <button type="submit" class="engine-btn primary">Submit Exam</button>
                </form>
            </div>
        </div>
    </header>

    {{-- ===== MAIN LAYOUT GRID ===== --}}
    <div class="engine-layout">

        {{-- LEFT SIDEBAR --}}
        <aside class="engine-sidebar">
            <h3>Question Navigator</h3>

            <input class="engine-search"
                   type="search"
                   x-model="searchQuery"
                   @input="filterNav()"
                   placeholder="Search questions...">

            <div class="qnav" id="qnav">
                @foreach($answers as $idx => $ans)
                    <button type="button"
                            id="navbtn-{{ $idx }}"
                            @click="goToQuestion({{ $idx }})"
                            :class="[
                                activeIndex === {{ $idx }} ? 'active' : '',
                                answers[{{ $idx }}].flagged ? 'flagged' :
                                (answers[{{ $idx }}].checked || answers[{{ $idx }}].revealed
                                    ? (answers[{{ $idx }}].is_correct ? 'correct' : 'wrong')
                                    : '')
                            ]"
                            :title="'Question {{ $idx + 1 }}: ' + (answers[{{ $idx }}].checked || answers[{{ $idx }}].revealed ? (answers[{{ $idx }}].is_correct ? 'Correct' : 'Reviewed') : 'Not answered')">
                        {{ $idx + 1 }}
                    </button>
                @endforeach
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="main">

            {{-- Hero --}}
            <section class="hero">
                <h1>{{ $exam->exam_code }} — Practice Test</h1>
                <p>Converted into a self-contained, fully responsive test interface. Questions are presented <strong>one at a time</strong>.</p>
                <div class="stats">
                    <span class="stat">{{ $attempt->total_questions }} total questions</span>
                    <span class="stat">MCQ + Hotspot + Drag &amp; Drop</span>
                    <span class="stat">Sequential test mode</span>
                </div>
            </section>

            {{-- Question Cards --}}
            <section id="questions">
                @foreach($answers as $idx => $ans)
                    <div x-show="activeIndex === {{ $idx }} && !testComplete"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <x-test-engine.question-card
                            :ans="$ans"
                            :index="$idx"
                            :total="$attempt->total_questions"
                            :mode="$attempt->mode" />
                    </div>
                @endforeach
            </section>

            {{-- Test Complete Screen --}}
            <div x-show="testComplete" x-transition class="test-complete">
                <h2>🎉 Test Completed</h2>
                <p>You have reached the end of the question set.</p>
                <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
                    <button type="button" class="engine-btn primary" @click="restartTest()">Restart Test</button>
                    <form action="{{ route('public.demo-test-engine.submit', $attempt->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <input type="hidden" name="time_taken" :value="timeTaken">
                        <button type="submit" class="engine-btn">View Results</button>
                    </form>
                </div>
            </div>

        </main>
    </div>

    {{-- Footer --}}
    <footer class="footer">
        &copy; {{ date('Y') }} Exam Topics Base — Master Interactive Test Engine. All rights reserved.
    </footer>

</div>

<script>
function examEngine() {
    return {
        activeIndex:  0,
        total:        {{ $attempt->total_questions }},
        mode:         '{{ $attempt->mode }}',
        testComplete: false,
        searchQuery:  '',
        timeRemaining: {{ $attempt->mode === 'exam' ? ($attempt->total_questions * 120) : 999999 }},
        timerString:  '00:00:00',
        timeTaken:    0,
        answers:      window.demoTestEngineAnswers,

        init() {
            if (this.mode === 'exam') {
                this.startTimer();
            }
        },

        // --- Progress: count answered questions (checked OR revealed) ---
        answeredCount() {
            return Object.values(this.answers).filter(a => a.checked || a.revealed).length;
        },

        // --- Timer ---
        startTimer() {
            const tick = () => {
                if (this.timeRemaining <= 0) {
                    document.getElementById('submit-exam-form').submit();
                    return;
                }
                this.timeRemaining--;
                this.timeTaken++;
                let h = Math.floor(this.timeRemaining / 3600);
                let m = Math.floor((this.timeRemaining % 3600) / 60);
                let s = this.timeRemaining % 60;
                this.timerString = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
                setTimeout(tick, 1000);
            };
            tick();
        },

        // --- Save single-select answer ---
        saveAnswer(index, option) {
            this.answers[index].selected = option;
            this.ajaxSave(index);
        },

        // --- Toggle checkbox (multi-select) with selection limit ---
        toggleCheckbox(index, option) {
            let current = (this.answers[index].selected || '').split(',').map(c => c.trim()).filter(Boolean);
            let pos = current.indexOf(option);
            if (pos > -1) {
                current.splice(pos, 1);
            } else {
                let limit = this.answers[index].selection_limit || 1;
                if (current.length < limit) {
                    current.push(option);
                }
            }
            this.answers[index].selected = current.join(',');
            this.ajaxSave(index);
        },

        // --- Toggle flag ---
        toggleFlag(index) {
            this.answers[index].flagged = !this.answers[index].flagged;
            fetch('{{ route('public.demo-test-engine.flag') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ attempt_id: {{ $attempt->id }}, question_id: this.answers[index].id })
            });
        },

        // --- Check Answer (matches master HTML logic) ---
        checkQuestion(index) {
            let selected = this.answers[index].selected || '';
            if (!selected) {
                this.answers[index].feedback = 'Please select an answer first.';
                this.answers[index].is_correct = false;
                return;
            }

            let correct  = this.answers[index].correct || '';
            let qType    = this.answers[index].type;

            if (qType === 'single_choice' || qType === 'yes_no') {
                this.answers[index].is_correct = selected.trim() === correct.trim();
            } else if (qType === 'multiple_choice') {
                let sArr = selected.split(',').map(s => s.trim()).filter(Boolean).sort();
                let cArr = correct.split(',').map(c => c.trim()).filter(Boolean).sort();
                this.answers[index].is_correct = (JSON.stringify(sArr) === JSON.stringify(cArr));
            } else {
                // Hotspot / Drag-drop — mark as attempted
                this.answers[index].is_correct = true;
            }

            this.answers[index].checked  = true;
            this.answers[index].revealed = true;
            this.answers[index].feedback = this.answers[index].is_correct
                ? 'Correct! Your answer has been recorded.'
                : 'Your answer has been recorded. You may reveal the answer below to review it.';

            this.ajaxSave(index);
        },

        // --- Reveal Answer ---
        revealAnswer(index) {
            this.answers[index].revealed = !this.answers[index].revealed;
            if (this.answers[index].revealed && !this.answers[index].checked) {
                this.answers[index].checked    = true;
                this.answers[index].is_correct = true;
                this.answers[index].feedback   = 'Answer revealed. Review the explanation, then continue to the next question.';
                this.ajaxSave(index);
            }
        },

        // --- Reset Question ---
        resetQuestion(index) {
            this.answers[index].selected   = '';
            this.answers[index].checked    = false;
            this.answers[index].revealed   = false;
            this.answers[index].is_correct = false;
            this.answers[index].feedback   = '';
            // Restore hotspot/drag_drop defaults
            if (this.answers[index].type === 'hotspot' && this.answers[index].hotspot_answers) {
                this.answers[index].hotspot_answers = this.answers[index].hotspot_answers.map(b => ({...b, selected: ''}));
            }
            this.ajaxSave(index);
        },

        // --- Navigate to question — SEQUENTIAL RESTRICTION ---
        // Can only jump to: current question OR already answered/revealed questions
        goToQuestion(i) {
            if (i === this.activeIndex) return;
            if (i < this.activeIndex || this.answers[i].checked || this.answers[i].revealed) {
                this.testComplete = false;
                this.activeIndex  = i;
                this.$nextTick(() => {
                    const el = document.getElementById('q' + (i + 1));
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }
            // Future unanswered questions: silently ignore (no skip)
        },

        // --- Next Question (called by "Next Question →" / "Finish Test ✓" button) ---
        goNext(index) {
            let next = index + 1;
            if (next >= this.total) {
                this.testComplete = true;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                this.activeIndex = next;
                this.$nextTick(() => {
                    const el = document.getElementById('q' + (next + 1));
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }
        },

        // --- Filter sidebar nav by search query ---
        filterNav() {
            let term = this.searchQuery.toLowerCase().trim();
            @foreach($answers as $idx => $ans)
                let btn{{ $idx }} = document.getElementById('navbtn-{{ $idx }}');
                if (btn{{ $idx }}) {
                    let searchText = this.answers[{{ $idx }}].search_text || '';
                    btn{{ $idx }}.style.display = (!term || String({{ $idx + 1 }}).includes(term) || searchText.includes(term)) ? '' : 'none';
                }
            @endforeach
        },

        // --- Restart Test ---
        restartTest() {
            for (let i = 0; i < this.total; i++) {
                this.answers[i].selected   = '';
                this.answers[i].checked    = false;
                this.answers[i].revealed   = false;
                this.answers[i].is_correct = false;
                this.answers[i].feedback   = '';
                this.answers[i].flagged    = false;
            }
            this.testComplete = false;
            this.activeIndex  = 0;
            this.searchQuery  = '';
            this.filterNav();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        // --- AJAX save to backend ---
        ajaxSave(index) {
            fetch('{{ route('public.demo-test-engine.answer') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    attempt_id:      {{ $attempt->id }},
                    question_id:     this.answers[index].id,
                    selected_option: this.answers[index].selected,
                    time_spent:      0
                })
            });
        }
    };
}
</script>
@endsection
