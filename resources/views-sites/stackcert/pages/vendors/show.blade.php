@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : ($globalSettings['site_name'] ?? config('app.name'));
    $vendorName = $vendor->name;
    $groups = \App\Support\ExamTracks::group($exams, $vendor->slug);
    $examCount = $exams->count();
    $money = fn ($v) => '$' . number_format((float) $v, ((float) $v == floor((float) $v)) ? 0 : 2);
    $lowest = $exams->map(fn ($e) => collect([(float) $e->resolved_price_pdf, (float) $e->resolved_price_engine])->filter(fn ($p) => $p > 0)->min())->filter()->min();
    $bank = (int) $exams->sum(fn ($e) => (int) $e->question_count);
    $latest = $exams->map(fn ($e) => $e->last_updated_at ?: $e->updated_at)->filter()->max();

    // Role-based entry points: fundamentals exams are the usual first step.
    $isFundamentals = fn ($e) => (bool) preg_match('/-900$/', (string) $e->exam_code);
    $starters = $exams->filter($isFundamentals)->values()->take(6);

    $meta = \App\Support\SeoMeta::vendor($vendorName, $examCount, $brand);
    $title = $meta['title'];
    $description = $meta['description'];
    $crumbs = [['name' => 'Home', 'url' => url('/')], ['name' => $vendorName . ' exams', 'url' => route('vendors.show', $vendor->slug)]];

    $items = $exams->map(fn ($e) => [
        'id' => $e->id,
        'k' => \App\Support\ExamTracks::forExam((string) $e->exam_code, $vendor->slug)['key'] ?? 'more',
        's' => mb_strtolower($e->exam_code . ' ' . $e->exam_name),
    ])->values();
    $trackIcons = [
        'cloud' => 'M3 15a4 4 0 004 4h9a5 5 0 10-.9-9.9A6 6 0 106 11a4 4 0 00-3 4z',
        'shield' => 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3z',
        'layers' => 'M12 3l9 5-9 5-9-5 9-5zm-9 9l9 5 9-5M3 16l9 5 9-5',
        'bolt' => 'M13 2L4 14h7l-1 8 9-12h-7l1-8z',
        'chart' => 'M4 20V10m6 10V4m6 16v-7m4 7H2',
        'spark' => 'M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3z',
        'grid' => 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z',
    ];
@endphp

@section('title', $title)
@section('meta_description', $description)
@section('canonical_url', route('vendors.show', $vendor->slug))
@section('seo_tags')
    @include('partials.schema-breadcrumbs', ['crumbs' => $crumbs])
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => "{$vendorName} certification practice exams",
        'url' => route('vendors.show', $vendor->slug),
        'mainEntity' => ['@type' => 'ItemList', 'numberOfItems' => $examCount, 'itemListElement' => $exams->values()->map(fn ($e, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'url' => $e->url, 'name' => "{$e->exam_code} {$e->exam_name}"])->all()],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
<div x-data="{ q: '', track: 'all', items: {{ \Illuminate\Support\Js::from($items) }},
        match(id) { const i = this.items.find(x => x.id === id); return !!i && (this.track === 'all' || this.track === i.k) && (this.q.trim() === '' || i.s.includes(this.q.trim().toLowerCase())); },
        count(k) { return this.items.filter(i => i.k === k && this.match(i.id)).length; },
        total() { return this.items.filter(i => this.match(i.id)).length; } }">

{{-- ================= HERO ================= --}}
<section class="relative overflow-hidden bg-slate-900 text-white">
    <div class="pointer-events-none absolute inset-0" style="background: radial-gradient(55% 90% at 85% 0%, color-mix(in srgb, var(--brand) 45%, transparent), transparent 70%), radial-gradient(40% 60% at 0% 100%, color-mix(in srgb, var(--brand-dark) 40%, transparent), transparent 70%);"></div>
    <div class="container-custom relative py-12 lg:py-20">
        <nav aria-label="Breadcrumb" class="text-sm text-slate-400">
            <ol class="flex items-center gap-x-2"><li><a href="{{ url('/') }}" class="hover:text-white">Home</a></li><li aria-hidden="true">/</li><li aria-current="page" class="font-semibold text-white">{{ $vendorName }} exams</li></ol>
        </nav>
        <h1 class="mt-6 max-w-3xl text-4xl font-extrabold leading-[1.08] sm:text-5xl">{{ $vendorName }} certification practice exams</h1>
        <p class="mt-4 max-w-2xl text-base leading-relaxed text-slate-300 sm:text-lg">Find your exam, answer free questions with explanations, then decide if you need the full bank. {{ $examCount }} exams, grouped by the skills they test.</p>

        <div class="mt-8 max-w-2xl">
            <label for="exam-search" class="sr-only">Search {{ $vendorName }} exams</label>
            <div class="flex items-center gap-3 rounded-2xl bg-white p-2 pl-4 text-slate-900 shadow-2xl shadow-black/30 focus-within:ring-2" style="--tw-ring-color: var(--brand);">
                <svg class="h-5 w-5 flex-none text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M20 20l-3.5-3.5"/></svg>
                <input id="exam-search" type="search" x-model="q" placeholder="Search by code or name, e.g. AZ-104 or security" autocomplete="off" class="min-w-0 flex-1 border-0 bg-transparent py-3 text-base focus:outline-none focus:ring-0">
                <span class="hidden flex-none rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600 sm:block"><span x-text="total()">{{ $examCount }}</span> exams</span>
            </div>
        </div>

        <dl class="mt-10 grid max-w-3xl grid-cols-3 gap-3">
            <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10"><dd class="text-2xl font-extrabold sm:text-3xl">{{ $examCount }}</dd><dt class="mt-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">exams covered</dt></div>
            <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10"><dd class="text-2xl font-extrabold sm:text-3xl">{{ number_format($bank) }}</dd><dt class="mt-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">practice questions</dt></div>
            <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/10"><dd class="text-2xl font-extrabold sm:text-3xl">{{ $lowest ? $money($lowest) : '-' }}</dd><dt class="mt-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">plans from</dt></div>
        </dl>
    </div>
</section>

{{-- ================= TRACK FILTER (sticky) ================= --}}
<div class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="container-custom">
        <div class="-mx-1 flex gap-2 overflow-x-auto px-1 py-3" role="tablist" aria-label="Filter by track">
            <button type="button" @click="track = 'all'" class="flex-none whitespace-nowrap rounded-full border px-4 py-2 text-sm font-bold transition" :class="track === 'all' ? 'border-transparent text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'" :style="track === 'all' ? 'background: var(--brand);' : ''">All tracks</button>
            @foreach($groups as $g)
                <button type="button" @click="track = '{{ $g['key'] }}'" class="flex-none whitespace-nowrap rounded-full border px-4 py-2 text-sm font-bold transition" :class="track === '{{ $g['key'] }}' ? 'border-transparent text-white' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'" :style="track === '{{ $g['key'] }}' ? 'background: var(--brand);' : ''">{{ $g['title'] }} <span class="opacity-70">{{ $g['exams']->count() }}</span></button>
            @endforeach
        </div>
    </div>
</div>

{{-- ================= START HERE ================= --}}
@if($starters->isNotEmpty())
<section class="border-b border-slate-200 bg-white py-12" :class="(q.trim() === '' && track === 'all') ? '' : 'hidden'">
    <div class="container-custom">
        <p class="text-xs font-extrabold uppercase tracking-widest" style="color: var(--brand);">New to {{ $vendorName }} certification?</p>
        <h2 class="mt-1 text-2xl font-extrabold text-slate-900">Start with a fundamentals exam</h2>
        <p class="mt-2 max-w-2xl text-slate-600">The 900-series exams test core concepts and need no hands-on job experience, which makes them the usual first step into a track.</p>
        <ul class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($starters as $e)
                <li class="min-w-0"><a href="{{ $e->url }}" class="group flex h-full items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300 hover:bg-white hover:shadow-md">
                    <span class="flex h-12 w-12 flex-none items-center justify-center rounded-xl text-xs font-extrabold" style="background: var(--brand-soft); color: var(--brand-dark);">{{ $e->exam_code }}</span>
                    <span class="min-w-0"><span class="block truncate font-extrabold text-slate-900">{{ $e->exam_name }}</span><span class="text-sm text-slate-500">Exam guide and free questions</span></span>
                </a></li>
            @endforeach
        </ul>
    </div>
</section>
@endif

{{-- ================= EXAM LIST ================= --}}
<section class="py-12 lg:py-16">
    <div class="container-custom space-y-14">
        @foreach($groups as $g)
            <div x-cloak :class="(track === 'all' || track === '{{ $g['key'] }}') && count('{{ $g['key'] }}') > 0 ? '' : 'hidden'" id="track-{{ $g['slug'] ?? $g['key'] }}" class="scroll-mt-24">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <span class="flex h-12 w-12 flex-none items-center justify-center rounded-2xl" style="background: var(--brand-soft); color: var(--brand);">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $trackIcons[$g['icon']] ?? $trackIcons['grid'] }}"/></svg>
                        </span>
                        <div>
                            <h2 class="text-2xl font-extrabold text-slate-900">{{ $g['title'] }}</h2>
                            <p class="text-sm text-slate-600">{{ $g['blurb'] }}</p>
                        </div>
                    </div>
                    @if(Route::has('tracks.show') && !empty($g['slug']))
                        <a href="{{ route('tracks.show', $g['slug']) }}" class="text-sm font-bold hover:underline" style="color: var(--brand);">View the {{ $g['title'] }} track &rarr;</a>
                    @endif
                </div>

                <ul class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($g['exams'] as $e)
                        @php
                            $p = collect([(float) $e->resolved_price_pdf, (float) $e->resolved_price_engine])->filter(fn ($v) => $v > 0)->min();
                            $lvl = $isFundamentals($e) ? 'Fundamentals' : $e->difficulty;
                        @endphp
                        <li x-cloak :class="match({{ $e->id }}) ? '' : 'hidden'" class="flex">
                            <article class="flex w-full flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="rounded-md px-2.5 py-1 text-xs font-extrabold" style="background: var(--brand-soft); color: var(--brand-dark);">{{ $e->exam_code }}</span>
                                    @if($lvl)<span class="text-xs font-bold text-slate-500">{{ $lvl }}</span>@endif
                                </div>
                                <h3 class="mt-3 text-base font-extrabold leading-snug text-slate-900"><a href="{{ $e->url }}" class="hover:underline">{{ $e->exam_name }}</a></h3>
                                <p class="mt-1 text-sm text-slate-500">{{ number_format((int) $e->question_count) }} questions{{ $p ? ' &middot; from ' . $money($p) : '' }}</p>
                                <div class="mt-auto flex items-center justify-between gap-3 pt-5 text-sm font-bold">
                                    <a href="{{ $e->questions_url }}" class="rounded-lg px-3 py-2 text-white transition hover:opacity-90" style="background: var(--brand);">Free questions</a>
                                    <a href="{{ $e->url }}" class="text-slate-700 hover:underline">Exam guide</a>
                                </div>
                            </article>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        <div x-cloak :class="total() === 0 ? '' : 'hidden'" class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <p class="text-lg font-extrabold text-slate-900">No exam matches that search</p>
            <p class="mt-1 text-slate-600">Try the exam code (for example SC-300) or a single word like "security".</p>
            <button type="button" @click="q = ''; track = 'all'" class="mt-4 rounded-xl px-5 py-2.5 text-sm font-bold text-white" style="background: var(--brand);">Show all exams</button>
        </div>
    </div>
</section>

{{-- ================= HOW TO CHOOSE + HONESTY ================= --}}
<section class="border-t border-slate-200 bg-white py-14">
    <div class="container-custom grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <h2 class="text-2xl font-extrabold text-slate-900">How to pick your first exam</h2>
            <p class="mt-3 text-slate-600">Certification paths are role-based, so start from the job you want, not the exam number.</p>
        </div>
        <ol class="grid gap-4 sm:grid-cols-3 lg:col-span-2">
            @foreach([['Choose a track', 'Pick the area you work in or want to move into: cloud, security, data, Power Platform or Microsoft 365.'], ['Start at the right level', 'Fundamentals exams test concepts. Associate and expert exams expect hands-on experience.'], ['Practise before you book', 'Answer the free questions to see which topics need work before you pay for an exam.']] as $n => [$h, $t])
                <li class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-extrabold text-white" style="background: var(--brand);">{{ $n + 1 }}</span>
                    <h3 class="mt-3 font-extrabold text-slate-900">{{ $h }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $t }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

<section class="pb-14">
    <div class="container-custom">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm leading-relaxed text-slate-600">
            <strong class="text-slate-900">Use the official resources too.</strong> {{ $vendorName }} publishes the skills outline for every exam and offers free
            <a href="https://learn.microsoft.com/en-us/credentials/certifications/practice-assessments-for-microsoft-certifications" target="_blank" rel="noopener" class="font-bold underline" style="color: var(--brand);">practice assessments</a>
            on Microsoft Learn. {{ $brand }} is independent and not affiliated with {{ $vendorName }}; our materials are not official exam content.
            @if($latest) Catalog last updated {{ $latest->format('F j, Y') }}. @endif
        </div>
    </div>
</section>
</div>
@endsection
