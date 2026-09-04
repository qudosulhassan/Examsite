@extends('layouts.public')

@section('title', 'Order Cancelled - Exam Topics Base')

@section('content')
<section class="py-20 bg-gray-50 min-h-[700px] flex items-center relative overflow-hidden">
    <!-- Background accents -->
    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-red-500/10 rounded-full mix-blend-multiply filter blur-[100px] pointer-events-none"></div>

    <div class="max-w-xl mx-auto px-4 sm:px-6 text-center relative z-10 w-full">
        
        <div class="bg-white border border-gray-100 rounded-3xl p-10 md:p-14 shadow-[0_20px_50px_rgba(0,0,0,0.05)] space-y-8 transform hover:-translate-y-1 transition-transform duration-500">
            <!-- Icon -->
            <div class="relative">
                <div class="absolute inset-0 bg-red-400 rounded-full blur-[20px] opacity-20"></div>
                <div class="h-28 w-28 bg-gradient-to-br from-red-400 to-red-600 text-white rounded-full flex items-center justify-center mx-auto text-5xl shadow-[0_10px_20px_rgba(248,113,113,0.3)] relative z-10">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>
            </div>
            
            <!-- Headline -->
            <div>
                <h1 class="text-3xl md:text-4xl font-black text-navy mb-4 tracking-tight">Checkout Cancelled</h1>
                <p class="text-lg text-gray-500 font-medium">Your payment process was cancelled and no charges were made.</p>
            </div>

            <div class="bg-red-50/50 rounded-2xl p-6 text-center border border-red-100">
                <p class="text-sm text-gray-600 font-medium leading-relaxed">If you encountered issues, you can review your shopping cart items or try completing checkout again with a different payment method.</p>
            </div>

            <!-- Action buttons -->
            <div class="flex flex-col gap-4 pt-4">
                <a href="{{ route('cart') }}" 
                   class="block w-full text-center bg-navy hover:bg-gradient-to-r hover:from-cyan hover:to-blue-500 text-white font-black py-4 px-6 rounded-xl shadow-lg hover:shadow-[0_10px_30px_rgba(0,212,170,0.3)] transition-all duration-300">
                    Return to Shopping Cart
                </a>
                <a href="{{ route('vendors.index') }}" 
                   class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-navy font-black py-4 px-6 rounded-xl shadow-sm border border-gray-200 transition-colors">
                    Browse Other Certifications
                </a>
            </div>
        </div>

    </div>
</section>
@endsection
