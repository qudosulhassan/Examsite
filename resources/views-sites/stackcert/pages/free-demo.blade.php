@extends('layouts.public')

@php
    $brand = $currentSite ? $currentSite->name : config('app.name');
    $crumbs = [['name' => 'Home', 'url' => url('/')], ['name' => 'Free sample', 'url' => url('/free-demo')]];
@endphp

@section('seo_tags')
    @include('partials.schema-breadcrumbs', ['crumbs' => $crumbs])
@endsection

@section('content')
@include('partials.static-hero', ['h1' => 'Free Microsoft practice question sample', 'crumb' => 'Free sample', 'lead' => 'Choose an exam and we email you a PDF with practice questions and the explanation for every answer. No payment needed.'])

<section class="py-10 lg:py-14">
    <div class="container-custom grid gap-8 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 lg:p-8">
            <h2 class="text-xl font-extrabold text-slate-900">What is in the sample</h2>
            <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-relaxed text-slate-700">
                <li>Practice questions with multiple-choice options for the exam you pick</li>
                <li>The correct answer marked, with an explanation of why it is right and why the others are not</li>
                <li>A PDF that works on a phone and prints cleanly</li>
            </ul>
            <p class="mt-5 text-sm leading-relaxed text-slate-600">The questions are original study material written for the skills Microsoft lists in each exam's study guide. They are not official exam content. If you would rather try questions online straight away, use the <a href="{{ url('/exams') }}" class="font-bold underline" style="color: var(--brand);">free practice questions on each exam page</a>.</p>
            <p class="mt-5 rounded-xl bg-slate-50 p-4 text-xs leading-relaxed text-slate-600">We use your email only to send the sample. We do not share it. You will receive one email with a download link.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
            <h2 class="text-xl font-extrabold text-slate-900">Request your sample</h2>
            @if(session('status'))
                <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800" role="status">{{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700" role="alert">{{ $errors->first() }}</div>
            @endif
            <form action="{{ url('/free-demo') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="d-name" class="block text-sm font-bold text-slate-800">Your name</label>
                    <input id="d-name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" class="mt-1 min-h-[48px] w-full rounded-xl border border-slate-300 px-4 text-base">
                </div>
                <div>
                    <label for="d-email" class="block text-sm font-bold text-slate-800">Email address</label>
                    <input id="d-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="mt-1 min-h-[48px] w-full rounded-xl border border-slate-300 px-4 text-base">
                </div>
                <div>
                    <label for="d-exam" class="block text-sm font-bold text-slate-800">Exam</label>
                    <select id="d-exam" name="exam_id" required class="mt-1 min-h-[48px] w-full rounded-xl border border-slate-300 bg-white px-3 text-base">
                        <option value="">Choose an exam</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" @selected(old('exam_id') == $exam->id)>{{ $exam->exam_code }}: {{ $exam->exam_name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="min-h-[48px] w-full rounded-xl px-6 text-sm font-bold text-white" style="background: var(--brand);">Email me the sample</button>
            </form>
        </div>
    </div>
</section>
@endsection
