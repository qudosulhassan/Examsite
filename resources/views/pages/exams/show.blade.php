@extends('layouts.public')

@section('title', $exam->meta_title ?? "{$exam->exam_code} Exam Dumps & Study Guide | ExamsNinja")
@section('meta_description', $exam->meta_description ?? "Get updated {$exam->exam_code} ({$exam->exam_name}) exam questions, answers, and study guides. Try our free demo or web-based test engine.")
@section('meta_keywords', $exam->meta_keywords ?? "{$exam->exam_code}, {$exam->exam_code} exam dumps, {$exam->exam_code} practice test, {$exam->vendor->name} certification")
@section('canonical_url', route('exams.show', $exam->slug))
@section('og_type', 'product')

@section('seo_tags')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "Course",
  "name": "{{ $exam->exam_code }} - {{ $exam->exam_name }}",
  "description": "{{ strip_tags($exam->description) }}",
  "provider": {
    "@type": "Organization",
    "name": "{{ $exam->vendor->name }}",
    "sameAs": "{{ route('vendors.show', $exam->vendor->slug) }}"
  },
  "offers": {
    "@type": "Offer",
    "price": "{{ $exam->price_engine }}",
    "priceCurrency": "USD",
    "category": "Test Preparation"
  }
}
</script>
@endsection

@section('content')
<!-- Hero Section (Premium Deep Space) -->
<section class="bg-gradient-to-br from-[#07101E] via-[#0A1628] to-[#0F172A] text-white pt-12 pb-28 relative overflow-hidden" x-data="{ demoModalOpen: false }">
    <!-- Abstract glowing orbs -->
    <div class="absolute -top-32 -left-32 w-[500px] h-[500px] bg-cyan rounded-full mix-blend-screen filter blur-[150px] opacity-15 animate-pulse pointer-events-none"></div>
    <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 pointer-events-none"></div>
    
    <!-- Abstract Tech Lines -->
    <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 40px 40px;"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <!-- Breadcrumbs -->
        <div class="mb-10 relative z-20">
            <nav class="flex" aria-label="Breadcrumb">
              <ol class="inline-flex items-center space-x-1 md:space-x-3 bg-white/5 backdrop-blur-md px-4 py-2 rounded-full border border-white/10 shadow-lg">
                <li class="inline-flex items-center">
                  <a href="{{ url('/') }}" class="inline-flex items-center text-[13px] font-bold text-gray-400 hover:text-cyan transition-colors">
                    <svg class="w-3.5 h-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 001 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                    Home
                  </a>
                </li>
                <li>
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="{{ url('/vendors') }}" class="ml-1 text-[13px] font-bold text-gray-400 hover:text-cyan transition-colors md:ml-2">Vendors</a>
                  </div>
                </li>
                <li>
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="{{ url('/vendors/' . $exam->vendor->slug) }}" class="ml-1 text-[13px] font-bold text-gray-400 hover:text-cyan transition-colors md:ml-2">{{ $exam->vendor->name }}</a>
                  </div>
                </li>
                <li aria-current="page">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="ml-1 text-[13px] font-bold text-cyan md:ml-2">{{ $exam->exam_code }}</span>
                  </div>
                </li>
              </ol>
            </nav>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            
            <!-- Left Column: Details & Previews -->
            <div class="lg:col-span-8 space-y-12">
                
                <!-- Exam Intro Header -->
                <div class="space-y-10">
                    <h1 class="text-4xl sm:text-5xl lg:text-[54px] font-black tracking-tight leading-[1.1] text-white">
                        <span class="block mb-2">{{ $exam->exam_name }}</span>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan via-blue-400 to-blue-500 inline-block drop-shadow-sm">Study Guide & Practice Questions</span>
                    </h1>
                    
                    <!-- Premium Details Grid -->
                    <div class="bg-[#0F172A]/60 backdrop-blur-2xl border border-white/10 rounded-[32px] p-8 sm:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.3)] relative overflow-hidden group">
                        <!-- Decorative glow on hover -->
                        <div class="absolute inset-0 bg-gradient-to-br from-cyan/10 to-blue-600/10 opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none"></div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-y-10 gap-x-8 relative z-10">
                            
                            <!-- Detail: Vendor -->
                            <div class="flex flex-col group/item">
                                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-3 flex items-center">
                                    <div class="w-7 h-7 rounded-lg bg-cyan/10 flex items-center justify-center mr-3 group-hover/item:scale-110 transition-transform duration-300 border border-cyan/20">
                                        <svg class="w-4 h-4 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    </div>
                                    Vendor
                                </span>
                                <span class="text-xl font-black text-white pl-[40px]">{{ $exam->vendor->name ?? 'N/A' }}</span>
                            </div>

                            <!-- Detail: Exam Code -->
                            <div class="flex flex-col group/item">
                                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-3 flex items-center">
                                    <div class="w-7 h-7 rounded-lg bg-blue-500/10 flex items-center justify-center mr-3 group-hover/item:scale-110 transition-transform duration-300 border border-blue-500/20">
                                        <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                    </div>
                                    Exam Code
                                </span>
                                <div class="pl-[40px] flex items-center space-x-2 mt-1 whitespace-nowrap">
                                    <span class="text-xl font-black text-white">{{ $exam->exam_code }}</span>
                                    <span class="bg-gradient-to-r from-cyan/20 to-blue-500/20 text-cyan text-[10px] px-2.5 py-1 rounded-md border border-cyan/30 font-bold uppercase tracking-wider">{{ $exam->difficulty }}</span>
                                </div>
                            </div>
                            
                            <!-- Detail: Exam Name -->
                            <div class="flex flex-col md:col-span-1 group/item">
                                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-3 flex items-center">
                                    <div class="w-7 h-7 rounded-lg bg-purple-500/10 flex items-center justify-center mr-3 group-hover/item:scale-110 transition-transform duration-300 border border-purple-500/20">
                                        <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                    </div>
                                    Full Name
                                </span>
                                <span class="text-sm font-bold text-gray-300 leading-snug pl-[40px] line-clamp-2" title="{{ $exam->exam_name }}">{{ $exam->exam_name }}</span>
                            </div>

                            <!-- Detail: Exam Questions -->
                            <div class="flex flex-col group/item">
                                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-3 flex items-center">
                                    <div class="w-7 h-7 rounded-lg bg-green-500/10 flex items-center justify-center mr-3 group-hover/item:scale-110 transition-transform duration-300 border border-green-500/20">
                                        <svg class="w-4 h-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    Questions
                                </span>
                                <span class="text-xl font-black text-white pl-[40px]">{{ $exam->question_count }} <span class="text-[11px] font-bold text-gray-500 ml-1 uppercase tracking-widest">Available</span></span>
                            </div>

                            <!-- Detail: Last Updated -->
                            <div class="flex flex-col group/item">
                                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-3 flex items-center">
                                    <div class="w-7 h-7 rounded-lg bg-orange/10 flex items-center justify-center mr-3 group-hover/item:scale-110 transition-transform duration-300 border border-orange/20">
                                        <svg class="w-4 h-4 text-orange" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    Last Updated
                                </span>
                                <span class="text-[15px] font-black text-white pl-[40px] flex items-center mt-1 whitespace-nowrap">
                                    <span class="relative flex h-2.5 w-2.5 mr-2.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-cyan"></span>
                                    </span>
                                    {{ $exam->last_updated_at ? $exam->last_updated_at->format('M d, Y') : 'Recently Updated' }}
                                </span>
                            </div>

                            <!-- Detail: Exam Certification -->
                            <div class="flex flex-col group/item">
                                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-3 flex items-center">
                                    <div class="w-7 h-7 rounded-lg bg-pink-500/10 flex items-center justify-center mr-3 group-hover/item:scale-110 transition-transform duration-300 border border-pink-500/20">
                                        <svg class="w-4 h-4 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                                    </div>
                                    Certification
                                </span>
                                <span class="text-[15px] font-bold text-cyan pl-[40px] line-clamp-2 mt-1" title="{{ $exam->certifications && $exam->certifications->count() > 0 ? $exam->certifications->first()->name : 'N/A' }}">
                                    @if($exam->certifications && $exam->certifications->count() > 0)
                                        <a href="{{ route('certifications.show', $exam->certifications->first()->slug) }}" class="hover:text-white transition-colors border-b border-cyan/30 hover:border-white pb-0.5">{{ $exam->certifications->first()->name }}</a>
                                    @else
                                        <span class="text-gray-500">N/A</span>
                                    @endif
                                </span>
                            </div>

                        </div>
                    </div>
                    
                    <!-- Guarantee card info (Moved to balance layout) -->
                    <div class="bg-white/5 backdrop-blur-md border border-orange/20 rounded-2xl p-6 flex items-start space-x-4 shadow-xl relative overflow-hidden group mt-8">
                        <div class="absolute -right-10 -top-10 text-orange/10 group-hover:scale-150 transition-transform duration-700 pointer-events-none">
                            <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-orange/20 flex items-center justify-center flex-shrink-0 relative z-10 shadow-[0_0_15px_rgba(255,107,53,0.3)]">
                            <svg class="w-6 h-6 text-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="relative z-10 space-y-1">
                            <p class="text-[11px] font-black text-orange uppercase tracking-widest">100% Pass Guarantee</p>
                            <p class="text-[12px] text-gray-400 font-medium leading-relaxed">Pass on your first attempt or get a full refund within 30 days. No questions asked.</p>
                        </div>
                    </div>
                </div>

                <!-- Sticky Purchase Box (Mobile only) -->
                <div class="block lg:hidden mt-10">
                    <!-- Right Column content stacks here on mobile -->
                </div>

            </div>

            <!-- Right Column: Sticky Purchase Box -->
            <div id="purchase-card" class="lg:col-span-4 lg:pl-6 space-y-6 lg:sticky lg:top-28 z-30"
                 x-data="{ 
                    updatePeriodCombo: '3',
                    updatePeriodPdf: '3',
                    updatePeriodEngine: '3',
                    baseCombo: {{ ($exam->price_pdf + $exam->price_engine) * 0.90 }},
                    basePdf: {{ (float)$exam->price_pdf }},
                    baseEngine: {{ (float)$exam->price_engine }},
                    extra3: {{ (float)($exam->update_price_3_months ?? 0) }},
                    extra6: {{ (float)($exam->update_price_6_months ?? 10) }},
                    extra12: {{ (float)($exam->update_price_12_months ?? 20) }},
                    get extraPriceCombo() { return this.updatePeriodCombo === '12' ? this.extra12 : (this.updatePeriodCombo === '6' ? this.extra6 : this.extra3); },
                    get extraPricePdf() { return this.updatePeriodPdf === '12' ? this.extra12 : (this.updatePeriodPdf === '6' ? this.extra6 : this.extra3); },
                    get extraPriceEngine() { return this.updatePeriodEngine === '12' ? this.extra12 : (this.updatePeriodEngine === '6' ? this.extra6 : this.extra3); }
                 }">

                 <!-- Flash Alerts -->
                 @if(session('error'))
                     <div class="mb-6 bg-red-500/10 border border-red-500/50 rounded-xl p-4 flex items-start space-x-3 backdrop-blur-md z-50">
                         <svg class="w-6 h-6 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                         <p class="text-red-400 text-sm font-medium">{{ session('error') }}</p>
                     </div>
                 @endif
                 @if(session('success'))
                     <div class="mb-6 bg-green-500/10 border border-green-500/50 rounded-xl p-4 flex items-start space-x-3 backdrop-blur-md z-50">
                         <svg class="w-6 h-6 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                         <p class="text-green-400 text-sm font-medium">{{ session('success') }}</p>
                     </div>
                 @endif
                 
                 <div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-3xl shadow-[0_20px_60px_rgba(0,0,0,0.5)] p-8 text-white space-y-8 relative overflow-hidden">
                    <!-- Glass reflection -->
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-[10px] font-black text-cyan uppercase tracking-widest bg-cyan/10 px-3 py-1.5 rounded-md border border-cyan/20">Full Access Package</span>
                            <!-- Rating -->
                            <div class="flex items-center space-x-1 text-yellow-400 text-xs drop-shadow-[0_0_5px_rgba(250,204,21,0.6)]">
                                <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                            </div>
                        </div>
                        <h3 class="text-2xl font-black text-white leading-tight">{{ $exam->exam_code }} Ultimate Bundle</h3>
                    </div>

                    <!-- Premium Combo Package (Best Value) -->
                    <div class="relative group rounded-2xl p-[3px] transition-all duration-300 z-10 hover:scale-[1.03] hover:-translate-y-1">
                        <!-- Glowing Animated Border -->
                        <div class="absolute inset-0 bg-gradient-to-r from-cyan via-blue-500 to-purple-600 rounded-2xl opacity-80 group-hover:opacity-100 group-hover:blur-md transition-all duration-500"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-cyan via-blue-500 to-purple-600 rounded-2xl"></div>
                        
                        <!-- Inner Card -->
                        <div class="relative h-full bg-navy rounded-[14px] p-6 z-10 overflow-hidden shadow-inner">
                            <!-- Discount Badge -->
                            <div class="absolute top-0 right-0 bg-gradient-to-l from-orange to-red-500 text-white text-[11px] font-black px-4 py-2 rounded-bl-xl uppercase tracking-widest shadow-[0_5px_15px_rgba(255,107,53,0.4)]">
                                Save 10%
                            </div>
                            
                            <div class="flex justify-between items-start mb-6 pt-3">
                                <div>
                                    <h4 class="font-black text-xl text-white leading-tight">PDF + Engine</h4>
                                </div>
                                <div class="text-right pr-2">
                                    <span class="block text-xs text-gray-400 line-through font-bold mb-1">${{ number_format($exam->price_pdf + $exam->price_engine, 2) }}</span>
                                    <span class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-cyan to-blue-400" x-text="`$${(baseCombo + extraPriceCombo).toFixed(2)}`">${{ number_format(($exam->price_pdf + $exam->price_engine) * 0.90 + ($exam->update_price_3_months ?? 0), 2) }}</span>
                                </div>
                            </div>
                            
                            <ul class="text-xs text-gray-300 font-medium space-y-3.5 mb-8">
                                <li class="flex items-center">
                                    <div class="w-5 h-5 rounded-full bg-cyan/20 flex items-center justify-center mr-3 shadow-[0_0_10px_rgba(0,212,170,0.2)]">
                                        <svg class="h-3 w-3 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    Web-Based Practice Simulator
                                </li>
                                <li class="flex items-center">
                                    <div class="w-5 h-5 rounded-full bg-cyan/20 flex items-center justify-center mr-3 shadow-[0_0_10px_rgba(0,212,170,0.2)]">
                                        <svg class="h-3 w-3 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    Printable & Mobile PDF Guides
                                </li>
                                <li class="flex items-center">
                                    <div class="w-5 h-5 rounded-full bg-cyan/20 flex items-center justify-center mr-3 shadow-[0_0_10px_rgba(0,212,170,0.2)]">
                                        <svg class="h-3 w-3 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    100% Verified Accurate Answers
                                </li>
                                <li class="flex items-center">
                                    <div class="w-5 h-5 rounded-full bg-cyan/20 flex items-center justify-center mr-3 shadow-[0_0_10px_rgba(0,212,170,0.2)]">
                                        <svg class="h-3 w-3 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    90 Days of Instant Free Updates
                                </li>
                            </ul>
                            <div class="mb-4">
                                <select x-model="updatePeriodCombo" class="w-full bg-slate-800 border border-white/20 text-white text-[11px] rounded-lg px-3 py-2 focus:ring-cyan focus:border-cyan">
                                    <option value="3" class="bg-white text-gray-900">3 Months Updates {{ ($exam->update_price_3_months ?? 0) > 0 ? '(+$' . number_format($exam->update_price_3_months, 2) . ')' : '(Included)' }}</option>
                                    <option value="6" class="bg-white text-gray-900">6 Months Updates (+${{ number_format($exam->update_price_6_months ?? 10, 2) }})</option>
                                    <option value="12" class="bg-white text-gray-900">12 Months Updates (+${{ number_format($exam->update_price_12_months ?? 20, 2) }})</option>
                                </select>
                            </div>
                            
                            <form id="combo_cart_form" action="{{ url('/cart/add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                                <input type="hidden" name="type" value="combo">
                                <input type="hidden" name="update_period" :value="updatePeriodCombo">
                                <button type="submit" class="w-full relative overflow-hidden group/btn bg-gradient-to-r from-cyan via-blue-500 to-cyan bg-[length:200%_auto] hover:bg-[right_center] text-white font-black py-4 rounded-xl shadow-[0_0_30px_rgba(0,212,170,0.5)] transition-all duration-500 transform active:scale-95 flex items-center justify-center space-x-2">
                                    <span class="text-sm uppercase tracking-widest text-white drop-shadow-md z-10">Add To Cart</span>
                                    <svg class="w-5 h-5 text-white group-hover/btn:translate-x-1 transition-transform z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    <!-- Shine effect -->
                                    <div class="absolute top-0 -inset-full h-full w-1/2 z-5 block transform -skew-x-12 bg-gradient-to-r from-transparent to-white opacity-20 group-hover/btn:animate-shine"></div>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 z-10 relative">
                        <!-- Option 1: PDF Guide -->
                        <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-5 hover:bg-gray-700/50 transition-colors group/opt">
                            <div class="mb-4 text-center">
                                <span class="bg-gray-900 text-gray-400 text-[10px] font-black px-2.5 py-1 rounded uppercase tracking-widest border border-gray-700">PDF Guide</span>
                                <div class="mt-3 text-xl font-black text-white group-hover/opt:text-cyan transition-colors" x-text="`$${(basePdf + extraPricePdf).toFixed(2)}`">${{ number_format($exam->price_pdf + ($exam->update_price_3_months ?? 0), 2) }}</div>
                            </div>
                            <div class="mb-4">
                                <select x-model="updatePeriodPdf" class="w-full bg-slate-800 border border-white/10 text-white text-[10px] rounded-lg px-2 py-1.5 focus:ring-cyan focus:border-cyan">
                                    <option value="3" class="bg-white text-gray-900">3 Months {{ ($exam->update_price_3_months ?? 0) > 0 ? '(+$' . number_format($exam->update_price_3_months, 2) . ')' : '(Included)' }}</option>
                                    <option value="6" class="bg-white text-gray-900">6 Months (+${{ number_format($exam->update_price_6_months ?? 10, 2) }})</option>
                                    <option value="12" class="bg-white text-gray-900">12 Months (+${{ number_format($exam->update_price_12_months ?? 20, 2) }})</option>
                                </select>
                            </div>
                            <form id="pdf_cart_form" action="{{ url('/cart/add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                                <input type="hidden" name="type" value="pdf">
                                <input type="hidden" name="update_period" :value="updatePeriodPdf">
                                <button type="submit" class="w-full bg-white/5 border border-white/10 hover:bg-cyan hover:border-cyan text-gray-300 hover:text-white text-[11px] font-bold py-2.5 rounded-lg transition-all uppercase tracking-wider">
                                    Select PDF
                                </button>
                            </form>
                        </div>

                        <!-- Option 2: Test Engine -->
                        <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl p-5 hover:bg-gray-700/50 transition-colors group/opt">
                            <div class="mb-4 text-center">
                                <span class="bg-blue-900/40 text-blue-300 text-[10px] font-black px-2.5 py-1 rounded uppercase tracking-widest border border-blue-500/30">Test Engine</span>
                                <div class="mt-3 text-xl font-black text-white group-hover/opt:text-blue-400 transition-colors" x-text="`$${(baseEngine + extraPriceEngine).toFixed(2)}`">${{ number_format($exam->price_engine + ($exam->update_price_3_months ?? 0), 2) }}</div>
                            </div>
                            <div class="mb-4">
                                <select x-model="updatePeriodEngine" class="w-full bg-slate-800 border border-white/10 text-white text-[10px] rounded-lg px-2 py-1.5 focus:ring-cyan focus:border-cyan">
                                    <option value="3" class="bg-white text-gray-900">3 Months {{ ($exam->update_price_3_months ?? 0) > 0 ? '(+$' . number_format($exam->update_price_3_months, 2) . ')' : '(Included)' }}</option>
                                    <option value="6" class="bg-white text-gray-900">6 Months (+${{ number_format($exam->update_price_6_months ?? 10, 2) }})</option>
                                    <option value="12" class="bg-white text-gray-900">12 Months (+${{ number_format($exam->update_price_12_months ?? 20, 2) }})</option>
                                </select>
                            </div>
                            <form id="engine_cart_form" action="{{ url('/cart/add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                                <input type="hidden" name="type" value="engine_single">
                                <input type="hidden" name="update_period" :value="updatePeriodEngine">
                                <button type="submit" class="w-full bg-white/5 border border-white/10 hover:bg-blue-600 hover:border-blue-600 text-gray-300 hover:text-white text-[11px] font-bold py-2.5 rounded-lg transition-all uppercase tracking-wider">
                                    Select Engine
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Free Demo Button Link -->
                    <div class="pt-2">
                        <button x-on:click="$dispatch('open-demo-modal')" class="relative z-10 w-full bg-transparent border-2 border-white/10 hover:border-cyan hover:bg-cyan/10 text-gray-300 hover:text-cyan text-xs font-black py-4 rounded-xl text-center transition-all duration-300 uppercase tracking-widest flex items-center justify-center space-x-2 group">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-cyan group-hover:-translate-y-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span>Download Free Demo</span>
                        </button>
                    </div>

                    <!-- Trust info -->
                    <div class="relative z-10 border-t border-white/10 pt-6 text-center space-y-3">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <span class="text-[10px] text-gray-300 font-bold uppercase tracking-widest">256-Bit SSL Secure Checkout</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Content Section -->
<section class="bg-slate-50 py-20 relative overflow-hidden">
    <!-- Soft Background Blobs -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute -top-[10%] -right-[10%] w-[40%] h-[40%] rounded-full bg-cyan/5 blur-[120px]"></div>
        <div class="absolute top-[40%] -left-[10%] w-[30%] h-[50%] rounded-full bg-blue-500/5 blur-[120px]"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <!-- Main Content Area -->
            <div class="lg:col-span-8 space-y-16">
                
                <!-- Description -->
                <div class="bg-white border border-gray-100 rounded-3xl p-8 sm:p-10 shadow-[0_10px_40px_rgba(0,0,0,0.03)] space-y-6">
                    <h3 class="text-2xl sm:text-3xl font-black text-navy mb-4 flex items-center">
                        <svg class="w-8 h-8 text-cyan mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        About the Certification Exam
                    </h3>
                    <div class="prose prose-base sm:prose-lg text-gray-600 max-w-none leading-relaxed font-normal space-y-4">
                        <p class="font-normal text-gray-600">{{ $exam->description }}</p>
                        <p class="font-normal text-gray-600">Our expert certification guides include comprehensive questions and answers designed to mirror the actual exam environment. The full study package will prepare you for the variety of formats found on this test, including multiple choice, multi-select, and drag-and-drop questions.</p>
                    </div>
                </div>

                <!-- Collapsible Topics covered -->
                <div class="space-y-4" x-data="{ open: true }">
                    <button x-on:click="open = !open" class="flex justify-between items-center w-full bg-white border border-gray-100 hover:border-cyan/50 rounded-3xl p-6 sm:p-8 font-black text-navy text-xl sm:text-2xl text-left focus:outline-none shadow-[0_5px_20px_rgba(0,0,0,0.02)] transition-all group">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center group-hover:bg-cyan/10 transition-colors">
                                <svg class="w-5 h-5 text-gray-400 group-hover:text-cyan transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            </div>
                            <span>Blueprinted Exam Topics Covered</span>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-gray-50 group-hover:bg-cyan/10 flex items-center justify-center transition-colors">
                            <svg class="h-6 w-6 text-gray-400 group-hover:text-cyan transition duration-300" :class="open ? 'transform rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 -translate-y-4"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="bg-white border border-gray-100 rounded-3xl p-8 sm:p-10 shadow-[0_10px_40px_rgba(0,0,0,0.03)] mt-4 relative overflow-hidden">
                        
                        <div class="absolute top-0 right-0 w-32 h-32 bg-cyan/5 rounded-bl-full pointer-events-none"></div>

                        <p class="text-base font-normal text-gray-600 mb-8 relative z-10">The study guide addresses all core domains defined in the official vendor certification syllabus:</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8 text-base font-normal text-gray-700 relative z-10">
                            @if(is_array($exam->topics))
                                @foreach($exam->topics as $topic)
                                    <div class="flex items-start space-x-3 group/topic">
                                        <div class="mt-1 w-5 h-5 rounded-full bg-cyan/10 flex items-center justify-center flex-shrink-0 group-hover/topic:bg-cyan transition-colors">
                                            <svg class="w-3 h-3 text-cyan group-hover/topic:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <span class="group-hover/topic:text-cyan transition-colors font-normal">{{ $topic }}</span>
                                    </div>
                                @endforeach
                            @else
                                <div class="flex items-start space-x-3 group/topic">
                                    <div class="mt-1 w-5 h-5 rounded-full bg-cyan/10 flex items-center justify-center flex-shrink-0 group-hover/topic:bg-cyan transition-colors">
                                        <svg class="w-3 h-3 text-cyan group-hover/topic:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <span class="group-hover/topic:text-cyan transition-colors font-normal">General Exam Concepts</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sample Questions -->
                <div class="space-y-10 pt-8">
                    <div class="text-center mb-12">
                        <span class="text-[11px] font-black text-cyan uppercase tracking-widest bg-cyan/10 px-3 py-1.5 rounded-full border border-cyan/20 mb-4 inline-block">Free Trial</span>
                        <h3 class="text-3xl sm:text-4xl font-black text-navy mb-4 tracking-tight">Interactive Sample Questions</h3>
                        <p class="text-lg text-gray-600 font-normal max-w-2xl mx-auto">Try solving these actual questions from the latest {{ $exam->exam_code }} exam pool to test your knowledge.</p>
                    </div>
                    
                    @foreach($sampleQuestions as $index => $question)
                        <div class="bg-white border border-gray-100 rounded-[32px] p-8 sm:p-10 text-navy space-y-8 shadow-[0_15px_40px_rgba(0,0,0,0.04)] hover:shadow-[0_20px_50px_rgba(0,212,170,0.08)] hover:border-cyan/30 transition-all duration-300 relative overflow-hidden" x-data="{ selectedOption: null, checked: false }">
                            
                            <div class="absolute top-0 left-0 w-2 h-full bg-gradient-to-b from-cyan to-blue-500 opacity-50"></div>

                            <!-- Question Header -->
                            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 pb-6 border-b border-gray-100">
                                <div class="flex items-center space-x-4">
                                    <span class="bg-navy text-white text-sm font-black px-4 py-1.5 rounded-lg tracking-widest uppercase shadow-md">Question {{ $index + 1 }}</span>
                                </div>
                                <span class="bg-gray-50 text-gray-500 text-[10px] font-black px-4 py-2 rounded-xl border border-gray-100 uppercase tracking-widest truncate max-w-full sm:max-w-xs">{{ $question->topic }}</span>
                            </div>
                            
                            <!-- Question Text -->
                            <p class="text-base sm:text-lg font-normal leading-relaxed text-gray-800">{!! $question->question_text !!}</p>
                            
                            <!-- Options list styled -->
                            <div class="space-y-4">
                                <!-- Option A -->
                                <button x-on:click="if(!checked) selectedOption = 'A'"
                                        class="w-full text-left p-5 rounded-2xl border-2 text-base transition-all flex items-center justify-between group/opt shadow-sm"
                                        :class="[
                                            !checked && selectedOption === 'A' ? 'border-cyan bg-cyan/5 text-navy font-bold shadow-[0_0_20px_rgba(0,212,170,0.15)] scale-[1.01]' : '',
                                            !checked && selectedOption !== 'A' ? 'border-gray-100 hover:border-gray-300 hover:bg-gray-50 text-gray-700 font-normal' : '',
                                            checked && 'A' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-50 text-green-800 font-bold shadow-md' : '',
                                            checked && selectedOption === 'A' && 'A' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-50 text-red-800 font-bold' : '',
                                            checked && 'A' !== '{{ $question->correct_option }}' && selectedOption !== 'A' ? 'border-gray-50 opacity-40 text-gray-400 cursor-not-allowed font-normal' : ''
                                        ]"
                                        :disabled="checked">
                                    <div class="flex items-center space-x-5 pr-4">
                                        <!-- Custom Radio -->
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors shadow-sm"
                                             :class="[
                                                selectedOption === 'A' && !checked ? 'border-cyan' : '',
                                                selectedOption !== 'A' && !checked ? 'border-gray-300 group-hover/opt:border-gray-400' : '',
                                                checked && 'A' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-500' : '',
                                                checked && selectedOption === 'A' && 'A' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-500' : '',
                                                checked && 'A' !== '{{ $question->correct_option }}' && selectedOption !== 'A' ? 'border-gray-300' : ''
                                             ]">
                                            <div class="w-3 h-3 rounded-full transition-all"
                                                 :class="[
                                                    selectedOption === 'A' && !checked ? 'bg-cyan scale-100' : 'scale-0',
                                                    checked && 'A' === '{{ $question->correct_option }}' ? 'bg-white scale-100' : '',
                                                    checked && selectedOption === 'A' && 'A' !== '{{ $question->correct_option }}' ? 'bg-white scale-100' : ''
                                                 ]"></div>
                                        </div>
                                        <span class="leading-relaxed font-normal text-gray-700"><span class="mr-2 font-bold text-navy text-base">A.</span> {{ $question->option_a }}</span>
                                    </div>
                                    
                                    <!-- Status Icons -->
                                    <template x-if="checked && 'A' === '{{ $question->correct_option }}'">
                                        <svg class="h-8 w-8 text-green-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    </template>
                                    <template x-if="checked && selectedOption === 'A' && 'A' !== '{{ $question->correct_option }}'">
                                        <svg class="h-8 w-8 text-red-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </template>
                                </button>

                                <!-- Option B -->
                                <button x-on:click="if(!checked) selectedOption = 'B'"
                                        class="w-full text-left p-5 rounded-2xl border-2 text-base transition-all flex items-center justify-between group/opt shadow-sm"
                                        :class="[
                                            !checked && selectedOption === 'B' ? 'border-cyan bg-cyan/5 text-navy font-bold shadow-[0_0_20px_rgba(0,212,170,0.15)] scale-[1.01]' : '',
                                            !checked && selectedOption !== 'B' ? 'border-gray-100 hover:border-gray-300 hover:bg-gray-50 text-gray-700 font-normal' : '',
                                            checked && 'B' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-50 text-green-800 font-bold shadow-md' : '',
                                            checked && selectedOption === 'B' && 'B' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-50 text-red-800 font-bold' : '',
                                            checked && 'B' !== '{{ $question->correct_option }}' && selectedOption !== 'B' ? 'border-gray-50 opacity-40 text-gray-400 cursor-not-allowed font-normal' : ''
                                        ]"
                                        :disabled="checked">
                                    <div class="flex items-center space-x-5 pr-4">
                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors shadow-sm"
                                             :class="[
                                                selectedOption === 'B' && !checked ? 'border-cyan' : '',
                                                selectedOption !== 'B' && !checked ? 'border-gray-300 group-hover/opt:border-gray-400' : '',
                                                checked && 'B' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-500' : '',
                                                checked && selectedOption === 'B' && 'B' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-500' : '',
                                                checked && 'B' !== '{{ $question->correct_option }}' && selectedOption !== 'B' ? 'border-gray-300' : ''
                                             ]">
                                            <div class="w-3 h-3 rounded-full transition-all"
                                                 :class="[
                                                    selectedOption === 'B' && !checked ? 'bg-cyan scale-100' : 'scale-0',
                                                    checked && 'B' === '{{ $question->correct_option }}' ? 'bg-white scale-100' : '',
                                                    checked && selectedOption === 'B' && 'B' !== '{{ $question->correct_option }}' ? 'bg-white scale-100' : ''
                                                 ]"></div>
                                        </div>
                                        <span class="leading-relaxed font-normal text-gray-700"><span class="mr-2 font-bold text-navy text-base">B.</span> {{ $question->option_b }}</span>
                                    </div>
                                    <template x-if="checked && 'B' === '{{ $question->correct_option }}'">
                                        <svg class="h-8 w-8 text-green-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                    </template>
                                    <template x-if="checked && selectedOption === 'B' && 'B' !== '{{ $question->correct_option }}'">
                                        <svg class="h-8 w-8 text-red-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </template>
                                </button>

                                <!-- Option C -->
                                @if(!empty($question->option_c))
                                    <button x-on:click="if(!checked) selectedOption = 'C'"
                                            class="w-full text-left p-5 rounded-2xl border-2 text-base transition-all flex items-center justify-between group/opt shadow-sm"
                                            :class="[
                                                !checked && selectedOption === 'C' ? 'border-cyan bg-cyan/5 text-navy font-bold shadow-[0_0_20px_rgba(0,212,170,0.15)] scale-[1.01]' : '',
                                                !checked && selectedOption !== 'C' ? 'border-gray-100 hover:border-gray-300 hover:bg-gray-50 text-gray-700 font-normal' : '',
                                                checked && 'C' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-50 text-green-800 font-bold shadow-md' : '',
                                                checked && selectedOption === 'C' && 'C' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-50 text-red-800 font-bold' : '',
                                                checked && 'C' !== '{{ $question->correct_option }}' && selectedOption !== 'C' ? 'border-gray-50 opacity-40 text-gray-400 cursor-not-allowed font-normal' : ''
                                            ]"
                                            :disabled="checked">
                                        <div class="flex items-center space-x-5 pr-4">
                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors shadow-sm"
                                                 :class="[
                                                    selectedOption === 'C' && !checked ? 'border-cyan' : '',
                                                    selectedOption !== 'C' && !checked ? 'border-gray-300 group-hover/opt:border-gray-400' : '',
                                                    checked && 'C' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-500' : '',
                                                    checked && selectedOption === 'C' && 'C' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-500' : '',
                                                    checked && 'C' !== '{{ $question->correct_option }}' && selectedOption !== 'C' ? 'border-gray-300' : ''
                                                 ]">
                                                <div class="w-3 h-3 rounded-full transition-all"
                                                     :class="[
                                                        selectedOption === 'C' && !checked ? 'bg-cyan scale-100' : 'scale-0',
                                                        checked && 'C' === '{{ $question->correct_option }}' ? 'bg-white scale-100' : '',
                                                        checked && selectedOption === 'C' && 'C' !== '{{ $question->correct_option }}' ? 'bg-white scale-100' : ''
                                                     ]"></div>
                                            </div>
                                            <span class="leading-relaxed font-normal text-gray-700"><span class="mr-2 font-bold text-navy text-base">C.</span> {{ $question->option_c }}</span>
                                        </div>
                                        <template x-if="checked && 'C' === '{{ $question->correct_option }}'">
                                            <svg class="h-8 w-8 text-green-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                        </template>
                                        <template x-if="checked && selectedOption === 'C' && 'C' !== '{{ $question->correct_option }}'">
                                            <svg class="h-8 w-8 text-red-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </template>
                                    </button>
                                @endif

                                <!-- Option D -->
                                @if(!empty($question->option_d))
                                    <button x-on:click="if(!checked) selectedOption = 'D'"
                                            class="w-full text-left p-5 rounded-2xl border-2 text-base transition-all flex items-center justify-between group/opt shadow-sm"
                                            :class="[
                                                !checked && selectedOption === 'D' ? 'border-cyan bg-cyan/5 text-navy font-bold shadow-[0_0_20px_rgba(0,212,170,0.15)] scale-[1.01]' : '',
                                                !checked && selectedOption !== 'D' ? 'border-gray-100 hover:border-gray-300 hover:bg-gray-50 text-gray-700 font-normal' : '',
                                                checked && 'D' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-50 text-green-800 font-bold shadow-md' : '',
                                                checked && selectedOption === 'D' && 'D' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-50 text-red-800 font-bold' : '',
                                                checked && 'D' !== '{{ $question->correct_option }}' && selectedOption !== 'D' ? 'border-gray-50 opacity-40 text-gray-400 cursor-not-allowed font-normal' : ''
                                            ]"
                                            :disabled="checked">
                                        <div class="flex items-center space-x-5 pr-4">
                                            <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors shadow-sm"
                                                 :class="[
                                                    selectedOption === 'D' && !checked ? 'border-cyan' : '',
                                                    selectedOption !== 'D' && !checked ? 'border-gray-300 group-hover/opt:border-gray-400' : '',
                                                    checked && 'D' === '{{ $question->correct_option }}' ? 'border-green-500 bg-green-500' : '',
                                                    checked && selectedOption === 'D' && 'D' !== '{{ $question->correct_option }}' ? 'border-red-500 bg-red-500' : '',
                                                    checked && 'D' !== '{{ $question->correct_option }}' && selectedOption !== 'D' ? 'border-gray-300' : ''
                                                 ]">
                                                <div class="w-3 h-3 rounded-full transition-all"
                                                     :class="[
                                                        selectedOption === 'D' && !checked ? 'bg-cyan scale-100' : 'scale-0',
                                                        checked && 'D' === '{{ $question->correct_option }}' ? 'bg-white scale-100' : '',
                                                        checked && selectedOption === 'D' && 'D' !== '{{ $question->correct_option }}' ? 'bg-white scale-100' : ''
                                                     ]"></div>
                                            </div>
                                            <span class="leading-relaxed font-normal text-gray-700"><span class="mr-2 font-bold text-navy text-base">D.</span> {{ $question->option_d }}</span>
                                        </div>
                                        <template x-if="checked && 'D' === '{{ $question->correct_option }}'">
                                            <svg class="h-8 w-8 text-green-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                        </template>
                                        <template x-if="checked && selectedOption === 'D' && 'D' !== '{{ $question->correct_option }}'">
                                            <svg class="h-8 w-8 text-red-500 flex-shrink-0 filter drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </template>
                                    </button>
                                @endif
                            </div>

                            <!-- Grade actions -->
                            <div class="flex justify-between items-center pt-8 mt-6 border-t border-gray-100">
                                <span class="text-[11px] font-black text-gray-400 uppercase tracking-widest flex items-center bg-gray-50 px-3 py-1.5 rounded-md">
                                    <svg class="w-4 h-4 mr-2 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Select an option to check
                                </span>
                                <template x-if="!checked">
                                    <button x-on:click="checked = true"
                                            class="bg-navy text-white text-sm font-black px-8 py-3.5 rounded-xl shadow-[0_10px_20px_rgba(10,22,40,0.15)] hover:shadow-[0_15px_30px_rgba(10,22,40,0.2)] hover:-translate-y-1 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none"
                                            :disabled="!selectedOption">
                                        Check Answer
                                    </button>
                                </template>
                                <template x-if="checked">
                                    <span class="text-sm font-black px-6 py-3 rounded-xl shadow-sm" :class="selectedOption === '{{ $question->correct_option }}' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'">
                                        <span x-text="selectedOption === '{{ $question->correct_option }}' ? '✓ Correct Answer!' : '✗ Incorrect Answer'"></span>
                                    </span>
                                </template>
                            </div>

                            <!-- Explanation slide down -->
                            <div x-show="checked" 
                                 x-transition:enter="transition ease-out duration-500"
                                 x-transition:enter-start="opacity-0 transform -translate-y-4 scale-95"
                                 x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                                 class="mt-8 bg-gradient-to-r from-gray-50 to-white border-l-4 border-cyan p-6 sm:p-8 rounded-r-2xl shadow-sm space-y-3 relative overflow-hidden">
                                
                                <div class="absolute -right-8 -top-8 w-24 h-24 bg-cyan/10 rounded-full blur-xl pointer-events-none"></div>

                                <div class="font-black text-navy text-[11px] uppercase tracking-widest flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                                    Expert Explanation
                                </div>
                                <p class="text-gray-600 leading-relaxed text-base font-normal relative z-10">{{ $question->explanation }}</p>
                            </div>
                        </div>
                    @endforeach

                    <!-- CTA to buy full exam to unlock answers -->
                    <div class="relative bg-gradient-to-br from-orange to-red-600 rounded-[32px] p-10 sm:p-14 text-center space-y-8 shadow-[0_20px_50px_rgba(255,107,53,0.3)] overflow-hidden mt-12 group hover:scale-[1.01] transition-transform duration-500">
                        <!-- Abstract glowing backgrounds -->
                        <div class="absolute -right-20 -top-20 bg-white/20 w-80 h-80 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000 pointer-events-none"></div>
                        <div class="absolute -left-20 -bottom-20 bg-yellow-400/20 w-60 h-60 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000 pointer-events-none"></div>
                        
                        <h4 class="text-3xl sm:text-4xl font-black text-white relative z-10 leading-tight">Ready to master all {{ $exam->question_count }} questions?</h4>
                        <p class="text-lg text-white/90 font-normal max-w-2xl mx-auto relative z-10 leading-relaxed">Unlock full access to the timed Test Engine and downloadable PDF study guides. Practice under real exam conditions.</p>
                        
                        <a href="#purchase-card" class="inline-block bg-white text-orange hover:bg-gray-50 hover:text-red-500 text-lg font-black py-5 px-12 rounded-2xl shadow-[0_10px_30px_rgba(0,0,0,0.15)] transition-all transform hover:-translate-y-1 relative z-10 uppercase tracking-widest hover:shadow-[0_15px_40px_rgba(0,0,0,0.2)]">
                            Unlock Full Access Now
                        </a>
                    </div>
                </div>

                <!-- Customer Reviews Section -->
                <div class="bg-white border border-gray-100 rounded-[32px] p-8 sm:p-10 space-y-10 shadow-[0_10px_40px_rgba(0,0,0,0.03)] mt-12">
                    <div class="text-center">
                        <span class="text-[11px] font-black text-orange uppercase tracking-widest bg-orange/10 px-3 py-1.5 rounded-full border border-orange/20 mb-4 inline-block">Testimonials</span>
                        <h3 class="text-3xl font-black text-navy tracking-tight">Verified Customer Reviews</h3>
                    </div>
                    
                    @if(count($reviews) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($reviews as $review)
                                <div class="bg-gray-50 rounded-3xl p-8 border border-gray-100 hover:border-cyan/30 hover:shadow-lg transition-all duration-300 flex flex-col h-full group">
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center space-x-4">
                                            <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-cyan to-blue-500 text-white flex items-center justify-center font-black shadow-md text-lg">{{ substr($review->user->name, 0, 2) }}</div>
                                            <div>
                                                <span class="block text-base font-black text-navy leading-tight">{{ $review->user->name }}</span>
                                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $review->created_at->format('M d, Y') }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-0.5 text-yellow-400 drop-shadow-sm">
                                            @for($i=1; $i<=5; $i++)
                                                <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            @endfor
                                        </div>
                                    </div>
                                    <div class="relative flex-grow">
                                        <svg class="absolute -top-2 -left-2 w-8 h-8 text-cyan/10 transform -scale-x-100" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"></path></svg>
                                        <p class="text-base text-gray-600 font-normal leading-relaxed pl-6 relative z-10">{{ $review->review_text }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-16 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                                <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            </div>
                            <p class="text-gray-500 font-bold text-lg">No reviews posted yet.</p>
                            <p class="text-gray-400 font-normal mt-1">Be the first to leave a review after your purchase!</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- lg right column placeholder to keep grid layout intact -->
            <div class="hidden lg:block lg:col-span-4"></div>
        </div>
    </div>
</section>

<!-- Related Exams Section -->
@php
    $relatedExams = App\Models\Exam::where('vendor_id', $exam->vendor_id)
        ->where('id', '!=', $exam->id)
        ->where('is_active', true)
        ->inRandomOrder()
        ->take(6)
        ->get();
@endphp

@if($relatedExams->count() > 0)
<section class="py-24 bg-white border-t border-gray-100 overflow-hidden relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-black text-navy tracking-tight">Frequently Bought Together</h2>
            <div class="w-24 h-1.5 bg-gradient-to-r from-cyan to-blue-500 mx-auto mt-6 rounded-full"></div>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
            @foreach($relatedExams as $related)
            <a href="{{ route('exams.show', $related->slug) }}" class="bg-gray-50 border border-gray-100 rounded-3xl p-6 hover:shadow-[0_15px_30px_rgba(0,0,0,0.06)] hover:border-cyan/30 hover:-translate-y-2 transition-all duration-300 flex flex-col h-full group relative overflow-hidden">
                <!-- Hover Accent Line -->
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-cyan to-blue-500 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-500"></div>

                <div class="text-[10px] text-gray-400 font-black mb-3 uppercase tracking-widest bg-white inline-block self-start px-2 py-1 rounded shadow-sm border border-gray-100">{{ $exam->vendor->name }}</div>
                <h3 class="text-lg font-black text-navy mb-4 leading-tight flex-grow group-hover:text-cyan transition-colors">
                    {{ $related->exam_code }}
                </h3>
                <div class="flex items-center justify-between mt-auto pt-5 border-t border-gray-200/60">
                    <span class="font-black text-gray-900 text-lg">${{ $related->price_pdf }}</span>
                    <div class="w-8 h-8 rounded-full bg-white shadow-sm flex items-center justify-center group-hover:bg-cyan group-hover:shadow-md transition-all duration-300 border border-gray-100 group-hover:border-transparent">
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Related Blog Posts Section -->
@php
    $relatedBlogPosts = \App\Models\BlogPost::with('category', 'user')
        ->where('status', 'published')
        ->where('related_exam_id', $exam->id)
        ->latest('published_at')
        ->limit(3)
        ->get();
@endphp

@if($relatedBlogPosts->count() > 0)
<section class="py-24 bg-gray-50 border-t border-gray-200 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-cyan/5 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-12 gap-4">
            <div>
                <span class="text-[11px] font-black text-cyan uppercase tracking-widest bg-cyan/10 px-3 py-1.5 rounded-full border border-cyan/20 mb-3 inline-block">Learn More</span>
                <h2 class="text-3xl sm:text-4xl font-black text-navy tracking-tight mb-2">Related Articles</h2>
                <p class="text-gray-600 font-normal text-lg">Read our latest guides and tips for the {{ $exam->exam_code }} exam.</p>
            </div>
            <a href="{{ route('blog.index') }}" class="inline-flex items-center justify-center space-x-2 bg-white hover:bg-navy hover:text-white text-navy font-black px-6 py-3.5 rounded-xl transition-all duration-300 border border-gray-200 shadow-sm group">
                <span class="uppercase tracking-widest text-xs">View all articles</span>
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($relatedBlogPosts as $post)
                <article class="bg-white rounded-[32px] overflow-hidden shadow-[0_5px_20px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] transition-all duration-500 border border-gray-100 flex flex-col group hover:-translate-y-2">
                    @if($post->featured_image)
                        <a href="{{ route('blog.show', $post->slug) }}" class="block shrink-0 overflow-hidden relative h-56">
                            <div class="absolute inset-0 bg-navy/20 group-hover:bg-transparent transition-colors z-10"></div>
                            <img src="{{ str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            
                            @if($post->category)
                                <div class="absolute top-4 left-4 z-20">
                                    <span class="text-[10px] font-black bg-white/90 backdrop-blur-md text-navy uppercase tracking-widest px-3 py-1.5 rounded-lg shadow-sm">{{ $post->category->name }}</span>
                                </div>
                            @endif
                        </a>
                    @endif
                    <div class="p-8 flex flex-col flex-1">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-[11px] font-black text-gray-400 uppercase tracking-widest flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ $post->published_at->format('M d, Y') }}
                            </span>
                        </div>
                        <a href="{{ route('blog.show', $post->slug) }}" class="block mb-4 flex-1">
                            <h3 class="text-xl font-black text-navy group-hover:text-cyan transition-colors leading-snug">{{ $post->title }}</h3>
                        </a>
                        <p class="text-sm text-gray-600 line-clamp-2 mb-6 font-normal leading-relaxed">{{ $post->excerpt }}</p>
                        <div class="mt-auto flex items-center justify-between pt-6 border-t border-gray-100">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan to-blue-500 flex items-center justify-center text-[11px] font-black text-white shadow-sm">{{ substr($post->user->name, 0, 1) }}</div>
                                <span class="text-xs font-black text-navy">{{ $post->user->name }}</span>
                            </div>
                            <span class="text-[11px] font-bold text-gray-400 flex items-center bg-gray-50 px-2 py-1 rounded">
                                <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $post->reading_time ?? 1 }} min
                            </span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Global Modal Container -->
<div x-data="{ demoModalOpen: false }" 
     x-on:open-demo-modal.window="demoModalOpen = true"
     x-show="demoModalOpen" 
     class="fixed inset-0 z-[100] overflow-y-auto flex items-center justify-center p-4 sm:p-6" 
     style="display: none;">
     
    <!-- Backdrop -->
    <div x-show="demoModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-navy/80 backdrop-blur-sm"
         x-on:click="demoModalOpen = false"></div>

    <!-- Modal Panel -->
    <div x-show="demoModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="bg-white rounded-[32px] shadow-[0_30px_60px_rgba(0,0,0,0.3)] text-navy max-w-lg w-full overflow-hidden relative z-10 border border-white/20">
        
        <!-- Header -->
        <div class="bg-navy p-8 relative overflow-hidden">
            <!-- Decorative bg -->
            <div class="absolute top-0 right-0 w-48 h-48 bg-cyan/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-500/20 rounded-full blur-2xl pointer-events-none"></div>
            
            <button x-on:click="demoModalOpen = false" class="absolute top-5 right-5 text-white/50 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-2.5 transition-colors focus:outline-none z-10">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>

            <div class="relative z-10">
                <span class="inline-block bg-cyan/20 text-cyan text-[10px] font-black px-3 py-1.5 rounded-md uppercase tracking-widest mb-4 border border-cyan/30">Free PDF Guide</span>
                <h3 class="text-3xl font-black text-white mb-2">Request Demo</h3>
                <p class="text-sm text-gray-300 font-medium leading-relaxed">Get a free sample guide containing the first 10 questions and verified answers for <strong class="text-white">{{ $exam->exam_code }}</strong>.</p>
            </div>
        </div>

        <div class="p-8">
            <form action="{{ url('/free-demo') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="exam_id" value="{{ $exam->id }}">
                
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-widest text-gray-500 mb-2.5">Your Full Name</label>
                    <input type="text" name="name" required placeholder="John Doe" class="w-full px-5 py-4 rounded-xl border-2 border-gray-100 text-sm focus:outline-none focus:border-cyan focus:ring-4 focus:ring-cyan/10 transition-all font-bold text-navy placeholder-gray-400 shadow-sm">
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase tracking-widest text-gray-500 mb-2.5">Email Address</label>
                    <input type="email" name="email" required placeholder="john@example.com" class="w-full px-5 py-4 rounded-xl border-2 border-gray-100 text-sm focus:outline-none focus:border-cyan focus:ring-4 focus:ring-cyan/10 transition-all font-bold text-navy placeholder-gray-400 shadow-sm">
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-gradient-to-r from-orange to-red-500 hover:from-red-500 hover:to-orange text-white font-black py-4.5 rounded-xl shadow-[0_10px_20px_rgba(255,107,53,0.25)] transition-all duration-300 transform active:scale-95 uppercase tracking-widest text-sm flex justify-center items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Send Free Demo Link</span>
                    </button>
                </div>
                
                <p class="text-center text-[10px] text-gray-400 font-bold mt-6 flex items-center justify-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <span>We never share your email. Secure 256-bit encryption.</span>
                </p>
            </form>
        </div>
    </div>
</div>

@endsection
