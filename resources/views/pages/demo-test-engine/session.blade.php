@extends('layouts.public')

@section('title', "Practice Session - {$exam->exam_code}")

@section('styles')
<style>
    /* Clean layout overrides for exam mode */
    body > header, body > footer { display: none !important; }
    body { background-color: #f8fafc; }
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
                correct: @json($ans->question->correct_option),
                options: @json($ans->question->options->map(fn($o) => ['key' => $o->option_key, 'text' => $o->option_text])),
                drag_items: @json($ans->question->question_data['drag_items'] ?? []),
                matching_pairs: @json($ans->question->question_data['matching_pairs'] ?? []),
                hotspot_answers: @json($ans->question->question_data['hotspot_answers'] ?? []),
                revealed: false
            },
        @endforeach
    };
</script>

<div class="h-screen flex flex-col justify-between" 
     x-data="{
         activeIndex: 0,
         total: {{ $attempt->total_questions }},
         mode: '{{ $attempt->mode }}',
         timeRemaining: {{ $attempt->mode === 'exam' ? ($attempt->total_questions * 120) : 999999 }}, // 2 minutes per question in exam mode
         timerString: '00:00:00',
         timerVisible: true,
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
             // For multi-select, option is handled in toggleCheckbox
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
                     time_spent: 0 // Tracked on backend or simplified here
                 })
             });
         }
     }">

    <!-- Simulator Header -->
    <header class="bg-gradient-to-r from-[#07101E] via-navy to-[#0F172A] border-b border-white/10 text-white h-16 flex items-center justify-between px-6 shadow-md shrink-0 relative z-20">
        <div class="flex items-center space-x-4">
            <span class="text-xl font-black text-white tracking-widest uppercase">Exams<span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan to-blue-500">Ninja</span></span>
            <span class="text-[10px] font-black bg-white/10 text-cyan uppercase tracking-widest px-2.5 py-1 rounded-md border border-cyan/20">{{ $exam->exam_code }}</span>
        </div>

        <!-- Timer / Mode description -->
        <div class="flex items-center space-x-6 text-sm font-semibold">
            <span class="text-gray-400 font-bold uppercase tracking-widest text-[11px]">Mode: <span class="text-cyan font-black" x-text="mode === 'practice' ? 'Practice' : (mode === 'exam' ? 'Exam Mode' : 'Review')"></span></span>
            
            <div x-show="mode === 'exam'" class="flex items-center space-x-3 border-l border-white/10 pl-6">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Timer</span>
                <span class="font-mono text-cyan text-lg font-bold drop-shadow-[0_0_8px_rgba(0,212,170,0.5)]" x-text="timerString"></span>
                <button @click="timerVisible = !timerVisible" class="text-gray-500 hover:text-white transition-colors text-[10px] uppercase tracking-widest font-bold focus:outline-none bg-white/5 px-2 py-1 rounded">
                    <span x-text="timerVisible ? 'Hide' : 'Show'"></span>
                </button>
            </div>
        </div>

        <!-- Submit Button -->
        <form id="submit-exam-form" action="{{ route('public.demo-test-engine.submit', $attempt->id) }}" method="POST">
            @csrf
            <input type="hidden" name="time_taken" :value="(activeIndex * 10)"> <!-- Simulating duration -->
            <button type="submit" class="bg-gradient-to-r from-orange to-red-500 hover:from-orange hover:to-red-600 text-white text-[11px] font-black uppercase tracking-widest px-6 py-2.5 rounded-lg shadow-[0_4px_15px_rgba(249,115,22,0.4)] transition-all transform hover:-translate-y-0.5 focus:outline-none">
                Submit Simulator Exam
            </button>
        </form>
    </header>

    <!-- Main Workspace -->
    <div class="flex-grow flex overflow-hidden">
        
        <!-- Left Panel: Grid Navigation (25%) -->
        <aside class="hidden md:flex md:w-72 bg-[#0a1526] border-r border-white/5 flex-col shrink-0 overflow-y-auto p-6 shadow-inner">
            <h3 class="font-black text-white text-[11px] uppercase tracking-widest mb-6 border-b border-white/10 pb-3">Question Navigator</h3>
            
            <!-- Grid list -->
            <div class="grid grid-cols-5 gap-3">
                <template x-for="i in Array.from({length: total}, (_, idx) => idx)">
                    <button @click="activeIndex = i"
                            :class="[
                                activeIndex === i ? 'ring-2 ring-cyan ring-offset-2 ring-offset-[#0a1526] transform scale-110' : '',
                                answers[i].flagged ? 'bg-gradient-to-br from-orange to-red-500 text-white border-transparent shadow-[0_2px_10px_rgba(249,115,22,0.3)]' : 
                                (answers[i].selected ? 'bg-cyan/20 text-cyan border border-cyan/40 shadow-[0_2px_10px_rgba(0,212,170,0.2)]' : 'bg-white/5 text-gray-400 border border-white/10 hover:bg-white/10')
                            ]"
                            class="h-10 w-10 rounded-lg text-xs font-black transition-all duration-200 flex items-center justify-center focus:outline-none">
                        <span x-text="i + 1"></span>
                    </button>
                </template>
            </div>

            <!-- Legends -->
            <div class="mt-8 border-t border-white/10 pt-6 space-y-4 text-[11px] uppercase tracking-widest text-gray-400 font-bold">
                <div class="flex items-center space-x-3">
                    <span class="h-5 w-5 bg-white/5 border border-white/10 rounded-md"></span>
                    <span>Unanswered</span>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="h-5 w-5 bg-cyan/20 border border-cyan/40 rounded-md shadow-[0_0_8px_rgba(0,212,170,0.2)]"></span>
                    <span class="text-cyan">Answered</span>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="h-5 w-5 bg-gradient-to-br from-orange to-red-500 rounded-md shadow-[0_0_8px_rgba(249,115,22,0.3)]"></span>
                    <span class="text-orange">Flagged for Review</span>
                </div>
            </div>
        </aside>

        <!-- Main Body Workspace (75%) -->
        <div class="flex-1 flex flex-col overflow-y-auto p-6 md:p-10 relative">
            
            <template x-for="(ans, index) in answers" :key="index">
                <div x-show="activeIndex == index" class="space-y-8 max-w-4xl mx-auto w-full relative z-10" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
                    
                    <!-- Progress Header -->
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-black text-navy uppercase tracking-widest">Question <span x-text="index + 1" class="text-cyan text-lg"></span> of <span x-text="total"></span></span>
                        <div class="w-1/2 bg-gray-200 h-1.5 rounded-full overflow-hidden flex">
                            <div class="bg-gradient-to-r from-cyan to-blue-500 h-full rounded-full transition-all duration-500 ease-out" :style="'width: ' + (((index + 1) / total) * 100) + '%'"></div>
                        </div>
                    </div>

                    <!-- Question Container -->
                    <div class="bg-white rounded-2xl p-8 md:p-10 shadow-[0_10px_40px_rgba(0,0,0,0.04)] border border-gray-100 space-y-8 relative overflow-hidden">
                        <!-- Decorative accent -->
                        <div class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-cyan to-blue-500"></div>

                        <!-- Heading -->
                        <div class="flex justify-between items-start pb-6 border-b border-gray-100">
                            <div>
                                <span class="bg-cyan/10 text-cyan font-black text-[10px] px-3 py-1.5 rounded-lg uppercase tracking-widest border border-cyan/20" x-text="'Topic: ' + (answers[index].correct || '').slice(0, 15)"></span>
                            </div>
                            <!-- Flag trigger -->
                            <button @click="toggleFlag(index)" 
                                    :class="answers[index].flagged ? 'text-orange font-bold bg-orange/10 border-orange/30' : 'text-gray-400 hover:text-navy hover:bg-gray-50 border-transparent'"
                                    class="text-[11px] font-black uppercase tracking-widest border px-3 py-1.5 rounded-lg transition-colors focus:outline-none flex items-center space-x-2">
                                <span class="text-sm">⚑</span>
                                <span x-text="answers[index].flagged ? 'Flagged' : 'Flag for Review'"></span>
                            </button>
                        </div>

                        <!-- Question Text -->
                        <div class="prose max-w-none">
                            <p class="text-lg md:text-xl font-bold text-navy leading-relaxed">
                                @if(count($answers) > 0)
                                    @foreach($answers as $idx => $ans)
                                        <span x-show="activeIndex == {{ $idx }}">{!! $ans->question->question_text !!}</span>
                                    @endforeach
                                @endif
                            </p>
                        </div>

                        <!-- Answer Choice Options list -->
                        <div class="space-y-4 pt-2">
                            @foreach($answers as $idx => $ans)
                                <div x-show="activeIndex == {{ $idx }}" class="space-y-4">
                                    
                                    <!-- 1. Single / Multiple Choice / Yes-No Rendering -->
                                    <template x-if="answers[{{ $idx }}].type === 'single_choice' || answers[{{ $idx }}].type === 'multiple_choice' || answers[{{ $idx }}].type === 'yes_no'">
                                        <div class="space-y-3">
                                            <template x-for="opt in answers[{{ $idx }}].options" :key="opt.key">
                                                <label class="group relative flex items-start space-x-4 p-5 rounded-xl border-2 transition-all duration-200 cursor-pointer"
                                                       :class="(answers[{{ $idx }}].selected || '').split(',').includes(opt.key) ? 'border-cyan bg-cyan/5 shadow-[0_4px_15px_rgba(0,212,170,0.1)]' : 'border-gray-100 bg-white hover:border-gray-300 hover:bg-gray-50'">
                                                    <div class="pt-0.5">
                                                        <template x-if="answers[{{ $idx }}].type === 'single_choice' || answers[{{ $idx }}].type === 'yes_no'">
                                                            <input type="radio" :name="'q_' + {{ $idx }}" :value="opt.key" @change="saveAnswer({{ $idx }}, opt.key)" :checked="answers[{{ $idx }}].selected === opt.key" class="peer sr-only">
                                                        </template>
                                                        <template x-if="answers[{{ $idx }}].type === 'multiple_choice'">
                                                            <input type="checkbox" :name="'q_' + {{ $idx }} + '[]'" :value="opt.key" @change="toggleCheckbox({{ $idx }}, opt.key)" :checked="(answers[{{ $idx }}].selected || '').split(',').includes(opt.key)" class="peer sr-only">
                                                        </template>
                                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors" :class="(answers[{{ $idx }}].selected || '').split(',').includes(opt.key) ? 'border-cyan' : 'border-gray-300 group-hover:border-gray-400'">
                                                            <div class="w-3 h-3 rounded-full bg-cyan transition-transform transform scale-0" :class="(answers[{{ $idx }}].selected || '').split(',').includes(opt.key) ? 'scale-100' : ''"></div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1">
                                                        <span class="font-black text-gray-400 mr-2 uppercase tracking-widest text-sm" :class="(answers[{{ $idx }}].selected || '').split(',').includes(opt.key) ? 'text-cyan' : ''" x-text="opt.key + '.'"></span> 
                                                        <span class="text-navy font-medium leading-relaxed" x-text="opt.text"></span>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- 2. Drag & Drop (Ordering) Rendering -->
                                    <template x-if="answers[{{ $idx }}].type === 'drag_drop'">
                                        <div class="space-y-4">
                                            <p class="text-xs font-bold text-gray-400 uppercase">Sequencing Items (Click arrows to reorder items to match correct sequence):</p>
                                            <div class="space-y-2">
                                                <template x-for="(item, itemIdx) in (answers[{{ $idx }}].drag_items || [])" :key="item.id">
                                                    <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-xl shadow-sm">
                                                        <div class="flex items-center space-x-3">
                                                            <span class="bg-gray-100 text-gray-500 font-bold px-2 py-0.5 rounded text-xs" x-text="itemIdx + 1"></span>
                                                            <span class="text-navy font-semibold" x-text="item.text"></span>
                                                        </div>
                                                        <div class="flex space-x-1">
                                                            <button type="button" 
                                                                    @click="
                                                                        if (itemIdx > 0) {
                                                                            let temp = answers[{{ $idx }}].drag_items[itemIdx];
                                                                            answers[{{ $idx }}].drag_items[itemIdx] = answers[{{ $idx }}].drag_items[itemIdx - 1];
                                                                            answers[{{ $idx }}].drag_items[itemIdx - 1] = temp;
                                                                            saveAnswer({{ $idx }}, answers[{{ $idx }}].drag_items.map(di => di.id).join(','));
                                                                        }
                                                                    "
                                                                    x-show="itemIdx > 0"
                                                                    class="text-gray-400 hover:text-navy px-2 py-1 text-xs bg-gray-50 rounded">▲</button>
                                                            <button type="button" 
                                                                    @click="
                                                                        if (itemIdx < answers[{{ $idx }}].drag_items.length - 1) {
                                                                            let temp = answers[{{ $idx }}].drag_items[itemIdx];
                                                                            answers[{{ $idx }}].drag_items[itemIdx] = answers[{{ $idx }}].drag_items[itemIdx + 1];
                                                                            answers[{{ $idx }}].drag_items[itemIdx + 1] = temp;
                                                                            saveAnswer({{ $idx }}, answers[{{ $idx }}].drag_items.map(di => di.id).join(','));
                                                                        }
                                                                    "
                                                                    x-show="itemIdx < answers[{{ $idx }}].drag_items.length - 1"
                                                                    class="text-gray-400 hover:text-navy px-2 py-1 text-xs bg-gray-50 rounded">▼</button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- 3. Matching Pairs Rendering -->
                                    <template x-if="answers[{{ $idx }}].type === 'matching'">
                                        <div class="space-y-4">
                                            <p class="text-xs font-bold text-gray-400 uppercase">Matching Definitions (Type corresponding match text):</p>
                                            <div class="space-y-3">
                                                <template x-for="(pair, pairIdx) in (answers[{{ $idx }}].matching_pairs || [])" :key="pairIdx">
                                                    <div class="grid grid-cols-2 gap-4 items-center p-4 bg-white border border-gray-200 rounded-xl">
                                                        <div class="text-navy font-bold" x-text="pair.left"></div>
                                                        <input type="text" placeholder="Type correct match..."
                                                               @input="
                                                                   answers[{{ $idx }}].matching_pairs[pairIdx].input = $event.target.value;
                                                                   let values = answers[{{ $idx }}].matching_pairs.map(p => p.input || '');
                                                                   saveAnswer({{ $idx }}, values.join('||'));
                                                               "
                                                               class="border-gray-300 rounded text-sm px-3 py-1.5 focus:border-cyan focus:ring-cyan">
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- 4. Hotspot Selection Rendering -->
                                    <template x-if="answers[{{ $idx }}].type === 'hotspot'">
                                        <div class="space-y-4">
                                            <p class="text-xs font-bold text-gray-400 uppercase">Complete configuration selectors:</p>
                                            <div class="space-y-4">
                                                <template x-for="(box, boxIdx) in (answers[{{ $idx }}].hotspot_answers || [])" :key="box.id">
                                                    <div class="p-4 bg-white border border-gray-200 rounded-xl space-y-2">
                                                        <label class="block text-xs font-bold text-navy" x-text="box.label"></label>
                                                        <select @change="
                                                                    box.selected = $event.target.value;
                                                                    let vals = answers[{{ $idx }}].hotspot_answers.map(b => b.selected || '');
                                                                    saveAnswer({{ $idx }}, vals.join(','));
                                                                "
                                                                class="w-full border-gray-300 rounded text-xs px-3 py-2 focus:border-cyan focus:ring-cyan">
                                                            <option value="">Choose option...</option>
                                                            <template x-for="optVal in box.options" :key="optVal">
                                                                <option :value="optVal" x-text="optVal"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Show Explanation trigger (Practice Mode Only) -->
                                    <template x-if="mode === 'practice'">
                                        <div class="pt-8 border-t border-gray-100">
                                            <button @click="answers[{{ $idx }}].revealed = !answers[{{ $idx }}].revealed" 
                                                    class="text-[11px] font-black uppercase tracking-widest text-cyan hover:text-navy transition-colors focus:outline-none flex items-center space-x-2">
                                                <span x-text="answers[{{ $idx }}].revealed ? 'Hide Correct Answer' : 'Show Answer & Explanation'"></span>
                                                <span class="text-sm" x-text="answers[{{ $idx }}].revealed ? '↑' : '↓'"></span>
                                            </button>
                                            
                                            <!-- Explanation Details -->
                                            <div x-show="answers[{{ $idx }}].revealed" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="mt-6 p-6 bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-2xl text-sm leading-relaxed shadow-sm relative overflow-hidden" style="display: none;">
                                                <div class="absolute top-0 left-0 w-1.5 h-full bg-green-500"></div>
                                                
                                                <template x-if="answers[{{ $idx }}].type === 'single_choice' || answers[{{ $idx }}].type === 'multiple_choice' || answers[{{ $idx }}].type === 'yes_no'">
                                                    <p class="font-black text-navy mb-3 uppercase tracking-widest text-[11px]">Correct Answer: <span class="text-green-600 text-sm ml-1" x-text="answers[{{ $idx }}].correct"></span></p>
                                                </template>
                                                
                                                <template x-if="answers[{{ $idx }}].type === 'drag_drop'">
                                                    <p class="font-black text-navy mb-3 uppercase tracking-widest text-[11px]">Correct Order: <span class="text-green-600 text-sm ml-1" x-text="answers[{{ $idx }}].correct"></span></p>
                                                </template>

                                                <template x-if="answers[{{ $idx }}].type === 'matching'">
                                                    <p class="font-black text-navy mb-3 uppercase tracking-widest text-[11px]">Correct Matches: 
                                                        <template x-for="p in (answers[{{ $idx }}].matching_pairs || [])">
                                                            <span class="block text-green-600 text-xs ml-1 font-semibold" x-text="p.left + ' → ' + p.right"></span>
                                                        </template>
                                                    </p>
                                                </template>

                                                <template x-if="answers[{{ $idx }}].type === 'hotspot'">
                                                    <p class="font-black text-navy mb-3 uppercase tracking-widest text-[11px]">Correct Configurations: 
                                                        <template x-for="b in (answers[{{ $idx }}].hotspot_answers || [])">
                                                            <span class="block text-green-600 text-xs ml-1 font-semibold" x-text="b.label + ': ' + b.correct_answer"></span>
                                                        </template>
                                                    </p>
                                                </template>

                                                <div class="prose max-w-none text-gray-700 font-medium mt-3">
                                                    {!! $ans->question->explanation !!}
                                                </div>
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
    <footer class="bg-white border-t border-gray-200 h-[80px] flex items-center justify-between px-6 md:px-10 shadow-[0_-4px_20px_rgba(0,0,0,0.02)] shrink-0 z-20 relative">
        <button @click="activeIndex = Math.max(0, activeIndex - 1)" 
                :disabled="activeIndex === 0" 
                class="border-2 border-gray-200 hover:border-cyan text-navy hover:text-cyan hover:bg-cyan/5 text-[11px] uppercase tracking-widest font-black py-3 px-6 rounded-xl transition-all focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
            <span>&larr;</span> <span>Previous Question</span>
        </button>

        <button @click="activeIndex = Math.min(total - 1, activeIndex + 1)" 
                :disabled="activeIndex === total - 1" 
                class="bg-gradient-to-r from-cyan to-blue-500 hover:from-cyan hover:to-blue-600 text-white text-[11px] uppercase tracking-widest font-black py-3 px-8 rounded-xl shadow-[0_4px_15px_rgba(0,212,170,0.3)] transition-all transform hover:-translate-y-0.5 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center space-x-2">
            <span>Next Question</span> <span>&rarr;</span>
        </button>
    </footer>
</div>
@endsection
