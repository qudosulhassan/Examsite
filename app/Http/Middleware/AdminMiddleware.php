<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            return $next($request);
        }

        // SEO Manager: only the Technical SEO Center (its pages and its save/regenerate buttons)
        if (auth()->check() && auth()->user()->isActive() && auth()->user()->can('manage-seo') && $request->is('admin/settings/seo', 'admin/settings/seo/*', 'admin/seo')) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Access denied. You do not have administrator privileges.');
    }
}
