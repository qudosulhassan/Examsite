@extends('layouts.public')

@section('title', 'IT Certification Study Guides & Articles | ProExamTopics Blog')
@section('meta_description', 'Latest IT certification study tips, exam roadmaps, and career insights to help you pass your vendor exams on your first attempt.')

@section('content')
<div x-data="{ search: '', category: 'all' }">
    <!-- Premium Editorial Hero (Panel 6) -->
    <x-site-b.hero variant="blog" glowColor="purple" padding="compact">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center max-w-6xl mx-auto">
            <!-- Left: Editorial Content & Filter Controls -->
            <div class="lg:col-span-8 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/60 text-xs font-semibold text-slate-300 mb-4 backdrop-blur-sm shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-purple-400 animate-pulse"></span>
                    <span class="font-mono-code text-cyan-300 font-bold">EDITORIAL DESK</span>
                    <span class="text-slate-500">|</span>
                    <span>Industry Analysis & Blueprints</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight mb-3">
                    Certification Insights & <span class="bg-gradient-to-r from-purple-400 via-cyan-300 to-[#00D4AA] bg-clip-text text-transparent">IT Career Guides</span>
                </h1>
                <p class="text-slate-300 text-sm sm:text-base max-w-xl mx-auto lg:mx-0 mb-8 leading-relaxed">
                    Explore comprehensive study roadmaps, exam blueprint analyses, and career strategies authored by certified professionals.
                </p>

                <!-- Search & Filter Controls (Panel 6) -->
                <div class="flex flex-col sm:flex-row items-center justify-start gap-3 max-w-xl mx-auto lg:mx-0">
                    <div class="relative w-full">
                        <input 
                            type="text" 
                            x-model="search" 
                            placeholder="Search articles & study guides..." 
                            class="w-full bg-white border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#2563eb] shadow-xl"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <select x-model="category" class="w-full sm:w-48 py-2.5 px-3 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 focus:outline-none cursor-pointer shadow-xl">
                        <option value="all">All Disciplines</option>
                        <option value="azure">Azure Cloud</option>
                        <option value="cisco">Cisco Network</option>
                        <option value="aws">AWS Cloud</option>
                        <option value="security">Cybersecurity</option>
                    </select>
                </div>
            </div>

            <!-- Right: Floating Editorial Document Cards -->
            <div class="lg:col-span-4 relative hidden lg:flex items-center justify-center">
                <div class="relative w-64 h-56 flex items-center justify-center">
                    <!-- Base Document 1 -->
                    <div class="cpm-float w-52 p-4 rounded-xl bg-slate-900/90 border border-slate-700/80 shadow-2xl backdrop-blur-md space-y-2">
                        <div class="flex items-center justify-between text-[10px] text-purple-400 font-mono-code font-bold">
                            <span>STUDY GUIDE</span>
                            <span>5 MIN READ</span>
                        </div>
                        <div class="h-2 w-3/4 bg-slate-700 rounded"></div>
                        <div class="h-2 w-1/2 bg-slate-800 rounded"></div>
                        <div class="flex items-center gap-1.5 pt-2">
                            <span class="w-4 h-4 rounded-full bg-cyan-400/20 text-cyan-300 flex items-center justify-center text-[9px] font-bold">✓</span>
                            <span class="text-[10px] text-slate-300">Passing Framework</span>
                        </div>
                    </div>

                    <!-- Overlapping Document 2 -->
                    <div class="cpm-float-reverse absolute -bottom-3 -right-2 w-48 p-3.5 rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 border border-cyan-500/30 shadow-2xl backdrop-blur-md">
                        <div class="text-[10px] font-mono-code text-[#00D4AA] font-bold mb-1">BLUEPRINT 2026</div>
                        <div class="text-xs font-bold text-white leading-tight">Domain Breakdown</div>
                        <div class="text-[9px] text-slate-400 mt-1">Updated Syllabus Notes</div>
                    </div>
                </div>
            </div>
        </div>
    </x-site-b.hero>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <!-- 6-Card Article Grid (Panel 6) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($posts as $post)
            @php
                $readTime = ceil(str_word_count(strip_tags($post->content ?? $post->body ?? '')) / 200) ?: 5;
                $catName = $post->category ? $post->category->name : 'Certifications';
            @endphp
            <article class="bg-white border border-gray-200/90 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between group">
                <div>
                    <!-- Thumbnail / Banner -->
                    <div class="h-44 bg-gradient-to-tr from-slate-800 to-blue-900 relative flex items-center justify-center overflow-hidden">
                        @if($post->featured_image)
                            <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="text-white text-center p-4">
                                <span class="text-2xl font-black font-mono-code text-[#00D4AA]">{{ substr($catName, 0, 3) }}</span>
                                <span class="block text-xs text-slate-300 mt-1">Study Guide</span>
                            </div>
                        @endif
                    </div>

                    <div class="p-5">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-50 text-[#2563eb]">
                            {{ $catName }}
                        </span>

                        <h2 class="text-base font-bold text-slate-900 mt-2.5 group-hover:text-[#2563eb] transition-colors leading-snug">
                            <a href="{{ url('/blog/' . $post->slug) }}">
                                {{ $post->title }}
                            </a>
                        </h2>

                        <p class="text-xs text-slate-500 mt-2 line-clamp-2">
                            {{ $post->excerpt ?? Str::limit(strip_tags($post->content ?? $post->body ?? ''), 110) }}
                        </p>
                    </div>
                </div>

                <div class="px-5 pb-5 pt-2 flex items-center justify-between text-[11px] text-slate-400 border-t border-gray-50">
                    <span>{{ $post->published_at ? $post->published_at->format('M d, Y') : date('M d, Y') }}</span>
                    <span>{{ $readTime }} min read</span>
                </div>
            </article>
        @empty
            <div class="col-span-3 py-12 text-center text-slate-500 text-xs">
                No blog articles currently published.
            </div>
        @endforelse
    </div>

    <!-- Pagination (Panel 6) -->
    @if($posts->hasPages())
        <div class="mt-12 flex justify-center">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection