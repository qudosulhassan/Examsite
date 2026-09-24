<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionImportItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'raw_data' => 'array',
        'normalized_data' => 'array',
        'validation_errors' => 'array',
        'validation_warnings' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(QuestionImportBatch::class, 'batch_id');
    }

    public function duplicateQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'duplicate_question_id');
    }

    public function importedQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'imported_question_id');
    }
}
