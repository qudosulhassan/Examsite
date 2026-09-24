{{-- One question in the results "Detailed Answers Review". Expects: $answer (TestAnswer with question), $index --}}
@php
    $engine = \App\Services\TestEngine\EngineQuestion::payload($answer->question);
    $key = $engine['answer'] ?? [];
    $status = $answer->response['status'] ?? ($answer->selected_option ? ($answer->is_correct ? 'correct' : 'incorrect') : null);
    $badge = match ($status) {
        'correct' => ['Correct', 'bg-green-50 border-green-200 text-green-700'],
        'incorrect' => ['Incorrect', 'bg-red-50 border-red-200 text-red-700'],
        'revealed', 'reviewed' => ['Revealed', 'bg-amber-50 border-amber-200 text-amber-700'],
        default => ['Not answered', 'bg-gray-50 border-gray-200 text-gray-500'],
    };
    $correctText = match (true) {
        !empty($key['correctAnswer']) => implode(', ', $key['correctAnswer']),
        !empty($engine['ia']) && !empty($key['rowAnswers']) => null,
        !empty($key['boxes']) => collect($key['boxes'])->map(fn ($b) => "Box {$b['box']}: {$b['value']}")->implode(' | '),
        default => null,
    };
    $optionText = collect($engine['options'] ?? [])->pluck('text', 'label');
@endphp
<div class="space-y-5 pt-6 {{ $index ? 'border-t border-gray-100' : '' }}">
    <div class="flex justify-between items-center gap-3">
        <span class="font-black text-navy uppercase tracking-widest text-lg">Question <span class="text-cyan">{{ $index + 1 }}</span></span>
        <span class="px-4 py-1.5 rounded-lg font-black uppercase tracking-widest text-[10px] border shadow-sm {{ $badge[1] }}">{{ $badge[0] }}</span>
    </div>

    <div class="text-base font-semibold text-navy leading-relaxed space-y-2">
        @if(!empty($engine['questionHtml']))
            <div class="prose max-w-none">{!! $engine['questionHtml'] !!}</div>
        @else
            @foreach($engine['question'] ?? [] as $p)<p>{{ $p }}</p>@endforeach
        @endif
    </div>

    @foreach($engine['images'] ?? [] as $src)
        <img src="{{ $src }}" alt="Question {{ $index + 1 }} exhibit" loading="lazy" class="max-w-full h-auto border border-gray-100 rounded-xl">
    @endforeach

    <div class="text-sm p-4 bg-gray-50 rounded-xl border border-gray-100 space-y-2">
        <div>
            <span class="font-black text-gray-500 uppercase tracking-widest text-[10px] mr-2">Your answer:</span>
            <span class="font-bold {{ $status === 'correct' ? 'text-green-600' : ($status === 'incorrect' ? 'text-red-500' : 'text-gray-600') }}">{{ $answer->selected_option && !in_array($answer->selected_option, ['revealed', 'reviewed', 'correct', 'incorrect'], true) ? $answer->selected_option : ($status === 'revealed' || $status === 'reviewed' ? 'Answer revealed' : 'Not answered') }}</span>
        </div>
        @if($correctText !== null)
            <div>
                <span class="font-black text-gray-500 uppercase tracking-widest text-[10px] mr-2">Correct answer:</span>
                <span class="font-black text-green-600">{{ $correctText }}</span>
                @if(!empty($key['correctAnswer']))
                    <ul class="mt-1 text-gray-700 list-disc pl-5">
                        @foreach($key['correctAnswer'] as $letter)<li><b>{{ $letter }}.</b> {{ $optionText[$letter] ?? '' }}</li>@endforeach
                    </ul>
                @endif
            </div>
        @elseif(!empty($engine['ia']) && !empty($key['rowAnswers']))
            <div>
                <span class="font-black text-gray-500 uppercase tracking-widest text-[10px]">Correct answer:</span>
                <ul class="mt-1 text-gray-700 list-disc pl-5">
                    @foreach($engine['ia']['rows'] as $n => $row)<li>{{ $row['label'] }} → <b class="text-green-600">{{ $key['rowAnswers'][$n] ?? '' }}</b></li>@endforeach
                </ul>
            </div>
        @endif
        @foreach($key['answerImages'] ?? [] as $src)
            <img src="{{ $src }}" alt="Correct answer" loading="lazy" class="max-w-full h-auto border border-gray-100 rounded-xl bg-white">
        @endforeach
    </div>

    @if(!empty($key['explanation']) || !empty($key['explanationHtml']) || !empty($key['reference']))
        <div class="p-6 bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-2xl text-sm leading-relaxed shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-green-500"></div>
            <strong class="block text-navy font-black uppercase tracking-widest text-[11px] mb-3">Explanation</strong>
            <div class="prose max-w-none text-gray-700 font-medium space-y-2">
                @if(!empty($key['explanation']))
                    @foreach($key['explanation'] as $e)
                        @if(is_array($e))<img src="{{ $e['img'] }}" alt="Explanation image" loading="lazy" class="max-w-full h-auto">@else<p>{{ $e }}</p>@endif
                    @endforeach
                @elseif(!empty($key['explanationHtml']))
                    {!! $key['explanationHtml'] !!}
                @endif
                @foreach($key['reference'] ?? [] as $ref)
                    <p class="break-all">@if(preg_match('#^https?://#i', $ref))<a href="{{ $ref }}" target="_blank" rel="noopener noreferrer" class="text-cyan underline">{{ $ref }}</a>@else{{ $ref }}@endif</p>
                @endforeach
            </div>
        </div>
    @endif
</div>
