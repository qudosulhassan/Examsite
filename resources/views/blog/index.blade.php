@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header (Deep Space Premium) -->
        <div class="relative bg-gradient-to-br from-[#07101E] via-navy to-[#0F172A] rounded-3xl overflow-hidden mb-16 mt-6 shadow-[0_20px_50px_rgba(0,0,0,0.2)]">
            <!-- Abstract glowing orbs -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-cyan rounded-full mix-blend-screen filter blur-[100px] opacity-20 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[100px] opacity-20 pointer-events-none"></div>
            <!-- Abstract Tech Lines -->
            <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 64px 64px;"></div>

            <div class="relative z-10 px-8 py-20 md:py-24 text-center">
                <div class="inline-flex items-center space-x-2 bg-white/5 border border-white/10 rounded-full px-4 py-1.5 mb-6">
                    <span class="text-xs font-bold uppercase tracking-widest text-cyan">Exam Topics Base Blog</span>
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight mb-6">
                    Insights & <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan to-blue-500">Resources</span>
                </h1>
                <p class="text-lg text-gray-300 max-w-2xl mx-auto font-medium">Stay up to date with the latest certification tips, exam strategies, and tech industry news.</p>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white p-4 md:p-6 rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.04)] border border-gray-100 mb-16 flex flex-col md:flex-row gap-6 items-center justify-between relative z-20 -mt-24 mx-4 md:mx-8">
            <form action="{{ route('blog.index') }}" method="GET" class="w-full md:w-96 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..." class="w-full pl-12 pr-4 py-3.5 rounded-xl border-gray-200 focus:border-cyan focus:ring-1 focus:ring-cyan text-sm font-medium bg-gray-50/50 hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 text-cyan absolute left-4 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </form>
            <div class="flex gap-3 overflow-x-auto pb-2 md:pb-0 hide-scrollbar w-full md:w-auto items-center">
                <a href="{{ route('blog.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all duration-300 {{ !request('category') ? 'bg-navy text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">All</a>
                @foreach($categories as $category)
                    <a href="{{ route('blog.index', ['category' => $category->slug]) }}" class="px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all duration-300 {{ request('category') == $category->slug ? 'bg-cyan text-navy shadow-[0_5px_15px_rgba(0,212,170,0.3)]' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </div>

        @if($featuredPosts->isNotEmpty() && !request('page') && !request('search') && !request('category'))
            <!-- Featured Section -->
            <div class="mb-16">
                <h2 class="text-3xl font-black text-navy mb-8 flex items-center">
                    <svg class="w-8 h-8 text-cyan mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                    Featured Posts
                </h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Main Featured -->
                    @php $mainFeatured = $featuredPosts->first(); @endphp
                    <a href="{{ route('blog.show', $mainFeatured->slug) }}" class="group block relative rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.15)] h-[440px] transform hover:-translate-y-2 transition-all duration-500">
                        <img src="{{ $mainFeatured->featured_image ? (str_starts_with($mainFeatured->featured_image, 'http') ? $mainFeatured->featured_image : asset('storage/' . $mainFeatured->featured_image)) : 'https://via.placeholder.com/800x400?text=Exam Topics Base' }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="{{ $mainFeatured->title }}">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#07101E] via-[#07101E]/60 to-transparent opacity-90"></div>
                        <div class="absolute bottom-0 left-0 p-10 w-full">
                            @if($mainFeatured->category)
                                <span class="bg-cyan text-navy text-[11px] font-black px-4 py-1.5 rounded-xl uppercase tracking-widest mb-4 inline-block shadow-md">{{ $mainFeatured->category->name }}</span>
                            @endif
                            <h3 class="text-3xl lg:text-4xl font-black text-white mb-4 group-hover:text-cyan transition-colors leading-tight">{{ $mainFeatured->title }}</h3>
                            <p class="text-gray-300 text-sm font-medium line-clamp-2 mb-6">{{ $mainFeatured->excerpt }}</p>
                            <div class="flex items-center text-xs font-bold uppercase tracking-wider text-gray-400">
                                <span>{{ $mainFeatured->published_at->format('M d, Y') }}</span>
                                <span class="mx-3 text-gray-600">&bull;</span>
                                <span>{{ $mainFeatured->views_count }} views</span>
                            </div>
                        </div>
                    </a>

                    <!-- Sub Featured -->
                    <div class="flex flex-col gap-8">
                        @foreach($featuredPosts->skip(1) as $subFeatured)
                        <a href="{{ route('blog.show', $subFeatured->slug) }}" class="group flex bg-white rounded-3xl overflow-hidden shadow-[0_10px_40px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_50px_rgba(0,0,0,0.08)] border border-gray-100 hover:border-cyan/30 transform hover:-translate-y-1 transition-all duration-300 h-[204px]">
                            <div class="w-2/5 relative overflow-hidden">
                                <img src="{{ $subFeatured->featured_image ? (str_starts_with($subFeatured->featured_image, 'http') ? $subFeatured->featured_image : asset('storage/' . $subFeatured->featured_image)) : 'https://via.placeholder.com/400x300?text=Exam Topics Base' }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="{{ $subFeatured->title }}">
                            </div>
                            <div class="w-3/5 p-8 flex flex-col justify-center">
                                @if($subFeatured->category)
                                    <span class="text-cyan text-[10px] font-black uppercase tracking-widest mb-3 block">{{ $subFeatured->category->name }}</span>
                                @endif
                                <h3 class="text-xl font-black text-navy mb-3 group-hover:text-cyan transition-colors line-clamp-2">{{ $subFeatured->title }}</h3>
                                <div class="flex items-center text-[11px] font-bold uppercase tracking-wider text-gray-400 mt-auto">
                                    <span>{{ $subFeatured->published_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Latest Posts Grid -->
        <h2 class="text-3xl font-black text-navy mb-8">Latest Articles</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($posts as $post)
                <div class="bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.04)] border border-gray-100 overflow-hidden flex flex-col hover:-translate-y-2 hover:shadow-[0_20px_50px_rgba(0,0,0,0.08)] transition-all duration-300 group">
                    <a href="{{ route('blog.show', $post->slug) }}" class="relative h-56 overflow-hidden block">
                        <img src="{{ $post->featured_image ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . $post->featured_image)) : 'https://via.placeholder.com/600x400?text=Exam Topics Base' }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="{{ $post->title }}">
                        @if($post->category)
                            <div class="absolute top-4 left-4">
                                <span class="bg-white/90 backdrop-blur-md text-navy text-[10px] font-black px-3 py-1.5 rounded-lg uppercase tracking-widest shadow-sm">{{ $post->category->name }}</span>
                            </div>
                        @endif
                    </a>
                    <div class="p-8 flex-1 flex flex-col">
                        <a href="{{ route('blog.show', $post->slug) }}" class="block mt-2">
                            <h3 class="text-xl font-black text-navy group-hover:text-cyan transition-colors mb-3 line-clamp-2 leading-tight">{{ $post->title }}</h3>
                            <p class="text-[13px] font-medium text-gray-500 mb-6 line-clamp-3 leading-relaxed">{{ $post->excerpt }}</p>
                        </a>
                        <div class="mt-auto pt-6 border-t border-gray-100 flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-gray-400">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-cyan/10 flex items-center justify-center text-cyan text-sm">
                                    {{ substr($post->user->name, 0, 1) }}
                                </div>
                                <span>{{ $post->user->name }}</span>
                            </div>
                            <span>{{ $post->published_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-20 bg-white rounded-3xl border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.04)]">
                    <svg class="mx-auto h-16 w-16 text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5L18.5 5H20a2 2 0 012 2v12a2 2 0 01-2 2z"></path></svg>
                    <h3 class="text-xl font-black text-navy">No articles found</h3>
                    <p class="mt-2 text-sm font-medium text-gray-500">We couldn't find any articles matching your criteria.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-12 flex justify-center">
            {{ $posts->links() }}
        </div>

    </div>
</div>
@endsection
