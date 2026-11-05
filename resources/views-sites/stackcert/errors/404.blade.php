@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : config('app.name');
    $vendorSlug = (string) (config('site.vendors')[0] ?? 'microsoft');
    $vendorName = \App\Models\Vendor::where('slug', $vendorSlug)->value('name') ?: 'Microsoft';

    // A lost visitor needs the way back: every track, and the exams people look for most.
    $allExams = \App\Models\Exam::where('is_active', true)->with('vendor')->orderBy('exam_code')->get();
    $tracks = \App\Support\ExamTracks::group($allExams, $vendorSlug);
    $popular = collect(['az-900', 'az-104', 'az-305', 'sc-900', 'sc-200', 'sc-300', 'pl-300', 'dp-900'])
        ->map(fn ($slug) => $allExams->firstWhere('slug', $slug))->filter()->values();
@endphp

@section('title', "Page not found | {$brand}")
@section('robots', 'noindex, follow')

@section('content')
<section class="border-b border-slate-200 bg-white">
    <div class="container-custom max-w-4xl py-14 text-center">
        <p class="text-sm font-extrabold uppercase tracking-widest" style="color: var(--brand);">Error 404</p>
        <h1 class="mt-3 text-3xl font-extrabold leading-tight text-slate-900 sm:text-4xl">We could not find that page</h1>
        <p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-slate-600">
            The link may be old, or the exam may have moved. Search for an exam code, or pick a track below.
        </p>
        <form action="{{ url('/search') }}" method="GET" role="search" class="mx-auto mt-6 flex max-w-xl gap-2">
            <label for="nf-search" class="sr-only">Search {{ $vendorName }} exams</label>
            <input id="nf-search" type="search" name="q" placeholder="Search an exam code, e.g. AZ-900" class="min-h-[48px] w-full rounded-xl border border-slate-300 px-4 text-base" autocomplete="off">
            <button type="submit" class="min-h-[48px] rounded-xl px-6 text-sm font-bold text-white" style="background: var(--brand);">Search</button>
        </form>
    </div>
</section>

<section class="py-12">
    <div class="container-custom max-w-5xl">
        <h2 class="text-xl font-extrabold text-slate-900">{{ $vendorName }} exam tracks</h2>
        <ul class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($tracks as $t)
                <li>
                    <a href="{{ url('/tracks/' . $t['slug']) }}" class="block rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-slate-300 hover:shadow-md">
                        <span class="font-extrabold text-slate-900">{{ $t['title'] }}</span>
                        <span class="mt-1 block text-sm text-slate-600">{{ $t['exams']->count() }} {{ \Illuminate\Support\Str::plural('exam', $t['exams']->count()) }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        @if($popular->isNotEmpty())
            <h2 class="mt-10 text-xl font-extrabold text-slate-900">Popular exams</h2>
            <ul class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($popular as $exam)
                    <li>
                        <a href="{{ $exam->url }}" class="block rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300 hover:shadow-md">
                            <span class="text-xs font-extrabold" style="color: var(--brand-dark);">{{ $exam->exam_code }}</span>
                            <span class="mt-1 block text-sm font-bold text-slate-900">{{ $exam->exam_code }} exam guide and practice questions</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <p class="mt-10 text-sm text-slate-600">
            Or go to the <a href="{{ url('/') }}" class="font-bold underline" style="color: var(--brand);">home page</a>, the
            <a href="{{ url('/vendors/' . $vendorSlug) }}" class="font-bold underline" style="color: var(--brand);">full exam list</a> or the
            <a href="{{ url('/contact') }}" class="font-bold underline" style="color: var(--brand);">contact page</a>.
        </p>
    </div>
</section>
@endsection
