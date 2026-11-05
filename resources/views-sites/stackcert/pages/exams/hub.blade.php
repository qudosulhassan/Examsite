@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : ($globalSettings['site_name'] ?? config('app.name'));
    $vendorName = $exam->vendor->name ?? '';
    $code = $exam->exam_code;
    $money = fn ($v) => '$' . number_format((float) $v, ((float) $v == floor((float) $v)) ? 0 : 2);
    $fmtDate = fn ($d) => $d ? $d->format('F j, Y') : null;
    $realUpdated = $exam->last_updated_at ?: $exam->updated_at;

    $pdfPrice = (float) $exam->resolved_price_pdf;
    $enginePrice = (float) $exam->resolved_price_engine;
    $bundlePrice = (float) $exam->effective_bundle_price;
    $priceList = array_filter([$pdfPrice, $enginePrice, $bundlePrice], fn ($p) => $p > 0);
    $lowPrice = $priceList ? min($priceList) : null;
    $highPrice = $priceList ? max($priceList) : null;

    // The guide is built from Microsoft's official study guide for the exam (see ExamGuideContent).
    // $g is null for an exam Microsoft Learn no longer lists; that page stays minimal and out of the index.
    $g = \App\Support\ExamGuideContent::for($exam, (int) $freeCount, (int) $bankCount);
    $examName = $g['official'] ?? trim((string) $exam->exam_name);

    $meta = \App\Support\SeoMeta::guide($code, $examName, $brand);
    $title = $meta['title'];
    $description = $meta['description'];
    $h1 = "{$code} Exam Guide: {$examName}";

    // Opens with a plain definition so search and AI answers can quote it directly.
    if ($g) {
        $areas = collect($g['domains'])->pluck('name')->take(3)->implode(', ');
        $lede = \App\Support\ExamGuideContent::describe($code, $examName, $vendorName ?: 'Microsoft') . " It tests " . count($g['domains']) . " skill areas ({$areas}" . (count($g['domains']) > 3 ? ' and more' : '') . ") and you need {$g['passing']} to pass. This guide covers the skills measured, a {$g['weeks']}-week study plan and FAQs"
            . ($freeCount ? ", with {$freeCount} free practice questions to test yourself." : '.');
    } else {
        $lede = "{$code}: {$examName}. Microsoft Learn does not list a current study guide for this exam, so check Microsoft's official certification pages before you book it.";
    }

    $faqs = $g['faqs'] ?? [];

    $crumbs = [['name' => 'Home', 'url' => url('/')]];
    if (!empty($track)) { $crumbs[] = ['name' => $track['title'], 'url' => route('tracks.show', $track['slug'])]; }
    $crumbs[] = ['name' => $code, 'url' => $exam->url];
@endphp

@section('title', $title)
@section('meta_description', $description)
@section('canonical_url', $exam->url)
@section('og_type', 'article')
@unless(\App\Support\ExamGuideContent::isIndexable((string) $exam->slug))
    @section('robots', 'noindex, follow')
@endunless

@section('seo_tags')
    @include('partials.schema-breadcrumbs', ['crumbs' => $crumbs])
    <script type="application/ld+json">
    {!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => $exam->url . '#webpage',
        'name' => $title,
        'url' => $exam->url,
        'description' => $description,
        'inLanguage' => 'en',
        'dateModified' => $realUpdated ? $realUpdated->toAtomString() : null,
        'isPartOf' => ['@type' => 'WebSite', 'name' => $brand, 'url' => url('/')],
        'about' => ['@type' => 'Thing', 'name' => "{$code} {$examName}"],
        'isBasedOn' => $g['guideUrl'] ?? null,
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @if($faqs)
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]], $faqs),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif
    @if($g && \App\Support\ExamGuideContent::isIndexable((string) $exam->slug))
    <script type="application/ld+json">
    {!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Course',
        'name' => "{$code} {$examName} exam preparation",
        'description' => "A study guide for the {$code} {$examName} exam: the skills measured with their weightings, a {$g['weeks']}-week study plan and FAQs, based on Microsoft's official study guide.",
        'url' => $exam->url,
        'provider' => ['@type' => 'Organization', 'name' => $brand, 'url' => url('/')],
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif
@endsection

@section('content')
@include('partials.exam-header', ['exam' => $exam, 'active' => 'guide', 'h1' => $h1, 'lede' => $lede, 'track' => $track])

<section class="py-10 lg:py-14">
    <div class="container-custom grid grid-cols-1 gap-10 lg:grid-cols-12">

        {{-- ================= MAIN ================= --}}
        <div class="min-w-0 space-y-12 lg:col-span-8">

            {{-- Retirement notice (from Microsoft's own study guide) --}}
            @if(!empty($g['retire']))
                <div class="rounded-2xl border border-amber-300 bg-amber-50 p-5 text-sm leading-relaxed text-amber-900" role="note">
                    <strong class="font-extrabold">{{ $g['retire']['status'] === 'retired' ? 'This exam has been retired.' : 'This exam is retiring.' }}</strong>
                    Microsoft {{ $g['retire']['status'] === 'retired' ? 'retired' : 'retires' }} {{ $code }} on {{ $g['retire']['date'] }}.
                    Check the <a href="{{ $g['guideUrl'] }}" class="font-bold underline" rel="noopener" target="_blank">official study guide</a> for its replacement before you start studying.
                </div>
            @endif

            {{-- At a glance --}}
            <section aria-labelledby="glance">
                <h2 id="glance" class="text-2xl font-extrabold text-slate-900">What is the {{ $code }} exam? At a glance</h2>
                <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                    @foreach(array_filter([
                        'Exam code' => $code,
                        'Official title' => $examName,
                        'Provider' => $vendorName,
                        'Level' => $g['level'] ?? null,
                        'Passing score' => $g['passing'] ?? null,
                        'Skill areas' => $g ? count($g['domains']) . ' domains, ' . $g['skillTotal'] . ' listed skills' : null,
                        'Skills outline updated' => $g['asOf'] ?? null,
                        'Our question bank' => $bankCount ? number_format($bankCount) . ' questions' : null,
                        'Free questions' => $freeCount ? $freeCount . ' with explanations' : null,
                        'Formats' => implode(' and ', array_filter([$exam->is_pdf_available ? 'PDF study guide' : null, $exam->is_engine_available ? 'online test engine' : null])),
                    ]) as $label => $value)
                        <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                            <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</dt>
                            <dd class="mt-1 font-extrabold text-slate-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                <p class="mt-3 text-xs text-slate-500">
                    Skills, weightings and the pass mark come from Microsoft's
                    @if(!empty($g['guideUrl']))<a href="{{ $g['guideUrl'] }}" class="underline" rel="noopener" target="_blank">official {{ $code }} study guide</a>@else official study guide @endif.
                    Microsoft can change an exam, so confirm the current outline, languages and price on its exam page before you book.
                </p>
            </section>

            @if($g)
            {{-- Who it is for --}}
            @if(!empty($g['audience']))
            <section aria-labelledby="audience">
                <h2 id="audience" class="text-2xl font-extrabold text-slate-900">Who should take the {{ $code }} exam?</h2>
                <div class="mt-5 space-y-3 rounded-2xl border border-slate-200 bg-white p-6 text-sm leading-relaxed text-slate-700">
                    <p>Microsoft describes the candidate for {{ $code }} like this:</p>
                    <blockquote class="border-l-4 pl-4 italic text-slate-600" style="border-color: var(--brand);">{{ \Illuminate\Support\Arr::first($g['audience']) }}</blockquote>
                    @foreach(array_slice($g['audience'], 1, 2) as $p)
                        <p>{{ $p }}</p>
                    @endforeach
                    @if(!empty($track))
                        <p>It sits in our <a href="{{ route('tracks.show', $track['slug']) }}" class="font-bold underline" style="color: var(--brand);">{{ $track['title'] }} exams</a> track, where you can compare it with the other exams in the same area.</p>
                    @endif
                </div>
            </section>
            @endif

            {{-- Skills measured --}}
            <section aria-labelledby="skills">
                <h2 id="skills" class="text-2xl font-extrabold text-slate-900">What skills does the {{ $code }} exam measure?</h2>
                <p class="mt-3 text-sm leading-relaxed text-slate-600">
                    The {{ $code }} outline has {{ count($g['domains']) }} domains. Each one is weighted, and the weights tell you where the exam spends its questions.
                    The tasks below are taken from Microsoft's official list{{ !empty($g['asOf']) ? ' (last updated ' . $g['asOf'] . ')' : '' }}.
                </p>
                <div class="mt-5 space-y-4">
                    @foreach($g['domains'] as $domain)
                        <article class="rounded-2xl border border-slate-200 bg-white p-6">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-lg font-extrabold text-slate-900">{{ $domain['name'] }}</h3>
                                <span class="rounded-full px-3 py-1 text-xs font-extrabold" style="background: var(--brand-soft); color: var(--brand-dark);">{{ $domain['weight'] }} of the exam</span>
                            </div>
                            <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $domain['explanation'] }}</p>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                @foreach(array_slice($domain['sections'], 0, 6) as $section)
                                    <div>
                                        <h4 class="text-sm font-extrabold text-slate-800">{{ $section['title'] }}</h4>
                                        <ul class="mt-1.5 list-disc space-y-1 pl-5 text-sm text-slate-600">
                                            @foreach(array_slice($section['skills'], 0, 3) as $skill)
                                                <li>{{ $skill }}</li>
                                            @endforeach
                                        </ul>
                                        @if(count($section['skills']) > 3)
                                            <p class="mt-1 pl-5 text-xs text-slate-500">and {{ count($section['skills']) - 3 }} more in the official guide</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            @if(count($domain['sections']) > 6)
                                <p class="mt-3 text-xs text-slate-500">and {{ count($domain['sections']) - 6 }} more areas in the official study guide</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>

            {{-- Study plan --}}
            <section aria-labelledby="plan">
                <h2 id="plan" class="text-2xl font-extrabold text-slate-900">{{ $code }} study plan: {{ $g['weeks'] }} weeks</h2>
                <p class="mt-3 text-sm leading-relaxed text-slate-600">
                    This plan splits the {{ count($g['domains']) }} domains across the weeks by weighting, and ends with timed practice. Stretch it if you are new to the topics, or shorten it if you work with them every day.
                </p>
                <ol class="mt-5 space-y-3">
                    @foreach($g['plan'] as $step)
                        <li class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-5">
                            <span class="flex h-9 w-9 flex-none items-center justify-center rounded-lg text-sm font-extrabold text-white" style="background: var(--brand);">{{ $step['week'] }}</span>
                            <div>
                                <h3 class="font-extrabold text-slate-900">Week {{ $step['week'] }}: {{ $step['title'] }}</h3>
                                <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $step['text'] }}
                                    @if($loop->last) Start with the <a href="{{ $exam->questions_url }}" class="font-bold underline" style="color: var(--brand);">free {{ $code }} practice questions</a>, then see the <a href="{{ $exam->plans_url }}" class="font-bold underline" style="color: var(--brand);">{{ $code }} practice test plans</a> for the full bank. @endif
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
            @endif

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
            @if($faqs)
            <section aria-labelledby="faq">
                <h2 id="faq" class="text-2xl font-extrabold text-slate-900">{{ $code }} exam FAQs</h2>
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
            @endif

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
