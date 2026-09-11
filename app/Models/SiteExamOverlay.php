<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteExamOverlay extends Model
{
    protected $fillable = [
        'site_id',
        'exam_id',
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
    ];

    protected $casts = [
        'custom_faqs' => 'array',
        'is_featured' => 'boolean',
        'is_indexed' => 'boolean',
        'is_active' => 'boolean',
        'custom_price_engine' => 'decimal:2',
        'custom_price_pdf' => 'decimal:2',
        'custom_price_bundle' => 'decimal:2',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
