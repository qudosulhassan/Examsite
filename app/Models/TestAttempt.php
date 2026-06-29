<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TestAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'exam_id',
        'mode',
        'total_questions',
        'answered',
        'correct',
        'skipped',
        'score_percentage',
        'passed',
        'time_taken_seconds',
        'completed_at',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'score_percentage' => 'decimal:2',
        'completed_at' => 'datetime',
        'total_questions' => 'integer',
        'answered' => 'integer',
        'correct' => 'integer',
        'skipped' => 'integer',
        'time_taken_seconds' => 'integer',
    ];

    /**
     * Get the user who made the attempt.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the exam attempted.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the answers for this test attempt.
     */
    public function answers()
    {
        return $this->hasMany(TestAnswer::class, 'attempt_id');
    }
}
