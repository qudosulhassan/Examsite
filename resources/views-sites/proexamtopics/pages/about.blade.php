@extends('layouts.public')

@section('title', 'About Us - ' . ($currentSite->name ?? ($globalSettings['site_name'] ?? config('app.name', 'ProExamTopics'))))

@section('content')
<!-- Premium Brand Story Hero -->
<x-site-b.hero variant="about" glowColor="cyan" padding="compact">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center max-w-6xl mx-auto">
        <!-- Left: Mission Statement & Credibility -->
        <div class="lg:col-span-8 text-center lg:text-left">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/60 text-xs font-semibold text-slate-300 mb-4 backdrop-blur-sm shadow-sm">
                <span class="w-2 h-2 rounded-full bg-[#00D4AA] animate-pulse"></span>
                <span class="font-mono-code text-[#00D4AA] font-bold">ABOUT PROEXAMTOPICS</span>
                <span class="text-slate-500">|</span>
                <span>Trust & Academic Integrity</span>
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight mb-4 leading-tight">
                Our Mission & <span class="bg-gradient-to-r from-[#00D4AA] via-cyan-300 to-blue-400 bg-clip-text text-transparent">Brand Story</span>
            </h1>
            <p class="text-sm sm:text-base text-slate-300 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                Founded by certified enterprise engineers, ProExamTopics delivers verified, accurate study guides and high-fidelity browser testing simulations to empower IT careers across the globe.
            </p>
        </div>

        <!-- Right: ProExamTopics Shield Emblem & Trust Particles -->
        <div class="lg:col-span-4 relative hidden lg:flex items-center justify-center">
            <div class="relative w-60 h-52 flex items-center justify-center">
                <div class="absolute inset-0 bg-blue-500/15 rounded-full blur-2xl"></div>
                
                <!-- Central Trust Shield Emblem -->
                <div class="cpm-float w-40 h-40 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 border border-cyan-500/30 p-4 shadow-2xl flex flex-col items-center justify-center text-center backdrop-blur-md">
                    <div class="w-12 h-12 rounded-xl bg-[#00D4AA]/15 border border-[#00D4AA]/40 text-[#00D4AA] flex items-center justify-center mb-2">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <span class="text-xs font-bold text-white font-mono-code uppercase tracking-wider">Certified</span>
                    <span class="text-[10px] text-cyan-300">Subject Experts</span>
                </div>
            </div>
        </div>
    </div>
</x-site-b.hero>

<!-- Company Details -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="space-y-4">
            <h2 class="text-2xl font-bold text-navy">Pass Certification Exams with Confidence</h2>
            <p class="text-sm text-gray-600 leading-relaxed">{{ $currentSite->name ?? ($globalSettings['site_name'] ?? 'Our platform') }} was founded in 2022 by a group of senior cloud architects and network security engineers who noticed that many certification dumps online were outdated, filled with incorrect answers, and lacked proper technical explanations.</p>
            <p class="text-sm text-gray-600 leading-relaxed">We set out to create a platform that provides verified, blazingly fast updates for IT exams, paired with a web-based testing engine that doesn't require downloading unsafe binaries. Our materials help students actually understand the reasoning behind questions, rather than relying on raw memorization.</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-6 border-t border-b border-gray-150 py-8">
            <div class="text-center">
                <span class="block text-3xl font-extrabold text-cyan">3,500+</span>
                <span class="text-xs font-semibold text-gray-400 uppercase">Active Exams</span>
            </div>
            <div class="text-center">
                <span class="block text-3xl font-extrabold text-cyan">200K+</span>
                <span class="text-xs font-semibold text-gray-400 uppercase">Students Passed</span>
            </div>
            <div class="text-center">
                <span class="block text-3xl font-extrabold text-cyan">99.6%</span>
                <span class="text-xs font-semibold text-gray-400 uppercase">Success Rate</span>
            </div>
            <div class="text-center">
                <span class="block text-3xl font-extrabold text-cyan">17+</span>
                <span class="text-xs font-semibold text-gray-400 uppercase">Core Vendors</span>
            </div>
        </div>

        <div class="space-y-4">
            <h3 class="text-xl font-bold text-navy">Our Core Values</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-600">
                <div class="space-y-2">
                    <h4 class="font-bold text-navy">Accuracy First</h4>
                    <p class="leading-relaxed">Every question in our database is verified by at least two certified subject matter experts before release.</p>
                </div>
                <div class="space-y-2">
                    <h4 class="font-bold text-navy">Instant Fulfillment</h4>
                    <p class="leading-relaxed">No delays. Your access is instantly set up on Cloudflare R2 object storage immediately post-payment.</p>
                </div>
                <div class="space-y-2">
                    <h4 class="font-bold text-navy">Guaranteed Results</h4>
                    <p class="leading-relaxed">We stand by our products. If you fail using our dumps, we provide a full refund without questions.</p>
                </div>
                <div class="space-y-2">
                    <h4 class="font-bold text-navy">Constant Updates</h4>
                    <p class="leading-relaxed">IT certification scopes change fast. We monitor blueprint changes weekly and refresh files within 30 days.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
