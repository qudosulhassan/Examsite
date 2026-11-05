<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Redirect;
use App\Services\TechnicalSeoService;

class CheckRedirects
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = '/' . ltrim($request->path(), '/');

        // Check active redirects
        $redirect = null;
        // Table/column checks are information_schema queries; on a site whose database is on another server
        // each one costs a network round trip, so remember the answers for a day.
        $schema = \Illuminate\Support\Facades\Cache::remember('schema_redirects_v1', 86400, fn () => [
            'table'  => \Illuminate\Support\Facades\Schema::hasTable('redirects'),
            'active' => \Illuminate\Support\Facades\Schema::hasColumn('redirects', 'is_active'),
            'site'   => \Illuminate\Support\Facades\Schema::hasColumn('redirects', 'site_id'),
        ]);
        if ($schema['table']) {
            try {
                $site = app()->bound('current_site') ? app('current_site') : null;
                $q = Redirect::query();
                if ($schema['active']) {
                    $q->where('is_active', true);
                }

                if ($site && $schema['site']) {
                    $q->where(function ($query) use ($site) {
                        $query->where('site_id', $site->id)
                              ->orWhereNull('site_id');
                    });
                }

                // Most paths have no redirect rule, and asking the database costs a round trip on every request (a
                // network hop when the database is on another server), so remember the answer, including "none".
                // Off by default (SITE_REDIRECT_CACHE_TTL=0): with it on, a rule edited in the admin applies after the TTL.
                $lookup = function () use ($q, $path) {
                    return $q->where(function ($query) use ($path) {
                        $query->where('old_url', $path)
                              ->orWhere('old_url', ltrim($path, '/'));
                    })->orderByRaw('CASE WHEN site_id IS NULL THEN 1 ELSE 0 END ASC')->first() ?: false;
                };
                $ttl = (int) config('site.redirect_cache_ttl', 0);
                $found = $ttl > 0
                    ? \Illuminate\Support\Facades\Cache::remember('redirect_rule:' . ($site->id ?? 0) . ':' . sha1($path), $ttl, $lookup)
                    : $lookup();
                $redirect = $found ?: null;
            } catch (\Throwable $th) {}
        }

        if ($redirect) {
            $dest = $redirect->new_url;

            // Loop safety: Do not redirect if destination matches current path
            if (Redirect::normalizeUrl($dest) !== Redirect::normalizeUrl($path)) {
                // Increment stats asynchronously or silently
                try {
                    $redirect->increment('hits_count');
                    $redirect->update(['last_accessed_at' => now()]);
                } catch (\Throwable $th) {}

                $statusCode = in_array((int)$redirect->status_code, [301, 302, 307, 308]) ? (int)$redirect->status_code : 301;
                return redirect($dest, $statusCode);
            }
        }

        $response = $next($request);

        // Record 404 hit for SEO tracking
        if ($response->getStatusCode() === 404 && !$request->is('admin*') && !$request->is('api*')) {
            try {
                app(TechnicalSeoService::class)->recordNotFound($request);
            } catch (\Throwable $th) {}
        }

        return $response;
    }
}
