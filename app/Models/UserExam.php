<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserExam extends Model
{
    use HasFactory;

    protected $table = 'user_exams';

    protected $fillable = [
        'user_id',
        'exam_id',
        'order_id',
        'access_type',
        'download_count',
        'max_downloads',
        'purchased_at',
        'expires_at',
    ];

    protected $casts = [
        'download_count' => 'integer',
        'max_downloads' => 'integer',
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user who owns the exam access.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the exam that the user has access to.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the order that granted this access.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if the exam access is still valid.
     */
    public function isValid(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        return true;
    }

    /**
     * Check if download attempts are still remaining.
     */
    public function canDownload(): bool
    {
        return $this->download_count < $this->max_downloads;
    }
}
