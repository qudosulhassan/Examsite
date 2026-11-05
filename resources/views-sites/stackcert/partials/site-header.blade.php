{{-- StackCert header: light, sticky, with the shared promo banner, cart badge and account menu. --}}
@php
    $isBannerActive = ($globalSettings['home_banner_active'] ?? '0') === '1';
    $bannerStart = !empty($globalSettings['home_banner_start_date']) ? strtotime($globalSettings['home_banner_start_date']) : null;
    $bannerEnd = !empty($globalSettings['home_banner_end_date']) ? strtotime($globalSettings['home_banner_end_date']) : null;
    $now = time();
    if (($bannerStart && $now < $bannerStart) || ($bannerEnd && $now > $bannerEnd)) {
        $isBannerActive = false;
    }

    $brandName = $currentSite ? $currentSite->name : ($globalSettings['site_name'] ?? config('app.name'));
    $brandLogo = ($currentSite && !empty($currentSite->logo)) ? asset($currentSite->logo) : null;
    $cartCount = count(session('cart', []));
    $onlyVendor = (count((array) config('site.vendors', [])) === 1) ? config('site.vendors')[0] : null;
    $examsUrl = $onlyVendor ? url('/vendors/' . $onlyVendor) : url('/vendors');
@endphp

<header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-slate-200" x-data="{ mobileMenuOpen: false }">
    @if($isBannerActive)
        <div x-data="{ showBanner: true }" x-show="showBanner" x-cloak class="relative bg-slate-900 text-white py-2 px-10 text-center text-xs sm:text-sm">
            <span class="font-semibold">{{ $globalSettings['home_banner_text'] ?? '' }}</span>
            @if(!empty($globalSettings['home_banner_coupon']))
                <span class="mx-1 rounded bg-white/15 px-2 py-0.5 font-mono text-xs font-bold">{{ $globalSettings['home_banner_coupon'] }}</span>
            @endif
            @if(!empty($globalSettings['home_banner_link']) && !empty($globalSettings['home_banner_button_text']))
                <a href="{{ $globalSettings['home_banner_link'] }}" class="ml-1 inline-flex items-center rounded bg-white px-2.5 py-1 text-[11px] font-bold uppercase text-slate-900 hover:bg-slate-100">{{ $globalSettings['home_banner_button_text'] }}</a>
            @endif
            <button @click="showBanner = false" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/70 hover:text-white" aria-label="Dismiss">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    <div class="container-custom">
        <div class="flex h-[68px] items-center justify-between gap-6">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5" aria-label="{{ $brandName }} home">
                @if($brandLogo)
                    <img src="{{ $brandLogo }}" alt="{{ $brandName }} logo" class="h-6 w-auto max-w-[210px] object-contain sm:h-7 sm:max-w-[270px]" width="270" height="30">
                @else
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl text-base font-black text-white" style="background: linear-gradient(135deg, var(--brand), var(--brand-dark));">{{ strtoupper(mb_substr($brandName, 0, 1)) }}</span>
                    <span class="text-lg font-extrabold tracking-tight text-slate-900">{{ $brandName }}</span>
                @endif
            </a>

            <nav class="hidden md:flex items-center gap-1 text-sm font-semibold text-slate-600" aria-label="Main">
                <a href="{{ $examsUrl }}" class="rounded-lg px-3 py-2 hover:bg-slate-100 hover:text-slate-900">All exams</a>
                <a href="{{ url('/') }}#tracks" class="rounded-lg px-3 py-2 hover:bg-slate-100 hover:text-slate-900">Tracks</a>
                <a href="{{ url('/certifications') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100 hover:text-slate-900">Certifications</a>
                <a href="{{ url('/test-engine') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100 hover:text-slate-900">Test Engine</a>
                <a href="{{ url('/contact') }}" class="rounded-lg px-3 py-2 hover:bg-slate-100 hover:text-slate-900">Contact</a>
            </nav>

            <div class="hidden md:flex items-center gap-2">
                <a href="{{ url('/search') }}" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900" aria-label="Search exams">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                </a>
                <a href="{{ url('/cart') }}" class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900" aria-label="Cart">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span id="cart-badge" class="absolute -right-0.5 -top-0.5 inline-flex min-w-[18px] items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none text-white {{ $cartCount > 0 ? '' : 'hidden' }}" style="background: var(--brand);">{{ $cartCount }}</span>
                </a>

                @auth
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 rounded-lg py-1 pl-1 pr-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 focus:outline-none">
                            <img class="h-8 w-8 rounded-full object-cover ring-1 ring-slate-200" src="{{ auth()->user()->avatar_url }}" alt="">
                            <span class="max-w-[140px] truncate">{{ auth()->user()->name }}</span>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 z-50 mt-2 w-52 rounded-xl bg-white py-1 text-sm text-slate-700 shadow-lg ring-1 ring-slate-200" style="display:none;">
                            @if(auth()->user()->isAdmin())
                                <a href="{{ url('/admin') }}" class="block px-4 py-2 font-semibold hover:bg-slate-50" style="color: var(--brand);">Admin panel</a>
                            @endif
                            <a href="{{ url('/dashboard') }}" class="block px-4 py-2 hover:bg-slate-50">Dashboard</a>
                            <a href="{{ url('/dashboard/my-exams') }}" class="block px-4 py-2 hover:bg-slate-50">My purchased guides</a>
                            <a href="{{ url('/dashboard/profile') }}" class="block px-4 py-2 hover:bg-slate-50">Profile settings</a>
                            <hr class="my-1 border-slate-100">
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-red-600 hover:bg-slate-50">Log out</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-lg px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:opacity-90" style="background: var(--brand);">Sign up</a>
                @endauth
            </div>

            <div class="flex items-center md:hidden">
                <a href="{{ url('/cart') }}" class="relative mr-1 rounded-lg p-2 text-slate-600" aria-label="Cart">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span id="cart-badge-mobile" class="absolute -right-0.5 -top-0.5 inline-flex min-w-[18px] items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none text-white {{ $cartCount > 0 ? '' : 'hidden' }}" style="background: var(--brand);">{{ $cartCount }}</span>
                </a>
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="rounded-lg p-2 text-slate-600 focus:outline-none" aria-label="Menu">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display:none;"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="mobileMenuOpen" class="border-t border-slate-200 bg-white md:hidden" style="display:none;">
        <div class="space-y-1 px-3 py-3">
            <a href="{{ $examsUrl }}" class="block rounded-lg px-3 py-2 text-base font-semibold text-slate-700 hover:bg-slate-50">All exams</a>
            <a href="{{ url('/') }}#tracks" class="block rounded-lg px-3 py-2 text-base font-semibold text-slate-700 hover:bg-slate-50">Tracks</a>
            <a href="{{ url('/certifications') }}" class="block rounded-lg px-3 py-2 text-base font-semibold text-slate-700 hover:bg-slate-50">Certifications</a>
            <a href="{{ url('/test-engine') }}" class="block rounded-lg px-3 py-2 text-base font-semibold text-slate-700 hover:bg-slate-50">Test Engine</a>
            <a href="{{ url('/contact') }}" class="block rounded-lg px-3 py-2 text-base font-semibold text-slate-700 hover:bg-slate-50">Contact</a>
            <hr class="my-2 border-slate-100">
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ url('/admin') }}" class="block rounded-lg px-3 py-2 text-base font-semibold hover:bg-slate-50" style="color: var(--brand);">Admin panel</a>
                @endif
                <a href="{{ url('/dashboard') }}" class="block rounded-lg px-3 py-2 text-base font-semibold text-slate-700 hover:bg-slate-50">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-base font-semibold text-red-600 hover:bg-slate-50">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-base font-semibold text-slate-700 hover:bg-slate-50">Log in</a>
                <a href="{{ route('register') }}" class="mt-1 block rounded-lg px-3 py-2.5 text-center text-base font-bold text-white" style="background: var(--brand);">Sign up</a>
            @endauth
        </div>
    </div>
</header>
