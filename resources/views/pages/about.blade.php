@extends('layouts.public')

@section('title', 'About Us - Exam Topics Base')

@section('content')
<!-- Header Banner -->
<section class="bg-navy text-white py-16 text-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-4">
            Our Mission & Story
        </h1>
        <p class="text-lg text-gray-300 max-w-2xl mx-auto">
            Providing high-quality, verified preparation study guides and browser-based testing engines to IT professionals globally.
        </p>
    </div>
</section>

<!-- Company Details -->
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="space-y-4">
            <h2 class="text-2xl font-bold text-navy">Pass Certification Exams with Confidence</h2>
            <p class="text-sm text-gray-600 leading-relaxed">Exam Topics Base was founded in 2022 by a group of senior cloud architects and network security engineers who noticed that many certification dumps online were outdated, filled with incorrect answers, and lacked proper technical explanations.</p>
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
