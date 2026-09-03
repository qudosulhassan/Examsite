@extends('layouts.public')

@section('title', 'All IT Certifications & Study Guides - ExamsNinja')
@section('meta_description', 'Browse our comprehensive directory of IT certifications grouped by vendors like Microsoft, AWS, Cisco, and more. Find the perfect study guide and practice exams.')
@section('meta_keywords', 'IT certifications, certification paths, microsoft certifications, aws certifications, cisco certifications, comptia')
@section('canonical_url', route('certifications.index'))
@section('og_type', 'website')

@section('content')
<!-- Hero Section (Premium Deep Space) -->
<section class="bg-gradient-to-br from-[#07101E] via-navy to-[#0F172A] text-white pt-20 pb-32 relative overflow-hidden">
    <!-- Abstract glowing orbs -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-cyan rounded-full mix-blend-screen filter blur-[120px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[120px] opacity-20"></div>
    <!-- Abstract Tech Lines -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 64px 64px;"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Breadcrumbs -->
        <div class="mb-8 flex justify-center">
            <x-breadcrumbs :links="[
                ['name' => 'Home', 'url' => '/'],
                ['name' => 'Certifications', 'url' => '']
            ]" />
        </div>
        
        <!-- H1 / H2 -->
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight mb-6 max-w-4xl mx-auto leading-tight">
            Official Certification <span class="text-cyan">Paths</span>
        </h1>
        <p class="text-lg sm:text-xl text-gray-300 max-w-3xl mx-auto leading-relaxed font-light">
            Explore industry-recognized IT certifications grouped by vendor. Find your next career milestone and start preparing with our premium practice engines.
        </p>
    </div>
</section>

<!-- Certifications Hub Listing & Search -->
<section class="py-16 bg-gray-50 relative -mt-16 z-20 min-h-screen" x-data="{ 
    searchQuery: '',
    vendorBlocks: [],
    init() {
        this.vendorBlocks = Array.from(document.querySelectorAll('.vendor-block')).map(block => {
            return {
                el: block,
                text: block.innerText.toLowerCase()
            }
        });
    },
    get filteredBlocks() {
        if (this.searchQuery === '') {
            this.vendorBlocks.forEach(v => v.el.style.display = 'block');
            return;
        }
        const query = this.searchQuery.toLowerCase();
        this.vendorBlocks.forEach(v => {
            if (v.text.includes(query)) {
                v.el.style.display = 'block';
            } else {
                v.el.style.display = 'none';
            }
        });
    }
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Interactive Search Bar (Glassmorphic) -->
        <div class="max-w-3xl mx-auto mb-20 relative transform -translate-y-8">
            <!-- Ambient Glow -->
            <div class="absolute inset-0 bg-gradient-to-r from-cyan via-blue-500 to-purple-600 rounded-3xl blur-xl opacity-30 group-hover:opacity-50 transition duration-500"></div>
            
            <div class="relative rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.1)] group bg-white/95 backdrop-blur-xl border-2 border-white/60 focus-within:border-cyan focus-within:ring-4 focus-within:ring-cyan/20 transition-all flex items-center overflow-hidden">
                <div class="pl-8 text-gray-400 group-focus-within:text-cyan transition-colors">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" 
                       x-model="searchQuery" 
                       @input="filteredBlocks"
                       placeholder="Find certifications (e.g. CCNA, Azure)..." 
                       class="w-full py-6 px-5 text-navy placeholder-gray-400 focus:outline-none text-xl font-black bg-transparent tracking-wide">
                
                <div class="pr-6 flex items-center space-x-3">
                    <button class="bg-navy hover:bg-cyan text-white font-bold py-3 px-8 rounded-xl transition-colors shadow-md hidden sm:block">
                        Search
                    </button>
                </div>
            </div>
        </div>

        @if($vendors->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                @foreach($vendors as $vendor)
                    <div class="vendor-block group bg-white rounded-[28px] border border-gray-100 shadow-[0_15px_40px_rgba(0,0,0,0.03)] overflow-hidden flex flex-col transition-all duration-500 hover:shadow-[0_20px_50px_rgba(0,212,170,0.1)] hover:border-cyan/30 hover:-translate-y-2 relative">
                        
                        <!-- Hover Glow Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-br from-cyan/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                        <!-- Top Accent Line -->
                        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-cyan to-blue-500 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-500"></div>

                        <!-- Vendor Header Card -->
                        <div class="p-8 border-b border-gray-100 bg-white flex items-center space-x-5 relative z-10">
                            @php
                                $logoStyles = [
                                    'microsoft' => ['bg' => 'bg-slate-50 border-gray-200', 'html' => '<div class="grid grid-cols-2 gap-0.5 w-6 h-6"><div class="bg-red-500 w-2.5 h-2.5"></div><div class="bg-green-500 w-2.5 h-2.5"></div><div class="bg-blue-500 w-2.5 h-2.5"></div><div class="bg-yellow-500 w-2.5 h-2.5"></div></div>'],
                                    'amazon-web-services-aws' => ['bg' => 'bg-zinc-900 border-zinc-700', 'html' => '<div class="flex flex-col items-center justify-center"><span class="text-[10px] tracking-widest font-extrabold text-white leading-none">AWS</span><svg class="w-6 h-2 text-amber-500 -mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 8"><path stroke-linecap="round" d="M2 2c6 4 14 4 20 0m-2.5 1.5L22 2l-3.5-1" /></svg></div>'],
                                    'google-cloud-platform-gcp' => ['bg' => 'bg-white border-gray-150', 'html' => '<div class="flex items-center space-x-0.5"><span class="text-blue-500 font-extrabold text-sm">G</span><span class="text-red-500 font-extrabold text-sm">C</span><span class="text-yellow-500 font-extrabold text-sm">P</span></div>'],
                                    'cisco' => ['bg' => 'bg-sky-50 border-sky-200', 'html' => '<div class="flex items-end justify-center space-x-0.5 h-6"><div class="bg-sky-650 w-[2px] h-2 rounded-full"></div><div class="bg-sky-650 w-[2px] h-3 rounded-full"></div><div class="bg-sky-650 w-[2px] h-4 rounded-full"></div><div class="bg-sky-650 w-[2px] h-3 rounded-full"></div><div class="bg-sky-650 w-[2px] h-2 rounded-full"></div></div>'],
                                ];
                                $style = $logoStyles[$vendor->slug] ?? ['bg' => 'bg-slate-100 border-gray-200', 'html' => '<span class="text-sm font-bold uppercase text-gray-700">' . substr($vendor->name, 0, 2) . '</span>'];
                            @endphp
                            
                            @if($vendor->logo_url)
                                <div class="h-16 w-16 rounded-2xl shadow-[0_5px_15px_rgba(0,0,0,0.05)] border border-gray-100 flex items-center justify-center group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-500 bg-white p-2.5 shrink-0">
                                    <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->name }}" class="max-h-full max-w-full object-contain">
                                </div>
                            @else
                                <div class="h-16 w-16 rounded-2xl flex items-center justify-center border {{ $style['bg'] }} shrink-0 shadow-[0_5px_15px_rgba(0,0,0,0.05)] group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-500">
                                    {!! $style['html'] !!}
                                </div>
                            @endif
                            
                            <div class="flex-grow">
                                <h2 class="text-2xl font-black text-navy group-hover:text-cyan transition-colors leading-tight">
                                    <a href="{{ route('vendors.show', $vendor->slug) }}" class="focus:outline-none focus:underline">{{ $vendor->name }}</a>
                                </h2>
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-black mt-2 bg-gray-50 inline-block px-2.5 py-1 rounded-md border border-gray-100">{{ $vendor->certifications->count() }} Certifications</p>
                            </div>
                        </div>

                        <!-- Certifications List -->
                        <div class="p-6 flex-grow flex flex-col relative z-10 bg-gray-50/50">
                            <ul class="space-y-4">
                                @foreach($vendor->certifications as $cert)
                                    <li>
                                        <a href="{{ route('certifications.show', $cert->slug) }}" class="block p-5 rounded-2xl border border-white bg-white shadow-[0_5px_15px_rgba(0,0,0,0.02)] hover:border-cyan/30 hover:bg-cyan/5 hover:shadow-[0_10px_25px_rgba(0,212,170,0.1)] transition-all duration-300 group/item transform hover:-translate-y-1">
                                            <div class="flex items-center justify-between">
                                                <div class="pr-5">
                                                    <h3 class="text-[15px] font-black text-navy group-hover/item:text-cyan transition-colors leading-snug">{{ $cert->name }}</h3>
                                                    @if($cert->description)
                                                        <p class="text-[13px] text-gray-500 mt-2 line-clamp-1 font-medium leading-relaxed">{{ $cert->description }}</p>
                                                    @endif
                                                </div>
                                                <div class="shrink-0">
                                                    <div class="w-10 h-10 rounded-xl bg-gray-50 group-hover/item:bg-gradient-to-r group-hover/item:from-cyan group-hover/item:to-blue-500 flex items-center justify-center transition-all duration-300 shadow-sm group-hover/item:shadow-[0_5px_15px_rgba(0,212,170,0.3)]">
                                                        <svg class="w-5 h-5 text-gray-400 group-hover/item:text-white transform group-hover/item:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        
                        <!-- Footer Action -->
                        <div class="p-6 bg-white mt-auto text-center border-t border-gray-100 relative z-10 flex justify-center">
                            <a href="{{ route('vendors.show', $vendor->slug) }}" class="text-[13px] font-black text-white bg-navy hover:bg-gradient-to-r hover:from-cyan hover:to-blue-500 px-6 py-3 rounded-xl transition-all duration-300 flex items-center justify-center space-x-2 shadow-md hover:shadow-[0_5px_20px_rgba(0,212,170,0.3)] w-full group/btn">
                                <span>Browse all {{ $vendor->name }}</span>
                                <svg class="w-4 h-4 transform group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white border border-gray-200 rounded-3xl p-16 text-center text-gray-500 shadow-sm max-w-3xl mx-auto">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <h3 class="text-2xl font-black text-navy mb-2">No Certifications Found</h3>
                <p class="text-gray-500 font-medium">We are currently updating our certification database. Please check back soon.</p>
                <a href="{{ url('/vendors') }}" class="mt-8 inline-block bg-navy hover:bg-cyan text-white font-bold py-3 px-8 rounded-xl transition-colors shadow-md">Browse Vendors Instead</a>
            </div>
        @endif
        
    </div>
</section>
@endsection
