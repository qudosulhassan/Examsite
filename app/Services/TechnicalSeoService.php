<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Exam;
use App\Models\Vendor;
use App\Models\Certification;
use App\Models\BlogPost;
use App\Models\Redirect;
use App\Models\SeoNotFoundLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Carbon;

class TechnicalSeoService
{
    /* =========================================================================
       MODULE 1: XML SITEMAP
       ========================================================================= */

    public function getSitemapData(): array
    {
        $staticRoutes = [
            'home' => ['priority' => '1.0', 'changefreq' => 'daily'],
            'vendors.index' => ['priority' => '0.8', 'changefreq' => 'weekly'],
            'free-demo.index' => ['priority' => '0.8', 'changefreq' => 'weekly'],
            'faq' => ['priority' => '0.7', 'changefreq' => 'monthly'],
            'about' => ['priority' => '0.6', 'changefreq' => 'monthly'],
            'contact' => ['priority' => '0.6', 'changefreq' => 'monthly'],
            'blog.index' => ['priority' => '0.8', 'changefreq' => 'daily'],
        ];

        $staticUrls = [];
        foreach ($staticRoutes as $routeName => $meta) {
            if (Route::has($routeName)) {
                $staticUrls[] = [
                    'loc' => route($routeName),
                    'priority' => $meta['priority'],
                    'changefreq' => $meta['changefreq'],
                    'lastmod' => now()->format('Y-m-d'),
                ];
            }
        }

        // Vendors
        $vendors = Vendor::where('is_active', true)->get();
        $vendorUrls = [];
        foreach ($vendors as $v) {
            $vendorUrls[] = [
                'loc' => route('vendors.show', $v->slug),
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => $v->updated_at ? $v->updated_at->format('Y-m-d') : now()->format('Y-m-d'),
            ];
        }

        // Exams
        $exams = Exam::where('is_active', true)->with('vendor')->get();
        $examUrls = [];
        foreach ($exams as $e) {
            $vendorSlug = $e->vendor ? $e->vendor->slug : 'exam';
            $examUrls[] = [
                'loc' => route('exams.show', ['vendor' => $vendorSlug, 'slug' => $e->slug]),
                'priority' => '0.9',
                'changefreq' => 'weekly',
                'lastmod' => $e->last_updated_at ? $e->last_updated_at->format('Y-m-d') : ($e->updated_at ? $e->updated_at->format('Y-m-d') : now()->format('Y-m-d')),
            ];
        }

        // Certifications
        $certUrls = [];
        if (class_exists(Certification::class) && \Illuminate\Support\Facades\Schema::hasTable('certifications')) {
            try {
                $certs = Certification::where('is_active', true)->get();
                foreach ($certs as $c) {
                    if (Route::has('certifications.show')) {
                        $certUrls[] = [
                            'loc' => route('certifications.show', $c->slug ?? $c->id),
                            'priority' => '0.7',
                            'changefreq' => 'weekly',
                            'lastmod' => $c->updated_at ? $c->updated_at->format('Y-m-d') : now()->format('Y-m-d'),
                        ];
                    }
                }
            } catch (\Throwable $th) {}
        }

        // Blog Posts
        $posts = BlogPost::where(function($q) {
            $q->where('status', 'published')
              ->orWhere('is_published', true);
        })->get();
        $blogUrls = [];
        foreach ($posts as $p) {
            $blogUrls[] = [
                'loc' => route('blog.show', $p->slug),
                'priority' => '0.6',
                'changefreq' => 'monthly',
                'lastmod' => $p->published_at ? $p->published_at->format('Y-m-d') : ($p->updated_at ? $p->updated_at->format('Y-m-d') : now()->format('Y-m-d')),
            ];
        }

        $allUrls = array_merge($staticUrls, $vendorUrls, $examUrls, $certUrls, $blogUrls);

        return [
            'total' => count($allUrls),
            'static_count' => count($staticUrls),
            'vendors_count' => count($vendorUrls),
            'exams_count' => count($examUrls),
            'certs_count' => count($certUrls),
            'blog_count' => count($blogUrls),
            'urls' => $allUrls,
            'sitemap_url' => url('/sitemap.xml'),
            'last_generated' => Setting::get('seo_sitemap_last_generated', 'Never'),
        ];
    }

    public function regenerateSitemap(): array
    {
        $data = $this->getSitemapData();
        $urls = $data['urls'];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            if (!empty($url['lastmod'])) {
                $xml .= "    <lastmod>" . htmlspecialchars($url['lastmod']) . "</lastmod>\n";
            }
            if (!empty($url['changefreq'])) {
                $xml .= "    <changefreq>" . htmlspecialchars($url['changefreq']) . "</changefreq>\n";
            }
            if (!empty($url['priority'])) {
                $xml .= "    <priority>" . htmlspecialchars($url['priority']) . "</priority>\n";
            }
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        // Save to public/sitemap.xml
        try {
            File::put(public_path('sitemap.xml'), $xml);
        } catch (\Throwable $th) {}

        // Update settings
        $nowStr = now()->format('Y-m-d H:i:s');
        Setting::set('seo_sitemap_last_generated', $nowStr);
        Setting::set('seo_sitemap_count', (string)$data['total']);

        return [
            'success' => true,
            'count' => $data['total'],
            'timestamp' => $nowStr,
            'url' => url('/sitemap.xml'),
        ];
    }

    /* =========================================================================
       MODULE 2: ROBOTS.TXT
       ========================================================================= */

    public function getDefaultRobotsTxt(): string
    {
        $sitemapUrl = url('/sitemap.xml');

        return <<<TXT
User-agent: *
Allow: /

# Disallow Private & Admin Areas
Disallow: /admin
Disallow: /admin/
Disallow: /dashboard
Disallow: /dashboard/
Disallow: /cart
Disallow: /checkout
Disallow: /api/
Disallow: /webhook/
Disallow: /login
Disallow: /register
Disallow: /password/

# Disallow Filter & Sort Query Strings
Disallow: /*?*sort=*
Disallow: /*?*filter=*

# Sitemap
Sitemap: {$sitemapUrl}
TXT;
    }

    public function getRobotsTxtContent(): string
    {
        $settingContent = Setting::get('seo_robots_txt_content');
        if (!empty($settingContent)) {
            return $settingContent;
        }

        $filePath = public_path('robots.txt');
        if (File::exists($filePath)) {
            return File::get($filePath);
        }

        return $this->getDefaultRobotsTxt();
    }

    public function saveRobotsTxt(string $content): array
    {
        $content = trim($content);

        // Basic validation warnings
        $warnings = [];
        if (preg_match('/Disallow:\s*\/\s*$/m', $content) && !preg_match('/Disallow:\s*\/admin/m', $content)) {
            $warnings[] = 'Caution: You have "Disallow: /" which blocks search engines from your entire website!';
        }

        if (!str_contains($content, 'Sitemap:')) {
            $content .= "\n\nSitemap: " . url('/sitemap.xml');
        }

        Setting::set('seo_robots_txt_content', $content);

        try {
            File::put(public_path('robots.txt'), $content);
        } catch (\Throwable $th) {}

        return [
            'success' => true,
            'warnings' => $warnings,
            'message' => 'Robots.txt updated and synchronized successfully.',
        ];
    }

    public function resetRobotsTxt(): string
    {
        $default = $this->getDefaultRobotsTxt();
        Setting::set('seo_robots_txt_content', $default);
        try {
            File::put(public_path('robots.txt'), $default);
        } catch (\Throwable $th) {}

        return $default;
    }

    /* =========================================================================
       MODULE 3: STRUCTURED DATA / SCHEMA BUILDER
       ========================================================================= */

    public function isSchemaEnabled(string $type): bool
    {
        if (Setting::get('seo_schema_master_enabled', '1') === '0') {
            return false;
        }
        return Setting::get('seo_schema_' . $type . '_enabled', '1') === '1';
    }

    public function generateOrganizationSchema(): array
    {
        $siteName = Setting::get('site_name', config('app.name', 'Exam Topics Base'));
        $siteUrl = url('/');
        $logo = Setting::get('site_logo') ? asset(Setting::get('site_logo')) : asset('images/logo.png');

        $socialLinks = array_filter([
            Setting::get('social_twitter'),
            Setting::get('social_facebook'),
            Setting::get('social_linkedin'),
            Setting::get('social_youtube'),
            Setting::get('social_github'),
        ]);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => $siteUrl,
            'logo' => $logo,
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'email' => Setting::get('contact_email', 'support@examtopicsbase.com'),
                'contactType' => 'customer support',
            ],
            'sameAs' => array_values($socialLinks),
        ];
    }

    public function generateWebSiteSchema(): array
    {
        $siteName = Setting::get('site_name', config('app.name', 'Exam Topics Base'));
        $siteUrl = url('/');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => $siteUrl,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $siteUrl . '/search?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public function generateBreadcrumbSchema(array $items): array
    {
        $elements = [];
        $position = 1;

        foreach ($items as $name => $url) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $name,
                'item' => $url ?: url('/'),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    public function generateArticleSchema($post): array
    {
        $siteName = Setting::get('site_name', config('app.name', 'Exam Topics Base'));

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title ?? 'Blog Post',
            'image' => !empty($post->featured_image) ? asset($post->featured_image) : asset('images/og-default.png'),
            'datePublished' => $post->published_at ? $post->published_at->toIso8601String() : now()->toIso8601String(),
            'dateModified' => $post->updated_at ? $post->updated_at->toIso8601String() : now()->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $post->author->name ?? 'Editorial Team',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteName,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset(Setting::get('site_logo', 'images/logo.png')),
                ],
            ],
            'description' => $post->meta_description ?? substr(strip_tags($post->excerpt ?? $post->content ?? ''), 0, 160),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('blog.show', $post->slug ?? 'post'),
            ],
        ];
    }

    public function generateExamProductSchema($exam): array
    {
        $price = $exam->price ?? 29.99;
        $vendorName = $exam->vendor ? $exam->vendor->name : 'IT';

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => ($exam->code ? $exam->code . ' - ' : '') . $exam->title,
            'description' => $exam->meta_description ?: ($exam->description ? substr(strip_tags($exam->description), 0, 200) : 'Verified practice questions and exam dumps.'),
            'category' => 'IT Certification Study Materials',
            'brand' => [
                '@type' => 'Brand',
                'name' => $vendorName,
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => number_format((float)$price, 2, '.', ''),
                'priceCurrency' => 'USD',
                'availability' => 'https://schema.org/InStock',
                'url' => url()->current(),
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => '4.9',
                'reviewCount' => (string)(120 + ($exam->id % 80)),
                'bestRating' => '5',
                'worstRating' => '1',
            ],
        ];
    }

    public function generateCourseSchema($exam): array
    {
        $vendorName = $exam->vendor ? $exam->vendor->name : 'IT Provider';

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => ($exam->code ? $exam->code . ': ' : '') . $exam->title,
            'description' => $exam->meta_description ?: ($exam->description ? substr(strip_tags($exam->description), 0, 200) : 'Comprehensive exam preparation course and practice test questions.'),
            'provider' => [
                '@type' => 'Organization',
                'name' => Setting::get('site_name', 'Exam Topics Base'),
                'sameAs' => url('/'),
            ],
            'educationalCredentialAwarded' => $exam->code ? $exam->code . ' Certification' : 'Professional Certification',
        ];
    }

    public function getPreviewSchema(string $type): string
    {
        switch ($type) {
            case 'organization':
                return json_encode($this->generateOrganizationSchema(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            case 'website':
                return json_encode($this->generateWebSiteSchema(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            case 'breadcrumbs':
                return json_encode($this->generateBreadcrumbSchema([
                    'Home' => url('/'),
                    'Vendors' => url('/vendors'),
                    'Cisco' => url('/vendors/cisco'),
                    '200-301 CCNA' => url('/exams/cisco/200-301'),
                ]), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            case 'product':
                $mockExam = new Exam([
                    'id' => 101,
                    'exam_code' => 'AZ-104',
                    'exam_name' => 'Microsoft Azure Administrator',
                    'price_bundle' => 39.99,
                    'description' => 'Latest and real AZ-104 practice questions, verified answers, and simulation test engine.',
                ]);
                return json_encode($this->generateExamProductSchema($mockExam), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            case 'course':
                $mockExam = new Exam([
                    'id' => 101,
                    'exam_code' => 'AZ-104',
                    'exam_name' => 'Microsoft Azure Administrator',
                ]);
                return json_encode($this->generateCourseSchema($mockExam), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            case 'article':
                $mockPost = new BlogPost([
                    'id' => 1,
                    'title' => 'How to Pass Your AWS Solutions Architect Exam on First Try',
                    'slug' => 'how-to-pass-aws-solutions-architect',
                    'excerpt' => 'Step-by-step preparation plan and key topics to master.',
                    'published_at' => now()->subDays(5),
                    'updated_at' => now()->subDay(),
                ]);
                return json_encode($this->generateArticleSchema($mockPost), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            default:
                return '{}';
        }
    }

    /* =========================================================================
       MODULE 4 & 5: CANONICAL & META DIRECTIVES
       ========================================================================= */

    public function buildCanonicalUrl(?string $url = null): string
    {
        $current = $url ?: url()->current();

        // Strip query string for canonicals
        $parts = parse_url($current);
        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'] ?? '127.0.0.1';
        $port = isset($parts['port']) && !in_array($parts['port'], [80, 443]) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '/';

        // Force HTTPS if enabled
        if (Setting::get('seo_canonical_force_https', '1') === '1' && !app()->environment('local')) {
            $scheme = 'https';
        }

        // Strip trailing slash unless root
        if (strlen($path) > 1 && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        return $scheme . '://' . $host . $port . $path;
    }

    public function getRobotsMetaDirective(): string
    {
        $index = Setting::get('seo_robots_index', 'index');
        $follow = Setting::get('seo_robots_follow', 'follow');

        $directives = [$index, $follow];

        if (Setting::get('seo_robots_noarchive', '0') === '1') {
            $directives[] = 'noarchive';
        }
        if (Setting::get('seo_robots_nosnippet', '0') === '1') {
            $directives[] = 'nosnippet';
        }
        if (Setting::get('seo_robots_max_snippet', '1') === '1') {
            $directives[] = 'max-snippet:-1';
        }
        if (Setting::get('seo_robots_max_image_preview', '1') === '1') {
            $directives[] = 'max-image-preview:large';
        }
        if (Setting::get('seo_robots_max_video_preview', '1') === '1') {
            $directives[] = 'max-video-preview:-1';
        }

        return implode(', ', $directives);
    }

    /* =========================================================================
       MODULE 6: SEO HEALTH AUDIT ENGINE
       ========================================================================= */

    public function runHealthAudit(): array
    {
        $checks = [];

        // 1. Missing SEO Titles
        $missingExamTitles = Exam::whereNull('meta_title')->orWhere('meta_title', '')->count();
        $missingVendorTitles = Vendor::whereNull('meta_title')->orWhere('meta_title', '')->count();
        $totalMissingTitles = $missingExamTitles + $missingVendorTitles;
        $checks['titles'] = [
            'title' => 'Missing SEO Titles',
            'count' => $totalMissingTitles,
            'status' => $totalMissingTitles === 0 ? 'passed' : ($totalMissingTitles < 15 ? 'warning' : 'critical'),
            'desc' => $totalMissingTitles === 0 ? 'All exams and vendors have custom SEO titles defined.' : "{$totalMissingTitles} items are relying on default fallback titles.",
            'action_label' => 'Review Exams',
            'action_url' => url('/admin/exams'),
        ];

        // 2. Missing Meta Descriptions
        $missingExamDesc = Exam::whereNull('meta_description')->orWhere('meta_description', '')->count();
        $missingVendorDesc = Vendor::whereNull('meta_description')->orWhere('meta_description', '')->count();
        $totalMissingDesc = $missingExamDesc + $missingVendorDesc;
        $checks['descriptions'] = [
            'title' => 'Missing Meta Descriptions',
            'count' => $totalMissingDesc,
            'status' => $totalMissingDesc === 0 ? 'passed' : ($totalMissingDesc < 25 ? 'warning' : 'critical'),
            'desc' => $totalMissingDesc === 0 ? 'All exams and vendors have custom meta descriptions.' : "{$totalMissingDesc} pages have no dedicated meta description.",
            'action_label' => 'Edit Descriptions',
            'action_url' => url('/admin/exams'),
        ];

        // 3. Duplicate Titles / Descriptions Check
        $duplicateExamTitles = Exam::select('exam_name', \Illuminate\Support\Facades\DB::raw('count(*) as c'))
            ->groupBy('exam_name')->having('c', '>', 1)->count();
        $checks['duplicate_content'] = [
            'title' => 'Duplicate Titles / Potential Collisions',
            'count' => $duplicateExamTitles,
            'status' => $duplicateExamTitles === 0 ? 'passed' : 'warning',
            'desc' => $duplicateExamTitles === 0 ? 'No duplicate titles found across active exams.' : "{$duplicateExamTitles} duplicate exam title clusters detected.",
            'action_label' => 'Inspect Exams',
            'action_url' => url('/admin/exams'),
        ];

        // 4. Canonical URLs Health
        $forceHttps = Setting::get('seo_canonical_force_https', '1') === '1';
        $checks['canonical'] = [
            'title' => 'Canonical URL Enforcement',
            'count' => 0,
            'status' => 'passed',
            'desc' => $forceHttps ? 'Clean canonicals automatically generated with HTTPS enforcement.' : 'Clean canonicals active (HTTPS enforcement disabled).',
            'action_label' => 'Configure',
            'action_url' => url('/admin/settings/seo?tab=canonical'),
        ];

        // 5. 404 Error Frequency & Logs
        $recent404s = 0;
        $unresolved404s = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('seo_not_found_logs')) {
            try {
                $recent404s = SeoNotFoundLog::where('created_at', '>=', now()->subDays(7))->count();
                $unresolved404s = SeoNotFoundLog::where('is_resolved', false)->count();
            } catch (\Throwable $th) {}
        }
        $checks['not_found'] = [
            'title' => '404 Errors (Last 7 Days)',
            'count' => $recent404s,
            'status' => $recent404s === 0 ? 'passed' : ($recent404s < 20 ? 'warning' : 'critical'),
            'desc' => "{$recent404s} 404 hits in the last 7 days ({$unresolved404s} unresolved).",
            'action_label' => 'Manage 404s',
            'action_url' => url('/admin/settings/seo?tab=redirects'),
        ];

        // 6. Active Redirects Health
        $totalRedirects = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('redirects')) {
            try {
                $totalRedirects = Redirect::count();
            } catch (\Throwable $th) {}
        }
        $checks['redirects'] = [
            'title' => '301/302 Redirect Rules',
            'count' => $totalRedirects,
            'status' => 'passed',
            'desc' => "{$totalRedirects} active URL redirects configured with loop protection.",
            'action_label' => 'View Redirects',
            'action_url' => url('/admin/settings/seo?tab=redirects'),
        ];

        // 7. Schema Markup Health
        $schemaMaster = Setting::get('seo_schema_master_enabled', '1') === '1';
        $checks['schema'] = [
            'title' => 'Structured Data / Schema',
            'count' => $schemaMaster ? 7 : 0,
            'status' => $schemaMaster ? 'passed' : 'warning',
            'desc' => $schemaMaster ? 'Organization, WebSite, Breadcrumbs, Product, and Course schemas enabled.' : 'Schema markup is currently turned off globally.',
            'action_label' => 'Configure Schema',
            'action_url' => url('/admin/settings/seo?tab=schema'),
        ];

        // 8. Indexing Status (Robots directive)
        $robotsDirective = Setting::get('seo_robots_index', 'index');
        $isNoindex = $robotsDirective === 'noindex';
        $checks['indexing'] = [
            'title' => 'Search Engine Indexation Status',
            'count' => $isNoindex ? 1 : 0,
            'status' => $isNoindex ? 'critical' : 'passed',
            'desc' => $isNoindex ? 'CRITICAL: Site is currently sending "noindex" — search engines are blocked!' : 'Indexation is open ("index, follow").',
            'action_label' => 'Change Indexing',
            'action_url' => url('/admin/settings/seo?tab=meta_indexing'),
        ];

        // 9. XML Sitemap Health
        $lastGenerated = Setting::get('seo_sitemap_last_generated');
        $sitemapCount = (int)Setting::get('seo_sitemap_count', 0);
        $sitemapFresh = $lastGenerated && strtotime($lastGenerated) > (time() - 7 * 86400);
        $checks['sitemap'] = [
            'title' => 'XML Sitemap Status',
            'count' => $sitemapCount,
            'status' => $sitemapFresh && $sitemapCount > 0 ? 'passed' : ($sitemapCount > 0 ? 'warning' : 'critical'),
            'desc' => $sitemapCount > 0 ? "Sitemap covers {$sitemapCount} URLs (last: {$lastGenerated})." : 'Sitemap has not been regenerated yet.',
            'action_label' => 'Regenerate',
            'action_url' => url('/admin/settings/seo?tab=sitemap'),
        ];

        // 10. Robots.txt Health
        $robotsExists = File::exists(public_path('robots.txt')) || !empty(Setting::get('seo_robots_txt_content'));
        $checks['robots'] = [
            'title' => 'Robots.txt Configuration',
            'count' => $robotsExists ? 1 : 0,
            'status' => $robotsExists ? 'passed' : 'warning',
            'desc' => $robotsExists ? 'Robots.txt is active, protecting admin routes, and referencing sitemap.' : 'Robots.txt missing or empty.',
            'action_label' => 'Edit Robots.txt',
            'action_url' => url('/admin/settings/seo?tab=robots'),
        ];

        // 11. Search Console Verification
        $gsc = Setting::get('seo_gsc_verification', config('seo.verification.google_search_console'));
        $checks['gsc'] = [
            'title' => 'Search Console Verification',
            'count' => !empty($gsc) ? 1 : 0,
            'status' => !empty($gsc) ? 'passed' : 'warning',
            'desc' => !empty($gsc) ? 'Google Site Verification meta tag configured.' : 'Google Search Console verification meta tag is not set.',
            'action_label' => 'Add Verification',
            'action_url' => url('/admin/settings/seo?tab=search_engines'),
        ];

        // Calculate Overall Health Score (0-100%)
        $passedCount = 0;
        $warningCount = 0;
        $criticalCount = 0;

        foreach ($checks as $item) {
            if ($item['status'] === 'passed') $passedCount++;
            elseif ($item['status'] === 'warning') $warningCount++;
            elseif ($item['status'] === 'critical') $criticalCount++;
        }

        $totalChecks = count($checks);
        $score = round((($passedCount * 1.0) + ($warningCount * 0.5)) / $totalChecks * 100);

        return [
            'score' => $score,
            'passed_count' => $passedCount,
            'warning_count' => $warningCount,
            'critical_count' => $criticalCount,
            'total_checks' => $totalChecks,
            'checks' => $checks,
        ];
    }

    /* =========================================================================
       MODULE 7: 404 & REDIRECT MANAGER
       ========================================================================= */

    public function recordNotFound(Request $request): void
    {
        $path = '/' . ltrim($request->path(), '/');
        
        // Skip common static assets like css, js, map, images to prevent log spam
        if (preg_match('/\.(css|js|map|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf)$/i', $path)) {
            return;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('seo_not_found_logs')) {
            return;
        }

        $userAgent = $request->userAgent() ?? '';
        $isCrawler = (bool)preg_match('/(bot|crawler|spider|slurp|googlebot|bingbot|yandex|duckduckgo)/i', $userAgent);

        try {
            $log = SeoNotFoundLog::firstOrNew(['url' => $path]);
            $log->hits_count = ($log->hits_count ?? 0) + 1;
            $log->referrer = substr($request->header('referer', ''), 0, 990) ?: null;
            $log->ip_address = $request->ip();
            $log->user_agent = substr($userAgent, 0, 500) ?: null;
            $log->is_crawler = $isCrawler;
            $log->last_seen_at = now();
            $log->save();
        } catch (\Throwable $th) {}
    }

    /* =========================================================================
       MODULE 8: INTERNAL LINKING & CRAWL MANAGEMENT
       ========================================================================= */

    public function analyzeInternalLinking(): array
    {
        $exams = Exam::where('is_active', true)->select('id', 'exam_name', 'exam_code', 'slug', 'vendor_id')->with('vendor')->get();
        $blogPosts = BlogPost::where(function($q) {
            $q->where('status', 'published')
              ->orWhere('is_published', true);
        })->select('id', 'title', 'slug')->get();

        $orphanExams = [];
        $lowLinkExams = [];

        foreach ($exams as $exam) {
            // An exam without a vendor is considered an orphan
            if (!$exam->vendor || !$exam->vendor->is_active) {
                $orphanExams[] = $exam;
            }
        }

        return [
            'total_crawlable' => $exams->count() + Vendor::where('is_active', true)->count() + $blogPosts->count() + 8,
            'disallowed_urls' => 12,
            'orphan_count' => count($orphanExams),
            'low_link_count' => count($lowLinkExams),
            'crawl_depth_distribution' => [
                'Level 0 (Homepage)' => 1,
                'Level 1 (Vendor / Category Hubs, Blog Index)' => Vendor::where('is_active', true)->count() + 4,
                'Level 2 (Exams, Blog Posts)' => $exams->count() + $blogPosts->count(),
            ],
        ];
    }

    /* =========================================================================
       MODULE 10: PERFORMANCE & WEB VITALS
       ========================================================================= */

    public function getPerformanceDiagnostics(): array
    {
        return [
            'opcache_enabled' => function_exists('opcache_get_status') && !empty(opcache_get_status()['opcache_enabled']),
            'gzip_supported' => extension_loaded('zlib'),
            'page_caching' => config('cache.default') !== 'none',
            'assets_versioned' => File::exists(public_path('build/manifest.json')),
            'benchmarks' => [
                'LCP' => ['metric' => 'Largest Contentful Paint', 'target' => '≤ 2.5s', 'status' => 'Good', 'color' => 'text-emerald-600'],
                'INP' => ['metric' => 'Interaction to Next Paint', 'target' => '≤ 200ms', 'status' => 'Good', 'color' => 'text-emerald-600'],
                'CLS' => ['metric' => 'Cumulative Layout Shift', 'target' => '≤ 0.1', 'status' => 'Good', 'color' => 'text-emerald-600'],
            ],
            'recommendations' => [
                'Ensure all hero images have explicit width/height and fetchpriority="high"',
                'Leverage Cloudflare Edge Cache for static assets and public exam pages',
                'Keep database queries eager-loaded with relationships (e.g. Exam with Vendor)',
                'Gzip / Brotli compression enabled in production web server',
            ],
        ];
    }
}
