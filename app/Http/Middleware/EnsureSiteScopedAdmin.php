<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSiteScopedAdmin
{
    /**
     * Handle an incoming request.
     * Enforces that requests with a {site} route parameter match the application's fixed site ID.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentAppSiteId = (int) config('site.id', 1);
        $siteParam = $request->route('site');

        // Central Core (site 1) administrators manage every site.
        if ($siteParam !== null && $currentAppSiteId !== 1) {
            $siteId = $siteParam instanceof Site ? (int) $siteParam->id : (int) $siteParam;

            if ($siteId !== $currentAppSiteId) {
                abort(403, "Access denied. This application instance is strictly locked to Site ID {$currentAppSiteId}.");
            }
        }

        return $next($request);
    }
}
