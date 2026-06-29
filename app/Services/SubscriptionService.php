<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\SubscriptionStartedMail;
use App\Mail\SubscriptionRenewalReminderMail;
use App\Mail\SubscriptionCancelledMail;
use App\Mail\PaymentFailedMail;

class SubscriptionService
{
    /**
     * Start a subscription for a user.
     */
    public function startSubscription(
        User $user,
        string $planName,
        string $billingCycle,
        float $amount,
        string $paymentMethod,
        ?string $stripeSubscriptionId = null,
        ?string $paypalSubscriptionId = null,
        ?string $stripeCustomerId = null
    ): Subscription {
        // Cancel any existing active subscriptions first
        $user->subscriptions()->where('status', 'active')->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $periodEnd = $billingCycle === 'annual' ? now()->addYear() : now()->addMonth();

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_name' => $planName,
            'stripe_subscription_id' => $stripeSubscriptionId,
            'stripe_customer_id' => $stripeCustomerId,
            'paypal_subscription_id' => $paypalSubscriptionId,
            'status' => 'active',
            'billing_cycle' => $billingCycle,
            'amount' => $amount,
            'currency' => 'usd',
            'current_period_start' => now(),
            'current_period_end' => $periodEnd,
        ]);

        // Assign subscriber roles if Spatie permission role exists
        if ($user->hasRole('student')) {
            // Log role assignment/subscription status
        }

        ActivityLog::log(
            $user->id,
            'subscription_started',
            "Started subscription to " . ucfirst($planName) . " ($billingCycle) plan via " . ucfirst($paymentMethod) . "."
        );

        // Queue subscription started email
        try {
            Mail::to($user->email)->queue(new SubscriptionStartedMail($subscription));
        } catch (\Exception $e) {
            Log::error('Subscription started email dispatch failed: ' . $e->getMessage());
        }

        return $subscription;
    }

    /**
     * Renew an existing subscription.
     */
    public function renewSubscription(Subscription $subscription, ?Carbon $newPeriodEnd = null): Subscription
    {
        $interval = $subscription->billing_cycle === 'annual' ? 12 : 1;
        $nextEnd = $newPeriodEnd ?: Carbon::parse($subscription->current_period_end)->addMonths($interval);

        $subscription->update([
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => $nextEnd,
        ]);

        ActivityLog::log(
            $subscription->user_id,
            'subscription_renewed',
            "Renewed subscription to " . ucfirst($subscription->plan_name) . " plan."
        );

        // Queue subscription renewal reminder / confirmation email
        try {
            Mail::to($subscription->user->email)->queue(new SubscriptionRenewalReminderMail($subscription));
        } catch (\Exception $e) {
            Log::error('Subscription renewed email dispatch failed: ' . $e->getMessage());
        }

        return $subscription;
    }

    /**
     * Mark subscription as cancelled (usually pending until period end).
     */
    public function cancelSubscription(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        ActivityLog::log(
            $subscription->user_id,
            'subscription_cancelled',
            "Cancelled subscription to " . ucfirst($subscription->plan_name) . " plan."
        );

        // Queue subscription cancelled email
        try {
            Mail::to($subscription->user->email)->queue(new SubscriptionCancelledMail($subscription));
        } catch (\Exception $e) {
            Log::error('Subscription cancelled email dispatch failed: ' . $e->getMessage());
        }

        return $subscription;
    }

    /**
     * Handle a failed payment for subscription.
     */
    public function handleFailedPayment(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => 'past_due',
        ]);

        ActivityLog::log(
            $subscription->user_id,
            'subscription_payment_failed',
            "Subscription payment failed for " . ucfirst($subscription->plan_name) . " plan."
        );

        // Queue payment failed alert email
        try {
            Mail::to($subscription->user->email)->queue(new PaymentFailedMail($subscription));
        } catch (\Exception $e) {
            Log::error('Subscription payment failed email dispatch failed: ' . $e->getMessage());
        }

        return $subscription;
    }

    /**
     * Cancel via Stripe/PayPal integration service.
     */
    public function cancelProviderSubscription(Subscription $subscription): bool
    {
        if ($subscription->stripe_subscription_id) {
            $stripeService = new StripeService();
            if ($stripeService->cancelSubscription($subscription->stripe_subscription_id)) {
                $this->cancelSubscription($subscription);
                return true;
            }
        } elseif ($subscription->paypal_subscription_id) {
            $paypalService = new PayPalService();
            if ($paypalService->cancelSubscription($subscription->paypal_subscription_id)) {
                $this->cancelSubscription($subscription);
                return true;
            }
        }

        // Fallback cancellation for local/mock
        $this->cancelSubscription($subscription);
        return true;
    }
}
