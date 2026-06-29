<?php

namespace App\Services;

use Stripe\StripeClient;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class StripeService
{
    protected ?StripeClient $stripe = null;
    protected bool $isMocked = false;

    public function __construct()
    {
        $secret = config('services.stripe.secret');
        if (empty($secret) || $secret === 'sk_test_placeholder') {
            $this->isMocked = true;
        } else {
            try {
                $this->stripe = new StripeClient($secret);
            } catch (\Exception $e) {
                Log::error('Stripe initialization failed: ' . $e->getMessage());
                $this->isMocked = true;
            }
        }
    }

    /**
     * Check if the Stripe service is in mock mode.
     */
    public function isMocked(): bool
    {
        return $this->isMocked;
    }

    /**
     * Create a Stripe PaymentIntent for one-time purchases.
     */
    public function createPaymentIntent(float $amount, array $metadata = []): array
    {
        if ($this->isMocked) {
            $intentId = 'pi_mock_' . bin2hex(random_bytes(8));
            return [
                'id' => $intentId,
                'client_secret' => $intentId . '_secret_' . bin2hex(random_bytes(8)),
                'amount' => (int)($amount * 100),
                'currency' => 'usd',
                'status' => 'requires_payment_method',
                'is_mock' => true,
            ];
        }

        try {
            $intent = $this->stripe->paymentIntents->create([
                'amount' => (int)($amount * 100), // Stripe expects cents
                'currency' => 'usd',
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return [
                'id' => $intent->id,
                'client_secret' => $intent->client_secret,
                'amount' => $intent->amount,
                'currency' => $intent->currency,
                'status' => $intent->status,
                'is_mock' => false,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe PaymentIntent creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a Stripe Customer if not exists, or return the existing one.
     */
    public function getOrCreateCustomer(User $user): string
    {
        if ($this->isMocked) {
            return 'cus_mock_' . bin2hex(random_bytes(8));
        }

        // Check if user already has a stripe customer ID in a subscription or settings
        $existingSub = $user->subscriptions()->whereNotNull('stripe_customer_id')->first();
        if ($existingSub && $existingSub->stripe_customer_id) {
            return $existingSub->stripe_customer_id;
        }

        try {
            $customer = $this->stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);
            return $customer->id;
        } catch (\Exception $e) {
            Log::error('Stripe Customer creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a Stripe Subscription for engine plans (using Stripe Elements flow).
     */
    public function createSubscription(User $user, string $planName, string $billingCycle, float $price, array $metadata = []): array
    {
        $customerId = $this->getOrCreateCustomer($user);

        if ($this->isMocked) {
            $subId = 'sub_mock_' . bin2hex(random_bytes(8));
            $intentId = 'pi_mock_' . bin2hex(random_bytes(8));
            return [
                'id' => $subId,
                'customer_id' => $customerId,
                'client_secret' => $intentId . '_secret_' . bin2hex(random_bytes(8)),
                'status' => 'incomplete',
                'is_mock' => true,
            ];
        }

        try {
            // Find or create a Stripe Product & Price dynamically to avoid hardcoding Price IDs
            // This is extremely helpful for local and Hostinger settings where they don't have to pre-create prices.
            $stripePriceId = $this->getOrCreateStripePrice($planName, $billingCycle, $price);

            $subscription = $this->stripe->subscriptions->create([
                'customer' => $customerId,
                'items' => [[
                    'price' => $stripePriceId,
                ]],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => array_merge($metadata, [
                    'user_id' => $user->id,
                    'plan_name' => $planName,
                    'billing_cycle' => $billingCycle,
                ]),
            ]);

            $paymentIntent = $subscription->latest_invoice->payment_intent;

            return [
                'id' => $subscription->id,
                'customer_id' => $customerId,
                'client_secret' => $paymentIntent ? $paymentIntent->client_secret : null,
                'status' => $subscription->status,
                'is_mock' => false,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe Subscription creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Dynamically resolve or create Stripe Product and Price for the given plan.
     */
    protected function getOrCreateStripePrice(string $planName, string $billingCycle, float $price): string
    {
        $productName = "ExamsNinja " . ucfirst($planName) . " Plan";
        $priceCode = strtolower($planName) . "_" . strtolower($billingCycle);
        
        try {
            // Check if product already exists
            $products = $this->stripe->products->search([
                'query' => "name:'" . $productName . "'",
            ]);

            if (count($products->data) > 0) {
                $product = $products->data[0];
            } else {
                $product = $this->stripe->products->create([
                    'name' => $productName,
                    'metadata' => ['plan_name' => $planName],
                ]);
            }

            // Check if price already exists for this amount and interval
            $prices = $this->stripe->prices->all([
                'product' => $product->id,
                'active' => true,
            ]);

            $interval = ($billingCycle === 'annual') ? 'year' : 'month';
            $amountCents = (int)($price * 100);

            foreach ($prices->data as $p) {
                if ($p->unit_amount === $amountCents && $p->recurring->interval === $interval) {
                    return $p->id;
                }
            }

            // Create new price if not exists
            $newPrice = $this->stripe->prices->create([
                'unit_amount' => $amountCents,
                'currency' => 'usd',
                'recurring' => ['interval' => $interval],
                'product' => $product->id,
                'metadata' => ['price_code' => $priceCode],
            ]);

            return $newPrice->id;
        } catch (\Exception $e) {
            Log::error('Stripe Product/Price dynamic lookup failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Retrieve subscription details.
     */
    public function getSubscription(string $subscriptionId)
    {
        if ($this->isMocked) {
            return null;
        }
        return $this->stripe->subscriptions->retrieve($subscriptionId);
    }

    /**
     * Cancel a Stripe subscription.
     */
    public function cancelSubscription(string $subscriptionId, bool $cancelAtPeriodEnd = true): bool
    {
        if ($this->isMocked) {
            return true;
        }

        try {
            if ($cancelAtPeriodEnd) {
                $this->stripe->subscriptions->update($subscriptionId, [
                    'cancel_at_period_end' => true,
                ]);
            } else {
                $this->stripe->subscriptions->cancel($subscriptionId);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Stripe Subscription cancellation failed: ' . $e->getMessage());
            return false;
        }
    }
}
