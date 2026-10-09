<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    @php
        $siteFavicon32 = file_exists(public_path('sites/proexamtopics/favicon-32x32.png')) ? asset('sites/proexamtopics/favicon-32x32.png') : (($currentSite && !empty($currentSite->favicon)) ? asset($currentSite->favicon) : asset('sites/proexamtopics/favicon-32x32.png'));
        $siteFavicon16 = file_exists(public_path('sites/proexamtopics/favicon-16x16.png')) ? asset('sites/proexamtopics/favicon-16x16.png') : $siteFavicon32;
        $siteFaviconIco = file_exists(public_path('sites/proexamtopics/favicon.ico')) ? asset('sites/proexamtopics/favicon.ico') : $siteFavicon32;
        $customAppleIcon = file_exists(public_path('sites/proexamtopics/apple-touch-icon.png')) ? asset('sites/proexamtopics/apple-touch-icon.png') : (!empty($globalSettings['apple_touch_icon']) ? asset($globalSettings['apple_touch_icon']) : asset('sites/proexamtopics/apple-touch-icon.png'));
        $faviconVer = file_exists(public_path('sites/proexamtopics/favicon-32x32.png')) ? filemtime(public_path('sites/proexamtopics/favicon-32x32.png')) : time();
    @endphp
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $siteFavicon32 }}?v={{ $faviconVer }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $siteFavicon16 }}?v={{ $faviconVer }}">
    <link rel="shortcut icon" href="{{ $siteFaviconIco }}?v={{ $faviconVer }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $customAppleIcon }}?v={{ $faviconVer }}">

    <!-- Site Verification Tags -->
    @php
        $gscVerification = ($currentSite && !empty($currentSite->google_search_console_code)) ? $currentSite->google_search_console_code : ($globalSettings['seo_gsc_verification'] ?? config('seo.verification.google_search_console'));
        $bingVerification = $globalSettings['seo_bing_verification'] ?? '';
        $yandexVerification = $globalSettings['seo_yandex_verification'] ?? '';
        $pinterestVerification = $globalSettings['seo_pinterest_verification'] ?? '';

        $seoService = app(\App\Services\TechnicalSeoService::class);
        $rawCanonical = trim($__env->yieldContent('canonical_url'));
        $computedCanonical = !empty($rawCanonical) ? $seoService->buildCanonicalUrl($rawCanonical) : $seoService->buildCanonicalUrl();
        $yieldRobots = trim($__env->yieldContent('robots'));
        $computedRobots = !empty($yieldRobots) ? $yieldRobots : (($currentSite && !empty($currentSite->robots_directive) && $currentSite->id > 1) ? $currentSite->robots_directive : $seoService->getRobotsMetaDirective());

        $defaultSiteTitle = ($currentSite && !empty($currentSite->default_seo_title)) ? $currentSite->default_seo_title : ($globalSettings['default_seo_title'] ?? config('seo.defaults.title'));
        $defaultSiteDesc = ($currentSite && !empty($currentSite->default_meta_description)) ? $currentSite->default_meta_description : ($globalSettings['default_meta_description'] ?? config('seo.defaults.description'));
        $defaultSiteKeywords = ($currentSite && !empty($currentSite->primary_keyword_strategy)) ? $currentSite->primary_keyword_strategy : ($globalSettings['default_meta_keywords'] ?? config('seo.defaults.keywords'));
        $activeSiteName = $currentSite ? $currentSite->name : ($globalSettings['site_name'] ?? config('seo.site_name', 'ProExamTopics'));
    @endphp
    @if(!empty($gscVerification))
        <meta name="google-site-verification" content="{{ $gscVerification }}">
    @endif
    @if(!empty($bingVerification))
        <meta name="msvalidate.01" content="{{ $bingVerification }}">
    @endif
    @if(!empty($yandexVerification))
        <meta name="yandex-verification" content="{{ $yandexVerification }}">
    @endif
    @if(!empty($pinterestVerification))
        <meta name="p:domain_verify" content="{{ $pinterestVerification }}">
    @endif

    <title>@yield('title', $defaultSiteTitle)</title>
    <meta name="description" content="@yield('meta_description', $defaultSiteDesc)">
    <meta name="keywords" content="@yield('meta_keywords', $defaultSiteKeywords)">
    <link rel="canonical" href="{{ $computedCanonical }}">
    <meta name="robots" content="{{ $computedRobots }}">
    <meta name="googlebot" content="{{ $computedRobots }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="@yield('og_type', $globalSettings['seo_og_type'] ?? config('seo.defaults.og_type', 'website'))">
    <meta property="og:url" content="{{ $computedCanonical }}">
    <meta property="og:title" content="@yield('title', $defaultSiteTitle)">
    <meta property="og:description" content="@yield('meta_description', $defaultSiteDesc)">
    <meta property="og:image" content="@yield('og_image', !empty($globalSettings['default_og_image']) ? asset($globalSettings['default_og_image']) : asset(config('seo.defaults.og_image', 'images/og-default.png')))">
    <meta property="og:site_name" content="{{ $activeSiteName }}">
    @if(!empty($globalSettings['seo_facebook_app_id']))
        <meta property="fb:app_id" content="{{ $globalSettings['seo_facebook_app_id'] }}">
    @endif

    <!-- Twitter -->
    <meta property="twitter:card" content="{{ $globalSettings['seo_twitter_card'] ?? 'summary_large_image' }}">
    <meta property="twitter:site" content="{{ $globalSettings['social_twitter'] ?? config('seo.social.twitter_handle') }}">
    <meta property="twitter:url" content="@yield('canonical_url', $computedCanonical)">
    <meta property="twitter:title" content="@yield('title', $globalSettings['default_og_title'] ?? ($globalSettings['default_seo_title'] ?? config('seo.defaults.title')))">
    <meta property="twitter:description" content="@yield('meta_description', $globalSettings['default_og_description'] ?? ($globalSettings['default_meta_description'] ?? config('seo.defaults.description')))">
    <meta property="twitter:image" content="@yield('og_image', !empty($globalSettings['default_og_image']) ? asset($globalSettings['default_og_image']) : asset(config('seo.defaults.og_image', 'images/og-default.png')))">

    <!-- Structured Data (JSON-LD) -->
    @if(($globalSettings['seo_schema_master_enabled'] ?? '1') === '1')
        @if(($globalSettings['seo_schema_organization_enabled'] ?? '1') === '1')
            <script type="application/ld+json">
                {!! json_encode($seoService->generateOrganizationSchema(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
            </script>
        @endif
        @if(request()->is('/') && ($globalSettings['seo_schema_website_enabled'] ?? '1') === '1')
            <script type="application/ld+json">
                {!! json_encode($seoService->generateWebSiteSchema(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
            </script>
        @endif
    @endif

    @yield('seo_tags')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <!-- CSS / JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #F8F9FA;
            color: #1E293B;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Sora', sans-serif;
        }
        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
    @yield('styles')
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-blue-500 selection:text-white">
    <!-- Clean White Navbar matching 9-Panel Target Reference -->
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand Logo -->
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center group">
                        <x-application-logo />
                    </a>
                </div>

                <!-- Desktop Nav Links (Reference Source of Truth) -->
                <nav class="hidden md:flex items-center space-x-8 text-sm font-semibold text-slate-700">
                    <a href="{{ url('/vendors') }}" class="hover:text-[#2563eb] transition-colors py-2 {{ request()->is('vendors*') ? 'text-[#2563eb] font-bold' : '' }}">Vendors</a>
                    <a href="{{ url('/certifications') }}" class="hover:text-[#2563eb] transition-colors py-2 {{ request()->is('certifications*') ? 'text-[#2563eb] font-bold' : '' }}">Certifications</a>
                    <a href="{{ url('/contact') }}" class="hover:text-[#2563eb] transition-colors py-2 {{ request()->is('contact*') ? 'text-[#2563eb] font-bold' : '' }}">Contact</a>
                </nav>

                <!-- Right Actions (Search Icon, Cart, Auth) -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Search Icon -->
                    <a href="{{ url('/search') }}" class="text-slate-600 hover:text-slate-900 transition-colors p-1" title="Search Certifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </a>

                    <!-- Cart Icon -->
                    <a href="{{ url('/cart') }}" class="relative text-slate-600 hover:text-slate-900 transition-colors p-1" title="Shopping Cart">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        @php $cartCount = count(session('cart', [])); @endphp
                        <span id="cart-badge" class="absolute -top-1.5 -right-1.5 inline-flex items-center justify-center w-4 h-4 text-[10px] font-black text-white bg-[#2563eb] rounded-full {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                    </a>

                    <!-- Auth State -->
                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 text-slate-700 hover:text-[#2563eb] transition focus:outline-none">
                                <span class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-xs text-slate-800">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </span>
                                <span class="text-sm font-semibold max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-52 rounded-xl shadow-xl py-2 bg-white ring-1 ring-black/5 divide-y divide-gray-100 z-50">
                                <div class="px-4 py-2 text-xs text-slate-500">
                                    Signed in as <strong class="text-slate-800 block truncate">{{ auth()->user()->email }}</strong>
                                </div>
                                <div class="py-1">
                                    @if(auth()->user()->isAdmin())
                                        <a href="{{ url('/admin') }}" class="block px-4 py-2 text-xs font-bold text-amber-600 hover:bg-slate-50">Admin Dashboard</a>
                                    @endif
                                    <a href="{{ url('/dashboard') }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Student Workspace</a>
                                    <a href="{{ url('/dashboard/orders') }}" class="block px-4 py-2 text-xs text-slate-600 hover:bg-slate-50">Order History</a>
                                </div>
                                <div class="py-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-xs text-red-600 hover:bg-red-50 font-medium">Log Out</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-slate-700 hover:text-slate-900 border border-gray-200 hover:border-gray-300 hover:bg-slate-50 text-sm font-semibold px-4 py-1.5 rounded-lg transition">Log In</a>
                        <a href="{{ route('register') }}" class="bg-[#2563eb] hover:bg-blue-700 text-white text-sm font-semibold px-5 py-1.5 rounded-lg shadow-sm transition">Sign Up</a>
                    @endauth
                </div>

                <!-- Mobile Menu Hamburger -->
                <div class="flex items-center md:hidden space-x-3">
                    <a href="{{ url('/cart') }}" class="relative text-slate-600 p-1">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="absolute -top-1 -right-1 inline-flex items-center justify-center w-4 h-4 text-[9px] font-black text-white bg-[#2563eb] rounded-full {{ $cartCount > 0 ? '' : 'hidden' }}">{{ $cartCount }}</span>
                    </a>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-slate-600 hover:text-slate-900 focus:outline-none p-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden bg-white border-b border-gray-200 px-4 pt-2 pb-6 space-y-3 shadow-lg">
            <a href="{{ url('/vendors') }}" class="block px-3 py-2 rounded-md text-base font-semibold text-slate-700 hover:bg-slate-50">Vendors</a>
            <a href="{{ url('/certifications') }}" class="block px-3 py-2 rounded-md text-base font-semibold text-slate-700 hover:bg-slate-50">Certifications</a>
            <a href="{{ url('/contact') }}" class="block px-3 py-2 rounded-md text-base font-semibold text-slate-700 hover:bg-slate-50">Contact</a>
            <div class="pt-4 border-t border-gray-100 flex flex-col gap-2">
                @auth
                    <a href="{{ url('/dashboard') }}" class="w-full text-center bg-[#2563eb] text-white py-2.5 rounded-lg font-semibold">Student Workspace</a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-2 border border-gray-200 rounded-lg font-semibold text-slate-700">Log In</a>
                    <a href="{{ route('register') }}" class="w-full text-center bg-[#2563eb] text-white py-2.5 rounded-lg font-semibold shadow-sm">Sign Up</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Professional Dark Navy Footer matching 9-Panel Target Reference -->
    <footer class="bg-[#0A1628] text-slate-400 text-sm border-t border-slate-800/80 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <!-- Col 1: Brand Info -->
                <div class="space-y-4">
                    <x-application-logo :dark="true" />
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Your Partner in Exam Success. Providing industry-leading practice questions, test simulation, and detailed study materials for top IT certifications.
                    </p>
                    <p class="text-xs text-slate-500">
                        &copy; {{ date('Y') }} {{ $activeSiteName }}. All rights reserved.
                    </p>
                </div>

                <!-- Col 2: Quick Links -->
                <div>
                    <h3 class="text-white font-bold text-sm tracking-wider uppercase mb-4 font-sans">Quick Links</h3>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="{{ url('/certifications') }}" class="hover:text-white transition">Certifications</a></li>
                        <li><a href="{{ url('/vendors') }}" class="hover:text-white transition">Vendors</a></li>
                        <li><a href="{{ url('/pricing') }}" class="hover:text-white transition">Pricing</a></li>
                        <li><a href="{{ url('/test-engine') }}" class="hover:text-white transition">Test Engine</a></li>
                        <li><a href="{{ url('/blog') }}" class="hover:text-white transition">Blog</a></li>
                    </ul>
                </div>

                <!-- Col 3: Support -->
                <div>
                    <h3 class="text-white font-bold text-sm tracking-wider uppercase mb-4 font-sans">Support &amp; Legal</h3>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="{{ url('/faq') }}" class="hover:text-white transition">Help Center / FAQ</a></li>
                        <li><a href="{{ url('/contact') }}" class="hover:text-white transition">Contact Us</a></li>
                        <li><a href="{{ url('/free-demo') }}" class="hover:text-white transition">Free PDF Demo</a></li>
                        <li><a href="{{ url('/terms') }}" class="hover:text-white transition">Terms &amp; Conditions</a></li>
                        <li><a href="{{ url('/dmca') }}" class="hover:text-white transition">DMCA &amp; Copyright</a></li>
                        <li><a href="{{ url('/about') }}" class="hover:text-white transition">About Us</a></li>
                    </ul>
                </div>

                <!-- Col 4: Follow Us & Trust -->
                <div>
                    <h3 class="text-white font-bold text-sm tracking-wider uppercase mb-4 font-sans">Follow Us</h3>
                    <div class="flex items-center space-x-3 mb-6">
                        <a href="#" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-[#2563eb] text-white flex items-center justify-center transition" aria-label="Twitter">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-[#2563eb] text-white flex items-center justify-center transition" aria-label="LinkedIn">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                        <a href="#" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-[#2563eb] text-white flex items-center justify-center transition" aria-label="Facebook">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.5 5H18V0h-3.808C10.592 0 9 1.583 9 4.615V8z"/></svg>
                        </a>
                    </div>
                    <div class="inline-flex items-center space-x-2 bg-slate-800/60 border border-slate-700/60 px-3 py-1.5 rounded-lg text-xs text-slate-300">
                        <span class="w-2 h-2 rounded-full bg-[#00D4AA]"></span>
                        <span>100% Pass Guarantee</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Legal Bar -->
            <div class="border-t border-slate-800 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>Designed for professional IT certification candidates.</p>
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                    <a href="{{ url('/terms') }}" class="hover:text-slate-300 transition">Terms of Service</a>
                    <a href="{{ url('/dmca') }}" class="hover:text-slate-300 transition">DMCA Policy</a>
                    <a href="{{ url('/about') }}" class="hover:text-slate-300 transition">About Us</a>
                    <a href="{{ url('/sitemap.xml') }}" class="hover:text-slate-300 transition">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>