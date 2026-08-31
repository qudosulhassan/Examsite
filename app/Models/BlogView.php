<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogView extends Model
{
    use HasFactory;

    public $timestamps = false; // Only using viewed_at

    protected $fillable = [
        'blog_post_id',
        'ip_address',
        'session_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }
}
