@extends('layouts.public')

@section('title', 'Online Test Engine & Certification Simulator - ExamsNinja')
@section('meta_description', 'Experience the ExamsNinja interactive timed test engine. Practice with real exam scenarios, exam simulation, immediate response grading, and detailed explanations.')

@section('content')
<!-- Hero Section (Premium Deep Space) -->
<section class="bg-gradient-to-br from-[#07101E] via-navy to-[#0F172A] text-white pt-24 pb-20 relative overflow-hidden">
    <!-- Abstract glowing orbs -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-cyan rounded-full mix-blend-screen filter blur-[120px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[120px] opacity-20"></div>
    <!-- Abstract Tech Lines -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 64px 64px;"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Left Side Text -->
            <div class="lg:col-span-6 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center space-x-2 bg-white/5 border border-white/10 rounded-full px-4 py-1.5 mb-2 shadow">
                    <span class="bg-cyan h-2 w-2 rounded-full animate-ping"></span>
                    <span class="text-xs font-bold uppercase tracking-widest text-gray-300">Interactive Practice Software</span>
                </div>
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                    The Ultimate <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan to-blue-500">Simulator</span>
                </h1>
                
                <p class="text-lg text-gray-300 max-w-lg mx-auto lg:mx-0">
                    Prepare for certification success under real exam conditions. Our browser-based software simulates actual testing environments for AWS, Cisco, CompTIA, Microsoft, and more.
                </p>

                <!-- Stats Grid -->
                <div class="grid grid-cols-3 gap-4 pt-2 max-w-md mx-auto lg:mx-0">
                    <div class="bg-gray-800/50 p-3 rounded-xl border border-gray-700/50 text-center">
                        <div class="text-2xl font-bold text-cyan">{{ $totalQuestions }}</div>
                        <div class="text-xs text-gray-400">Total Questions</div>
                    </div>
                    <div class="bg-gray-800/50 p-3 rounded-xl border border-gray-700/50 text-center">
                        <div class="text-2xl font-bold text-white">{{ $totalExams }}</div>
                        <div class="text-xs text-gray-400">Exams Supported</div>
                    </div>
                    <div class="bg-gray-800/50 p-3 rounded-xl border border-gray-700/50 text-center">
                        <div class="text-2xl font-bold text-orange">99.4%</div>
                        <div class="text-xs text-gray-400">Pass Rate</div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start space-y-3 sm:space-y-0 sm:space-x-4 pt-2">
                    <a href="{{ route('vendors.index') }}" class="w-full sm:w-auto bg-navy hover:bg-gradient-to-r hover:from-cyan hover:to-blue-500 text-white px-8 py-4 rounded-xl font-black text-center shadow-[0_10px_20px_rgba(0,0,0,0.1)] hover:shadow-[0_10px_30px_rgba(0,212,170,0.3)] transition-all duration-300">
                        Get All-Access Pass
                    </a>
                    <a href="#compatible-exams" class="w-full sm:w-auto bg-white/5 hover:bg-white/10 text-white border border-white/10 px-8 py-4 rounded-xl font-bold text-center transition-all duration-300">
                        Try Live Demo Below
                    </a>
                </div>
            </div>

            <!-- Right Side Live Demo Mock Widget -->
            <div class="lg:col-span-6" id="demo-simulator">
                <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-[0_20px_50px_rgba(0,0,0,0.3)] relative overflow-hidden transform hover:-translate-y-2 transition-all duration-500">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="h-40 w-40 text-cyan" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L14.5 9.5H22L16 14L18.5 21.5L12 17L5.5 21.5L8 14L2 9.5H9.5L12 2Z" /></svg>
                    </div>
                    
                    <h3 class="text-2xl font-black text-white mb-2 relative z-10">Experience the Engine</h3>
                    <p class="text-sm font-medium text-gray-300 mb-8 relative z-10">Access free demo questions in our fully functional, interactive testing environment. No signup required.</p>
                    
                    <div class="space-y-4 relative z-10">
                        @foreach($compatibleExams->take(3) as $exam)
                            <a href="{{ route('public.demo-test-engine.lobby', $exam->slug) }}" class="block bg-white/5 hover:bg-white/10 border border-white/10 hover:border-cyan/50 rounded-2xl p-5 transition-all duration-300 group">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span class="bg-black/30 text-gray-300 text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-md border border-white/5">{{ $exam->vendor->name }}</span>
                                        </div>
                                        <h4 class="font-black text-white group-hover:text-cyan transition-colors text-lg">{{ $exam->exam_code }}</h4>
                                        <p class="text-xs font-medium text-gray-400 truncate max-w-[250px] sm:max-w-xs">{{ $exam->exam_name }}</p>
                                    </div>
                                    <div class="h-12 w-12 rounded-xl bg-cyan/10 text-cyan flex items-center justify-center group-hover:bg-cyan group-hover:text-navy transition-all duration-300 shadow-[0_5px_15px_rgba(0,212,170,0.2)] group-hover:shadow-[0_10px_20px_rgba(0,212,170,0.4)]">
                                        <svg class="h-5 w-5 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    
                    <div class="mt-8 text-center relative z-10">
                        <a href="#compatible-exams" class="text-xs font-bold text-cyan hover:text-white transition-colors uppercase tracking-wider">View all compatible exams &darr;</a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<!-- Core Features Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-4">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-navy">
                Designed to Match Actual Exam Platforms
            </h2>
            <p class="text-gray-600">
                Forget simple static PDFs. Learn interactively with multiple practice settings designed to expose weak areas and lock in key concepts.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative z-20 -mt-24">
            <!-- Practice Mode -->
            <div class="bg-white p-10 rounded-3xl border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.04)] hover:-translate-y-2 transition-all duration-300">
                <div class="h-14 w-14 rounded-2xl bg-cyan/10 flex items-center justify-center text-cyan mb-8 shadow-inner">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-navy mb-4">Practice Mode</h3>
                <p class="text-[13px] font-medium text-gray-500 leading-relaxed">
                    Study at your own pace. Reveal the correct answer and read detailed, step-by-step explanations immediately after submitting each question. Perfect for learning new concepts.
                </p>
            </div>

            <!-- Exam Simulator -->
            <div class="bg-white p-10 rounded-3xl border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.04)] hover:-translate-y-2 transition-all duration-300">
                <div class="h-14 w-14 rounded-2xl bg-orange/10 flex items-center justify-center text-orange mb-8 shadow-inner">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-navy mb-4">Exam Simulator</h3>
                <p class="text-[13px] font-medium text-gray-500 leading-relaxed">
                    Test yourself under realistic conditions. Features strict time limits and randomized questions. Explanations and overall score results are locked until you submit the entire attempt.
                </p>
            </div>

            <!-- Review Mode -->
            <div class="bg-white p-10 rounded-3xl border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.04)] hover:-translate-y-2 transition-all duration-300">
                <div class="h-14 w-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-500 mb-8 shadow-inner">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <h3 class="text-2xl font-black text-navy mb-4">Focused Review</h3>
                <p class="text-[13px] font-medium text-gray-500 leading-relaxed">
                    Study smarter, not longer. Adaptive logic filters and presents only questions you previously flagged or answered incorrectly in your last attempt. Drill down on weak spots.
                </p>
            </div>
        </div>

    </div>
</section>

<!-- Compatible Exams Section with Single Unified Search Control & Smooth Vendor Tabs -->
<section id="compatible-exams" class="py-20 bg-gray-50 border-t border-b border-gray-200/50"
         x-data="testEngineManager()"
         x-init="initComponent()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Title & Intro -->
        <div class="text-center max-w-3xl mx-auto mb-12 space-y-3">
            <div class="inline-flex items-center space-x-2 bg-cyan/10 border border-cyan/20 text-cyan rounded-full px-4 py-1.5 text-xs font-bold uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>Interactive Exam Database</span>
            </div>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-navy">Find Your Practice Exam</h2>
            <p class="text-gray-600 text-sm sm:text-base">
                Search over 3,500+ certification test engine practice dumps by exam code, title, or vendor.
            </p>
        </div>

        <!-- Single Sleek Search Bar Box & Vendor Tabs -->
        <div class="max-w-4xl mx-auto mb-12" @click.outside="showDropdown = false">
            <form @submit.prevent="submitSearch()" class="relative">
                <div class="bg-white p-2 sm:p-2.5 rounded-3xl border border-gray-200/90 shadow-[0_15px_35px_rgba(0,0,0,0.06)] flex items-center transition-all duration-300 focus-within:border-cyan focus-within:ring-4 focus-within:ring-cyan/10">
                    <div class="pl-4 pr-2 text-cyan">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <input 
                        type="text" 
                        name="q" 
                        x-model="query"
                        @input.debounce.250ms="liveSearch()"
                        @focus="if(results.length) showDropdown = true"
                        placeholder="Search exam code, title or vendor (e.g. 200-301, AWS, CompTIA, Cisco)..." 
                        class="w-full bg-transparent border-none text-navy placeholder-gray-400 text-sm sm:text-base font-semibold focus:outline-none focus:ring-0 py-2.5 px-2"
                        autocomplete="off"
                    >

                    <template x-if="query || activeVendor">
                        <button type="button" @click="resetFilters()" class="text-gray-400 hover:text-gray-600 px-3 py-2 text-xs font-bold uppercase tracking-wider whitespace-nowrap">
                            Clear
                        </button>
                    </template>

                    <button type="submit" class="bg-navy hover:bg-gradient-to-r hover:from-cyan hover:to-blue-500 text-white font-black text-xs sm:text-sm uppercase tracking-wider px-6 sm:px-8 py-3.5 rounded-2xl transition-all duration-300 shadow-md flex items-center space-x-2 whitespace-nowrap">
                        <svg class="w-4 h-4 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Search Engine</span>
                    </button>
                </div>

                <!-- Instant Auto-Complete Dropdown -->
                <div x-show="showDropdown && results.length > 0" x-transition class="absolute left-0 right-0 top-full mt-2 bg-white border border-gray-200 rounded-2xl shadow-2xl z-50 overflow-hidden text-left divide-y divide-gray-100">
                    <div class="p-3 bg-gray-50 text-[11px] font-bold uppercase tracking-wider text-gray-500 flex justify-between items-center">
                        <span>Live Exam Results</span>
                        <span class="text-cyan font-bold" x-text="results.length + ' matches found'"></span>
                    </div>
                    <div class="max-h-72 overflow-y-auto">
                        <template x-for="item in results" :key="item.code">
                            <a :href="item.demo_url || item.url" class="flex items-center justify-between p-4 hover:bg-cyan/5 transition-colors group">
                                <div>
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="text-sm font-black text-navy group-hover:text-cyan transition-colors" x-text="item.code"></span>
                                        <span class="text-[10px] font-bold text-gray-500 uppercase bg-gray-100 px-2 py-0.5 rounded" x-text="item.vendor"></span>
                                    </div>
                                    <div class="text-xs text-gray-500 truncate max-w-sm" x-text="item.name"></div>
                                </div>
                                <span class="text-xs font-black uppercase text-navy bg-gray-100 group-hover:bg-cyan group-hover:text-navy px-3 py-1.5 rounded-xl transition-all">Launch Demo &rarr;</span>
                            </a>
                        </template>
                    </div>
                </div>
            </form>

            <!-- Quick Vendor Filter Pills (Smooth Tab Switching) -->
            <div class="flex flex-wrap items-center justify-center gap-2 pt-6">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mr-2">Top Vendors:</span>
                
                <button type="button" 
                        @click="setVendor('')"
                        class="text-xs font-bold px-4 py-2 rounded-xl border transition-all duration-200 shadow-sm cursor-pointer"
                        :class="!activeVendor ? 'bg-navy text-white border-navy font-black shadow-md scale-[1.02]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:text-navy'">
                    All Vendors
                </button>

                @foreach($vendors as $vendorItem)
                    <button type="button" 
                            @click="setVendor('{{ $vendorItem->slug }}')"
                            class="text-xs font-bold px-4 py-2 rounded-xl border transition-all duration-200 shadow-sm cursor-pointer"
                            :class="activeVendor === '{{ $vendorItem->slug }}' ? 'bg-cyan text-navy border-cyan font-black shadow-md scale-[1.02]' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:text-navy'">
                        {{ $vendorItem->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Dynamic Grid Container (AJAX Target) -->
        <div id="compatible-exams-grid" class="relative min-h-[300px] transition-opacity duration-300" :class="loadingGrid ? 'opacity-50 pointer-events-none' : 'opacity-100'">
            @include('pages.partials.test-engine-grid', ['compatibleExams' => $compatibleExams, 'searchQuery' => $searchQuery, 'vendorFilter' => $vendorFilter])
        </div>

    </div>
</section>

<script>
function testEngineManager() {
    return {
        query: '{{ addslashes($searchQuery) }}',
        activeVendor: '{{ addslashes($vendorFilter) }}',
        results: [],
        loadingResults: false,
        loadingGrid: false,
        showDropdown: false,

        initComponent() {
            document.addEventListener('click', (e) => {
                const link = e.target.closest('.ajax-pagination a');
                if (link && link.href) {
                    e.preventDefault();
                    this.fetchGrid(link.href);
                }
            });
        },

        liveSearch() {
            if (this.query.length < 2) {
                this.results = [];
                this.showDropdown = false;
                return;
            }
            this.loadingResults = true;
            fetch('/api/search?q=' + encodeURIComponent(this.query) + '&context=test-engine')
                .then(res => res.json())
                .then(data => {
                    this.results = data.exams || [];
                    this.showDropdown = true;
                    this.loadingResults = false;
                }).catch(() => { this.loadingResults = false; });
        },

        setVendor(vendorSlug) {
            this.activeVendor = vendorSlug || '';
            this.updateUrlAndFetch();
        },

        submitSearch() {
            this.showDropdown = false;
            this.updateUrlAndFetch();
        },

        resetFilters() {
            this.query = '';
            this.activeVendor = '';
            this.updateUrlAndFetch();
        },

        updateUrlAndFetch() {
            const params = new URLSearchParams();
            if (this.query.trim()) {
                params.set('q', this.query.trim());
            }
            if (this.activeVendor.trim()) {
                params.set('vendor', this.activeVendor.trim());
            }

            const queryString = params.toString();
            const cleanUrl = window.location.pathname + (queryString ? '?' + queryString : '');

            window.history.pushState({}, '', cleanUrl);

            const fetchUrl = '/test-engine?' + queryString + (queryString ? '&ajax=1' : 'ajax=1');
            this.fetchGrid(fetchUrl);
        },

        fetchGrid(url) {
            this.loadingGrid = true;
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.html) {
                    const gridContainer = document.getElementById('compatible-exams-grid');
                    if (gridContainer) {
                        gridContainer.innerHTML = data.html;
                    }
                }
                this.loadingGrid = false;
            })
            .catch(() => {
                this.loadingGrid = false;
            });
        }
    };
}
</script>

<!-- Call to Action Section -->
<section class="py-16 bg-navy text-white text-center relative overflow-hidden">
    <div class="absolute inset-0 opacity-5 mix-blend-overlay bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1517694712202-14dd9538aa97');"></div>
    <div class="max-w-4xl mx-auto px-4 relative z-10 space-y-6">
        <h2 class="text-3xl sm:text-4xl font-extrabold">Ready to Ace Your Exam?</h2>
        <p class="text-lg text-gray-300 max-w-2xl mx-auto">
            Get unlimited access to ALL certification test engines by choosing one of our membership plans, or buy single exam simulator keys today.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4 pt-4">
            <a href="{{ route('vendors.index') }}" class="w-full sm:w-auto bg-cyan hover-bg-cyan text-navy px-8 py-3.5 rounded-md font-bold transition shadow-lg">
                View Pricing Plans
            </a>
            @guest
                <a href="{{ route('register') }}" class="w-full sm:w-auto bg-transparent hover:bg-white/10 text-white border border-gray-600 px-8 py-3.5 rounded-md font-semibold transition">
                    Create Free Account
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="w-full sm:w-auto bg-transparent hover:bg-white/10 text-white border border-gray-600 px-8 py-3.5 rounded-md font-semibold transition">
                    Go to Dashboard
                </a>
            @endguest
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-16">
            <h2 class="text-3xl font-extrabold text-navy">Test Engine FAQ</h2>
            <p class="text-gray-500 mt-2">Common questions about our online practice environment</p>
        </div>

        <div class="space-y-4" x-data="{ active: null }">
            
            <!-- FAQ 1 -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button @click="active = active === 1 ? null : 1" class="w-full flex justify-between items-center p-5 text-left font-semibold text-navy bg-gray-50 hover:bg-gray-100/70 transition">
                    <span>Is the Test Engine mobile friendly?</span>
                    <svg class="h-5 w-5 text-gray-500 transition-transform duration-200" :class="active === 1 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="active === 1" class="p-5 border-t border-gray-200 text-sm text-gray-600 leading-relaxed bg-white">
                    Yes, absolutely! The Test Engine portal is built with a fully responsive layout. You can practice on your phone, tablet, or desktop computer anytime. Your progress, attempt history, and flagged questions sync automatically.
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button @click="active = active === 2 ? null : 2" class="w-full flex justify-between items-center p-5 text-left font-semibold text-navy bg-gray-50 hover:bg-gray-100/70 transition">
                    <span>How often are exam questions updated?</span>
                    <svg class="h-5 w-5 text-gray-500 transition-transform duration-200" :class="active === 2 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="active === 2" class="p-5 border-t border-gray-200 text-sm text-gray-600 leading-relaxed bg-white">
                    Our database is continuously updated based on actual candidate feedback, vendor syllabus changes, and question updates. When a vendor updates their exam format (e.g., from CLF-C01 to CLF-C02), we sync our question database within 48 hours to ensure compatibility.
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button @click="active = active === 3 ? null : 3" class="w-full flex justify-between items-center p-5 text-left font-semibold text-navy bg-gray-50 hover:bg-gray-100/70 transition">
                    <span>What is your money-back guarantee?</span>
                    <svg class="h-5 w-5 text-gray-500 transition-transform duration-200" :class="active === 3 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="active === 3" class="p-5 border-t border-gray-200 text-sm text-gray-600 leading-relaxed bg-white">
                    We offer a 100% money-back guarantee. If you purchase access to any Test Engine exam dump, complete at least 2 full-length simulated practice attempts scoring 80% or above in our software, and still fail the official certification exam within 30 days of purchase, just email us your official score report, and we will issue a full refund immediately.
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button @click="active = active === 4 ? null : 4" class="w-full flex justify-between items-center p-5 text-left font-semibold text-navy bg-gray-50 hover:bg-gray-100/70 transition">
                    <span>Can I use the Test Engine offline?</span>
                    <svg class="h-5 w-5 text-gray-500 transition-transform duration-200" :class="active === 4 ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="active === 4" class="p-5 border-t border-gray-200 text-sm text-gray-600 leading-relaxed bg-white">
                    No, the interactive Test Engine runs directly inside your browser and requires an active internet connection to retrieve questions, record attempts, track flagged items, and calculate performance metrics. However, you can download static study guides in PDF format to study offline.
                </div>
            </div>

        </div>

    </div>
</section>
@endsection
