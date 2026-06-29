<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_option',
        'explanation',
        'image_filename',
        'topic',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the exam that owns the question.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Check if a given option is correct.
     * Supports multiple correct options (comma-separated).
     */
    public function isCorrect(string $option): bool
    {
        $correct = explode(',', $this->correct_option);
        $correct = array_map('trim', $correct);
        return in_array($option, $correct);
    }
}
