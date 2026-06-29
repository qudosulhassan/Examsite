<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\Coupon;
use App\Models\Setting;

class CartController extends Controller
{
    /**
     * Display the shopping cart page.
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        
        // Calculate subtotal
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'];
        }

        // Handle Coupon
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
                // Cap discount at subtotal
                $discount = min($discount, $subtotal);
            } else {
                // Invalid coupon, remove it
                session()->forget('cart_coupon');
            }
        }

        $total = max(0, $subtotal - $discount);

        return view('pages.cart', compact('cart', 'subtotal', 'coupon', 'discount', 'total'));
    }

    /**
     * Add an item to the cart.
     */
    public function add(Request $request)
    {
        // Resolve parameters with fallbacks to support existing Blade templates
        $type = $request->input('item_type', $request->input('type'));
        $examId = $request->input('exam_id');
        $planName = $request->input('plan_name');
        $billingCycle = $request->input('billing_cycle', $request->input('billing'));

        if ($type === 'pdf' || $type === 'engine_single') {
            if (empty($examId)) {
                return back()->with('error', 'Exam reference is required.');
            }

            $exam = Exam::findOrFail($examId);
            $cartKey = $type . '_' . $exam->id;

            // Check if user already owns this PDF guide or single engine
            if (auth()->check()) {
                $owns = auth()->user()->userExams()
                    ->where('exam_id', $exam->id)
                    ->where('access_type', $type === 'pdf' ? 'pdf' : 'engine')
                    ->exists();
                if ($owns) {
                    return back()->with('error', 'You already own this item. Check your student dashboard.');
                }
            }

            $cart = session()->get('cart', []);

            // Subscriptions and items don't mix in a single checkout.
            foreach ($cart as $key => $item) {
                if ($item['type'] === 'subscription') {
                    unset($cart[$key]);
                }
            }

            $price = $type === 'pdf' ? (float)$exam->price_pdf : (float)$exam->price_engine;
            $name = $type === 'pdf' 
                ? $exam->vendor->name . ' ' . $exam->exam_code . ' Study Guide (PDF)'
                : $exam->vendor->name . ' ' . $exam->exam_code . ' Test Engine Simulator';

            $cart[$cartKey] = [
                'id' => $exam->id,
                'key' => $cartKey,
                'name' => $name,
                'code' => $exam->exam_code,
                'type' => $type,
                'price' => $price,
            ];

            session()->put('cart', $cart);
            return redirect()->route('cart')->with('success', 'Item added to shopping cart.');

        } elseif ($type === 'subscription') {
            if (empty($planName) || empty($billingCycle)) {
                return back()->with('error', 'Subscription details are incomplete.');
            }

            $plansJson = Setting::get('subscription_plans');
            $plans = json_decode($plansJson, true) ?: [];
            
            $targetPlan = null;
            foreach ($plans as $plan) {
                if (strtolower($plan['name']) === strtolower($planName)) {
                    $targetPlan = $plan;
                    break;
                }
            }

            if (!$targetPlan) {
                return back()->with('error', 'Selected subscription plan is invalid.');
            }

            $price = $billingCycle === 'annual' ? $targetPlan['price_annual'] : $targetPlan['price_monthly'];
            $cartKey = 'sub_' . strtolower($targetPlan['name']) . '_' . $billingCycle;

            // Clear entire cart because subscription checkout is handled individually
            $cart = [];
            $cart[$cartKey] = [
                'name' => 'ExamsNinja ' . $targetPlan['name'] . ' Subscription (' . ucfirst($billingCycle) . ')',
                'key' => $cartKey,
                'plan_name' => $targetPlan['name'],
                'billing_cycle' => $billingCycle,
                'type' => 'subscription',
                'price' => (float)$price,
            ];

            session()->put('cart', $cart);
            return redirect()->route('cart')->with('success', 'Subscription plan added to cart.');
        }

        return back()->with('error', 'Invalid item type selected.');
    }

    /**
     * Add a package (bundle/subscription) to the cart.
     */
    public function addPackage(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);

        $package = \App\Models\Package::findOrFail($request->package_id);

        if (auth()->check()) {
            $owns = auth()->user()->userPackages()
                ->where('package_id', $package->id)
                ->where('status', 'active')
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->exists();
            
            if ($owns) {
                return back()->with('error', 'You already own this package. Check your dashboard.');
            }
        }

        $price = $package->type === 'subscription' ? $package->price_monthly : $package->price_lifetime;
        if (!$price || $price <= 0) {
            // fallback if monthly is null but annual is set? Let's just use monthly/lifetime as default
            if ($package->type === 'subscription' && !$price) $price = $package->price_annual;
        }

        $cartKey = 'pkg_' . $package->id;

        $cart = session()->get('cart', []);

        // Subscriptions and items don't mix well if we want single-item checkout for subs,
        // but for now we'll just clear it like the old subscription logic did for safety.
        $cart = [];

        $cart[$cartKey] = [
            'id' => $package->id,
            'key' => $cartKey,
            'name' => $package->name,
            'type' => 'package',
            'package_type' => $package->type, // 'subscription' or 'bundle'
            'price' => (float)$price,
        ];

        session()->put('cart', $cart);
        return redirect()->route('cart')->with('success', 'Package added to shopping cart.');
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$request->key])) {
            unset($cart[$request->key]);
            session()->put('cart', $cart);
            return redirect()->route('cart')->with('success', 'Item removed from shopping cart.');
        }

        return redirect()->route('cart')->with('error', 'Item not found in cart.');
    }

    /**
     * Apply coupon code.
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $coupon = Coupon::where('code', $request->code)->where('is_active', true)->first();

        if (!$coupon) {
            return back()->with('error', 'Invalid discount coupon code.');
        }

        $cart = session()->get('cart', []);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'];
        }

        if (!$coupon->isValid($subtotal)) {
            return back()->with('error', 'Coupon code cannot be applied to this order (expiry or minimum order total not met).');
        }

        session()->put('cart_coupon', $coupon->code);

        return redirect()->route('cart')->with('success', 'Discount coupon code "' . $coupon->code . '" applied successfully!');
    }

    /**
     * Remove applied coupon.
     */
    public function removeCoupon()
    {
        session()->forget('cart_coupon');
        return redirect()->route('cart')->with('success', 'Coupon code removed.');
    }
}
