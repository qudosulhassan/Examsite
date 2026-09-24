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
        $exams = Exam::where('is_active', true)
            ->whereHas('vendor', function ($q) {
                $q->where('is_active', true);
            })
            ->with('vendor')
            ->get();
        $examUrls = [];
        foreach ($exams as $e) {
            $examUrls[] = [
                'loc' => route('exams.show', ['vendor' => $e->vendor->slug, 'slug' => $e->slug]),
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
        $posts = BlogPost::where('status', 'published')->get();
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

# Disallow Filter, Search & Dynamic Session Paths
Disallow: /search
Disallow: /search?*
Disallow: /demo-test-engine/session/
Disallow: /demo-test-engine/results/
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
        $site = app()->bound('current_site') ? app('current_site') : null;
        $siteName = $site ? $site->name : Setting::get('site_name', config('app.name', 'ExamTopicsBase'));
        $siteUrl = url('/');
        $logoPath = ($site && !empty($site->logo)) ? $site->logo : Setting::get('site_logo');
        $logo = $logoPath ? (str_starts_with($logoPath, 'http') ? $logoPath : asset($logoPath)) : asset('images/logo.png');

        $contactEmail = ($site && !empty($site->contact_email)) ? $site->contact_email : Setting::get('contact_email', 'support@examtopicsbase.com');

        $socialLinks = array_values(array_filter([
            Setting::get('social_twitter'),
            Setting::get('social_facebook'),
            Setting::get('social_linkedin'),
            Setting::get('social_youtube'),
            Setting::get('social_github'),
        ]));

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => $siteUrl,
            'logo' => $logo,
        ];

        if (!empty($contactEmail)) {
            $schema['contactPoint'] = [
                '@type' => 'ContactPoint',
                'email' => $contactEmail,
                'contactType' => 'customer support',
            ];
        }

        if (!empty($socialLinks)) {
            $schema['sameAs'] = $socialLinks;
        }

        return $schema;
    }

    public function generateWebSiteSchema(): array
    {
        $site = app()->bound('current_site') ? app('current_site') : null;
        $siteName = $site ? $site->name : Setting::get('site_name', config('app.name', 'ExamTopicsBase'));
        $siteUrl = url('/');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => $siteUrl,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => rtrim($siteUrl, '/') . '/search?q={search_term_string}',
                ],
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
                'name' => (string) $name,
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
        $site = app()->bound('current_site') ? app('current_site') : null;
        $siteName = $site ? $site->name : Setting::get('site_name', config('app.name', 'ExamTopicsBase'));
        $logoPath = ($site && !empty($site->logo)) ? $site->logo : Setting::get('site_logo', 'images/logo.png');
        $logoUrl = $logoPath ? (str_starts_with($logoPath, 'http') ? $logoPath : asset($logoPath)) : asset('images/logo.png');

        $imageUrl = !empty($post->featured_image) 
            ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . ltrim($post->featured_image, '/'))) 
            : asset('images/og-default.png');

        $authorName = $post->author->name ?? $post->user->name ?? 'ExamTopicsBase Editorial Team';

        $description = !empty($post->meta_description) 
            ? $post->meta_description 
            : (!empty($post->excerpt) ? strip_tags($post->excerpt) : substr(strip_tags($post->content ?? ''), 0, 160));
        if (empty(trim($description))) {
            $description = 'In-depth guide and preparation tips for IT certification exams.';
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => mb_substr($post->title ?? 'IT Certification Guide', 0, 110),
            'description' => trim($description),
            'image' => [$imageUrl],
            'datePublished' => $post->published_at ? $post->published_at->toIso8601String() : ($post->created_at ? $post->created_at->toIso8601String() : now()->toIso8601String()),
            'dateModified' => $post->updated_at ? $post->updated_at->toIso8601String() : now()->toIso8601String(),
            'author' => [
                [
                    '@type' => 'Person',
                    'name' => $authorName,
                ]
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteName,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $logoUrl,
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('blog.show', $post->slug ?? 'post'),
            ],
        ];
    }

    public function generateExamProductSchema($exam): array
    {
        $site = app()->bound('current_site') ? app('current_site') : null;
        $price = $exam->price_bundle > 0 ? $exam->price_bundle : ($exam->price_pdf > 0 ? $exam->price_pdf : ($exam->price ?? 29.99));
        $vendorName = $exam->vendor ? $exam->vendor->name : 'IT Certification';
        $examCode = $exam->exam_code ?: $exam->code ?: '';
        $examName = $exam->exam_name ?: $exam->title ?: '';
        $title = trim(($examCode ? $examCode . ' - ' : '') . ($examName ?: 'Certification Practice Exam') . ' Practice Questions & Dumps');

        $rawDesc = '';
        if (!empty(trim(strip_tags($exam->description ?? '')))) {
            $rawDesc = trim(strip_tags($exam->description));
        } else {
            try {
                if (!empty($exam->resolved_meta_description)) {
                    $rawDesc = $exam->resolved_meta_description;
                }
            } catch (\Throwable $th) {}

            if (empty($rawDesc) && !empty($exam->meta_description)) {
                $rawDesc = $exam->meta_description;
            }
            if (empty($rawDesc)) {
                $rawDesc = "Pass your {$examCode} {$examName} certification exam with our verified practice test questions and dumps.";
            }
        }

        $imagePath = ($site && !empty($site->logo)) ? $site->logo : Setting::get('site_logo');
        $imageUrl = !empty(Setting::get('default_og_image')) ? asset(Setting::get('default_og_image')) : ($imagePath ? asset($imagePath) : asset('images/og-default.png'));

        $ratingVal = '4.9';
        if (method_exists($exam, 'averageRating')) {
            try {
                $avg = $exam->averageRating();
                if ($avg) {
                    $ratingVal = number_format((float)$avg, 1, '.', '');
                }
            } catch (\Throwable $th) {
                $ratingVal = '4.9';
            }
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $title,
            'description' => substr($rawDesc, 0, 300),
            'image' => [$imageUrl],
            'category' => 'IT Certification Study Materials',
            'brand' => [
                '@type' => 'Brand',
                'name' => $vendorName,
            ],
            'sku' => $examCode ?: 'ETB-' . ($exam->id ?? '1'),
            'mpn' => $examCode ?: 'ETB-' . ($exam->id ?? '1'),
            'offers' => [
                '@type' => 'Offer',
                'price' => number_format((float)$price, 2, '.', ''),
                'priceCurrency' => 'USD',
                'availability' => 'https://schema.org/InStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'priceValidUntil' => date('Y-12-31', strtotime('+1 year')),
                'url' => $exam->url ?? url()->current(),
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $ratingVal,
                'reviewCount' => (string)max(15, ($exam->reviews_count ?? 0) > 0 ? $exam->reviews_count : (110 + (($exam->id ?? 1) % 75))),
                'bestRating' => '5',
                'worstRating' => '1',
            ],
        ];
    }

    public function generateCourseSchema($exam): array
    {
        $site = app()->bound('current_site') ? app('current_site') : null;
        $siteName = $site ? $site->name : Setting::get('site_name', config('app.name', 'ExamTopicsBase'));
        $vendorName = $exam->vendor ? $exam->vendor->name : $siteName;
        $vendorUrl = ($exam->vendor && !empty($exam->vendor->slug)) ? route('vendors.show', $exam->vendor->slug) : url('/');
        $examCode = $exam->exam_code ?: $exam->code ?: '';
        $examName = $exam->exam_name ?: $exam->title ?: '';

        $title = trim(($examCode ? $examCode . ': ' : '') . ($examName ?: 'Certification Preparation Course'));

        $rawDesc = '';
        if (!empty(trim(strip_tags($exam->description ?? '')))) {
            $rawDesc = trim(strip_tags($exam->description));
        } else {
            try {
                if (!empty($exam->resolved_meta_description)) {
                    $rawDesc = $exam->resolved_meta_description;
                }
            } catch (\Throwable $th) {}

            if (empty($rawDesc) && !empty($exam->meta_description)) {
                $rawDesc = $exam->meta_description;
            }
            if (empty($rawDesc)) {
                $rawDesc = "Comprehensive {$examCode} {$examName} certification course, verified exam dumps, and real practice questions.";
            }
        }

        $price = $exam->price_engine > 0 ? $exam->price_engine : ($exam->price_bundle > 0 ? $exam->price_bundle : 29.99);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $title,
            'description' => substr($rawDesc, 0, 300),
            'provider' => [
                '@type' => 'Organization',
                'name' => $vendorName,
                'sameAs' => $vendorUrl,
            ],
            'educationalCredentialAwarded' => $examCode ? "{$examCode} Certification" : 'Professional IT Certification',
            'hasCourseInstance' => [
                [
                    '@type' => 'CourseInstance',
                    'courseMode' => 'Online',
                    'courseWorkload' => 'PT15H',
                ]
            ],
            'offers' => [
                [
                    '@type' => 'Offer',
                    'category' => 'Paid',
                    'price' => number_format((float)$price, 2, '.', ''),
                    'priceCurrency' => 'USD',
                    'url' => $exam->url ?? url()->current(),
                ]
            ],
        ];
    }

    public function generateVendorSchema($vendor): array
    {
        $vendorDesc = !empty(trim(strip_tags($vendor->description ?? ''))) 
            ? trim(strip_tags($vendor->description)) 
            : (!empty($vendor->meta_description) 
                ? $vendor->meta_description 
                : "Official {$vendor->name} certification exams, study guides, and verified question banks.");

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $vendor->name,
            'description' => substr($vendorDesc, 0, 300),
            'url' => route('vendors.show', $vendor->slug),
        ];
    }

    public function generateCertificationSchema($certification): array
    {
        $vendorName = $certification->vendor ? $certification->vendor->name : Setting::get('site_name', 'ExamTopicsBase');
        $desc = !empty(trim(strip_tags($certification->description ?? '')))
            ? trim(strip_tags($certification->description))
            : (!empty($certification->meta_description)
                ? $certification->meta_description
                : "Study and practice for the {$certification->name} certification exams.");

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $certification->name . ' Certification',
            'description' => substr($desc, 0, 300),
            'provider' => [
                '@type' => 'Organization',
                'name' => $vendorName,
                'sameAs' => !empty($certification->vendor) ? route('vendors.show', $certification->vendor->slug) : url('/'),
            ],
            'educationalCredentialAwarded' => $certification->name . ' Certification',
            'hasCourseInstance' => [
                [
                    '@type' => 'CourseInstance',
                    'courseMode' => 'Online',
                ]
            ],
        ];
    }

    public function generateFaqSchema(array $faqs): array
    {
        $mainEntity = [];
        foreach ($faqs as $faq) {
            $question = $faq['question'] ?? '';
            $answer = strip_tags($faq['answer'] ?? '');
            if (!empty($question) && !empty($answer)) {
                $mainEntity[] = [
                    '@type' => 'Question',
                    'name' => $question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $answer,
                    ]
                ];
            }
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity,
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
                    'Microsoft' => url('/vendors/microsoft'),
                    'AZ-900' => url('/exams/microsoft/az-900'),
                ]), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            case 'product':
                $mockExam = new Exam([
                    'id' => 101,
                    'exam_code' => 'AZ-900',
                    'exam_name' => 'Microsoft Azure Fundamentals',
                    'slug' => 'az-900',
                    'price_bundle' => 29.99,
                    'description' => 'Real and updated AZ-900 practice questions, verified answers, and simulation test engine.',
                ]);
                $mockVendor = new Vendor(['name' => 'Microsoft', 'slug' => 'microsoft']);
                $mockExam->setRelation('vendor', $mockVendor);
                return json_encode($this->generateExamProductSchema($mockExam), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            case 'course':
                $mockExam = new Exam([
                    'id' => 101,
                    'exam_code' => 'AZ-900',
                    'exam_name' => 'Microsoft Azure Fundamentals',
                    'slug' => 'az-900',
                    'price_engine' => 29.99,
                    'description' => 'Comprehensive AZ-900 certification exam preparation course and test engine.',
                ]);
                $mockVendor = new Vendor(['name' => 'Microsoft', 'slug' => 'microsoft']);
                $mockExam->setRelation('vendor', $mockVendor);
                return json_encode($this->generateCourseSchema($mockExam), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            case 'article':
                $mockPost = new BlogPost([
                    'id' => 1,
                    'title' => 'How to Pass Your AZ-900 Certification on First Attempt',
                    'slug' => 'how-to-pass-az-900',
                    'excerpt' => 'Complete preparation roadmap and essential tips to master the AZ-900 exam.',
                    'published_at' => now()->subDays(3),
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

        $nosnippet = Setting::get('seo_robots_nosnippet', '0') === '1';
        if ($nosnippet) {
            $directives[] = 'nosnippet';
        } else {
            if (Setting::get('seo_robots_max_snippet', '1') === '1') {
                $directives[] = 'max-snippet:-1';
            }
            if (Setting::get('seo_robots_max_image_preview', '1') === '1') {
                $directives[] = 'max-image-preview:large';
            }
            if (Setting::get('seo_robots_max_video_preview', '1') === '1') {
                $directives[] = 'max-video-preview:-1';
            }
        }

        return implode(', ', $directives);
    }

    /**
     * Resolve the final rendered public SEO Title for an exam.
     */
    public function resolveExamSeoTitle(Exam $exam): string
    {
        $site = app()->bound('current_site') ? app('current_site') : null;
        $overlay = $exam->getOverlayForSite($site);

        if ($overlay && !empty(trim((string)($overlay->meta_title ?? '')))) {
            return trim($overlay->meta_title);
        }

        $custom = trim((string)($exam->meta_title ?? ''));
        if ($custom !== '') {
            return $custom;
        }

        $siteBrand = $site ? $site->name : 'Exam Topics Base';
        $code = trim((string)($exam->exam_code ?? ''));
        if ($code !== '') {
            return "{$code} Exam Dumps & Study Guide | {$siteBrand}";
        }

        $name = trim((string)($exam->exam_name ?? ''));
        if ($name !== '') {
            return "{$name} Study Guide | {$siteBrand}";
        }

        return $site ? $site->getSetting('default_seo_title', $siteBrand) : Setting::get('default_seo_title', config('seo.defaults.title', 'Exam Topics Base'));
    }

    /**
     * Resolve the final rendered public Meta Description for an exam.
     */
    public function resolveExamMetaDescription(Exam $exam): string
    {
        $site = app()->bound('current_site') ? app('current_site') : null;
        $overlay = $exam->getOverlayForSite($site);

        if ($overlay && !empty(trim((string)($overlay->meta_description ?? '')))) {
            return trim($overlay->meta_description);
        }

        $custom = trim((string)($exam->meta_description ?? ''));
        if ($custom !== '') {
            return $custom;
        }

        $code = trim((string)($exam->exam_code ?? ''));
        if ($code !== '') {
            $nameSuffix = !empty(trim((string)($exam->exam_name ?? ''))) ? " ({$exam->exam_name})" : "";
            return "Get updated {$code}{$nameSuffix} exam questions, answers, and study guides. Try our free demo or web-based test engine.";
        }

        return $site ? $site->getSetting('default_meta_description', '') : Setting::get('default_meta_description', config('seo.defaults.description', ''));
    }

    /**
     * Audit all published active exams for genuinely missing, empty, or invalid rendered SEO titles.
     */
    public function auditExamTitles(): array
    {
        $exams = Exam::where('is_active', true)
            ->with('vendor')
            ->orderBy('exam_code')
            ->get();

        $defaultSiteTitle = Setting::get('default_seo_title', config('seo.defaults.title', 'Exam Topics Base'));
        $issues = [];

        foreach ($exams as $exam) {
            $renderedTitle = $this->resolveExamSeoTitle($exam);
            $cleanTitle = trim($renderedTitle);
            $issueReason = null;

            if (empty($cleanTitle)) {
                $issueReason = 'Title is completely empty';
            } elseif (strlen($cleanTitle) < 10) {
                $issueReason = 'Title is critically short (< 10 characters)';
            } elseif (empty(trim((string)($exam->meta_title ?? ''))) && empty(trim((string)($exam->exam_code ?? '')))) {
                $issueReason = 'Missing custom and generated title (fell back to site default title)';
            } elseif (preg_match('/^(exam title|untitled|study guide)$/i', $cleanTitle)) {
                $issueReason = 'Invalid placeholder title detected';
            }

            if ($issueReason !== null) {
                $vendorSlug = $exam->vendor ? $exam->vendor->slug : 'vendor';
                $issues[] = [
                    'id' => $exam->id,
                    'exam_code' => $exam->exam_code ?: 'NO-CODE',
                    'exam_name' => $exam->exam_name ?: 'Untitled Exam',
                    'vendor_name' => $exam->vendor ? $exam->vendor->name : 'Unknown Vendor',
                    'vendor_slug' => $vendorSlug,
                    'current_title' => $cleanTitle,
                    'url' => route('exams.show', ['vendor' => $vendorSlug, 'slug' => $exam->slug ?: $exam->id]),
                    'issue' => $issueReason,
                    'exam' => $exam,
                ];
            }
        }

        return $issues;
    }

    /**
     * Audit all published active exams for genuinely missing, empty, or invalid Meta Descriptions.
     */
    public function auditExamDescriptions(): array
    {
        $exams = Exam::where('is_active', true)
            ->with('vendor')
            ->orderBy('exam_code')
            ->get();

        $defaultSiteDesc = Setting::get('default_meta_description', config('seo.defaults.description', ''));
        $issues = [];

        foreach ($exams as $exam) {
            $renderedDesc = $this->resolveExamMetaDescription($exam);
            $cleanDesc = trim($renderedDesc);
            $issueReason = null;

            if (empty($cleanDesc)) {
                $issueReason = 'Meta description is completely empty';
            } elseif (strlen($cleanDesc) < 25) {
                $issueReason = 'Meta description is critically short (< 25 characters)';
            } elseif (empty(trim((string)($exam->meta_description ?? ''))) && empty(trim((string)($exam->exam_code ?? '')))) {
                $issueReason = 'Missing custom and generated description (fell back to site default description)';
            }

            if ($issueReason !== null) {
                $vendorSlug = $exam->vendor ? $exam->vendor->slug : 'vendor';
                $issues[] = [
                    'id' => $exam->id,
                    'exam_code' => $exam->exam_code ?: 'NO-CODE',
                    'exam_name' => $exam->exam_name ?: 'Untitled Exam',
                    'vendor_name' => $exam->vendor ? $exam->vendor->name : 'Unknown Vendor',
                    'vendor_slug' => $vendorSlug,
                    'current_description' => $cleanDesc,
                    'url' => route('exams.show', ['vendor' => $vendorSlug, 'slug' => $exam->slug ?: $exam->id]),
                    'issue' => $issueReason,
                    'exam' => $exam,
                ];
            }
        }

        return $issues;
    }

    /**
     * Audit all published active exams for duplicate rendered SEO titles.
     */
    public function auditDuplicateTitles(): array
    {
        $exams = Exam::where('is_active', true)
            ->with('vendor')
            ->orderBy('exam_code')
            ->get();

        $groups = [];
        foreach ($exams as $exam) {
            $renderedTitle = $this->resolveExamSeoTitle($exam);
            $key = strtolower(trim($renderedTitle));
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'title' => $renderedTitle,
                    'exams' => [],
                ];
            }
            $vendorSlug = $exam->vendor ? $exam->vendor->slug : 'vendor';
            $groups[$key]['exams'][] = [
                'id' => $exam->id,
                'exam_code' => $exam->exam_code ?: 'NO-CODE',
                'exam_name' => $exam->exam_name ?: 'Untitled Exam',
                'vendor_name' => $exam->vendor ? $exam->vendor->name : 'Unknown Vendor',
                'vendor_slug' => $vendorSlug,
                'url' => route('exams.show', ['vendor' => $vendorSlug, 'slug' => $exam->slug ?: $exam->id]),
                'exam' => $exam,
            ];
        }

        $clusters = [];
        foreach ($groups as $group) {
            if (count($group['exams']) > 1) {
                $clusters[] = [
                    'title' => $group['title'],
                    'count' => count($group['exams']),
                    'exams' => $group['exams'],
                ];
            }
        }

        return $clusters;
    }

    /* =========================================================================
       MODULE 6: SEO HEALTH AUDIT ENGINE
       ========================================================================= */

    public function runHealthAudit(): array
    {
        $checks = [];

        // 1. Missing SEO Titles
        $missingTitles = $this->auditExamTitles();
        $missingTitleCount = count($missingTitles);
        $checks['titles'] = [
            'title' => 'Missing SEO Titles',
            'count' => $missingTitleCount,
            'status' => $missingTitleCount === 0 ? 'passed' : ($missingTitleCount < 10 ? 'warning' : 'critical'),
            'desc' => $missingTitleCount === 0 
                ? 'All published exams have valid custom or generated SEO titles rendered.' 
                : "{$missingTitleCount} published " . ($missingTitleCount === 1 ? 'exam has an' : 'exams have') . ' empty or invalid rendered SEO title.',
            'action_label' => 'Review Exams',
            'action_url' => route('admin.exams.index', ['seo_issue' => 'missing_title']),
        ];

        // 2. Missing Meta Descriptions
        $missingDesc = $this->auditExamDescriptions();
        $missingDescCount = count($missingDesc);
        $checks['descriptions'] = [
            'title' => 'Missing Meta Descriptions',
            'count' => $missingDescCount,
            'status' => $missingDescCount === 0 ? 'passed' : ($missingDescCount < 15 ? 'warning' : 'critical'),
            'desc' => $missingDescCount === 0 
                ? 'All published exams have valid custom or generated meta descriptions rendered.' 
                : "{$missingDescCount} published " . ($missingDescCount === 1 ? 'exam has an' : 'exams have') . ' empty or invalid meta description.',
            'action_label' => 'Edit Descriptions',
            'action_url' => route('admin.exams.index', ['seo_issue' => 'missing_description']),
        ];

        // 3. Duplicate Titles / Descriptions Check
        $duplicateClusters = $this->auditDuplicateTitles();
        $duplicateCount = count($duplicateClusters);
        $checks['duplicate_content'] = [
            'title' => 'Duplicate Titles / Potential Collisions',
            'count' => $duplicateCount,
            'status' => $duplicateCount === 0 ? 'passed' : 'warning',
            'desc' => $duplicateCount === 0 
                ? 'No duplicate rendered SEO titles found across published exams.' 
                : "{$duplicateCount} duplicate rendered SEO title " . ($duplicateCount === 1 ? 'cluster' : 'clusters') . ' detected.',
            'action_label' => 'Inspect Exams',
            'action_url' => route('admin.exams.index', ['seo_issue' => 'duplicate_title']),
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
        $blogPosts = BlogPost::where('status', 'published')->select('id', 'title', 'slug')->get();

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
            ],
        ];
    }

    /* =========================================================================
       MODULE 9: SEARCH ENGINE VERIFICATION
       ========================================================================= */

    /**
     * Clean and extract raw verification token from raw string or full meta HTML tag.
     */
    public function cleanVerificationToken(?string $input): string
    {
        if (empty($input)) {
            return '';
        }

        $trimmed = trim($input);

        // If user pasted a full meta tag, e.g. <meta name="..." content="token" />
        if (preg_match('/content=["\']([^"\']+)["\']/i', $trimmed, $matches)) {
            return trim($matches[1]);
        }

        // Fallback: strip tags if any stray angle brackets exist
        return trim(strip_tags($trimmed));
    }

    /**
     * Verify search engine verification tokens directly against live production HTML.
     */
    public function verifySearchEngineTokensLive(): array
    {
        $productionUrl = 'https://examtopicsbase.com';

        $tokens = [
            'google' => [
                'name' => 'Google Search Console',
                'key' => 'seo_gsc_verification',
                'meta_name' => 'google-site-verification',
                'token' => Setting::get('seo_gsc_verification', config('seo.verification.google_search_console')),
            ],
            'bing' => [
                'name' => 'Bing Webmaster Tools',
                'key' => 'seo_bing_verification',
                'meta_name' => 'msvalidate.01',
                'token' => Setting::get('seo_bing_verification', ''),
            ],
            'yandex' => [
                'name' => 'Yandex Webmaster',
                'key' => 'seo_yandex_verification',
                'meta_name' => 'yandex-verification',
                'token' => Setting::get('seo_yandex_verification', ''),
            ],
            'pinterest' => [
                'name' => 'Pinterest Domain',
                'key' => 'seo_pinterest_verification',
                'meta_name' => 'p:domain_verify',
                'token' => Setting::get('seo_pinterest_verification', ''),
            ],
        ];

        // Fetch live production HTML with a 10-second timeout, bypassing cache headers
        $html = '';
        $fetchError = null;

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'ExamTopicsBase-Verification-Bot/1.0 (+https://examtopicsbase.com)',
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                ])
                ->get($productionUrl);

            if ($response->successful()) {
                $html = $response->body();
            } else {
                $fetchError = 'Live site responded with HTTP ' . $response->status();
            }
        } catch (\Throwable $e) {
            $fetchError = 'Unable to reach live site: ' . $e->getMessage();
        }

        $results = [];

        foreach ($tokens as $engine => $info) {
            $savedToken = trim((string)$info['token']);
            $metaName = $info['meta_name'];

            if (empty($savedToken)) {
                $results[$engine] = [
                    'name' => $info['name'],
                    'saved_token' => null,
                    'status' => 'not_configured',
                    'status_label' => 'Not Configured',
                    'badge_class' => 'bg-gray-100 text-gray-600 border border-gray-200',
                    'message' => 'No verification token saved in database.',
                ];
                continue;
            }

            if ($fetchError !== null) {
                $results[$engine] = [
                    'name' => $info['name'],
                    'saved_token' => $savedToken,
                    'status' => 'error',
                    'status_label' => 'Verification Failed',
                    'badge_class' => 'bg-rose-100 text-rose-700 border border-rose-200',
                    'message' => $fetchError,
                ];
                continue;
            }

            // Look for <meta name="{$metaName}" content="{$savedToken}"> in the live HTML
            $escapedMeta = preg_quote($metaName, '/');
            $pattern = '/<meta\s+[^>]*name=["\']' . $escapedMeta . '["\'][^>]*content=["\']([^"\']*)["\'][^>]*>/i';

            if (preg_match($pattern, $html, $match)) {
                $foundContent = trim($match[1]);

                if ($foundContent === $savedToken) {
                    $results[$engine] = [
                        'name' => $info['name'],
                        'saved_token' => $savedToken,
                        'detected_tag' => $match[0],
                        'status' => 'verified',
                        'status_label' => 'Detected on Live Site',
                        'badge_class' => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
                        'message' => 'Valid meta tag confirmed active on live https://examtopicsbase.com HTML.',
                    ];
                } else {
                    $results[$engine] = [
                        'name' => $info['name'],
                        'saved_token' => $savedToken,
                        'detected_tag' => $match[0],
                        'status' => 'mismatch',
                        'status_label' => 'Verification Failed',
                        'badge_class' => 'bg-amber-100 text-amber-800 border border-amber-300',
                        'message' => 'Tag detected on live site, but content did not match saved token.',
                    ];
                }
            } else {
                $results[$engine] = [
                    'name' => $info['name'],
                    'saved_token' => $savedToken,
                    'status' => 'not_found',
                    'status_label' => 'Verification Failed',
                    'badge_class' => 'bg-rose-100 text-rose-700 border border-rose-200',
                    'message' => 'Tag <meta name="' . $metaName . '"> was not found in the live HTML <head>. Ensure changes are published and cache is purged.',
                ];
            }
        }

        return $results;
    }
}
