<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = [
        'name',
        'code',
        'default_theme',
        'default_locale',
        'is_active',
        'logo',
        'favicon',
        'primary_color',
        'secondary_color',
        'contact_email',
        'contact_phone',
        'contact_address',
        'default_seo_title',
        'default_meta_description',
        'default_h1',
        'primary_keyword_strategy',
        'secondary_keyword_strategy',
        'robots_directive',
        'canonical_strategy',
        'og_image',
        'twitter_handle',
        'google_search_console_code',
        'google_analytics_id',
        'custom_head_scripts',
        'custom_footer_scripts',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function domains()
    {
        return $this->hasMany(SiteDomain::class);
    }

    public function primaryDomain()
    {
        return $this->hasOne(SiteDomain::class)->where('is_primary', true);
    }

    public function settings()
    {
        return $this->hasMany(SiteSetting::class);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        if (isset($this->attributes[$key]) && !empty($this->attributes[$key])) {
            return $this->attributes[$key];
        }
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public function examOverlays()
    {
        return $this->hasMany(SiteExamOverlay::class);
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'site_exam_overlays')
            ->withPivot([
                'custom_slug',
                'custom_header_title',
                'custom_h1',
                'custom_description',
                'custom_intro',
                'custom_cta_text',
                'custom_cta_url',
                'custom_article_content',
                'custom_faqs',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'primary_keyword',
                'secondary_keywords',
                'custom_price_engine',
                'custom_price_pdf',
                'custom_price_bundle',
                'is_featured',
                'is_indexed',
                'is_active',
            ])
            ->withTimestamps();
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'site_vendors')->withPivot(['is_active', 'is_featured'])->withTimestamps();
    }

    public function certifications()
    {
        return $this->belongsToMany(Certification::class, 'site_certifications')->withPivot(['is_active'])->withTimestamps();
    }

    /**
     * Get primary host or first domain
     */
    public function getHostAttribute(): string
    {
        $primary = $this->primaryDomain;
        if ($primary) {
            return $primary->domain;
        }
        $first = $this->domains->first();
        return $first ? $first->domain : 'localhost';
    }

    /**
     * Real database metrics for this site
     */
    public function getMetricsAttribute(): array
    {
        // If it's root/default site (id = 1), count all core published records minus inactive overlays
        // If secondary site, count assigned/active records
        $isRoot = $this->id === 1;

        $totalExams = $isRoot
            ? Exam::where('is_active', true)->count()
            : $this->examOverlays()->where('is_active', true)->count();

        $totalVendors = $isRoot
            ? Vendor::where('is_active', true)->count()
            : $this->vendors()->wherePivot('is_active', true)->count();

        $totalQuestions = $isRoot
            ? Question::where('is_active', true)->count()
            : Question::whereIn('exam_id', $this->examOverlays()->where('is_active', true)->pluck('exam_id'))->count();

        $totalArticles = $isRoot
            ? Exam::whereNotNull('article_content')->where('article_content', '!=', '')->count()
            : $this->examOverlays()->whereNotNull('custom_article_content')->where('custom_article_content', '!=', '')->count();

        $indexedPages = $isRoot
            ? Exam::where('is_active', true)->count() + Vendor::where('is_active', true)->count()
            : $this->examOverlays()->where('is_active', true)->where('is_indexed', true)->count();

        return [
            'published_exams' => $totalExams,
            'available_vendors' => $totalVendors,
            'total_questions' => $totalQuestions,
            'total_articles' => $totalArticles,
            'indexed_pages' => $indexedPages,
            'active_domains' => $this->domains()->count(),
        ];
    }

    /**
     * Calculate SEO Health Score (0 - 100%)
     */
    public function getSeoHealthAttribute(): int
    {
        $hasTitle = !empty($this->default_seo_title) ? 25 : 10;
        $hasDesc = !empty($this->default_meta_description) ? 25 : 10;
        $hasRobots = !empty($this->robots_directive) ? 25 : 20;
        $hasKeywords = !empty($this->primary_keyword_strategy) ? 25 : 10;

        return min(100, $hasTitle + $hasDesc + $hasRobots + $hasKeywords);
    }
}
