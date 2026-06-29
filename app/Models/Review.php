<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exam_id',
        'rating',
        'review_text',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
    ];

    /**
     * Get the user who left the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the exam reviewed.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
