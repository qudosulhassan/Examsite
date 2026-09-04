<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'performed_by',
        'event',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user/admin who triggered the event.
     */
    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Static helper to log an order timeline event.
     */
    public static function record(int $orderId, string $event, string $description, ?int $performedBy = null, ?array $metadata = null): self
    {
        return self::create([
            'order_id' => $orderId,
            'performed_by' => $performedBy ?? auth()->id(),
            'event' => $event,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
