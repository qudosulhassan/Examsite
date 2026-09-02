<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'topic',
        'question_type',
        'question_text',
        'instructions',
        'explanation',
        'is_active',
        'status',
        'source_type',
        'source_reference',
        'question_hash',
        'question_data',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'source_reference' => 'array',
        'question_data' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function ($question) {
            if ($question->question_text) {
                // Normalize spacing and convert to lowercase for hashing
                $normalized = preg_replace('/\s+/', ' ', strtolower(trim($question->question_text)));
                $question->question_hash = md5($normalized);
            }
        });
    }

    /**
     * Get the exam that owns the question.
     */
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Relationships
     */
    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('sort_order');
    }

    public function answers()
    {
        return $this->hasMany(QuestionAnswer::class)->orderBy('sort_order');
    }

    public function media()
    {
        return $this->hasMany(QuestionMedia::class)->orderBy('sort_order');
    }

    public function references()
    {
        return $this->hasMany(QuestionReference::class)->orderBy('sort_order');
    }

    /**
     * Backwards Compatibility Layer (Accessors)
     * Maps relational option/correct answer data to legacy direct attributes.
     */
    public function getOptionAAttribute()
    {
        return $this->options->where('option_key', 'A')->first()?->option_text;
    }

    public function getOptionBAttribute()
    {
        return $this->options->where('option_key', 'B')->first()?->option_text;
    }

    public function getOptionCAttribute()
    {
        return $this->options->where('option_key', 'C')->first()?->option_text;
    }

    public function getOptionDAttribute()
    {
        return $this->options->where('option_key', 'D')->first()?->option_text;
    }

    public function getCorrectOptionAttribute()
    {
        return $this->answers->pluck('answer_value')->implode(',');
    }

    /**
     * Check if a given option is correct.
     */
    public function isCorrect(string $option): bool
    {
        return $this->answers->pluck('answer_value')->contains(trim($option));
    }

    /**
     * Save from the master universal question model structure.
     */
    public static function saveFromUniversalModel(array $data, ?Question $existingQuestion = null): Question
    {
        return DB::transaction(function () use ($data, $existingQuestion) {
            $question = $existingQuestion ?? new Question();

            $existingQData = $question->question_data ?? [];
            if (!is_array($existingQData)) {
                $existingQData = [];
            }

            $newBoxes = !empty($data['boxes']) ? $data['boxes'] : (!empty($data['hotspot_answers']) ? $data['hotspot_answers'] : ($existingQData['boxes'] ?? []));
            foreach ($newBoxes as &$b) {
                if (empty($b['options']) && !empty($b['optionsText'])) {
                    $b['options'] = array_map('trim', explode(',', $b['optionsText']));
                }
            }
            unset($b);

            $mergedQData = array_merge($existingQData, [
                'drag_items' => !empty($data['drag_items']) ? $data['drag_items'] : ($existingQData['drag_items'] ?? []),
                'correct_order' => !empty($data['correct_order']) ? $data['correct_order'] : ($existingQData['correct_order'] ?? []),
                'matching_pairs' => !empty($data['matching_pairs']) ? $data['matching_pairs'] : ($existingQData['matching_pairs'] ?? []),
                'boxes' => $newBoxes,
            ]);

            if (isset($data['question_data']['answer_area_image'])) {
                $mergedQData['answer_area_image'] = $data['question_data']['answer_area_image'];
            }

            // Auto-populate correct_answers for hotspot if not explicitly set
            if (($data['question_type'] ?? '') === 'hotspot' && empty($data['correct_answers']) && !empty($newBoxes)) {
                $data['correct_answers'] = array_filter(array_map(fn($b) => $b['correct_answer'] ?? '', $newBoxes));
            }

            $question->fill([
                'exam_id' => $data['exam_id'] ?? null,
                'topic' => $data['topic'] ?? '',
                'question_type' => $data['question_type'] ?? 'single_choice',
                'question_text' => $data['question_text'] ?? '',
                'instructions' => $data['instructions'] ?? '',
                'explanation' => $data['explanation'] ?? '',
                'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : false,
                'status' => $data['status'] ?? 'draft',
                'source_type' => $data['source_type'] ?? 'manual',
                'source_reference' => $data['source_reference'] ?? null,
                'question_data' => $mergedQData,
            ]);

            $question->save();

            // Clear and rebuild options
            $question->options()->delete();
            if (!empty($data['options'])) {
                foreach ($data['options'] as $index => $opt) {
                    $optKey = trim((string)($opt['key'] ?? chr(65 + $index)));
                    $optText = trim((string)($opt['text'] ?? ''));
                    
                    if ($optText !== '' || in_array($data['question_type'] ?? '', ['single_choice', 'multiple_choice', 'yes_no'])) {
                        $question->options()->create([
                            'option_key' => $optKey !== '' ? $optKey : chr(65 + $index),
                            'option_text' => $optText !== '' ? $optText : ('Option ' . ($optKey !== '' ? $optKey : chr(65 + $index))),
                            'sort_order' => (int)($opt['sort_order'] ?? ($index + 1)),
                        ]);
                    }
                }
            }

            // Clear and rebuild answers
            $question->answers()->delete();
            if (!empty($data['correct_answers'])) {
                foreach ($data['correct_answers'] as $index => $ans) {
                    $ansVal = trim((string)$ans);
                    if ($ansVal !== '') {
                        $question->answers()->create([
                            'answer_value' => $ansVal,
                            'sort_order' => (int)($index + 1),
                        ]);
                    }
                }
            }

            // Clear and rebuild references
            $question->references()->delete();
            if (!empty($data['references'])) {
                foreach ($data['references'] as $index => $ref) {
                    $title = trim((string)($ref['title'] ?? ''));
                    $url = trim((string)($ref['url'] ?? ''));
                    
                    if ($title !== '' || $url !== '') {
                        $question->references()->create([
                            'title' => $title !== '' ? $title : ($url !== '' ? $url : 'Reference'),
                            'url' => $url !== '' ? $url : null,
                            'sort_order' => (int)($ref['sort_order'] ?? ($index + 1)),
                        ]);
                    }
                }
            }

            // Clear and rebuild media
            $question->media()->delete();
            if (!empty($data['media'])) {
                foreach ($data['media'] as $index => $m) {
                    $mediaUrl = trim((string)($m['url'] ?? ''));
                    if ($mediaUrl !== '') {
                        $question->media()->create([
                            'media_type' => !empty($m['type']) ? trim((string)$m['type']) : 'image',
                            'media_url' => $mediaUrl,
                            'caption' => !empty($m['caption']) ? trim((string)$m['caption']) : null,
                            'alt_text' => !empty($m['alt']) ? trim((string)$m['alt']) : null,
                            'sort_order' => (int)($m['sort_order'] ?? ($index + 1)),
                        ]);
                    }
                }
            }

            // Update exam question count
            if ($question->exam) {
                $question->exam->update(['question_count' => $question->exam->questions()->count()]);
            }

            return $question;
        });
    }

    /**
     * Helper to normalize input data from forms, legacy JSON structures into the universal model.
     */
    public static function convertToUniversalModel(array $input): array
    {
        $examId = $input['exam_id'] ?? null;
        if (!$examId && !empty($input['exam_code'])) {
            $resolvedExam = \App\Models\Exam::where('exam_code', trim($input['exam_code']))->first();
            if ($resolvedExam) {
                $examId = $resolvedExam->id;
            }
        }

        $qData = $input['question_data'] ?? [];
        $dragItems = $input['drag_items'] ?? $qData['drag_items'] ?? [];
        $correctOrder = $input['correct_order'] ?? $qData['correct_order'] ?? [];
        $matchingPairs = $input['matching_pairs'] ?? $qData['matching_pairs'] ?? [];
        $hotspotAnswers = $input['hotspot_answers'] ?? $input['boxes'] ?? $qData['boxes'] ?? $qData['hotspot_answers'] ?? [];

        $universal = [
            'exam_id' => $examId,
            'topic' => $input['topic'] ?? '',
            'question_type' => $input['question_type'] ?? 'single_choice',
            'question_text' => $input['question_text'] ?? '',
            'instructions' => $input['instructions'] ?? '',
            'explanation' => $input['explanation'] ?? '',
            'is_active' => isset($input['is_active']) ? (bool)$input['is_active'] : false,
            'status' => $input['status'] ?? 'draft',
            'source_type' => $input['source_type'] ?? 'manual',
            'source_reference' => $input['source_reference'] ?? null,
            'options' => [],
            'correct_answers' => [],
            'drag_items' => $dragItems,
            'correct_order' => $correctOrder,
            'matching_pairs' => $matchingPairs,
            'hotspot_answers' => $hotspotAnswers,
            'references' => $input['references'] ?? [],
            'media' => $input['media'] ?? [],
        ];

        // 1. Convert Option A-D legacy fields if options list is empty
        if (empty($input['options'])) {
            $optionsMap = [
                'A' => $input['option_a'] ?? null,
                'B' => $input['option_b'] ?? null,
                'C' => $input['option_c'] ?? null,
                'D' => $input['option_d'] ?? null,
            ];
            $sortOrder = 1;
            foreach ($optionsMap as $key => $text) {
                if ($text !== null && trim((string)$text) !== '') {
                    $universal['options'][] = [
                        'key' => $key,
                        'text' => trim((string)$text),
                        'sort_order' => $sortOrder++,
                    ];
                }
            }
        } else {
            foreach ($input['options'] as $index => $opt) {
                $key = trim((string)($opt['key'] ?? chr(65 + $index)));
                $text = trim((string)($opt['text'] ?? ''));
                $universal['options'][] = [
                    'key' => $key !== '' ? $key : chr(65 + $index),
                    'text' => $text,
                    'sort_order' => (int)($opt['sort_order'] ?? ($index + 1)),
                ];
            }
        }

        // 2. Convert correct_option legacy field if correct_answers is empty
        if (empty($input['correct_answers']) && !empty($input['correct_option'])) {
            $raw = trim($input['correct_option']);
            if (str_contains($raw, ',')) {
                $correctOptions = array_map('trim', explode(',', $raw));
            } else {
                $correctOptions = str_split($raw);
            }
            $universal['correct_answers'] = array_filter(array_map('trim', $correctOptions));
        } else {
            $universal['correct_answers'] = $input['correct_answers'] ?? [];
        }

        if (count($universal['correct_answers']) > 1 && $universal['question_type'] === 'single_choice') {
            $universal['question_type'] = 'multiple_choice';
        }

        // 3. Convert legacy image_filename field if media is empty
        if (empty($input['media']) && !empty($input['image_filename'])) {
            $universal['media'][] = [
                'type' => 'image',
                'url' => '/storage/questions/' . $input['image_filename'],
                'caption' => 'Exhibit',
                'alt' => 'Exhibit',
                'sort_order' => 1,
            ];
        }

        return $universal;
    }
}
