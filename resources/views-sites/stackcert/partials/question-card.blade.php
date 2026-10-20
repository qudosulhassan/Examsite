{{--
    One free practice question: pick an option, "Check answer" reveals the result and the explanation.
    Needs $question and $number. The full text is in the HTML for everyone (no hidden-from-users content).
--}}
@php
    $options = ['A' => $question->option_a, 'B' => $question->option_b, 'C' => $question->option_c, 'D' => $question->option_d];
    $correct = $question->correct_option;
@endphp
<article id="question-{{ $number }}" class="scroll-mt-28 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7"
         x-data="{ picked: null, checked: false, correct: '{{ $correct }}' }">
    <header class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-4">
        <h3 class="text-base font-extrabold text-slate-900">Question {{ $number }}</h3>
        @if(!empty($question->topic))
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $question->topic }}</span>
        @endif
    </header>

    <div class="prose prose-slate mt-5 max-w-none text-base leading-relaxed text-slate-800">{!! $question->question_text !!}</div>

    <fieldset class="mt-5 space-y-2.5">
        <legend class="sr-only">Choose one answer for question {{ $number }}</legend>
        @foreach($options as $letter => $text)
            @continue(trim((string) $text) === '')
            <label class="relative flex cursor-pointer items-start gap-3 rounded-xl border-2 border-slate-200 p-4 text-slate-800 transition hover:border-slate-300 hover:bg-slate-50"
                   :class="{
                       'border-green-500 bg-green-50': checked && correct === '{{ $letter }}',
                       'border-red-400 bg-red-50': checked && picked === '{{ $letter }}' && correct !== '{{ $letter }}',
                       'opacity-60': checked && picked !== '{{ $letter }}' && correct !== '{{ $letter }}',
                       'cursor-default': checked
                   }"
                   :style="(!checked && picked === '{{ $letter }}') ? 'border-color: var(--brand); background: var(--brand-soft);' : ''">
                <input type="radio" class="sr-only" name="q{{ $number }}" value="{{ $letter }}" x-model="picked" :disabled="checked">
                <span class="flex h-7 w-7 flex-none items-center justify-center rounded-full bg-white text-sm font-extrabold text-slate-700 ring-1 ring-slate-300">{{ $letter }}</span>
                <span class="pt-0.5 leading-relaxed">{{ $text }}</span>
            </label>
        @endforeach
    </fieldset>

    <div class="mt-5 flex flex-wrap items-center gap-3">
        <button type="button" @click="checked = true" :disabled="picked === null || checked"
                class="rounded-xl px-5 py-2.5 text-sm font-bold text-white transition disabled:cursor-not-allowed disabled:opacity-40" style="background: var(--brand);">Check answer</button>
        <p x-cloak class="text-sm font-bold" :class="[checked ? '' : 'hidden', picked === correct ? 'text-green-700' : 'text-red-600']"
           x-text="picked === correct ? 'Correct.' : 'Not quite. The correct answer is ' + correct + '.'"></p>
    </div>

    <div x-cloak :class="checked ? '' : 'hidden'" class="mt-5 rounded-xl bg-slate-50 p-5 ring-1 ring-slate-200">
        <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Answer and explanation</p>
        <p class="mt-2 text-sm font-bold text-slate-900">Correct answer: {{ $correct }}</p>
        @if(!empty(trim(strip_tags((string) $question->explanation))))
            <div class="prose prose-sm prose-slate mt-2 max-w-none text-slate-700">{!! $question->explanation !!}</div>
        @endif
    </div>
</article>
