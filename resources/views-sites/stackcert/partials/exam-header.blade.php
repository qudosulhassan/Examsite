{{--
    Header shared by the three exam pages: breadcrumb, H1, one-line summary and the Guide / Free questions / Plans tabs.
    Needs: $exam, $active ('guide'|'questions'|'plans'), $h1, $lede, optional $track.
--}}
@php
    $headBrandName = $currentSite ? $currentSite->name : ($globalSettings['site_name'] ?? config('app.name'));
    $prices = array_filter([(float) $exam->resolved_price_pdf, (float) $exam->resolved_price_engine], fn ($p) => $p > 0);
    $fromPrice = $prices ? min($prices) : null;
    $money = fn ($v) => '$' . number_format((float) $v, ((float) $v == floor((float) $v)) ? 0 : 2);
    $realUpdated = $exam->last_updated_at ?: $exam->updated_at;
    $tabs = [
        ['key' => 'guide', 'label' => 'Exam guide', 'url' => $exam->url],
        ['key' => 'questions', 'label' => 'Free practice questions', 'url' => $exam->questions_url],
        ['key' => 'plans', 'label' => 'Plans' . ($fromPrice ? ' from ' . $money($fromPrice) : ''), 'url' => $exam->plans_url],
    ];
@endphp

<section class="border-b border-slate-200 bg-white">
    <div class="container-custom pt-6 pb-0">
        <nav aria-label="Breadcrumb" class="text-sm text-slate-500">
            <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <li><a href="{{ url('/') }}" class="hover:text-slate-900">Home</a></li>
                @if(!empty($track))
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('tracks.show', $track['slug']) }}" class="hover:text-slate-900">{{ $track['title'] }}</a></li>
                @endif
                <li aria-hidden="true">/</li>
                @if($active === 'guide')
                    <li aria-current="page" class="font-semibold text-slate-800">{{ $exam->exam_code }}</li>
                @else
                    <li><a href="{{ $exam->url }}" class="hover:text-slate-900">{{ $exam->exam_code }}</a></li>
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="font-semibold text-slate-800">{{ $active === 'questions' ? 'Free practice questions' : 'Plans' }}</li>
                @endif
            </ol>
        </nav>

        <div class="mt-5 max-w-3xl">
            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-extrabold tracking-wide" style="background: var(--brand-soft); color: var(--brand-dark);">{{ $exam->exam_code }}</span>
            <h1 class="mt-3 text-3xl font-extrabold leading-tight text-slate-900 sm:text-4xl">{{ $h1 }}</h1>
            <p class="mt-3 text-base leading-relaxed text-slate-600 sm:text-lg">{{ $lede }}</p>
            @if($realUpdated)
                <p class="mt-3 text-sm text-slate-500">Last updated <time datetime="{{ $realUpdated->format('Y-m-d') }}">{{ $realUpdated->format('F j, Y') }}</time></p>
            @endif
        </div>

        <div class="mt-6 -mb-px flex gap-1 overflow-x-auto" role="tablist" aria-label="{{ $exam->exam_code }} pages">
            @foreach($tabs as $tab)
                <a href="{{ $tab['url'] }}" role="tab" aria-selected="{{ $active === $tab['key'] ? 'true' : 'false' }}"
                   class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-bold transition {{ $active === $tab['key'] ? 'text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
                   @if($active === $tab['key']) style="border-color: var(--brand);" @endif>{{ $tab['label'] }}</a>
            @endforeach
        </div>
    </div>
</section>
