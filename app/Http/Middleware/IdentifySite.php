<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Site;
use App\Models\SiteDomain;
use App\Services\SiteContext;
use App\Services\ThemeManager;

class IdentifySite
{
    public function __construct(
        protected SiteContext $siteContext,
        protected ThemeManager $themeManager
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $site = $this->siteContext->resolve($request->getHost());

        if ($site) {
            app()->instance('current_site', $site);
            view()->share('currentSite', $site);

            $this->themeManager->setTheme($this->siteContext->theme());
        }

        return $next($request);
    }
}
