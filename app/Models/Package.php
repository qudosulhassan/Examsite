<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'type',
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_annual',
        'price_lifetime',
        'features',
        'includes_pdf',
        'includes_te',
        'access_days',
        'is_popular',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_annual' => 'decimal:2',
        'price_lifetime' => 'decimal:2',
        'features' => 'array',
        'includes_pdf' => 'boolean',
        'includes_te' => 'boolean',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'access_days' => 'integer',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
