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
        $redirect = Redirect::where('is_active', true)
            ->where(function ($q) use ($path) {
                $q->where('old_url', $path)
                  ->orWhere('old_url', ltrim($path, '/'));
            })
            ->first();

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
