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
        'topics',
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
        return (float)($this->attributes['price_bundle'] ?? $this->attributes['price_pdf'] ?? 29.99);
    }
}
