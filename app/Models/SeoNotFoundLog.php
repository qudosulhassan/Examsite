<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoNotFoundLog extends Model
{
    protected $table = 'seo_not_found_logs';

    protected $fillable = [
        'url',
        'referrer',
        'ip_address',
        'user_agent',
        'is_crawler',
        'hits_count',
        'last_seen_at',
        'is_resolved',
    ];

    protected $casts = [
        'is_crawler' => 'boolean',
        'is_resolved' => 'boolean',
        'hits_count' => 'integer',
        'last_seen_at' => 'datetime',
    ];
}
