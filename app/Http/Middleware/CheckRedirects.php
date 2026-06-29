<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Redirect;

class CheckRedirects
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check on GET requests for a 404 to avoid unnecessary DB hits?
        // Actually, let's just check the path if it matches a redirect.
        $path = $request->path();
        
        // Also check with leading slash just in case
        $redirect = Redirect::where('old_url', $path)->orWhere('old_url', '/' . $path)->first();

        if ($redirect) {
            return redirect($redirect->new_url, $redirect->status_code);
        }

        return $next($request);
    }
}
