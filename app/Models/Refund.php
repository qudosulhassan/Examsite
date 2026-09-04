<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'admin_id',
        'amount',
        'currency',
        'reason',
        'status',
        'gateway_refund_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the order that this refund belongs to.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the admin user who issued the refund.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
