@extends('layouts.public')

@section('title', $post->title . ' | ProExamTopics Blog')
@section('meta_description', $post->meta_description ?? Str::limit(strip_tags($post->content ?? $post->body ?? ''), 150))

@section('content')
<article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Breadcrumb (Panel 7) -->
    <nav class="flex items-center text-xs font-semibold text-slate-500 mb-6 space-x-2">
        <a href="{{ url('/') }}" class="hover:text-slate-900 transition">Home</a>
        <span>&rsaquo;</span>
        <a href="{{ url('/blog') }}" class="hover:text-slate-900 transition">Blog</a>
        <span>&rsaquo;</span>
        <span class="text-slate-800 font-bold truncate">{{ $post->title }}</span>
    </nav>

    <!-- Header Meta -->
    <div class="mb-8">
        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase bg-blue-50 text-[#2563eb] mb-3">
            {{ $post->category ? $post->category->name : 'Exam Strategy' }}
        </span>
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-4">
            {{ $post->title }}
        </h1>
        <div class="flex items-center text-xs text-slate-500 space-x-4 border-b border-gray-100 pb-4">
            <span class="font-semibold text-slate-700">By ProExamTopics Staff</span>
            <span>&bull;</span>
            <span>{{ $post->published_at ? $post->published_at->format('M d, Y') : date('M d, Y') }}</span>
            <span>&bull;</span>
            <span>{{ ceil(str_word_count(strip_tags($post->content ?? $post->body ?? '')) / 200) ?: 5 }} min read</span>
        </div>
    </div>

    <!-- Featured Image -->
    @if($post->featured_image)
        <div class="mb-8 rounded-2xl overflow-hidden shadow-sm">
            <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-auto max-h-96 object-cover">
        </div>
    @endif

    <!-- Content Body -->
    <div class="prose prose-slate max-w-none text-sm sm:text-base leading-relaxed text-slate-700">
        {!! $post->content ?? $post->body ?? '' !!}
    </div>

    <!-- CTA Box -->
    <div class="mt-12 bg-gradient-to-r from-slate-900 to-[#0A1628] rounded-2xl p-8 text-white flex flex-col sm:flex-row items-center justify-between gap-6 shadow-xl">
        <div>
            <h3 class="text-lg font-bold">Ready to test your exam readiness?</h3>
            <p class="text-xs sm:text-sm text-slate-300 mt-1">Practice with our real exam simulator and verify your readiness.</p>
        </div>
        <a href="{{ url('/search') }}" class="bg-[#2563eb] hover:bg-blue-700 text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-lg shadow-md transition whitespace-nowrap">
            Browse Practice Exams
        </a>
    </div>
</article>
@endsection