@extends('layouts.public')

@section('title', 'Exam Topics Base Blog - IT Certification News & Tips')
@section('meta_description', 'Latest news, tips, and study guides for IT certifications. Stay updated with Exam Topics Base blog.')

@section('content')
<!-- Blog Header -->
<div class="bg-navy py-12 lg:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-3xl font-extrabold text-white sm:text-4xl lg:text-5xl">
                Exam Topics Base <span class="text-cyan">Blog</span>
            </h1>
            <p class="mt-4 max-w-2xl text-xl text-gray-300 mx-auto">
                Insights, updates, and expert advice to help you ace your IT certifications.
            </p>
        </div>
    </div>
</div>

<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Main Content Area -->
            <div class="lg:w-2/3 space-y-10">
                
                @if(isset($featuredPost) && $featuredPost && request()->url() === route('blog.index') && !request()->has('page'))
                    <!-- Featured Post -->
                    <article class="bg-white rounded-xl shadow-lg overflow-hidden transform hover:-translate-y-1 transition duration-300 border border-gray-100">
                        @if($featuredPost->featured_image)
                            <a href="{{ route('blog.show', $featuredPost->slug) }}" class="block">
                                <img src="{{ str_starts_with($featuredPost->featured_image, 'http') ? $featuredPost->featured_image : asset('storage/' . $featuredPost->featured_image) }}" alt="{{ $featuredPost->title }}" class="w-full h-72 object-cover">
                            </a>
                        @endif
                        <div class="p-8">
                            <div class="flex items-center space-x-2 text-sm text-cyan font-semibold uppercase tracking-wider mb-3">
                                @if($featuredPost->category)
                                    <a href="{{ route('blog.category', $featuredPost->category->slug) }}" class="hover:text-navy transition">{{ $featuredPost->category->name }}</a>
                                @endif
                                <span class="text-gray-300">&bull;</span>
                                <span class="text-gray-500 font-medium">{{ $featuredPost->reading_time ?? 1 }} min read</span>
                            </div>
                            <a href="{{ route('blog.show', $featuredPost->slug) }}" class="block">
                                <h2 class="text-3xl font-bold text-navy hover:text-cyan transition mb-4">{{ $featuredPost->title }}</h2>
                                <p class="text-gray-600 mb-6 text-lg line-clamp-3">{{ $featuredPost->excerpt }}</p>
                            </a>
                            <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100">
                                <div class="flex items-center">
                                    <img src="{{ $featuredPost->user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($featuredPost->user->name).'&color=FF6B35&background=0A1628' }}" alt="{{ $featuredPost->user->name }}" class="w-10 h-10 rounded-full border border-gray-200">
                                    <div class="ml-3">
                                        <a href="{{ route('blog.author', $featuredPost->user->id) }}" class="text-sm font-bold text-gray-900 hover:text-cyan">{{ $featuredPost->user->name }}</a>
                                        <p class="text-xs text-gray-500">{{ $featuredPost->published_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('blog.show', $featuredPost->slug) }}" class="text-cyan font-semibold hover:text-navy transition flex items-center">
                                    Read Article <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endif

                <!-- Section Header (if category, tag, search) -->
                @if(isset($category))
                    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-cyan">
                        <h2 class="text-2xl font-bold text-gray-800">Category: {{ $category->name }}</h2>
                        <p class="text-gray-600 mt-2">Showing {{ $posts->total() }} posts in this category.</p>
                    </div>
                @elseif(isset($tag))
                    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-cyan">
                        <h2 class="text-2xl font-bold text-gray-800">Tag: #{{ $tag->name }}</h2>
                        <p class="text-gray-600 mt-2">Showing {{ $posts->total() }} posts with this tag.</p>
                    </div>
                @elseif(isset($author))
                    <div class="bg-white p-6 rounded-lg shadow flex items-center space-x-6">
                        <img src="{{ $author->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($author->name).'&color=FF6B35&background=0A1628' }}" alt="{{ $author->name }}" class="w-20 h-20 rounded-full border-2 border-cyan">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">{{ $author->name }}</h2>
                            <p class="text-gray-600 mt-1">Author of {{ $posts->total() }} articles.</p>
                        </div>
                    </div>
                @elseif(isset($q))
                    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-cyan">
                        <h2 class="text-2xl font-bold text-gray-800">Search Results for "{{ $q }}"</h2>
                        <p class="text-gray-600 mt-2">Found {{ $posts->total() }} articles.</p>
                    </div>
                @else
                    @if(!isset($featuredPost) || request()->has('page'))
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-navy">Latest Articles</h2>
                        </div>
                    @endif
                @endif

                <!-- Post Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @forelse($posts as $post)
                        <article class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden flex flex-col h-full hover:shadow-lg transition">
                            @if($post->featured_image)
                                <a href="{{ route('blog.show', $post->slug) }}" class="block shrink-0">
                                    <img src="{{ str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                                </a>
                            @endif
                            <div class="p-6 flex flex-col flex-1">
                                <div class="flex items-center justify-between mb-3">
                                    @if($post->category)
                                        <a href="{{ route('blog.category', $post->category->slug) }}" class="text-xs font-bold text-cyan uppercase tracking-wide hover:text-navy">{{ $post->category->name }}</a>
                                    @else
                                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">Uncategorized</span>
                                    @endif
                                    <span class="text-xs text-gray-500 font-medium">{{ $post->reading_time ?? 1 }} min read</span>
                                </div>
                                <a href="{{ route('blog.show', $post->slug) }}" class="group block flex-1">
                                    <h3 class="text-xl font-bold text-gray-900 group-hover:text-cyan transition leading-tight mb-3">{{ $post->title }}</h3>
                                    <p class="text-sm text-gray-600 line-clamp-3 mb-4">{{ $post->excerpt }}</p>
                                </a>
                                <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <img src="{{ $post->user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($post->user->name).'&color=FF6B35&background=0A1628' }}" class="w-8 h-8 rounded-full border border-gray-200" alt="{{ $post->user->name }}">
                                        <div class="ml-2">
                                            <a href="{{ route('blog.author', $post->user->id) }}" class="text-xs font-bold text-gray-900 hover:text-cyan">{{ $post->user->name }}</a>
                                            <p class="text-[10px] text-gray-500">{{ $post->published_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center text-xs text-gray-400">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        {{ number_format($post->views_count) }}
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-1 md:col-span-2 bg-white p-10 text-center rounded-lg shadow border border-gray-100">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No posts found</h3>
                            <p class="mt-1 text-sm text-gray-500">Check back later for new articles.</p>
                            @if(isset($q) || isset($category) || isset($tag))
                                <div class="mt-6">
                                    <a href="{{ route('blog.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-navy hover:bg-opacity-90">
                                        Back to Blog Home
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($posts->hasPages())
                    <div class="mt-10 bg-white p-4 rounded-lg shadow flex justify-center">
                        {{ $posts->links() }}
                    </div>
                @endif
                
            </div>

            <!-- Sidebar -->
            <div class="lg:w-1/3 space-y-8">
                <!-- Search -->
                <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Search Articles</h3>
                    <form action="{{ route('blog.search') }}" method="GET" class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="What are you looking for?" class="w-full pl-4 pr-10 py-3 rounded-lg border-gray-300 focus:ring-cyan focus:border-cyan text-sm" required>
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-cyan">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </form>
                </div>

                <!-- Newsletter -->
                <div class="bg-gradient-to-br from-navy to-gray-900 p-6 rounded-xl shadow border border-gray-800 text-white">
                    <div class="flex items-center space-x-3 mb-4 border-b border-gray-700 pb-4">
                        <div class="p-2 bg-orange bg-opacity-20 rounded-lg text-orange">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold">Subscribe & Stay Updated</h3>
                    </div>
                    <p class="text-sm text-gray-300 mb-5">Get the latest exam tips, study guides, and special offers delivered to your inbox.</p>
                    
                    @if(session('success') && str_contains(session('success'), 'subscribe'))
                        <div class="bg-green-600/20 border border-green-500 text-green-400 p-3 rounded mb-4 text-sm font-medium">
                            {{ session('success') }}
                        </div>
                    @else
                        <form action="{{ route('blog.subscribe') }}" method="POST" class="space-y-3">
                            @csrf
                            <input type="hidden" name="source" value="blog_sidebar">
                            <div>
                                <input type="text" name="first_name" placeholder="First Name" required class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border-gray-700 text-white focus:ring-orange focus:border-orange placeholder-gray-500 text-sm">
                            </div>
                            <div>
                                <input type="email" name="email" placeholder="Email Address" required class="w-full px-4 py-2.5 rounded-lg bg-gray-800 border-gray-700 text-white focus:ring-orange focus:border-orange placeholder-gray-500 text-sm">
                            </div>
                            <button type="submit" class="w-full bg-orange hover:bg-opacity-90 text-navy font-bold py-2.5 rounded-lg transition text-sm">
                                Subscribe Now
                            </button>
                        </form>
                        <p class="text-[10px] text-gray-500 mt-3 text-center">No spam. Unsubscribe anytime.</p>
                    @endif
                </div>

                <!-- Categories -->
                @if(isset($categories) && $categories->count() > 0)
                    <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Categories</h3>
                        <ul class="space-y-2">
                            @foreach($categories as $cat)
                                <li>
                                    <a href="{{ route('blog.category', $cat->slug) }}" class="flex items-center justify-between group">
                                        <span class="text-sm text-gray-600 group-hover:text-cyan font-medium transition">{{ $cat->name }}</span>
                                        <span class="bg-gray-100 text-gray-500 text-xs px-2 py-0.5 rounded-full group-hover:bg-cyan group-hover:text-white transition">{{ $cat->posts_count }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Popular Posts -->
                @if(isset($popularPosts) && $popularPosts->count() > 0)
                    <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Popular Posts</h3>
                        <div class="space-y-4">
                            @foreach($popularPosts as $popPost)
                                <a href="{{ route('blog.show', $popPost->slug) }}" class="group flex items-start space-x-3">
                                    @if($popPost->featured_image)
                                        <img src="{{ str_starts_with($popPost->featured_image, 'http') ? $popPost->featured_image : asset('storage/' . $popPost->featured_image) }}" alt="" class="w-16 h-16 rounded object-cover shrink-0">
                                    @endif
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-800 group-hover:text-cyan transition leading-tight mb-1">{{ $popPost->title }}</h4>
                                        <p class="text-xs text-gray-500">{{ $popPost->published_at->format('M d, Y') }} &bull; {{ number_format($popPost->views_count) }} views</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Tags -->
                @if(isset($tags) && $tags->count() > 0)
                    <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Popular Tags</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($tags as $t)
                                <a href="{{ route('blog.tag', $t->slug) }}" class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-medium rounded hover:bg-cyan hover:text-white transition">
                                    #{{ $t->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                
            </div>
        </div>
        
    </div>
</div>
@endsection
