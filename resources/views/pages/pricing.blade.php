@extends('layouts.public')

@section('title', 'Pricing Plans & Bundles - ExamsNinja')

@section('content')
<!-- Header Banner -->
<section class="bg-navy text-white py-16 text-center">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-4">
            Simple, Transparent Pricing
        </h1>
        <p class="text-lg text-gray-300 max-w-2xl mx-auto">
            Choose the subscription plan that fits your preparation style or select a single guide package below.
        </p>
    </div>
</section>

<!-- Subscription Tiers (Alpine Toggle) -->
<section class="py-16 bg-white" x-data="{ billingCycle: 'monthly' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Toggle Button -->
        <div class="flex justify-center items-center space-x-4 mb-16">
            <span class="text-sm font-semibold" :class="billingCycle === 'monthly' ? 'text-navy' : 'text-gray-400'">Monthly Billing</span>
            <button @click="billingCycle = billingCycle === 'monthly' ? 'annual' : 'monthly'" 
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none bg-cyan">
                <span :class="billingCycle === 'annual' ? 'translate-x-5' : 'translate-x-0'"
                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
            </button>
            <span class="text-sm font-semibold flex items-center" :class="billingCycle === 'annual' ? 'text-navy' : 'text-gray-400'">
                <span>Annual Billing</span>
                <span class="ml-2 bg-orange text-white text-[10px] font-bold px-2 py-0.5 rounded-full">Save up to 50%</span>
            </span>
        </div>

        <!-- Plan Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-24">
            @foreach($subscriptions as $sub)
            <div class="{{ $sub->is_popular ? 'border-2 border-cyan relative' : 'border border-gray-200' }} rounded-lg p-6 flex flex-col justify-between hover:shadow-lg transition">
                @if($sub->is_popular)
                <span class="absolute top-0 right-1/2 transform translate-x-1/2 -translate-y-1/2 bg-cyan text-navy text-[10px] font-bold px-3 py-1 rounded-full border border-cyan uppercase tracking-wider">Most Popular</span>
                @endif
                <div>
                    <h3 class="text-lg font-bold text-navy mb-2">{{ $sub->name }}</h3>
                    <p class="text-xs text-gray-500 mb-6">{{ $sub->description }}</p>
                    <div class="mb-6">
                        <span class="text-4xl font-extrabold text-navy" x-text="billingCycle === 'monthly' ? '${{ rtrim(rtrim(number_format($sub->price_monthly, 2), '0'), '.') }}' : '${{ rtrim(rtrim(number_format($sub->price_annual, 2), '0'), '.') }}'"></span>
                        <span class="text-sm font-medium text-gray-400" x-text="billingCycle === 'monthly' ? '/mo' : '/yr'"></span>
                    </div>
                    <ul class="space-y-3 text-sm text-gray-600 mb-8">
                        @foreach($sub->features ?? [] as $feature)
                        <li class="flex items-center">✔&nbsp;{{ $feature }}</li>
                        @endforeach
                    </ul>
                </div>
                <form action="{{ url('/cart/add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_name" value="{{ $sub->name }}">
                    <input type="hidden" name="type" value="subscription">
                    <input type="hidden" name="billing" :value="billingCycle">
                    <button type="submit" class="w-full {{ $sub->is_popular ? 'bg-cyan text-navy' : 'bg-navy hover:bg-opacity-95 text-white' }} font-bold py-2.5 rounded text-center transition text-sm">
                        Select {{ $sub->name }} Plan
                    </button>
                </form>
            </div>
            @endforeach
        </div>

        <!-- Section 2: PDF Dump Bundles -->
        <div class="text-center mb-16">
            <h2 class="text-2xl font-bold text-navy mb-4">Prefer PDF Study Guide Bundles?</h2>
            <p class="text-sm text-gray-600 max-w-lg mx-auto">Get absolute lifetime access to PDF questions and answers without subscription overhead.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            @foreach($bundles as $bundle)
            <div class="{{ $bundle->is_popular ? 'bg-navy text-white relative' : 'bg-gray-50 border border-gray-250' }} rounded-lg p-6 text-center space-y-4 hover:shadow-md transition">
                @if($bundle->is_popular)
                <span class="absolute top-0 right-1/2 transform translate-x-1/2 -translate-y-1/2 bg-orange text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Save 40%</span>
                @endif
                <h4 class="font-bold {{ $bundle->is_popular ? 'text-base' : 'text-navy text-base' }}">{{ $bundle->name }}</h4>
                <div class="text-3xl font-extrabold {{ $bundle->is_popular ? 'text-cyan' : 'text-navy' }}">${{ $bundle->slug === 'single-exam-pdf' ? '15–25' : rtrim(rtrim(number_format($bundle->price_lifetime, 2), '0'), '.') }}</div>
                <p class="text-xs {{ $bundle->is_popular ? 'text-gray-300 font-medium' : 'text-gray-500' }}">{{ $bundle->description }}</p>
                <ul class="text-xs {{ $bundle->is_popular ? 'text-gray-300' : 'text-gray-600' }} text-left space-y-1 inline-block">
                    @foreach($bundle->features ?? [] as $feature)
                    <li>• {{ $feature }}</li>
                    @endforeach
                </ul>
                
                @if($bundle->slug === 'single-exam-pdf')
                <a href="{{ url('/vendors') }}" class="block w-full border border-gray-300 hover:border-cyan text-navy text-xs font-bold py-2.5 rounded transition">
                    Browse Guides
                </a>
                @else
                <form action="{{ url('/cart/add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_name" value="{{ $bundle->name }}">
                    <input type="hidden" name="type" value="{{ $bundle->slug }}">
                    <input type="hidden" name="price" value="{{ $bundle->price_lifetime }}">
                    <button type="submit" class="w-full {{ $bundle->is_popular ? 'bg-cyan text-navy shadow' : 'border border-gray-300 hover:border-cyan text-navy' }} text-xs font-bold py-2.5 rounded transition">
                        Add Bundle to Cart
                    </button>
                </form>
                @endif
            </div>
            @endforeach
        </div>

    </div>
</section>
@endsection
