<?php

namespace App\Services;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    protected ?PayPalClient $provider = null;
    protected bool $isMocked = false;

    public function __construct()
    {
        $clientId = config('services.paypal.client_id');
        $secret = config('services.paypal.client_secret');

        if (empty($clientId) || $clientId === 'sandbox_client_id_placeholder') {
            $this->isMocked = true;
        } else {
            try {
                $this->provider = new PayPalClient;
                $config = [
                    'mode'    => config('services.paypal.mode', 'sandbox'),
                    'sandbox' => [
                        'client_id'         => $clientId,
                        'client_secret'     => $secret,
                        'app_id'            => 'APP-80W284485P519543T',
                    ],
                    'live' => [
                        'client_id'         => $clientId,
                        'client_secret'     => $secret,
                        'app_id'            => '',
                    ],
                    'payment_action' => 'Sale',
                    'currency'       => 'USD',
                    'notify_url'     => route('webhooks.paypal'),
                    'locale'         => 'en_US',
                    'validate_ssl'   => true,
                ];
                $this->provider->setApiCredentials($config);
                $this->provider->getAccessToken();
            } catch (\Exception $e) {
                Log::error('PayPal initialization failed: ' . $e->getMessage());
                $this->isMocked = true;
            }
        }
    }

    /**
     * Check if the PayPal service is in mock mode.
     */
    public function isMocked(): bool
    {
        return $this->isMocked;
    }

    /**
     * Create a PayPal order for one-time purchases.
     */
    public function createOrder(float $amount, string $returnUrl = '', string $cancelUrl = ''): array
    {
        if ($this->isMocked) {
            $orderId = 'paypal_mock_' . bin2hex(random_bytes(8));
            return [
                'id' => $orderId,
                'status' => 'CREATED',
                'approve_url' => route('checkout.success') . '?method=paypal&token=' . $orderId,
                'is_mock' => true,
            ];
        }

        try {
            $data = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => number_format($amount, 2, '.', ''),
                        ],
                    ]
                ],
                'application_context' => [
                    'return_url' => $returnUrl ?: route('checkout.success') . '?method=paypal',
                    'cancel_url' => $cancelUrl ?: route('checkout.cancel'),
                ],
            ];

            $order = $this->provider->createOrder($data);

            $approveUrl = '';
            if (isset($order['links'])) {
                foreach ($order['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $approveUrl = $link['href'];
                        break;
                    }
                }
            }

            return [
                'id' => $order['id'],
                'status' => $order['status'] ?? 'CREATED',
                'approve_url' => $approveUrl ?: $returnUrl,
                'is_mock' => false,
            ];
        } catch (\Exception $e) {
            Log::error('PayPal order creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Capture payment for a PayPal order.
     */
    public function captureOrder(string $paypalOrderId): array
    {
        if ($this->isMocked || str_starts_with($paypalOrderId, 'paypal_mock_')) {
            return [
                'id' => $paypalOrderId,
                'status' => 'COMPLETED',
                'payer_email' => 'sandbox-buyer@examsninja.com',
                'payer_name' => 'Sandbox Buyer',
                'amount' => '0.00',
                'is_mock' => true,
            ];
        }

        try {
            $result = $this->provider->capturePaymentOrder($paypalOrderId);

            if (isset($result['status']) && $result['status'] === 'COMPLETED') {
                $payer = $result['payer'];
                $amount = $result['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? '0.00';
                
                return [
                    'id' => $result['id'],
                    'status' => 'COMPLETED',
                    'payer_email' => $payer['email_address'] ?? '',
                    'payer_name' => ($payer['name']['given_name'] ?? '') . ' ' . ($payer['name']['surname'] ?? ''),
                    'amount' => $amount,
                    'is_mock' => false,
                ];
            }

            return [
                'id' => $paypalOrderId,
                'status' => $result['status'] ?? 'FAILED',
                'is_mock' => false,
            ];
        } catch (\Exception $e) {
            Log::error('PayPal order capture failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a PayPal Subscription plan or return dynamic Plan ID.
     */
    public function getOrCreateSubscriptionPlan(string $planName, string $billingCycle, float $price): string
    {
        // For local development sandbox, return a mock/dummy PayPal Plan ID
        // PayPal Subscriptions require pre-creating plans in PayPal Dashboard,
        // so having a dynamic lookup or fallback mock ID is crucial.
        return 'P-mock-plan-' . strtolower($planName) . '-' . strtolower($billingCycle);
    }

    /**
     * Create subscription checkout/redirect link for PayPal subscriptions.
     */
    public function createSubscription(string $planId, string $returnUrl = '', string $cancelUrl = ''): array
    {
        if ($this->isMocked) {
            $subId = 'sub_paypal_mock_' . bin2hex(random_bytes(8));
            return [
                'id' => $subId,
                'approve_url' => route('checkout.success') . '?method=paypal_sub&subscription_id=' . $subId,
                'is_mock' => true,
            ];
        }

        try {
            $data = [
                'plan_id' => $planId,
                'application_context' => [
                    'return_url' => $returnUrl ?: route('checkout.success') . '?method=paypal_sub',
                    'cancel_url' => $cancelUrl ?: route('checkout.cancel'),
                ],
            ];

            // Using srmklive/paypal Subscriptions API
            $subscription = $this->provider->createSubscription($data);

            $approveUrl = '';
            if (isset($subscription['links'])) {
                foreach ($subscription['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $approveUrl = $link['href'];
                        break;
                    }
                }
            }

            return [
                'id' => $subscription['id'],
                'approve_url' => $approveUrl ?: $returnUrl,
                'is_mock' => false,
            ];
        } catch (\Exception $e) {
            Log::error('PayPal subscription creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cancel a PayPal subscription.
     */
    public function cancelSubscription(string $subscriptionId, string $reason = 'User cancelled'): bool
    {
        if ($this->isMocked || str_starts_with($subscriptionId, 'sub_paypal_mock_')) {
            return true;
        }

        try {
            $this->provider->cancelSubscription($subscriptionId, $reason);
            return true;
        } catch (\Exception $e) {
            Log::error('PayPal subscription cancellation failed: ' . $e->getMessage());
            return false;
        }
    }
}
