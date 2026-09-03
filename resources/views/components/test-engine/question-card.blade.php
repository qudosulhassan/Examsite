@props([
    'ans',
    'index',
    'total' => 1,
    'mode' => 'practice',
])

@php
    $question = $ans->question;
    $qType = $question->question_type ?? 'single_choice';
    $qData = $question->question_data ?? [];
    $scenario = $qData['scenario'] ?? null;
    $boxes = $qData['boxes'] ?? $qData['hotspot_answers'] ?? [];
    $dragItems = $qData['drag_items'] ?? [];
    $matchingPairs = $qData['matching_pairs'] ?? [];
    
    // Exhibits / Images
    $mediaItems = $question->media ?? collect();
    $qImage = $mediaItems->firstWhere('media_type', 'question_image')?->media_url 
              ?? ($question->image_filename ? '/storage/questions/' . $question->image_filename : null);
              
    // Badge Class
    $badgeClass = 'multiple-choice';
    $badgeLabel = 'Multiple Choice';
    if ($qType === 'hotspot') {
        $badgeClass = 'hotspot';
        $badgeLabel = 'Hotspot';
    } elseif ($qType === 'drag_drop') {
        $badgeClass = 'drag-drop';
        $badgeLabel = 'Drag & Drop';
    } elseif ($qType === 'matching') {
        $badgeClass = 'matching';
        $badgeLabel = 'Matching';
    } elseif ($qType === 'multiple_choice') {
        $badgeClass = 'multiple-choice';
        $badgeLabel = 'Multiple Answer';
    } elseif ($qType === 'yes_no') {
        $badgeClass = 'yes-no';
        $badgeLabel = 'Yes / No';
    }
@endphp

<article class="question-card" id="q{{ $index + 1 }}">
    
    <!-- Question Top Header -->
    <div class="question-top">
        <div class="flex items-center">
            <span class="question-number">Question {{ $index + 1 }}</span>
            <span class="type-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
        </div>
        <div class="flex items-center space-x-3">
            <span class="status" x-text="answers[{{ $index }}].selected ? 'Answered' : 'Not answered'"></span>
            
            <button type="button" 
                    @click="toggleFlag({{ $index }})" 
                    :class="answers[{{ $index }}].flagged ? 'text-amber-600 bg-amber-50 border-amber-300' : 'text-gray-400 hover:text-gray-700 bg-white border-gray-200'"
                    class="text-xs font-bold uppercase tracking-wider border px-3 py-1.5 rounded-lg transition-colors flex items-center space-x-1.5 focus:outline-none">
                <span class="text-sm">⚑</span>
                <span x-text="answers[{{ $index }}].flagged ? 'Flagged' : 'Flag'"></span>
            </button>
        </div>
    </div>

    <!-- Scenario Background (If present) -->
    @if($scenario)
        <div class="mb-4 p-4 bg-slate-50 border-l-4 border-slate-700 rounded-r-xl space-y-1">
            <span class="text-[11px] font-black text-slate-700 uppercase tracking-wider block">Scenario Background</span>
            <div class="prose max-w-none text-gray-700 text-sm leading-relaxed">{!! $scenario !!}</div>
        </div>
    @endif

    <!-- Question Content Body -->
    <div class="question-content space-y-3">
        <div class="prose max-w-none text-navy font-medium text-base md:text-lg leading-relaxed">
            {!! $question->question_text !!}
        </div>

        <!-- Exhibits / Figure Images -->
        @if($qImage || $mediaItems->count() > 0)
            <div class="exhibits">
                @if($qImage)
                    <figure>
                        <img src="{{ $qImage }}" alt="Exhibit for Question {{ $index + 1 }}" loading="lazy">
                        <figcaption>Exhibit for Question {{ $index + 1 }}</figcaption>
                    </figure>
                @endif
                @foreach($mediaItems as $m)
                    @if($m->media_url !== $qImage)
                        <figure>
                            <img src="{{ $m->media_url }}" alt="{{ $m->caption ?? 'Exhibit' }}" loading="lazy">
                            @if($m->caption)
                                <figcaption>{{ $m->caption }}</figcaption>
                            @endif
                        </figure>
                    @endif
                @endforeach
            </div>
        @endif

        <!-- Answer Areas / Question Type Controls -->
        
        <!-- 1. Single Choice / Multiple Response / Yes-No -->
        <template x-if="answers[{{ $index }}].type === 'single_choice' || answers[{{ $index }}].type === 'multiple_choice' || answers[{{ $index }}].type === 'yes_no'">
            <div class="options">
                <template x-for="opt in answers[{{ $index }}].options" :key="opt.key">
                    <label class="option" :class="(answers[{{ $index }}].selected || '').split(',').includes(opt.key) ? 'border-accent bg-blue-50/50' : ''">
                        <template x-if="answers[{{ $index }}].type === 'single_choice' || answers[{{ $index }}].type === 'yes_no'">
                            <input type="radio" :name="'q_' + {{ $index }}" :value="opt.key" @change="saveAnswer({{ $index }}, opt.key)" :checked="answers[{{ $index }}].selected === opt.key">
                        </template>
                        <template x-if="answers[{{ $index }}].type === 'multiple_choice'">
                            <input type="checkbox" :name="'q_' + {{ $index }} + '[]'" :value="opt.key" @change="toggleCheckbox({{ $index }}, opt.key)" :checked="(answers[{{ $index }}].selected || '').split(',').includes(opt.key)">
                        </template>
                        <span class="option-letter" x-text="opt.key"></span>
                        <span class="option-text text-sm font-medium text-navy leading-relaxed" x-text="opt.text"></span>
                    </label>
                </template>
            </div>
        </template>

        <!-- 2. Hotspot Question (Question 11 Interactive Selectors) -->
        <template x-if="answers[{{ $index }}].type === 'hotspot'">
            <div class="special-answer space-y-4">
                <div class="special-title">Answer Area Selection</div>
                
                <template x-for="(box, boxIdx) in (answers[{{ $index }}].hotspot_answers || [])" :key="box.id || boxIdx">
                    <div class="p-3 bg-white border border-gray-200 rounded-xl space-y-2">
                        <label class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider" x-text="box.label || ('Selector ' + (boxIdx + 1))"></label>
                        <select @change="
                                    box.selected = $event.target.value;
                                    let vals = answers[{{ $index }}].hotspot_answers.map(b => b.selected || '');
                                    saveAnswer({{ $index }}, vals.join(','));
                                "
                                class="engine-search bg-white text-navy font-semibold text-sm">
                            <option value="">[ Select Answer... ]</option>
                            <template x-for="optVal in (box.options || [])" :key="optVal">
                                <option :value="optVal" x-text="optVal"></option>
                            </template>
                        </select>
                    </div>
                </template>
            </div>
        </template>

        <!-- 3. Drag & Drop (Sequencing Items) -->
        <template x-if="answers[{{ $index }}].type === 'drag_drop'">
            <div class="special-answer space-y-3">
                <div class="special-title">Sequencing Order (Click arrows to arrange in correct order)</div>
                <div class="space-y-2">
                    <template x-for="(item, itemIdx) in (answers[{{ $index }}].drag_items || [])" :key="item.id || itemIdx">
                        <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-xl shadow-sm">
                            <div class="flex items-center space-x-3">
                                <span class="option-letter" x-text="itemIdx + 1"></span>
                                <span class="text-navy font-semibold text-sm" x-text="item.text"></span>
                            </div>
                            <div class="flex space-x-1">
                                <button type="button" 
                                        @click="
                                            if (itemIdx > 0) {
                                                let temp = answers[{{ $index }}].drag_items[itemIdx];
                                                answers[{{ $index }}].drag_items[itemIdx] = answers[{{ $index }}].drag_items[itemIdx - 1];
                                                answers[{{ $index }}].drag_items[itemIdx - 1] = temp;
                                                saveAnswer({{ $index }}, answers[{{ $index }}].drag_items.map(di => di.id).join(','));
                                            }
                                        "
                                        x-show="itemIdx > 0"
                                        class="engine-btn text-xs py-1 px-2">▲</button>
                                <button type="button" 
                                        @click="
                                            if (itemIdx < answers[{{ $index }}].drag_items.length - 1) {
                                                let temp = answers[{{ $index }}].drag_items[itemIdx];
                                                answers[{{ $index }}].drag_items[itemIdx] = answers[{{ $index }}].drag_items[itemIdx + 1];
                                                answers[{{ $index }}].drag_items[itemIdx + 1] = temp;
                                                saveAnswer({{ $index }}, answers[{{ $index }}].drag_items.map(di => di.id).join(','));
                                            }
                                        "
                                        x-show="itemIdx < answers[{{ $index }}].drag_items.length - 1"
                                        class="engine-btn text-xs py-1 px-2">▼</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- 4. Matching Pairs -->
        <template x-if="answers[{{ $index }}].type === 'matching'">
            <div class="special-answer space-y-3">
                <div class="special-title">Matching Pairs</div>
                <div class="space-y-3">
                    <template x-for="(pair, pairIdx) in (answers[{{ $index }}].matching_pairs || [])" :key="pairIdx">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-center p-3 bg-white border border-gray-200 rounded-xl">
                            <div class="text-navy font-bold text-sm" x-text="pair.left"></div>
                            <input type="text" placeholder="Type match..."
                                   @input="
                                       answers[{{ $index }}].matching_pairs[pairIdx].input = $event.target.value;
                                       let values = answers[{{ $index }}].matching_pairs.map(p => p.input || '');
                                       saveAnswer({{ $index }}, values.join('||'));
                                   "
                                   class="engine-search text-sm">
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <!-- Action Buttons -->
    <div class="question-actions">
        <template x-if="mode === 'practice'">
            <button type="button" class="engine-btn primary" @click="checkQuestion({{ $index }})">Check Answer</button>
        </template>
        <template x-if="mode === 'practice'">
            <button type="button" class="engine-btn" @click="answers[{{ $index }}].revealed = !answers[{{ $index }}].revealed">
                <span x-text="answers[{{ $index }}].revealed ? 'Hide Answer' : 'Reveal Answer'"></span>
            </button>
        </template>
        <button type="button" class="engine-btn ghost" @click="answers[{{ $index }}].selected = ''; ajaxSave({{ $index }})">Reset</button>
    </div>

    <!-- Feedback Banner (If checked in practice mode) -->
    <template x-if="answers[{{ $index }}].checked">
        <div class="feedback" :class="answers[{{ $index }}].is_correct ? 'good' : 'bad'">
            <span x-text="answers[{{ $index }}].is_correct ? 'Correct! Excellent work.' : 'Incorrect. Review the solution below.'"></span>
        </div>
    </template>

    <!-- Correct Answer & Explanation Panel (Practice/Review Mode Only) -->
    <template x-if="mode === 'practice' || mode === 'review'">
        <div x-show="answers[{{ $index }}].revealed || answers[{{ $index }}].checked" x-transition class="answer-panel space-y-3">
            <div class="answer-heading">Correct Answer</div>
            <div class="answer-value" x-text="answers[{{ $index }}].correct || 'See explanation'"></div>
            
            <div class="explanation-heading">Explanation & References</div>
            <div class="explanation prose max-w-none text-gray-700 text-sm font-normal">
                {!! $question->explanation !!}
            </div>

            @if($question->references && $question->references->count() > 0)
                <div class="pt-3 border-t border-blue-100 flex flex-wrap gap-2 text-xs">
                    <span class="font-bold text-gray-500 uppercase tracking-wider block w-full mb-1">References</span>
                    @foreach($question->references as $ref)
                        <a href="{{ $ref->url }}" target="_blank" class="text-accent hover:underline bg-white border border-blue-200 px-2.5 py-1 rounded-md">
                            {{ $ref->title }} &rarr;
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </template>

    <!-- Next Question Wrapper -->
    <div class="next-question-wrap">
        <button type="button" 
                class="engine-btn primary next-question-btn" 
                @click="activeIndex = Math.min(total - 1, activeIndex + 1)"
                x-show="activeIndex < total - 1">
            Next Question &rarr;
        </button>
    </div>

</article>
