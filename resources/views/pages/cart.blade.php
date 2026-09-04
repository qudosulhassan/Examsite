@extends('layouts.public')

@section('title', 'Shopping Cart - Exam Topics Base')

@section('content')
<!-- Hero Section (Premium Deep Space) -->
<section class="bg-gradient-to-br from-[#07101E] via-navy to-[#0F172A] text-white pt-20 pb-32 relative overflow-hidden">
    <!-- Abstract glowing orbs -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-cyan rounded-full mix-blend-screen filter blur-[120px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[120px] opacity-20"></div>
    <!-- Abstract Tech Lines -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 64px 64px;"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black tracking-tight mb-6 leading-tight">Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan to-blue-500">Cart</span></h1>
        <p class="text-lg text-gray-300 max-w-2xl mx-auto font-light leading-relaxed">
            Review your premium exam preparation items before completing checkout.
        </p>
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
            <div class="bg-white border border-gray-100 rounded-3xl p-16 text-center max-w-2xl mx-auto shadow-sm relative overflow-hidden group transform -translate-y-16 z-20">
                <div class="absolute inset-0 bg-gradient-to-br from-cyan/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                
                <div class="h-24 w-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-8 text-gray-400 border border-gray-100 shadow-inner group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 0a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-navy mb-3">Your cart is empty</h3>
                <p class="text-gray-500 mb-10 text-lg leading-relaxed">You haven't added any study guides or practice simulators yet.</p>
                <a href="{{ route('vendors.index') }}" class="inline-flex items-center justify-center space-x-2 bg-navy hover:bg-gradient-to-r hover:from-cyan hover:to-blue-500 text-white font-black py-4 px-10 rounded-xl transition-all duration-300 shadow-md hover:shadow-[0_10px_30px_rgba(0,212,170,0.3)]">
                    <span>Browse Certifications</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        @else
            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 relative z-20 -mt-20">
                
                <!-- Left: Item List (66%) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.04)] border border-gray-100 overflow-hidden">
                        <div class="border-b border-gray-100 bg-white px-8 py-6 flex justify-between items-center">
                            <h2 class="text-xl font-black text-navy">Items in Cart</h2>
                            <span class="bg-gray-100 text-navy px-3 py-1 rounded-lg text-xs font-black">{{ count($cart) }} Item(s)</span>
                        </div>
                        <ul class="divide-y divide-gray-50">
                            @foreach($cart as $key => $item)
                                <li class="px-8 py-8 flex flex-col sm:flex-row sm:items-center sm:justify-between hover:bg-gray-50/50 transition-colors group">
                                    <div class="flex items-start space-x-5 mb-4 sm:mb-0">
                                        <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 text-navy flex flex-col items-center justify-center font-bold shadow-sm p-1 group-hover:scale-105 group-hover:-rotate-3 transition-transform duration-300 shrink-0">
                                            @if($item['type'] === 'pdf')
                                                <span class="text-xs text-orange font-black">PDF</span>
                                            @elseif($item['type'] === 'engine_single')
                                                <span class="text-[10px] text-cyan font-black uppercase">SIM</span>
                                            @else
                                                <span class="text-[10px] text-cyan font-black uppercase">SUB</span>
                                            @endif
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-black text-navy group-hover:text-cyan transition-colors">{{ $item['name'] }}</h4>
                                            <p class="text-sm text-gray-500 mt-1 font-medium">
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
                                    <div class="flex items-center justify-between sm:justify-end sm:space-x-8 shrink-0">
                                        <span class="text-2xl font-black text-navy">${{ number_format($item['price'], 2) }}</span>
                                        <form action="{{ route('cart.remove') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="key" value="{{ $item['key'] }}">
                                            <button type="submit" class="text-sm font-bold text-gray-400 hover:text-red-500 bg-gray-50 hover:bg-red-50 px-3 py-2 rounded-lg transition-colors flex items-center space-x-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                <span class="hidden sm:inline">Remove</span>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Right: Summary & Coupon (33%) -->
                <div class="space-y-6">
                    
                    <!-- Cart Summary Totals -->
                    <div class="bg-white border border-gray-100 rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.04)] overflow-hidden">
                        <div class="px-8 py-6 bg-navy text-white relative overflow-hidden">
                            <!-- Abstract glowing orbs -->
                            <div class="absolute top-0 right-0 w-32 h-32 bg-cyan rounded-full mix-blend-screen filter blur-[50px] opacity-20"></div>
                            <h3 class="text-lg font-black relative z-10">Order Summary</h3>
                        </div>
                        
                        <div class="p-8 space-y-6">
                            
                            <!-- Coupon Block -->
                            <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 mb-2">
                                <h4 class="text-xs font-black text-navy uppercase tracking-widest mb-3">Promo Code</h4>
                                
                                @if($coupon)
                                    <div class="bg-cyan/10 border border-cyan/20 rounded-xl p-4 flex justify-between items-center">
                                        <div>
                                            <span class="font-black text-cyan text-lg">{{ $coupon->code }}</span>
                                            <p class="text-xs text-navy font-bold mt-1">
                                                {{ $coupon->discount_type === 'percentage' ? $coupon->discount_value.'%' : '$'.$coupon->discount_value }} off applied
                                            </p>
                                        </div>
                                        <form action="{{ route('cart.coupon.remove') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-gray-400 hover:text-red-500 bg-white hover:bg-red-50 p-2 rounded-lg transition-colors shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <form action="{{ route('cart.coupon') }}" method="POST" class="flex space-x-2">
                                        @csrf
                                        <input type="text" name="code" placeholder="Enter code" required
                                            class="flex-grow bg-white border border-gray-200 focus:border-cyan focus:ring-2 focus:ring-cyan/20 rounded-xl text-sm px-4 py-3 uppercase placeholder-gray-400 font-bold transition-all shadow-sm">
                                        <button type="submit" class="bg-navy text-white text-sm font-black px-5 py-3 rounded-xl hover:bg-cyan transition-colors shadow-md">
                                            Apply
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="space-y-4 text-[15px] font-medium text-gray-600">
                                <div class="flex justify-between items-center">
                                    <span>Subtotal</span>
                                    <span class="font-bold text-navy">${{ number_format($subtotal, 2) }}</span>
                                </div>
                                @if($discount > 0)
                                    <div class="flex justify-between items-center text-cyan font-bold">
                                        <span>Discount</span>
                                        <span>-${{ number_format($discount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between items-center border-t border-gray-100 pt-4 mt-2">
                                    <span class="text-lg font-black text-navy">Total</span>
                                    <span class="text-3xl font-black text-navy">${{ number_format($total, 2) }}</span>
                                </div>
                            </div>

                            @guest
                                <!-- Call to action if guest -->
                                <div class="pt-4 space-y-4">
                                    <a href="{{ route('login') }}?redirect=checkout" 
                                    class="block w-full bg-navy hover:bg-gradient-to-r hover:from-cyan hover:to-blue-500 text-white text-center font-black py-4 rounded-xl shadow-lg hover:shadow-[0_10px_30px_rgba(0,212,170,0.3)] transition-all duration-300">
                                        Login to Checkout
                                    </a>
                                    <p class="text-xs text-gray-500 text-center font-medium leading-relaxed bg-gray-50 p-3 rounded-lg border border-gray-100">
                                        Create an account to save your progress and access your exams from any device.
                                    </p>
                                </div>
                            @else
                                <!-- Proceed to Checkout -->
                                <div class="pt-4 space-y-4">
                                    <a href="{{ route('checkout') }}" 
                                    class="block w-full bg-navy hover:bg-gradient-to-r hover:from-cyan hover:to-blue-500 text-white text-center font-black py-4 rounded-xl shadow-lg hover:shadow-[0_10px_30px_rgba(0,212,170,0.3)] transition-all duration-300 flex items-center justify-center space-x-2 group">
                                        <span>Proceed to Checkout</span>
                                        <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </a>
                                </div>
                            @endguest
                            
                            <!-- Trust indicators -->
                            <div class="pt-4 border-t border-gray-100 space-y-3">
                                <div class="flex items-center justify-center space-x-2 text-xs font-bold text-gray-500 uppercase tracking-widest">
                                    <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    <span>Secure Checkout</span>
                                </div>
                                <div class="flex justify-center space-x-2">
                                    <div class="h-8 w-12 bg-gray-100 rounded border border-gray-200 flex items-center justify-center text-xs font-black text-gray-400">VISA</div>
                                    <div class="h-8 w-12 bg-gray-100 rounded border border-gray-200 flex items-center justify-center text-xs font-black text-gray-400">MC</div>
                                    <div class="h-8 w-12 bg-gray-100 rounded border border-gray-200 flex items-center justify-center text-xs font-black text-gray-400">AMEX</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        @endif

    </div>
</section>
@endsection
