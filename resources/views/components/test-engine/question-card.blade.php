@props([
    'ans',
    'index',
    'total'  => 1,
    'mode'   => 'practice',
])

@php
    $question   = $ans->question;
    $qType      = $question->question_type ?? 'single_choice';
    $qData      = $question->question_data ?? [];
    $instructions = $question->instructions ?: ($qData['instructions'] ?? null);
    $selectionLimit = $qData['selection_limit'] ?? 1;
    $boxes      = $qData['boxes'] ?? $qData['hotspot_answers'] ?? [];
    $dragItems  = $qData['drag_items'] ?? [];
    $mediaItems = $question->media ?? collect();

    // Determine badge text — matches master HTML labels exactly
    $badgeClass = 'multiple-choice';
    $badgeLabel = 'Multiple Choice';
    if ($qType === 'hotspot') {
        $badgeClass = 'hotspot';
        $badgeLabel = 'Hotspot';
    } elseif ($qType === 'drag_drop') {
        $badgeClass = 'drag-drop';
        $badgeLabel = 'Drag & Drop';
    } elseif ($qType === 'multiple_choice') {
        $badgeClass = 'multiple-choice';
        $badgeLabel = 'Multiple Choice';
    } elseif ($qType === 'yes_no') {
        $badgeClass = 'multiple-choice';
        $badgeLabel = 'Yes / No';
    }
@endphp

<article class="question-card" id="q{{ $index + 1 }}">

    {{-- Question Top Header --}}
    <div class="question-top">
        <div>
            <span class="question-number">Question {{ $index + 1 }}</span>
            <span class="type-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <span class="status"
                  x-text="answers[{{ $index }}].checked || answers[{{ $index }}].revealed
                          ? (answers[{{ $index }}].is_correct ? 'Completed' : 'Reviewed')
                          : 'Not answered'">
                Not answered
            </span>
            <button type="button"
                    @click="toggleFlag({{ $index }})"
                    :class="answers[{{ $index }}].flagged
                            ? 'text-amber-600 bg-amber-50 border-amber-300'
                            : 'text-gray-400 hover:text-gray-700 bg-white border-gray-200'"
                    class="text-xs font-bold uppercase tracking-wider border px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5 focus:outline-none">
                <span>⚑</span>
                <span x-text="answers[{{ $index }}].flagged ? 'Flagged' : 'Flag'"></span>
            </button>
        </div>
    </div>

    {{-- Question Content --}}
    <div class="question-content">

        {{-- Question Text --}}
        <div class="prose max-w-none" style="margin:0 0 11px;">
            {!! $question->question_text !!}
        </div>

        {{-- Instruction / Note Banner --}}
        @if($instructions)
            <div style="margin:10px 0 14px;padding:10px 13px;background:#fff4db;border:1px solid #f5cc6b;border-radius:10px;font-size:13px;font-weight:600;color:#7a4f00;">
                ℹ {{ $instructions }}
            </div>
        @endif

        {{-- Multi-select instruction badge --}}
        @if($qType === 'multiple_choice' && $selectionLimit > 1)
            <div style="margin:8px 0 14px;display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:#eaf2fa;border:1px solid #b3d4ef;border-radius:999px;font-size:12px;font-weight:800;color:#31526f;">
                ✓ Select {{ $selectionLimit }} options
            </div>
        @endif

        {{-- Exhibits / Images from QuestionMedia --}}
        @if($mediaItems->count() > 0)
            <div class="exhibits">
                @foreach($mediaItems as $m)
                    <figure>
                        <img src="{{ $m->media_url }}"
                             alt="{{ $m->alt_text ?? ($m->caption ?? 'Exhibit') }}"
                             loading="lazy">
                        @if($m->caption)
                            <figcaption>{{ $m->caption }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @endif

        {{-- ============================================================
             ANSWER AREA — Question Type Specific
             ============================================================ --}}

        {{-- 1. Single / Multiple Choice / Yes-No (Radio or Checkbox) --}}
        <template x-if="answers[{{ $index }}].type === 'single_choice'
                     || answers[{{ $index }}].type === 'multiple_choice'
                     || answers[{{ $index }}].type === 'yes_no'">
            <div class="options">
                <template x-for="opt in answers[{{ $index }}].options" :key="opt.key">
                    <label class="option"
                           :class="(answers[{{ $index }}].selected || '').split(',').map(s => s.trim()).includes(opt.key) ? 'selected' : ''">

                        {{-- Radio for single / yes_no --}}
                        <template x-if="answers[{{ $index }}].type !== 'multiple_choice'">
                            <input type="radio"
                                   :name="'q_' + {{ $index }}"
                                   :value="opt.key"
                                   :checked="answers[{{ $index }}].selected === opt.key"
                                   @change="saveAnswer({{ $index }}, opt.key)">
                        </template>

                        {{-- Checkbox for multiple_choice --}}
                        <template x-if="answers[{{ $index }}].type === 'multiple_choice'">
                            <input type="checkbox"
                                   :name="'q_' + {{ $index }} + '[]'"
                                   :value="opt.key"
                                   :checked="(answers[{{ $index }}].selected || '').split(',').map(s => s.trim()).includes(opt.key)"
                                   @change="toggleCheckbox({{ $index }}, opt.key)">
                        </template>

                        <span class="option-letter" x-text="opt.key"></span>
                        <span class="option-text" x-text="opt.text"></span>
                    </label>
                </template>
            </div>
        </template>

        {{-- 2. Hotspot (dropdown selectors) --}}
        <template x-if="answers[{{ $index }}].type === 'hotspot'">
            <div class="special-answer">
                <div class="special-title">Answer Area Selection</div>
                <template x-for="(box, boxIdx) in (answers[{{ $index }}].hotspot_answers || [])" :key="box.id || boxIdx">
                    <div style="margin-bottom:12px;">
                        <label style="display:block;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#4b5563;margin-bottom:5px;"
                               x-text="box.label || ('Dropdown ' + (boxIdx + 1))"></label>
                        <select class="engine-search"
                                style="width:auto;min-width:220px;font-weight:600;"
                                @change="
                                    box.selected = $event.target.value;
                                    let vals = answers[{{ $index }}].hotspot_answers.map(b => b.selected || '');
                                    saveAnswer({{ $index }}, vals.join(','));
                                ">
                            <option value="">[ Select Answer... ]</option>
                            <template x-for="optVal in (box.options || [])" :key="optVal">
                                <option :value="optVal" x-text="optVal"></option>
                            </template>
                        </select>
                    </div>
                </template>
            </div>
        </template>

        {{-- 3. Drag & Drop (Sequencing) --}}
        <template x-if="answers[{{ $index }}].type === 'drag_drop'">
            <div class="special-answer">
                <div class="special-title">Sequencing Order — Click arrows to arrange in correct order</div>
                <div>
                    <template x-for="(item, itemIdx) in (answers[{{ $index }}].drag_items || [])" :key="item.id || itemIdx">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 13px;background:#fff;border:1px solid var(--border);border-radius:10px;margin-bottom:8px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span class="option-letter" x-text="itemIdx + 1"></span>
                                <span style="font-size:14px;font-weight:600;color:var(--text);" x-text="item.text"></span>
                            </div>
                            <div style="display:flex;gap:4px;">
                                <button type="button"
                                        x-show="itemIdx > 0"
                                        class="engine-btn"
                                        style="padding:4px 9px;font-size:12px;"
                                        @click="
                                            let arr = answers[{{ $index }}].drag_items;
                                            if (itemIdx > 0) {
                                                let tmp = arr[itemIdx];
                                                arr[itemIdx] = arr[itemIdx - 1];
                                                arr[itemIdx - 1] = tmp;
                                                answers[{{ $index }}].drag_items = [...arr];
                                                saveAnswer({{ $index }}, answers[{{ $index }}].drag_items.map(di => di.id).join(','));
                                            }
                                        ">▲</button>
                                <button type="button"
                                        x-show="itemIdx < answers[{{ $index }}].drag_items.length - 1"
                                        class="engine-btn"
                                        style="padding:4px 9px;font-size:12px;"
                                        @click="
                                            let arr = answers[{{ $index }}].drag_items;
                                            if (itemIdx < arr.length - 1) {
                                                let tmp = arr[itemIdx];
                                                arr[itemIdx] = arr[itemIdx + 1];
                                                arr[itemIdx + 1] = tmp;
                                                answers[{{ $index }}].drag_items = [...arr];
                                                saveAnswer({{ $index }}, answers[{{ $index }}].drag_items.map(di => di.id).join(','));
                                            }
                                        ">▼</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

    </div>{{-- end .question-content --}}

    {{-- Action Buttons --}}
    <div class="question-actions">
        <template x-if="mode === 'practice'">
            <button type="button"
                    class="engine-btn primary"
                    @click="checkQuestion({{ $index }})">
                Check Answer
            </button>
        </template>
        <template x-if="mode === 'practice'">
            <button type="button"
                    class="engine-btn"
                    @click="revealAnswer({{ $index }})">
                <span x-text="answers[{{ $index }}].revealed ? 'Hide Answer' : 'Reveal Answer'"></span>
            </button>
        </template>
        <button type="button"
                class="engine-btn ghost"
                @click="resetQuestion({{ $index }})">
            Reset
        </button>
    </div>

    {{-- Feedback Banner --}}
    <template x-if="answers[{{ $index }}].feedback">
        <div class="feedback" :class="answers[{{ $index }}].is_correct ? 'good' : 'bad'"
             x-text="answers[{{ $index }}].feedback">
        </div>
    </template>

    {{-- Answer & Explanation Panel --}}
    <template x-if="mode === 'practice' || mode === 'review'">
        <div x-show="answers[{{ $index }}].revealed || answers[{{ $index }}].checked"
             x-transition
             class="answer-panel">
            <div class="answer-heading">Correct Answer</div>
            <div class="answer-value" x-text="answers[{{ $index }}].correct || 'See explanation'"></div>

            <div class="explanation-heading">Explanation</div>
            <div class="explanation">
                {!! $question->explanation !!}
            </div>

            @if($question->references && $question->references->count() > 0)
                <div style="padding-top:12px;border-top:1px solid #cfe2f3;margin-top:12px;display:flex;flex-wrap:wrap;gap:8px;font-size:12px;">
                    <span style="font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.07em;width:100%;margin-bottom:4px;">References</span>
                    @foreach($question->references as $ref)
                        <a href="{{ $ref->url }}" target="_blank"
                           style="color:var(--accent);background:#fff;border:1px solid #b3d4ef;padding:4px 10px;border-radius:6px;text-decoration:none;font-weight:600;">
                            {{ $ref->title }} →
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </template>

    {{-- Next Question / Finish Test Button --}}
    <div class="next-question-wrap"
         x-show="answers[{{ $index }}].checked || answers[{{ $index }}].revealed">
        <button type="button"
                class="engine-btn primary next-question-btn"
                @click="goNext({{ $index }})"
                x-text="{{ $index }} < total - 1 ? 'Next Question →' : 'Finish Test ✓'">
        </button>
    </div>

</article>
