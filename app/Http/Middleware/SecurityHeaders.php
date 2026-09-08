<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'microphone=(), camera=(), geolocation=()');
        
        // Smart X-Robots-Tag: Explicitly noindex private/admin/auth/checkout/engine pages.
        $isPrivate = $request->is([
            'admin*',
            'dashboard*',
            'user*',
            'profile*',
            'my-account*',
            'orders*',
            'downloads*',
            'login',
            'register',
            'password*',
            'forgot-password',
            'reset-password*',
            'cart*',
            'checkout*',
            'demo-test-engine/session*',
            'demo-test-engine/results*',
            'api*',
            'webhook*',
            'webhooks*',
        ]);

        if ($isPrivate) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        } else {
            // For public routes, check if Technical SEO setting is explicitly noindex
            try {
                $robotsSetting = app(\App\Services\TechnicalSeoService::class)->getRobotsMetaDirective();
                if (str_contains($robotsSetting, 'noindex')) {
                    $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
                } else {
                    $response->headers->remove('X-Robots-Tag');
                }
            } catch (\Throwable $e) {
                $response->headers->remove('X-Robots-Tag');
            }
        }
        
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Basic CSP allowing Stripe, PayPal, Google Tag Manager, Google Fonts, and internal assets
        $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com https://www.paypal.com https://www.googletagmanager.com https://www.google-analytics.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https://www.google-analytics.com https://*.stripe.com; frame-src 'self' https://js.stripe.com https://www.paypal.com;";
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
