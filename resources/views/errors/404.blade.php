@extends('layouts.public')

@section('title', 'Page Not Found - Exam Topics Base')

@section('content')
<section class="bg-navy text-white py-24 text-center min-h-[65vh] flex flex-col justify-center relative overflow-hidden">
    <!-- Abstract background decorations -->
    <div class="absolute left-0 top-0 opacity-10 transform -translate-x-1/3 translate-y-1/4 select-none pointer-events-none">
        <svg class="h-96 w-96 text-cyan" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2L14.5 9.5H22L16 14L18.5 21.5L12 17L5.5 21.5L8 14L2 9.5H9.5L12 2Z" />
        </svg>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Icon -->
        <div class="flex justify-center mb-6">
            <div class="bg-gray-800 p-4 rounded-full border border-gray-700 shadow-lg text-cyan">
                <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </div>
        </div>

        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight mb-4 leading-tight">
            Oops, we couldn't find that page!
        </h1>
        <p class="text-lg text-gray-300 mb-8 max-w-xl mx-auto font-normal">
            The exam or vendor you are looking for might have been moved, updated, or you might have mistyped the URL. 
        </p>

        <!-- Live Search Bar -->
        <div class="max-w-xl mx-auto mb-10 relative text-left" x-data="{
            query: '',
            results: [],
            showDropdown: false,
            liveSearch() {
                if (this.query.length < 2) {
                    this.results = [];
                    this.showDropdown = false;
                    return;
                }
                fetch('/api/search?q=' + encodeURIComponent(this.query))
                    .then(res => res.json())
                    .then(data => {
                        this.results = data.exams || [];
                        this.showDropdown = true;
                    }).catch(() => {});
            }
        }" @click.outside="showDropdown = false">
            <form action="{{ url('/search') }}" method="GET" class="relative group shadow-2xl">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-6 w-6 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" 
                       name="q" 
                       x-model="query"
                       @input.debounce.250ms="liveSearch()"
                       @focus="if(results.length) showDropdown = true"
                       placeholder="Search by exam code (e.g. AZ-900, 200-301) or vendor..." 
                       class="block w-full pl-12 pr-28 py-4 text-base sm:text-lg border-2 border-gray-700 bg-gray-800 text-white placeholder-gray-400 rounded-2xl focus:outline-none focus:ring-0 focus:border-cyan transition-colors"
                       autocomplete="off">

                <button type="submit" class="absolute right-2 top-2 bottom-2 bg-cyan text-navy font-extrabold px-5 rounded-xl hover:bg-white transition-colors text-sm uppercase tracking-wider">
                    Search
                </button>

                <!-- Live Results Dropdown -->
                <div x-show="showDropdown && results.length > 0" x-transition class="absolute left-0 right-0 top-full mt-2 bg-white text-navy border border-gray-200 rounded-2xl shadow-2xl z-50 overflow-hidden text-left divide-y divide-gray-100">
                    <div class="p-3 bg-gray-50 text-[11px] font-bold uppercase tracking-wider text-gray-500 flex justify-between items-center">
                        <span>Live Exam Matches</span>
                        <span class="text-cyan font-bold" x-text="results.length + ' found'"></span>
                    </div>
                    <div class="max-h-64 overflow-y-auto">
                        <template x-for="item in results" :key="item.code">
                            <a :href="item.url" class="flex items-center justify-between p-4 hover:bg-cyan/5 transition-colors group">
                                <div>
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="text-sm font-black text-navy group-hover:text-cyan transition-colors" x-text="item.code"></span>
                                        <span class="text-[10px] font-bold text-gray-500 uppercase bg-gray-100 px-2 py-0.5 rounded" x-text="item.vendor"></span>
                                    </div>
                                    <div class="text-xs text-gray-500 truncate max-w-sm" x-text="item.name"></div>
                                </div>
                                <span class="text-xs font-bold text-cyan group-hover:underline">View Exam &rarr;</span>
                            </a>
                        </template>
                    </div>
                </div>
            </form>
        </div>

        <!-- Quick Links -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-6 py-3.5 border border-transparent text-sm font-bold uppercase tracking-wider rounded-xl text-navy bg-cyan hover:bg-white transition-colors w-full sm:w-auto shadow-md">
                Go to Homepage
            </a>
            <a href="{{ url('/vendors') }}" class="inline-flex items-center justify-center px-6 py-3.5 border border-gray-700 text-sm font-bold uppercase tracking-wider rounded-xl text-white bg-gray-800 hover:bg-gray-700 transition-colors w-full sm:w-auto">
                Browse All Vendors
            </a>
            <a href="{{ url('/test-engine') }}" class="inline-flex items-center justify-center px-6 py-3.5 border border-gray-700 text-sm font-bold uppercase tracking-wider rounded-xl text-white bg-gray-800 hover:bg-gray-700 transition-colors w-full sm:w-auto">
                Test Engine Simulator
            </a>
        </div>
    </div>
</section>
@endsection
