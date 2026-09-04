@extends('layouts.app')

@section('content')
<div class="bg-white min-h-screen">
    
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-br from-[#07101E] via-navy to-[#0F172A] pt-32 pb-40 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden mix-blend-overlay">
            <img src="{{ $post->featured_image ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . $post->featured_image)) : 'https://via.placeholder.com/1200x600?text=Exam Topics Base' }}" alt="{{ $post->title }}" class="w-full h-full object-cover opacity-30">
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-navy/50 to-transparent"></div>
        <!-- Abstract Tech Lines -->
        <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 64px 64px;"></div>
        
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            @if($post->category)
                <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}" class="inline-block bg-cyan/10 border border-cyan/20 text-cyan text-[11px] font-black px-5 py-2 rounded-xl uppercase tracking-widest mb-6 hover:bg-cyan/20 transition-colors backdrop-blur-sm">
                    {{ $post->category->name }}
                </a>
            @endif
            
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white mb-8 leading-tight max-w-4xl mx-auto">
                {{ $post->title }}
            </h1>
            
            <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto mb-10 font-medium leading-relaxed line-clamp-3">
                {{ $post->excerpt }}
            </p>
            
            <div class="flex items-center justify-center gap-6 text-[13px] font-bold uppercase tracking-wider text-gray-400 backdrop-blur-md bg-white/5 border border-white/10 py-3 px-8 rounded-2xl mx-auto max-w-fit">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-cyan to-blue-500 flex items-center justify-center text-white font-bold shadow-[0_5px_15px_rgba(0,212,170,0.3)]">
                        {{ substr($post->user->name, 0, 1) }}
                    </div>
                    <span class="text-white">{{ $post->user->name }}</span>
                </div>
                <span class="text-gray-600">&bull;</span>
                <span>{{ $post->published_at->format('M d, Y') }}</span>
                <span class="text-gray-600">&bull;</span>
                <span>{{ $post->views_count }} views</span>
            </div>
        </div>
    </div>

    <!-- Article Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20 -mt-20 mb-20">
        <div class="bg-white rounded-3xl p-8 md:p-16 shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-gray-100">
            <article class="prose prose-lg prose-cyan mx-auto text-gray-700 prose-headings:font-black prose-headings:text-navy prose-a:text-cyan hover:prose-a:text-blue-500">
                {!! $post->content !!}
            </article>

            <!-- Tags -->
            @if($post->tags->isNotEmpty())
                <div class="mt-16 pt-8 border-t border-gray-100 flex items-center gap-3 flex-wrap">
                    <span class="text-[11px] font-black text-gray-400 uppercase tracking-widest mr-2">Tags:</span>
                    @foreach($post->tags as $tag)
                        <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" class="px-5 py-2 bg-gray-50 border border-gray-200 text-gray-600 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-cyan hover:text-navy hover:border-cyan transition-all duration-300">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Related Posts -->
    @if($relatedPosts->isNotEmpty())
        <div class="bg-gray-50 py-20 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-black text-navy mb-12 text-center">Read Next</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($relatedPosts as $related)
                        <div class="bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.04)] border border-gray-100 overflow-hidden flex flex-col hover:-translate-y-2 hover:shadow-[0_20px_50px_rgba(0,0,0,0.08)] transition-all duration-300 group">
                            <a href="{{ route('blog.show', $related->slug) }}" class="relative h-56 overflow-hidden block">
                                <img src="{{ $related->featured_image ? (str_starts_with($related->featured_image, 'http') ? $related->featured_image : asset('storage/' . $related->featured_image)) : 'https://via.placeholder.com/600x400?text=Exam Topics Base' }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="{{ $related->title }}">
                                @if($related->category)
                                    <div class="absolute top-4 left-4">
                                        <span class="bg-white/90 backdrop-blur-md text-navy text-[10px] font-black px-3 py-1.5 rounded-lg uppercase tracking-widest shadow-sm">{{ $related->category->name }}</span>
                                    </div>
                                @endif
                            </a>
                            <div class="p-8 flex-1 flex flex-col">
                                <a href="{{ route('blog.show', $related->slug) }}" class="block mt-2">
                                    <h3 class="text-xl font-black text-navy group-hover:text-cyan transition-colors mb-3 line-clamp-2 leading-tight">{{ $related->title }}</h3>
                                </a>
                                <div class="mt-auto pt-6 border-t border-gray-100 flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                    <span>{{ $related->published_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
