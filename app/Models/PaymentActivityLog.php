<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentActivityLog extends Model
{
    protected $fillable = [
        'order_id',
        'gateway',
        'event',
        'status',
        'message',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Associated order if applicable.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Record a payment activity log event.
     */
    public static function record(?string $gateway, string $event, string $status, string $message, ?int $orderId = null, ?array $payload = null): self
    {
        return self::create([
            'order_id' => $orderId,
            'gateway' => $gateway,
            'event' => $event,
            'status' => $status,
            'message' => $message,
            'payload' => $payload,
        ]);
    }
}
