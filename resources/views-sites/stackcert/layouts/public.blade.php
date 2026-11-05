<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    @php
        $siteFavicon = ($currentSite && !empty($currentSite->favicon)) ? asset($currentSite->favicon) : (!empty($globalSettings['site_favicon']) ? asset($globalSettings['site_favicon']) : asset('favicon-32x32.png'));
        $customAppleIcon = !empty($globalSettings['apple_touch_icon']) ? asset($globalSettings['apple_touch_icon']) : asset('apple-touch-icon.png');
        // A site with its own favicon folder (public/sites/<name>/) also brings its own apple-touch icon and share image.
        $siteIconDir = ($currentSite && !empty($currentSite->favicon)) ? dirname($currentSite->favicon) : null;
        if ($siteIconDir && is_file(public_path($siteIconDir . '/apple-touch-icon.png'))) {
            $customAppleIcon = asset($siteIconDir . '/apple-touch-icon.png');
        }
        $ogDefault = ($currentSite && !empty($currentSite->og_image))
            ? asset($currentSite->og_image)
            : (!empty($globalSettings['default_og_image']) ? asset($globalSettings['default_og_image']) : asset(config('seo.defaults.og_image', 'images/og-default.png')));
    @endphp
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $siteFavicon }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $siteFavicon }}">
    <link rel="shortcut icon" href="{{ $siteFavicon }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $customAppleIcon }}">

    <!-- Site Verification Tags (Google, Bing, Yandex, Pinterest) -->
    @php
        $gscVerification = ($currentSite && !empty($currentSite->google_search_console_code)) ? $currentSite->google_search_console_code : ($globalSettings['seo_gsc_verification'] ?? config('seo.verification.google_search_console'));
        $bingVerification = $globalSettings['seo_bing_verification'] ?? '';
        $yandexVerification = $globalSettings['seo_yandex_verification'] ?? '';
        $pinterestVerification = $globalSettings['seo_pinterest_verification'] ?? '';

        $seoService = app(\App\Services\TechnicalSeoService::class);
        $rawCanonical = trim($__env->yieldContent('canonical_url'));
        $computedCanonical = !empty($rawCanonical) ? $seoService->buildCanonicalUrl($rawCanonical) : $seoService->buildCanonicalUrl();
        $yieldRobots = trim($__env->yieldContent('robots'));
        $computedRobots = !empty($yieldRobots) ? $yieldRobots : $seoService->robotsForRequest(request(), $currentSite);

        $defaultSiteTitle = ($currentSite && !empty($currentSite->default_seo_title)) ? $currentSite->default_seo_title : (!empty($globalSettings['default_seo_title']) ? $globalSettings['default_seo_title'] : config('seo.defaults.title'));
        $defaultSiteDesc = ($currentSite && !empty($currentSite->default_meta_description)) ? $currentSite->default_meta_description : (!empty($globalSettings['default_meta_description']) ? $globalSettings['default_meta_description'] : config('seo.defaults.description'));
        $defaultSiteKeywords = ($currentSite && !empty($currentSite->primary_keyword_strategy)) ? $currentSite->primary_keyword_strategy : (!empty($globalSettings['default_meta_keywords']) ? $globalSettings['default_meta_keywords'] : config('seo.defaults.keywords'));
        $activeSiteName = $currentSite ? $currentSite->name : (!empty($globalSettings['site_name']) ? $globalSettings['site_name'] : config('seo.site_name', 'Exam Topics Base'));
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
    <meta name="keywords" content="{!! \App\Services\SiteContext::scrubDumps($__env->yieldContent('meta_keywords', $defaultSiteKeywords)) !!}">
    <link rel="canonical" href="{{ $computedCanonical }}">
    <link rel="alternate" hreflang="en" href="{{ $computedCanonical }}">
    <link rel="alternate" hreflang="x-default" href="{{ $computedCanonical }}">
    <meta name="robots" content="{{ $computedRobots }}">
    <meta name="googlebot" content="{{ $computedRobots }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="@yield('og_type', $globalSettings['seo_og_type'] ?? config('seo.defaults.og_type', 'website'))">
    <meta property="og:url" content="{{ $computedCanonical }}">
    <meta property="og:title" content="@yield('title', $defaultSiteTitle)">
    <meta property="og:description" content="@yield('meta_description', $defaultSiteDesc)">
    <meta property="og:image" content="@yield('og_image', $ogDefault)">
    <meta property="og:site_name" content="{{ $activeSiteName }}">
    @if(!empty($globalSettings['seo_facebook_app_id']))
        <meta property="fb:app_id" content="{{ $globalSettings['seo_facebook_app_id'] }}">
    @endif

    <!-- Twitter / X Cards -->
    <meta name="twitter:card" content="{{ $globalSettings['seo_twitter_card'] ?? 'summary_large_image' }}">
    <meta name="twitter:site" content="{{ $globalSettings['social_twitter'] ?? config('seo.social.twitter_handle', '@ExamTopicsBase') }}">
    <meta name="twitter:creator" content="{{ $globalSettings['social_twitter'] ?? config('seo.social.twitter_handle', '@ExamTopicsBase') }}">
    <meta name="twitter:url" content="@yield('canonical_url', $computedCanonical)">
    <meta name="twitter:title" content="@yield('title', $defaultSiteTitle)">
    <meta name="twitter:description" content="@yield('meta_description', $defaultSiteDesc)">
    <meta name="twitter:image" content="@yield('og_image', $ogDefault)">
    <meta property="twitter:card" content="{{ $globalSettings['seo_twitter_card'] ?? 'summary_large_image' }}">
    <meta property="twitter:title" content="@yield('title', $defaultSiteTitle)">
    <meta property="twitter:description" content="@yield('meta_description', $defaultSiteDesc)">
    <meta property="twitter:image" content="@yield('og_image', $ogDefault)">

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

    <!-- Custom SEO Tags (Schema.org JSON-LD from page views) -->
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

    @php
        $brandPrimary = ($currentSite && !empty($currentSite->primary_color)) ? $currentSite->primary_color : '#0F6CBD';
        $brandDark = ($currentSite && !empty($currentSite->secondary_color)) ? $currentSite->secondary_color : '#0B3A66';
    @endphp
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: {{ $brandPrimary }};
            --brand-dark: {{ $brandDark }};
            --brand-soft: color-mix(in srgb, var(--brand) 9%, white);
        }
        body { font-family: 'Inter', sans-serif; background-color: #F6F8FB; color: #1E293B; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; letter-spacing: -0.01em; }
        /* Shared pages use these accent utilities; map them onto this site's brand. */
        .bg-navy { background-color: #0A1628; }
        .text-navy { color: #0A1628; }
        .text-cyan { color: var(--brand); }
        .bg-cyan { background-color: var(--brand); }
        .border-cyan { border-color: var(--brand); }
        .hover-bg-cyan:hover { background-color: var(--brand-dark); }
        .text-orange { color: #F97316; }
        .bg-orange { background-color: #F97316; }
        .hover-bg-orange:hover { background-color: #EA580C; }
        .ring-brand:focus { outline: 2px solid var(--brand); outline-offset: 2px; }
    </style>
    @yield('styles')
</head>
<body class="antialiased min-h-screen flex flex-col">
    @include('partials.site-header')

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    @include('partials.site-footer')

    @yield('scripts')
</body>
</html>
