<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_uses',
        'used_count',
        'per_user_limit',
        'applicable_to',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'per_user_limit' => 'integer',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * The discount amount this coupon takes off a given subtotal, capped so it can
     * never exceed the subtotal itself (a $10-off coupon on a $5 order takes $5, not $10).
     * Single source of truth for this math -- previously duplicated across Cart/Checkout.
     */
    public function calculateDiscount(float $subtotal): float
    {
        $discount = $this->discount_type === 'percentage'
            ? ($subtotal * (float) $this->discount_value) / 100
            : (float) $this->discount_value;

        return min($discount, $subtotal);
    }

    /**
     * Check if coupon is valid for use.
     */
    public function isValid(float $orderTotal = 0.00): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        if ($orderTotal < $this->min_order_amount) {
            return false;
        }

        return true;
    }
}
