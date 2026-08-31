@extends('layouts.public')

@section('title', 'Secure Checkout - ExamsNinja')

@section('content')
<!-- Hero Section (Premium Deep Space) -->
<section class="bg-gradient-to-br from-[#07101E] via-navy to-[#0F172A] text-white pt-20 pb-32 relative overflow-hidden">
    <!-- Abstract glowing orbs -->
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-cyan rounded-full mix-blend-screen filter blur-[120px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[120px] opacity-20"></div>
    <!-- Abstract Tech Lines -->
    <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: linear-gradient(#ffffff 1px, transparent 1px), linear-gradient(90deg, #ffffff 1px, transparent 1px); background-size: 64px 64px;"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center space-x-2 bg-white/5 border border-white/10 rounded-full px-4 py-1.5 mb-6">
            <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            <span class="text-xs font-bold uppercase tracking-widest text-gray-300">256-bit SSL Encrypted</span>
        </div>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black tracking-tight mb-6 leading-tight">Secure <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan to-blue-500">Checkout</span></h1>
        <p class="text-lg text-gray-300 max-w-2xl mx-auto font-light leading-relaxed">
            Complete your details and choose your preferred payment option to get instant access to your materials.
        </p>
    </div>
</section>

<!-- Main Checkout Content -->
<section class="py-12 bg-gray-50 min-h-[600px]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Flash Messages -->
        @if(session('error'))
            <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-4 rounded shadow-sm">
                <p class="text-sm text-red-700 font-semibold">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 relative z-20 -mt-20">
            
            <!-- Left: Checkout Forms (66%) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Billing Details Card -->
                <div class="bg-white border border-gray-100 rounded-3xl p-8 shadow-[0_10px_40px_rgba(0,0,0,0.04)]">
                    <div class="flex items-center space-x-3 border-b border-gray-100 pb-5 mb-6">
                        <div class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center font-black text-sm">1</div>
                        <h3 class="text-xl font-black text-navy">Account Details</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Full Name</label>
                            <input type="text" id="billing_name" value="{{ auth()->user()->name }}" readonly
                                   class="w-full bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 px-4 py-3 cursor-not-allowed focus:ring-0">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Email Address</label>
                            <input type="email" id="billing_email" value="{{ auth()->user()->email }}" readonly
                                   class="w-full bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 px-4 py-3 cursor-not-allowed focus:ring-0">
                        </div>
                    </div>
                    <p class="text-[11px] font-bold text-gray-400 mt-4 flex items-center space-x-2">
                        <svg class="w-4 h-4 text-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>ExamsNinja accounts are bound to this email. Purchased guides are activated immediately on this account.</span>
                    </p>
                </div>

                <!-- Payment Option Selection Card -->
                <div class="bg-white border border-gray-100 rounded-3xl p-8 shadow-[0_10px_40px_rgba(0,0,0,0.04)]" x-data="{ paymentMethod: 'stripe' }">
                    <div class="flex items-center space-x-3 border-b border-gray-100 pb-5 mb-6">
                        <div class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center font-black text-sm">2</div>
                        <h3 class="text-xl font-black text-navy">Payment Method</h3>
                    </div>
                    
                    @if($total == 0)
                        <!-- Free Order Checkout -->
                        <div class="p-6 bg-cyan bg-opacity-10 border border-cyan border-opacity-35 rounded text-center">
                            <h4 class="font-bold text-navy mb-2">No Payment Required</h4>
                            <p class="text-xs text-gray-500 mb-6">Your order is 100% discounted. Click the button below to complete registration.</p>
                            <form action="{{ route('checkout.free') }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-navy hover:bg-opacity-95 text-white font-extrabold py-3 px-8 rounded shadow transition text-sm">
                                    Activate Order Instantly
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Payment Methods Toggle -->
                        <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-3 sm:space-y-0 mb-8">
                            <!-- Stripe Select -->
                            <label class="flex-1 flex items-center justify-between border rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition"
                                   :class="paymentMethod === 'stripe' ? 'border-cyan bg-cyan bg-opacity-5' : 'border-gray-200'">
                                <div class="flex items-center space-x-3">
                                    <input type="radio" name="payment_selector" value="stripe" x-model="paymentMethod" class="text-cyan focus:ring-cyan h-4 w-4">
                                    <span class="text-sm font-bold text-navy">Credit Card / Debit Card</span>
                                </div>
                                <span class="text-xs text-gray-400 font-bold uppercase">Stripe</span>
                            </label>
                            
                            <!-- PayPal Select -->
                            <label class="flex-1 flex items-center justify-between border rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition"
                                   :class="paymentMethod === 'paypal' ? 'border-cyan bg-cyan bg-opacity-5' : 'border-gray-200'">
                                <div class="flex items-center space-x-3">
                                    <input type="radio" name="payment_selector" value="paypal" x-model="paymentMethod" class="text-cyan focus:ring-cyan h-4 w-4">
                                    <span class="text-sm font-bold text-navy">PayPal Account</span>
                                </div>
                                <span class="text-xs text-gray-400 font-bold uppercase">PayPal</span>
                            </label>
                        </div>

                        <!-- Stripe Form Container -->
                        <div x-show="paymentMethod === 'stripe'" class="space-y-6">
                            @if(empty($stripeKey) || empty($stripeClientSecret))
                                <!-- Stripe Mock Testing Card -->
                                <div class="border border-dashed border-gray-300 rounded-lg p-6 bg-gray-50 text-center">
                                    <span class="text-2xl mb-2 block">💳</span>
                                    <h4 class="font-bold text-navy text-sm mb-1">Stripe Sandbox (Simulator Mode)</h4>
                                    <p class="text-xs text-gray-500 mb-6">No Stripe keys configured in .env. Use this simulator to check checkout handlers.</p>
                                    <a href="{{ route('checkout.success') }}?payment_intent=pi_mock_{{ Str::random(16) }}{{ $itemType === 'subscription' ? '&stripe_subscription_id=sub_mock_'.Str::random(16) : '' }}"
                                       class="inline-block bg-navy hover:bg-opacity-95 text-white text-xs font-bold py-3 px-8 rounded shadow transition">
                                        Simulate Successful Stripe Payment
                                    </a>
                                </div>
                            @else
                                <!-- Real Stripe Elements Elements -->
                                <form id="stripe-payment-form" class="space-y-4">
                                    <div class="p-4 bg-gray-50 border border-gray-200 rounded">
                                        <div id="stripe-card-element" class="p-1">
                                            <!-- Stripe Elements will inject here -->
                                        </div>
                                    </div>
                                    <div id="card-errors" class="text-xs text-red-500 font-semibold" role="alert"></div>

                                    <button type="submit" id="stripe-pay-button"
                                            class="w-full bg-navy text-white text-center font-extrabold py-3 rounded shadow hover:bg-opacity-95 transition text-sm">
                                        Pay Securely via Stripe (${{ number_format($total, 2) }})
                                    </button>
                                </form>
                            @endif
                        </div>

                        <!-- PayPal Form Container -->
                        <div x-show="paymentMethod === 'paypal'" class="space-y-4">
                            @if(config('services.paypal.client_id') === 'sandbox_client_id_placeholder')
                                <!-- PayPal Mock Testing Card -->
                                <div class="border border-dashed border-gray-300 rounded-lg p-6 bg-gray-50 text-center">
                                    <span class="text-2xl mb-2 block">💰</span>
                                    <h4 class="font-bold text-navy text-sm mb-1">PayPal Sandbox (Simulator Mode)</h4>
                                    <p class="text-xs text-gray-500 mb-6">No PayPal Client ID configured. Use this simulator to test redirects and callback processing.</p>
                                    
                                    @if($itemType === 'subscription')
                                        <a href="{{ route('checkout.success') }}?method=paypal_sub&subscription_id=sub_paypal_mock_{{ Str::random(16) }}"
                                           class="inline-block bg-yellow-400 hover:bg-yellow-500 text-navy text-xs font-extrabold py-3 px-8 rounded shadow transition">
                                            Simulate PayPal Subscription Checkout
                                        </a>
                                    @else
                                        <a href="{{ route('checkout.success') }}?method=paypal&payment_intent=pi_paypal_mock&token=paypal_mock_{{ Str::random(16) }}"
                                           class="inline-block bg-yellow-400 hover:bg-yellow-500 text-navy text-xs font-extrabold py-3 px-8 rounded shadow transition">
                                            Simulate PayPal One-Time Purchase
                                        </a>
                                    @endif
                                </div>
                            @else
                                <!-- PayPal SDK Button Renders here -->
                                <div class="p-4 bg-gray-50 border border-gray-200 rounded">
                                    <div id="paypal-button-container"></div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

            </div>

            <!-- Right: Order Summary Sidebar (33%) -->
            <div>
                <div class="bg-white border border-gray-100 rounded-3xl shadow-[0_10px_40px_rgba(0,0,0,0.04)] overflow-hidden">
                    <div class="px-8 py-6 bg-navy text-white relative overflow-hidden">
                        <!-- Abstract glowing orbs -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-cyan rounded-full mix-blend-screen filter blur-[50px] opacity-20"></div>
                        <h3 class="text-lg font-black relative z-10">Order Summary</h3>
                    </div>
                    
                    <div class="p-8">
                        <ul class="divide-y divide-gray-50 max-h-60 overflow-y-auto pr-2 mb-6">
                            @foreach($cart as $item)
                                <li class="py-4 flex justify-between items-center group">
                                    <div class="pr-4">
                                        <span class="font-black text-[13px] text-navy block group-hover:text-cyan transition-colors">{{ $item['name'] }}</span>
                                        <span class="text-gray-400 uppercase font-bold text-[9px]">{{ $item['type'] }} package</span>
                                    </div>
                                    <span class="font-black text-navy">${{ number_format($item['price'], 2) }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="border-t border-gray-100 pt-6 space-y-4 text-[13px] font-medium text-gray-600">
                            <div class="flex justify-between items-center">
                                <span>Subtotal</span>
                                <span class="font-bold text-navy">${{ number_format($subtotal, 2) }}</span>
                            </div>
                            @if($discount > 0)
                                <div class="flex justify-between items-center text-cyan font-bold">
                                    <span>Promo Discount</span>
                                    <span>-${{ number_format($discount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between items-center border-t border-gray-100 pt-6 mt-2">
                                <span class="text-lg font-black text-navy">Total</span>
                                <span class="text-3xl font-black text-navy">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <!-- Trust indicators -->
                        <div class="mt-8 pt-6 border-t border-gray-100 space-y-3">
                            <div class="flex justify-center space-x-2">
                                <div class="h-8 w-12 bg-gray-50 rounded border border-gray-200 flex items-center justify-center text-xs font-black text-gray-400">VISA</div>
                                <div class="h-8 w-12 bg-gray-50 rounded border border-gray-200 flex items-center justify-center text-xs font-black text-gray-400">MC</div>
                                <div class="h-8 w-12 bg-gray-50 rounded border border-gray-200 flex items-center justify-center text-xs font-black text-gray-400">AMEX</div>
                            </div>
                            <p class="text-[10px] text-gray-400 text-center font-bold">Payments processed securely.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- Stripe SDK & JS Setup -->
@if(!empty($stripeKey) && !empty($stripeClientSecret))
<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stripe = Stripe('{{ $stripeKey }}');
        const elements = stripe.elements();
        const cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '14px',
                    color: '#1e293b',
                    fontFamily: '"Inter", sans-serif',
                    '::placeholder': {
                        color: '#94a3b8',
                    },
                },
            }
        });
        cardElement.mount('#stripe-card-element');

        const form = document.getElementById('stripe-payment-form');
        const payButton = document.getElementById('stripe-pay-button');
        const errorDiv = document.getElementById('card-errors');

        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            payButton.disabled = true;
            payButton.innerText = "Processing Transaction...";
            errorDiv.textContent = "";

            const billingName = '{{ auth()->user()->name }}';
            const billingEmail = '{{ auth()->user()->email }}';

            @if($itemType === 'subscription')
                // Confirm Card Setup for subscription payment method assignment
                const { setupIntent, error } = await stripe.confirmCardSetup(
                    '{{ $stripeClientSecret }}', {
                        payment_method: {
                            card: cardElement,
                            billing_details: {
                                name: billingName,
                                email: billingEmail
                            }
                        }
                    }
                );

                if (error) {
                    errorDiv.textContent = error.message;
                    payButton.disabled = false;
                    payButton.innerText = "Pay Securely via Stripe";
                } else {
                    // Redirect to success endpoint passing subscription details
                    window.location.href = '{{ route("checkout.success") }}?payment_intent=' + setupIntent.id + '&stripe_subscription_id={{ $stripeSubscriptionId }}';
                }
            @else
                // One-time payment confirmation
                const { paymentIntent, error } = await stripe.confirmCardPayment(
                    '{{ $stripeClientSecret }}', {
                        payment_method: {
                            card: cardElement,
                            billing_details: {
                                name: billingName,
                                email: billingEmail
                            }
                        }
                    }
                );

                if (error) {
                    errorDiv.textContent = error.message;
                    payButton.disabled = false;
                    payButton.innerText = "Pay Securely via Stripe";
                } else {
                    window.location.href = '{{ route("checkout.success") }}?payment_intent=' + paymentIntent.id;
                }
            @endif
        });
    });
</script>
@endif

<!-- PayPal SDK & JS Setup -->
@if(config('services.paypal.client_id') !== 'sandbox_client_id_placeholder' && $total > 0)
    @if($itemType === 'subscription')
        <script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&vault=true&intent=subscription&currency=USD"></script>
    @else
        <script src="https://www.paypal.com/sdk/js?client-id={{ config('services.paypal.client_id') }}&currency=USD"></script>
    @endif
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = '{{ csrf_token() }}';

        paypal.Buttons({
            style: {
                layout: 'vertical',
                color:  'gold',
                shape:  'rect',
                label:  'paypal'
            },
            @if($itemType === 'subscription')
                // PayPal subscription creation
                createSubscription: function(data, actions) {
                    return fetch('{{ route("checkout.paypal.create-subscription") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            plan_id: '{{ $paypalPlanId }}'
                        })
                    }).then(res => {
                        if (!res.ok) throw new Error('Network error');
                        return res.json();
                    }).then(subData => {
                        return subData.id;
                    }).catch(err => {
                        alert('Could not initialize PayPal Subscription. Please try again.');
                    });
                },
                onApprove: function(data, actions) {
                    window.location.href = '{{ route("checkout.success") }}?method=paypal_sub&subscription_id=' + data.subscriptionID;
                }
            @else
                // One-time PayPal order creation
                createOrder: function(data, actions) {
                    return fetch('{{ route("checkout.paypal.create-order") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    }).then(res => {
                        if (!res.ok) throw new Error('Network error');
                        return res.json();
                    }).then(orderData => {
                        return orderData.id;
                    }).catch(err => {
                        alert('Could not create PayPal Order.');
                    });
                },
                onApprove: function(data, actions) {
                    return fetch('{{ route("checkout.paypal.capture-order") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            order_id: data.orderID
                        })
                    }).then(res => {
                        if (!res.ok) throw new Error('Capture error');
                        return res.json();
                    }).then(captureData => {
                        if (captureData.success) {
                            window.location.href = captureData.redirect_url;
                        } else {
                            alert('PayPal capturing failed. Contact support.');
                        }
                    }).catch(err => {
                        alert('Error processing PayPal payout.');
                    });
                }
            @endif
        }).render('#paypal-button-container');
    });
</script>
@endif
@endsection
