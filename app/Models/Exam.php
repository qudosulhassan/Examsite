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
            if ($exam->isDirty('slug')) {
                Redirect::create([
                    'old_url' => 'exams/' . $exam->getOriginal('slug'),
                    'new_url' => 'exams/' . $exam->slug,
                    'status_code' => 301,
                ]);
            }
        });

        static::saved(function ($exam) {
            // Trigger sitemap regeneration
            Artisan::call('sitemap:generate');
        });

        static::deleted(function ($exam) {
            Artisan::call('sitemap:generate');
        });
    }

    protected $fillable = [
        'vendor_id',
        'exam_code',
        'exam_name',
        'slug',
        'description',
        'topics',
        'question_count',
        'passing_score',
        'difficulty',
        'exam_type',
        'price_pdf',
        'price_engine',
        'demo_pdf_filename',
        'full_pdf_filename',
        'last_updated_at',
        'is_active',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'topics' => 'array',
        'is_active' => 'boolean',
        'last_updated_at' => 'datetime',
        'price_pdf' => 'decimal:2',
        'price_engine' => 'decimal:2',
        'sort_order' => 'integer',
        'question_count' => 'integer',
        'passing_score' => 'integer',
    ];

    /**
     * Get the vendor that owns the exam.
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
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
}
