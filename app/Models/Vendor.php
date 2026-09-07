<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Artisan;
use App\Models\Redirect;

class Vendor extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::updating(function ($vendor) {
            if ($vendor->isDirty('slug')) {
                Redirect::create([
                    'old_url' => 'vendors/' . $vendor->getOriginal('slug'),
                    'new_url' => 'vendors/' . $vendor->slug,
                    'status_code' => 301,
                ]);
            }
        });

        static::saved(function ($vendor) {
            Artisan::call('sitemap:generate');
        });

        static::deleted(function ($vendor) {
            Artisan::call('sitemap:generate');
        });
    }

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'description',
        'category',
        'exam_count',
        'is_active',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'exam_count' => 'integer',
    ];

    /**
     * Get the actual exams count dynamically.
     * Uses withCount('exams') if loaded, or counts related exams, fallback to column if null.
     */
    public function getExamCountAttribute($value): int
    {
        if (isset($this->attributes['exams_count'])) {
            return (int) $this->attributes['exams_count'];
        }

        if ($this->relationLoaded('exams')) {
            return (int) $this->exams->count();
        }

        // Return the actual count from relationship
        $actualCount = $this->exams()->count();
        return $actualCount > 0 ? $actualCount : (int) ($value ?? 0);
    }

    /**
     * Get the exams for the vendor.
     */
    public function exams()
    {
        return $this->hasMany(Exam::class)->orderBy('sort_order');
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function certifications()
    {
        return $this->hasMany(Certification::class)->orderBy('sort_order');
    }

    /**
     * Get browser-accessible URL for vendor logo
     */
    public function getLogoUrlAttribute(): ?string
    {
        // 1. Check if an official local SVG asset exists for this vendor slug
        $localSvg = 'images/vendors/' . $this->slug . '.svg';
        if (file_exists(public_path($localSvg))) {
            return asset($localSvg);
        }

        if (!$this->logo_path) {
            return null;
        }

        // 2. If it's a full remote URL (e.g. Wikimedia / CDN), return it
        if (str_starts_with($this->logo_path, 'http://') || str_starts_with($this->logo_path, 'https://')) {
            return $this->logo_path;
        }

        // 3. If it's a local storage path, only return asset() if the physical file actually exists!
        $cleaned = ltrim(str_replace('/storage/', '', $this->logo_path), '/');
        if (file_exists(storage_path('app/public/' . $cleaned)) || file_exists(public_path('storage/' . $cleaned))) {
            return asset('storage/' . $cleaned);
        }

        return null;
    }
}

