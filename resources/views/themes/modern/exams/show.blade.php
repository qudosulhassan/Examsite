@extends('layouts.public')

@section('title', $exam->resolved_seo_title)
@section('meta_description', $exam->resolved_meta_description)

@section('content')
<!-- Modern High-Contrast Theme Header -->
<div class="bg-slate-950 text-white border-b border-slate-800 py-16">
    <div class="container-custom">
        <div class="flex items-center gap-2 mb-4">
            <span class="px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-cyan/10 text-cyan border border-cyan/30">
                MODERN THEME &bull; {{ $currentSite->name ?? 'Exam Portal' }}
            </span>
            <span class="text-xs text-slate-400 font-mono">{{ $exam->vendor->name ?? 'Vendor' }}</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white mb-4">
            {{ $overlay->custom_h1 ?? $exam->exam_code . ': ' . $exam->exam_name }}
        </h1>
        <p class="text-lg text-slate-300 max-w-3xl leading-relaxed">
            {{ $overlay->custom_intro ?? ($exam->description ? strip_tags($exam->description) : 'Updated practice questions, testing simulator, and study notes.') }}
        </p>

        <!-- Modern Action Bar -->
        <div class="mt-8 flex flex-wrap items-center gap-4">
            <div class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-sm font-mono">
                <span class="text-slate-400">Questions:</span> <strong class="text-cyan">{{ $exam->question_count }}</strong>
            </div>
            <div class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-sm font-mono">
                <span class="text-slate-400">Passing Score:</span> <strong class="text-white">{{ $exam->passing_score ?? '700' }}</strong>
            </div>
            <div class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-sm font-mono">
                <span class="text-slate-400">Bundle Price:</span> <strong class="text-emerald-400">${{ number_format($exam->price, 2) }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- Modern Content Section -->
<div class="bg-slate-900 py-16 text-slate-100 min-h-[500px]">
    <div class="container-custom space-y-12">
        @php
            $renderedArticle = $exam->resolved_article_content;
        @endphp
        @if(!empty($renderedArticle))
        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-8 sm:p-10 shadow-xl">
            <h2 class="text-xs font-mono font-bold uppercase tracking-widest text-cyan mb-6">Verified Exam Syllabus & Guide</h2>
            <div class="prose prose-invert prose-cyan max-w-none text-slate-300 leading-relaxed">
                {!! $renderedArticle !!}
            </div>
        </div>
        @endif

        <!-- Sample Questions Preview -->
        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-8 shadow-xl space-y-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-cyan"></span>
                Interactive Question Preview
            </h2>
            <div class="space-y-4">
                @foreach($sampleQuestions as $q)
                <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 text-sm">
                    <div class="font-bold text-slate-200 mb-2">Question #{{ $loop->iteration }}: {{ $q->question_text }}</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-400 font-mono">
                        <div class="p-2.5 rounded bg-slate-950 border border-slate-800">A. {{ $q->option_a }}</div>
                        <div class="p-2.5 rounded bg-slate-950 border border-slate-800">B. {{ $q->option_b }}</div>
                        @if($q->option_c)<div class="p-2.5 rounded bg-slate-950 border border-slate-800">C. {{ $q->option_c }}</div>@endif
                        @if($q->option_d)<div class="p-2.5 rounded bg-slate-950 border border-slate-800">D. {{ $q->option_d }}</div>@endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
