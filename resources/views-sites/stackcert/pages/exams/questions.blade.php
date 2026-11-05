@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : ($globalSettings['site_name'] ?? config('app.name'));
    $vendorName = $exam->vendor->name ?? '';
    $code = $exam->exam_code;
    $examName = trim((string) $exam->exam_name);
    $money = fn ($v) => '$' . number_format((float) $v, ((float) $v == floor((float) $v)) ? 0 : 2);
    $prices = array_filter([(float) $exam->resolved_price_pdf, (float) $exam->resolved_price_engine, (float) $exam->effective_bundle_price], fn ($p) => $p > 0);
    $lowPrice = $prices ? min($prices) : null;

    $baseUrl = $exam->questions_url;
    $pageUrl = fn (int $n) => $n > 1 ? $baseUrl . '?page=' . $n : $baseUrl;
    $lastNumber = $firstNumber + $questions->count() - 1;

    $title = collect(["Free {$code} Practice Questions with Explanations | {$brand}", "Free {$code} Practice Questions and Answers | {$brand}"])->first(fn ($t) => mb_strlen($t) <= 60) ?? "Free {$code} Practice Questions | {$brand}";
    if ($page > 1) { $title = "Free {$code} Practice Questions, Page {$page} | {$brand}"; }
    $h1 = "Free {$code} practice questions";
    $description = \Illuminate\Support\Str::limit("Answer {$freeCount} free {$code} ({$examName}) practice questions and read an explanation for each one" . ($bankCount > $freeCount ? ". The full bank has {$bankCount} questions." : '.'), 158);
    $lede = "{$freeCount} free questions for the {$examName} exam. Choose an answer, check it, and read why it is right. No account needed.";

    $topicTags = $questions->pluck('topic')->filter()->unique()->values();

    $crumbs = [['name' => 'Home', 'url' => url('/')]];
    if (!empty($track)) { $crumbs[] = ['name' => $track['title'], 'url' => route('tracks.show', $track['slug'])]; }
    $crumbs[] = ['name' => $code, 'url' => $exam->url];
    $crumbs[] = ['name' => 'Free practice questions', 'url' => $baseUrl];

    $nextExam = $related->first();
@endphp

@section('title', $title)
@section('meta_description', $description)
@section('canonical_url', $pageUrl($page))
@if(!$freeCount)
    {{-- Nothing to show yet: keep the page out of search results until free questions are added --}}
    @section('robots', 'noindex, follow')
@endif
@section('seo_tags')
    @include('partials.schema-breadcrumbs', ['crumbs' => $crumbs])
@endsection

@section('content')
@include('partials.exam-header', ['exam' => $exam, 'active' => 'questions', 'h1' => $h1, 'lede' => $lede, 'track' => $track])

<section class="py-10 lg:py-14">
    <div class="container-custom grid grid-cols-1 gap-10 lg:grid-cols-12">

        <div class="min-w-0 lg:col-span-8">
            @if($questions->isEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center">
                    <h2 class="text-xl font-extrabold text-slate-900">Free questions are on the way</h2>
                    <p class="mt-2 text-slate-600">We have not published free questions for {{ $code }} yet. The exam guide and the full plans are available now.</p>
                    <a href="{{ $exam->url }}" class="mt-5 inline-block rounded-xl px-5 py-3 text-sm font-bold text-white" style="background: var(--brand);">Read the {{ $code }} guide</a>
                </div>
            @else
                <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-slate-500">
                    <p>Showing questions <strong class="text-slate-800">{{ $firstNumber }}-{{ $lastNumber }}</strong> of <strong class="text-slate-800">{{ $freeCount }}</strong> free questions</p>
                    @if($topicTags->isNotEmpty())
                        <p class="flex flex-wrap items-center gap-1.5">
                            <span class="font-semibold">Topics on this page:</span>
                            @foreach($topicTags as $tag)
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">{{ $tag }}</span>
                            @endforeach
                        </p>
                    @endif
                </div>

                <div class="mt-5 space-y-6">
                    @foreach($questions as $i => $question)
                        @include('partials.question-card', ['question' => $question, 'number' => $firstNumber + $i])
                    @endforeach
                </div>

                {{-- Pagination: plain links so every page can be crawled --}}
                @if($lastPage > 1)
                    <nav aria-label="Question pages" class="mt-8 flex items-center justify-between gap-3">
                        @if($page > 1)
                            <a href="{{ $pageUrl($page - 1) }}" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-800 hover:bg-slate-50">&larr; Previous questions</a>
                        @else <span></span> @endif
                        <span class="text-sm font-semibold text-slate-500">Page {{ $page }} of {{ $lastPage }}</span>
                        @if($page < $lastPage)
                            <a href="{{ $pageUrl($page + 1) }}" class="rounded-xl px-5 py-3 text-sm font-bold text-white" style="background: var(--brand);">Next questions &rarr;</a>
                        @else <span></span> @endif
                    </nav>
                @endif

                {{-- One calm call to action at the end of the free set --}}
                @if($page === $lastPage)
                <div class="mt-10 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <h2 class="text-xl font-extrabold text-slate-900">Want more than {{ $freeCount }} questions?</h2>
                    <p class="mt-2 text-slate-600">
                        The full {{ $code }} bank{{ $bankCount > $freeCount ? ' has ' . number_format($bankCount) . ' questions' : '' }} and adds a timed exam mode,
                        per-topic review and a printable PDF. Plans start {{ $lowPrice ? 'from ' . $money($lowPrice) : 'here' }}.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <a href="{{ $exam->plans_url }}" class="rounded-xl px-5 py-3 text-sm font-bold text-white shadow-md hover:opacity-90" style="background: var(--brand);">See {{ $code }} plans</a>
                        <a href="{{ $exam->url }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-800 hover:bg-slate-50">Back to the {{ $code }} guide</a>
                    </div>
                </div>
                @endif
            @endif
        </div>

        <aside class="lg:col-span-4">
            <div class="space-y-4 lg:sticky lg:top-24">
                @if($questions->isNotEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Jump to question</p>
                    <div class="mt-3 grid grid-cols-5 gap-2">
                        @foreach($questions as $i => $q)
                            <a href="#question-{{ $firstNumber + $i }}" class="flex h-10 items-center justify-center rounded-lg border border-slate-200 text-sm font-bold text-slate-700 transition hover:text-white" onmouseover="this.style.background='var(--brand)'" onmouseout="this.style.background=''">{{ $firstNumber + $i }}</a>
                        @endforeach
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Page {{ $page }} of {{ $lastPage }} &middot; {{ $freeCount }} free questions</p>
                </div>
                @endif
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500">How to use this page</p>
                    <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm leading-relaxed text-slate-600">
                        <li>Pick an answer without looking at the explanation.</li>
                        <li>Check it, then read why the right option is right.</li>
                        <li>Note the topic of every miss and study it before the next set.</li>
                    </ol>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-extrabold uppercase tracking-widest text-slate-500">Also for {{ $code }}</p>
                    <ul class="mt-3 space-y-2 text-sm font-bold">
                        <li><a href="{{ $exam->url }}" class="hover:underline" style="color: var(--brand);">{{ $code }} exam guide</a></li>
                        <li><a href="{{ $exam->plans_url }}" class="hover:underline" style="color: var(--brand);">{{ $code }} practice test plans</a></li>
                        @if($nextExam)
                            <li><a href="{{ $nextExam->questions_url }}" class="hover:underline" style="color: var(--brand);">Free {{ $nextExam->exam_code }} practice questions</a></li>
                        @endif
                    </ul>
                </div>
                <p class="px-1 text-xs leading-relaxed text-slate-500">These are independent practice questions. They are not produced or approved by {{ $vendorName }} and are not official exam content.</p>
            </div>
        </aside>
    </div>
</section>
@endsection
