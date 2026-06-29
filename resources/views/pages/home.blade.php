@extends('layouts.public')

@section('title', 'ExamsNinja - Pass Your IT Certification Exam First Attempt')

@section('seo_tags')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "WebSite",
  "name": "ExamsNinja",
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
<!-- Hero Section -->
<section class="bg-navy text-white pt-20 pb-24 relative overflow-hidden">
    <!-- Abstract shuriken background decorations -->
    <div class="absolute right-0 top-0 opacity-10 transform translate-x-1/3 -translate-y-1/4 select-none pointer-events-none">
        <svg class="h-96 w-96 text-cyan" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2L14.5 9.5H22L16 14L18.5 21.5L12 17L5.5 21.5L8 14L2 9.5H9.5L12 2Z" />
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center space-x-1 bg-gray-800 text-cyan text-xs font-semibold px-3 py-1.5 rounded-full border border-gray-700 mb-6 shadow">
            <span class="flex h-2 w-2 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan"></span>
            </span>
            <span>June 2026 Core Database Verified Updates</span>
        </div>

        <!-- H1 / H2 -->
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-6 max-w-4xl mx-auto leading-tight">
            Pass Your IT Certification Exam on the <span class="text-cyan">First Attempt</span>
        </h1>
        <p class="text-lg sm:text-xl text-gray-300 mb-10 max-w-2xl mx-auto leading-relaxed">
            ExamsNinja delivers verified exam dumps, PDF study guides, and a powerful web-based test engine — for every major vendor.
        </p>

        <!-- Search Bar with Live Suggestions (Alpine.js) -->
        <div class="max-w-xl mx-auto mb-10 relative" x-data="{
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
            <div class="relative rounded-lg shadow-lg">
                <input type="text" 
                       x-model="query" 
                       @input.debounce.300ms="fetchResults()"
                       @focus="if(query.length >= 2) open = true"
                       @click.away="open = false"
                       placeholder="Search by exam code (e.g. AZ-900, CLF-C02) or vendor name..." 
                       class="w-full pl-5 pr-12 py-4 rounded-lg bg-white text-navy focus:outline-none focus:ring-2 focus:ring-cyan text-sm sm:text-base border border-gray-300">
                <div class="absolute right-4 top-1/2 transform -translate-y-1/2 flex items-center space-x-2">
                    <template x-if="loading">
                        <svg class="animate-spin h-5 w-5 text-cyan" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Live Dropdown Results -->
            <div x-show="open" 
                 class="absolute left-0 right-0 mt-2 bg-white rounded-md shadow-xl text-navy text-left overflow-hidden z-50 border border-gray-200" 
                 style="display: none;">
                
                <!-- Vendors Section -->
                <div x-show="results.vendors.length > 0" class="border-b border-gray-150">
                    <div class="bg-gray-50 px-4 py-2 text-xs font-bold tracking-wider text-gray-500 uppercase">Vendors</div>
                    <template x-for="vendor in results.vendors">
                        <a :href="vendor.url" class="block px-4 py-2.5 hover:bg-gray-100 transition text-sm font-semibold">
                            <span x-text="vendor.name"></span>
                        </a>
                    </template>
                </div>

                <!-- Exams Section -->
                <div x-show="results.exams.length > 0">
                    <div class="bg-gray-50 px-4 py-2 text-xs font-bold tracking-wider text-gray-500 uppercase">Exams</div>
                    <template x-for="exam in results.exams">
                        <a :href="exam.url" class="block px-4 py-3 hover:bg-gray-100 transition border-b border-gray-100 last:border-0">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-xs font-bold bg-cyan bg-opacity-20 text-navy px-1.5 py-0.5 rounded mr-2" x-text="exam.code"></span>
                                    <span class="text-sm font-semibold" x-text="exam.name"></span>
                                </div>
                                <span class="text-xs text-gray-500" x-text="exam.vendor"></span>
                            </div>
                        </a>
                    </template>
                </div>

                <!-- No Results State -->
                <div x-show="results.exams.length === 0 && results.vendors.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">
                    No certifications found. Try another search term.
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-6 mb-16">
            <a href="{{ url('/vendors') }}" class="w-full sm:w-auto bg-orange hover-bg-orange text-white px-8 py-4 rounded-md text-base font-bold shadow-lg transition">
                Browse Exams
            </a>
            <a href="{{ url('/free-demo') }}" class="w-full sm:w-auto border border-cyan text-cyan hover:bg-cyan hover:text-navy px-8 py-4 rounded-md text-base font-bold transition">
                Download Free Demo
            </a>
        </div>

        <!-- Trust Bar -->
        <div class="border-t border-gray-800 pt-10 grid grid-cols-2 md:grid-cols-5 gap-4 text-xs sm:text-sm text-gray-400">
            <div class="flex items-center justify-center space-x-1">
                <span>✅ 3,500+ Exams</span>
            </div>
            <div class="flex items-center justify-center space-x-1">
                <span>✅ 200,000+ Students</span>
            </div>
            <div class="flex items-center justify-center space-x-1">
                <span>✅ 99.6% Pass Rate</span>
            </div>
            <div class="flex items-center justify-center space-x-1">
                <span>✅ Updated Within 30 Days</span>
            </div>
            <div class="flex items-center justify-center space-x-1 col-span-2 md:col-span-1">
                <span>✅ 30-Day Money Back</span>
            </div>
        </div>
    </div>
</section>

<!-- Vendor Grid Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-extrabold text-navy sm:text-4xl mb-4">Supported Certification Providers</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Get access to premium dumps and testing tools for all major IT certification vendors.</p>
        </div>

        <!-- Vendors Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-6">
            @foreach($vendors as $vendor)
                <a href="{{ url('/vendors/' . $vendor->slug) }}" class="group border border-gray-200 rounded-lg p-6 flex flex-col items-center text-center hover:shadow-xl hover:border-cyan transition duration-300">
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
                    <div class="h-12 w-12 rounded border flex items-center justify-center mb-4 group-hover:scale-110 transition duration-300 {{ $style['bg'] }}">
                        {!! $style['html'] !!}
                    </div>
                    <h3 class="font-bold text-navy text-sm mb-1 group-hover:text-cyan transition">{{ $vendor->name }}</h3>
                    <span class="bg-gray-150 text-gray-600 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $vendor->exam_count }} Exams</span>
                </a>
            @endforeach
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-20 bg-gray-50 border-t border-gray-150">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-extrabold text-navy sm:text-4xl mb-4">How ExamsNinja Works</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Prepare effectively and guarantee your exam success in three simple steps.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <div class="text-center space-y-4">
                <div class="h-16 w-16 bg-cyan bg-opacity-15 text-cyan mx-auto rounded-full flex items-center justify-center">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-navy">1. Locate Your Exam</h3>
                <p class="text-gray-600 text-sm leading-relaxed">Search from our library of 3,500+ IT certification exams covering AWS, Microsoft, Cisco, CompTIA, and more.</p>
            </div>

            <div class="text-center space-y-4">
                <div class="h-16 w-16 bg-orange bg-opacity-15 text-orange mx-auto rounded-full flex items-center justify-center">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-navy">2. Download PDF / Start Engine</h3>
                <p class="text-gray-600 text-sm leading-relaxed">Instantly access your study guides on Cloudflare R2 or launch our interactive, browser-based online testing engine.</p>
            </div>

            <div class="text-center space-y-4">
                <div class="h-16 w-16 bg-green-100 text-green-600 mx-auto rounded-full flex items-center justify-center">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-navy">3. Pass Confidently</h3>
                <p class="text-gray-600 text-sm leading-relaxed">Study realistic questions and answers with standard explanations to ensure you pass on your first attempt.</p>
            </div>
        </div>
    </div>
</section>

<!-- Latest Updated Exams Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-extrabold text-navy sm:text-4xl mb-4">Latest Updated Exams</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">We dynamically update our exam dumps weekly to match the latest vendor certification blueprints.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            @foreach($latestExams as $exam)
                <div class="border border-gray-200 rounded-lg p-5 flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-cyan bg-opacity-15 text-navy border border-cyan border-opacity-35">{{ $exam->exam_code }}</span>
                            <span class="text-xs text-gray-500">{{ $exam->last_updated_at ? $exam->last_updated_at->format('M Y') : 'June 2026' }}</span>
                        </div>
                        <h3 class="font-bold text-navy text-sm mb-2 line-clamp-2 h-10">{{ $exam->exam_name }}</h3>
                        <p class="text-xs text-gray-500 mb-4">{{ $exam->vendor ? $exam->vendor->name : '' }}</p>
                    </div>
                    <div class="flex justify-between items-center pt-4 border-t border-gray-150">
                        <span class="text-sm font-bold text-navy">${{ $exam->price_pdf }}</span>
                        <a href="{{ url('/exams/' . $exam->slug) }}" class="text-xs font-bold text-cyan hover:text-navy transition flex items-center space-x-1">
                            <span>Get Study Pack</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center">
            <a href="{{ url('/vendors') }}" class="inline-flex items-center space-x-2 text-cyan hover:text-navy font-bold transition">
                <span>View all supported exams</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-20 bg-gray-50 border-t border-b border-gray-150">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-extrabold text-navy sm:text-4xl mb-4">What Our Ninja Students Say</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">Over 200,000 students successfully certified using our study materials.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Review 1 -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center space-x-1 text-yellow-400 mb-4">
                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                </div>
                <p class="text-sm text-gray-600 mb-6 italic">"I was skeptical about dumps, but ExamsNinja's AZ-104 study guide was 100% accurate. 42 out of 45 questions on my exam were identical to the guide. Highly recommended!"</p>
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-navy text-white flex items-center justify-center font-bold text-xs">MA</div>
                    <div>
                        <h4 class="text-sm font-bold text-navy">Marc A.</h4>
                        <span class="text-xs text-gray-500">Microsoft Certified Administrator</span>
                    </div>
                </div>
            </div>

            <!-- Review 2 -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center space-x-1 text-yellow-400 mb-4">
                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                </div>
                <p class="text-sm text-gray-600 mb-6 italic">"The browser-based test engine is brilliant. I practiced timed exam mode for AWS SysOps Administrator until I consistently got 90%+. Passed on my first try with a score of 875!"</p>
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-navy text-white flex items-center justify-center font-bold text-xs">SL</div>
                    <div>
                        <h4 class="text-sm font-bold text-navy">Sarah L.</h4>
                        <span class="text-xs text-gray-500">AWS DevOps Engineer</span>
                    </div>
                </div>
            </div>

            <!-- Review 3 -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center space-x-1 text-yellow-400 mb-4">
                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                </div>
                <p class="text-sm text-gray-600 mb-6 italic">"CompTIA Security+ was a beast, but the explanations inside the ExamsNinja package helped me understand why an option was correct, rather than just memorizing answers."</p>
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-navy text-white flex items-center justify-center font-bold text-xs">DK</div>
                    <div>
                        <h4 class="text-sm font-bold text-navy">Devon K.</h4>
                        <span class="text-xs text-gray-500">Security Analyst</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
