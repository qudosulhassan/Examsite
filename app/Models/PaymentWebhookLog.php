<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentWebhookLog extends Model
{
    protected $fillable = [
        'gateway',
        'event_type',
        'event_id',
        'status',
        'payload',
        'error_message',
        'processing_time_ms',
        'ip_address',
    ];

    protected $casts = [
        'payload' => 'array',
        'processing_time_ms' => 'integer',
    ];

    /**
     * Get semantic status badge.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'processed' => [
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'label' => 'Processed',
            ],
            'failed' => [
                'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
                'label' => 'Failed',
            ],
            default => [
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'label' => 'Pending',
            ],
        };
    }
}
