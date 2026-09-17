<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Vendor;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class DynamicSeoController extends Controller
{
    /**
     * Dynamically stream site-scoped XML sitemap.
     */
    public function sitemap(Request $request): Response
    {
        $siteContext = app(\App\Services\SiteContext::class);
        $site = $siteContext->site();
        $siteId = $siteContext->id() ?? 1;
        $host = $request->getSchemeAndHttpHost();

        $cacheKey = "sitemap_site_{$siteId}_" . md5($host);

        $xml = Cache::remember($cacheKey, 1800, function () use ($siteContext, $site, $siteId, $host) {
            $urls = [];

            // 1. Static URLs
            $staticRoutes = [
                '/' => ['priority' => '1.0', 'changefreq' => 'daily'],
                '/vendors' => ['priority' => '0.8', 'changefreq' => 'weekly'],
                '/faq' => ['priority' => '0.7', 'changefreq' => 'monthly'],
                '/about' => ['priority' => '0.6', 'changefreq' => 'monthly'],
                '/contact' => ['priority' => '0.6', 'changefreq' => 'monthly'],
                '/privacy-policy' => ['priority' => '0.5', 'changefreq' => 'monthly'],
                '/dmca' => ['priority' => '0.5', 'changefreq' => 'monthly'],
                '/guarantee' => ['priority' => '0.7', 'changefreq' => 'monthly'],
            ];

            foreach ($staticRoutes as $path => $meta) {
                $urls[] = [
                    'loc' => rtrim($host, '/') . $path,
                    'priority' => $meta['priority'],
                    'changefreq' => $meta['changefreq'],
                    'lastmod' => now()->format('Y-m-d'),
                ];
            }

            // 2. Vendors
            $vendors = Vendor::where('is_active', true)->get();
            foreach ($vendors as $v) {
                $urls[] = [
                    'loc' => rtrim($host, '/') . '/vendors/' . $v->slug,
                    'priority' => '0.7',
                    'changefreq' => 'weekly',
                    'lastmod' => $v->updated_at ? $v->updated_at->format('Y-m-d') : now()->format('Y-m-d'),
                ];
            }

            // 3. Active Exams (scoped via SiteContext)
            $baseQuery = Exam::where('is_active', true)
                ->whereHas('vendor', function ($q) {
                    $q->where('is_active', true);
                });

            $examsQuery = $siteContext->scopeVisibleExams($baseQuery, indexedOnly: ($siteId > 1));

            $exams = $examsQuery->with(['vendor', 'overlays' => function ($q) use ($site) {
                    if ($site) {
                        $q->where('site_id', $site->id);
                    }
                }])
                ->get();

            foreach ($exams as $e) {
                $overlay = $site ? $e->overlays->first() : null;
                // If overlay exists and marked not active or noindex, skip from sitemap
                if ($overlay && (!$overlay->is_active || !$overlay->is_indexed)) {
                    continue;
                }

                $vendorSlug = $e->vendor ? $e->vendor->slug : 'vendor';
                $slug = ($overlay && !empty($overlay->custom_slug)) ? $overlay->custom_slug : $e->slug;

                $urls[] = [
                    'loc' => rtrim($host, '/') . "/exams/{$vendorSlug}/{$slug}",
                    'priority' => '0.9',
                    'changefreq' => 'weekly',
                    'lastmod' => $e->last_updated_at ? $e->last_updated_at->format('Y-m-d') : ($e->updated_at ? $e->updated_at->format('Y-m-d') : now()->format('Y-m-d')),
                ];
            }

            // Generate XML
            $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            foreach ($urls as $u) {
                $out .= "  <url>\n";
                $out .= "    <loc>" . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
                $out .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
                $out .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
                $out .= "    <priority>{$u['priority']}</priority>\n";
                $out .= "  </url>\n";
            }

            $out .= '</urlset>';
            return $out;
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Dynamically stream site-scoped robots.txt.
     */
    public function robots(Request $request): Response
    {
        $site = app()->bound('current_site') ? app('current_site') : null;
        $host = $request->getSchemeAndHttpHost();

        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin/',
            'Disallow: /checkout/',
            'Disallow: /cart/',
            'Disallow: /api/',
            '',
            'Sitemap: ' . rtrim($host, '/') . '/sitemap.xml',
        ];

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
    }
}
