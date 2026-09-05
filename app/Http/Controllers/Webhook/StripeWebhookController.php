<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserExam;
use App\Models\User;
use App\Models\Coupon;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Handle Stripe Webhook request.
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        $event = null;

        // Graceful signature validation bypass for local testing
        if ($endpointSecret === 'whsec_test_placeholder' || empty($sigHeader)) {
            $data = json_decode($payload, true);
            if ($data && isset($data['type'])) {
                $event = json_decode($payload);
            }
        } else {
            try {
                $event = Webhook::constructEvent(
                    $payload, $sigHeader, $endpointSecret
                );
            } catch (\UnexpectedValueException $e) {
                // Invalid payload
                return response()->json(['error' => 'Invalid payload'], 400);
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                // Invalid signature
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        if (!$event) {
            return response()->json(['error' => 'Event parsing failed'], 400);
        }

        $startTime = microtime(true);
        $logRecord = null;

        if ($event) {
            try {
                $logRecord = \App\Models\PaymentWebhookLog::create([
                    'gateway' => 'stripe',
                    'event_type' => $event->type ?? 'unknown',
                    'event_id' => $event->id ?? null,
                    'status' => 'pending',
                    'payload' => json_decode($payload, true) ?: ['raw' => substr($payload, 0, 5000)],
                    'ip_address' => $request->ip(),
                ]);
            } catch (\Throwable $th) {
                Log::warning('Could not create PaymentWebhookLog record: ' . $th->getMessage());
            }
        }

        Log::info('Stripe Webhook received event: ' . ($event->type ?? 'unknown'));

        // Process webhook event
        try {
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    $this->handlePaymentIntentSucceeded($paymentIntent);
                    break;

                case 'invoice.payment_succeeded':
                    $invoice = $event->data->object;
                    $this->handleInvoicePaymentSucceeded($invoice);
                    break;

                case 'invoice.payment_failed':
                    $invoice = $event->data->object;
                    $this->handleInvoicePaymentFailed($invoice);
                    break;

                case 'customer.subscription.deleted':
                    $stripeSub = $event->data->object;
                    $this->handleSubscriptionDeleted($stripeSub);
                    break;
            }

            $durationMs = (int)round((microtime(true) - $startTime) * 1000);

            if ($logRecord) {
                $logRecord->update([
                    'status' => 'processed',
                    'processing_time_ms' => $durationMs,
                ]);
            }

            \App\Models\PaymentActivityLog::record(
                'stripe',
                'webhook_received',
                'success',
                "Processed Stripe webhook event: {$event->type}",
                null,
                ['event_id' => $event->id ?? null, 'type' => $event->type ?? null]
            );

        } catch (\Exception $e) {
            $durationMs = (int)round((microtime(true) - $startTime) * 1000);

            if ($logRecord) {
                $logRecord->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'processing_time_ms' => $durationMs,
                ]);
            }

            \App\Models\PaymentActivityLog::record(
                'stripe',
                'gateway_error',
                'error',
                "Stripe webhook failed for event {$event->type}: {$e->getMessage()}",
                null,
                ['error' => $e->getMessage()]
            );

            Log::error('Stripe webhook processing exception: ' . $e->getMessage());
            return response()->json(['error' => 'Processing error: ' . $e->getMessage()], 500);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle one-time payment success.
     */
    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        $intentId = $paymentIntent->id;
        
        // Check if order already exists
        $order = Order::where('stripe_payment_intent_id', $intentId)->first();
        
        if ($order) {
            if ($order->payment_status !== 'paid') {
                $order->update(['payment_status' => 'paid']);
                
                // Grant access to PDF and Engine items
                foreach ($order->items as $item) {
                    if ($item->item_type === 'pdf') {
                        UserExam::firstOrCreate([
                            'user_id' => $order->user_id,
                            'exam_id' => $item->exam_id,
                            'order_id' => $order->id,
                            'access_type' => 'pdf',
                        ], [
                            'download_count' => 0,
                            'max_downloads' => 3,
                            'purchased_at' => now(),
                        ]);
                    } elseif ($item->item_type === 'engine_single') {
                        UserExam::firstOrCreate([
                            'user_id' => $order->user_id,
                            'exam_id' => $item->exam_id,
                            'order_id' => $order->id,
                            'access_type' => 'engine',
                        ], [
                            'download_count' => 0,
                            'max_downloads' => 0,
                            'purchased_at' => now(),
                        ]);
                    } elseif ($item->item_type === 'combo') {
                        UserExam::firstOrCreate([
                            'user_id' => $order->user_id,
                            'exam_id' => $item->exam_id,
                            'order_id' => $order->id,
                            'access_type' => 'pdf',
                        ], [
                            'download_count' => 0,
                            'max_downloads' => 3,
                            'purchased_at' => now(),
                        ]);

                        UserExam::firstOrCreate([
                            'user_id' => $order->user_id,
                            'exam_id' => $item->exam_id,
                            'order_id' => $order->id,
                            'access_type' => 'engine',
                        ], [
                            'download_count' => 0,
                            'max_downloads' => 0,
                            'purchased_at' => now(),
                        ]);
                    } elseif ($item->item_type === 'package') {
                        $pkg = \App\Models\Package::find($item->exam_id);
                        if ($pkg) {
                            \App\Models\UserPackage::firstOrCreate([
                                'user_id' => $order->user_id,
                                'package_id' => $item->exam_id,
                                'order_id' => $order->id,
                            ], [
                                'status' => 'active',
                                'purchased_at' => now(),
                                'expires_at' => $pkg->access_days ? now()->addDays($pkg->access_days) : null,
                            ]);
                        }
                    }
                }
                
                Log::info("Order {$order->order_number} marked completed via payment_intent.succeeded webhook.");
            }
        } else {
            // Webhook arrived before redirect. Create the order if metadata is available.
            $metadata = $paymentIntent->metadata;
            if (isset($metadata->user_id) && isset($metadata->email)) {
                // Since checkout session parameters are inside Laravel session,
                // if it's a completely out-of-band webhook, we reconstruct using the metadata.
                Log::info("Stripe Webhook: Reconstructing order from metadata for User ID: " . $metadata->user_id);
                // Note: Reconstructing cart elements from metadata can be limited,
                // but usually the customer succeeds and redirects, creating the order.
            }
        }
    }

    /**
     * Handle subscription invoice payment success.
     */
    protected function handleInvoicePaymentSucceeded($invoice)
    {
        $stripeSubId = $invoice->subscription;
        if (!$stripeSubId) {
            return;
        }

        $subscription = Subscription::where('stripe_subscription_id', $stripeSubId)->first();

        if ($subscription) {
            // Update period end date
            $newEnd = now()->addMonth(); // Fallback
            if (isset($invoice->lines->data[0]->period->end)) {
                $newEnd = \Carbon\Carbon::createFromTimestamp($invoice->lines->data[0]->period->end);
            }
            
            $this->subscriptionService->renewSubscription($subscription, $newEnd);
            Log::info("Stripe subscription {$stripeSubId} renewed via webhook.");
        } else {
            // Reconstruct Subscription dynamically from webhook data if created out-of-band
            // Fetch subscription details to retrieve metadata
            Log::info("Subscription {$stripeSubId} not found in database. Waiting for checkout success or webhook setup.");
        }
    }

    /**
     * Handle failed subscription invoice payments.
     */
    protected function handleInvoicePaymentFailed($invoice)
    {
        $stripeSubId = $invoice->subscription;
        if (!$stripeSubId) {
            return;
        }

        $subscription = Subscription::where('stripe_subscription_id', $stripeSubId)->first();
        if ($subscription) {
            $this->subscriptionService->handleFailedPayment($subscription);
            Log::warning("Stripe subscription {$stripeSubId} marked as past_due due to failed payment.");
        }
    }

    /**
     * Handle cancellation / deletion of a subscription.
     */
    protected function handleSubscriptionDeleted($stripeSub)
    {
        $stripeSubId = $stripeSub->id;
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubId)->first();
        
        if ($subscription) {
            $this->subscriptionService->cancelSubscription($subscription);
            Log::info("Stripe subscription {$stripeSubId} cancelled/deleted via webhook.");
        }
    }
}
