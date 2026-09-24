<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionImportBatch extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'options' => 'array',
        'completed_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(QuestionImportItem::class, 'batch_id');
    }

    public function defaultExam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'default_exam_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recalculate summary counts based on items.
     */
    public function recalculateCounts(): void
    {
        $this->update([
            'total_questions' => $this->items()->count(),
            'valid_count' => $this->items()->where('validation_status', 'valid')->count(),
            'warning_count' => $this->items()->where('validation_status', 'warning')->count(),
            'error_count' => $this->items()->where('validation_status', 'error')->count(),
            'duplicate_count' => $this->items()->where('validation_status', 'duplicate')->count(),
            'imported_count' => $this->items()->whereNotNull('imported_question_id')->count(),
        ]);
    }
}
