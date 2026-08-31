@extends('layouts.public')

@section('title', 'Browse IT Certification Providers - ExamsNinja')

@section('content')
<!-- Hero Section (Premium Deep Space) -->
<section class="bg-gradient-to-br from-[#07101E] via-navy to-[#0F172A] text-white pt-20 pb-32 relative overflow-hidden">
    <!-- Abstract glowing orbs -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-cyan rounded-full mix-blend-screen filter blur-[120px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[120px] opacity-20"></div>
    <!-- Abstract Tech Lines -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 64px 64px;"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <!-- H1 / H2 -->
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight mb-6 max-w-4xl mx-auto leading-tight">
            Browse IT Certification <span class="text-cyan">Providers</span>
        </h1>
        <p class="text-lg sm:text-xl text-gray-300 max-w-3xl mx-auto leading-relaxed font-light">
            Choose your exam vendor below to find updated study guides, verified question banks, and dynamic test engines.
        </p>
    </div>
</section>

<!-- Vendor Grid & Search -->
<section class="py-16 bg-gray-50 relative -mt-16 z-20" x-data="{ 
    searchQuery: '',
    vendors: [],
    init() {
        this.vendors = Array.from(document.querySelectorAll('.vendor-card')).map(card => {
            return {
                el: card,
                name: card.dataset.name.toLowerCase()
            }
        });
    },
    get filteredVendors() {
        if (this.searchQuery === '') {
            this.vendors.forEach(v => v.el.style.display = 'flex');
            return;
        }
        const query = this.searchQuery.toLowerCase();
        this.vendors.forEach(v => {
            if (v.name.includes(query)) {
                v.el.style.display = 'flex';
            } else {
                v.el.style.display = 'none';
            }
        });
    }
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Interactive Search Bar -->
        <div class="max-w-3xl mx-auto mb-20 relative transform -translate-y-8">
            <!-- Ambient Glow -->
            <div class="absolute inset-0 bg-gradient-to-r from-cyan via-blue-500 to-purple-600 rounded-3xl blur-xl opacity-30 group-hover:opacity-50 transition duration-500"></div>
            
            <div class="relative rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.1)] group bg-white/95 backdrop-blur-xl border-2 border-white/60 focus-within:border-cyan focus-within:ring-4 focus-within:ring-cyan/20 transition-all flex items-center overflow-hidden">
                <div class="pl-8 text-gray-400 group-focus-within:text-cyan transition-colors">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" 
                       x-model="searchQuery" 
                       @input="filteredVendors"
                       placeholder="Find your vendor (e.g. AWS, Cisco)..." 
                       class="w-full py-6 px-5 text-navy placeholder-gray-400 focus:outline-none text-xl font-black bg-transparent tracking-wide">
                
                <div class="pr-6 flex items-center space-x-3">
                    <!-- Search Button -->
                    <button class="bg-navy hover:bg-cyan text-white font-bold py-3 px-8 rounded-xl transition-colors shadow-md hidden sm:block">
                        Search
                    </button>
                </div>
            </div>
        </div>

        <!-- Vendors Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($vendors as $vendor)
                <div class="vendor-card group bg-white border border-gray-100 rounded-[28px] p-8 flex flex-col justify-between shadow-[0_15px_40px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_50px_rgba(0,212,170,0.1)] hover:border-cyan/30 hover:-translate-y-2 transition-all duration-500 relative overflow-hidden" data-name="{{ $vendor->name }}">
                    <!-- Hover Accent Line -->
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-cyan to-blue-500 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-500"></div>
                    
                    <!-- Hover Glow Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                    <div class="flex items-center space-x-5 mb-5 relative z-10">
                        @php
                            $logoStyles = [
                                'microsoft' => [
                                    'bg' => 'bg-slate-50 border-gray-200', 
                                    'html' => '<div class="grid grid-cols-2 gap-0.5 w-6 h-6"><div class="bg-red-500 w-2.5 h-2.5"></div><div class="bg-green-500 w-2.5 h-2.5"></div><div class="bg-blue-500 w-2.5 h-2.5"></div><div class="bg-yellow-500 w-2.5 h-2.5"></div></div>'
                                ],
                                'amazon-web-services-aws' => [
                                    'bg' => 'bg-zinc-900 border-zinc-700', 
                                    'html' => '<div class="flex flex-col items-center justify-center"><span class="text-[10px] tracking-widest font-extrabold text-white leading-none">AWS</span><svg class="w-6 h-2 text-amber-500 -mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 8"><path stroke-linecap="round" d="M2 2c6 4 14 4 20 0m-2.5 1.5L22 2l-3.5-1" /></svg></div>'
                                ],
                                'google-cloud-platform-gcp' => [
                                    'bg' => 'bg-white border-gray-150', 
                                    'html' => '<div class="flex items-center space-x-0.5"><span class="text-blue-500 font-extrabold text-sm">G</span><span class="text-red-500 font-extrabold text-sm">C</span><span class="text-yellow-500 font-extrabold text-sm">P</span></div>'
                                ],
                                'cisco' => [
                                    'bg' => 'bg-sky-50 border-sky-200', 
                                    'html' => '<div class="flex items-end justify-center space-x-0.5 h-6"><div class="bg-sky-650 w-0.5 h-2 rounded-full"></div><div class="bg-sky-650 w-0.5 h-3 rounded-full"></div><div class="bg-sky-650 w-0.5 h-4.5 rounded-full"></div><div class="bg-sky-650 w-0.5 h-3 rounded-full"></div><div class="bg-sky-650 w-0.5 h-2 rounded-full"></div></div>'
                                ],
                                'comptia' => [
                                    'bg' => 'bg-emerald-50 border-emerald-250', 
                                    'html' => '<span class="text-xs font-black tracking-tighter text-emerald-700">CompTIA</span>'
                                ],
                                'salesforce' => [
                                    'bg' => 'bg-sky-50 border-sky-100', 
                                    'html' => '<svg class="w-7 h-7 text-sky-500" fill="currentColor" viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z"/></svg>'
                                ],
                                'oracle' => [
                                    'bg' => 'bg-red-50 border-red-150', 
                                    'html' => '<span class="text-xs font-serif font-black tracking-tight uppercase text-red-600">Oracle</span>'
                                ],
                                'red-hat' => [
                                    'bg' => 'bg-red-950 border-red-900', 
                                    'html' => '<span class="text-[10px] font-bold font-sans text-white">RedHat</span>'
                                ],
                                'vmware' => [
                                    'bg' => 'bg-slate-50 border-teal-250', 
                                    'html' => '<span class="text-xs font-bold tracking-tight text-teal-600 font-mono">vmware</span>'
                                ],
                                'project-management-institute-pmi' => [
                                    'bg' => 'bg-purple-50 border-purple-200', 
                                    'html' => '<span class="text-xs font-extrabold tracking-tighter text-purple-700">PMI</span>'
                                ],
                                'isaca' => [
                                    'bg' => 'bg-indigo-50 border-indigo-200', 
                                    'html' => '<span class="text-xs font-extrabold tracking-tighter uppercase text-indigo-700">ISACA</span>'
                                ],
                                'itil' => [
                                    'bg' => 'bg-green-50 border-green-200', 
                                    'html' => '<span class="text-xs font-extrabold font-mono tracking-tighter text-green-700">ITIL</span>'
                                ],
                                'palo-alto' => [
                                    'bg' => 'bg-orange-50 border-orange-200', 
                                    'html' => '<span class="text-[9px] font-black tracking-tighter uppercase text-orange-700">PaloAlto</span>'
                                ],
                                'fortinet' => [
                                    'bg' => 'bg-red-50 border-red-200', 
                                    'html' => '<span class="text-xs font-black uppercase text-red-700">Forti</span>'
                                ]
                            ];
                            $style = $logoStyles[$vendor->slug] ?? [
                                'bg' => 'bg-slate-100 border-gray-200', 
                                'html' => '<span class="text-sm font-bold uppercase text-gray-700">' . substr($vendor->name, 0, 2) . '</span>'
                            ];
                        @endphp
                        @if($vendor->logo_path)
                            <div class="h-16 w-16 rounded-2xl shadow-[0_5px_15px_rgba(0,0,0,0.05)] border border-gray-100 flex items-center justify-center group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-500 bg-white p-2.5 shrink-0">
                                <img src="{{ $vendor->logo_path }}" alt="{{ $vendor->name }}" class="max-h-full max-w-full object-contain">
                            </div>
                        @else
                            <div class="h-16 w-16 rounded-2xl shadow-[0_5px_15px_rgba(0,0,0,0.05)] border border-gray-100 flex items-center justify-center group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-500 shrink-0 {{ $style['bg'] }}">
                                {!! $style['html'] !!}
                            </div>
                        @endif
                        <div>
                            <h3 class="font-black text-navy text-xl group-hover:text-cyan transition-colors leading-tight">{{ $vendor->name }}</h3>
                        </div>
                    </div>
                    
                    <p class="text-[14px] text-gray-500 mb-8 line-clamp-3 relative z-10 font-medium leading-relaxed mt-2">
                        {{ $vendor->description }}
                    </p>

                    <div class="flex justify-between items-center pt-6 border-t border-gray-100 mt-auto relative z-10">
                        <span class="bg-gray-50 text-gray-400 group-hover:bg-cyan/10 group-hover:text-cyan text-[11px] uppercase tracking-widest font-black px-3 py-1.5 rounded-lg border border-transparent group-hover:border-cyan/20 transition-colors">{{ $vendor->exam_count }} Exams</span>
                        <a href="{{ url('/vendors/' . $vendor->slug) }}" class="text-[13px] font-black text-white bg-navy group-hover:bg-gradient-to-r group-hover:from-cyan group-hover:to-blue-500 px-5 py-2.5 rounded-xl transition-all duration-300 flex items-center space-x-2 shadow-md group-hover:shadow-[0_0_20px_rgba(0,212,170,0.3)] group/btn">
                            <span>Browse</span>
                            <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($vendors->isEmpty())
            <div class="text-center py-20 bg-white rounded-2xl border border-gray-200">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <h3 class="text-2xl font-black text-navy mb-2">No Vendors Found</h3>
                <p class="text-gray-500">We are currently updating our database with new certification providers.</p>
            </div>
        @endif
    </div>
</section>
@endsection
