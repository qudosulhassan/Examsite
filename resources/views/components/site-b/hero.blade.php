@props([
    'variant' => 'default',
    'glowColor' => 'cyan',
    'padding' => 'default', // default, compact, large, product
])

@php
    $glowClasses = [
        'cyan' => 'from-blue-600/25 via-[#00D4AA]/15 to-transparent',
        'blue' => 'from-[#2563eb]/30 via-cyan-500/15 to-transparent',
        'purple' => 'from-purple-600/25 via-blue-600/15 to-transparent',
        'emerald' => 'from-emerald-500/25 via-[#00D4AA]/15 to-transparent',
        'amber' => 'from-amber-600/20 via-orange-500/15 to-transparent',
    ];
    $glowClass = $glowClasses[$glowColor] ?? $glowClasses['cyan'];

    $paddingClasses = [
        'default' => 'py-10 lg:py-14',
        'compact' => 'py-6 lg:py-8',
        'large' => 'py-12 lg:py-16',
        'product' => 'pt-8 pb-8 lg:pt-12 lg:pb-10',
    ];
    $pyClass = $paddingClasses[$padding] ?? $paddingClasses['default'];
@endphp

<section class="cpm-hero-system hero--{{ $variant }} relative overflow-hidden text-white border-b border-slate-800/80" style="background-color: #071328;">
    @if($variant === 'home')
        <!-- Atmospheric Ambient Glows matching Target Reference Image 1 -->
        <!-- Top Left Vibrant Teal Glow -->
        <div class="absolute -top-24 -left-16 w-[560px] h-[560px] rounded-full pointer-events-none blur-3xl opacity-75" style="background: radial-gradient(circle, rgba(0, 212, 170, 0.28) 0%, rgba(0, 229, 255, 0.12) 40%, transparent 70%);"></div>

        <!-- Top Right Deep Indigo / Blue Glow -->
        <div class="absolute -top-24 -right-16 w-[650px] h-[650px] rounded-full pointer-events-none blur-3xl opacity-80" style="background: radial-gradient(circle, rgba(37, 99, 235, 0.35) 0%, rgba(30, 64, 175, 0.15) 50%, transparent 75%);"></div>

        <!-- Center Ambient Top Light -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[750px] h-[360px] pointer-events-none blur-2xl opacity-60" style="background: radial-gradient(ellipse at top, rgba(0, 212, 170, 0.14) 0%, rgba(37, 99, 235, 0.1) 45%, transparent 75%);"></div>

        <!-- Soft Volumetric 3D Faceted Crystal Shapes matching Image 1 -->
        <!-- Left 3D Facet Cluster -->
        <div class="cpm-float absolute top-10 left-4 lg:left-14 w-60 h-60 pointer-events-none opacity-30 hidden sm:block">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <polygon points="100,20 170,60 170,140 100,180 30,140 30,60" fill="url(#tealFacet1)" stroke="rgba(0, 212, 170, 0.25)" stroke-width="1.5"/>
                <polygon points="100,20 170,60 100,100 30,60" fill="url(#tealFacetTop)" stroke="rgba(0, 212, 170, 0.2)" stroke-width="1"/>
                <polygon points="30,60 100,100 100,180 30,140" fill="url(#tealFacetLeft)" stroke="rgba(0, 212, 170, 0.2)" stroke-width="1"/>
                <polygon points="170,60 100,100 100,180 170,140" fill="url(#tealFacetRight)" stroke="rgba(0, 212, 170, 0.2)" stroke-width="1"/>
                <defs>
                    <linearGradient id="tealFacet1" x1="30" y1="20" x2="170" y2="180" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#00D4AA" stop-opacity="0.14"/>
                        <stop offset="1" stop-color="#071328" stop-opacity="0.02"/>
                    </linearGradient>
                    <linearGradient id="tealFacetTop" x1="100" y1="20" x2="100" y2="100" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#00E5FF" stop-opacity="0.22"/>
                        <stop offset="1" stop-color="#00D4AA" stop-opacity="0.06"/>
                    </linearGradient>
                    <linearGradient id="tealFacetLeft" x1="30" y1="60" x2="100" y2="180" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#00D4AA" stop-opacity="0.12"/>
                        <stop offset="1" stop-color="#071328" stop-opacity="0.02"/>
                    </linearGradient>
                    <linearGradient id="tealFacetRight" x1="170" y1="60" x2="100" y2="180" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#2563EB" stop-opacity="0.15"/>
                        <stop offset="1" stop-color="#071328" stop-opacity="0.02"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <!-- Right 3D Facet Cluster -->
        <div class="cpm-float-reverse absolute top-8 right-4 lg:right-14 w-64 h-64 pointer-events-none opacity-35 hidden sm:block">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                <polygon points="100,15 180,60 180,145 100,190 20,145 20,60" fill="url(#blueFacet1)" stroke="rgba(37, 99, 235, 0.3)" stroke-width="1.5"/>
                <polygon points="100,15 180,60 100,105 20,60" fill="url(#blueFacetTop)" stroke="rgba(37, 99, 235, 0.22)" stroke-width="1"/>
                <polygon points="20,60 100,105 100,190 20,145" fill="url(#blueFacetLeft)" stroke="rgba(37, 99, 235, 0.18)" stroke-width="1"/>
                <polygon points="180,60 100,105 100,190 180,145" fill="url(#blueFacetRight)" stroke="rgba(0, 229, 255, 0.22)" stroke-width="1"/>
                <defs>
                    <linearGradient id="blueFacet1" x1="20" y1="15" x2="180" y2="190" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#2563EB" stop-opacity="0.16"/>
                        <stop offset="1" stop-color="#071328" stop-opacity="0.02"/>
                    </linearGradient>
                    <linearGradient id="blueFacetTop" x1="100" y1="15" x2="100" y2="105" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#3B82F6" stop-opacity="0.24"/>
                        <stop offset="1" stop-color="#2563EB" stop-opacity="0.08"/>
                    </linearGradient>
                    <linearGradient id="blueFacetLeft" x1="20" y1="60" x2="100" y2="190" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#1D4ED8" stop-opacity="0.14"/>
                        <stop offset="1" stop-color="#071328" stop-opacity="0.02"/>
                    </linearGradient>
                    <linearGradient id="blueFacetRight" x1="180" y1="60" x2="100" y2="190" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#00D4AA" stop-opacity="0.16"/>
                        <stop offset="1" stop-color="#2563EB" stop-opacity="0.04"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>
    @elseif($variant === 'exam')
        <!-- Exam Product Page Atmospheric Ambient Glows -->
        <div class="absolute -top-24 -left-20 w-[550px] h-[550px] rounded-full pointer-events-none blur-3xl opacity-60" style="background: radial-gradient(circle, rgba(0, 212, 170, 0.22) 0%, rgba(0, 229, 255, 0.1) 45%, transparent 70%);"></div>
        <div class="absolute -top-20 -right-20 w-[600px] h-[600px] rounded-full pointer-events-none blur-3xl opacity-70" style="background: radial-gradient(circle, rgba(37, 99, 235, 0.28) 0%, rgba(30, 64, 175, 0.12) 50%, transparent 75%);"></div>
        <div class="absolute inset-0 pointer-events-none" style="opacity: 0.025; background-image: linear-gradient(rgba(255,255,255,0.4) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.4) 1px, transparent 1px); background-size: 32px 32px;"></div>
    @else
        <!-- Other variants (vendors, test-engine, blog, etc.) with controlled subtle background -->
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[850px] h-[460px] rounded-full pointer-events-none blur-[110px] opacity-60" style="background: radial-gradient(ellipse at top, rgba(37,99,235,0.3) 0%, rgba(0,212,170,0.15) 50%, transparent 80%);"></div>
        <div class="absolute inset-0 pointer-events-none" style="opacity: 0.02; background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px); background-size: 40px 40px;"></div>
    @endif

    <!-- Content Slot -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 {{ $pyClass }}">
        @if(isset($breadcrumbs))
            <div class="mb-4 sm:mb-5">
                {{ $breadcrumbs }}
            </div>
        @endif

        {{ $slot }}
    </div>

    <!-- Bottom Edge Cyan Accent Rule -->
    <div class="absolute bottom-0 inset-x-0 h-[1px]" style="background: linear-gradient(90deg, transparent, rgba(0, 212, 170, 0.35), transparent);"></div>
</section>

<style>
@keyframes cpmHeroFloat {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-8px) rotate(1deg); }
}

@keyframes cpmHeroFloatReverse {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(8px) rotate(-1deg); }
}

@keyframes cpmGlowPulse {
    0%, 100% { opacity: 0.85; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.04); }
}

@keyframes cpmScanlineMove {
    0% { transform: translateY(-100%); }
    100% { transform: translateY(1000%); }
}

@keyframes cpmPulseSlow {
    0%, 100% { opacity: 0.3; transform: scale(0.98); }
    50% { opacity: 0.8; transform: scale(1.02); }
}

.cpm-float {
    animation: cpmHeroFloat 6s ease-in-out infinite;
}

.cpm-float-reverse {
    animation: cpmHeroFloatReverse 7s ease-in-out infinite;
}

.cpm-hero-glow {
    animation: cpmGlowPulse 8s ease-in-out infinite alternate;
}

.cpm-pulse-slow {
    animation: cpmPulseSlow 4s ease-in-out infinite alternate;
}

.cpm-scanline {
    width: 100%;
    height: 120px;
    background: linear-gradient(180deg, transparent, rgba(0,212,170,0.18), transparent);
    animation: cpmScanlineMove 6s linear infinite;
}

@media (prefers-reduced-motion: reduce) {
    .cpm-float,
    .cpm-float-reverse,
    .cpm-hero-glow,
    .cpm-pulse-slow,
    .cpm-scanline {
        animation: none !important;
        transform: none !important;
    }
}
</style>
