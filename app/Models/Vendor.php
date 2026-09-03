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
        if (!$this->logo_path) {
            return null;
        }

        if (str_starts_with($this->logo_path, 'http://') || str_starts_with($this->logo_path, 'https://')) {
            return $this->logo_path;
        }

        if (str_starts_with($this->logo_path, '/storage/')) {
            return asset($this->logo_path);
        }

        return asset('storage/' . ltrim($this->logo_path, '/'));
    }
}

