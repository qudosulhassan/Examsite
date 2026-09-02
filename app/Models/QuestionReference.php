<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionReference extends Model
{
    protected $fillable = [
        'question_id',
        'title',
        'url',
        'sort_order',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
