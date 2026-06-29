<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TestAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'selected_option',
        'is_correct',
        'is_flagged',
        'time_spent_seconds',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'is_flagged' => 'boolean',
        'time_spent_seconds' => 'integer',
    ];

    /**
     * Get the test attempt associated with this answer.
     */
    public function attempt()
    {
        return $this->belongsTo(TestAttempt::class, 'attempt_id');
    }

    /**
     * Get the question associated with this answer.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
