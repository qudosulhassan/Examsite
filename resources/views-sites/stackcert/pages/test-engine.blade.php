@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : config('app.name');
    $crumbs = [['name' => 'Home', 'url' => url('/')], ['name' => 'Test engine', 'url' => url('/test-engine')]];
@endphp

@section('seo_tags')
    @include('partials.schema-breadcrumbs', ['crumbs' => $crumbs])
@endsection

@section('content')
@include('partials.static-hero', ['h1' => 'Online practice test engine for Microsoft exams', 'crumb' => 'Test engine', 'lead' => "Practise in your browser with timed or untimed sessions, see the explanation for each answer, and review your results by topic. It works on a phone, tablet or computer."])

<section class="py-10 lg:py-14">
    <div class="container-custom">
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach([
                ['Timed or untimed', 'Practise at your own pace first, then switch to a timed session when you are ready to test your speed.'],
                ['Explanations for every answer', 'After each question you see why the right answer is right and why the other options are not.'],
                ['Review by topic', 'Your results show which skill areas need more work, so you spend study time where it counts.'],
            ] as [$title, $text])
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="text-base font-extrabold text-slate-900">{{ $title }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $text }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-5 max-w-3xl text-sm leading-relaxed text-slate-600">The engine is a study tool. Its questions are original practice material based on Microsoft's published skills outlines, not official exam content, and {{ $brand }} is not affiliated with Microsoft.</p>

        <h2 class="mt-12 text-2xl font-extrabold text-slate-900">Choose an exam to practise</h2>
        <form action="{{ url('/test-engine') }}" method="GET" class="mt-4 flex max-w-xl gap-2" role="search">
            <label for="te-q" class="sr-only">Search exams</label>
            <input id="te-q" type="search" name="q" value="{{ $searchQuery }}" placeholder="Exam code or name, for example AZ-104" class="min-h-[48px] w-full rounded-xl border border-slate-300 px-4 text-base">
            <button type="submit" class="min-h-[48px] rounded-xl px-5 text-sm font-bold text-white" style="background: var(--brand);">Search</button>
        </form>

        <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($compatibleExams as $exam)
                <li>
                    <a href="{{ route('public.demo-test-engine.lobby', $exam->slug) }}" class="block h-full rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-slate-300 hover:shadow-md">
                        <span class="text-xs font-extrabold" style="color: var(--brand-dark);">{{ $exam->exam_code }}</span>
                        <span class="mt-1 block text-sm font-bold leading-snug text-slate-900">{{ $exam->exam_name }}</span>
                        <span class="mt-2 block text-xs font-semibold text-slate-500">Start a free practice session</span>
                    </a>
                </li>
            @empty
                <li class="text-sm text-slate-600">No exams match that search. Try an exam code such as AZ-900 or SC-200.</li>
            @endforelse
        </ul>

        <div class="mt-8">{{ $compatibleExams->links() }}</div>
    </div>
</section>
@endsection
