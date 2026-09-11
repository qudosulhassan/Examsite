<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteDomain extends Model
{
    protected $fillable = [
        'site_id',
        'domain',
        'is_primary',
        'is_secure',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_secure' => 'boolean',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
