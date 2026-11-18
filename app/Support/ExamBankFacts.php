<?php

namespace App\Support;

use App\Models\Exam;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * True facts about an exam's practice question bank, read from the questions themselves: how many there are, what
 * formats they use, which topics they cover and how many have an explanation. Nothing here is estimated or copied
 * from the vendor, so it is safe to show on every exam page (including the ones with no written exam details).
 */
class ExamBankFacts
{
    private const TYPE_LABELS = [
        'single_choice'   => 'single-answer multiple choice',
        'multiple_choice' => 'multiple-answer multiple choice',
        'true_false'      => 'true or false',
        'drag_drop'       => 'drag and drop',
        'hotspot'         => 'hotspot',
        'fill_blank'      => 'fill in the blank',
        'case_study'      => 'case study',
    ];

    /** @return array<string,mixed>|null null when the exam has no active questions */
    public static function for(Exam $exam): ?array
    {
        return Cache::remember('exam_bank_facts_' . $exam->id, 3600, function () use ($exam) {
            $base = $exam->questions()->where('is_active', true);
            $total = (clone $base)->count();
            if ($total === 0) {
                return null;
            }

            $types = [];
            foreach ((clone $base)->selectRaw('question_type, count(*) as c')->groupBy('question_type')->orderByDesc('c')->get() as $row) {
                $label = self::TYPE_LABELS[$row->question_type] ?? str_replace('_', ' ', (string) $row->question_type);
                $types[$label] = (int) $row->c;
            }

            $topics = [];
            foreach ((clone $base)->whereNotNull('topic')->where('topic', '!=', '')->selectRaw('topic, count(*) as c')->groupBy('topic')->orderByDesc('c')->limit(12)->get() as $row) {
                $topics[Str::limit(trim((string) $row->topic), 90)] = (int) $row->c;
            }

            $explained = (clone $base)->whereNotNull('explanation')->where('explanation', '!=', '')->count();
            $updated = $exam->last_updated_at ?? $exam->updated_at;

            return [
                'total'     => $total,
                'types'     => $types,
                'topics'    => $topics,
                'explained' => $explained,
                'updated'   => $updated ? $updated->format('F j, Y') : null,
            ];
        });
    }
}
