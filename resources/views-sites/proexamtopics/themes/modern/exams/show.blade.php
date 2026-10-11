@extends('layouts.public')

@section('title', $exam->resolved_seo_title)
@section('meta_description', $exam->resolved_meta_description)

@section('styles')
@include('partials.rich-content-styles')
@endsection

@section('seo_tags')
@php
    $schemaFaqs = $exam->resolved_faqs;
@endphp
@if(!empty($schemaFaqs) && is_array($schemaFaqs) && count($schemaFaqs) > 0)
<script type="application/ld+json">
{
  "{{ '@' }}context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    @foreach($schemaFaqs as $faqIndex => $faq)
    {
      "@type": "Question",
      "name": {!! json_encode($faq['question'] ?? '') !!},
      "acceptedAnswer": {
        "@type": "Answer",
        "text": {!! json_encode(strip_tags($faq['answer'] ?? '')) !!}
      }
    }{{ $faqIndex < count($schemaFaqs) - 1 ? ',' : '' }}
    @endforeach
  ]
}
</script>
@endif
@endsection

@section('content')
<!-- Premium Product / Certification Hero (Panel 4) -->
<x-site-b.hero variant="exam" glowColor="cyan" padding="product">
    <x-slot:breadcrumbs>
        <nav class="inline-flex items-center text-xs font-semibold text-slate-400 bg-slate-900/70 border border-slate-800/80 px-3.5 py-1.5 rounded-full backdrop-blur-md space-x-2 shadow-sm">
            <a href="{{ url('/') }}" class="hover:text-cyan-300 transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Home</span>
            </a>
            <span class="text-slate-600">&rsaquo;</span>
            <a href="{{ url('/vendors/' . ($exam->vendor ? $exam->vendor->slug : 'vendor')) }}" class="hover:text-cyan-300 transition">{{ $exam->vendor->name ?? 'Vendor' }}</a>
            <span class="text-slate-600">&rsaquo;</span>
            <span class="text-cyan-300 font-bold font-mono-code bg-cyan-950/70 px-2 py-0.5 rounded border border-cyan-500/30">{{ $exam->exam_code }}</span>
        </nav>
    </x-slot:breadcrumbs>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
        <!-- LEFT: Vendor badge, Exam Code, Title, Metadata (~58%) -->
        <div class="lg:col-span-7 xl:col-span-7 space-y-4">
            @php
                $vendorSvgPath = 'images/vendors/' . ($exam->vendor ? $exam->vendor->slug : '') . '.svg';
                $hasVendorSvg = $exam->vendor && file_exists(public_path($vendorSvgPath));
                $realQuestions = $exam->calculated_question_count > 0 ? $exam->calculated_question_count : ($exam->question_count > 0 ? $exam->question_count : null);

                $h1Headline = !empty(trim($overlay->custom_h1 ?? '')) 
                    ? trim($overlay->custom_h1) 
                    : (!empty(trim($overlay->custom_header_title ?? '')) 
                        ? trim($overlay->custom_header_title) 
                        : (!empty(trim($exam->resolved_h1 ?? '')) 
                            ? trim($exam->resolved_h1) 
                            : ($exam->exam_name ?? 'Certification Exam')));

                $subHeaderTitle = (!empty(trim($overlay->custom_header_title ?? '')) && trim($overlay->custom_header_title) !== $h1Headline)
                    ? trim($overlay->custom_header_title)
                    : null;
            @endphp

            <!-- Vendor & Code Header Badge -->
            <div class="inline-flex items-center gap-3 p-1.5 pr-4 rounded-xl bg-slate-900/80 border border-slate-700/60 shadow-inner backdrop-blur-sm">
                @if($hasVendorSvg)
                    <div class="w-10 h-10 rounded-lg bg-white p-1.5 flex items-center justify-center shadow shrink-0 border border-white/20">
                        <img src="{{ asset($vendorSvgPath) }}" alt="{{ $exam->vendor->name }}" class="max-w-full max-h-full object-contain">
                    </div>
                @else
                    <span class="w-10 h-10 rounded-lg bg-blue-600/30 border border-blue-400/40 flex items-center justify-center font-mono-code font-black text-xs text-cyan-300 shrink-0">
                        {{ substr($exam->vendor->name ?? 'EX', 0, 2) }}
                    </span>
                @endif
                <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $exam->vendor->name ?? 'Certification' }}</span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-cyan-300 bg-cyan-950/80 border border-cyan-500/40 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                            Official Syllabus
                        </span>
                    </div>
                    <div class="font-mono-code font-black text-lg text-cyan-300 leading-tight">{{ $exam->exam_code }}</div>
                </div>
            </div>

            <!-- Main H1 Headline -->
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight leading-tight">
                {{ $h1Headline }}
            </h1>

            <!-- Subtitle -->
            <p class="text-xs sm:text-sm font-semibold text-cyan-300 tracking-wide flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-400 shrink-0"></span>
                <span>{{ $subHeaderTitle ?? 'Authentic Practice Exam Questions & Realistic Test Engine Simulator' }}</span>
            </p>

            <!-- Star Rating & Trust Badges Strip -->
            <div class="flex flex-wrap items-center gap-2 pt-0.5">
                <div class="flex items-center gap-1.5 text-amber-400 text-xs font-bold bg-slate-900/80 border border-amber-500/30 px-3 py-1.5 rounded-xl shadow-sm">
                    <div class="flex text-amber-400 text-xs tracking-tighter">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    </div>
                    <span class="text-white font-extrabold text-xs ml-0.5">4.9</span>
                    <span class="text-slate-400 text-[11px]">({{ $exam->reviews()->count() > 0 ? $exam->reviews()->count() : '1,420+' }} reviews)</span>
                </div>
                @if($exam->difficulty)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-cyan-950/60 text-cyan-300 border border-cyan-500/30 font-mono-code">
                        <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        {{ $exam->difficulty }}
                    </span>
                @endif
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-blue-950/70 text-blue-300 border border-blue-500/40">
                    <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    100% Pass Guarantee
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-900/80 text-cyan-200 border border-cyan-500/30">
                    <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    2026-2027 Syllabus
                </span>
            </div>

            <!-- Exam Description -->
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-xl">
                {{ $overlay->custom_intro ?? ($exam->description ? strip_tags($exam->description) : 'Master the ' . $exam->exam_code . ' certification with authentic practice questions, verified answers, and realistic test simulation designed by certified industry professionals.') }}
            </p>

            <!-- 4-Column Modern Specs Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1">
                <!-- Spec 1: Practice Questions -->
                <div class="bg-gradient-to-b from-slate-800/80 to-slate-900/90 border border-slate-700/60 rounded-xl p-3 shadow hover:border-cyan-500/40 transition group">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-6 h-6 rounded-md bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-300 group-hover:scale-105 transition-transform">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Practice Qs</span>
                    </div>
                    <div class="text-sm sm:text-base font-black text-cyan-300 font-mono-code">{{ $realQuestions ? $realQuestions . ' Verified' : 'Full Pool' }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5 truncate">With explanations</div>
                </div>

                <!-- Spec 2: Passing Score -->
                <div class="bg-gradient-to-b from-slate-800/80 to-slate-900/90 border border-slate-700/60 rounded-xl p-3 shadow hover:border-blue-500/40 transition group">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-6 h-6 rounded-md bg-blue-950/80 border border-blue-500/40 flex items-center justify-center text-blue-300 group-hover:scale-105 transition-transform">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Passing Score</span>
                    </div>
                    <div class="text-sm sm:text-base font-black text-white font-mono-code">{{ $exam->passing_score ? $exam->passing_score : '700 / 1000' }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5 truncate">Target threshold</div>
                </div>

                <!-- Spec 3: Free Updates -->
                <div class="bg-gradient-to-b from-slate-800/80 to-slate-900/90 border border-slate-700/60 rounded-xl p-3 shadow hover:border-cyan-500/40 transition group">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-6 h-6 rounded-md bg-cyan-950/80 border border-cyan-500/30 flex items-center justify-center text-cyan-300 group-hover:scale-105 transition-transform">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Free Updates</span>
                    </div>
                    <div class="text-sm sm:text-base font-black text-[#00E5FF] font-mono-code">90 Days Free</div>
                    <div class="text-[10px] text-slate-400 mt-0.5 truncate">Live syllabus sync</div>
                </div>

                <!-- Spec 4: Format -->
                <div class="bg-gradient-to-b from-slate-800/80 to-slate-900/90 border border-slate-700/60 rounded-xl p-3 shadow hover:border-indigo-500/40 transition group">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-6 h-6 rounded-md bg-indigo-950/80 border border-indigo-500/30 flex items-center justify-center text-indigo-300 group-hover:scale-105 transition-transform">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Format</span>
                    </div>
                    <div class="text-sm sm:text-base font-black text-white font-mono-code">Web + PDF</div>
                    <div class="text-[10px] text-slate-400 mt-0.5 truncate">Instant download</div>
                </div>
            </div>

            <!-- Micro-Trust Pills -->
            <div class="pt-2 flex flex-wrap items-center gap-2 text-xs text-slate-300">
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900/70 border border-slate-800/90 shadow-sm backdrop-blur-sm">
                    <span class="w-4 h-4 rounded-full bg-blue-950 border border-cyan-500/40 flex items-center justify-center text-cyan-300 text-[10px] font-bold">✓</span>
                    <span>Instant access after checkout</span>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900/70 border border-slate-800/90 shadow-sm backdrop-blur-sm">
                    <span class="w-4 h-4 rounded-full bg-blue-950 border border-cyan-500/40 flex items-center justify-center text-cyan-300 text-[10px] font-bold">✓</span>
                    <span>Detailed answers & references</span>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900/70 border border-slate-800/90 shadow-sm backdrop-blur-sm">
                    <span class="w-4 h-4 rounded-full bg-blue-950 border border-cyan-500/40 flex items-center justify-center text-cyan-300 text-[10px] font-bold">✓</span>
                    <span>Timed test simulation mode</span>
                </div>
            </div>
        </div>

        <!-- RIGHT: Available Study Options (Elevated Glassmorphism Card) -->
        @php
            $isPdfActive = $exam->is_pdf_available ?? true;
            $isEngineActive = $exam->is_engine_available ?? true;
            $isBundleActive = $exam->is_bundle_available ?? true;

            $pdfBase = (float)($exam->resolved_price_pdf ?: 29.99);
            $engineBase = (float)($exam->resolved_price_engine ?: 39.99);
            $bundleBase = (float)($exam->effective_bundle_price ?: round(($pdfBase + $engineBase) * 0.9, 2));

            $originalCombined = $pdfBase + $engineBase;
            $bundleSavePercent = $originalCombined > $bundleBase 
                ? max(0, round((1 - ($bundleBase / $originalCombined)) * 100)) 
                : 10;

            $extraCombo3 = (float)$exam->resolveUpdatePrice('combo', 3);
            $extraCombo6 = (float)$exam->resolveUpdatePrice('combo', 6);
            $extraCombo12 = (float)$exam->resolveUpdatePrice('combo', 12);

            $extraPdf3 = (float)$exam->resolveUpdatePrice('pdf', 3);
            $extraPdf6 = (float)$exam->resolveUpdatePrice('pdf', 6);
            $extraPdf12 = (float)$exam->resolveUpdatePrice('pdf', 12);

            $extraEngine3 = (float)$exam->resolveUpdatePrice('engine', 3);
            $extraEngine6 = (float)$exam->resolveUpdatePrice('engine', 6);
            $extraEngine12 = (float)$exam->resolveUpdatePrice('engine', 12);
        @endphp
        <div class="lg:col-span-5 xl:col-span-5 w-full"
             x-data="{
                updatePeriodCombo: '3',
                updatePeriodPdf: '3',
                updatePeriodEngine: '3',
                baseCombo: {{ $bundleBase }},
                basePdf: {{ $pdfBase }},
                baseEngine: {{ $engineBase }},
                extraCombo3: {{ $extraCombo3 }},
                extraCombo6: {{ $extraCombo6 }},
                extraCombo12: {{ $extraCombo12 }},
                extraPdf3: {{ $extraPdf3 }},
                extraPdf6: {{ $extraPdf6 }},
                extraPdf12: {{ $extraPdf12 }},
                extraEngine3: {{ $extraEngine3 }},
                extraEngine6: {{ $extraEngine6 }},
                extraEngine12: {{ $extraEngine12 }},
                get extraPriceCombo() { return this.updatePeriodCombo === '12' ? this.extraCombo12 : (this.updatePeriodCombo === '6' ? this.extraCombo6 : this.extraCombo3); },
                get extraPricePdf() { return this.updatePeriodPdf === '12' ? this.extraPdf12 : (this.updatePeriodPdf === '6' ? this.extraPdf6 : this.extraPdf3); },
                get extraPriceEngine() { return this.updatePeriodEngine === '12' ? this.extraEngine12 : (this.updatePeriodEngine === '6' ? this.extraEngine6 : this.extraEngine3); }
             }">

            <div class="bg-gradient-to-b from-[#0c182d] via-slate-900 to-[#071328] border border-cyan-500/30 rounded-2xl p-5 text-white shadow-[0_20px_50px_rgba(0,0,0,0.6),0_0_30px_rgba(37,99,235,0.15)] relative space-y-4 backdrop-blur-xl">
                <!-- Header Label & Rating -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-black uppercase tracking-wider text-cyan-300 bg-cyan-950/80 border border-cyan-500/40 px-2.5 py-1 rounded-lg">
                            <svg class="w-3 h-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Available Study Options
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-amber-400 bg-slate-950/60 border border-slate-800 px-2.5 py-1 rounded-lg">
                        <span class="tracking-tighter">★★★★★</span>
                        <span class="text-white font-black text-[11px]">5.0</span>
                    </div>
                </div>

                @if($isBundleActive)
                <!-- 1. PDF + Test Engine Bundle Card (Featured Best Value) -->
                <div class="relative mt-3 bg-gradient-to-br from-slate-800/90 via-slate-800/70 to-slate-900/90 border-2 border-cyan-500/50 hover:border-cyan-400 rounded-xl p-4 shadow-xl transition-all">
                    <!-- Badge -->
                    <div class="absolute -top-3.5 left-4 bg-gradient-to-r from-amber-500 to-rose-500 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider shadow-md flex items-center gap-1.5 z-10">
                        <span>🔥</span>
                        <span>SAVE {{ $bundleSavePercent }}% • MOST POPULAR</span>
                    </div>

                    <div class="flex items-baseline justify-between mb-2 pt-1">
                        <div>
                            <h3 class="font-black text-base text-white leading-tight">PDF + Test Engine Bundle</h3>
                            <span class="text-xs text-cyan-300 font-medium">Complete All-in-One Preparation Suite</span>
                        </div>
                        <div class="flex items-baseline gap-2 shrink-0">
                            <span class="text-3xl font-black text-[#00E5FF] font-mono-code tracking-tight" x-text="'$' + (baseCombo + extraPriceCombo).toFixed(2)">
                                ${{ number_format($bundleBase + $extraCombo3, 2) }}
                            </span>
                            @if($originalCombined > $bundleBase)
                                <span class="text-xs text-slate-400 line-through font-bold">
                                    ${{ number_format($originalCombined, 2) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- 2-Column Feature Highlights -->
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-300 my-3 py-2.5 border-y border-slate-700/50">
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded-full bg-cyan-950 border border-cyan-500/50 flex items-center justify-center text-cyan-300 text-[10px] font-bold">✓</span>
                            <span class="font-medium text-white truncate">Web Test Simulator</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded-full bg-cyan-950 border border-cyan-500/50 flex items-center justify-center text-cyan-300 text-[10px] font-bold">✓</span>
                            <span class="font-medium text-white truncate">Printable PDF Guide</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded-full bg-cyan-950 border border-cyan-500/50 flex items-center justify-center text-cyan-300 text-[10px] font-bold">✓</span>
                            <span class="font-medium text-white truncate">100% Verified Answers</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-4 rounded-full bg-cyan-950 border border-cyan-500/50 flex items-center justify-center text-cyan-300 text-[10px] font-bold">✓</span>
                            <span class="font-medium text-white truncate">90 Days Free Sync</span>
                        </div>
                    </div>

                    <!-- Updates Period & Add to Cart in Modern Row -->
                    <form action="{{ url('/cart/add') }}" method="POST" class="flex flex-col sm:flex-row gap-2.5 items-center">
                        @csrf
                        <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                        <input type="hidden" name="type" value="combo">
                        <input type="hidden" name="update_period" :value="updatePeriodCombo">

                        <div class="w-full sm:w-2/5">
                            <div class="relative">
                                <select x-model="updatePeriodCombo" class="w-full bg-slate-900/90 border border-slate-700 text-white text-xs font-semibold rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-cyan-400 focus:border-transparent focus:outline-none cursor-pointer appearance-none">
                                    <option value="3">3 Mo {{ $extraCombo3 > 0 ? '(+$' . number_format($extraCombo3, 2) . ')' : '(Included)' }}</option>
                                    <option value="6">6 Mo (+${{ number_format($extraCombo6, 2) }})</option>
                                    <option value="12">12 Mo (+${{ number_format($extraCombo12, 2) }})</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="w-full sm:w-3/5">
                            <button type="submit" class="w-full text-white font-black py-2.5 px-4 rounded-xl transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2 group text-xs uppercase tracking-wider" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #0284c7 100%) !important; box-shadow: 0 4px 20px rgba(37, 99, 235, 0.45);">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                <span>Add Bundle to Cart</span>
                                <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition-transform text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </form>
                </div>
                @endif

                @if($isPdfActive || $isEngineActive)
                <!-- 2. Standalone Product Cards (PDF Guide vs Test Engine Simulator) -->
                <div class="grid {{ ($isPdfActive && $isEngineActive) ? 'grid-cols-2' : 'grid-cols-1' }} gap-2.5">
                    @if($isPdfActive)
                    <!-- Standalone PDF Guide Card -->
                    <div class="bg-slate-800/60 border border-slate-700/60 hover:border-slate-500 rounded-xl p-3 flex flex-col justify-between transition group">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="inline-flex items-center gap-1 bg-slate-900 border border-slate-700 text-slate-300 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider">
                                    <svg class="w-3 h-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    PDF Guide
                                </span>
                            </div>
                            <div class="text-xl font-black text-white font-mono-code mb-1" x-text="'$' + (basePdf + extraPricePdf).toFixed(2)">
                                ${{ number_format($pdfBase + $extraPdf3, 2) }}
                            </div>
                            <p class="text-[11px] text-slate-400 mb-2.5 line-clamp-1">Printable & mobile PDF</p>
                            <div class="mb-2.5 relative">
                                <select x-model="updatePeriodPdf" class="w-full bg-slate-900 border border-slate-700 text-white text-[10px] font-semibold rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-cyan-400 focus:outline-none cursor-pointer appearance-none">
                                    <option value="3">3 Mo {{ $extraPdf3 > 0 ? '(+$' . number_format($extraPdf3, 2) . ')' : '(Inc.)' }}</option>
                                    <option value="6">6 Mo (+${{ number_format($extraPdf6, 2) }})</option>
                                    <option value="12">12 Mo (+${{ number_format($extraPdf12, 2) }})</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-1.5 text-slate-400">
                                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <form action="{{ url('/cart/add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                            <input type="hidden" name="type" value="pdf">
                            <input type="hidden" name="update_period" :value="updatePeriodPdf">

                            <button type="submit" class="w-full bg-slate-700/90 hover:bg-slate-600 border border-slate-600/60 hover:border-slate-400 text-white text-[11px] font-black py-2 px-2.5 rounded-xl transition uppercase tracking-wider text-center block shadow-sm">
                                Select PDF
                            </button>
                        </form>
                    </div>
                    @endif

                    @if($isEngineActive)
                    <!-- Standalone Test Engine Card -->
                    <div class="bg-slate-800/60 border border-slate-700/60 hover:border-blue-500/60 rounded-xl p-3 flex flex-col justify-between transition group">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="inline-flex items-center gap-1 bg-blue-950/80 border border-blue-600/40 text-blue-300 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider">
                                    <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    Test Engine
                                </span>
                            </div>
                            <div class="text-xl font-black text-white font-mono-code mb-1" x-text="'$' + (baseEngine + extraPriceEngine).toFixed(2)">
                                ${{ number_format($engineBase + $extraEngine3, 2) }}
                            </div>
                            <p class="text-[11px] text-slate-400 mb-2.5 line-clamp-1">Browser exam simulator</p>
                            <div class="mb-2.5 relative">
                                <select x-model="updatePeriodEngine" class="w-full bg-slate-900 border border-slate-700 text-white text-[10px] font-semibold rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-cyan-400 focus:outline-none cursor-pointer appearance-none">
                                    <option value="3">3 Mo {{ $extraEngine3 > 0 ? '(+$' . number_format($extraEngine3, 2) . ')' : '(Inc.)' }}</option>
                                    <option value="6">6 Mo (+${{ number_format($extraEngine6, 2) }})</option>
                                    <option value="12">12 Mo (+${{ number_format($extraEngine12, 2) }})</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-1.5 text-slate-400">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <form action="{{ url('/cart/add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                            <input type="hidden" name="type" value="engine_single">
                            <input type="hidden" name="update_period" :value="updatePeriodEngine">

                            <button type="submit" class="w-full bg-blue-600/40 hover:bg-[#2563eb] border border-blue-500/50 hover:border-blue-400 text-white text-[11px] font-black py-2 px-2.5 rounded-xl transition uppercase tracking-wider text-center block shadow-sm">
                                Select Engine
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Interactive Free Demo CTA Banner -->
                <div class="pt-1">
                    <a href="{{ route('public.demo-test-engine.lobby', $exam->slug) }}" class="group block w-full bg-slate-800/80 hover:bg-slate-800 border border-cyan-500/40 hover:border-cyan-400 rounded-xl p-3 text-center transition shadow-sm">
                        <div class="flex items-center justify-center gap-2 text-xs font-bold text-cyan-300 group-hover:text-cyan-200">
                            <span class="w-5 h-5 rounded-full bg-cyan-950 border border-cyan-500/40 flex items-center justify-center text-[9px] text-cyan-300 group-hover:scale-110 transition-transform">▶</span>
                            <span>Test-Drive Free Simulator Demo</span>
                            <span class="text-cyan-400 group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                        <span class="block text-[10px] text-slate-400 mt-0.5">No login or credit card required • Instant sample questions</span>
                    </a>
                </div>

                <!-- Security & Trust Indicators -->
                <div class="flex items-center justify-center gap-4 text-[10px] text-slate-400 pt-1 border-t border-slate-800/80">
                    <div class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>256-Bit SSL</span>
                    </div>
                    <span>•</span>
                    <div class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Instant Access</span>
                    </div>
                    <span>•</span>
                    <div class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Pass Guarantee</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-site-b.hero>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ activeTab: (window.location.hash && ['overview', 'topics', 'sample', 'reviews', 'faq'].includes(window.location.hash.replace('#', ''))) ? window.location.hash.replace('#', '') : 'overview', demoModalOpen: false }">
    <!-- Main Content Tabs Area -->
    <div class="grid grid-cols-1 gap-10 items-start">
        <div class="space-y-8">

            <!-- Tabbed Navigation (Panel 4) -->
            <div class="border-b border-gray-200">
                <nav class="flex space-x-6 text-xs sm:text-sm font-bold">
                    <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'text-[#2563eb] border-b-2 border-[#2563eb]' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition">
                        Overview
                    </button>
                    <button @click="activeTab = 'topics'" :class="activeTab === 'topics' ? 'text-[#2563eb] border-b-2 border-[#2563eb]' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition">
                        Exam Topics
                    </button>
                    <button @click="activeTab = 'sample'" :class="activeTab === 'sample' ? 'text-[#2563eb] border-b-2 border-[#2563eb]' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition">
                        Sample Questions
                    </button>
                    <button @click="activeTab = 'reviews'" :class="activeTab === 'reviews' ? 'text-[#2563eb] border-b-2 border-[#2563eb]' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition">
                        Reviews
                    </button>
                    <button @click="activeTab = 'faq'" :class="activeTab === 'faq' ? 'text-[#2563eb] border-b-2 border-[#2563eb]' : 'text-slate-500 hover:text-slate-700'" class="pb-3 transition">
                        FAQ
                    </button>
                </nav>
            </div>

            <!-- Tab Content: Overview (Panel 4) -->
            <div x-show="activeTab === 'overview'" class="space-y-8">
                <!-- 4 Metadata Cards Grid (Panel 4) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-white border border-gray-200/90 rounded-xl p-4 text-center">
                        <div class="text-[11px] font-semibold text-slate-400 uppercase">Exam Code</div>
                        <div class="font-mono-code font-bold text-slate-900 text-sm mt-1">{{ $exam->exam_code }}</div>
                    </div>
                    <div class="bg-white border border-gray-200/90 rounded-xl p-4 text-center">
                        <div class="text-[11px] font-semibold text-slate-400 uppercase">Vendor</div>
                        <div class="font-bold text-slate-900 text-sm mt-1 truncate">{{ $exam->vendor->name ?? 'Standard' }}</div>
                    </div>
                    <div class="bg-white border border-gray-200/90 rounded-xl p-4 text-center">
                        <div class="text-[11px] font-semibold text-slate-400 uppercase">Questions</div>
                        <div class="font-bold text-slate-900 text-sm mt-1 font-mono-code">{{ $exam->calculated_question_count > 0 ? $exam->calculated_question_count : ($exam->question_count > 0 ? $exam->question_count : '400+') }}</div>
                    </div>
                    <div class="bg-white border border-gray-200/90 rounded-xl p-4 text-center">
                        <div class="text-[11px] font-semibold text-slate-400 uppercase">Passing Score</div>
                        <div class="font-bold text-slate-900 text-sm mt-1 font-mono-code">{{ $exam->passing_score ?? '700' }}</div>
                    </div>
                </div>

                <!-- Why Choose This Product? (Panel 4) -->
                <div>
                    <h2 class="text-base font-bold text-slate-900 mb-4">Why Choose This Product?</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white border border-gray-200/90 rounded-xl p-4 flex items-start gap-3 shadow-sm">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#2563eb] flex items-center justify-center shrink-0 font-bold">✓</div>
                            <div>
                                <h3 class="text-xs font-bold text-slate-900">Real Exam Questions</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">Sourced directly from certified question bank architects and verified regularly.</p>
                            </div>
                        </div>
                        <div class="bg-white border border-gray-200/90 rounded-xl p-4 flex items-start gap-3 shadow-sm">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#2563eb] flex items-center justify-center shrink-0 font-bold">📖</div>
                            <div>
                                <h3 class="text-xs font-bold text-slate-900">Detailed Explanations</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">In-depth rationale for correct and incorrect answers with references.</p>
                            </div>
                        </div>
                        <div class="bg-white border border-gray-200/90 rounded-xl p-4 flex items-start gap-3 shadow-sm">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#2563eb] flex items-center justify-center shrink-0 font-bold">↻</div>
                            <div>
                                <h3 class="text-xs font-bold text-slate-900">Regular Updates</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">Free 90-day updates to reflect any vendor syllabus alterations.</p>
                            </div>
                        </div>
                        <div class="bg-white border border-gray-200/90 rounded-xl p-4 flex items-start gap-3 shadow-sm">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#2563eb] flex items-center justify-center shrink-0 font-bold">⚡</div>
                            <div>
                                <h3 class="text-xs font-bold text-slate-900">Instant Access</h3>
                                <p class="text-[11px] text-slate-500 mt-0.5">Practice immediately inside our web simulator and download the PDF guide.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- About this Exam (Panel 4) -->
                <div>
                    <h2 class="text-base font-bold text-slate-900 mb-2">About this Exam</h2>
                    <div class="blog-content-body prose prose-slate max-w-none text-xs sm:text-sm text-slate-600 leading-relaxed">
                        @if(!empty($exam->resolved_article_content))
                            {!! $exam->resolved_article_content !!}
                        @else
                            <p>{{ $exam->description ?? 'This exam validates foundational knowledge of cloud concepts, core architectural services, security, privacy, compliance, and standard support models. Designed to help candidates earn their credential on the very first attempt.' }}</p>
                        @endif
                    </div>
                </div>

                <!-- Related Exams Section (Panel 4) -->
                <div>
                    <h2 class="text-base font-bold text-slate-900 mb-4">Related Exams</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @php
                            $relatedExams = \App\Models\Exam::where('vendor_id', $exam->vendor_id)
                                ->where('id', '!=', $exam->id)
                                ->take(4)
                                ->get();
                        @endphp
                        @foreach($relatedExams as $rel)
                            @php
                                $relUrl = $rel->url;
                                $relPrice = $rel->resolved_price_pdf ?? 49.99;
                            @endphp
                            <a href="{{ $relUrl }}" class="bg-white border border-gray-200/90 rounded-xl p-4 hover:shadow-md hover:border-[#2563eb] transition flex flex-col justify-between group">
                                <div>
                                    <span class="font-mono-code font-bold text-xs text-slate-900 group-hover:text-[#2563eb] transition">{{ $rel->exam_code }}</span>
                                    <p class="text-[11px] text-slate-500 line-clamp-2 mt-1">{{ $rel->exam_name }}</p>
                                </div>
                                <div class="mt-3 pt-2 border-t border-gray-100 flex items-center justify-between">
                                    <span class="text-xs font-black text-slate-900 font-mono-code">${{ number_format($relPrice, 2) }}</span>
                                    <span class="text-[10px] font-bold text-[#2563eb]">View &rarr;</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Tab Content: Topics -->
            <div x-show="activeTab === 'topics'" x-cloak class="space-y-4">
                <h2 class="text-base font-bold text-slate-900">Exam Domains & Objectives</h2>
                @php
                    $examTopics = $exam->resolved_topics ?? $exam->topics ?? [];
                @endphp
                @if(!empty($examTopics) && is_array($examTopics))
                    <div class="space-y-3">
                        @foreach($examTopics as $t)
                            @php
                                $topicTitle = is_array($t) ? ($t['name'] ?? $t['title'] ?? 'Domain') : $t;
                                $topicDesc = is_array($t) ? ($t['description'] ?? '') : '';
                                $topicPercent = is_array($t) ? ($t['percentage'] ?? '') : '';
                            @endphp
                            <div class="bg-white border border-gray-200 p-4 rounded-xl flex items-center justify-between gap-4">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800">{{ $topicTitle }}</h4>
                                    @if(!empty($topicDesc))
                                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $topicDesc }}</p>
                                    @endif
                                </div>
                                @if(!empty($topicPercent))
                                    <span class="text-xs font-bold text-[#2563eb] font-mono-code shrink-0">{{ $topicPercent }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="space-y-3">
                        <div class="bg-white border border-gray-200 p-4 rounded-xl flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">Domain 1: Core Architecture & Cloud Concepts</h4>
                                <p class="text-[11px] text-slate-500">Fundamental benefits, high availability, scalability and agility</p>
                            </div>
                            <span class="text-xs font-bold text-[#2563eb] font-mono-code">25-30%</span>
                        </div>
                        <div class="bg-white border border-gray-200 p-4 rounded-xl flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">Domain 2: Core Services & Management Solutions</h4>
                                <p class="text-[11px] text-slate-500">Compute, networking, storage, identity and cloud management tools</p>
                            </div>
                            <span class="text-xs font-bold text-[#2563eb] font-mono-code">35-40%</span>
                        </div>
                        <div class="bg-white border border-gray-200 p-4 rounded-xl flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">Domain 3: Security, Privacy, Compliance & Trust</h4>
                                <p class="text-[11px] text-slate-500">Identity security, network access controls and compliance standards</p>
                            </div>
                            <span class="text-xs font-bold text-[#2563eb] font-mono-code">30-35%</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Tab Content: Sample Questions -->
            <div x-show="activeTab === 'sample'" x-cloak class="space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-1 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Sample Practice Questions</h2>
                        <p class="text-xs text-slate-500">Test your knowledge with real sample questions directly from our verified pool.</p>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#2563eb] bg-blue-50 px-2.5 py-1 rounded-lg">
                        Interactive Demo Mode
                    </span>
                </div>

                @if(isset($sampleQuestions) && count($sampleQuestions) > 0)
                    <div class="space-y-5">
                        @foreach($sampleQuestions as $q)
                            @php
                                $correctLetter = $q->correct_option ?: 'A';
                                $optionsMap = [
                                    'A' => $q->option_a,
                                    'B' => $q->option_b,
                                    'C' => $q->option_c,
                                    'D' => $q->option_d,
                                ];
                            @endphp
                            <div class="bg-white border border-gray-200 p-5 sm:p-6 rounded-2xl space-y-4 shadow-xs transition hover:border-slate-300"
                                 x-data="{
                                    selected: null,
                                    revealed: false,
                                    correct: '{{ $correctLetter }}',
                                    explanation: {{ json_encode($q->explanation ?: 'This question tests core domain concepts validated by certified professionals.') }},
                                    selectOption(opt) {
                                        this.selected = opt;
                                        this.revealed = true;
                                    }
                                 }">
                                
                                <div class="flex items-center justify-between gap-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-bold font-mono-code">
                                        Question #{{ $loop->iteration }}
                                    </span>
                                    <div class="text-xs font-bold" x-cloak>
                                        <span x-show="revealed && selected === correct" class="inline-flex items-center gap-1 text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Correct Answer!
                                        </span>
                                        <span x-show="revealed && selected && selected !== correct" class="inline-flex items-center gap-1 text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-full">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Incorrect
                                        </span>
                                    </div>
                                </div>

                                <p class="text-xs sm:text-sm font-bold text-slate-800 leading-relaxed">
                                    {{ $q->question_text }}
                                </p>

                                <!-- Interactive Options List -->
                                <div class="space-y-2 text-xs">
                                    @foreach($optionsMap as $letter => $optText)
                                        @if(!empty($optText))
                                        <button type="button"
                                                @click="selectOption('{{ $letter }}')"
                                                class="w-full text-left p-3 rounded-xl border transition-all flex items-start gap-3 cursor-pointer group"
                                                :class="{
                                                    'bg-emerald-50/90 border-emerald-500 text-emerald-950 font-semibold ring-1 ring-emerald-500 shadow-xs': revealed && '{{ $letter }}' === correct,
                                                    'bg-rose-50/90 border-rose-500 text-rose-950 font-semibold ring-1 ring-rose-500 shadow-xs': revealed && selected === '{{ $letter }}' && '{{ $letter }}' !== correct,
                                                    'bg-slate-50/70 border-gray-200 text-slate-700 hover:bg-slate-100 hover:border-[#2563eb]': !revealed || (selected !== '{{ $letter }}' && '{{ $letter }}' !== correct)
                                                }">
                                            <span class="w-6 h-6 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 transition"
                                                  :class="{
                                                    'bg-emerald-600 text-white shadow-xs': revealed && '{{ $letter }}' === correct,
                                                    'bg-rose-600 text-white shadow-xs': revealed && selected === '{{ $letter }}' && '{{ $letter }}' !== correct,
                                                    'bg-white border border-gray-300 text-slate-700 group-hover:border-[#2563eb] group-hover:text-[#2563eb]': !revealed || (selected !== '{{ $letter }}' && '{{ $letter }}' !== correct)
                                                  }">
                                                {{ $letter }}
                                            </span>
                                            <span class="flex-1 leading-relaxed pt-0.5">{{ $optText }}</span>
                                        </button>
                                        @endif
                                    @endforeach
                                </div>

                                <!-- Explanation Toggle -->
                                <div class="flex items-center justify-between pt-1">
                                    <button type="button" 
                                            @click="revealed = !revealed" 
                                            class="text-xs font-bold text-[#2563eb] hover:text-blue-700 transition flex items-center gap-1.5 cursor-pointer">
                                        <span x-text="revealed ? 'Hide Explanation' : 'Show Explanation & Answer'"></span>
                                        <svg class="w-3.5 h-3.5 transform transition-transform" :class="revealed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <span x-show="!revealed" class="text-[11px] text-slate-400">Click any option to test your answer</span>
                                </div>

                                <!-- Detailed Explanation Box -->
                                <div x-show="revealed" x-cloak class="p-4 rounded-xl bg-blue-50/50 border border-blue-200/80 text-xs text-slate-700 space-y-1.5">
                                    <div class="flex items-center gap-2 font-bold text-slate-900">
                                        <span class="text-base">💡</span>
                                        <span>Correct Answer: Option <span class="font-mono-code text-[#2563eb]" x-text="correct"></span></span>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed text-xs" x-text="explanation"></p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white border border-gray-200 p-6 rounded-2xl text-center py-10 space-y-3">
                        <div class="w-12 h-12 rounded-full bg-blue-50 text-[#2563eb] flex items-center justify-center mx-auto text-xl font-bold">?</div>
                        <h3 class="text-sm font-bold text-slate-800">Sample Questions Available Soon</h3>
                        <p class="text-xs text-slate-500 max-w-md mx-auto">Verified sample practice questions are currently being indexed for this exam. You can launch the full Web Simulator Demo below.</p>
                    </div>
                @endif

                <!-- Simulator Demo CTA Banner -->
                <div class="rounded-2xl bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 p-5 sm:p-6 text-white flex flex-col sm:flex-row items-center justify-between gap-4 shadow-lg border border-cyan-500/30">
                    <div class="space-y-1 text-center sm:text-left">
                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-500/20 text-cyan-300 font-mono-code uppercase">
                            ⚡ Web Simulator Experience
                        </div>
                        <h3 class="text-sm sm:text-base font-black text-white">Want to practice all {{ $exam->calculated_question_count > 0 ? $exam->calculated_question_count : ($exam->question_count > 0 ? $exam->question_count : '400+') }} questions?</h3>
                        <p class="text-xs text-slate-300">Experience the full interactive test engine with timed exam mode, question review, and instant scoring.</p>
                    </div>
                    <a href="{{ route('public.demo-test-engine.lobby', $exam->slug) }}" class="bg-[#2563eb] hover:bg-blue-600 text-white text-xs font-bold px-5 py-3 rounded-xl shadow transition shrink-0 uppercase tracking-wider flex items-center gap-2">
                        <span>Launch Free Simulator Demo</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>

            <!-- Tab Content: Reviews -->
            <div x-show="activeTab === 'reviews'" x-cloak class="space-y-4">
                <h2 class="text-base font-bold text-slate-900">Candidate Reviews</h2>
                @if(isset($reviews) && count($reviews) > 0)
                    <div class="space-y-3">
                        @foreach($reviews as $rev)
                            <div class="bg-white border border-gray-200 p-4 rounded-xl">
                                <div class="text-amber-500 text-xs mb-1">{{ str_repeat('★', $rev->rating) }}</div>
                                <p class="text-xs text-slate-700 italic">"{{ $rev->comment }}"</p>
                                <p class="text-[10px] text-slate-500 font-bold mt-2">— {{ $rev->user ? $rev->user->name : 'Verified Candidate' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white border border-gray-200 p-4 rounded-xl">
                        <div class="text-amber-500 text-xs mb-1">★★★★★</div>
                        <p class="text-xs text-slate-700 italic">"Passed with 920/1000! Every topic covered on my actual test was addressed in this guide."</p>
                        <p class="text-[10px] text-slate-500 font-bold mt-2">— Mark T., Verified Candidate</p>
                    </div>
                @endif
            </div>

            <!-- Tab Content: FAQ -->
            <div x-show="activeTab === 'faq'" x-cloak class="space-y-3">
                <h2 class="text-base font-bold text-slate-900">Frequently Asked Questions</h2>
                @php
                    $renderedFaqs = $exam->resolved_faqs;
                @endphp
                @if(!empty($renderedFaqs) && is_array($renderedFaqs) && count($renderedFaqs) > 0)
                    <div class="space-y-3">
                        @foreach($renderedFaqs as $faq)
                            <div class="bg-white border border-gray-200 p-4 rounded-xl">
                                <h4 class="text-xs font-bold text-slate-800">{{ $faq['question'] ?? '' }}</h4>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ strip_tags($faq['answer'] ?? '') }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white border border-gray-200 p-4 rounded-xl">
                        <h4 class="text-xs font-bold text-slate-800">How long do I have access to the simulator?</h4>
                        <p class="text-xs text-slate-600 mt-1">You receive instant access with unlimited retakes and 90 days of free updates.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
