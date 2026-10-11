@extends('layouts.public')

@section('title', 'Search IT Certification Practice Exams | ProExamTopics')
@section('meta_description', 'Search and filter IT certification practice exams by vendor, difficulty, question format, and price on ProExamTopics.')

@section('content')
<div x-data="{ mobileFiltersOpen: false, priceMax: '{{ request('price_max', 200) }}' }">
    <!-- Premium Search Application Hero (Panel 7) -->
    <x-site-b.hero variant="search" glowColor="cyan" padding="compact">
        <div class="relative max-w-5xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
                <!-- Main Search Application Form Column -->
                <div class="lg:col-span-8 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/60 text-xs font-semibold text-slate-300 mb-3 backdrop-blur-sm shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                        <span class="font-mono-code text-[#00D4AA] font-bold">SEARCH APPLICATION</span>
                        <span class="text-slate-500">|</span>
                        <span>Direct Exam Index Query</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight leading-tight mb-2">
                        Catalog Query <span class="bg-gradient-to-r from-blue-400 via-cyan-300 to-[#00D4AA] bg-clip-text text-transparent">Interface</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300 mb-6">
                        Showing <strong class="text-white font-bold font-mono-code">{{ $exams->total() }}</strong> verified certification records matching <span class="text-cyan-300 font-semibold font-mono-code">"{{ $query ?: 'All Certifications' }}"</span>
                    </p>

                    <!-- Search Bar with Blue Button -->
                    <form action="{{ url('/search') }}" method="GET" class="flex flex-col sm:flex-row gap-2 bg-white p-2 rounded-2xl shadow-2xl border border-slate-700/40">
                        <div class="relative flex-grow flex items-center pl-3">
                            <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input 
                                type="text" 
                                name="q" 
                                value="{{ $query }}" 
                                placeholder="Search by exam code, title, or vendor..." 
                                class="w-full bg-transparent border-0 px-3 py-2 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none font-medium"
                            >
                        </div>
                        <button type="submit" class="bg-[#2563eb] hover:bg-blue-600 text-white font-bold px-7 py-2.5 rounded-xl text-xs sm:text-sm shadow-md transition whitespace-nowrap">
                            Query Database
                        </button>
                    </form>
                </div>

                <!-- Right: Animated Search Nodes & Certification Cards -->
                <div class="lg:col-span-4 relative hidden lg:flex items-center justify-center">
                    <div class="relative w-60 h-44 flex items-center justify-center">
                        <!-- Orbiting Search Node Card 1 -->
                        <div class="cpm-float absolute top-0 left-0 bg-slate-900/90 border border-slate-700/80 rounded-xl px-3 py-2 shadow-xl backdrop-blur-md flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                            <span class="text-xs font-mono-code font-bold text-white">AZ-900</span>
                            <span class="text-[9px] text-emerald-400">Indexed</span>
                        </div>

                        <!-- Orbiting Search Node Card 2 -->
                        <div class="cpm-float-reverse absolute bottom-1 right-0 bg-slate-900/90 border border-slate-700/80 rounded-xl px-3 py-2 shadow-xl backdrop-blur-md flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                            <span class="text-xs font-mono-code font-bold text-white">CCNA 200-301</span>
                        </div>

                        <!-- Center Query Filter Chip -->
                        <div class="px-3.5 py-1.5 rounded-lg bg-slate-800/80 border border-cyan-500/30 text-[10px] font-mono-code text-cyan-300 shadow">
                            Indexed: {{ $exams->total() }} Exams
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-site-b.hero>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- 2-Column Search Layout (Panel 7) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Left Filter Sidebar (Desktop, ~28%) -->
        <aside class="hidden lg:block lg:col-span-4 xl:col-span-3 bg-white border border-gray-200/90 rounded-2xl p-6 shadow-sm sticky top-24">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Filters</h3>
                <a href="{{ url('/search') }}" class="text-[11px] font-semibold text-[#2563eb] hover:underline">Reset</a>
            </div>

            <form action="{{ url('/search') }}" method="GET" class="space-y-6 mt-4">
                <input type="hidden" name="q" value="{{ $query }}">

                <!-- Vendors Filter (Panel 7) -->
                <div>
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Vendors</h4>
                    <div class="space-y-2 text-xs text-slate-600">
                        @php
                            $filterVendors = \App\Models\Vendor::orderBy('name')->take(6)->get();
                        @endphp
                        @foreach($filterVendors as $fv)
                            <label class="flex items-center justify-between hover:text-slate-900 cursor-pointer">
                                <span class="flex items-center gap-2">
                                    <input type="checkbox" name="vendor_id[]" value="{{ $fv->id }}" {{ in_array($fv->id, (array)request('vendor_id', [])) ? 'checked' : '' }} class="rounded border-gray-300 text-[#2563eb] focus:ring-[#2563eb]">
                                    <span>{{ $fv->name }}</span>
                                </span>
                                <span class="text-[11px] text-slate-400 font-mono-code">{{ $fv->exams()->count() }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Difficulty Filter (Panel 7) -->
                <div>
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Difficulty</h4>
                    <div class="space-y-2 text-xs text-slate-600">
                        <label class="flex items-center justify-between hover:text-slate-900 cursor-pointer">
                            <span class="flex items-center gap-2">
                                <input type="radio" name="difficulty" value="" {{ !request('difficulty') ? 'checked' : '' }} class="border-gray-300 text-[#2563eb] focus:ring-[#2563eb]">
                                <span>All Levels</span>
                            </span>
                        </label>
                        <label class="flex items-center justify-between hover:text-slate-900 cursor-pointer">
                            <span class="flex items-center gap-2">
                                <input type="radio" name="difficulty" value="Beginner" {{ request('difficulty') === 'Beginner' ? 'checked' : '' }} class="border-gray-300 text-[#2563eb] focus:ring-[#2563eb]">
                                <span>Beginner</span>
                            </span>
                        </label>
                        <label class="flex items-center justify-between hover:text-slate-900 cursor-pointer">
                            <span class="flex items-center gap-2">
                                <input type="radio" name="difficulty" value="Intermediate" {{ request('difficulty') === 'Intermediate' ? 'checked' : '' }} class="border-gray-300 text-[#2563eb] focus:ring-[#2563eb]">
                                <span>Intermediate</span>
                            </span>
                        </label>
                        <label class="flex items-center justify-between hover:text-slate-900 cursor-pointer">
                            <span class="flex items-center gap-2">
                                <input type="radio" name="difficulty" value="Advanced" {{ request('difficulty') === 'Advanced' ? 'checked' : '' }} class="border-gray-300 text-[#2563eb] focus:ring-[#2563eb]">
                                <span>Advanced</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Price Range Filter (Panel 7) -->
                <div>
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">Price Range</h4>
                    <div class="flex items-center justify-between text-xs text-slate-600 mb-2 font-mono-code">
                        <span>$0</span>
                        <span class="font-bold text-slate-900" x-text="'$' + priceMax">$200</span>
                    </div>
                    <input 
                        type="range" 
                        name="price_max" 
                        min="20" 
                        max="200" 
                        step="10" 
                        x-model="priceMax"
                        class="w-full accent-[#2563eb] cursor-pointer"
                    >
                </div>

                <button type="submit" class="w-full bg-[#2563eb] hover:bg-blue-700 text-white font-bold py-2 rounded-xl text-xs shadow-sm transition">
                    Apply Filters
                </button>
            </form>
        </aside>

        <!-- Right Results Column (~72%, Panel 7) -->
        <main class="lg:col-span-8 xl:col-span-9 space-y-4">
            @forelse($exams as $exam)
                @php
                    $examUrl = $exam->url;
                    $price = $exam->regular_price ?? 49.99;
                @endphp
                <!-- Horizontal Result Card (Panel 7) -->
                <div class="bg-white border border-gray-200/90 rounded-2xl p-5 shadow-sm hover:shadow-md transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 group">
                    <div class="flex items-start gap-4">
                        <!-- Vendor Box (Panel 7) -->
                        <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <span class="font-mono-code font-black text-xs text-slate-800">{{ substr($exam->vendor->name ?? 'EX', 0, 2) }}</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <a href="{{ $examUrl }}" class="font-mono-code font-bold text-sm text-[#2563eb] hover:underline">
                                    {{ $exam->code }}
                                </a>
                                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">&bull; {{ $exam->vendor->name ?? 'Certification' }}</span>
                            </div>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900 mt-0.5 group-hover:text-[#2563eb] transition">
                                <a href="{{ $examUrl }}">{{ $exam->name }}</a>
                            </h3>
                            <div class="flex items-center gap-3 text-xs mt-1.5">
                                <span class="text-amber-500 font-bold">★ 4.8</span>
                                <span class="text-slate-400 text-[11px]">(2,500)</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-slate-500 font-mono-code text-[11px]">{{ $exam->questions_count ?? 400 }}+ Questions</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side: Price & Action Button (Panel 7) -->
                    <div class="flex items-center justify-between w-full sm:w-auto sm:justify-end gap-4 shrink-0 mt-2 sm:mt-0 pt-3 sm:pt-0 border-t sm:border-t-0 border-gray-100">
                        <div class="text-left sm:text-right">
                            <span class="text-lg font-black text-slate-900 font-mono-code">${{ number_format($price, 2) }}</span>
                        </div>
                        <a href="{{ $examUrl }}" class="bg-[#2563eb] hover:bg-blue-700 text-white p-2.5 rounded-xl shadow-sm transition flex items-center justify-center group-hover:scale-105" title="View Exam">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center">
                    <p class="text-sm font-bold text-slate-700">No exams match your search criteria.</p>
                    <p class="text-xs text-slate-500 mt-1">Try broadening your keywords or resetting filters.</p>
                    <a href="{{ url('/search') }}" class="inline-block mt-4 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold px-4 py-2 rounded-lg transition">
                        Reset All Filters
                    </a>
                </div>
            @endforelse

            <!-- Pagination (Panel 7) -->
            @if($exams->hasPages())
                <div class="pt-6">
                    {{ $exams->links() }}
                </div>
            @endif
        </main>
    </div>
</div>
@endsection