<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share global settings across views safely
        try {
            \Illuminate\Support\Facades\View::composer('*', function ($view) {
                $view->with('globalSettings', \App\Models\Setting::allAsAssoc());
            });
        } catch (\Throwable $e) {
            // Ignore during migrations or initial bootstrap
        }

        // Dynamically configure payment gateways from encrypted database settings if present
        try {
            $stripeKey = \App\Models\Setting::get('stripe_publishable_key');
            $stripeSecretEnc = \App\Models\Setting::get('stripe_secret_key');
            $stripeWebhookEnc = \App\Models\Setting::get('stripe_webhook_secret');
            $stripeMode = \App\Models\Setting::get('stripe_mode');

            if (!empty($stripeKey)) {
                config(['services.stripe.key' => $stripeKey]);
            }
            if (!empty($stripeSecretEnc)) {
                try {
                    config(['services.stripe.secret' => \Illuminate\Support\Facades\Crypt::decryptString($stripeSecretEnc)]);
                } catch (\Throwable $th) {
                    config(['services.stripe.secret' => $stripeSecretEnc]);
                }
            }
            if (!empty($stripeWebhookEnc)) {
                try {
                    config(['services.stripe.webhook_secret' => \Illuminate\Support\Facades\Crypt::decryptString($stripeWebhookEnc)]);
                } catch (\Throwable $th) {
                    config(['services.stripe.webhook_secret' => $stripeWebhookEnc]);
                }
            }

            $paypalClientId = \App\Models\Setting::get('paypal_client_id');
            $paypalSecretEnc = \App\Models\Setting::get('paypal_client_secret');
            $paypalWebhookId = \App\Models\Setting::get('paypal_webhook_id');
            $paypalMode = \App\Models\Setting::get('paypal_mode');

            if (!empty($paypalClientId)) {
                config(['services.paypal.client_id' => $paypalClientId]);
            }
            if (!empty($paypalSecretEnc)) {
                try {
                    config(['services.paypal.client_secret' => \Illuminate\Support\Facades\Crypt::decryptString($paypalSecretEnc)]);
                } catch (\Throwable $th) {
                    config(['services.paypal.client_secret' => $paypalSecretEnc]);
                }
            }
            if (!empty($paypalMode)) {
                config(['services.paypal.mode' => $paypalMode]);
            }
            if (!empty($paypalWebhookId)) {
                config(['services.paypal.webhook_id' => $paypalWebhookId]);
            }
        } catch (\Throwable $e) {
            // Safe fallback if database isn't ready
        }
    }
}
