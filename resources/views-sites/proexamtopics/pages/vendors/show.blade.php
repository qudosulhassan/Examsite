@extends('layouts.public')

@section('title', $vendor->name . ' Practice Exams & Certification Catalog | ProExamTopics')
@section('meta_description', 'Prepare for ' . $vendor->name . ' certification exams with verified practice questions, simulator testing, and expert study materials on ProExamTopics.')

@section('content')
<!-- Premium Vendor Spotlight Hero (Panel 3) -->
<x-site-b.hero variant="vendor-detail" glowColor="cyan" padding="compact">
    <x-slot:breadcrumbs>
        <nav class="flex items-center text-xs font-semibold text-slate-400 space-x-2">
            <a href="{{ url('/') }}" class="hover:text-white transition">Home</a>
            <span>&rsaquo;</span>
            <a href="{{ url('/vendors') }}" class="hover:text-white transition">Vendors</a>
            <span>&rsaquo;</span>
            <span class="text-cyan-300 font-bold font-mono-code">{{ $vendor->name }}</span>
        </nav>
    </x-slot:breadcrumbs>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <!-- Left: Vendor Brand Identity & Description -->
        <div class="lg:col-span-7 flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
            @php
                $vendorSvgPath = 'images/vendors/' . $vendor->slug . '.svg';
                $hasVendorSvg = file_exists(public_path($vendorSvgPath));
                $realExamTotal = (method_exists($exams, 'total') ? $exams->total() : $exams->count());
            @endphp
            <div class="w-20 h-20 rounded-2xl bg-white p-3.5 flex items-center justify-center shrink-0 shadow-2xl border border-white/20">
                @if($hasVendorSvg)
                    <img src="{{ asset($vendorSvgPath) }}" alt="{{ $vendor->name }}" class="max-w-full max-h-full object-contain">
                @elseif($vendor->logo_path)
                    <img src="{{ asset($vendor->logo_path) }}" alt="{{ $vendor->name }}" class="max-w-full max-h-full object-contain">
                @else
                    <span class="font-black text-2xl text-slate-900 font-mono-code">{{ substr($vendor->name, 0, 2) }}</span>
                @endif
            </div>

            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-md bg-blue-900/60 border border-blue-700/50 text-[11px] font-mono-code text-cyan-300 font-semibold mb-2">
                    OFFICIAL VENDOR SPOTLIGHT
                </div>
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white mb-2">{{ $vendor->name }}</h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-xl leading-relaxed">
                    {{ $vendor->description ?? $vendor->name . ' certifications validate your technical skills and help you advance your IT career with industry-proven credentials.' }}
                </p>
                <div class="flex items-center justify-center sm:justify-start gap-6 mt-4 text-xs font-semibold">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#00D4AA] animate-pulse"></span>
                        <span class="text-[#00D4AA] font-bold font-mono-code text-sm">{{ $realExamTotal }}</span> 
                        <span class="text-slate-400">Certification Exams Available</span>
                    </div>
                </div>
            </div>
        </div>

        @php
            $mainPackage = $vendorPackages->first();
            if ($mainPackage) {
                $basePrice = (float)($mainPackage->type === 'subscription' ? $mainPackage->price_monthly : $mainPackage->price_lifetime);
                if ($basePrice <= 0) $basePrice = 199.99;
                $up3 = (float)($mainPackage->update_price_3_months ?? 0);
                $up6 = (float)($mainPackage->update_price_6_months ?? 29.99);
                $up12 = (float)($mainPackage->update_price_12_months ?? 49.99);
                $licInd = (float)($mainPackage->license_price_individual ?? 0);
                $licCorp = (float)($mainPackage->license_price_corporate ?? 149.99);
                $licTrain = (float)($mainPackage->license_price_trainer ?? 299.99);
            }
        @endphp

        @if($mainPackage)
        <!-- Right: Vendor All-Access Bundle Purchase Card (Hero) -->
        <div class="lg:col-span-5 w-full mt-6 lg:mt-0"
             x-data="{
                updatePeriod: '3',
                licenseType: 'individual',
                basePrice: {{ $basePrice }},
                up3: {{ $up3 }},
                up6: {{ $up6 }},
                up12: {{ $up12 }},
                licInd: {{ $licInd }},
                licCorp: {{ $licCorp }},
                licTrain: {{ $licTrain }},
                get extraPrice() {
                    let extra = 0;
                    if (this.updatePeriod === '3') extra += this.up3;
                    else if (this.updatePeriod === '6') extra += this.up6;
                    else if (this.updatePeriod === '12') extra += this.up12;

                    if (this.licenseType === 'individual') extra += this.licInd;
                    else if (this.licenseType === 'corporate') extra += this.licCorp;
                    else if (this.licenseType === 'trainer') extra += this.licTrain;
                    
                    return extra;
                }
             }">

            <div class="relative bg-slate-900/90 border border-slate-700/80 hover:border-cyan-500/50 rounded-2xl p-4 sm:p-5 text-white shadow-2xl backdrop-blur-md space-y-3.5 transition-all">
                <!-- Top Badge Strip -->
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                    <div class="flex items-center gap-1.5">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-gradient-to-r from-cyan-500/20 to-blue-600/20 border border-cyan-400/40 text-cyan-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                            All-Access Vendor Pass
                        </span>
                        <span class="text-[9px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2 py-0.5 rounded hidden sm:inline-block">
                            Save 70%+
                        </span>
                    </div>
                    <div class="flex items-center text-amber-400 text-xs">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                        <span class="text-[10px] text-slate-400 ml-1 font-semibold">5.0</span>
                    </div>
                </div>

                <!-- Package Name & Live Price -->
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-white leading-tight">
                            {{ $mainPackage->name }}
                        </h3>
                        <p class="text-[11px] text-cyan-300 font-medium mt-0.5">
                            Full Access to All {{ $realExamTotal }} {{ $vendor->name }} Exams
                        </p>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-2xl sm:text-3xl font-black text-[#00E5FF] font-mono-code leading-none" x-text="'$' + (basePrice + extraPrice).toFixed(2)">
                            ${{ number_format($basePrice, 2) }}
                        </div>
                        <span class="text-[9px] uppercase tracking-wider font-bold text-slate-400">
                            {{ $mainPackage->type === 'subscription' ? 'Per Month' : 'Lifetime Access' }}
                        </span>
                    </div>
                </div>

                <!-- Feature Bullets (2 Columns) -->
                <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-[11px] text-slate-300 bg-slate-950/50 border border-slate-800/80 rounded-xl p-2.5">
                    <div class="flex items-center gap-1.5">
                        <span class="text-cyan-400 font-bold text-xs">✓</span>
                        <span class="truncate">All {{ $realExamTotal }} Exam PDFs</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-cyan-400 font-bold text-xs">✓</span>
                        <span class="truncate">{{ $mainPackage->includes_te ? 'Web Simulator Access' : 'Verified Study Dumps' }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-cyan-400 font-bold text-xs">✓</span>
                        <span class="truncate">Instant Downloads</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-cyan-400 font-bold text-xs">✓</span>
                        <span class="truncate">100% Pass Guarantee</span>
                    </div>
                </div>

                <!-- Options: Updates & License Form -->
                <form action="{{ url('/cart/add-package') }}" method="POST" class="space-y-3">
                    @csrf
                    <input type="hidden" name="package_id" value="{{ $mainPackage->id }}">
                    <input type="hidden" name="update_period" :value="updatePeriod">
                    <input type="hidden" name="license_type" :value="licenseType">

                    <div class="grid grid-cols-2 gap-2">
                        <!-- Update Period Dropdown -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                Updates Period
                            </label>
                            <select x-model="updatePeriod" class="w-full bg-slate-950 border border-slate-700 text-white text-[11px] font-medium rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-cyan-400 focus:outline-none cursor-pointer">
                                <option value="3">3 Mo (Included)</option>
                                <option value="6">6 Mo (+${{ number_format($up6, 2) }})</option>
                                <option value="12">12 Mo (+${{ number_format($up12, 2) }})</option>
                            </select>
                        </div>

                        <!-- License Type Dropdown -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                License Type
                            </label>
                            <select x-model="licenseType" class="w-full bg-slate-950 border border-slate-700 text-white text-[11px] font-medium rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-cyan-400 focus:outline-none cursor-pointer">
                                <option value="individual">Personal (2 PCs)</option>
                                <option value="corporate">Corporate (10 PCs +${{ number_format($licCorp, 2) }})</option>
                                <option value="trainer">Trainer (25 PCs +${{ number_format($licTrain, 2) }})</option>
                            </select>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-[#2563eb] hover:bg-blue-600 text-white font-extrabold py-2.5 px-4 rounded-xl shadow-lg transition flex items-center justify-center gap-2 group text-xs uppercase tracking-wider">
                        <span>Buy Full Vendor Bundle</span>
                        <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

                <!-- Footer Assurance -->
                <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-slate-800/80">
                    <span class="flex items-center gap-1 text-emerald-400 font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        100% Pass Guarantee
                    </span>
                    <span class="text-slate-400">Instant {{ $mainPackage->includes_te ? 'Simulator & PDF' : 'PDF' }} Access</span>
                </div>
            </div>
        </div>
        @else
        <!-- Right: Fallback Geometric Visual when no package exists -->
        <div class="lg:col-span-5 relative hidden lg:flex items-center justify-center">
            <div class="relative w-72 h-60 flex items-center justify-center">
                <!-- Glowing Aura -->
                <div class="absolute inset-0 bg-gradient-to-tr from-cyan-500/20 via-blue-600/20 to-purple-600/20 rounded-full blur-2xl"></div>

                <!-- Central Geometric Vendor Prism -->
                <div class="w-36 h-36 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 border border-cyan-500/40 shadow-2xl flex flex-col items-center justify-center text-center p-4 backdrop-blur-md z-10">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-400/30 flex items-center justify-center mb-1 text-cyan-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                    <span class="text-xs font-mono-code font-bold text-white uppercase">{{ substr($vendor->name, 0, 14) }}</span>
                    <span class="text-[10px] text-cyan-300 font-mono-code">Validated Catalog</span>
                </div>

                <!-- Floating Card 1: Top Right -->
                <div class="cpm-float absolute -top-2 -right-4 bg-slate-900/90 border border-slate-700/80 rounded-xl px-3 py-2 shadow-xl backdrop-blur-md z-20 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <div>
                        <div class="text-[11px] font-bold text-white font-mono-code">{{ $realExamTotal }} Active Exams</div>
                        <div class="text-[9px] text-slate-400">Updated for 2026</div>
                    </div>
                </div>

                <!-- Floating Card 2: Bottom Left -->
                <div class="cpm-float-reverse absolute -bottom-3 -left-4 bg-slate-900/90 border border-slate-700/80 rounded-xl px-3 py-2 shadow-xl backdrop-blur-md z-20 flex items-center gap-2">
                    <div class="w-5 h-5 rounded-md bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-[10px]">
                        ✓
                    </div>
                    <div>
                        <div class="text-[11px] font-bold text-white">Full Simulator</div>
                        <div class="text-[9px] text-cyan-300">Instant Practice</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-site-b.hero>

<!-- Real Vendor Statistics Bar Below Hero -->
<section class="bg-white border-b border-gray-150 py-4 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap items-center justify-between gap-4 text-xs font-semibold text-slate-600">
        <div class="flex items-center gap-2">
            <span class="text-slate-400 uppercase tracking-wider text-[11px]">Provider:</span>
            <span class="text-slate-900 font-bold">{{ $vendor->name }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-slate-400 uppercase tracking-wider text-[11px]">Exams in Catalog:</span>
            <span class="font-mono-code text-[#2563eb] font-black text-sm">{{ $realExamTotal }}</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-slate-400 uppercase tracking-wider text-[11px]">Study Formats:</span>
            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-mono-code text-[11px]">Web Simulator + PDF</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-slate-400 uppercase tracking-wider text-[11px]">Guarantee:</span>
            <span class="text-emerald-600 font-bold">100% Pass Guarantee</span>
        </div>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ search: '', activeTab: 'all' }">

    <!-- Category Tabs & Search Bar (Panel 3) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <!-- Filter Tabs -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-1 text-xs font-semibold">
            <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-[#2563eb] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-gray-200'" class="px-4 py-2 rounded-lg transition whitespace-nowrap">
                All Exams
            </button>
            <button @click="activeTab = 'cloud'" :class="activeTab === 'cloud' ? 'bg-[#2563eb] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-gray-200'" class="px-4 py-2 rounded-lg transition whitespace-nowrap">
                Cloud
            </button>
            <button @click="activeTab = 'security'" :class="activeTab === 'security' ? 'bg-[#2563eb] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-gray-200'" class="px-4 py-2 rounded-lg transition whitespace-nowrap">
                Security
            </button>
            <button @click="activeTab = 'developer'" :class="activeTab === 'developer' ? 'bg-[#2563eb] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-gray-200'" class="px-4 py-2 rounded-lg transition whitespace-nowrap">
                Developer
            </button>
        </div>

        <!-- Live Search Field -->
        <div class="relative w-full sm:w-72">
            <input 
                type="text" 
                x-model="search" 
                placeholder="Search {{ $vendor->name }} exams..." 
                class="w-full bg-white border border-gray-200 rounded-lg pl-9 pr-4 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#2563eb]"
            >
            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </div>

    <!-- Exam Catalog Table (Panel 3) -->
    <div class="bg-white border border-gray-200/90 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Exam Code</th>
                        <th class="py-3.5 px-6">Exam Name</th>
                        <th class="py-3.5 px-6 text-center">Questions</th>
                        <th class="py-3.5 px-6 text-right">Price</th>
                        <th class="py-3.5 px-6 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($exams as $exam)
                        @php
                            $examUrl = $exam->url;
                            $price = $exam->resolved_price_pdf ?? 49.99;
                            $examCode = $exam->exam_code ?? $exam->code;
                            $examName = $exam->exam_name ?? $exam->name;
                            $qCount = $exam->calculated_question_count > 0 ? $exam->calculated_question_count : ($exam->question_count > 0 ? $exam->question_count : '400+');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors" x-show="search === '' || '{{ strtolower($examCode . ' ' . $examName) }}'.includes(search.toLowerCase())">
                            <td class="py-4 px-6">
                                <a href="{{ $examUrl }}" class="font-mono-code font-bold text-[#2563eb] hover:underline">
                                    {{ $examCode }}
                                </a>
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-800">
                                <a href="{{ $examUrl }}" class="hover:text-[#2563eb] transition">
                                    {{ $examName }}
                                </a>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-xs font-semibold font-mono-code">
                                    {{ $qCount }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right font-black text-slate-900 font-mono-code">
                                ${{ number_format($price, 2) }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                <a href="{{ $examUrl }}" class="inline-block bg-[#2563eb] hover:bg-blue-700 text-white text-xs font-bold px-4 py-1.5 rounded-lg shadow-sm transition">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-500 text-xs">
                                No active certification exams found for this vendor.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($exams, 'hasPages') && $exams->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-slate-50">
                {{ $exams->links() }}
            </div>
        @endif
    </div>
</div>
@endsection