<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the rendered HTML of public pages for logged-out visitors, so a request costs one cache read instead of
 * a few dozen database round trips (the shared database can be on another server).
 *
 * Only plain GET requests for public content are cached, and never for a signed-in user, a visitor with items in
 * their cart, or a request that carries flash messages or old form input. Pages contain the visitor's CSRF token (meta
 * tag and hidden form fields), so the token is replaced with a placeholder before storing and with the current
 * visitor's token when serving. Off unless SITE_PAGE_CACHE=true; entries expire after SITE_PAGE_CACHE_TTL seconds and
 * `php artisan pagecache:flush` clears them all (run it from the deploy).
 */
class CacheGuestPages
{
    private const PLACEHOLDER = '@@CSRF-TOKEN@@';

    /** Public paths worth caching (everything else, such as search, cart and login, is always rendered fresh). */
    private const PATHS = [
        '/', 'vendors', 'vendors/*', 'tracks/*', 'exams/*', 'certifications', 'certifications/*',
        'about', 'contact', 'faq', 'privacy-policy', 'dmca', 'terms', 'refund-policy', 'guarantee', 'blog', 'blog/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!config('site.page_cache') || !$this->cacheable($request)) {
            return $next($request);
        }

        $key = $this->key($request);
        $hit = Cache::get($key);

        if (is_string($hit)) {
            return response(str_replace(self::PLACEHOLDER, csrf_token(), $hit), 200, [
                'Content-Type'  => 'text/html; charset=UTF-8',
                'X-Page-Cache'  => 'HIT',
            ]);
        }

        $response = $next($request);

        if ($response->getStatusCode() === 200
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            && !$request->session()->has('errors')
        ) {
            $html = (string) $response->getContent();
            $token = csrf_token();
            if ($token !== '' && $html !== '') {
                Cache::put($key, str_replace($token, self::PLACEHOLDER, $html), (int) config('site.page_cache_ttl', 900));
                $response->headers->set('X-Page-Cache', 'MISS');
            }
        }

        return $response;
    }

    private function cacheable(Request $request): bool
    {
        if (!$request->isMethod('GET') || $request->ajax() || auth()->check()) {
            return false;
        }

        // ?page=N is the only query string that selects different content
        if ($request->query() && array_keys($request->query()) !== ['page']) {
            return false;
        }

        if (!$request->is(self::PATHS)) {
            return false;
        }

        $session = $request->session();

        return empty($session->get('cart')) && empty($session->get('cart_coupon'))
            && !$session->has('_old_input') && !$session->has('success') && !$session->has('error') && !$session->has('status');
    }

    private function key(Request $request): string
    {
        $version = (int) Cache::get('page_cache_version', 1);

        return 'page:v' . $version . ':' . sha1($request->getHost() . '|' . $request->getPathInfo() . '|' . ($request->query('page') ?? ''));
    }
}
