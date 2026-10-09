@extends('layouts.public')

@section('title', 'Pricing Plans & Certification Bundles | ProExamTopics')
@section('meta_description', 'Flexible pricing options for every IT professional. Choose between individual exam access, monthly simulator subscriptions, and vendor bundles.')

@section('content')
<div x-data="{ isAnnual: false }">
    <!-- Premium Pricing Hero (Panel 5) -->
    <x-site-b.hero variant="pricing" glowColor="blue" padding="compact">
        <div class="relative max-w-4xl mx-auto text-center">
            <!-- Floating Pricing Shape Left -->
            <div class="cpm-float hidden md:flex absolute -left-8 top-6 items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900/90 border border-slate-700/80 shadow-xl backdrop-blur-md">
                <span class="text-xs font-mono-code font-bold text-[#00D4AA]">$19.99/mo</span>
                <span class="text-[10px] text-slate-400">Annual Saver</span>
            </div>

            <!-- Floating Guarantee Shape Right -->
            <div class="cpm-float-reverse hidden md:flex absolute -right-8 top-12 items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900/90 border border-slate-700/80 shadow-xl backdrop-blur-md">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span class="text-[10px] font-bold text-white">30-Day Money-Back</span>
            </div>

            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/60 text-xs font-semibold text-slate-300 mb-4 backdrop-blur-sm shadow-sm">
                <span class="w-2 h-2 rounded-full bg-[#00D4AA] animate-pulse"></span>
                <span class="font-mono-code text-[#00D4AA] font-bold">TRANSPARENT VALUE</span>
                <span class="text-slate-500">|</span>
                <span>Instant Activation • Cancel Anytime</span>
            </div>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight mb-3">
                Simple, Transparent Plans for Your <span class="bg-gradient-to-r from-blue-400 via-cyan-300 to-[#00D4AA] bg-clip-text text-transparent">Certification Journey</span>
            </h1>
            <p class="text-slate-300 text-sm sm:text-base max-w-xl mx-auto mb-8 leading-relaxed">
                Choose the preparation plan that fits your study timeline. Unlimited test engine simulations and guaranteed syllabus updates.
            </p>

            <!-- Billing Toggle Switch (Panel 5) -->
            <div class="inline-flex items-center justify-center gap-3 bg-slate-900/90 border border-slate-700/80 rounded-full px-5 py-2.5 backdrop-blur-md shadow-2xl">
                <span class="text-xs sm:text-sm font-semibold transition" :class="!isAnnual ? 'text-white font-bold' : 'text-slate-400'">Monthly Billing</span>
                <button 
                    @click="isAnnual = !isAnnual" 
                    class="w-12 h-6 rounded-full bg-slate-700 p-1 flex items-center transition duration-300 focus:outline-none"
                    :class="isAnnual ? 'bg-[#2563eb]' : 'bg-slate-600'"
                    aria-label="Toggle annual pricing"
                >
                    <div class="w-4 h-4 rounded-full bg-white shadow-md transform transition duration-300" :class="isAnnual ? 'translate-x-6' : ''"></div>
                </button>
                <span class="text-xs sm:text-sm font-semibold flex items-center gap-1.5 transition" :class="isAnnual ? 'text-white font-bold' : 'text-slate-400'">
                    <span>Annual Billing</span>
                    <span class="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-black px-2 py-0.5 rounded-full uppercase">Save 30%</span>
                </span>
            </div>
        </div>
    </x-site-b.hero>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">

    <!-- 3-Tier Pricing Cards (Panel 5) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch max-w-6xl mx-auto mb-20">
        <!-- Basic Plan -->
        <div class="bg-white border border-gray-200/90 rounded-2xl p-7 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                <h3 class="text-base font-bold text-slate-900">Basic</h3>
                <div class="mt-4 mb-6">
                    <span class="text-3xl sm:text-4xl font-black text-slate-900 font-mono-code" x-text="isAnnual ? '$19.99' : '$29.99'">$29.99</span>
                    <span class="text-xs text-slate-500 font-medium">/month</span>
                </div>
                <ul class="space-y-3 text-xs text-slate-600 mb-8">
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Practice Questions</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Basic Explanations</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>1-Month Access</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Mobile & Desktop</span>
                    </li>
                </ul>
            </div>
            <a href="{{ url('/search') }}" class="w-full text-center bg-white border-2 border-[#2563eb] text-[#2563eb] hover:bg-blue-50 font-bold py-2.5 rounded-xl text-xs sm:text-sm transition">
                Get Started
            </a>
        </div>

        <!-- Standard Plan (Most Popular) -->
        <div class="bg-white border-2 border-[#2563eb] rounded-2xl p-7 shadow-xl flex flex-col justify-between relative transform md:-translate-y-2">
            <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-[#2563eb] text-white text-[10px] font-black tracking-wider uppercase py-1 px-4 rounded-full shadow-sm">
                Most Popular
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 mt-2">Standard</h3>
                <div class="mt-4 mb-6">
                    <span class="text-3xl sm:text-4xl font-black text-slate-900 font-mono-code" x-text="isAnnual ? '$34.99' : '$49.99'">$49.99</span>
                    <span class="text-xs text-slate-500 font-medium">/month</span>
                </div>
                <ul class="space-y-3 text-xs text-slate-700 mb-8 font-medium">
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Everything in Basic</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Detailed Explanations</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Latest Updates</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Performance Tracking</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>30-Day Guarantee</span>
                    </li>
                </ul>
            </div>
            <a href="{{ url('/search') }}" class="w-full text-center bg-[#2563eb] hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-xs sm:text-sm shadow-md transition">
                Get Started
            </a>
        </div>

        <!-- Premium Plan -->
        <div class="bg-white border border-gray-200/90 rounded-2xl p-7 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                <h3 class="text-base font-bold text-slate-900">Premium</h3>
                <div class="mt-4 mb-6">
                    <span class="text-3xl sm:text-4xl font-black text-slate-900 font-mono-code" x-text="isAnnual ? '$49.99' : '$69.99'">$69.99</span>
                    <span class="text-xs text-slate-500 font-medium">/month</span>
                </div>
                <ul class="space-y-3 text-xs text-slate-600 mb-8">
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Everything in Standard</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Exam Simulator</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Study Notes (PDF)</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>Priority Support</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-500 font-bold">✓</span>
                        <span>6 Months Access</span>
                    </li>
                </ul>
            </div>
            <a href="{{ url('/search') }}" class="w-full text-center bg-[#2563eb] hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl text-xs sm:text-sm transition shadow-sm">
                Get Started
            </a>
        </div>
    </div>

    <!-- Bundle & Save Section (Panel 5) -->
    <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl font-bold text-slate-900 tracking-tight mb-6">Bundle & Save</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
                $dbPackages = \App\Models\Package::take(3)->get();
                $fallbackBundles = [
                    ['name' => 'Microsoft Bundle', 'exams' => '5 exams', 'price' => 199, 'badge' => 'Save 30%'],
                    ['name' => 'Cisco Bundle', 'exams' => '5 exams', 'price' => 179, 'badge' => 'Save 30%'],
                    ['name' => 'All Access Bundle', 'exams' => '50+ exams', 'price' => 499, 'badge' => 'Best Value'],
                ];
            @endphp

            @if($dbPackages->count() > 0)
                @foreach($dbPackages as $idx => $pkg)
                    <div class="bg-white border border-gray-200/90 rounded-xl p-6 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-base font-bold text-slate-900">{{ $pkg->name }}</h3>
                                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-0.5 rounded-full uppercase">
                                    {{ $idx === 2 ? 'Best Value' : 'Save 30%' }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500">{{ $pkg->exam_count ?? 5 }} certification exams included</p>
                            <div class="mt-4 mb-6">
                                <span class="text-2xl sm:text-3xl font-black text-slate-900 font-mono-code">${{ number_format($pkg->price ?? 199, 0) }}</span>
                            </div>
                        </div>
                        <a href="{{ url('/search') }}" class="w-full text-center bg-slate-100 hover:bg-[#2563eb] hover:text-white text-slate-800 text-xs font-bold py-2 rounded-lg transition">
                            View Bundle
                        </a>
                    </div>
                @endforeach
            @else
                @foreach($fallbackBundles as $fb)
                    <div class="bg-white border border-gray-200/90 rounded-xl p-6 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-base font-bold text-slate-900">{{ $fb['name'] }}</h3>
                                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-black px-2 py-0.5 rounded-full uppercase">{{ $fb['badge'] }}</span>
                            </div>
                            <p class="text-xs text-slate-500">{{ $fb['exams'] }} included</p>
                            <div class="mt-4 mb-6">
                                <span class="text-2xl sm:text-3xl font-black text-slate-900 font-mono-code">${{ $fb['price'] }}</span>
                            </div>
                        </div>
                        <a href="{{ url('/search') }}" class="w-full text-center bg-slate-100 hover:bg-[#2563eb] hover:text-white text-slate-800 text-xs font-bold py-2 rounded-lg transition">
                            View Bundle
                        </a>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection