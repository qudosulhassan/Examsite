<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'email',
        'status',
        'subscribed_at',
        'unsubscribed_at',
        'ip_address',
        'source',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];
}
