@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : ($globalSettings['site_name'] ?? config('app.name'));
    $vendorName = $exam->vendor->name ?? '';
    $code = $exam->exam_code;
    $examName = trim((string) $exam->exam_name);
    $money = fn ($v) => '$' . number_format((float) $v, ((float) $v == floor((float) $v)) ? 0 : 2);
    $fmtDate = fn ($d) => $d ? $d->format('F j, Y') : null;
    $realUpdated = $exam->last_updated_at ?: $exam->updated_at;

    $pdfPrice = (float) $exam->resolved_price_pdf;
    $enginePrice = (float) $exam->resolved_price_engine;
    $bundlePrice = (float) $exam->effective_bundle_price;
    $priceList = array_filter([$pdfPrice, $enginePrice, $bundlePrice], fn ($p) => $p > 0);
    $lowPrice = $priceList ? min($priceList) : null;
    $highPrice = $priceList ? max($priceList) : null;

    $title = "{$code} {$examName} Exam Guide and Practice | {$brand}";
    if (mb_strlen($title) > 62) {
        $title = "{$code} Exam Guide: Skills, Format and Practice | {$brand}";
    }
    $h1 = "{$code}: {$examName} exam guide";
    $lede = "What the {$code} exam covers, how to prepare, and where to practise with " . ($freeCount ? "{$freeCount} free questions with explanations" : 'practice questions') . ($bankCount > $freeCount ? " and a bank of {$bankCount}." : '.');
    $description = \Illuminate\Support\Str::limit("{$code} {$examName}: exam overview, topics covered, how to prepare, and practice options" . ($freeCount ? " including {$freeCount} free questions with explanations" : '') . '.', 158);

    // Topics: strings (plain list) or arrays with name/description/percentage (weights)
    $rawTopics = $exam->resolved_topics;
    $topics = collect($rawTopics)->map(function ($t) {
        return is_array($t)
            ? ['name' => (string) ($t['name'] ?? ''), 'description' => (string) ($t['description'] ?? ''), 'percentage' => trim((string) ($t['percentage'] ?? ''))]
            : ['name' => (string) $t, 'description' => '', 'percentage' => ''];
    })->filter(fn ($t) => $t['name'] !== '')->values();
    $hasWeights = $topics->contains(fn ($t) => $t['percentage'] !== '');

    $faqs = collect($exam->resolved_faqs)->filter(fn ($f) => is_array($f) && !empty($f['question']) && !empty($f['answer']))
        ->map(fn ($f) => ['q' => strip_tags((string) $f['question']), 'a' => strip_tags((string) $f['answer'])])->values()->all();
    $standardFaqs = [
        ['q' => "Are the free {$code} questions really free?", 'a' => "Yes. You can answer the free questions on the free practice questions page without an account, check each answer, and read the explanation."],
        ['q' => "How is this different from Microsoft's own practice assessment?", 'a' => "Microsoft offers free practice assessments for many exams on Microsoft Learn, and you should use them. We add a larger question bank, a timed exam mode, per-topic review and a printable PDF. We are independent of {$vendorName} and our materials are not official exam content."],
        ['q' => "How should I use these materials for {$code}?", 'a' => "Start with the free questions to find your weak areas, study those topics from the official skills outline, then take timed practice sessions and review every explanation."],
    ];
    $faqs = array_slice(array_merge($faqs, $standardFaqs), 0, 8);

    $crumbs = [['name' => 'Home', 'url' => url('/')]];
    if (!empty($track)) { $crumbs[] = ['name' => $track['title'], 'url' => route('tracks.show', $track['slug'])]; }
    $crumbs[] = ['name' => $code, 'url' => $exam->url];

    $contentHtml = $exam->resolved_article_content;
@endphp

@section('title', $title)
@section('meta_description', $description)
@section('canonical_url', $exam->url)
@section('og_type', 'product')
@section('styles')
    @include('partials.rich-content-styles')
@endsection

@section('seo_tags')
    @include('partials.schema-breadcrumbs', ['crumbs' => $crumbs])
    <script type="application/ld+json">
    {!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => "{$code} {$examName} practice questions and study guide",
        'description' => $description,
        'sku' => $code,
        'url' => $exam->url,
        'brand' => ['@type' => 'Brand', 'name' => $brand],
        'offers' => $lowPrice ? [
            '@type' => 'AggregateOffer',
            'priceCurrency' => 'USD',
            'lowPrice' => number_format($lowPrice, 2, '.', ''),
            'highPrice' => number_format($highPrice, 2, '.', ''),
            'offerCount' => count($priceList),
            'availability' => 'https://schema.org/InStock',
            'url' => $exam->plans_url,
        ] : null,
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
@include('partials.exam-header', ['exam' => $exam, 'active' => 'guide', 'h1' => $h1, 'lede' => $lede, 'track' => $track])

<section class="py-10 lg:py-14">
    <div class="container-custom grid grid-cols-1 gap-10 lg:grid-cols-12">

        {{-- ================= MAIN ================= --}}
        <div class="min-w-0 space-y-12 lg:col-span-8">

            {{-- At a glance --}}
            <section aria-labelledby="glance">
                <h2 id="glance" class="text-2xl font-extrabold text-slate-900">{{ $code }} at a glance</h2>
                <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                    @foreach(array_filter([
                        'Exam code' => $code,
                        'Exam title' => $examName,
                        'Provider' => $vendorName,
                        'Certification' => $exam->certifications->first()->name ?? null,
                        'Level' => $exam->difficulty,
                        'Passing score' => $exam->passing_score ? $exam->passing_score . '%' : null,
                        'Our question bank' => $bankCount ? number_format($bankCount) . ' questions' : null,
                        'Free questions' => $freeCount ? $freeCount . ' with explanations' : null,
                        'Formats' => implode(' and ', array_filter([$exam->is_pdf_available ? 'PDF study guide' : null, $exam->is_engine_available ? 'online test engine' : null])),
                        'Last updated' => $fmtDate($realUpdated),
                    ]) as $label => $value)
                        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</dt>
                            <dd class="mt-1 font-extrabold text-slate-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                <p class="mt-3 text-xs text-slate-500">Always confirm the current skills outline, languages and price on the provider's official exam page before you book.</p>
            </section>

            {{-- Topics / skills --}}
            @if($topics->isNotEmpty())
            <section aria-labelledby="topics">
                <h2 id="topics" class="text-2xl font-extrabold text-slate-900">{{ $hasWeights ? 'Skills measured' : 'Topics covered in our practice questions' }}</h2>
                <ul class="mt-5 space-y-3">
                    @foreach($topics as $topic)
                        <li class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-base font-extrabold text-slate-900">{{ $topic['name'] }}</h3>
                                @if($topic['percentage'] !== '')
                                    <span class="rounded-full px-3 py-1 text-xs font-extrabold" style="background: var(--brand-soft); color: var(--brand-dark);">{{ $topic['percentage'] }}</span>
                                @endif
                            </div>
                            @if($topic['description'] !== '')
                                <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $topic['description'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
            @endif

            {{-- Study guide text written for this exam --}}
            @if(!empty($contentHtml))
            <section aria-labelledby="study-guide">
                <h2 id="study-guide" class="text-2xl font-extrabold text-slate-900">{{ $code }} study guide</h2>
                <div class="blog-content-body prose prose-slate mt-5 max-w-none rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">{!! $contentHtml !!}</div>
            </section>
            @endif

            {{-- How to prepare --}}
            <section aria-labelledby="prepare">
                <h2 id="prepare" class="text-2xl font-extrabold text-slate-900">How to prepare for {{ $code }}</h2>
                <ol class="mt-5 grid gap-4 sm:grid-cols-3">
                    <li class="rounded-2xl border border-slate-200 bg-white p-5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-extrabold text-white" style="background: var(--brand);">1</span>
                        <h3 class="mt-3 font-extrabold text-slate-900">Find your gaps</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-600">Answer the <a href="{{ $exam->questions_url }}" class="font-bold underline" style="color: var(--brand);">free {{ $code }} practice questions</a> and note the topics you miss.</p>
                    </li>
                    <li class="rounded-2xl border border-slate-200 bg-white p-5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-extrabold text-white" style="background: var(--brand);">2</span>
                        <h3 class="mt-3 font-extrabold text-slate-900">Study the outline</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-600">Work through the official skills outline and try the tasks hands-on. Microsoft also provides <a href="https://learn.microsoft.com/en-us/credentials/certifications/practice-assessments-for-microsoft-certifications" class="font-bold underline" style="color: var(--brand);" rel="noopener" target="_blank">free practice assessments</a> for many exams.</p>
                    </li>
                    <li class="rounded-2xl border border-slate-200 bg-white p-5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg text-sm font-extrabold text-white" style="background: var(--brand);">3</span>
                        <h3 class="mt-3 font-extrabold text-slate-900">Practise under time</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-600">Take timed sessions and review every explanation. See the <a href="{{ $exam->plans_url }}" class="font-bold underline" style="color: var(--brand);">{{ $code }} plans</a> for the full bank.</p>
                    </li>
                </ol>
            </section>

            {{-- Reviews (only genuine, approved ones are ever shown) --}}
            @if($reviews->isNotEmpty())
            <section aria-labelledby="reviews">
                <h2 id="reviews" class="text-2xl font-extrabold text-slate-900">What candidates say</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach($reviews->take(4) as $review)
                        <figure class="rounded-2xl border border-slate-200 bg-white p-5">
                            <p class="text-sm font-bold" style="color: var(--brand);" aria-label="{{ $review->rating }} out of 5">{{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', max(0, 5 - (int) $review->rating)) }}</p>
                            <blockquote class="mt-2 text-sm leading-relaxed text-slate-700">{{ $review->comment }}</blockquote>
                            <figcaption class="mt-3 text-xs font-semibold text-slate-500">{{ $review->reviewer_name ?: ($review->user->name ?? 'Verified buyer') }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- FAQ --}}
            <section aria-labelledby="faq">
                <h2 id="faq" class="text-2xl font-extrabold text-slate-900">{{ $code }} questions answered</h2>
                <div class="mt-5 space-y-3" x-data="{ open: 0 }">
                    @foreach($faqs as $i => $faq)
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            <h3>
                                <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left" :aria-expanded="open === {{ $i }}">
                                    <span class="font-bold text-slate-900">{{ $faq['q'] }}</span>
                                    <svg class="h-5 w-5 flex-none text-slate-400 transition" :class="open === {{ $i }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </h3>
                            <div x-cloak :class="open === {{ $i }} ? '' : 'hidden'" class="border-t border-slate-100 px-5 py-4 text-sm leading-relaxed text-slate-600">{{ $faq['a'] }}</div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Related --}}
            @if($related->isNotEmpty())
            <section aria-labelledby="related">
                <h2 id="related" class="text-2xl font-extrabold text-slate-900">Related exams</h2>
                <ul class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach($related as $other)
                        <li>
                            <a href="{{ $other->url }}" class="block h-full rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-slate-300 hover:shadow-md">
                                <span class="text-xs font-extrabold" style="color: var(--brand-dark);">{{ $other->exam_code }}</span>
                                <span class="mt-1 block font-extrabold text-slate-900">{{ $other->exam_name }}</span>
                                <span class="mt-2 block text-sm text-slate-600">{{ $other->exam_code }} exam guide and practice questions</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
            @endif
        </div>

        {{-- ================= SIDEBAR ================= --}}
        <aside class="lg:col-span-4">
            <div class="space-y-4 lg:sticky lg:top-24">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70">
                    <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Start practising</p>
                    <a href="{{ $exam->questions_url }}" class="mt-4 block rounded-xl px-5 py-3.5 text-center text-sm font-bold text-white shadow-md transition hover:opacity-90" style="background: var(--brand);">
                        Try {{ $freeCount ?: 'the' }} free questions
                    </a>
                    <a href="{{ $exam->plans_url }}" class="mt-3 block rounded-xl border border-slate-300 px-5 py-3.5 text-center text-sm font-bold text-slate-800 transition hover:bg-slate-50">
                        See plans{{ $lowPrice ? ' from ' . $money($lowPrice) : '' }}
                    </a>
                    <ul class="mt-5 space-y-2.5 text-sm text-slate-600">
                        @if($exam->is_pdf_available)<li class="flex gap-2"><span style="color: var(--brand);">&#10003;</span> PDF study guide</li>@endif
                        @if($exam->is_engine_available)<li class="flex gap-2"><span style="color: var(--brand);">&#10003;</span> Timed online test engine</li>@endif
                        <li class="flex gap-2"><span style="color: var(--brand);">&#10003;</span> Explanations for every answer</li>
                        <li class="flex gap-2"><span style="color: var(--brand);">&#10003;</span> 3 months of updates included</li>
                    </ul>
                </div>
                <p class="px-1 text-xs leading-relaxed text-slate-500">Independent preparation material, not produced or endorsed by {{ $vendorName }}.</p>
            </div>
        </aside>
    </div>
</section>

{{-- Sticky bar on phones --}}
<div class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 p-3 shadow-[0_-4px_16px_rgba(15,23,42,0.08)] backdrop-blur lg:hidden">
    <div class="mx-auto flex max-w-xl gap-2">
        <a href="{{ $exam->questions_url }}" class="flex-1 rounded-xl px-4 py-3 text-center text-sm font-bold text-white" style="background: var(--brand);">Free questions</a>
        <a href="{{ $exam->plans_url }}" class="flex-1 rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-bold text-slate-800">Plans{{ $lowPrice ? ' ' . $money($lowPrice) . '+' : '' }}</a>
    </div>
</div>
<div class="h-16 lg:hidden"></div>
@endsection
