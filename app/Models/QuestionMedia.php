<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionMedia extends Model
{
    protected $table = 'question_media';

    protected $fillable = [
        'question_id',
        'media_type',
        'media_url',
        'caption',
        'alt_text',
        'sort_order',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
