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

            // A one-vendor site has no vendor index (/vendors redirects to the vendor page) and may not have a guarantee page.
            $singleVendor = count((array) config('site.vendors', [])) === 1;
            if ($singleVendor) {
                unset($staticRoutes['/vendors']);
            }
            if (!config('site.show_guarantee', true)) {
                unset($staticRoutes['/guarantee']);
            }

            foreach ($staticRoutes as $path => $meta) {
                $urls[] = [
                    'loc' => rtrim($host, '/') . $path,
                    'priority' => $meta['priority'],
                    'changefreq' => $meta['changefreq'],
                    // no lastmod: these pages have no real modified date, and a fake "today" teaches crawlers to ignore lastmod
                    'lastmod' => null,
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

            // Exams with no free (single-choice) question have nothing on their free-questions page yet: leave it out.
            $withFree = \App\Models\Question::withoutGlobalScopes()->where('is_active', true)->where('question_type', 'single_choice')
                ->whereIn('exam_id', $exams->pluck('id'))->distinct()->pluck('exam_id')->flip();

            foreach ($exams as $e) {
                $overlay = $site ? $e->overlays->first() : null;
                // If overlay exists and marked not active or noindex, skip from sitemap
                if ($overlay && (!$overlay->is_active || !$overlay->is_indexed)) {
                    continue;
                }

                $vendorSlug = $e->vendor ? $e->vendor->slug : 'vendor';
                $slug = ($overlay && !empty($overlay->custom_slug)) ? $overlay->custom_slug : $e->slug;

                $lastmod = $e->last_updated_at ? $e->last_updated_at->format('Y-m-d') : ($e->updated_at ? $e->updated_at->format('Y-m-d') : now()->format('Y-m-d'));
                $urls[] = [
                    'loc' => rtrim($host, '/') . '/' . \App\Models\Exam::publicPath($vendorSlug, $slug),
                    'priority' => '0.9',
                    'changefreq' => 'weekly',
                    'lastmod' => $lastmod,
                ];

                // Hub-style sites give every exam its own free-questions and plans pages
                if (\App\Models\Exam::usesHubUrls()) {
                    if (isset($withFree[$e->id])) {
                        $urls[] = ['loc' => $e->questions_url, 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $lastmod];
                    }
                    $urls[] = ['loc' => $e->plans_url, 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $lastmod];
                }
            }

            if (\App\Models\Exam::usesHubUrls() && $singleVendor) {
                foreach (\App\Support\ExamTracks::group($exams, (string) config('site.vendors')[0]) as $track) {
                    if (!empty($track['slug']) && $track['key'] !== 'more') {
                        $urls[] = ['loc' => route('tracks.show', $track['slug']), 'priority' => '0.7', 'changefreq' => 'weekly', 'lastmod' => null];
                    }
                }
            }

            // Generate XML
            $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            foreach ($urls as $u) {
                $out .= "  <url>\n";
                $out .= "    <loc>" . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
                if (!empty($u['lastmod'])) {
                    $out .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
                }
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
     * /llms.txt: a plain-text guide for AI assistants (what the site is, its main pages, its best exam pages).
     * Built per site from the catalogue, so every site describes itself and only lists pages it really has.
     */
    public function llms(Request $request): Response
    {
        $host = rtrim($request->getSchemeAndHttpHost(), '/');
        $site = app()->bound('current_site') ? app('current_site') : null;
        $brand = $site ? $site->name : config('app.name');
        $cacheKey = 'llms_txt_' . (int) config('site.id', 1) . '_' . md5($host);

        $text = Cache::remember($cacheKey, 3600, function () use ($host, $brand) {
            $exams = \App\Models\Exam::where('is_active', true)->with('vendor')->withCount('questions')
                ->orderByDesc('questions_count')->limit(30)->get();
            $vendors = \App\Models\Vendor::where('is_active', true)->orderByDesc('exam_count')->limit(12)->get();
            $examTotal = \App\Models\Exam::where('is_active', true)->count();

            $lines = [
                "# {$brand}",
                '',
                "> {$brand} offers practice tests, practice questions and study guides for {$examTotal} IT certification exams. It also has a free sample for each exam and an online timed test engine. It is independent study material: it is not affiliated with, endorsed by or sponsored by any certification vendor, and it is not official exam content.",
                '',
                '## Main pages',
                "- [Home]({$host}/): overview of the practice tests and certification vendors",
                "- [Vendors]({$host}/vendors): every certification vendor and its exams",
                "- [Free sample]({$host}/free-demo): request a free sample of practice questions",
                "- [Test engine]({$host}/test-engine): timed practice sessions in the browser",
                "- [Blog]({$host}/blog): study guides, certification news and exam preparation tips",
                "- [FAQ]({$host}/faq): answers about the material, access and refunds",
                "- [About]({$host}/about) and [Contact]({$host}/contact)",
                '',
                '## Certification vendors',
            ];
            foreach ($vendors as $v) {
                $lines[] = "- [{$v->name}]({$host}/vendors/{$v->slug}): {$v->name} certification practice tests and exam questions";
            }
            $lines[] = '';
            $lines[] = '## Popular exam pages';
            foreach ($exams as $e) {
                $label = trim(($e->exam_code ?? '') . ' ' . ($e->exam_name ?? ''));
                $lines[] = "- [{$label}]({$e->url}): practice test, practice questions and study guide" . ($e->vendor ? " for the {$e->vendor->name} exam" : '');
            }
            $lines[] = '';
            $lines[] = '## Notes for AI assistants';
            $lines[] = '- Each exam page names the vendor exam it prepares for, shows how many practice questions it has and links to a free sample.';
            $lines[] = "- Check the vendor's own exam page for the current exam outline, price and policies before recommending a booking.";

            return implode("\n", $lines) . "\n";
        });

        return response($text, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * Dynamically stream site-scoped robots.txt.
     */
    public function robots(Request $request): Response
    {
        $site = app()->bound('current_site') ? app('current_site') : null;
        $host = $request->getSchemeAndHttpHost();

        // Sites other than the core one serve their own full rule set (edited in Technical SEO,
        // or the default) with their own sitemap URL.
        if ((int) config('site.id', 1) !== 1) {
            return response(app(\App\Services\TechnicalSeoService::class)->getRobotsTxtContent(), 200, ['Content-Type' => 'text/plain']);
        }

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
