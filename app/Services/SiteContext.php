<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Setting;
use App\Models\Site;
use App\Models\SiteDomain;
use App\Models\SiteExamOverlay;
use App\Models\SiteSetting;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class SiteContext
{
    protected ?Site $site = null;
    protected ?int $fixedSiteId = null;
    protected ?string $mode = null;

    public function __construct(?int $fixedSiteId = null, ?string $mode = null)
    {
        $this->fixedSiteId = $fixedSiteId;
        $this->mode = $mode;
    }

    /**
     * Set explicit fixed site ID at runtime.
     */
    public function setFixedSiteId(?int $id): self
    {
        $this->fixedSiteId = $id;
        $this->site = null;
        return $this;
    }

    /**
     * Set operating mode at runtime ('standalone' or 'dynamic').
     */
    public function setMode(?string $mode): self
    {
        $this->mode = $mode;
        $this->site = null;
        return $this;
    }

    /**
     * Get current operating mode.
     */
    public function getMode(): string
    {
        return $this->mode ?: config('site.mode', env('APP_SITE_MODE', 'standalone'));
    }

    /**
     * Check if currently running in standalone (fixed site) mode.
     */
    public function isStandalone(): bool
    {
        return $this->getMode() === 'standalone';
    }

    /**
     * Manually set the active Site instance in memory.
     */
    public function setSite(?Site $site): self
    {
        $this->site = $site;
        if ($site) {
            app()->instance('current_site', $site);
            view()->share('currentSite', $site);
        }
        return $this;
    }

    /**
     * Clear current site from memory (useful for testing).
     */
    public function clear(): void
    {
        $this->site = null;
    }

    /**
     * Resolve site based on current mode and host.
     */
    public function resolve(?string $host = null): ?Site
    {
        // 1. Standalone / fixed site mode: resolve directly by site ID
        $fixedId = $this->fixedSiteId ?? config('site.id', env('APP_SITE_ID'));
        if ($this->isStandalone() && !empty($fixedId)) {
            $site = Cache::remember("site_fixed_{$fixedId}", 3600, function () use ($fixedId) {
                return Site::with('domains')->where('is_active', true)->find($fixedId);
            });

            if ($site) {
                $this->site = $site;
                return $site;
            }
        }

        // 2. Dynamic host-based resolution
        if (!empty($host)) {
            $cleanHost = strtolower(trim(explode(':', $host)[0]));
            $site = Cache::remember("site_domain_{$cleanHost}", 3600, function () use ($cleanHost) {
                $domainRecord = SiteDomain::with('site')->where('domain', $cleanHost)->first();
                if ($domainRecord && $domainRecord->site && $domainRecord->site->is_active) {
                    return $domainRecord->site;
                }

                return Site::with('domains')->where('is_active', true)->orderBy('id')->first();
            });

            if ($site) {
                $this->site = $site;
                return $site;
            }
        }

        // 3. Fallback to default root site (ID 1)
        $site = Cache::remember('site_default_root', 3600, function () {
            return Site::with('domains')->where('is_active', true)->orderBy('id')->first();
        });

        $this->site = $site;
        return $site;
    }

    /**
     * Get the active Site instance.
     */
    public function site(): ?Site
    {
        // 1. If an explicit instance was bound in container via app()->instance('current_site', $site)
        if (app()->bound('current_site')) {
            $bound = app('current_site');
            if ($bound instanceof Site) {
                $this->site = $bound;
                return $this->site;
            }
        }

        if ($this->site) {
            return $this->site;
        }

        return $this->resolve();
    }

    /**
     * Get the active site ID.
     */
    public function id(): ?int
    {
        return $this->site()?->id;
    }

    /**
     * Determine if active site is the root master site (ID 1 / ExamTopicsBase).
     */
    public function isRoot(): bool
    {
        return ($this->id() ?? 1) === 1;
    }

    /**
     * Get the active theme name.
     */
    public function theme(): string
    {
        return $this->site()?->default_theme ?? 'default';
    }

    /**
     * Get site setting with fallbacks.
     * Hierarchy: direct site column -> site_settings table -> global Setting -> default.
     */
    public function setting(string $key, mixed $default = null): mixed
    {
        $site = $this->site();
        if ($site) {
            if (isset($site->attributes[$key]) && !empty($site->attributes[$key])) {
                return $site->attributes[$key];
            }

            $siteSetting = SiteSetting::where('site_id', $site->id)->where('key', $key)->first();
            if ($siteSetting && $siteSetting->value !== null && $siteSetting->value !== '') {
                return $siteSetting->value;
            }
        }

        return Setting::get($key, $default);
    }

    /**
     * Get comprehensive branding array for the site.
     */
    public function branding(): array
    {
        $site = $this->site();

        return [
            'name'            => $site?->name ?? Setting::get('site_name', config('app.name', 'ExamTopicsBase')),
            'logo'            => (!empty($site?->logo)) ? $site->logo : Setting::get('site_logo', 'images/logo.png'),
            'favicon'         => (!empty($site?->favicon)) ? $site->favicon : Setting::get('site_favicon', 'favicon-32x32.png'),
            'primary_color'   => $site?->primary_color ?: '#2563eb',
            'secondary_color' => $site?->secondary_color ?: '#1e40af',
            'contact_email'   => $site?->contact_email ?: Setting::get('contact_email', 'support@examtopicsbase.com'),
            'contact_phone'   => $site?->contact_phone ?: Setting::get('contact_phone', ''),
            'contact_address' => $site?->contact_address ?: Setting::get('contact_address', ''),
        ];
    }

    /**
     * Get site SEO defaults.
     */
    public function seo(): array
    {
        $site = $this->site();
        $brandName = $site?->name ?? Setting::get('site_name', config('seo.site_name', 'Exam Topics Base'));

        return [
            'site_name'                  => $brandName,
            'default_title'              => $site?->default_seo_title ?: Setting::get('default_seo_title', config('seo.defaults.title', 'Exam Topics Base')),
            'default_description'        => $site?->default_meta_description ?: Setting::get('default_meta_description', config('seo.defaults.description', '')),
            'default_h1'                 => $site?->default_h1 ?: '',
            'primary_keyword_strategy'   => $site?->primary_keyword_strategy ?: Setting::get('default_meta_keywords', ''),
            'secondary_keyword_strategy' => $site?->secondary_keyword_strategy ?: '',
            'robots_directive'           => $site?->robots_directive ?: Setting::get('robots_meta_directive', 'index, follow'),
            'canonical_strategy'         => $site?->canonical_strategy ?: 'self',
            'google_search_console_code' => $site?->google_search_console_code ?: Setting::get('seo_gsc_verification', config('seo.verification.google_search_console', '')),
            'google_analytics_id'        => $site?->google_analytics_id ?: Setting::get('seo_ga4_id', config('seo.analytics.google_analytics_id', '')),
            'twitter_handle'             => $site?->twitter_handle ?: Setting::get('social_twitter', '@examtopicsbase'),
            'og_image'                   => $site?->og_image ?: Setting::get('seo_og_default_image', 'images/og-default.png'),
        ];
    }

    /**
     * Get SiteExamOverlay for a given exam and current site.
     */
    public function getOverlayForExam(Exam|int $exam): ?SiteExamOverlay
    {
        $site = $this->site();
        if (!$site) {
            return null;
        }

        $examId = $exam instanceof Exam ? $exam->id : $exam;

        if ($exam instanceof Exam && $exam->relationLoaded('overlays')) {
            return $exam->overlays->firstWhere('site_id', $site->id);
        }

        return SiteExamOverlay::where('site_id', $site->id)->where('exam_id', $examId)->first();
    }

    /**
     * Determine if an exam is visible on the current site.
     * Rules:
     * - Inactive exams globally are NEVER visible.
     * - On Root Site (ID 1): Exam is visible unless an overlay explicitly sets is_active = 0.
     * - On Secondary Sites (ID > 1): Exam is ONLY visible if an active overlay exists.
     */
    public function isExamVisible(Exam|int $exam): bool
    {
        $examModel = $exam instanceof Exam ? $exam : Exam::find($exam);
        if (!$examModel || !$examModel->is_active) {
            return false;
        }

        $overlay = $this->getOverlayForExam($examModel);

        if ($this->isRoot()) {
            return $overlay ? (bool)$overlay->is_active : true;
        }

        return $overlay ? (bool)$overlay->is_active : false;
    }

    /**
     * Scope an Exam Eloquent query to only include exams visible to the current site.
     */
    public function scopeVisibleExams(Builder $query, bool $indexedOnly = false): Builder
    {
        $site = $this->site();
        $isRoot = $this->isRoot();

        $query->where('is_active', true);

        if (!$isRoot && $site) {
            // Secondary site: must have overlay assigned and active (and indexed if requested)
            $query->whereHas('overlays', function ($q) use ($site, $indexedOnly) {
                $q->where('site_id', $site->id)->where('is_active', true);
                if ($indexedOnly) {
                    $q->where('is_indexed', true);
                }
            });
        } elseif ($isRoot && $site) {
            // Root site: exclude any exam with an overlay explicitly set to is_active = 0
            $query->whereDoesntHave('overlays', function ($q) use ($site) {
                $q->where('site_id', $site->id)->where('is_active', false);
            });
        }

        return $query;
    }

    /**
     * Resolve site-specific or global article content for an exam.
     */
    public function resolveExamArticleContent(Exam $exam): ?string
    {
        $overlay = $this->getOverlayForExam($exam);
        if ($overlay && !empty(trim($overlay->custom_article_content ?? ''))) {
            return $overlay->custom_article_content;
        }
        return $exam->article_content;
    }

    /**
     * Resolve site-specific or global FAQs for an exam.
     */
    public function resolveExamFaqs(Exam $exam): array
    {
        $overlay = $this->getOverlayForExam($exam);
        if ($overlay && !empty($overlay->custom_faqs)) {
            return is_array($overlay->custom_faqs) ? $overlay->custom_faqs : (json_decode($overlay->custom_faqs, true) ?: []);
        }
        return is_array($exam->faqs) ? $exam->faqs : (json_decode($exam->faqs, true) ?: []);
    }

    /**
     * Resolve site-specific or global price for an exam.
     */
    public function resolveExamPrice(Exam $exam, string $type = 'bundle'): float
    {
        $overlay = $this->getOverlayForExam($exam);

        if ($type === 'engine') {
            if ($overlay && $overlay->custom_price_engine !== null) {
                return (float)$overlay->custom_price_engine;
            }
            return (float)($exam->price_engine ?? 39.99);
        }

        if ($type === 'pdf') {
            if ($overlay && $overlay->custom_price_pdf !== null) {
                return (float)$overlay->custom_price_pdf;
            }
            return (float)($exam->price_pdf ?? 29.99);
        }

        // Default 'bundle'
        if ($overlay && $overlay->custom_price_bundle !== null) {
            return (float)$overlay->custom_price_bundle;
        }
        return (float)($exam->price_bundle ?? $exam->price_pdf ?? 49.99);
    }

    /**
     * Safely flush only this site's cached keys from the cache store.
     * When using database/redis store, global Cache::flush() would wipe other sites' caches.
     */
    public static function flushSiteCache(): void
    {
        $prefix = config('cache.prefix', '');
        if (!empty($prefix) && config('cache.default') === 'database') {
            try {
                \Illuminate\Support\Facades\DB::table('cache')
                    ->where('key', 'like', $prefix . '%')
                    ->delete();
                return;
            } catch (\Throwable $e) {
                // Fallback to standard flush if cache table query fails
            }
        }

        \Illuminate\Support\Facades\Cache::flush();
    }
}
