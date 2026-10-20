@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : ($globalSettings['site_name'] ?? config('app.name'));
    $vendorName = $siteVendor->name ?? 'IT';
    $vendorSlug = $siteVendor->slug ?? null;
    $examCount = collect($siteTracks)->sum(fn ($t) => $t['exams']->count());
    $trackTitles = collect($siteTracks)->reject(fn ($t) => $t['key'] === 'more')->pluck('title')->take(4)->all();
    $vendorUrl = $vendorSlug ? url('/vendors/' . $vendorSlug) : url('/vendors');
    $monthYear = now()->format('F Y');

    $faqs = [
        ['q' => "What is {$brand}?", 'a' => "{$brand} provides practice questions, PDF study guides and a timed test engine for {$vendorName} certification exams. It is an independent exam-preparation site and is not affiliated with {$vendorName}."],
        ['q' => "Which {$vendorName} exams are covered?", 'a' => "The catalog currently has {$examCount} {$vendorName} exams" . (count($trackTitles) ? ', including ' . implode(', ', $trackTitles) : '') . '. New exams are added to their track automatically as they are published.'],
        ['q' => 'What is the difference between the PDF guide and the test engine?', 'a' => 'The PDF guide is a printable study guide you can read on any device. The test engine lets you practise online in timed exam mode or untimed practice mode, with instant scoring and a review of every answer. Many people buy both as a bundle.'],
        ['q' => 'How do I get my material after I buy?', 'a' => 'Access is added to your account straight after payment. Sign in and open your dashboard to download your PDF guides or start the test engine.'],
        ['q' => 'Can I try before I buy?', 'a' => 'Yes. Every exam page includes free practice questions you can answer straight away, and many exams also offer a free PDF sample and a demo of the test engine.'],
        ['q' => "Are these official {$vendorName} materials?", 'a' => "No. {$brand} is not produced, endorsed or approved by {$vendorName}. The practice materials are not official exam content, so always use the vendor's own exam guide to confirm the skills measured."],
    ];
@endphp

@section('title', "{$vendorName} Certification Practice Exams & Study Guides | {$brand}")
@section('meta_description', "Practice for {$vendorName} certification exams with study guides, free practice questions and a timed test engine. {$examCount} exams across " . (count($trackTitles) ? implode(', ', $trackTitles) : 'every track') . '.')

@section('seo_tags')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => $brand,
    'url' => url('/'),
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => url('/search') . '?q={search_term_string}',
        'query-input' => 'required name=search_term_string',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(fn ($f) => [
        '@type' => 'Question',
        'name' => $f['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
    ], $faqs),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection

@section('content')

{{-- ============ HERO ============ --}}
<section class="relative overflow-hidden border-b border-slate-200 bg-white">
    <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(60% 80% at 85% 0%, color-mix(in srgb, var(--brand) 14%, transparent), transparent 70%), radial-gradient(40% 60% at 0% 100%, color-mix(in srgb, var(--brand-dark) 10%, transparent), transparent 70%);"></div>
    <div class="container-custom relative grid grid-cols-1 items-center gap-12 py-14 lg:grid-cols-12 lg:py-20">
        <div class="min-w-0 lg:col-span-7">
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-bold text-slate-600 shadow-sm">
                <span class="h-2 w-2 rounded-full" style="background: var(--brand);"></span>
                {{ $examCount }} {{ $vendorName }} exams &middot; updated {{ $monthYear }}
            </span>
            <h1 class="mt-5 text-4xl font-extrabold leading-[1.08] text-slate-900 sm:text-5xl lg:text-[56px]">
                Practice for {{ $vendorName }} certification exams with confidence
            </h1>
            <p class="mt-5 max-w-2xl text-lg leading-relaxed text-slate-600">
                Study guides in PDF and a timed test engine, organised by track@if(count($trackTitles)) &mdash; {{ implode(', ', $trackTitles) }} and more@endif.
                Start with free practice questions on every exam page.
            </p>

            <form action="{{ url('/search') }}" method="GET" class="mt-8 flex w-full max-w-xl items-center gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-lg shadow-slate-200/60 focus-within:ring-2" style="--tw-ring-color: var(--brand);" role="search">
                <svg class="ml-3 h-5 w-5 flex-none text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                <input type="search" name="q" placeholder="Search exams, e.g. AZ-900" class="min-w-0 flex-1 border-0 bg-transparent px-2 py-3 text-base text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-0" aria-label="Search exams">
                <button type="submit" class="flex-none rounded-xl px-5 py-3 text-sm font-bold text-white transition hover:opacity-90" style="background: var(--brand);">Search</button>
            </form>

            @if($latestExams->isNotEmpty())
                <div class="mt-5 flex flex-wrap items-center gap-2 text-sm">
                    <span class="font-semibold text-slate-500">Popular:</span>
                    @foreach($latestExams->take(6) as $chip)
                        <a href="{{ $chip->url }}" class="rounded-full border border-slate-200 bg-white px-3 py-1 font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">{{ $chip->exam_code }}</a>
                    @endforeach
                </div>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ $vendorUrl }}" class="rounded-xl px-6 py-3 text-sm font-bold text-white shadow-md transition hover:opacity-90" style="background: var(--brand);">Browse all {{ $examCount }} exams</a>
                <a href="{{ url('/free-demo') }}" class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-800 transition hover:bg-slate-50">Get a free sample</a>
            </div>
        </div>

        <div class="min-w-0 lg:col-span-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70 sm:p-8">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">What's inside</p>
                <div class="mt-5 grid grid-cols-2 gap-4">
                    <div class="rounded-2xl p-5" style="background: var(--brand-soft);">
                        <p class="text-3xl font-extrabold text-slate-900">{{ number_format($examCount) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-600">{{ $vendorName }} exams</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-5">
                        <p class="text-3xl font-extrabold text-slate-900">{{ number_format($siteQuestionCount) }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-600">practice questions</p>
                    </div>
                </div>
                <ul class="mt-6 space-y-3 text-sm text-slate-700">
                    @foreach([
                        ['PDF study guide', 'Read on any device or print it'],
                        ['Timed test engine', 'Exam mode and practice mode with instant scoring'],
                        ['Free updates', '3 months included with every purchase'],
                    ] as [$t, $d])
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-5 w-5 flex-none items-center justify-center rounded-full text-white" style="background: var(--brand);">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span><span class="font-bold text-slate-900">{{ $t }}</span> &mdash; {{ $d }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- ============ TRACKS ============ --}}
@if(count($siteTracks))
<section id="tracks" class="scroll-mt-24 py-16 lg:py-20">
    <div class="container-custom">
        <div class="max-w-2xl">
            <p class="text-xs font-bold uppercase tracking-widest" style="color: var(--brand);">Exam tracks</p>
            <h2 class="mt-2 text-3xl font-extrabold text-slate-900 sm:text-4xl">Find your path</h2>
            <p class="mt-3 text-slate-600">Exams are grouped by the area they certify. Pick a track to see every exam in it.</p>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($siteTracks as $track)
                @php
                    $searchPrefix = $track['prefixes'][0] ?? null;
                    $trackUrl = \Illuminate\Support\Facades\Route::has('tracks.show') && !empty($track['slug'])
                        ? route('tracks.show', $track['slug'])
                        : ($searchPrefix ? url('/search') . '?q=' . urlencode($searchPrefix . '-') : $vendorUrl);
                @endphp
                <a href="{{ $trackUrl }}" class="group flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-lg">
                    <div class="flex items-start justify-between">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl" style="background: var(--brand-soft); color: var(--brand);">
                            @switch($track['icon'])
                                @case('cloud') <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 15a4 4 0 004 4h9a5 5 0 10-.9-9.9A6 6 0 003 15z"/></svg> @break
                                @case('shield') <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l8 3v6c0 4.5-3.2 8.2-8 9-4.8-.8-8-4.5-8-9V6l8-3z"/></svg> @break
                                @case('layers') <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l9 5-9 5-9-5 9-5zM3 13l9 5 9-5"/></svg> @break
                                @case('bolt') <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 3L5 14h6l-1 7 8-11h-6l1-7z"/></svg> @break
                                @case('chart') <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 20V10m6 10V4m6 16v-7m4 7H2"/></svg> @break
                                @case('spark') <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3z"/></svg> @break
                                @default <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h7v7H4V6zm9 0h7v7h-7V6zM4 15h7v5H4v-5zm9 0h7v5h-7v-5z"/></svg>
                            @endswitch
                        </span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $track['exams']->count() }} {{ \Illuminate\Support\Str::plural('exam', $track['exams']->count()) }}</span>
                    </div>
                    <h3 class="mt-5 text-lg font-extrabold text-slate-900">{{ $track['title'] }}</h3>
                    <p class="mt-1.5 flex-1 text-sm leading-relaxed text-slate-600">{{ $track['blurb'] }}</p>
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @foreach($track['exams']->take(4) as $ex)
                            <span class="rounded-md bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">{{ $ex->exam_code }}</span>
                        @endforeach
                        @if($track['exams']->count() > 4)
                            <span class="rounded-md px-2 py-1 text-xs font-semibold text-slate-500">+{{ $track['exams']->count() - 4 }} more</span>
                        @endif
                    </div>
                    <span class="mt-5 inline-flex items-center gap-1 text-sm font-bold" style="color: var(--brand);">
                        View exams <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ RECENTLY UPDATED ============ --}}
@if($latestExams->isNotEmpty())
<section class="border-y border-slate-200 bg-white py-16 lg:py-20">
    <div class="container-custom">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest" style="color: var(--brand);">Recently updated</p>
                <h2 class="mt-2 text-3xl font-extrabold text-slate-900 sm:text-4xl">Fresh from the catalog</h2>
            </div>
            <a href="{{ $vendorUrl }}" class="text-sm font-bold" style="color: var(--brand);">See all {{ $examCount }} exams &rarr;</a>
        </div>

        <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200">
            <ul class="divide-y divide-slate-200">
                @foreach($latestExams as $exam)
                    @php
                        $prices = array_filter([(float) $exam->resolved_price_pdf, (float) $exam->resolved_price_engine], fn ($p) => $p > 0);
                        $fromPrice = $prices ? min($prices) : null;
                    @endphp
                    <li>
                        <a href="{{ $exam->url }}" class="flex flex-wrap items-center gap-x-6 gap-y-2 bg-white px-5 py-4 transition hover:bg-slate-50 sm:px-6">
                            <span class="w-24 flex-none rounded-lg px-2.5 py-1 text-center text-sm font-extrabold" style="background: var(--brand-soft); color: var(--brand-dark);">{{ $exam->exam_code }}</span>
                            <span class="min-w-0 flex-1 basis-60">
                                <span class="block truncate font-bold text-slate-900">{{ $exam->exam_name }}</span>
                                <span class="block text-xs text-slate-500">{{ $exam->question_count }} questions &middot; updated {{ $exam->display_last_updated->format('M j, Y') }}</span>
                            </span>
                            @if($fromPrice)
                                <span class="text-sm text-slate-500">from <span class="text-base font-extrabold text-slate-900">${{ number_format($fromPrice, $fromPrice == floor($fromPrice) ? 0 : 2) }}</span></span>
                            @endif
                            <span class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold text-slate-800">View</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
@endif

{{-- ============ BUNDLE ============ --}}
@if($siteBundle)
<section class="py-16 lg:py-20">
    <div class="container-custom">
        <div class="relative overflow-hidden rounded-3xl p-8 text-white sm:p-12" style="background: linear-gradient(120deg, var(--brand-dark), var(--brand));">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative grid grid-cols-1 items-center gap-8 lg:grid-cols-12">
                <div class="lg:col-span-8">
                    <p class="text-xs font-bold uppercase tracking-widest text-white/70">All-access bundle</p>
                    <h2 class="mt-2 text-3xl font-extrabold sm:text-4xl">{{ $siteBundle->name }}</h2>
                    <p class="mt-3 max-w-2xl text-white/85">One purchase for {{ $siteBundle->includes_pdf ? 'every PDF study guide' : '' }}{{ $siteBundle->includes_pdf && $siteBundle->includes_te ? ' and ' : '' }}{{ $siteBundle->includes_te ? 'the full test engine' : '' }} across the {{ $vendorName }} catalog.</p>
                </div>
                <div class="lg:col-span-4 lg:text-right">
                    @if((float) $siteBundle->price_lifetime > 0)
                        <p class="text-4xl font-extrabold">${{ number_format((float) $siteBundle->price_lifetime, 2) }}</p>
                        <p class="mt-1 text-sm text-white/70">one-time price</p>
                    @endif
                    <a href="{{ $vendorUrl }}" class="mt-4 inline-block rounded-xl bg-white px-6 py-3 text-sm font-bold text-slate-900 shadow-md transition hover:bg-slate-100">See what's included</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ============ HOW IT WORKS ============ --}}
<section class="border-t border-slate-200 bg-white py-16 lg:py-20">
    <div class="container-custom">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-xs font-bold uppercase tracking-widest" style="color: var(--brand);">How it works</p>
            <h2 class="mt-2 text-3xl font-extrabold text-slate-900 sm:text-4xl">From search to study session in minutes</h2>
        </div>
        <ol class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach([
                ['Pick your exam', 'Search by code or browse a track, then try the free practice questions on the exam page.'],
                ['Choose a format', 'Get the PDF study guide, the timed test engine, or both as a bundle, and pay securely.'],
                ['Study and review', 'Open your dashboard to download guides or start a practice session and review every answer.'],
            ] as $i => [$title, $text])
                <li class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl text-lg font-extrabold text-white" style="background: var(--brand);">{{ $i + 1 }}</span>
                    <h3 class="mt-4 text-lg font-extrabold text-slate-900">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- ============ FAQ ============ --}}
<section class="py-16 lg:py-20">
    <div class="container-custom max-w-3xl">
        <div class="text-center">
            <p class="text-xs font-bold uppercase tracking-widest" style="color: var(--brand);">FAQ</p>
            <h2 class="mt-2 text-3xl font-extrabold text-slate-900 sm:text-4xl">Questions, answered</h2>
        </div>
        <div class="mt-10 space-y-3" x-data="{ open: 0 }">
            @foreach($faqs as $i => $faq)
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left" :aria-expanded="open === {{ $i }}">
                        <span class="font-bold text-slate-900">{{ $faq['q'] }}</span>
                        <svg class="h-5 w-5 flex-none text-slate-400 transition" :class="open === {{ $i }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-cloak :class="open === {{ $i }} ? '' : 'hidden'" class="border-t border-slate-100 px-5 py-4 text-sm leading-relaxed text-slate-600">{{ $faq['a'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ CLOSING CTA ============ --}}
<section class="pb-16 lg:pb-20">
    <div class="container-custom">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12">
            <h2 class="text-3xl font-extrabold text-slate-900">Ready to start practising?</h2>
            <p class="mx-auto mt-3 max-w-xl text-slate-600">Open any exam and answer the free practice questions first. No account needed.</p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ $vendorUrl }}" class="rounded-xl px-6 py-3 text-sm font-bold text-white shadow-md transition hover:opacity-90" style="background: var(--brand);">Browse {{ $vendorName }} exams</a>
                <a href="{{ url('/contact') }}" class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-800 transition hover:bg-slate-50">Talk to us</a>
            </div>
        </div>
    </div>
</section>
@endsection
