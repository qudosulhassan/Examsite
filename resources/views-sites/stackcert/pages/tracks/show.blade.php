@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : ($globalSettings['site_name'] ?? config('app.name'));
    $vendor = \App\Models\Vendor::where('slug', $vendorSlug)->first();
    $vendorName = $vendor->name ?? '';
    $money = fn ($v) => '$' . number_format((float) $v, ((float) $v == floor((float) $v)) ? 0 : 2);

    $exams = $track['exams'];
    $count = $exams->count();
    $trackTitle = $track['title'];
    $trackUrl = route('tracks.show', $track['slug']);
    $others = collect($allTracks)->reject(fn ($t) => $t['slug'] === $track['slug'])->values();

    $meta = \App\Support\SeoMeta::track($trackTitle, $count, $exams->pluck('exam_code')->all(), $vendorName, $brand);
    $title = $meta['title'];
    $h1 = "{$trackTitle} certification practice exams";
    $codes = $exams->take(5)->pluck('exam_code')->implode(', ');
    $description = $meta['description'];

    $crumbs = [['name' => 'Home', 'url' => url('/')], ['name' => $trackTitle, 'url' => $trackUrl]];
@endphp

@section('title', $title)
@section('meta_description', $description)
@section('canonical_url', $trackUrl)
@section('seo_tags')
    @include('partials.schema-breadcrumbs', ['crumbs' => $crumbs])
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => $h1,
        'url' => $trackUrl,
        'mainEntity' => [
            '@type' => 'ItemList',
            'itemListElement' => $exams->values()->map(fn ($e, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'url' => $e->url, 'name' => "{$e->exam_code} {$e->exam_name}"])->all(),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
<section class="border-b border-slate-200 bg-white">
    <div class="container-custom py-8 lg:py-12">
        <nav aria-label="Breadcrumb" class="text-sm text-slate-500">
            <ol class="flex flex-wrap items-center gap-x-2">
                <li><a href="{{ url('/') }}" class="hover:text-slate-900">Home</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="font-semibold text-slate-800">{{ $trackTitle }}</li>
            </ol>
        </nav>
        <h1 class="mt-4 text-3xl font-extrabold leading-tight text-slate-900 sm:text-4xl">{{ $h1 }}</h1>
        <p class="mt-3 max-w-3xl text-base leading-relaxed text-slate-600 sm:text-lg">
            {{ $track['blurb'] }} There {{ $count === 1 ? 'is' : 'are' }} {{ $count }} {{ $vendorName }} {{ \Illuminate\Support\Str::plural('exam', $count) }} in this track.
            Open an exam for its guide, free practice questions and plans.
        </p>
    </div>
</section>

@php $intro = \App\Support\ExamTracks::intro($track['slug']); @endphp
@if($intro)
<section class="border-b border-slate-200 bg-slate-50" aria-labelledby="track-intro">
    <div class="container-custom max-w-4xl py-10">
        <h2 id="track-intro" class="text-2xl font-extrabold text-slate-900">About the {{ $trackTitle }} exams</h2>
        <div class="mt-4 space-y-4 text-base leading-relaxed text-slate-700">
            @foreach($intro as $p)
                <p>{{ $p }}</p>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="py-10 lg:py-14">
    <div class="container-custom">
        <ul class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @foreach($exams as $exam)
                @php
                    $prices = array_filter([(float) $exam->resolved_price_pdf, (float) $exam->resolved_price_engine], fn ($p) => $p > 0);
                    $from = $prices ? min($prices) : null;
                @endphp
                <li class="flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <span class="rounded-md px-2.5 py-1 text-xs font-extrabold" style="background: var(--brand-soft); color: var(--brand-dark);">{{ $exam->exam_code }}</span>
                        @if($from)<span class="text-sm text-slate-500">from <strong class="text-slate-900">{{ $money($from) }}</strong></span>@endif
                    </div>
                    <h2 class="mt-3 text-lg font-extrabold text-slate-900"><a href="{{ $exam->url }}" class="hover:underline">{{ $exam->exam_name }}</a></h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $exam->question_count }} questions in the bank{{ $exam->difficulty ? ' - ' . $exam->difficulty . ' level' : '' }}</p>
                    <div class="mt-5 flex flex-wrap gap-x-5 gap-y-2 text-sm font-bold">
                        <a href="{{ $exam->url }}" style="color: var(--brand);" class="hover:underline">{{ $exam->exam_code }} exam guide</a>
                        <a href="{{ $exam->questions_url }}" style="color: var(--brand);" class="hover:underline">Free practice questions</a>
                        <a href="{{ $exam->plans_url }}" style="color: var(--brand);" class="hover:underline">Plans</a>
                    </div>
                </li>
            @endforeach
        </ul>

        @if($others->isNotEmpty())
            <div class="mt-14">
                <h2 class="text-2xl font-extrabold text-slate-900">Other {{ $vendorName }} tracks</h2>
                <ul class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($others as $other)
                        <li>
                            <a href="{{ route('tracks.show', $other['slug']) }}" class="block h-full rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-slate-300 hover:shadow-md">
                                <span class="font-extrabold text-slate-900">{{ $other['title'] }}</span>
                                <span class="mt-1 block text-sm text-slate-600">{{ $other['exams']->count() }} {{ \Illuminate\Support\Str::plural('exam', $other['exams']->count()) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p class="mt-12 text-xs leading-relaxed text-slate-500">{{ $brand }} is independent and is not affiliated with {{ $vendorName }}. Practice materials are not official exam content.</p>
    </div>
</section>
@endsection
