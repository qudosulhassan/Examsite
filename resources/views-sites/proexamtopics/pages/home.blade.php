@extends('layouts.public')

@section('title', 'ProExamTopics — Master Your IT Certification Exams with Real Practice Tests')
@section('meta_description', 'Master your IT certification exams with ProExamTopics. 1,200+ verified practice exams, realistic test engine simulation, and in-depth expert explanations for AWS, Azure, CompTIA, Cisco, and more.')

@section('content')
<!-- Google-Friendly JSON-LD Structured Data Schemas -->
@php
    $schemaData = [
        "@context" => "https://schema.org",
        "@graph" => [
            [
                "@type" => "WebSite",
                "@id" => url('/') . '/#website',
                "url" => url('/'),
                "name" => "ProExamTopics",
                "description" => "Industry-leading IT certification practice exams, test simulation, and study guides.",
                "publisher" => [
                    "@id" => url('/') . '/#organization'
                ],
                "potentialAction" => [
                    "@type" => "SearchAction",
                    "target" => [
                        "@type" => "EntryPoint",
                        "urlTemplate" => url('/search') . "?q={search_term_string}"
                    ],
                    "query-input" => "required name=search_term_string"
                ]
            ],
            [
                "@type" => "Organization",
                "@id" => url('/') . '/#organization',
                "name" => "ProExamTopics",
                "url" => url('/'),
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => asset('images/proexamtopics-logo.png'),
                    "width" => 980,
                    "height" => 239
                ],
                "contactPoint" => [
                    "@type" => "ContactPoint",
                    "contactType" => "Customer Support",
                    "email" => "support@proexamtopics.com",
                    "availableLanguage" => ["English"]
                ]
            ],
            [
                "@type" => "FAQPage",
                "@id" => url('/') . '/#faq',
                "mainEntity" => [
                    [
                        "@type" => "Question",
                        "name" => "Are ProExamTopics practice questions updated to the latest exam syllabus?",
                        "acceptedAnswer" => [
                            "@type" => "Answer",
                            "text" => "Yes. Our team of certified IT professionals and subject matter experts continually monitors official vendor blueprints (including Amazon AWS, Microsoft Azure, CompTIA, and Cisco). Questions and explanations are updated regularly to ensure 100% alignment with current real exam objectives."
                        ]
                    ],
                    [
                        "@type" => "Question",
                        "name" => "What formats are included with my ProExamTopics purchase?",
                        "acceptedAnswer" => [
                            "@type" => "Answer",
                            "text" => "Every exam package includes both our interactive Web Test Engine (with customizable Practice and Timed Simulation modes) and a downloadable, printable PDF Study Guide with comprehensive answer explanations for offline study."
                        ]
                    ],
                    [
                        "@type" => "Question",
                        "name" => "How does the 100% Money-Back Pass Guarantee work?",
                        "acceptedAnswer" => [
                            "@type" => "Answer",
                            "text" => "We stand firmly behind the quality of our exam materials. If you prepare thoroughly using our verified practice questions and test engine and do not pass your official certification exam on your first attempt, we provide a full 100% money-back refund. Terms apply."
                        ]
                    ],
                    [
                        "@type" => "Question",
                        "name" => "Can I try sample questions before purchasing?",
                        "acceptedAnswer" => [
                            "@type" => "Answer",
                            "text" => "Absolutely! We provide free demo questions for every certification exam on our platform so you can evaluate the depth of our questions, user-friendly interface, and detailed answer explanations before buying."
                        ]
                    ],
                    [
                        "@type" => "Question",
                        "name" => "Can I use ProExamTopics on mobile devices and tablets?",
                        "acceptedAnswer" => [
                            "@type" => "Answer",
                            "text" => "Yes. Our Web Test Engine is fully responsive and optimized for smartphones, tablets, laptops, and desktop computers. No complicated software installation is required—simply log in and practice in any modern browser."
                        ]
                    ]
                ]
            ],
            [
                "@type" => "AggregateRating",
                "itemReviewed" => [
                    "@type" => "Course",
                    "name" => "IT Certification Practice Exams & Test Simulator",
                    "provider" => [
                        "@id" => url('/') . '/#organization'
                    ]
                ],
                "ratingValue" => "4.9",
                "bestRating" => "5",
                "worstRating" => "1",
                "ratingCount" => "254180"
            ]
        ]
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($schemaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>

<!-- 1. HIGH-IMPACT HERO SECTION -->
<x-site-b.hero variant="home" glowColor="cyan" padding="large">
    <div class="max-w-5xl mx-auto text-center relative z-10">
        
        <!-- Live Announcement Pill -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-500/10 border border-cyan-400/30 text-cyan-300 text-xs font-semibold uppercase tracking-wider mb-6 backdrop-blur-md shadow-inner animate-pulse">
            <span class="w-2 h-2 rounded-full bg-[#00D4AA] shadow-[0_0_8px_#00D4AA]"></span>
            <span>2026-2027 Syllabus Updated • 100% Pass Guarantee</span>
        </div>

        <!-- Main Headline with Gradient Glow -->
        @if(!empty($currentSite?->default_h1))
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.15] mb-5 font-sans">
                {!! $currentSite->default_h1 !!}
            </h1>
        @else
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.15] mb-5 font-sans">
                Master Your IT Certification<br class="hidden sm:inline">
                Exams with <span class="bg-gradient-to-r from-[#00D4AA] via-[#00E5FF] to-blue-400 bg-clip-text text-transparent drop-shadow-[0_0_35px_rgba(0,229,255,0.45)]">Real Practice Tests</span>
            </h1>
        @endif
        
        <!-- Subtitle -->
        <p class="text-sm sm:text-base md:text-lg max-w-2xl mx-auto mb-8 leading-relaxed text-slate-300 font-normal">
            Real exam questions, interactive simulator engine, and in-depth expert rationales.<br class="hidden sm:inline">
            Trusted by over <strong class="text-white font-semibold">250,000+</strong> IT professionals worldwide.
        </p>

        <!-- Centered High-Converting Search Bar -->
        <div class="mx-auto mb-6 relative max-w-2xl" x-data="{ query: '', suggestions: [], open: false, loading: false }">
            <form action="{{ url('/search') }}" method="GET" class="bg-white rounded-2xl shadow-2xl p-2 flex items-center border border-white/20 ring-4 ring-white/10 transition focus-within:ring-cyan-400/30">
                <div class="pl-4 text-slate-400">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    name="q" 
                    x-model="query"
                    @input.debounce.250ms="
                        if (query.length > 1) {
                            loading = true;
                            fetch('{{ url('/api/exams/search') }}?q=' + encodeURIComponent(query))
                                .then(res => res.json())
                                .then(data => {
                                    suggestions = data.slice(0, 6);
                                    open = true;
                                    loading = false;
                                })
                                .catch(() => { loading = false; });
                        } else {
                            suggestions = [];
                            open = false;
                        }
                    "
                    @focus="if (suggestions.length > 0) open = true"
                    @click.away="open = false"
                    placeholder="Search by Exam Code or Name (e.g., AZ-900, AWS SAA-C03, CCNA, Security+)" 
                    class="w-full px-4 py-3 text-slate-800 placeholder-slate-400 focus:outline-none border-0 text-sm sm:text-base font-medium bg-transparent"
                    autocomplete="off"
                >
                <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold px-6 py-3 rounded-xl text-sm sm:text-base transition shadow-lg flex items-center justify-center gap-2 shrink-0" aria-label="Search Exam">
                    <span>Search</span>
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </form>

            <!-- Autocomplete Suggestions Dropdown -->
            <div x-show="open && suggestions.length > 0" x-cloak class="absolute left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-gray-200 text-left z-50 overflow-hidden divide-y divide-gray-100">
                <div class="px-4 py-2 bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Top Matching Certifications</div>
                <template x-for="item in suggestions" :key="item.id">
                    <a :href="item.product_url || item.url || ('/' + (item.vendor ? (item.vendor.slug || item.vendor) : 'vendor') + '/' + (item.slug ? (item.slug.endsWith('-exam') ? item.slug : item.slug + '-exam') : (item.code ? item.code.toLowerCase() + '-exam' : 'exam')))" class="block px-4 py-3 hover:bg-blue-50/60 transition flex items-center justify-between text-slate-800 group">
                        <div class="flex items-center gap-2.5">
                            <span class="font-mono font-bold text-blue-600 bg-blue-100/70 px-2.5 py-1 rounded-md text-xs" x-text="item.code"></span>
                            <span class="text-sm font-semibold group-hover:text-blue-600 transition-colors" x-text="item.name"></span>
                        </div>
                        <span class="text-xs text-slate-400 font-medium" x-text="item.vendor ? (item.vendor.name || item.vendor) : ''"></span>
                    </a>
                </template>
            </div>
        </div>

        <!-- Trending Exam Pills -->
        <div class="flex flex-wrap items-center justify-center gap-2 mb-10 text-xs text-slate-300">
            <span class="text-slate-400 font-medium mr-1 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-amber-400 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.316.492-.633.991-1.002 1.408-.57.643-1.258 1.155-2.008 1.547-1.488.777-2.457 2.112-2.58 3.73-.061.802.13 1.62.53 2.308.528.91 1.34 1.59 2.306 1.954.966.364 2.03.35 3.018-.037.988-.387 1.83-1.077 2.39-1.966.56-.888.79-1.928.65-2.955-.102-.75-.38-1.482-.82-2.112-.44-.63-.98-1.18-1.57-1.69-.3-.26-.61-.51-.89-.78a16.9 16.9 0 01-.75-.807zM7.05 13.9c-.39-.42-.62-.97-.66-1.55-.04-.58.12-1.16.45-1.63.33-.47.81-.82 1.37-.99.56-.17 1.16-.14 1.7.09.54.23.99.63 1.28 1.15.29.52.39 1.12.28 1.71-.11.59-.42 1.12-.89 1.5-.47.38-1.06.58-1.67.56-.56-.02-1.11-.27-1.53-.69l-.33-.15z" clip-rule="evenodd"/></svg>
                Popular Searches:
            </span>
            @foreach(['AZ-900', 'SAA-C03', 'Security+', 'CCNA', 'CISSP', 'CLF-C02', 'PMP'] as $tag)
                <a href="{{ url('/search?q=' . urlencode($tag)) }}" class="px-2.5 py-1 rounded-lg bg-slate-800/80 hover:bg-blue-600 hover:text-white border border-slate-700/60 text-slate-300 font-mono text-xs transition duration-150">
                    {{ $tag }}
                </a>
            @endforeach
        </div>

        <!-- 4 Key Proof Counters with Subtle Glass Effect -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 max-w-4xl mx-auto text-center">
            <div class="bg-white/5 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/10 shadow-lg">
                <div class="text-2xl sm:text-3xl font-black text-white font-mono tracking-tight">1,200+</div>
                <div class="text-xs font-medium text-slate-400 mt-1">Verified Practice Exams</div>
            </div>
            <div class="bg-white/5 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/10 shadow-lg">
                <div class="text-2xl sm:text-3xl font-black text-white font-mono tracking-tight">250,000+</div>
                <div class="text-xs font-medium text-slate-400 mt-1">Certified Candidates</div>
            </div>
            <div class="bg-white/5 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/10 shadow-lg">
                <div class="text-2xl sm:text-3xl font-black font-mono tracking-tight text-[#00E5FF] drop-shadow-[0_0_15px_rgba(0,229,255,0.4)]">98.8%</div>
                <div class="text-xs font-medium text-slate-400 mt-1">First-Attempt Pass Rate</div>
            </div>
            <div class="bg-white/5 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/10 shadow-lg">
                <div class="text-2xl sm:text-3xl font-black text-white font-mono tracking-tight">24/7</div>
                <div class="text-xs font-medium text-slate-400 mt-1">SME Support & Updates</div>
            </div>
        </div>
    </div>
</x-site-b.hero>

<!-- TRUST BAR ROW -->
<div class="bg-slate-900 border-b border-slate-800 py-3.5 px-4">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-4 text-xs font-semibold text-slate-300">
        <div class="flex items-center gap-2">
            <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
            <span>100% Real Exam Format Simulation</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-5 h-5 rounded-full bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold text-xs">⚡</span>
            <span>Instant PDF & Engine Access</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-5 h-5 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-xs">🔄</span>
            <span>Free Syllabus Updates Included</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-xs">★</span>
            <span>100% Money-Back Pass Guarantee</span>
        </div>
    </div>
</div>

<!-- 2. TOP CERTIFICATION VENDORS SECTION -->
<section class="bg-white py-16 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest bg-blue-50 px-2.5 py-1 rounded-md">Industry Leaders</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-2">Top Certification Vendors</h2>
                <p class="text-sm text-slate-500 mt-1">Select your vendor to explore official exam objectives, practice tests, and study guides.</p>
            </div>
            <a href="{{ url('/vendors') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-blue-600 hover:text-blue-800 transition">
                <span>View All Vendors</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        @php
            $topVendors = \App\Models\Vendor::withCount('exams')->orderBy('exams_count', 'desc')->take(11)->get();
            
            // Brand color and badge icons mapping
            $vendorMeta = [
                'microsoft' => ['bg' => 'bg-sky-50', 'border' => 'border-sky-200', 'hover' => 'hover:border-sky-500', 'color' => '#00A4EF', 'icon' => 'MS'],
                'amazon-aws' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'hover' => 'hover:border-amber-500', 'color' => '#FF9900', 'icon' => 'AWS'],
                'amazon-web-services-aws' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'hover' => 'hover:border-amber-500', 'color' => '#FF9900', 'icon' => 'AWS'],
                'google-cloud-platform-gcp' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'hover' => 'hover:border-blue-500', 'color' => '#4285F4', 'icon' => 'GCP'],
                'cisco' => ['bg' => 'bg-teal-50', 'border' => 'border-teal-200', 'hover' => 'hover:border-teal-500', 'color' => '#00BCEB', 'icon' => 'CISCO'],
                'comptia' => ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'hover' => 'hover:border-rose-500', 'color' => '#C82333', 'icon' => 'CompTIA'],
                'salesforce' => ['bg' => 'bg-cyan-50', 'border' => 'border-cyan-200', 'hover' => 'hover:border-cyan-500', 'color' => '#00A1E0', 'icon' => 'SF'],
                'project-management-institute-pmi' => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'hover' => 'hover:border-indigo-500', 'color' => '#4F46E5', 'icon' => 'PMI'],
                'isaca' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'hover' => 'hover:border-emerald-500', 'color' => '#059669', 'icon' => 'ISACA'],
                'isc2' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'hover' => 'hover:border-green-500', 'color' => '#16A34A', 'icon' => 'ISC2'],
                'oracle' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'hover' => 'hover:border-red-500', 'color' => '#EA1B23', 'icon' => 'ORCL'],
                'red-hat' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'hover' => 'hover:border-red-500', 'color' => '#EE0000', 'icon' => 'RH'],
                'vmware' => ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'hover' => 'hover:border-slate-500', 'color' => '#607078', 'icon' => 'VMW'],
            ];
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($topVendors as $v)
                @php
                    $meta = $vendorMeta[$v->slug] ?? ['bg' => 'bg-slate-50', 'border' => 'border-slate-200', 'hover' => 'hover:border-blue-500', 'color' => '#2563eb', 'icon' => strtoupper(substr($v->name, 0, 3))];
                @endphp
                <a href="{{ url('/vendors/' . $v->slug) }}" class="group bg-white rounded-2xl p-4 border {{ $meta['border'] }} {{ $meta['hover'] }} hover:shadow-lg transition-all duration-200 flex flex-col items-center justify-between text-center min-h-[128px]">
                    <!-- Brand Badge Icon -->
                    <div class="w-11 h-11 rounded-xl {{ $meta['bg'] }} flex items-center justify-center font-bold text-xs tracking-wider text-slate-800 shadow-inner group-hover:scale-110 transition-transform duration-200" style="color: {{ $meta['color'] }};">
                        @if($v->slug === 'microsoft')
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path fill="#F25022" d="M1 1h10v10H1z"/>
                                <path fill="#7FBA00" d="M13 1h10v10H13z"/>
                                <path fill="#00A4EF" d="M1 13h10v10H1z"/>
                                <path fill="#FFB900" d="M13 13h10v10H13z"/>
                            </svg>
                        @elseif(str_contains($v->slug, 'amazon') || str_contains($v->slug, 'aws'))
                            <svg class="w-7 h-7 text-[#FF9900]" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15h-2v-6h2v6zm4 0h-2v-6h2v6zm-2-8a1.5 1.5 0 110-3 1.5 1.5 0 010 3z"/>
                            </svg>
                        @elseif(str_contains($v->slug, 'google'))
                            <svg class="w-6 h-6" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M23.7 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.6c-.3 1.5-1.1 2.8-2.4 3.7v3h3.9c2.3-2.1 3.6-5.2 3.6-8.9z"/>
                                <path fill="#34A853" d="M12 24c3.2 0 6-1.1 8-3l-3.9-3c-1.1.7-2.5 1.2-4.1 1.2-3.1 0-5.8-2.1-6.7-5H1.3v3.1C3.3 21.3 7.4 24 12 24z"/>
                                <path fill="#FBBC05" d="M5.3 14.2c-.2-.7-.4-1.5-.4-2.2s.1-1.5.4-2.2V6.7H1.3C.5 8.3 0 10.1 0 12s.5 3.7 1.3 5.3l4-3.1z"/>
                                <path fill="#EA4335" d="M12 4.8c1.8 0 3.3.6 4.6 1.8l3.4-3.4C17.9 1.2 15.2 0 12 0 7.4 0 3.3 2.7 1.3 6.7l4 3.1c.9-2.9 3.6-5 6.7-5z"/>
                            </svg>
                        @elseif(str_contains($v->slug, 'cisco'))
                            <svg class="w-7 h-7 text-[#00BCEB]" fill="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="10" width="2" height="7" rx="1"/>
                                <rect x="7" y="6" width="2" height="11" rx="1"/>
                                <rect x="11" y="2" width="2" height="15" rx="1"/>
                                <rect x="15" y="6" width="2" height="11" rx="1"/>
                                <rect x="19" y="10" width="2" height="7" rx="1"/>
                            </svg>
                        @elseif(str_contains($v->slug, 'comptia'))
                            <svg class="w-6 h-6 text-[#C82333]" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L4 5v6.09c0 5.05 3.41 9.76 8 10.91 4.59-1.15 8-5.86 8-10.91V5l-8-3zm-1 14.5l-4-4 1.41-1.41L11 13.67l6.59-6.59L19 8.5l-8 8z"/>
                            </svg>
                        @else
                            <span class="font-black text-xs">{{ $meta['icon'] }}</span>
                        @endif
                    </div>

                    <div class="mt-2 w-full">
                        <span class="block font-bold text-slate-800 text-sm group-hover:text-blue-600 transition-colors truncate">
                            {{ $v->name }}
                        </span>
                        <span class="inline-block text-[11px] font-semibold text-slate-400 mt-0.5">
                            {{ $v->exams_count }} {{ Str::plural('Exam', $v->exams_count) }}
                        </span>
                    </div>
                </a>
            @endforeach

            <!-- View All Card -->
            <a href="{{ url('/vendors') }}" class="group bg-gradient-to-br from-slate-50 to-blue-50/40 rounded-2xl p-4 border border-dashed border-blue-300 hover:border-blue-600 hover:shadow-lg transition-all duration-200 flex flex-col items-center justify-between text-center min-h-[128px]">
                <div class="w-11 h-11 rounded-xl bg-blue-100/60 text-blue-600 flex items-center justify-center font-bold text-lg group-hover:scale-110 transition-transform">
                    →
                </div>
                <div class="mt-2 w-full">
                    <span class="block font-bold text-blue-600 text-sm">All Vendors</span>
                    <span class="text-[11px] font-semibold text-slate-500 mt-0.5">Browse 50+ More</span>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- 3. POPULAR & TRENDING CERTIFICATION EXAMS -->
<section class="bg-[#F8FAFC] py-16 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
            <div>
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-widest bg-emerald-50 px-2.5 py-1 rounded-md">Trending Now</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-2">Popular Certification Exams</h2>
                <p class="text-sm text-slate-500 mt-1">Verified practice questions, detailed answer explanations, and realistic test engine simulation.</p>
            </div>
            <a href="{{ url('/search') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-blue-600 hover:text-blue-800 transition">
                <span>Explore All Exams</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        @php
            $popularExams = \App\Models\Exam::with('vendor')
                ->where('is_active', true)
                ->orderBy('id')
                ->take(8)
                ->get();
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($popularExams as $exam)
                @php
                    $examUrl = $exam->url;
                    $price = $exam->price_pdf ?: ($exam->price_engine ?: 39.99);
                    $strikePrice = round($price * 2.0, 2);
                    $vendorName = $exam->vendor->name ?? 'IT Certification';
                @endphp
                <div class="bg-white border border-slate-200/90 rounded-2xl p-5 hover:shadow-xl hover:border-blue-400 transition-all duration-200 flex flex-col justify-between group">
                    <div>
                        <!-- Top Header: Vendor & Verified Badge -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md uppercase tracking-wider truncate max-w-[130px]">
                                {{ $vendorName }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">
                                <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Verified
                            </span>
                        </div>

                        <!-- Exam Code -->
                        <a href="{{ $examUrl }}" class="block font-mono font-black text-xl text-slate-900 group-hover:text-blue-600 transition-colors">
                            {{ $exam->exam_code }}
                        </a>

                        <!-- Exam Title -->
                        <p class="text-xs text-slate-600 font-medium mt-1.5 line-clamp-2 min-h-[32px]">
                            {{ $exam->exam_name ?: $exam->title ?: ($vendorName . ' ' . $exam->exam_code) }}
                        </p>

                        <!-- Rating & Question Count Meta -->
                        <div class="flex items-center justify-between mt-3 text-xs text-slate-500 font-medium border-t border-slate-100 pt-2.5">
                            <span class="text-amber-500 font-bold flex items-center gap-1">
                                ★ 4.9 <span class="text-slate-400 font-normal">(1.4k)</span>
                            </span>
                            <span class="text-slate-500 text-[11px]">
                                Realistic Test Engine
                            </span>
                        </div>
                    </div>

                    <!-- Pricing and CTA Buttons -->
                    <div class="mt-5 pt-4 border-t border-slate-100">
                        <div class="flex items-baseline justify-between mb-3">
                            <div>
                                <span class="text-xl font-black text-slate-900">${{ number_format($price, 2) }}</span>
                                <span class="text-xs text-slate-400 line-through ml-1">${{ number_format($strikePrice, 2) }}</span>
                            </div>
                            <span class="text-[10px] font-extrabold text-emerald-600 bg-emerald-100/70 px-1.5 py-0.5 rounded">
                                50% OFF
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ $examUrl }}#demo" class="text-center py-2 px-3 rounded-xl border border-slate-200 hover:border-blue-600 text-slate-700 hover:text-blue-600 font-bold text-xs transition">
                                Free Demo
                            </a>
                            <a href="{{ $examUrl }}" class="text-center py-2 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition shadow-sm hover:shadow">
                                Practice Now
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 4. THE PROEXAMTOPICS ADVANTAGE: 3 POWERFUL STUDY MODES -->
<section class="bg-[#F8FAFC] py-20 border-b border-gray-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200/60 mb-3 shadow-xs">
                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Tailored Learning Experience
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Study How You Learn Best</h2>
            <p class="text-sm sm:text-base text-slate-600 mt-3 max-w-2xl mx-auto leading-relaxed">
                Choose the preparation format that fits your study routine. From interactive web practice to realistic timed test simulation and offline printable PDFs.
            </p>
        </div>

        <!-- 3 Study Mode Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
            
            <!-- Card 1: Interactive Practice Engine -->
            <div class="bg-white rounded-3xl p-8 border border-slate-200/90 shadow-sm hover:shadow-xl hover:border-blue-400 transition-all duration-300 flex flex-col justify-between group relative">
                <div>
                    <!-- Top Badge & Number -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-200/60 flex items-center justify-center text-blue-600 shadow-sm group-hover:scale-105 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <span class="text-xs font-extrabold text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase tracking-wider">
                            Mode 01
                        </span>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-xl font-extrabold text-slate-900 mb-3 group-hover:text-blue-600 transition-colors">
                        Interactive Practice Engine
                    </h3>
                    <p class="text-sm text-slate-600 leading-relaxed mb-6">
                        Study question by question with zero time pressure. Test your knowledge, view immediate answer confirmations, and absorb in-depth technical explanations for every option.
                    </p>

                    <!-- Feature Bullets -->
                    <ul class="space-y-3 pt-6 border-t border-slate-100 text-sm text-slate-700">
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <span><strong class="text-slate-900">Instant Answer Reveal:</strong> Immediate verification with official documentation citations.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <span><strong class="text-slate-900">Deep Rationale:</strong> Understand why the correct answer works and why alternatives are wrong.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <span><strong class="text-slate-900">Filter by Topic:</strong> Focus specifically on your weakest certification domains.</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Button -->
                <div class="mt-8 pt-6 border-t border-slate-100">
                    <a href="{{ url('/search') }}" class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-slate-100 hover:bg-blue-600 text-slate-700 hover:text-white font-bold text-sm transition-all duration-200">
                        <span>Try Practice Mode</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

            <!-- Card 2: Realistic Exam Simulator (Featured Card with guaranteed dark background & contrasting text) -->
            <div class="rounded-3xl p-8 shadow-2xl flex flex-col justify-between relative overflow-hidden group transform lg:-translate-y-2 transition-all duration-300" style="background: linear-gradient(145deg, #0A1628 0%, #0F172A 50%, #1E3A8A 100%); border: 2px solid #00D4AA; box-shadow: 0 20px 40px -15px rgba(0, 212, 170, 0.3);">
                
                <!-- Glowing Ambient Accent -->
                <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full pointer-events-none blur-3xl opacity-40" style="background: #00D4AA;"></div>
                
                <div class="relative z-10">
                    <!-- Top Badge & Pill -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform" style="background: linear-gradient(135deg, #00D4AA 0%, #00E5FF 100%); color: #0A1628;">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider shadow-sm" style="background: #00D4AA; color: #0A1628;">
                            ★ RECOMMENDED
                        </span>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-xl font-extrabold text-white mb-3" style="color: #FFFFFF !important;">
                        Timed Exam Simulator
                    </h3>
                    <p class="text-sm leading-relaxed mb-6" style="color: #CBD5E1 !important;">
                        Simulate official testing conditions. Practice under real countdown constraints with Pearson VUE style question navigation, score thresholds, and instant diagnostic reports.
                    </p>

                    <!-- Feature Bullets with Explicit High-Contrast Styling -->
                    <ul class="space-y-3 pt-6 border-t border-slate-700/80 text-sm">
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-black shrink-0 mt-0.5" style="background: rgba(0, 212, 170, 0.2); color: #00D4AA;">✓</span>
                            <span style="color: #F1F5F9 !important;"><strong style="color: #00E5FF !important;">Official Exam Environment:</strong> Mimics Pearson VUE & Prometric testing screens.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-black shrink-0 mt-0.5" style="background: rgba(0, 212, 170, 0.2); color: #00D4AA;">✓</span>
                            <span style="color: #F1F5F9 !important;"><strong style="color: #00E5FF !important;">Active Timer & Flagging:</strong> Learn pacing, flag tricky questions, and review before submitting.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-black shrink-0 mt-0.5" style="background: rgba(0, 212, 170, 0.2); color: #00D4AA;">✓</span>
                            <span style="color: #F1F5F9 !important;"><strong style="color: #00E5FF !important;">Diagnostic Score Report:</strong> Identifies pass/fail status and breakdown by exam domain.</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Button -->
                <div class="mt-8 pt-6 border-t border-slate-700/80 relative z-10">
                    <a href="{{ url('/search') }}" class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl font-bold text-sm transition-all duration-200 shadow-lg hover:shadow-cyan-500/20" style="background: linear-gradient(135deg, #00D4AA 0%, #00E5FF 100%); color: #0A1628;">
                        <span>Launch Test Simulator</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

            <!-- Card 3: Downloadable PDF Guide -->
            <div class="bg-white rounded-3xl p-8 border border-slate-200/90 shadow-sm hover:shadow-xl hover:border-blue-400 transition-all duration-300 flex flex-col justify-between group relative">
                <div>
                    <!-- Top Badge & Number -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-200/60 flex items-center justify-center text-indigo-600 shadow-sm group-hover:scale-105 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-xs font-extrabold text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full uppercase tracking-wider">
                            Mode 03
                        </span>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="text-xl font-extrabold text-slate-900 mb-3 group-hover:text-indigo-600 transition-colors">
                        Downloadable PDF Guide
                    </h3>
                    <p class="text-sm text-slate-600 leading-relaxed mb-6">
                        Study on your commute, at work, or anywhere offline. High-resolution, printable PDF compatible with PC, Mac, iPad, iPhone, Android, and e-readers.
                    </p>

                    <!-- Feature Bullets -->
                    <ul class="space-y-3 pt-6 border-t border-slate-100 text-sm text-slate-700">
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <span><strong class="text-slate-900">100% Offline Study:</strong> Practice anywhere without needing an active internet connection.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <span><strong class="text-slate-900">Printable & Searchable:</strong> Highlight, annotate, and quickly search keywords and codes.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <span><strong class="text-slate-900">Instant File Delivery:</strong> Download your PDF immediately upon checkout.</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Button -->
                <div class="mt-8 pt-6 border-t border-slate-100">
                    <a href="{{ url('/search') }}" class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-slate-100 hover:bg-indigo-600 text-slate-700 hover:text-white font-bold text-sm transition-all duration-200">
                        <span>Get PDF Study Guides</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 5. WHY CHOOSE PROEXAMTOPICS (6 PILLARS) -->
<section class="bg-[#F8FAFC] py-16 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="text-xs font-bold text-emerald-600 uppercase tracking-widest bg-emerald-50 px-2.5 py-1 rounded-md">Why We Stand Out</span>
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-2">Engineered for Your First-Attempt Pass</h2>
            <p class="text-sm text-slate-500 mt-2">Everything you need to eliminate exam anxiety and walk into the testing center with complete confidence.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg mb-4">🎯</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Real Exam Question Formats</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Practice with multiple-choice, multiple-response, drag-and-drop, case studies, and exhibit-based questions matching the actual test.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-lg mb-4">👨‍💻</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Verified by Certified SMEs</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Every answer is audited and supported by citations from official vendor documentation, whitepapers, and architecture guides.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-lg mb-4">🔄</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Continuous Syllabus Sync</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    When vendors change objectives or retire old exam blueprints, our question pools are updated immediately at no extra charge.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-lg mb-4">📱</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Multi-Platform Cross-Sync</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Practice seamlessly on any desktop, laptop, tablet, or phone browser without messy software downloads or plugins.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center font-bold text-lg mb-4">🆓</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Free Sample Practice Questions</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Experience our quality firsthand. Test drive free demo questions for any exam before making any commitment.
                </p>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-lg mb-4">🛡️</div>
                <h3 class="text-base font-bold text-slate-900 mb-2">100% Money-Back Guarantee</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    We believe in our preparation methodology. If you prepare thoroughly and don't pass, we refund your purchase. Zero hassle.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 6. WHAT OUR CERTIFIED STUDENTS SAY (SOCIAL PROOF) -->
<section class="bg-white py-16 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold text-amber-500 uppercase tracking-widest bg-amber-50 px-2.5 py-1 rounded-md">Verified Reviews</span>
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-2">What Our Certified Students Say</h2>
            <p class="text-sm text-slate-500 mt-1">Real feedback from IT professionals who achieved their certification goals with ProExamTopics.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-amber-400 text-sm tracking-wider">★★★★★</span>
                        <span class="text-[11px] font-bold text-emerald-600 bg-emerald-100/70 px-2 py-0.5 rounded">Scored 920/1000</span>
                    </div>
                    <p class="text-slate-700 text-sm leading-relaxed italic mb-6">
                        "ProExamTopics was the difference maker for my Microsoft AZ-900. The interactive simulator prepared me for the exact wording and style of Pearson VUE questions. Passed easily on my first try!"
                    </p>
                </div>
                <div class="flex items-center gap-3 pt-4 border-t border-slate-200/60">
                    <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-sm shadow">
                        AR
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Ahmed R.</p>
                        <p class="text-[11px] text-slate-500">Cloud Infrastructure Engineer • Passed AZ-900</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-amber-400 text-sm tracking-wider">★★★★★</span>
                        <span class="text-[11px] font-bold text-emerald-600 bg-emerald-100/70 px-2 py-0.5 rounded">Scored 885/1000</span>
                    </div>
                    <p class="text-slate-700 text-sm leading-relaxed italic mb-6">
                        "The comprehensive explanations for both right AND wrong choices are pure gold. You don't just memorize answers; you genuinely learn why architectural choices work. Highly recommended for AWS SAA-C03!"
                    </p>
                </div>
                <div class="flex items-center gap-3 pt-4 border-t border-slate-200/60">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-sm shadow">
                        SK
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Sarah K.</p>
                        <p class="text-[11px] text-slate-500">Solutions Architect • Passed AWS SAA-C03</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-amber-400 text-sm tracking-wider">★★★★★</span>
                        <span class="text-[11px] font-bold text-emerald-600 bg-emerald-100/70 px-2 py-0.5 rounded">First Attempt Pass</span>
                    </div>
                    <p class="text-slate-700 text-sm leading-relaxed italic mb-6">
                        "Took CCNA twice before with other materials and failed. Switched to ProExamTopics, practiced in the timed simulator for 2 weeks, and passed with flying colors. The PDF on iPad was super convenient!"
                    </p>
                </div>
                <div class="flex items-center gap-3 pt-4 border-t border-slate-200/60">
                    <div class="w-10 h-10 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-sm shadow">
                        BA
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Bilal A.</p>
                        <p class="text-[11px] text-slate-500">Network Administrator • Passed Cisco CCNA</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 7. GOOGLE-FRIENDLY INTERACTIVE FAQ ACCORDION -->
<section class="bg-[#F8FAFC] py-16 border-b border-gray-100" x-data="{ activeFaq: null }">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-xs font-bold text-blue-600 uppercase tracking-widest bg-blue-50 px-2.5 py-1 rounded-md">Frequently Asked Questions</span>
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-2">Everything You Need to Know</h2>
            <p class="text-sm text-slate-500 mt-1">Have questions about our exam prep materials? Find answers to common queries below.</p>
        </div>

        <div class="space-y-4">
            @php
                $faqs = [
                    [
                        'q' => 'Are ProExamTopics practice questions updated to the latest exam syllabus?',
                        'a' => 'Yes, absolutely. Our dedicated team of subject matter experts continually monitors all major vendor updates (including Amazon AWS, Microsoft Azure, CompTIA, Cisco, and Google Cloud). Whenever a certification changes its domain weights, question formats, or syllabus objectives, our questions and rationales are updated immediately.'
                    ],
                    [
                        'q' => 'What is included in the ProExamTopics exam preparation package?',
                        'a' => 'Each exam bundle includes full access to our interactive Web Test Engine (offering untimed Practice Mode and realistic Timed Exam Simulation) as well as an instantly downloadable, printable PDF Study Guide with complete explanations for all answer choices.'
                    ],
                    [
                        'q' => 'How does the 100% Money-Back Pass Guarantee work?',
                        'a' => 'We are confident in our materials. If you prepare thoroughly using our practice questions and test engine and do not pass your official certification exam on your first attempt, we will refund your purchase in full. Simply contact our support team with your score report within 30 days.'
                    ],
                    [
                        'q' => 'Can I test sample questions before making a purchase?',
                        'a' => 'Yes! We provide free demo questions for every single certification exam on our website. You can explore the user interface, question difficulty, and explanation clarity completely free before deciding to purchase full access.'
                    ],
                    [
                        'q' => 'Can I access the practice engine on my mobile phone or tablet?',
                        'a' => 'Yes. The ProExamTopics Web Test Engine is fully responsive and requires no software installation. You can practice seamlessly on desktop, laptop, iPad, iPhone, Android, or tablet with all progress automatically synced.'
                    ],
                    [
                        'q' => 'How long do I have access to the materials and free updates?',
                        'a' => 'You receive instant access upon checkout. All purchases include free syllabus updates for the duration of your chosen access period (with flexible 90-day and 365-day extensions available), so your materials are always up to date.'
                    ],
                ];
            @endphp

            @foreach($faqs as $idx => $faq)
                <div class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden transition shadow-sm">
                    <button 
                        type="button"
                        @click="activeFaq = (activeFaq === {{ $idx }}) ? null : {{ $idx }}" 
                        class="w-full px-6 py-4 text-left flex items-center justify-between font-bold text-slate-800 hover:text-blue-600 transition text-sm sm:text-base gap-4"
                    >
                        <span>{{ $faq['q'] }}</span>
                        <span class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold shrink-0 transition-transform duration-200" :class="{ 'rotate-180 bg-blue-100 text-blue-600': activeFaq === {{ $idx }} }">
                            ↓
                        </span>
                    </button>
                    <div x-show="activeFaq === {{ $idx }}" x-cloak x-collapse class="px-6 pb-5 pt-1 text-slate-600 text-sm leading-relaxed border-t border-slate-100">
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- 8. GOOGLE E-E-A-T EDITORIAL AUTHORITY SECTION -->
<section class="bg-white py-16 border-b border-gray-100">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 prose prose-slate max-w-none text-slate-700">
        <div class="text-center max-w-3xl mx-auto mb-10">
            <span class="text-xs font-bold text-indigo-600 uppercase tracking-widest bg-indigo-50 px-2.5 py-1 rounded-md">Certification Guide</span>
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mt-2">Your Definitive Path to Top IT Certifications</h2>
            <p class="text-sm text-slate-500 mt-1">Accelerate your career in cloud computing, cybersecurity, systems administration, and enterprise networking.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-sm leading-relaxed">
            <div>
                <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="text-blue-600 font-black">☁️</span> Cloud Architecture & Engineering
                </h3>
                <p class="text-slate-600 mb-4">
                    Cloud skills remain the highest-paying IT domain globally. Our comprehensive exam study guides cover foundational, associate, and professional credentials across 
                    <a href="{{ url('/vendors/amazon-aws') }}" class="text-blue-600 font-semibold hover:underline">Amazon Web Services (AWS)</a> (such as AWS Solutions Architect SAA-C03 and Cloud Practitioner CLF-C02) and 
                    <a href="{{ url('/vendors/microsoft') }}" class="text-blue-600 font-semibold hover:underline">Microsoft Azure</a> (including Azure Fundamentals AZ-900 and Administrator AZ-104).
                </p>
                
                <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="text-emerald-600 font-black">🔒</span> Cybersecurity & Threat Analysis
                </h3>
                <p class="text-slate-600">
                    Defend modern enterprise infrastructure with certified credentials from 
                    <a href="{{ url('/vendors/comptia') }}" class="text-blue-600 font-semibold hover:underline">CompTIA</a> (Security+, CySA+, PenTest+), 
                    <a href="{{ url('/vendors/isaca') }}" class="text-blue-600 font-semibold hover:underline">ISACA</a> (CISA, CISM), and 
                    <a href="{{ url('/vendors/isc2') }}" class="text-blue-600 font-semibold hover:underline">ISC2</a> (CISSP, CC). Master core principles of zero-trust architecture, incident response, and compliance.
                </p>
            </div>

            <div>
                <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="text-teal-600 font-black">🌐</span> Enterprise Networking & Infrastructure
                </h3>
                <p class="text-slate-600 mb-4">
                    Build reliable network backbones with industry-standard benchmarks from 
                    <a href="{{ url('/vendors/cisco') }}" class="text-blue-600 font-semibold hover:underline">Cisco Systems</a> (CCNA 200-301, CCNP Enterprise) and CompTIA Network+. Understand IP routing, switching, wireless architecture, and network security automation.
                </p>

                <h3 class="text-base font-bold text-slate-900 mb-2 flex items-center gap-2">
                    <span class="text-amber-600 font-black">📊</span> Project Management & Agile Methodologies
                </h3>
                <p class="text-slate-600">
                    Deliver mission-critical IT projects on time and within budget. Prepare with verified materials for the 
                    <a href="{{ url('/vendors/project-management-institute-pmi') }}" class="text-blue-600 font-semibold hover:underline">Project Management Institute (PMI)</a> PMP, CAPM, and Scrum.org Professional Scrum Master (PSM I) certifications.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 9. HIGH-ENERGY CONVERSION BANNER (PROMO CTA) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="bg-gradient-to-r from-[#00D4AA] via-blue-600 to-[#0A1628] rounded-3xl p-8 sm:p-14 text-white text-center shadow-2xl relative overflow-hidden border border-white/20">
        <!-- Ambient Decorative Shapes -->
        <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-72 h-72 rounded-full bg-cyan-400/20 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-3xl mx-auto">
            <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-white text-xs font-bold uppercase tracking-wider mb-4 backdrop-blur-sm">
                🎉 Limited Time Offer • Use Coupon Code: PRO50
            </span>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight mb-4">
                Ready to Pass Your Exam with Complete Confidence?
            </h2>
            <p class="text-slate-100 text-sm sm:text-base max-w-2xl mx-auto mb-8 font-normal leading-relaxed">
                Join thousands of certified engineers who achieved their career goals. Get immediate access to realistic exam simulation, verified PDF study guides, and free syllabus updates.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-4">
                <a href="{{ url('/search') }}" class="bg-white hover:bg-slate-100 text-blue-600 font-bold text-sm sm:text-base px-8 py-4 rounded-xl shadow-lg transition transform hover:-translate-y-0.5">
                    Browse All Practice Exams
                </a>
                <a href="{{ url('/vendors') }}" class="border-2 border-white/80 hover:bg-white/10 text-white font-bold text-sm sm:text-base px-8 py-4 rounded-xl transition">
                    Explore Vendors
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
