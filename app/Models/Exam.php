<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Artisan;
use App\Models\Redirect;

class Exam extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::updating(function ($exam) {
            if ($exam->isDirty('slug') && !empty($exam->getOriginal('slug')) && !empty($exam->slug) && $exam->is_active && $exam->vendor) {
                $vendorSlug = $exam->vendor->slug;
                $oldPath = 'exams/' . $vendorSlug . '/' . $exam->getOriginal('slug');
                $newPath = 'exams/' . $vendorSlug . '/' . $exam->slug;

                if ($oldPath !== $newPath && !Redirect::wouldCauseLoop($oldPath, $newPath)) {
                    Redirect::updateOrCreate(
                        ['old_url' => $oldPath],
                        [
                            'new_url' => $newPath,
                            'status_code' => 301,
                            'is_active' => true,
                        ]
                    );
                }
            }
        });

        static::saved(function ($exam) {
            // Recalculate vendor exam count
            if ($exam->vendor_id) {
                $count = static::where('vendor_id', $exam->vendor_id)->count();
                Vendor::where('id', $exam->vendor_id)->update(['exam_count' => $count]);
            }
            // Trigger sitemap regeneration
            Artisan::call('sitemap:generate');
        });

        static::deleted(function ($exam) {
            // Recalculate vendor exam count
            if ($exam->vendor_id) {
                $count = static::where('vendor_id', $exam->vendor_id)->count();
                Vendor::where('id', $exam->vendor_id)->update(['exam_count' => $count]);
            }

            // Remove any redirect rules that point to this deleted exam
            if ($exam->vendor && !empty($exam->slug)) {
                $targetPath = 'exams/' . $exam->vendor->slug . '/' . $exam->slug;
                Redirect::where('new_url', $targetPath)
                    ->orWhere('new_url', '/' . $targetPath)
                    ->delete();
            }

            Artisan::call('sitemap:generate');
        });
    }

    protected $fillable = [
        'vendor_id',
        'exam_code',
        'exam_name',
        'header_title',
        'slug',
        'description',
        'article_content',
        'topics',
        'faqs',
        'question_count',
        'passing_score',
        'difficulty',
        'exam_type',
        'price_pdf',
        'price_engine',
        'price_bundle',
        'is_pdf_available',
        'is_engine_available',
        'is_bundle_available',
        'update_price_3_months',
        'update_price_6_months',
        'update_price_12_months',
        'demo_pdf_filename',
        'full_pdf_filename',
        'last_updated_at',
        'is_active',
        'is_featured',
        'sort_order',
        'admin_notes',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'topics' => 'array',
        'faqs' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_pdf_available' => 'boolean',
        'is_engine_available' => 'boolean',
        'is_bundle_available' => 'boolean',
        'last_updated_at' => 'datetime',
        'price_pdf' => 'decimal:2',
        'price_engine' => 'decimal:2',
        'price_bundle' => 'decimal:2',
        'update_price_3_months' => 'decimal:2',
        'update_price_6_months' => 'decimal:2',
        'update_price_12_months' => 'decimal:2',
        'sort_order' => 'integer',
        'question_count' => 'integer',
        'passing_score' => 'integer',
    ];

    /**
     * Get effective bundle price (explicit price_bundle or fallback 10% discount).
     */
    public function getEffectiveBundlePriceAttribute(): float
    {
        if ($this->price_bundle !== null && (float)$this->price_bundle > 0) {
            return (float)$this->price_bundle;
        }

        return round(((float)$this->price_pdf + (float)$this->price_engine) * 0.90, 2);
    }

    /**
     * Get the vendor that owns the exam.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function certifications()
    {
        return $this->belongsToMany(Certification::class);
    }

    /**
     * Get the questions for the exam.
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the reviews for the exam.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the average rating of the exam reviews.
     */
    public function averageRating()
    {
        return $this->reviews()->where('is_approved', true)->avg('rating') ?: 5.0;
    }

    /**
     * Get public canonical URL for the exam.
     */
    public function getUrlAttribute(): string
    {
        $vendorSlug = $this->vendor ? $this->vendor->slug : 'exam';
        return route('exams.show', ['vendor' => $vendorSlug, 'slug' => $this->slug]);
    }

    /**
     * Get the count of real questions assigned to this exam.
     */
    public function getCalculatedQuestionCountAttribute(): int
    {
        return $this->questions()->count();
    }

    /**
     * Get resolved final rendered public SEO Title.
     */
    public function getResolvedSeoTitleAttribute(): string
    {
        return app(\App\Services\TechnicalSeoService::class)->resolveExamSeoTitle($this);
    }

    /**
     * Get resolved final rendered public Meta Description.
     */
    public function getResolvedMetaDescriptionAttribute(): string
    {
        return app(\App\Services\TechnicalSeoService::class)->resolveExamMetaDescription($this);
    }

    public function overlays()
    {
        return $this->hasMany(SiteExamOverlay::class);
    }

    public function sites()
    {
        return $this->belongsToMany(Site::class, 'site_exam_overlays')
            ->withPivot(['is_active', 'is_featured', 'is_indexed', 'custom_slug', 'custom_header_title', 'custom_h1', 'meta_title', 'meta_description'])
            ->withTimestamps();
    }

    /**
     * Get the overlay record for a given site or the currently active site.
     */
    public function getOverlayForSite(?Site $site = null): ?SiteExamOverlay
    {
        if ($site) {
            if ($this->relationLoaded('overlays')) {
                return $this->overlays->firstWhere('site_id', $site->id);
            }
            return $this->overlays()->where('site_id', $site->id)->first();
        }

        $targetSite = (app()->bound('current_site') && app('current_site') instanceof Site)
            ? app('current_site')
            : (app()->bound(\App\Services\SiteContext::class) ? app(\App\Services\SiteContext::class)->site() : null);

        if (!$targetSite) {
            return null;
        }

        if ($this->relationLoaded('overlays')) {
            return $this->overlays->firstWhere('site_id', $targetSite->id);
        }

        return $this->overlays()->where('site_id', $targetSite->id)->first();
    }

    /**
     * Resolve site-specific or fallback article content.
     */
    public function getResolvedArticleContentAttribute(): ?string
    {
        if (app()->bound(\App\Services\SiteContext::class)) {
            return app(\App\Services\SiteContext::class)->resolveExamArticleContent($this);
        }

        $overlay = $this->getOverlayForSite();
        if ($overlay && !empty(trim($overlay->custom_article_content ?? ''))) {
            return $overlay->custom_article_content;
        }
        return $this->article_content;
    }

    /**
     * Resolve site-specific or fallback FAQs.
     */
    public function getResolvedFaqsAttribute(): array
    {
        if (app()->bound(\App\Services\SiteContext::class)) {
            return app(\App\Services\SiteContext::class)->resolveExamFaqs($this);
        }

        $overlay = $this->getOverlayForSite();
        if ($overlay && !empty($overlay->custom_faqs)) {
            return is_array($overlay->custom_faqs) ? $overlay->custom_faqs : (json_decode($overlay->custom_faqs, true) ?: []);
        }
        return is_array($this->faqs) ? $this->faqs : (json_decode($this->faqs, true) ?: []);
    }

    /**
     * Accessor aliases for compatibility with general SEO and schema builders.
     */
    public function getTitleAttribute(): string
    {
        return $this->attributes['exam_name'] ?? $this->attributes['header_title'] ?? '';
    }

    public function getCodeAttribute(): string
    {
        return $this->attributes['exam_code'] ?? '';
    }

    public function getPriceAttribute(): float
    {
        if (app()->bound(\App\Services\SiteContext::class)) {
            return app(\App\Services\SiteContext::class)->resolveExamPrice($this, 'bundle');
        }

        $overlay = $this->getOverlayForSite();
        if ($overlay && $overlay->custom_price_bundle !== null) {
            return (float)$overlay->custom_price_bundle;
        }
        return (float)($this->attributes['price_bundle'] ?? $this->attributes['price_pdf'] ?? 29.99);
    }
}
