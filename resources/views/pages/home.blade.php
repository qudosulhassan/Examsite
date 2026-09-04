@extends('layouts.public')

@section('title', 'Exam Topics Base - Pass Your IT Certification Exam First Attempt')

@section('seo_tags')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "WebSite",
  "name": "Exam Topics Base",
  "url": "{{ url('/') }}",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "{{ url('/search') }}?q={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>
@endsection

@section('content')
<!-- Hero Section (Premium Deep Space) -->
<section class="bg-gradient-to-br from-[#07101E] via-navy to-[#0F172A] text-white pt-16 pb-20 relative overflow-hidden">
    <!-- Abstract glowing orbs -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-cyan rounded-full mix-blend-screen filter blur-[120px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[120px] opacity-20"></div>

    <!-- Abstract shuriken background decorations -->
    <div class="absolute right-0 top-0 opacity-[0.03] transform translate-x-1/3 -translate-y-1/4 select-none pointer-events-none">
        <svg class="h-[800px] w-[800px] text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2L14.5 9.5H22L16 14L18.5 21.5L12 17L5.5 21.5L8 14L2 9.5H9.5L12 2Z" />
        </svg>
    </div>

    <div class="container-custom relative z-10 text-center">
        <!-- Premium Badge -->
        <div class="inline-flex items-center space-x-2 bg-white/5 backdrop-blur-sm border border-cyan/30 text-cyan text-xs font-bold px-4 py-2 rounded-full mb-8 shadow-[0_0_20px_rgba(0,212,170,0.2)]">
            <span class="flex h-2.5 w-2.5 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-cyan"></span>
            </span>
            <span class="tracking-wider uppercase">June 2026 Verified Updates</span>
        </div>

        <!-- H1 / H2 -->
        <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black tracking-tight mb-6 max-w-4xl mx-auto leading-[1.1]">
            Pass Your IT Certification Exam on the <span class="text-cyan">First Attempt</span>
        </h1>
        <p class="text-lg sm:text-2xl text-gray-300 mb-12 max-w-3xl mx-auto leading-relaxed font-light">
            Exam Topics Base delivers verified exam dumps, PDF study guides, and a powerful web-based test engine for every major vendor.
        </p>

        <!-- Search Bar with Live Suggestions (Alpine.js) -->
        <div class="max-w-2xl mx-auto mb-12 relative" x-data="{
            query: '',
            results: { exams: [], vendors: [] },
            loading: false,
            open: false,
            fetchResults() {
                if (this.query.length < 2) {
                    this.results = { exams: [], vendors: [] };
                    this.open = false;
                    return;
                }
                this.loading = true;
                fetch('{{ url('/api/search') }}?q=' + encodeURIComponent(this.query))
                    .then(res => res.json())
                    .then(data => {
                        this.results = data;
                        this.loading = false;
                        this.open = true;
                    })
                    .catch(() => {
                        this.loading = false;
                    });
            }
        }">
            <!-- Glassmorphic Search Input -->
            <div class="relative rounded-2xl shadow-[0_15px_35px_rgba(0,0,0,0.5)] group">
                <div class="absolute inset-0 bg-gradient-to-r from-cyan via-blue-500 to-purple-600 rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-500"></div>
                <input type="text" 
                       x-model="query" 
                       @input.debounce.300ms="fetchResults()"
                       @focus="if(query.length >= 2) open = true"
                       @click.away="open = false"
                       placeholder="Search by exam code (e.g. AZ-900, CLF-C02)..." 
                       class="relative w-full pl-6 pr-14 py-5 rounded-2xl bg-white/10 backdrop-blur-md text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cyan focus:bg-white/15 text-base sm:text-lg border border-white/10 transition-all duration-300 font-medium">
                <div class="absolute right-5 top-1/2 transform -translate-y-1/2 flex items-center space-x-2 z-10">
                    <template x-if="loading">
                        <svg class="animate-spin h-6 w-6 text-cyan" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <svg class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Live Dropdown Results -->
            <div x-show="open" 
                 class="absolute left-0 right-0 mt-3 bg-white/95 backdrop-blur-xl rounded-xl shadow-[0_20px_50px_rgba(0,0,0,0.5)] text-navy text-left overflow-hidden z-50 border border-white/20" 
                 style="display: none;">
                
                <!-- Vendors Section -->
                <div x-show="results.vendors.length > 0" class="border-b border-gray-150">
                    <div class="bg-gray-50/80 px-5 py-2.5 text-xs font-bold tracking-widest text-gray-400 uppercase">Vendors</div>
                    <template x-for="vendor in results.vendors">
                        <a :href="vendor.url" class="block px-5 py-3 hover:bg-cyan/10 transition text-sm font-bold text-navy">
                            <span x-text="vendor.name"></span>
                        </a>
                    </template>
                </div>

                <!-- Exams Section -->
                <div x-show="results.exams.length > 0">
                    <div class="bg-gray-50/80 px-5 py-2.5 text-xs font-bold tracking-widest text-gray-400 uppercase">Exams</div>
                    <template x-for="exam in results.exams">
                        <a :href="exam.url" class="block px-5 py-3 hover:bg-cyan/10 transition border-b border-gray-100 last:border-0 group">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-xs font-black bg-cyan/20 text-navy px-2 py-0.5 rounded-md mr-2 group-hover:bg-cyan group-hover:text-white transition" x-text="exam.code"></span>
                                    <span class="text-sm font-bold text-gray-800 group-hover:text-navy transition" x-text="exam.name"></span>
                                </div>
                                <span class="text-xs font-medium text-gray-400" x-text="exam.vendor"></span>
                            </div>
                        </a>
                    </template>
                </div>

                <!-- No Results State -->
                <div x-show="results.exams.length === 0 && results.vendors.length === 0" class="px-5 py-8 text-center text-sm font-bold text-gray-400">
                    No certifications found. Try another search term.
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-6 mb-16 relative z-10">
            <a href="{{ url('/vendors') }}" class="w-full sm:w-auto bg-gradient-to-r from-orange to-red-500 hover:from-red-500 hover:to-orange text-white px-10 py-4 rounded-xl text-base font-black uppercase tracking-wide shadow-[0_0_20px_rgba(255,107,53,0.4)] transition-all duration-300 transform hover:scale-105">
                Browse Exams
            </a>
            <a href="{{ url('/free-demo') }}" class="w-full sm:w-auto bg-white/5 backdrop-blur-sm border-2 border-cyan/50 hover:border-cyan hover:bg-cyan/10 text-white px-10 py-4 rounded-xl text-base font-bold tracking-wide transition-all duration-300">
                Download Free Demo
            </a>
        </div>

        <!-- Glassmorphic Trust Bar -->
        <div class="flex flex-wrap justify-center gap-4 text-xs sm:text-sm font-semibold tracking-wide text-gray-200">
            <div class="bg-white/5 backdrop-blur-md border border-white/10 px-5 py-2.5 rounded-full flex items-center space-x-2 shadow-lg">
                <span class="text-cyan text-lg">✦</span>
                <span>3,500+ Exams</span>
            </div>
            <div class="bg-white/5 backdrop-blur-md border border-white/10 px-5 py-2.5 rounded-full flex items-center space-x-2 shadow-lg">
                <span class="text-cyan text-lg">✦</span>
                <span>200,000+ Students</span>
            </div>
            <div class="bg-white/5 backdrop-blur-md border border-white/10 px-5 py-2.5 rounded-full flex items-center space-x-2 shadow-lg">
                <span class="text-cyan text-lg">✦</span>
                <span>99.6% Pass Rate</span>
            </div>
            <div class="bg-white/5 backdrop-blur-md border border-white/10 px-5 py-2.5 rounded-full flex items-center space-x-2 shadow-lg">
                <span class="text-cyan text-lg">✦</span>
                <span>30-Day Money Back</span>
            </div>
        </div>
    </div>
</section>

<!-- Stats & Impact Section -->
<section class="py-16 bg-navy relative border-t border-white/5 z-20 shadow-[0_-20px_50px_rgba(0,0,0,0.5)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-white/10">
            <div class="px-4">
                <div class="text-4xl font-black text-cyan mb-2 drop-shadow-[0_0_15px_rgba(0,212,170,0.3)]">99.8%</div>
                <div class="text-xs font-bold tracking-widest text-gray-400 uppercase">First-Attempt Pass Rate</div>
            </div>
            <div class="px-4">
                <div class="text-4xl font-black text-cyan mb-2 drop-shadow-[0_0_15px_rgba(0,212,170,0.3)]">3,500+</div>
                <div class="text-xs font-bold tracking-widest text-gray-400 uppercase">Active Certification Exams</div>
            </div>
            <div class="px-4">
                <div class="text-4xl font-black text-cyan mb-2 drop-shadow-[0_0_15px_rgba(0,212,170,0.3)]">250k+</div>
                <div class="text-xs font-bold tracking-widest text-gray-400 uppercase">Successful Students</div>
            </div>
            <div class="px-4">
                <div class="text-4xl font-black text-cyan mb-2 drop-shadow-[0_0_15px_rgba(0,212,170,0.3)]">24/7</div>
                <div class="text-xs font-bold tracking-widest text-gray-400 uppercase">Expert Technical Support</div>
            </div>
        </div>
    </div>
</section>

<!-- Vendor Grid Section -->
<section class="py-24 bg-gray-50 relative overflow-hidden">
    <!-- Subtle background gradient -->
    <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-white to-transparent pointer-events-none"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-black text-navy sm:text-4xl mb-4 tracking-tight">Supported Certification Providers</h2>
            <p class="text-lg text-gray-500 max-w-2xl mx-auto font-medium">Get access to premium dumps and testing tools for all major IT certification vendors.</p>
        </div>

        <!-- Vendors Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-6">
            @foreach($vendors as $vendor)
                <a href="{{ url('/vendors/' . $vendor->slug) }}" class="group bg-white rounded-2xl p-6 flex flex-col items-center text-center shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 relative overflow-hidden border border-gray-100">
                    <!-- Hover Glow Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    
                    <!-- Placeholder Logo replacement -->
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
                    @if($vendor->logo_url)
                        <div class="h-14 w-14 rounded-xl border border-gray-100 flex items-center justify-center mb-4 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 bg-white p-2 shadow-sm shrink-0">
                            <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->name }}" class="max-h-full max-w-full object-contain">
                        </div>
                    @else
                        <div class="h-14 w-14 rounded-xl border flex items-center justify-center mb-4 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300 {{ $style['bg'] }} shadow-sm shrink-0">
                            {!! $style['html'] !!}
                        </div>
                    @endif
                    <h3 class="font-bold text-navy text-sm mb-2 group-hover:text-cyan transition-colors">{{ $vendor->name }}</h3>
                    <span class="bg-gray-100 text-gray-500 group-hover:bg-cyan/10 group-hover:text-cyan text-xs font-bold px-3 py-1 rounded-full transition-colors">{{ $vendor->exam_count }} Exams</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

<!-- How It Works Section (Premium Dark Mode Flow) -->
<section class="py-24 bg-navy border-t border-gray-900 relative overflow-hidden">
    <!-- Abstract Tech Lines -->
    <div class="absolute inset-0 opacity-5 pointer-events-none" style="background-image: radial-gradient(#00d4aa 1px, transparent 1px); background-size: 32px 32px;"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-20">
            <h2 class="text-3xl font-black text-white sm:text-4xl mb-4 tracking-tight">How Exam Topics Base Works</h2>
            <p class="text-lg text-gray-400 max-w-2xl mx-auto">Prepare effectively and guarantee your exam success in three simple steps.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
            <!-- Connector Line (Desktop Only) -->
            <div class="hidden md:block absolute top-10 left-[16.66%] w-[66.66%] h-1 bg-gradient-to-r from-cyan via-purple-500 to-green-500 opacity-20 rounded-full"></div>

            <div class="text-center space-y-5 relative group">
                <div class="h-20 w-20 bg-gray-800 border-2 border-cyan text-cyan mx-auto rounded-full flex items-center justify-center shadow-[0_0_30px_rgba(0,212,170,0.4)] group-hover:scale-110 transition-transform duration-500 z-10 relative">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-white tracking-tight">1. Locate Your Exam</h3>
                <p class="text-gray-400 text-sm leading-relaxed max-w-xs mx-auto">Search from our library of 3,500+ IT certification exams covering AWS, Microsoft, Cisco, CompTIA, and more.</p>
            </div>

            <div class="text-center space-y-5 relative group">
                <div class="h-20 w-20 bg-gray-800 border-2 border-purple-500 text-purple-400 mx-auto rounded-full flex items-center justify-center shadow-[0_0_30px_rgba(168,85,247,0.4)] group-hover:scale-110 transition-transform duration-500 z-10 relative">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-white tracking-tight">2. Download / Engine</h3>
                <p class="text-gray-400 text-sm leading-relaxed max-w-xs mx-auto">Instantly access your study guides on Cloudflare R2 or launch our interactive, browser-based online testing engine.</p>
            </div>

            <div class="text-center space-y-5 relative group">
                <div class="h-20 w-20 bg-gray-800 border-2 border-green-500 text-green-400 mx-auto rounded-full flex items-center justify-center shadow-[0_0_30px_rgba(34,197,94,0.4)] group-hover:scale-110 transition-transform duration-500 z-10 relative">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-white tracking-tight">3. Pass Confidently</h3>
                <p class="text-gray-400 text-sm leading-relaxed max-w-xs mx-auto">Study realistic questions and answers with standard explanations to ensure you pass on your first attempt.</p>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Exam Topics Base? (Features) -->
<section class="py-24 bg-gray-50 border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-black text-navy sm:text-4xl mb-4 tracking-tight">Why Choose Exam Topics Base?</h2>
            <p class="text-lg text-gray-500 max-w-2xl mx-auto font-medium">We provide the most accurate, reliable, and up-to-date certification study materials on the market.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-shadow border border-gray-100 group">
                <div class="w-14 h-14 bg-cyan/10 text-cyan rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-navy mb-3">100% Accurate Answers</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Our dumps are verified by certified IT professionals to ensure absolute accuracy for your actual exam.</p>
            </div>
            
            <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-shadow border border-gray-100 group">
                <div class="w-14 h-14 bg-purple-500/10 text-purple-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-navy mb-3">Web Testing Engine</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Practice in a realistic testing environment. Identify weak spots before you take the real certification test.</p>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-shadow border border-gray-100 group">
                <div class="w-14 h-14 bg-orange/10 text-orange rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </div>
                <h3 class="text-xl font-black text-navy mb-3">Free 90-Day Updates</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Vendors update their exams frequently. We provide 90 days of free updates so you're always studying the latest questions.</p>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-shadow border border-gray-100 group">
                <div class="w-14 h-14 bg-green-500/10 text-green-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-navy mb-3">Safe & Secure</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Your data and payments are 100% secure. We never share your information, and checkout is fully encrypted.</p>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-shadow border border-gray-100 group">
                <div class="w-14 h-14 bg-blue-500/10 text-blue-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-navy mb-3">Instant Access</h3>
                <p class="text-sm text-gray-500 leading-relaxed">Get immediate access to your study materials and test engine right after purchase. No waiting around.</p>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition-shadow border border-gray-100 group">
                <div class="w-14 h-14 bg-pink-500/10 text-pink-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-navy mb-3">Money-Back Guarantee</h3>
                <p class="text-sm text-gray-500 leading-relaxed">We are so confident in our dumps that if you fail your exam, we will refund 100% of your money. No questions asked.</p>
            </div>
        </div>
    </div>
</section>

<!-- Latest Updated Exams Section -->
<section class="py-24 bg-white relative overflow-hidden">
    <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-cyan/5 rounded-full filter blur-[100px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-black text-navy sm:text-4xl mb-4 tracking-tight">Latest Updated Exams</h2>
            <p class="text-lg text-gray-500 max-w-2xl mx-auto font-medium">We dynamically update our exam dumps weekly to match the latest vendor certification blueprints.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
            @foreach($latestExams as $exam)
                <div class="bg-white rounded-2xl p-6 flex flex-col justify-between shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
                    <!-- Hover Accent Line -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cyan to-blue-500 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></div>

                    <div>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-[11px] font-black tracking-widest px-2.5 py-1 rounded-md bg-cyan/10 text-cyan uppercase">{{ $exam->exam_code }}</span>
                            <span class="text-[11px] font-bold text-orange flex items-center">
                                <span class="relative flex h-2 w-2 mr-1.5">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-orange"></span>
                                </span>
                                {{ $exam->last_updated_at ? $exam->last_updated_at->format('M Y') : 'June 2026' }}
                            </span>
                        </div>
                        <h3 class="font-black text-navy text-base mb-2 line-clamp-2 h-12 leading-snug group-hover:text-cyan transition-colors">{{ $exam->exam_name }}</h3>
                        <p class="text-xs font-bold text-gray-400 mb-6 uppercase tracking-wider">{{ $exam->vendor ? $exam->vendor->name : '' }}</p>
                    </div>
                    <div class="flex justify-between items-center pt-5 border-t border-gray-100 mt-auto">
                        <span class="text-lg font-black text-navy">${{ number_format($exam->price_pdf, 2) }}</span>
                        <a href="{{ $exam->url }}" class="text-sm font-bold text-white bg-navy hover:bg-cyan px-4 py-2 rounded-lg transition-colors flex items-center space-x-1 shadow-md">
                            <span>Get Pack</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center">
            <a href="{{ url('/vendors') }}" class="inline-flex items-center space-x-2 bg-gray-50 hover:bg-gray-100 text-navy font-black px-8 py-4 rounded-xl transition-colors border border-gray-200 shadow-sm hover:shadow">
                <span>View All Supported Exams</span>
                <svg class="w-5 h-5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>
    </div>
</section>

<!-- Testimonials Section (Premium Dark Mode) -->
<section class="py-24 bg-gradient-to-b from-[#0F172A] to-[#07101E] relative overflow-hidden">
    <!-- Glowing background accents -->
    <div class="absolute top-1/2 left-0 w-96 h-96 bg-cyan/10 rounded-full filter blur-[120px] pointer-events-none -translate-y-1/2 -translate-x-1/2"></div>
    <div class="absolute top-1/2 right-0 w-96 h-96 bg-purple-600/10 rounded-full filter blur-[120px] pointer-events-none -translate-y-1/2 translate-x-1/2"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-20">
            <h2 class="text-3xl font-black text-white sm:text-4xl mb-4 tracking-tight">What Our Ninja Students Say</h2>
            <p class="text-lg text-gray-400 max-w-2xl mx-auto">Over 200,000 students successfully certified using our study materials.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Review 1 -->
            <div class="bg-white/5 backdrop-blur-xl p-8 rounded-2xl shadow-2xl border border-white/10 relative group hover:-translate-y-2 transition-transform duration-300">
                <div class="absolute -top-3 -right-3 bg-green-500 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg transform rotate-3 flex items-center space-x-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    <span>Verified</span>
                </div>
                <div class="flex items-center space-x-1 text-yellow-400 mb-6 drop-shadow-[0_0_8px_rgba(250,204,21,0.5)]">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                </div>
                <p class="text-base text-gray-300 mb-8 italic leading-relaxed font-light">"I was skeptical about dumps, but Exam Topics Base's AZ-104 study guide was 100% accurate. 42 out of 45 questions on my exam were identical to the guide. Highly recommended!"</p>
                <div class="flex items-center space-x-4 pt-4 border-t border-white/10">
                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-cyan to-blue-600 text-white flex items-center justify-center font-black text-sm shadow-[0_0_15px_rgba(0,212,170,0.5)]">MA</div>
                    <div>
                        <h4 class="text-base font-black text-white">Marc A.</h4>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Microsoft Certified</span>
                    </div>
                </div>
            </div>

            <!-- Review 2 -->
            <div class="bg-white/5 backdrop-blur-xl p-8 rounded-2xl shadow-2xl border border-white/10 relative group hover:-translate-y-2 transition-transform duration-300 md:mt-8">
                <div class="absolute -top-3 -right-3 bg-green-500 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg transform rotate-3 flex items-center space-x-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    <span>Verified</span>
                </div>
                <div class="flex items-center space-x-1 text-yellow-400 mb-6 drop-shadow-[0_0_8px_rgba(250,204,21,0.5)]">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                </div>
                <p class="text-base text-gray-300 mb-8 italic leading-relaxed font-light">"The browser-based test engine is brilliant. I practiced timed exam mode for AWS SysOps Administrator until I consistently got 90%+. Passed on my first try with a score of 875!"</p>
                <div class="flex items-center space-x-4 pt-4 border-t border-white/10">
                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-orange to-red-500 text-white flex items-center justify-center font-black text-sm shadow-[0_0_15px_rgba(255,107,53,0.5)]">SL</div>
                    <div>
                        <h4 class="text-base font-black text-white">Sarah L.</h4>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">AWS DevOps Eng</span>
                    </div>
                </div>
            </div>

            <!-- Review 3 -->
            <div class="bg-white/5 backdrop-blur-xl p-8 rounded-2xl shadow-2xl border border-white/10 relative group hover:-translate-y-2 transition-transform duration-300">
                <div class="absolute -top-3 -right-3 bg-green-500 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg transform rotate-3 flex items-center space-x-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    <span>Verified</span>
                </div>
                <div class="flex items-center space-x-1 text-yellow-400 mb-6 drop-shadow-[0_0_8px_rgba(250,204,21,0.5)]">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                </div>
                <p class="text-base text-gray-300 mb-8 italic leading-relaxed font-light">"CompTIA Security+ was a beast, but the explanations inside the Exam Topics Base package helped me understand why an option was correct, rather than just memorizing answers."</p>
                <div class="flex items-center space-x-4 pt-4 border-t border-white/10">
                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center font-black text-sm shadow-[0_0_15px_rgba(34,197,94,0.5)]">DK</div>
                    <div>
                        <h4 class="text-base font-black text-white">Devon K.</h4>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Security Analyst</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Frequently Asked Questions -->
<section class="py-24 bg-gray-50 border-t border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-black text-navy sm:text-4xl mb-4 tracking-tight">Frequently Asked Questions</h2>
            <p class="text-lg text-gray-500 font-medium">Everything you need to know about our study materials and test engine.</p>
        </div>

        <div class="space-y-4" x-data="{ active: null }">
            <!-- FAQ 1 -->
            <div class="bg-white border border-gray-100 rounded-xl overflow-hidden hover:border-cyan/30 transition-colors">
                <button @click="active !== 1 ? active = 1 : active = null" class="w-full text-left px-6 py-5 flex items-center justify-between focus:outline-none">
                    <span class="font-black text-navy text-lg">Are the exam questions actually updated?</span>
                    <svg class="w-5 h-5 text-cyan transform transition-transform duration-300" :class="{ 'rotate-180': active === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="active === 1" x-collapse x-cloak>
                    <div class="px-6 pb-5 text-gray-500 font-medium leading-relaxed">
                        Yes! Our team of certified IT professionals monitors vendor curriculum changes constantly. We update our question banks weekly to ensure you are studying the exact material you will see on the test.
                    </div>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="bg-white border border-gray-100 rounded-xl overflow-hidden hover:border-purple-500/30 transition-colors">
                <button @click="active !== 2 ? active = 2 : active = null" class="w-full text-left px-6 py-5 flex items-center justify-between focus:outline-none">
                    <span class="font-black text-navy text-lg">How does the Web-Based Test Engine work?</span>
                    <svg class="w-5 h-5 text-purple-500 transform transition-transform duration-300" :class="{ 'rotate-180': active === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="active === 2" x-collapse x-cloak>
                    <div class="px-6 pb-5 text-gray-500 font-medium leading-relaxed">
                        Our proprietary test engine runs entirely in your browser (no downloads required). It simulates the real exam environment with timed modes, randomized questions, and detailed score reports to pinpoint your weak areas.
                    </div>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="bg-white border border-gray-100 rounded-xl overflow-hidden hover:border-cyan/30 transition-colors">
                <button @click="active !== 3 ? active = 3 : active = null" class="w-full text-left px-6 py-5 flex items-center justify-between focus:outline-none">
                    <span class="font-black text-navy text-lg">Do I have to pay for updates?</span>
                    <svg class="w-5 h-5 text-cyan transform transition-transform duration-300" :class="{ 'rotate-180': active === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="active === 3" x-collapse x-cloak>
                    <div class="px-6 pb-5 text-gray-500 font-medium leading-relaxed">
                        No. When you purchase an Exam Topics Base study pack, you receive 90 days of free updates automatically. If the vendor changes the exam during that time, you'll instantly get the new questions in your account.
                    </div>
                </div>
            </div>
            
            <!-- FAQ 4 -->
            <div class="bg-white border border-gray-100 rounded-xl overflow-hidden hover:border-purple-500/30 transition-colors">
                <button @click="active !== 4 ? active = 4 : active = null" class="w-full text-left px-6 py-5 flex items-center justify-between focus:outline-none">
                    <span class="font-black text-navy text-lg">What if I fail the exam?</span>
                    <svg class="w-5 h-5 text-purple-500 transform transition-transform duration-300" :class="{ 'rotate-180': active === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="active === 4" x-collapse x-cloak>
                    <div class="px-6 pb-5 text-gray-500 font-medium leading-relaxed">
                        We have a 99.8% pass rate, but if you happen to fail, we've got your back. We offer a 100% money-back guarantee. Just send us your official failure transcript within 30 days of purchase, and we will refund you in full.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Latest from the Blog -->
<section class="py-24 bg-white relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 space-y-4 md:space-y-0">
            <div>
                <h2 class="text-3xl font-black text-navy sm:text-4xl mb-4 tracking-tight">Latest from our Blog</h2>
                <p class="text-lg text-gray-500 font-medium">Tips, news, and study guides for your next certification.</p>
            </div>
            <a href="{{ route('blog.index') }}" class="hidden md:flex items-center space-x-2 text-navy hover:text-cyan font-black transition-colors">
                <span>View All Articles</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>

        @php
            $latestBlogPosts = \App\Models\BlogPost::with('category', 'user')
                ->where('status', 'published')
                ->latest('published_at')
                ->limit(3)
                ->get();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @forelse($latestBlogPosts as $post)
                <article class="bg-white rounded-2xl overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-gray-100 flex flex-col group">
                    @if($post->featured_image)
                        <a href="{{ route('blog.show', $post->slug) }}" class="block shrink-0 overflow-hidden relative">
                            <div class="absolute inset-0 bg-navy/10 group-hover:bg-transparent transition-colors z-10"></div>
                            <img src="{{ str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-56 object-cover transform group-hover:scale-110 transition-transform duration-700 ease-in-out">
                        </a>
                    @endif
                    <div class="p-8 flex flex-col flex-1">
                        <div class="flex items-center justify-between mb-4">
                            @if($post->category)
                                <a href="{{ route('blog.category', $post->category->slug) }}" class="text-[10px] font-black text-cyan uppercase tracking-widest bg-cyan/10 px-3 py-1.5 rounded-md hover:bg-cyan hover:text-white transition-colors">{{ $post->category->name }}</a>
                            @else
                                <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest bg-gray-100 px-3 py-1.5 rounded-md">News</span>
                            @endif
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $post->published_at->format('M d, Y') }}</span>
                        </div>
                        <a href="{{ route('blog.show', $post->slug) }}" class="group block mb-4 flex-1">
                            <h3 class="text-xl font-black text-navy group-hover:text-cyan transition-colors leading-tight">{{ $post->title }}</h3>
                        </a>
                        <p class="text-sm text-gray-500 line-clamp-2 mb-6 font-medium leading-relaxed">{{ $post->excerpt }}</p>
                        <div class="mt-auto flex items-center justify-between pt-6 border-t border-gray-100">
                            <div class="flex items-center space-x-3">
                                <img src="{{ $post->user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($post->user->name).'&color=FF6B35&background=0A1628' }}" alt="{{ $post->user->name }}" class="w-8 h-8 rounded-full shadow-sm">
                                <span class="text-xs font-black text-navy">{{ $post->user->name }}</span>
                            </div>
                            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest flex items-center">
                                <svg class="w-4 h-4 mr-1 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $post->reading_time ?? 1 }} min
                            </span>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-3 text-center py-16 text-gray-400 font-bold">
                    No articles published yet. Check back soon!
                </div>
            @endforelse
        </div>
        
        <div class="mt-12 text-center md:hidden">
            <a href="{{ route('blog.index') }}" class="inline-flex items-center space-x-2 bg-gray-50 text-navy font-black px-8 py-4 rounded-xl border border-gray-200">
                <span>View All Articles</span>
                <svg class="w-5 h-5 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>
    </div>
</section>

<!-- Final CTA Banner -->
<section class="relative py-20 overflow-hidden bg-navy">
    <div class="absolute inset-0 bg-gradient-to-r from-cyan via-blue-600 to-purple-600 opacity-90"></div>
    <!-- Decorative patterns -->
    <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, #000 25%, transparent 25%, transparent 75%, #000 75%, #000), repeating-linear-gradient(45deg, #000 25%, #fff 25%, #fff 75%, #000 75%, #000); background-position: 0 0, 10px 10px; background-size: 20px 20px;"></div>
    
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <h2 class="text-3xl md:text-5xl font-black text-white mb-6 tracking-tight leading-tight">Ready to Advance Your IT Career?</h2>
        <p class="text-xl text-white/90 mb-10 max-w-2xl mx-auto font-medium">Join 250,000+ professionals who passed their certification exams on the first attempt with Exam Topics Base.</p>
        
        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-6">
            <a href="{{ url('/vendors') }}" class="w-full sm:w-auto bg-white text-navy hover:bg-gray-100 px-10 py-5 rounded-xl text-lg font-black uppercase tracking-wide shadow-2xl transition-transform hover:scale-105">
                Find Your Exam Now
            </a>
            <p class="text-white/80 text-sm font-bold mt-4 sm:mt-0 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                100% Money-Back Guarantee
            </p>
        </div>
    </div>
</section>
@endsection
