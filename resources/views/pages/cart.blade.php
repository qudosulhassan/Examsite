@extends('layouts.public')

@section('title', 'Shopping Cart - ExamsNinja')

@section('content')
<!-- Header Banner -->
<section class="bg-navy text-white py-12 text-center border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold tracking-tight mb-2">Your Shopping Cart</h1>
        <p class="text-sm text-gray-400">Review your exam preparation items before completing checkout.</p>
    </div>
</section>

<!-- Main Cart Page Content -->
<section class="py-12 bg-gray-50 min-h-[500px]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-8 bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <span class="text-green-500 font-bold">✓</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-semibold">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <span class="text-red-500 font-bold">⚠</span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-semibold">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(empty($cart))
            <!-- Empty Cart State -->
            <div class="bg-white border border-gray-200 rounded-lg p-12 text-center max-w-xl mx-auto shadow-sm">
                <div class="h-20 w-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-400 text-3xl font-bold">
                    🛒
                </div>
                <h3 class="text-xl font-bold text-navy mb-2">Your cart is currently empty</h3>
                <p class="text-sm text-gray-500 mb-8">You haven't added any study guides or practice simulators yet.</p>
                <a href="{{ route('vendors.index') }}" class="inline-block bg-cyan hover:bg-opacity-90 text-navy font-extrabold py-3 px-8 rounded shadow transition text-sm">
                    Browse Exam Providers
                </a>
            </div>
        @else
            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left: Item List (66%) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex justify-between items-center">
                            <h2 class="text-base font-bold text-navy">Items Summary</h2>
                            <span class="text-xs text-gray-500 font-semibold">{{ count($cart) }} Item(s)</span>
                        </div>
                        <ul class="divide-y divide-gray-150">
                            @foreach($cart as $key => $item)
                                <li class="px-6 py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between hover:bg-gray-50 transition">
                                    <div class="flex items-start space-x-4 mb-4 sm:mb-0">
                                        <div class="h-12 w-12 rounded bg-navy text-white flex flex-col items-center justify-center font-bold shadow-sm p-1">
                                            @if($item['type'] === 'pdf')
                                                <span class="text-xs text-orange font-extrabold">PDF</span>
                                            @elseif($item['type'] === 'engine_single')
                                                <span class="text-[9px] text-cyan font-extrabold uppercase">SIM</span>
                                            @else
                                                <span class="text-[9px] text-cyan font-extrabold uppercase">SUB</span>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="text-base font-bold text-navy">{{ $item['name'] }}</h4>
                                            <p class="text-xs text-gray-400 mt-0.5">
                                                @if($item['type'] === 'pdf')
                                                    Printable questions study guide with verified answers.
                                                @elseif($item['type'] === 'engine_single')
                                                    Web simulator access (timed/practice mode) for single exam.
                                                @else
                                                    Unlimited simulator access to all certification guides.
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between sm:justify-end sm:space-x-8">
                                        <span class="text-lg font-bold text-navy">${{ number_format($item['price'], 2) }}</span>
                                        <form action="{{ route('cart.remove') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                                            <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-700 transition">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="flex justify-between items-center px-2">
                        <a href="{{ route('vendors.index') }}" class="text-sm font-bold text-navy hover:text-cyan transition flex items-center">
                            ← Continue Shopping
                        </a>
                    </div>
                </div>

                <!-- Right: Summary & Coupon (33%) -->
                <div class="space-y-6">
                    
                    <!-- Coupon Block -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-4">
                        <h3 class="text-sm font-bold text-navy">Have a Promo Coupon?</h3>
                        
                        @if($coupon)
                            <div class="bg-cyan bg-opacity-10 border border-cyan border-opacity-35 rounded p-3 flex justify-between items-center text-xs">
                                <div>
                                    <span class="font-bold text-navy">{{ $coupon->code }}</span>
                                    <span class="text-gray-500 ml-1">
                                        ({{ $coupon->discount_type === 'percentage' ? $coupon->discount_value.'%' : '$'.$coupon->discount_value }} off)
                                    </span>
                                </div>
                                <form action="{{ route('cart.coupon.remove') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-red-500 font-bold hover:text-red-700 transition">Remove</button>
                                </form>
                            </div>
                        @else
                            <form action="{{ route('cart.coupon') }}" method="POST" class="flex space-x-2">
                                @csrf
                                <input type="text" name="code" placeholder="Enter coupon" required
                                       class="flex-grow border-gray-350 focus:border-cyan focus:ring-cyan rounded text-sm px-3 py-2 uppercase placeholder-gray-400">
                                <button type="submit" class="bg-navy text-white text-xs font-bold px-4 py-2 rounded hover:bg-opacity-95 transition">
                                    Apply
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- Cart Summary Totals -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-6">
                        <h3 class="text-sm font-bold text-navy border-b border-gray-150 pb-3">Checkout Order Summary</h3>
                        
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-gray-500">
                                <span>Cart Subtotal</span>
                                <span>${{ number_format($subtotal, 2) }}</span>
                            </div>
                            @if($discount > 0)
                                <div class="flex justify-between text-green-600 font-medium">
                                    <span>Discount (Coupon)</span>
                                    <span>-${{ number_format($discount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-gray-500">
                                <span>Tax / VAT</span>
                                <span>$0.00</span>
                            </div>
                            <div class="border-t border-gray-150 pt-4 flex justify-between items-baseline">
                                <span class="text-base font-bold text-navy">Grand Total</span>
                                <span class="text-2xl font-extrabold text-navy">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        @guest
                            <!-- Call to action if guest -->
                            <div class="space-y-4">
                                <a href="{{ route('login') }}?redirect=checkout" 
                                   class="block w-full bg-cyan hover:bg-opacity-90 text-navy text-center font-extrabold py-3 rounded shadow transition text-sm">
                                    Login to Proceed
                                </a>
                                <p class="text-[10px] text-gray-400 text-center">You must have an account to access purchased cert preps in the dashboard.</p>
                            </div>
                        @else
                            <!-- Proceed to Checkout -->
                            <a href="{{ route('checkout') }}" 
                               class="block w-full bg-cyan hover:bg-opacity-90 text-navy text-center font-extrabold py-3 rounded shadow transition text-sm">
                                Proceed to Checkout
                            </a>
                        @endguest
                    </div>

                    <!-- Trust indicators -->
                    <div class="text-center space-y-2 py-2">
                        <p class="text-[11px] text-gray-400 font-semibold flex justify-center items-center">
                            🔒 256-bit SSL Encrypted Transaction
                        </p>
                        <p class="text-[10px] text-gray-400">Hostinger Sandbox Checkout System. 100% Secure.</p>
                    </div>

                </div>

            </div>
        @endif

    </div>
</section>
@endsection
