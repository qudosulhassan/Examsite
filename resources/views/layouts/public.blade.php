<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Site Verification -->
    @if(config('seo.verification.google_search_console'))
        <meta name="google-site-verification" content="{{ config('seo.verification.google_search_console') }}">
    @endif

    <title>@yield('title', config('seo.defaults.title'))</title>
    <meta name="description" content="@yield('meta_description', config('seo.defaults.description'))">
    <meta name="keywords" content="@yield('meta_keywords', config('seo.defaults.keywords'))">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">
    <meta name="robots" content="@yield('robots', config('seo.defaults.robots', 'noindex, nofollow'))">
    <meta name="googlebot" content="@yield('googlebot', config('seo.defaults.robots', 'noindex, nofollow'))">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="@yield('og_type', config('seo.defaults.og_type'))">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">
    <meta property="og:title" content="@yield('title', config('seo.defaults.title'))">
    <meta property="og:description" content="@yield('meta_description', config('seo.defaults.description'))">
    <meta property="og:image" content="@yield('og_image', asset(config('seo.defaults.og_image')))">
    <meta property="og:site_name" content="{{ config('seo.site_name') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:site" content="{{ config('seo.social.twitter_handle') }}">
    <meta property="twitter:url" content="@yield('canonical_url', url()->current())">
    <meta property="twitter:title" content="@yield('title', config('seo.defaults.title'))">
    <meta property="twitter:description" content="@yield('meta_description', config('seo.defaults.description'))">
    <meta property="twitter:image" content="@yield('og_image', asset(config('seo.defaults.og_image')))">

    <!-- Custom SEO Tags (Schema.org JSON-LD, etc.) -->
    @yield('seo_tags')

    <!-- Google Analytics (GA4) -->
    @if(app()->environment('production') && config('seo.tracking.ga4_measurement_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('seo.tracking.ga4_measurement_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ config('seo.tracking.ga4_measurement_id') }}');
        </script>
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">

    <!-- CSS / JS (Tailwind + Alpine) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8F9FB;
            color: #1A1A2E;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Sora', sans-serif;
        }
        .bg-navy { background-color: #0A1628; }
        .text-navy { color: #0A1628; }
        .text-cyan { color: #00D4AA; }
        .bg-cyan { background-color: #00D4AA; }
        .border-cyan { border-color: #00D4AA; }
        .hover-bg-cyan:hover { background-color: #00bfa0; }
        .text-orange { color: #FF6B35; }
        .bg-orange { background-color: #FF6B35; }
        .hover-bg-orange:hover { background-color: #e55a26; }
    </style>
    @yield('styles')
</head>
<body class="antialiased min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-navy text-white sticky top-0 z-50 shadow-md" x-data="{ mobileMenuOpen: false }">
        <!-- Promotional Banner -->
        <div x-data="countdownTimer()" x-show="show" x-cloak class="bg-gradient-to-r from-orange to-red-500 text-white py-2.5 px-4 text-center text-sm shadow-md relative z-[60] flex items-center justify-center flex-wrap gap-2">
            <span class="font-bold tracking-wide">🔥 FLASH SALE! Use coupon <span class="bg-white text-orange px-2 py-0.5 rounded font-mono font-black mx-1">NINJA50</span> for 50% off all Vendor Bundles!</span>
            <span class="inline-flex items-center space-x-1 font-mono font-bold bg-black/20 px-3 py-1 rounded-full text-xs">
                <span>Ends in:</span>
                <span><span x-text="hours">00</span>h</span>
                <span><span x-text="minutes">00</span>m</span>
                <span><span x-text="seconds">00</span>s</span>
            </span>
            <button @click="show = false" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white/80 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="container-custom">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'ExamTopicsBase') }}" class="h-9 md:h-10 w-auto object-contain">
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden md:flex space-x-8 text-sm font-medium">
                    <a href="{{ url('/vendors') }}" class="text-gray-300 hover:text-cyan transition">Browse Vendors</a>
                    <a href="{{ url('/certifications') }}" class="text-gray-300 hover:text-cyan transition">Certifications</a>
                    <a href="{{ url('/test-engine') }}" class="text-gray-300 hover:text-cyan transition flex items-center space-x-1">
                        <span>Test Engine</span>
                        <span class="bg-cyan text-navy font-extrabold text-[8px] px-1.5 py-0.5 rounded-full uppercase tracking-wide">New</span>
                    </a>

                    <a href="{{ url('/free-demo') }}" class="text-gray-300 hover:text-cyan transition font-semibold text-cyan">Free Demo</a>
                    <a href="{{ url('/blog') }}" class="text-gray-300 hover:text-cyan transition">Blog</a>
                    <a href="{{ url('/faq') }}" class="text-gray-300 hover:text-cyan transition">FAQ</a>
                    <a href="{{ url('/contact') }}" class="text-gray-300 hover:text-cyan transition">Contact</a>
                </nav>

                <!-- Actions -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Shopping Cart Icon -->
                    <a href="{{ url('/cart') }}" class="relative p-2 text-gray-300 hover:text-cyan transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        @php $cartCount = count(session('cart', [])); @endphp
                        <span id="cart-badge" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-navy bg-cyan rounded-full {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                    </a>

                    <!-- User Account / Login -->
                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none text-gray-300 hover:text-cyan transition">
                                <img class="h-8 w-8 rounded-full border border-cyan" src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&color=00D4AA&background=0A1628' }}" alt="Avatar">
                                <span class="max-w-xs truncate">{{ auth()->user()->name }}</span>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 text-gray-700 z-50" style="display: none;">
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ url('/admin') }}" class="block px-4 py-2 text-sm hover:bg-gray-100 font-semibold text-orange">Admin Panel</a>
                                @endif
                                <a href="{{ url('/dashboard') }}" class="block px-4 py-2 text-sm hover:bg-gray-100">User Dashboard</a>
                                <a href="{{ url('/dashboard/my-exams') }}" class="block px-4 py-2 text-sm hover:bg-gray-100">My Purchased Guides</a>
                                <a href="{{ url('/dashboard/profile') }}" class="block px-4 py-2 text-sm hover:bg-gray-100">Profile Settings</a>
                                <hr class="my-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 text-red-600">Logout</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-300 hover:text-cyan transition text-sm font-medium">Log In</a>
                        <a href="{{ route('register') }}" class="bg-orange hover-bg-orange text-white px-4 py-2 rounded-md text-sm font-semibold shadow transition">Sign Up</a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex items-center md:hidden">
                    <a href="{{ url('/cart') }}" class="relative p-2 text-gray-300 hover:text-cyan mr-2">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span id="cart-badge-mobile" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-navy bg-cyan rounded-full {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                    </a>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-400 hover:text-white focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display: none;" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div x-show="mobileMenuOpen" class="md:hidden bg-navy border-t border-gray-800" style="display: none;">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="{{ url('/vendors') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800">Browse Vendors</a>
                <a href="{{ url('/certifications') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800">Certifications</a>
                <a href="{{ url('/test-engine') }}" class="block px-3 py-2 rounded-md text-base font-medium text-cyan hover:bg-gray-800 flex items-center justify-between">
                    <span>Test Engine</span>
                    <span class="bg-cyan text-navy font-extrabold text-[8px] px-1.5 py-0.5 rounded-full uppercase tracking-wide">New</span>
                </a>

                <a href="{{ url('/free-demo') }}" class="block px-3 py-2 rounded-md text-base font-medium text-cyan hover:bg-gray-800">Free Demo</a>
                <a href="{{ url('/blog') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800">Blog</a>
                <a href="{{ url('/faq') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800">FAQ</a>
                <a href="{{ url('/contact') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800">Contact</a>
                <hr class="border-gray-800 my-2">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ url('/admin') }}" class="block px-3 py-2 rounded-md text-base font-medium text-orange hover:bg-gray-800">Admin Panel</a>
                    @endif
                    <a href="{{ url('/dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800">User Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium text-red-400 hover:bg-gray-800">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800">Log In</a>
                    <a href="{{ route('register') }}" class="block px-3 py-2 rounded-md text-base font-medium text-center bg-orange text-white hover-bg-orange">Sign Up</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="relative bg-[#07101E] pt-20 pb-10 overflow-hidden border-t border-white/5">
        <!-- Background Elements -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-cyan rounded-full mix-blend-screen filter blur-[150px] opacity-5 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-5 pointer-events-none"></div>
        
        <div class="relative z-10 container-custom">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 lg:gap-8 mb-16">
                <!-- Branding -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="flex items-center">
                        <a href="{{ url('/') }}" class="inline-block">
                            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'ExamTopicsBase') }}" class="h-10 md:h-12 w-auto object-contain">
                        </a>
                    </div>
                    <p class="text-gray-400 text-sm font-medium leading-relaxed max-w-xs">
                        Pass your IT Certification Exam on the first attempt with our premium study guides, verified questions, and interactive practice engine.
                    </p>
                    <div class="flex items-center space-x-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 hover:bg-cyan hover:text-white hover:border-cyan transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 hover:bg-cyan hover:text-white hover:border-cyan transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Top Vendors -->
                <div class="lg:col-span-3">
                    <h3 class="text-white font-bold text-lg mb-6">Top Vendors</h3>
                    <ul class="grid grid-cols-1 gap-y-3">
                        @foreach(App\Models\Vendor::where('is_active', true)->orderBy('name')->take(5)->get() as $footerVendor)
                            <li>
                                <a href="{{ route('vendors.show', $footerVendor->slug) }}" class="text-gray-400 hover:text-cyan text-sm font-medium transition-colors flex items-center group">
                                    <span class="w-1.5 h-1.5 rounded-full bg-cyan/30 mr-2 group-hover:bg-cyan transition-colors"></span>
                                    {{ $footerVendor->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Quick Links -->
                <div class="lg:col-span-2">
                    <h3 class="text-white font-bold text-lg mb-6">Quick Links</h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="{{ url('/test-engine') }}" class="text-gray-400 hover:text-cyan text-sm font-medium transition-colors flex items-center group">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan/30 mr-2 group-hover:bg-cyan transition-colors"></span>
                                Test Engine <span class="ml-2 bg-cyan/10 text-cyan text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest border border-cyan/20">New</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ url('/free-demo') }}" class="text-gray-400 hover:text-cyan text-sm font-medium transition-colors flex items-center group">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan/30 mr-2 group-hover:bg-cyan transition-colors"></span>
                                Free Demo
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/blog') }}" class="text-gray-400 hover:text-cyan text-sm font-medium transition-colors flex items-center group">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan/30 mr-2 group-hover:bg-cyan transition-colors"></span>
                                Blog
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/contact') }}" class="text-gray-400 hover:text-cyan text-sm font-medium transition-colors flex items-center group">
                                <span class="w-1.5 h-1.5 rounded-full bg-cyan/30 mr-2 group-hover:bg-cyan transition-colors"></span>
                                Contact
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Trust / Guarantee -->
                <div class="lg:col-span-3">
                    <h3 class="text-white font-bold text-lg mb-6">100% Satisfaction</h3>
                    <div class="bg-white/5 border border-white/10 p-5 rounded-2xl backdrop-blur-sm shadow-xl">
                        <div class="flex items-start mb-3">
                            <div class="w-8 h-8 rounded-full bg-cyan/20 flex items-center justify-center text-cyan mr-3 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm mb-1">Money Back Guarantee</h4>
                                <p class="text-[13px] text-gray-400 font-medium leading-relaxed">Full 100% refund immediately if you fail within 30 days of purchase.</p>
                            </div>
                        </div>
                        <div class="flex space-x-2 pt-3 border-t border-white/10 mt-3">
                            <span class="bg-white/5 text-gray-300 text-[10px] font-black px-2.5 py-1 rounded uppercase tracking-widest border border-white/10 flex items-center">
                                <svg class="w-3 h-3 text-cyan mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                SSL Secure
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-xs font-medium text-gray-500">
                    &copy; {{ date('Y') }} Exam Topics Base. All rights reserved.
                </p>
                <div class="text-xs font-medium text-gray-500 max-w-2xl text-center md:text-right">
                    Disclaimer: Exam Topics Base is an independent provider of practice test materials. All trademarks and brand names are property of their respective owners.
                </div>
            </div>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
