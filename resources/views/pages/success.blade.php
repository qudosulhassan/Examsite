@extends('layouts.public')

@section('title', 'Order Complete - ExamsNinja')

@section('content')
<section class="py-20 bg-gray-50 min-h-[700px] flex items-center relative overflow-hidden">
    <!-- Background accents -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-cyan/10 rounded-full mix-blend-multiply filter blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-blue-500/10 rounded-full mix-blend-multiply filter blur-[100px] pointer-events-none"></div>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center relative z-10 w-full">
        
        <div class="bg-white border border-gray-100 rounded-3xl p-10 md:p-14 shadow-[0_20px_50px_rgba(0,0,0,0.05)] space-y-8 transform hover:-translate-y-1 transition-transform duration-500">
            <!-- Icon -->
            <div class="relative">
                <div class="absolute inset-0 bg-green-400 rounded-full blur-[20px] opacity-20 animate-pulse"></div>
                <div class="h-28 w-28 bg-gradient-to-br from-green-400 to-green-600 text-white rounded-full flex items-center justify-center mx-auto text-5xl shadow-[0_10px_20px_rgba(74,222,128,0.3)] relative z-10 animate-bounce">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
            </div>
            
            <!-- Headline -->
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-navy mb-4 tracking-tight">Order Completed!</h1>
                <p class="text-lg text-gray-500 font-medium">Thank you for your purchase. Your payment was verified successfully.</p>
            </div>

            <!-- Detailed info -->
            <div class="bg-gray-50/80 rounded-2xl p-6 text-left border border-gray-100 space-y-4">
                <div class="flex items-start space-x-3">
                    <div class="mt-1 bg-white p-1 rounded border border-gray-200 text-cyan"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    <p class="text-[13px] text-navy font-bold leading-relaxed">Study Guides: Printable PDFs are ready for download.</p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="mt-1 bg-white p-1 rounded border border-gray-200 text-cyan"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    <p class="text-[13px] text-navy font-bold leading-relaxed">Test Simulator: Timed practice sessions are unlocked.</p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="mt-1 bg-white p-1 rounded border border-gray-200 text-cyan"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    <p class="text-[13px] text-navy font-bold leading-relaxed">Email: An order confirmation and invoice has been sent to your registered address.</p>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                <a href="{{ route('dashboard.index') }}" 
                   class="flex-1 text-center bg-navy hover:bg-gradient-to-r hover:from-cyan hover:to-blue-500 text-white font-black py-4 px-6 rounded-xl shadow-lg hover:shadow-[0_10px_30px_rgba(0,212,170,0.3)] transition-all duration-300">
                    Go to Student Portal
                </a>
                <a href="{{ route('dashboard.test-engine') }}" 
                   class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-navy font-black py-4 px-6 rounded-xl shadow-sm border border-gray-200 transition-colors">
                    Launch Test Engine
                </a>
            </div>
        </div>

    </div>
</section>
@endsection
