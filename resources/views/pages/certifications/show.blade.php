@extends('layouts.public')

@section('title', $certification->meta_title ?? $certification->name . ' Certification')
@section('description', $certification->meta_description ?? 'Study and practice for the ' . $certification->name . ' certification exams.')

@section('content')

<!-- Hero Section (Premium Deep Space) -->
<section class="bg-gradient-to-br from-[#07101E] via-navy to-[#0F172A] text-white pt-20 pb-24 relative overflow-hidden">
    <!-- Abstract glowing orbs -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-cyan rounded-full mix-blend-screen filter blur-[120px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[120px] opacity-20"></div>
    <!-- Abstract Tech Lines -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 64px 64px;"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <div class="mb-8">
            <x-breadcrumbs :links="[
                ['name' => 'Home', 'url' => '/'],
                ['name' => 'Certifications', 'url' => '/certifications'],
                ['name' => $certification->name, 'url' => '']
            ]" />
        </div>

        <div class="flex flex-col md:flex-row items-center justify-between gap-10">
            <div class="w-full md:w-2/3">
                <a href="{{ route('vendors.show', $certification->vendor->slug) }}" class="inline-flex items-center space-x-2 text-cyan font-black uppercase tracking-widest text-[11px] mb-4 bg-cyan/10 px-3 py-1.5 rounded-lg border border-cyan/20 hover:bg-cyan/20 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>{{ $certification->vendor->name }} Certifications</span>
                </a>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black tracking-tight mb-6 leading-tight">{{ $certification->name }}</h1>
                <p class="text-lg text-gray-300 leading-relaxed font-light max-w-2xl">
                    {{ $certification->description ?? 'Get the most up-to-date practice exams and study guides for the ' . $certification->name . ' certification.' }}
                </p>
            </div>
            @if($certification->vendor->image)
            <div class="w-full md:w-1/3 flex justify-start md:justify-end">
                <div class="bg-white/5 backdrop-blur-xl p-8 rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-white/10 transform rotate-2 hover:rotate-0 transition-transform duration-500">
                    <img src="{{ Storage::url($certification->vendor->image) }}" alt="{{ $certification->vendor->name }}" class="h-24 md:h-32 object-contain filter drop-shadow-[0_10px_20px_rgba(255,255,255,0.1)]">
                </div>
            </div>
            @endif
        </div>
    </div>
</section>

<!-- Exams Listing -->
<div class="py-16 bg-gray-50 relative z-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-10 gap-4">
            <h2 class="text-3xl font-black text-navy tracking-tight">Exams in this Certification</h2>
            <div class="bg-cyan/10 border border-cyan/20 flex items-center space-x-2 px-4 py-2 rounded-xl shadow-inner">
                <div class="w-2 h-2 rounded-full bg-cyan animate-pulse"></div>
                <span class="text-cyan font-black text-sm uppercase tracking-widest">{{ $certification->exams->count() }} Exams</span>
            </div>
        </div>

        @if($certification->exams->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($certification->exams as $exam)
                    <div class="bg-white rounded-[24px] shadow-[0_10px_30px_rgba(0,0,0,0.03)] hover:shadow-[0_20px_50px_rgba(0,212,170,0.1)] hover:border-cyan/30 hover:-translate-y-2 transition-all duration-500 border border-gray-100 overflow-hidden flex flex-col h-full group relative">
                        
                        <!-- Hover Glow -->
                        <div class="absolute inset-0 bg-gradient-to-br from-cyan/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cyan to-blue-500 transform origin-left scale-x-0 group-hover:scale-x-100 transition-transform duration-500"></div>

                        <div class="p-8 flex-grow relative z-10">
                            <div class="flex justify-between items-start mb-6">
                                <span class="bg-gray-50 text-navy px-3 py-1.5 rounded-lg text-[11px] font-black uppercase tracking-widest border border-gray-200 group-hover:border-cyan/30 group-hover:bg-cyan/10 group-hover:text-cyan transition-colors">{{ $exam->exam_code }}</span>
                                <span class="text-2xl font-black text-navy group-hover:text-cyan transition-colors">${{ $exam->price_pdf }}</span>
                            </div>
                            <h3 class="text-xl font-black text-navy mb-4 line-clamp-2 group-hover:text-cyan transition-colors leading-tight">
                                <a href="{{ route('exams.show', $exam->slug) }}" class="focus:outline-none focus:underline">{{ $exam->exam_name }}</a>
                            </h3>
                            <div class="flex items-center space-x-4 text-[11px] text-gray-400 font-bold uppercase tracking-widest mb-2">
                                <span class="flex items-center bg-gray-50 px-2.5 py-1.5 rounded-md border border-gray-100">
                                    <svg class="w-4 h-4 mr-1 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> 
                                    {{ $exam->questions_count ?? rand(50, 200) }} Q's
                                </span>
                            </div>
                        </div>
                        <div class="px-8 py-6 bg-white border-t border-gray-100 mt-auto relative z-10 flex justify-center">
                            <a href="{{ route('exams.show', $exam->slug) }}" class="w-full text-center bg-navy hover:bg-gradient-to-r hover:from-cyan hover:to-blue-500 text-white font-black text-[13px] uppercase tracking-widest py-3.5 rounded-xl transition-all duration-300 shadow-md group-hover:shadow-[0_5px_20px_rgba(0,212,170,0.3)]">
                                View Exam Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-16 text-center max-w-3xl mx-auto">
                <svg class="w-20 h-20 text-gray-200 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <h3 class="text-2xl font-black text-navy mb-3">No Exams Available</h3>
                <p class="text-gray-500 font-medium text-lg leading-relaxed">We are currently updating our database. Please check back later for new exams under this certification.</p>
            </div>
        @endif
    </div>
</div>

@endsection
