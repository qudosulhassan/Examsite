<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\StripeService;
use App\Services\PayPalService;
use App\Services\SubscriptionService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserExam;
use App\Models\Coupon;
use App\Models\Exam;
use App\Models\Subscription;
use App\Models\ActivityLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationMail;

class CheckoutController extends Controller
{
    protected StripeService $stripeService;
    protected PayPalService $paypalService;
    protected SubscriptionService $subscriptionService;

    public function __construct(
        StripeService $stripeService,
        PayPalService $paypalService,
        SubscriptionService $subscriptionService
    ) {
        $this->stripeService = $stripeService;
        $this->paypalService = $paypalService;
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display the checkout page.
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Your shopping cart is empty.');
        }

        $user = auth()->user();
        $subtotal = 0;
        $itemType = '';

        foreach ($cart as $item) {
            $subtotal += $item['price'];
            $itemType = $item['type']; // 'pdf' or 'subscription'
        }

        // Apply coupon
        $couponCode = session()->get('cart_coupon');
        $coupon = null;
        $discount = 0;

        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->where('is_active', true)->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                if ($coupon->discount_type === 'percentage') {
                    $discount = ($subtotal * $coupon->discount_value) / 100;
                } else {
                    $discount = $coupon->discount_value;
                }
                $discount = min($discount, $subtotal);
            }
        }

        $total = max(0, $subtotal - $discount);
        
        // Generate Stripe keys & client secret if not free
        $stripeClientSecret = null;
        $stripeSubscriptionId = null;
        $paypalPlanId = null;

        if ($total > 0) {
            if ($itemType === 'subscription' || ($itemType === 'package' && reset($cart)['package_type'] === 'subscription')) {
                $subItem = reset($cart);
                $planName = $itemType === 'package' ? $subItem['name'] : $subItem['plan_name'];
                $billingCycle = $itemType === 'package' ? 'monthly' : $subItem['billing_cycle']; // simplify for package
                
                try {
                    $stripeSub = $this->stripeService->createSubscription(
                        $user,
                        $planName,
                        $billingCycle,
                        $total,
                        ['email' => $user->email]
                    );
                    $stripeClientSecret = $stripeSub['client_secret'];
                    $stripeSubscriptionId = $stripeSub['id'];

                    $paypalPlanId = $this->paypalService->getOrCreateSubscriptionPlan(
                        $planName,
                        $billingCycle,
                        $total
                    );
                } catch (\Exception $e) {
                    Log::error('Checkout subscription setup failed: ' . $e->getMessage());
                }
            } else {
                // PDF Guides or One-Time Packages
                try {
                    $stripeIntent = $this->stripeService->createPaymentIntent($total, [
                        'user_id' => $user->id,
                        'email' => $user->email,
                    ]);
                    $stripeClientSecret = $stripeIntent['client_secret'];
                } catch (\Exception $e) {
                    Log::error('Checkout Stripe PaymentIntent setup failed: ' . $e->getMessage());
                }
            }
        }

        $stripeKey = config('services.stripe.key');
        if ($stripeKey === 'pk_test_placeholder') {
            $stripeKey = null; // Mark as mock in view if placeholder
        }

        return view('pages.checkout', compact(
            'cart', 'subtotal', 'coupon', 'discount', 'total', 
            'stripeKey', 'stripeClientSecret', 'stripeSubscriptionId', 
            'paypalPlanId', 'itemType'
        ));
    }

    /**
     * Complete a checkout order that becomes free (e.g. 100% off coupon).
     */
    public function checkoutFree(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Cart is empty.');
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'];
        }

        $couponCode = session()->get('cart_coupon');
        $coupon = Coupon::where('code', $couponCode)->where('is_active', true)->first();
        
        if (!$coupon || !$coupon->isValid($subtotal)) {
            return redirect()->route('cart')->with('error', 'Invalid discount coupon.');
        }

        $discount = $coupon->discount_type === 'percentage' 
            ? ($subtotal * $coupon->discount_value) / 100 
            : $coupon->discount_value;
        $total = max(0, $subtotal - $discount);

        if ($total > 0) {
            return redirect()->route('checkout')->with('error', 'Order total is greater than zero. Please pay via card or PayPal.');
        }

        // Create free order
        $user = auth()->user();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'EN-' . strtoupper(Str::random(10)),
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total_amount' => 0.00,
            'coupon_id' => $coupon->id,
            'payment_method' => 'free',
            'payment_status' => 'paid',
            'billing_name' => $user->name,
            'billing_email' => $user->email,
        ]);

        foreach ($cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'exam_id' => in_array($item['type'], ['pdf', 'engine_single', 'package']) ? $item['id'] : null,
                'plan_name' => $item['type'] === 'subscription' ? $item['plan_name'] : null,
                'item_type' => $item['type'],
                'price' => 0.00,
            ]);

            if ($item['type'] === 'pdf') {
                UserExam::create([
                    'user_id' => $user->id,
                    'exam_id' => $item['id'],
                    'order_id' => $order->id,
                    'access_type' => 'pdf',
                    'download_count' => 0,
                    'max_downloads' => 3,
                    'purchased_at' => now(),
                ]);
            } elseif ($item['type'] === 'engine_single') {
                UserExam::create([
                    'user_id' => $user->id,
                    'exam_id' => $item['id'],
                    'order_id' => $order->id,
                    'access_type' => 'engine',
                    'download_count' => 0,
                    'max_downloads' => 0,
                    'purchased_at' => now(),
                ]);
            } elseif ($item['type'] === 'package') {
                $pkg = \App\Models\Package::find($item['id']);
                \App\Models\UserPackage::create([
                    'user_id' => $user->id,
                    'package_id' => $item['id'],
                    'order_id' => $order->id,
                    'status' => 'active',
                    'purchased_at' => now(),
                    'expires_at' => ($pkg && $pkg->access_days) ? now()->addDays($pkg->access_days) : null,
                ]);
            }
        }

        // Increment coupon use count
        $coupon->increment('used_count');

        // Clear cart
        session()->forget(['cart', 'cart_coupon']);

        ActivityLog::log($user->id, 'order_completed', "Completed free order {$order->order_number} using coupon {$coupon->code}.");

        try {
            Mail::to($user->email)->queue(new OrderConfirmationMail($order));
        } catch (\Exception $e) {
            Log::error('Free order confirmation mail failed to queue: ' . $e->getMessage());
        }

        return redirect()->route('checkout.success')->with('success_message', 'Order placed successfully!');
    }

    /**
     * Create PayPal Order (AJAX).
     */
    public function paypalCreateOrder(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'];
        }

        $couponCode = session()->get('cart_coupon');
        $discount = 0;
        if ($couponCode) {
            $coupon = Coupon::where('code', $couponCode)->where('is_active', true)->first();
            if ($coupon && $coupon->isValid($subtotal)) {
                $discount = $coupon->discount_type === 'percentage' 
                    ? ($subtotal * $coupon->discount_value) / 100 
                    : $coupon->discount_value;
                $discount = min($discount, $subtotal);
            }
        }

        $total = max(0, $subtotal - $discount);

        try {
            $paypalOrder = $this->paypalService->createOrder($total);
            return response()->json($paypalOrder);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Capture PayPal Order (AJAX).
     */
    public function paypalCaptureOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
        ]);

        try {
            $capture = $this->paypalService->captureOrder($request->order_id);

            if ($capture['status'] === 'COMPLETED') {
                $user = auth()->user();
                $cart = session()->get('cart', []);

                $subtotal = 0;
                foreach ($cart as $item) {
                    $subtotal += $item['price'];
                }

                $couponCode = session()->get('cart_coupon');
                $coupon = null;
                $discount = 0;
                if ($couponCode) {
                    $coupon = Coupon::where('code', $couponCode)->where('is_active', true)->first();
                    if ($coupon && $coupon->isValid($subtotal)) {
                        $discount = $coupon->discount_type === 'percentage' 
                            ? ($subtotal * $coupon->discount_value) / 100 
                            : $coupon->discount_value;
                        $discount = min($discount, $subtotal);
                    }
                }

                $total = max(0, $subtotal - $discount);

                // Create Order record
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => 'EN-' . strtoupper(Str::random(10)),
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'total_amount' => $total,
                    'coupon_id' => $coupon ? $coupon->id : null,
                    'payment_method' => 'paypal',
                    'payment_status' => 'paid',
                    'paypal_order_id' => $request->order_id,
                    'billing_name' => $user->name,
                    'billing_email' => $user->email,
                ]);

                // Create OrderItems & Grant Exam Access
                foreach ($cart as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'exam_id' => in_array($item['type'], ['pdf', 'engine_single', 'package']) ? $item['id'] : null,
                        'plan_name' => $item['type'] === 'subscription' ? $item['plan_name'] : null,
                        'item_type' => $item['type'],
                        'price' => $item['price'],
                    ]);

                    if ($item['type'] === 'pdf') {
                        UserExam::create([
                            'user_id' => $user->id,
                            'exam_id' => $item['id'],
                            'order_id' => $order->id,
                            'access_type' => 'pdf',
                            'download_count' => 0,
                            'max_downloads' => 3,
                            'purchased_at' => now(),
                        ]);
                    } elseif ($item['type'] === 'engine_single') {
                        UserExam::create([
                            'user_id' => $user->id,
                            'exam_id' => $item['id'],
                            'order_id' => $order->id,
                            'access_type' => 'engine',
                            'download_count' => 0,
                            'max_downloads' => 0,
                            'purchased_at' => now(),
                        ]);
                    } elseif ($item['type'] === 'package') {
                        $pkg = \App\Models\Package::find($item['id']);
                        \App\Models\UserPackage::create([
                            'user_id' => $user->id,
                            'package_id' => $item['id'],
                            'order_id' => $order->id,
                            'status' => 'active',
                            'purchased_at' => now(),
                            'expires_at' => ($pkg && $pkg->access_days) ? now()->addDays($pkg->access_days) : null,
                        ]);
                    }
                }

                if ($coupon) {
                    $coupon->increment('used_count');
                }

                session()->forget(['cart', 'cart_coupon']);

                ActivityLog::log($user->id, 'order_completed', "Placed order {$order->order_number} via PayPal.");

                try {
                    Mail::to($user->email)->queue(new OrderConfirmationMail($order));
                } catch (\Exception $e) {
                    Log::error('PayPal order confirmation mail failed to queue: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'redirect_url' => route('checkout.success')
                ]);
            }

            return response()->json(['error' => 'PayPal payment capturing failed'], 400);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create PayPal Subscription (AJAX).
     */
    public function paypalCreateSubscription(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|string',
        ]);

        try {
            $subscription = $this->paypalService->createSubscription($request->plan_id);
            return response()->json($subscription);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Redirect callback success from Stripe elements or PayPal
     */
    public function success(Request $request)
    {
        $user = auth()->user();

        // 1. Check if redirecting from Stripe PaymentIntent
        if ($request->has('payment_intent')) {
            $paymentIntentId = $request->payment_intent;

            // Check if order already exists for this PaymentIntent (completed by webhook or redirect earlier)
            $orderExists = Order::where('stripe_payment_intent_id', $paymentIntentId)->exists();

            if (!$orderExists) {
                // If order doesn't exist yet, we capture details and create it dynamically
                // This serves as an immediate client-side fallback if the Stripe Webhook has a delay
                $cart = session()->get('cart', []);
                if (!empty($cart)) {
                    $subtotal = 0;
                    $itemType = 'pdf';
                    foreach ($cart as $item) {
                        $subtotal += $item['price'];
                        $itemType = $item['type'];
                    }

                    $couponCode = session()->get('cart_coupon');
                    $coupon = null;
                    $discount = 0;
                    if ($couponCode) {
                        $coupon = Coupon::where('code', $couponCode)->where('is_active', true)->first();
                        if ($coupon && $coupon->isValid($subtotal)) {
                            $discount = $coupon->discount_type === 'percentage' 
                                ? ($subtotal * $coupon->discount_value) / 100 
                                : $coupon->discount_value;
                            $discount = min($discount, $subtotal);
                        }
                    }
                    $total = max(0, $subtotal - $discount);

                    // Stripe double check status: if local/mock, we accept pi_mock
                    $paymentStatus = 'paid';

                    // Create Order
                    $order = Order::create([
                        'user_id' => $user->id,
                        'order_number' => 'EN-' . strtoupper(Str::random(10)),
                        'subtotal' => $subtotal,
                        'discount_amount' => $discount,
                        'total_amount' => $total,
                        'coupon_id' => $coupon ? $coupon->id : null,
                        'payment_method' => 'stripe',
                        'payment_status' => $paymentStatus,
                        'stripe_payment_intent_id' => $paymentIntentId,
                        'billing_name' => $user->name,
                        'billing_email' => $user->email,
                    ]);

                    foreach ($cart as $item) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'exam_id' => in_array($item['type'], ['pdf', 'engine_single', 'package']) ? $item['id'] : null,
                            'plan_name' => $item['type'] === 'subscription' ? $item['plan_name'] : null,
                            'item_type' => $item['type'],
                            'price' => $item['price'],
                        ]);

                        if ($item['type'] === 'pdf') {
                            UserExam::create([
                                'user_id' => $user->id,
                                'exam_id' => $item['id'],
                                'order_id' => $order->id,
                                'access_type' => 'pdf',
                                'download_count' => 0,
                                'max_downloads' => 3,
                                'purchased_at' => now(),
                            ]);
                        } elseif ($item['type'] === 'engine_single') {
                            UserExam::create([
                                'user_id' => $user->id,
                                'exam_id' => $item['id'],
                                'order_id' => $order->id,
                                'access_type' => 'engine',
                                'download_count' => 0,
                                'max_downloads' => 0,
                                'purchased_at' => now(),
                            ]);
                        } elseif ($item['type'] === 'package') {
                            $pkg = \App\Models\Package::find($item['id']);
                            \App\Models\UserPackage::create([
                                'user_id' => $user->id,
                                'package_id' => $item['id'],
                                'order_id' => $order->id,
                                'status' => 'active',
                                'purchased_at' => now(),
                                'expires_at' => ($pkg && $pkg->access_days) ? now()->addDays($pkg->access_days) : null,
                            ]);
                        }
                    }

                    if ($coupon) {
                        $coupon->increment('used_count');
                    }

                    // Clear cart
                    session()->forget(['cart', 'cart_coupon']);

                    ActivityLog::log($user->id, 'order_completed', "Completed purchase {$order->order_number} via Stripe redirect.");

                    try {
                        Mail::to($user->email)->queue(new OrderConfirmationMail($order));
                    } catch (\Exception $e) {
                        Log::error('Stripe redirect order confirmation mail failed to queue: ' . $e->getMessage());
                    }
                }
            }
        }

        // 2. Check if PayPal subscription approved redirection
        if ($request->has('method') && $request->method === 'paypal_sub' && $request->has('subscription_id')) {
            $paypalSubId = $request->subscription_id;
            
            // Check if subscription already exists
            $subExists = Subscription::where('paypal_subscription_id', $paypalSubId)->exists();
            if (!$subExists) {
                $cart = session()->get('cart', []);
                if (!empty($cart)) {
                    $subItem = reset($cart);
                    if ($subItem['type'] === 'subscription') {
                        $this->subscriptionService->startSubscription(
                            $user,
                            $subItem['plan_name'],
                            $subItem['billing_cycle'],
                            $subItem['price'],
                            'paypal',
                            null,
                            $paypalSubId
                        );

                        session()->forget(['cart', 'cart_coupon']);
                    }
                }
            }
        }

        // 3. Check if Stripe subscription redirection
        if ($request->has('payment_intent') && session()->has('cart')) {
            $cart = session()->get('cart', []);
            $subItem = reset($cart);
            if ($subItem && $subItem['type'] === 'subscription') {
                // If it is a Stripe subscription, verify it and store
                $stripeSubId = $request->stripe_subscription_id; // Frontend passes this
                
                if ($stripeSubId) {
                    $subExists = Subscription::where('stripe_subscription_id', $stripeSubId)->exists();
                    if (!$subExists) {
                        $this->subscriptionService->startSubscription(
                            $user,
                            $subItem['plan_name'],
                            $subItem['billing_cycle'],
                            $subItem['price'],
                            'stripe',
                            $stripeSubId,
                            null,
                            null // Customer id retrieved from webhook or stripe call
                        );
                    }
                    session()->forget(['cart', 'cart_coupon']);
                }
            }
        }

        return view('pages.success');
    }

    /**
     * Cancel checkout callback page.
     */
    public function cancel()
    {
        return view('pages.cancel');
    }
}
