@extends('layouts.public')

@section('title', 'Order Complete - ExamsNinja')

@section('content')
<section class="py-16 bg-gray-50 min-h-[600px] flex items-center">
    <div class="max-w-md mx-auto px-4 sm:px-6 text-center">
        
        <div class="bg-white border border-gray-200 rounded-lg p-8 shadow-lg space-y-6">
            <!-- Icon -->
            <div class="h-20 w-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto text-4xl animate-bounce">
                ✓
            </div>
            
            <!-- Headline -->
            <div>
                <h1 class="text-2xl font-extrabold text-navy">Order Completed!</h1>
                <p class="text-sm text-gray-500 mt-2">Thank you for your purchase. Your payment was verified successfully.</p>
            </div>

            <!-- Detailed info -->
            <div class="bg-gray-50 rounded p-4 text-left border border-gray-150 space-y-2">
                <p class="text-xs text-gray-600 font-semibold">• Study Guides: Printable PDFs are ready for download.</p>
                <p class="text-xs text-gray-600 font-semibold">• Test Simulator: Timed practice sessions are unlocked.</p>
                <p class="text-xs text-gray-600 font-semibold">• Email: An order confirmation and invoice has been sent to your registered address.</p>
            </div>

            <!-- Action buttons -->
            <div class="space-y-3 pt-2">
                <a href="{{ route('dashboard.index') }}" 
                   class="block w-full bg-navy hover:bg-opacity-95 text-white font-extrabold py-3 rounded shadow transition text-sm">
                    Go to Student Portal
                </a>
                <a href="{{ route('dashboard.test-engine') }}" 
                   class="block w-full bg-cyan hover:bg-opacity-90 text-navy font-extrabold py-3 rounded shadow transition text-sm">
                    Launch Test Engine
                </a>
            </div>
        </div>

    </div>
</section>
@endsection
