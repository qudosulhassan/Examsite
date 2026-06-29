@extends('layouts.public')

@section('title', 'Order Cancelled - ExamsNinja')

@section('content')
<section class="py-16 bg-gray-50 min-h-[600px] flex items-center">
    <div class="max-w-md mx-auto px-4 sm:px-6 text-center">
        
        <div class="bg-white border border-gray-200 rounded-lg p-8 shadow-lg space-y-6">
            <!-- Icon -->
            <div class="h-20 w-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto text-4xl">
                ✕
            </div>
            
            <!-- Headline -->
            <div>
                <h1 class="text-2xl font-extrabold text-navy">Checkout Cancelled</h1>
                <p class="text-sm text-gray-500 mt-2">Your payment process was cancelled and no charges were made.</p>
            </div>

            <p class="text-xs text-gray-400">If you encountered issues, you can review your shopping cart items or try completing checkout again with a different payment method.</p>

            <!-- Action buttons -->
            <div class="space-y-3 pt-2">
                <a href="{{ route('cart') }}" 
                   class="block w-full bg-navy hover:bg-opacity-95 text-white font-extrabold py-3 rounded shadow transition text-sm">
                    Return to Shopping Cart
                </a>
                <a href="{{ route('vendors.index') }}" 
                   class="block w-full bg-cyan hover:bg-opacity-90 text-navy font-extrabold py-3 rounded shadow transition text-sm">
                    Browse Other Certifications
                </a>
            </div>
        </div>

    </div>
</section>
@endsection
