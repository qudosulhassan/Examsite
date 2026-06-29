<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Handle PayPal Webhook request.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $eventType = $payload['event_type'] ?? null;

        if (!$eventType) {
            return response()->json(['error' => 'Invalid webhook payload'], 400);
        }

        Log::info('PayPal Webhook received event: ' . $eventType);

        try {
            switch ($eventType) {
                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                    $resource = $payload['resource'] ?? [];
                    $paypalSubId = $resource['id'] ?? null;
                    $this->handleSubscriptionActivated($paypalSubId, $resource);
                    break;

                case 'BILLING.SUBSCRIPTION.CANCELLED':
                    $resource = $payload['resource'] ?? [];
                    $paypalSubId = $resource['id'] ?? null;
                    $this->handleSubscriptionCancelled($paypalSubId);
                    break;

                case 'PAYMENT.SALE.COMPLETED':
                    // This is triggered for recurring payment captures
                    $resource = $payload['resource'] ?? [];
                    $paypalSubId = $resource['billing_agreement_id'] ?? null;
                    $this->handleRecurringPaymentCompleted($paypalSubId, $resource);
                    break;

                case 'PAYMENT.SALE.DENIED':
                    $resource = $payload['resource'] ?? [];
                    $paypalSubId = $resource['billing_agreement_id'] ?? null;
                    $this->handleRecurringPaymentFailed($paypalSubId);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('PayPal webhook processing exception: ' . $e->getMessage());
            return response()->json(['error' => 'Processing error: ' . $e->getMessage()], 500);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle subscription activation.
     */
    protected function handleSubscriptionActivated(string $paypalSubId, array $resource)
    {
        $subscription = Subscription::where('paypal_subscription_id', $paypalSubId)->first();
        if ($subscription) {
            $subscription->update([
                'status' => 'active',
            ]);
            Log::info("PayPal Subscription {$paypalSubId} marked active in database.");
        }
    }

    /**
     * Handle subscription cancellation.
     */
    protected function handleSubscriptionCancelled(string $paypalSubId)
    {
        $subscription = Subscription::where('paypal_subscription_id', $paypalSubId)->first();
        if ($subscription) {
            $this->subscriptionService->cancelSubscription($subscription);
            Log::info("PayPal Subscription {$paypalSubId} cancelled via webhook.");
        }
    }

    /**
     * Handle successful recurring billing payment sale completion.
     */
    protected function handleRecurringPaymentCompleted(?string $paypalSubId, array $resource)
    {
        if (!$paypalSubId) {
            return;
        }

        $subscription = Subscription::where('paypal_subscription_id', $paypalSubId)->first();
        if ($subscription) {
            // PayPal subscription renew
            $newEnd = now()->addMonth(); // Default
            if (isset($resource['billing_agreement_details']['next_billing_date'])) {
                $newEnd = \Carbon\Carbon::parse($resource['billing_agreement_details']['next_billing_date']);
            }
            $this->subscriptionService->renewSubscription($subscription, $newEnd);
            Log::info("PayPal Subscription {$paypalSubId} renewed via webhook capture.");
        }
    }

    /**
     * Handle failed recurring billing payment sale.
     */
    protected function handleRecurringPaymentFailed(?string $paypalSubId)
    {
        if (!$paypalSubId) {
            return;
        }

        $subscription = Subscription::where('paypal_subscription_id', $paypalSubId)->first();
        if ($subscription) {
            $this->subscriptionService->handleFailedPayment($subscription);
            Log::warning("PayPal Subscription {$paypalSubId} renewal payment failed.");
        }
    }
}
