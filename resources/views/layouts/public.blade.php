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
    <meta name="robots" content="@yield('robots', config('seo.defaults.robots'))">

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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center space-x-2">
                        <svg class="h-8 w-8 text-cyan animate-pulse" viewBox="0 0 24 24" fill="currentColor">
                            <!-- Shuriken Icon SVG -->
                            <path d="M12 2L14.5 9.5H22L16 14L18.5 21.5L12 17L5.5 21.5L8 14L2 9.5H9.5L12 2Z" />
                        </svg>
                        <span class="text-xl font-bold tracking-tight text-white">Exams<span class="text-cyan">Ninja</span></span>
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden md:flex space-x-8 text-sm font-medium">
                    <a href="{{ url('/vendors') }}" class="text-gray-300 hover:text-cyan transition">Browse Vendors</a>
                    <a href="{{ url('/test-engine') }}" class="text-gray-300 hover:text-cyan transition flex items-center space-x-1">
                        <span>Test Engine</span>
                        <span class="bg-cyan text-navy font-extrabold text-[8px] px-1.5 py-0.5 rounded-full uppercase tracking-wide">New</span>
                    </a>
                    <a href="{{ url('/pricing') }}" class="text-gray-300 hover:text-cyan transition">Pricing</a>
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
                <a href="{{ url('/test-engine') }}" class="block px-3 py-2 rounded-md text-base font-medium text-cyan hover:bg-gray-800 flex items-center justify-between">
                    <span>Test Engine</span>
                    <span class="bg-cyan text-navy font-extrabold text-[8px] px-1.5 py-0.5 rounded-full uppercase tracking-wide">New</span>
                </a>
                <a href="{{ url('/pricing') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800">Pricing</a>
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
    <footer class="bg-navy text-gray-400 pt-16 pb-8 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Branding -->
            <div class="space-y-4">
                <div class="flex items-center space-x-2">
                    <svg class="h-8 w-8 text-cyan" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L14.5 9.5H22L16 14L18.5 21.5L12 17L5.5 21.5L8 14L2 9.5H9.5L12 2Z" />
                    </svg>
                    <span class="text-xl font-bold text-white">Exams<span class="text-cyan">Ninja</span></span>
                </div>
                <p class="text-sm">Pass your IT Certification Exam on the first attempt with our premium study guides, verified questions, and interactive practice engine.</p>
                <p class="text-xs text-gray-500">&copy; {{ date('Y') }} ExamsNinja. All rights reserved.</p>
            </div>

            <!-- Top Vendors -->
            <div>
                <h3 class="text-white font-semibold mb-4">Top Vendors</h3>
                <ul class="space-y-2 text-sm grid grid-cols-2 gap-x-4">
                    @foreach(App\Models\Vendor::where('is_active', true)->orderBy('name')->take(10)->get() as $footerVendor)
                        <li><a href="{{ route('vendors.show', $footerVendor->slug) }}" class="hover:text-cyan transition">{{ $footerVendor->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-white font-semibold mb-4">Support & Company</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/test-engine') }}" class="hover:text-cyan transition flex items-center space-x-1"><span>Test Engine</span> <span class="bg-cyan text-navy font-extrabold text-[8px] px-1.5 py-0.5 rounded-full uppercase tracking-wide">New</span></a></li>
                    <li><a href="{{ url('/pricing') }}" class="hover:text-cyan transition">Pricing Plans</a></li>
                    <li><a href="{{ url('/free-demo') }}" class="hover:text-cyan transition font-semibold text-cyan">Free Demo Guides</a></li>
                    <li><a href="{{ url('/blog') }}" class="hover:text-cyan transition">Industry Blog</a></li>
                    <li><a href="{{ url('/faq') }}" class="hover:text-cyan transition">FAQ & Support</a></li>
                    <li><a href="{{ url('/about') }}" class="hover:text-cyan transition">About Us</a></li>
                    <li><a href="{{ url('/contact') }}" class="hover:text-cyan transition">Contact Support</a></li>
                </ul>
            </div>

            <!-- Trust / Guarantee -->
            <div class="space-y-4">
                <h3 class="text-white font-semibold">100% Satisfaction</h3>
                <div class="bg-gray-800 p-4 rounded-md border border-gray-700 text-xs">
                    <p class="font-bold text-white mb-1">30-Day Money Back Guarantee</p>
                    <p>If you purchase any exam dump guide and fail the exam within 30 days of purchase, we offer a full 100% refund immediately.</p>
                </div>
                <div class="flex space-x-2">
                    <span class="bg-gray-800 text-cyan text-xs font-semibold px-2.5 py-0.5 rounded border border-gray-700">SSL SECURE</span>
                    <span class="bg-gray-800 text-cyan text-xs font-semibold px-2.5 py-0.5 rounded border border-gray-700">PCI COMPLIANT</span>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-gray-800 mt-12 pt-8 text-center text-xs text-gray-500">
            <p>Disclaimer: ExamsNinja.com is an independent provider of practice test materials. All trademarks, service marks, and brand names (such as Microsoft, AWS, Cisco, CompTIA, GCP) are the property of their respective owners. Their use does not imply any affiliation or endorsement.</p>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
