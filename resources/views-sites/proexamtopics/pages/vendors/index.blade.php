@extends('layouts.public')

@section('title', 'Certification Vendors — Browse Top IT Exam Providers | ProExamTopics')
@section('meta_description', 'Explore top IT certification vendors including Microsoft, Cisco, AWS, CompTIA, and Google Cloud. Select your vendor and start preparing for exam success.')

@section('content')
<!-- Premium Vendor Discovery Hero (Panel 2) -->
<x-site-b.hero variant="vendors" glowColor="blue" padding="compact">
    <div class="text-center max-w-4xl mx-auto">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/60 text-xs font-semibold text-slate-300 mb-4 backdrop-blur-sm shadow-sm">
            <span class="w-2 h-2 rounded-full bg-[#00D4AA] animate-pulse"></span>
            <span class="font-mono-code text-[#00D4AA] font-bold">VENDOR DISCOVERY HUB</span>
            <span class="text-slate-500">|</span>
            <span>{{ $vendors->count() }} Industry Certified Authorities</span>
        </div>

        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight mb-3">
            Explore Certification <span class="text-[#00E5FF] drop-shadow-[0_0_20px_rgba(0,229,255,0.35)]">Vendors</span>
        </h1>
        <p class="text-slate-300 text-sm sm:text-base max-w-2xl mx-auto mb-8 leading-relaxed">
            Choose your vendor to explore comprehensive certification tracks, current exam blueprints, and verified simulation pools.
        </p>

        <!-- Search & Filter Controls (Panel 2) -->
        <div class="bg-white rounded-2xl shadow-2xl p-2 flex flex-col sm:flex-row items-center gap-2 max-w-2xl mx-auto border border-slate-700/40 mb-10">
            <div class="flex-grow flex items-center pl-3 w-full">
                <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input 
                    type="text" 
                    x-model="search"
                    placeholder="Search vendor name (e.g. Microsoft, AWS, Cisco, CompTIA)..." 
                    class="w-full px-3 py-2 text-slate-800 placeholder-slate-400 focus:outline-none border-0 text-xs sm:text-sm font-medium"
                >
            </div>
            <div class="w-full sm:w-auto border-t sm:border-t-0 sm:border-l border-gray-200 pl-0 sm:pl-2">
                <select x-model="selectedCategory" class="w-full sm:w-44 py-2 px-3 text-xs font-semibold text-slate-700 bg-transparent border-0 focus:outline-none cursor-pointer">
                    <option value="all">All Disciplines</option>
                    <option value="cloud">Cloud Computing</option>
                    <option value="networking">Networking & Systems</option>
                    <option value="security">Cybersecurity</option>
                </select>
            </div>
        </div>

        <!-- Abstract Network of Certification-Provider Nodes & Animated Connections -->
        <div class="relative max-w-3xl mx-auto py-2 hidden sm:block">
            <div class="flex items-center justify-center gap-4 lg:gap-8 flex-wrap">
                <!-- Ecosystem Node 1 -->
                <div class="cpm-float flex items-center gap-2.5 px-4 py-2 rounded-xl bg-slate-900/90 border border-slate-700/80 shadow-lg backdrop-blur-md">
                    <div class="w-7 h-7 rounded-lg bg-white p-1 flex items-center justify-center">
                        <img src="{{ asset('images/vendors/microsoft.svg') }}" alt="Microsoft" class="w-full h-full object-contain">
                    </div>
                    <div class="text-left">
                        <div class="text-xs font-bold text-white leading-none">Microsoft</div>
                        <span class="text-[10px] text-cyan-300 font-mono-code">Azure / 365</span>
                    </div>
                </div>

                <!-- Connector Line SVG 1 -->
                <div class="hidden md:block w-8 h-[1px] bg-gradient-to-r from-cyan-500/50 to-blue-500/50"></div>

                <!-- Ecosystem Node 2 -->
                <div class="cpm-float-reverse flex items-center gap-2.5 px-4 py-2 rounded-xl bg-slate-900/90 border border-slate-700/80 shadow-lg backdrop-blur-md">
                    <div class="w-7 h-7 rounded-lg bg-white p-1 flex items-center justify-center">
                        <img src="{{ asset('images/vendors/amazon.svg') }}" alt="AWS" class="w-full h-full object-contain">
                    </div>
                    <div class="text-left">
                        <div class="text-xs font-bold text-white leading-none">Amazon AWS</div>
                        <span class="text-[10px] text-amber-300 font-mono-code">Cloud Arch</span>
                    </div>
                </div>

                <!-- Connector Line SVG 2 -->
                <div class="hidden md:block w-8 h-[1px] bg-gradient-to-r from-blue-500/50 to-cyan-500/50"></div>

                <!-- Ecosystem Node 3 -->
                <div class="cpm-float flex items-center gap-2.5 px-4 py-2 rounded-xl bg-slate-900/90 border border-slate-700/80 shadow-lg backdrop-blur-md">
                    <div class="w-7 h-7 rounded-lg bg-white p-1 flex items-center justify-center">
                        <img src="{{ asset('images/vendors/cisco.svg') }}" alt="Cisco" class="w-full h-full object-contain">
                    </div>
                    <div class="text-left">
                        <div class="text-xs font-bold text-white leading-none">Cisco</div>
                        <span class="text-[10px] text-blue-300 font-mono-code">CCNA / CCNP</span>
                    </div>
                </div>

                <!-- Connector Line SVG 3 -->
                <div class="hidden md:block w-8 h-[1px] bg-gradient-to-r from-cyan-500/50 to-emerald-500/50"></div>

                <!-- Ecosystem Node 4 -->
                <div class="cpm-float-reverse flex items-center gap-2.5 px-4 py-2 rounded-xl bg-slate-900/90 border border-slate-700/80 shadow-lg backdrop-blur-md">
                    <div class="w-7 h-7 rounded-lg bg-white p-1 flex items-center justify-center">
                        <img src="{{ asset('images/vendors/comptia.svg') }}" alt="CompTIA" class="w-full h-full object-contain">
                    </div>
                    <div class="text-left">
                        <div class="text-xs font-bold text-white leading-none">CompTIA</div>
                        <span class="text-[10px] text-emerald-300 font-mono-code">Security+</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-site-b.hero>

<!-- Vendor Grid Section (Panel 2) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" x-data="{ search: '' }">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($vendors as $vendor)
            <div class="bg-white border border-gray-200/90 rounded-xl p-6 shadow-sm hover:shadow-md transition text-center flex flex-col justify-between group">
                <div>
                    <!-- Vendor Icon / Emblem -->
                    @php
                        $vendorSvgPath = 'images/vendors/' . $vendor->slug . '.svg';
                        $hasVendorSvg = file_exists(public_path($vendorSvgPath));
                    @endphp
                    <div class="w-16 h-16 mx-auto rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center p-2.5 mb-4 group-hover:scale-105 transition-transform shadow-xs">
                        @if($hasVendorSvg)
                            <img src="{{ asset($vendorSvgPath) }}" alt="{{ $vendor->name }}" class="max-w-full max-h-full object-contain">
                        @elseif($vendor->logo_path)
                            <img src="{{ asset($vendor->logo_path) }}" alt="{{ $vendor->name }}" class="max-w-full max-h-full object-contain">
                        @else
                            <span class="font-black text-lg text-slate-700 font-mono-code">{{ substr($vendor->name, 0, 2) }}</span>
                        @endif
                    </div>

                    <h2 class="text-base font-bold text-slate-900 group-hover:text-[#2563eb] transition-colors">
                        {{ $vendor->name }}
                    </h2>

                    <div class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-[#2563eb]">
                        {{ $vendor->exams_count ?? $vendor->exams()->count() }}+ Exams
                    </div>

                    <p class="text-xs text-slate-500 mt-3 line-clamp-2">
                        {{ $vendor->description ?? 'Industry-standard certification practice materials and interactive simulator.' }}
                    </p>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    <a href="{{ url('/vendors/' . $vendor->slug) }}" class="inline-flex items-center text-xs font-bold text-[#2563eb] group-hover:text-blue-700 transition">
                        <span>Browse Exams</span>
                        <svg class="w-3.5 h-3.5 ml-1 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Can't find your vendor? Request Vendor CTA (Panel 2) -->
    <div class="mt-14 bg-blue-50/70 border border-blue-100 rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-sm">
        <div class="flex items-center gap-4 text-left">
            <div class="w-12 h-12 rounded-xl bg-[#2563eb]/10 text-[#2563eb] flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Can't find your vendor?</h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-0.5">Let us know and our certified curriculum team will prepare the exam materials for you.</p>
            </div>
        </div>
        <a href="{{ url('/contact') }}" class="bg-[#2563eb] hover:bg-blue-700 text-white text-xs sm:text-sm font-bold px-6 py-2.5 rounded-lg shadow-sm transition shrink-0">
            Request Vendor
        </a>
    </div>
</section>
@endsection