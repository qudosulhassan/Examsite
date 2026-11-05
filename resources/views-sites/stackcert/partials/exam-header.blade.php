{{--
    Top of each exam page. The three pages look and work differently on purpose: they are separate pages, not tabs.
      guide     dark, editorial hero with key facts and two calls to action
      questions focused practice header (a working page, no sales hero)
      plans     centred pricing hero
    Needs: $exam, $active ('guide'|'questions'|'plans'), $h1, $lede, optional $track, optional $bankCount/$freeCount.
--}}
@php
    $prices = array_filter([(float) $exam->resolved_price_pdf, (float) $exam->resolved_price_engine], fn ($p) => $p > 0);
    $fromPrice = $prices ? min($prices) : null;
    $money = fn ($v) => '$' . number_format((float) $v, ((float) $v == floor((float) $v)) ? 0 : 2);
    $realUpdated = $exam->last_updated_at ?: $exam->updated_at;
    $hBank = $bankCount ?? null;
    $hFree = $freeCount ?? null;
    $vendorLabel = $exam->vendor->name ?? '';
@endphp

{{-- Breadcrumb (shared) --}}
@php
    $crumbClass = $active === 'guide' ? 'text-slate-400' : 'text-slate-500';
    $crumbHover = $active === 'guide' ? 'hover:text-white' : 'hover:text-slate-900';
    $crumbNow = $active === 'guide' ? 'text-white' : 'text-slate-800';
@endphp

@if($active === 'guide')
<section class="relative overflow-hidden bg-slate-900 text-white">
    <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(55% 90% at 90% 0%, color-mix(in srgb, var(--brand) 45%, transparent), transparent 70%), radial-gradient(40% 60% at 0% 100%, color-mix(in srgb, var(--brand-dark) 40%, transparent), transparent 70%);"></div>
    <div class="container-custom relative py-10 lg:py-16">
        <nav aria-label="Breadcrumb" class="text-sm {{ $crumbClass }}">
            <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <li><a href="{{ url('/') }}" class="{{ $crumbHover }}">Home</a></li>
                @if(!empty($track))
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('tracks.show', $track['slug']) }}" class="{{ $crumbHover }}">{{ $track['title'] }}</a></li>
                @endif
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="font-semibold {{ $crumbNow }}">{{ $exam->exam_code }}</li>
            </ol>
        </nav>

        <div class="mt-8 grid items-end gap-10 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-extrabold tracking-wider ring-1 ring-white/20">{{ $exam->exam_code }}</span>
                    @if($vendorLabel)<span class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $vendorLabel }} exam guide</span>@endif
                </div>
                <h1 class="mt-4 text-3xl font-extrabold leading-[1.1] sm:text-5xl">{{ $h1 }}</h1>
                <p class="mt-4 max-w-2xl text-base leading-relaxed text-slate-300 sm:text-lg">{{ $lede }}</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ $exam->questions_url }}" class="inline-flex items-center gap-2 rounded-xl px-6 py-3.5 text-sm font-bold text-white shadow-lg transition hover:opacity-90" style="background: var(--brand);">
                        Practise free questions
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5-5 5M6 12h12"/></svg>
                    </a>
                    <a href="{{ $exam->plans_url }}" class="inline-flex items-center rounded-xl bg-white/10 px-6 py-3.5 text-sm font-bold text-white ring-1 ring-white/25 transition hover:bg-white/15">See plans{{ $fromPrice ? ' from ' . $money($fromPrice) : '' }}</a>
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-3 lg:col-span-5">
                @foreach(array_filter([
                    ['v' => $hBank ? number_format($hBank) : null, 'l' => 'questions in the bank'],
                    ['v' => $hFree ?: null, 'l' => 'free with explanations'],
                    ['v' => $exam->passing_score ? $exam->passing_score . '%' : null, 'l' => 'passing score'],
                    ['v' => $realUpdated ? $realUpdated->format('M Y') : null, 'l' => 'last updated'],
                ], fn ($s) => $s['v']) as $stat)
                    <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10 backdrop-blur">
                        <dd class="text-3xl font-extrabold">{{ $stat['v'] }}</dd>
                        <dt class="mt-1 text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $stat['l'] }}</dt>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
</section>

@elseif($active === 'questions')
<section class="border-b border-slate-200 bg-white">
    <div class="container-custom py-6 lg:py-8">
        <nav aria-label="Breadcrumb" class="text-sm {{ $crumbClass }}">
            <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <li><a href="{{ url('/') }}" class="{{ $crumbHover }}">Home</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ $exam->url }}" class="{{ $crumbHover }}">{{ $exam->exam_code }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="font-semibold {{ $crumbNow }}">Free practice questions</li>
            </ol>
        </nav>
        <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-extrabold" style="background: var(--brand-soft); color: var(--brand-dark);">
                    <span class="h-1.5 w-1.5 rounded-full" style="background: var(--brand);"></span> Practice mode
                </span>
                <h1 class="mt-3 text-2xl font-extrabold leading-tight text-slate-900 sm:text-4xl">{{ $h1 }}</h1>
                <p class="mt-2 text-base leading-relaxed text-slate-600">{{ $lede }}</p>
            </div>
            <a href="{{ $exam->url }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-slate-600 hover:text-slate-900">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 17l-5-5 5-5M18 12H6"/></svg>
                {{ $exam->exam_code }} exam guide
            </a>
        </div>
    </div>
</section>

@else
<section class="relative overflow-hidden border-b border-slate-200 bg-white">
    <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(50% 100% at 50% 0%, color-mix(in srgb, var(--brand) 16%, transparent), transparent 75%);"></div>
    <div class="container-custom relative py-10 text-center lg:py-14">
        <nav aria-label="Breadcrumb" class="text-sm {{ $crumbClass }}">
            <ol class="flex flex-wrap items-center justify-center gap-x-2 gap-y-1">
                <li><a href="{{ url('/') }}" class="{{ $crumbHover }}">Home</a></li>
                <li aria-hidden="true">/</li>
                <li><a href="{{ $exam->url }}" class="{{ $crumbHover }}">{{ $exam->exam_code }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="font-semibold {{ $crumbNow }}">Plans</li>
            </ol>
        </nav>
        <span class="mt-6 inline-flex rounded-full px-3 py-1 text-xs font-extrabold tracking-wider" style="background: var(--brand-soft); color: var(--brand-dark);">{{ $exam->exam_code }} PLANS</span>
        <h1 class="mx-auto mt-4 max-w-3xl text-3xl font-extrabold leading-tight text-slate-900 sm:text-5xl">{{ $h1 }}</h1>
        <p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-slate-600 sm:text-lg">{{ $lede }}</p>
    </div>
</section>
@endif
