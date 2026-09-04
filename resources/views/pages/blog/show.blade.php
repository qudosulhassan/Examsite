@extends('layouts.public')

@section('title', $post->meta_title ?? $post->title)
@section('meta_description', $post->meta_description ?? $post->excerpt)
@section('meta_keywords', $post->meta_keywords)
@if($post->canonical_url)
    @section('canonical_url', $post->canonical_url)
@endif
@if($post->og_title)
    @section('og_title', $post->og_title)
@endif
@if($post->og_description)
    @section('og_description', $post->og_description)
@endif
@if($post->og_image || $post->featured_image)
    @section('og_image', $post->og_image ?? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . $post->featured_image)))
@endif

@section('content')
<!-- Blog Header -->
<div class="bg-navy py-12 lg:py-16 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <svg class="h-full w-full" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none">
            <path d="M0 100 C 20 0 50 0 100 100 Z"></path>
        </svg>
    </div>
    
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        @if($post->category)
            <a href="{{ route('blog.category', $post->category->slug) }}" class="inline-block mb-4 px-3 py-1 bg-cyan bg-opacity-20 text-cyan text-sm font-bold uppercase tracking-wider rounded-full hover:bg-opacity-30 transition">
                {{ $post->category->name }}
            </a>
        @endif
        
        <h1 class="text-3xl font-extrabold text-white sm:text-4xl lg:text-5xl leading-tight mb-6">
            {{ $post->title }}
        </h1>
        
        <div class="flex items-center justify-center space-x-6 text-sm text-gray-300">
            <div class="flex items-center">
                <img src="{{ $post->user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($post->user->name).'&color=FF6B35&background=0A1628' }}" alt="{{ $post->user->name }}" class="w-8 h-8 rounded-full border border-gray-600 mr-2">
                <a href="{{ route('blog.author', $post->user->id) }}" class="hover:text-white transition">{{ $post->user->name }}</a>
            </div>
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                {{ $post->published_at->format('M d, Y') }}
            </div>
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ $post->reading_time ?? 1 }} min read
            </div>
        </div>
    </div>
</div>

<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row gap-8">
        
        <!-- Main Article Area -->
        <article class="lg:w-2/3 bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
            @if($post->featured_image)
                <img src="{{ str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . $post->featured_image) }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="w-full h-auto max-h-96 object-cover">
            @endif
            
            <div class="p-8 md:p-12">
                <div class="prose prose-lg prose-cyan max-w-none text-gray-700">
                    {!! $post->content !!}
                </div>
                
                <!-- Tags -->
                @if($post->tags->count() > 0)
                    <div class="mt-10 pt-6 border-t border-gray-100 flex flex-wrap gap-2">
                        <span class="text-gray-500 font-bold mr-2 self-center">Tags:</span>
                        @foreach($post->tags as $tag)
                            <a href="{{ route('blog.tag', $tag->slug) }}" class="px-3 py-1 bg-gray-100 text-gray-600 text-sm font-medium rounded hover:bg-cyan hover:text-white transition">
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
                
                <!-- Social Share -->
                <div class="mt-8 flex items-center space-x-4">
                    <span class="text-gray-500 font-bold">Share:</span>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" target="_blank" class="text-gray-400 hover:text-[#1DA1F2] transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" class="text-gray-400 hover:text-[#1877F2] transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->url()) }}&title={{ urlencode($post->title) }}" target="_blank" class="text-gray-400 hover:text-[#0A66C2] transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Author Box -->
            <div class="bg-gray-50 p-8 border-t border-gray-200 flex flex-col sm:flex-row items-start sm:items-center">
                <img src="{{ $post->user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($post->user->name).'&color=FF6B35&background=0A1628' }}" alt="{{ $post->user->name }}" class="w-20 h-20 rounded-full border-2 border-white shadow-sm mb-4 sm:mb-0 sm:mr-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Written by <a href="{{ route('blog.author', $post->user->id) }}" class="text-cyan hover:underline">{{ $post->user->name }}</a></h3>
                    <p class="text-gray-600 text-sm">Instructor and tech enthusiast dedicated to helping students achieve their IT certification goals.</p>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="p-8 md:p-12 border-t border-gray-100">
                <h3 class="text-2xl font-bold text-navy mb-8">Comments ({{ $post->comments->count() }})</h3>
                
                @if(session('success') && str_contains(session('success'), 'comment'))
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-8">
                        <p class="text-green-700 text-sm font-medium">{{ session('success') }}</p>
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8">
                        <p class="text-red-700 text-sm font-medium">{{ session('error') }}</p>
                    </div>
                @endif

                <!-- Comment Form -->
                <div class="bg-gray-50 rounded-lg p-6 mb-10 border border-gray-200">
                    <h4 class="text-lg font-bold text-gray-800 mb-4">Leave a Reply</h4>
                    <form action="{{ route('blog.comments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="blog_post_id" value="{{ $post->id }}">
                        
                        <!-- Honeypot -->
                        <div class="hidden">
                            <input type="text" name="website_url_honeypot" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="author_name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                                <input type="text" name="author_name" id="author_name" required value="{{ old('author_name', auth()->check() ? auth()->user()->name : '') }}" class="w-full rounded border-gray-300 focus:border-cyan focus:ring-cyan text-sm">
                            </div>
                            <div>
                                <label for="author_email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span> <span class="text-xs text-gray-400 font-normal">(will not be published)</span></label>
                                <input type="email" name="author_email" id="author_email" required value="{{ old('author_email', auth()->check() ? auth()->user()->email : '') }}" class="w-full rounded border-gray-300 focus:border-cyan focus:ring-cyan text-sm">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="comment_text" class="block text-sm font-medium text-gray-700 mb-1">Comment <span class="text-red-500">*</span></label>
                            <textarea name="comment_text" id="comment_text" rows="4" required class="w-full rounded border-gray-300 focus:border-cyan focus:ring-cyan text-sm">{{ old('comment_text') }}</textarea>
                        </div>
                        <button type="submit" class="bg-navy hover:bg-opacity-90 text-white font-bold py-2 px-6 rounded transition">
                            Post Comment
                        </button>
                    </form>
                </div>

                <!-- Comments List -->
                <div class="space-y-6">
                    @forelse($post->comments as $comment)
                        <div class="flex space-x-4">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($comment->author_name) }}&color=7F9CF5&background=EBF4FF" alt="{{ $comment->author_name }}" class="w-12 h-12 rounded-full mt-1">
                            <div class="flex-1 bg-white border border-gray-100 p-4 rounded-lg shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <h5 class="font-bold text-gray-900">{{ $comment->author_name }}</h5>
                                    <span class="text-xs text-gray-500">{{ $comment->created_at->format('M d, Y') }}</span>
                                </div>
                                <p class="text-gray-700 text-sm whitespace-pre-line">{{ $comment->comment_text }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 italic text-sm">No comments yet. Be the first to share your thoughts!</p>
                    @endforelse
                </div>
            </div>
        </article>

        <!-- Sidebar -->
        <aside class="lg:w-1/3 space-y-8">
            
            <!-- Promotional CTA if Related Exam -->
            @if($post->exam)
                <div class="bg-gradient-to-br from-navy to-cyan p-6 rounded-xl shadow-lg border border-cyan/20 text-white relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 opacity-10">
                        <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div class="relative z-10">
                        <span class="inline-block px-2 py-1 bg-orange text-navy text-xs font-bold rounded mb-3 uppercase tracking-wider">Recommended Tool</span>
                        <h3 class="text-xl font-bold mb-2">Prepare for {{ $post->exam->exam_code }}</h3>
                        <p class="text-sm text-gray-200 mb-5">Get the official study guide and practice test bundle to guarantee your success.</p>
                        <a href="{{ $post->exam->url }}" class="block text-center w-full bg-white text-navy hover:bg-gray-100 font-bold py-3 rounded-lg shadow transition">
                            View Study Materials
                        </a>
                    </div>
                </div>
            @endif

            <!-- Search -->
            <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Search Articles</h3>
                <form action="{{ route('blog.search') }}" method="GET" class="relative">
                    <input type="text" name="q" placeholder="What are you looking for?" class="w-full pl-4 pr-10 py-3 rounded-lg border-gray-300 focus:ring-cyan focus:border-cyan text-sm" required>
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-cyan">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>
            </div>

            <!-- Newsletter -->
            <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
                <div class="flex items-center space-x-3 mb-4 border-b pb-4">
                    <div class="p-2 bg-cyan bg-opacity-10 rounded-lg text-cyan">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Subscribe</h3>
                </div>
                <p class="text-sm text-gray-600 mb-5">Get the latest exam tips, study guides, and special offers delivered to your inbox.</p>
                
                @if(session('success') && str_contains(session('success'), 'subscribe'))
                    <div class="bg-green-50 border border-green-500 text-green-700 p-3 rounded mb-4 text-sm font-medium">
                        {{ session('success') }}
                    </div>
                @else
                    <form action="{{ route('blog.subscribe') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="source" value="blog_post_{{ $post->id }}">
                        <div>
                            <input type="text" name="first_name" placeholder="First Name" required class="w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-cyan focus:border-cyan text-sm">
                        </div>
                        <div>
                            <input type="email" name="email" placeholder="Email Address" required class="w-full px-4 py-2.5 rounded-lg border-gray-300 focus:ring-cyan focus:border-cyan text-sm">
                        </div>
                        <button type="submit" class="w-full bg-cyan hover:bg-opacity-90 text-white font-bold py-2.5 rounded-lg shadow transition text-sm">
                            Subscribe Now
                        </button>
                    </form>
                @endif
            </div>

            <!-- Related Posts -->
            @if(isset($relatedPosts) && $relatedPosts->count() > 0)
                <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Related Articles</h3>
                    <div class="space-y-4">
                        @foreach($relatedPosts as $rPost)
                            <a href="{{ route('blog.show', $rPost->slug) }}" class="group flex items-start space-x-3">
                                @if($rPost->featured_image)
                                    <img src="{{ str_starts_with($rPost->featured_image, 'http') ? $rPost->featured_image : asset('storage/' . $rPost->featured_image) }}" alt="" class="w-16 h-16 rounded object-cover shrink-0">
                                @endif
                                <div>
                                    <h4 class="text-sm font-bold text-gray-800 group-hover:text-cyan transition leading-tight mb-1">{{ $rPost->title }}</h4>
                                    <p class="text-xs text-gray-500">{{ $rPost->published_at->format('M d, Y') }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

        </aside>
    </div>
</div>
@endsection
