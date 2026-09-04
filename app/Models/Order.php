<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal',
        'discount_amount',
        'total_amount',
        'refunded_amount',
        'coupon_id',
        'payment_method',
        'payment_status',
        'stripe_payment_intent_id',
        'paypal_order_id',
        'billing_name',
        'billing_email',
        'admin_notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
    ];

    /**
     * Get the user that placed the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the coupon applied to the order.
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the items in the order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the refunds for this order.
     */
    public function refunds()
    {
        return $this->hasMany(Refund::class)->orderBy('id', 'desc');
    }

    /**
     * Get the audit timeline for this order.
     */
    public function timelines()
    {
        return $this->hasMany(OrderTimeline::class)->orderBy('id', 'desc');
    }

    /**
     * Get the certification access grants created by this order.
     */
    public function userExams()
    {
        return $this->hasMany(UserExam::class, 'order_id');
    }

    /**
     * Remaining amount available for refund.
     */
    public function remainingRefundableAmount(): float
    {
        $refundable = (float)$this->total_amount - (float)($this->refunded_amount ?? 0);
        return max(0.0, round($refundable, 2));
    }

    /**
     * Check if order can be refunded.
     */
    public function isRefundable(): bool
    {
        $status = strtolower($this->payment_status);
        return $this->remainingRefundableAmount() > 0 && in_array($status, ['paid', 'completed', 'partially_refunded']);
    }

    /**
     * Get semantic status badge classes.
     */
    public function getStatusBadgeAttribute(): array
    {
        $status = strtolower($this->payment_status);

        return match ($status) {
            'paid', 'completed' => [
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'dot' => 'bg-emerald-500',
                'label' => 'Paid',
            ],
            'pending', 'processing' => [
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'dot' => 'bg-amber-500',
                'label' => 'Pending',
            ],
            'partially_refunded' => [
                'bg' => 'bg-purple-50 text-purple-700 border-purple-200',
                'dot' => 'bg-purple-500',
                'label' => 'Partially Refunded',
            ],
            'refunded' => [
                'bg' => 'bg-gray-100 text-gray-700 border-gray-300',
                'dot' => 'bg-gray-500',
                'label' => 'Refunded',
            ],
            'failed', 'cancelled' => [
                'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
                'dot' => 'bg-rose-500',
                'label' => ucfirst($status),
            ],
            default => [
                'bg' => 'bg-gray-50 text-gray-600 border-gray-200',
                'dot' => 'bg-gray-400',
                'label' => ucfirst($status),
            ],
        };
    }
}
