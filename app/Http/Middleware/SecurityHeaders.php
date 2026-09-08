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
        
        // Route-specific X-Robots-Tag: Public SEO pages return index, follow.
        // Private admin, auth, checkout, dashboard, and test-session pages return noindex, nofollow.
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
            'verify-email*',
            'confirm-password',
            'cart*',
            'checkout*',
            'demo-test-engine/session*',
            'demo-test-engine/results*',
            'test-session*',
            'exam-session*',
            'practice-exam*',
            'simulator*',
            'api*',
            'webhook*',
            'webhooks*',
        ]) || $response->getStatusCode() >= 400;

        $isSearch = $request->is([
            'search*',
            'blog/search*',
        ]);

        $isFeedOrMeta = $request->is([
            'robots.txt',
            'sitemap*.xml',
            '*.xml',
        ]);

        if ($isFeedOrMeta) {
            $response->headers->remove('X-Robots-Tag');
        } elseif ($isPrivate) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        } elseif ($isSearch) {
            $response->headers->set('X-Robots-Tag', 'noindex, follow');
        } else {
            // Public SEO pages (exams, vendors, certifications, blog, home, and static pages)
            try {
                $index = \App\Models\Setting::get('seo_robots_index', 'index');
                $follow = \App\Models\Setting::get('seo_robots_follow', 'follow');

                $response->headers->set('X-Robots-Tag', "{$index}, {$follow}");
            } catch (\Throwable $e) {
                $response->headers->set('X-Robots-Tag', 'index, follow');
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
