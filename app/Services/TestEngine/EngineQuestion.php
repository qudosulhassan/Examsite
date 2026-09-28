<?php

namespace App\Services\TestEngine;

use App\Models\Question;
use App\Services\HtmlSanitizerService;

/**
 * Turns a Question into the practice-test engine shape (the PL-900 format) and grades
 * responses against it.
 *
 * Imported exam files already carry this shape in question_data['engine']. Questions
 * written in the admin "Add Question" form are converted from their classic columns.
 *
 * A response is the engine's per-question state: {sel: [], boxes: {n: v}, rows: {n: v}, status, self}.
 */
class EngineQuestion
{
    public static function payload(Question $question): array
    {
        $engine = $question->question_data['engine'] ?? null;
        return is_array($engine) ? static::withAdminEdits($question, $engine) : static::fromClassic($question);
    }

    /**
     * An imported question keeps its engine payload, but edits made later in the admin
     * form (text, explanation, option wording, correct option) must still show up.
     */
    protected static function withAdminEdits(Question $question, array $engine): array
    {
        $hashes = $question->question_data['engine_hashes'] ?? null;
        if (!$hashes) {
            return $engine;
        }

        if (md5((string) $question->question_text) !== $hashes['question']) {
            $engine['question'] = [];
            $engine['questionHtml'] = HtmlSanitizerService::sanitize($question->question_text);
        }
        if (md5((string) $question->explanation) !== $hashes['explanation']) {
            $engine['answer']['explanation'] = [];
            $engine['answer']['explanationHtml'] = HtmlSanitizerService::sanitize($question->explanation);
        }

        if (count($engine['options'] ?? [])) {
            $options = $question->options->map(fn ($o) => ['label' => $o->option_key, 'text' => $o->option_text])->values()->all();
            $correct = $question->answers->pluck('answer_value')->map(fn ($v) => trim((string) $v))->values()->all();
            if (count($options) >= 2) {
                $engine['options'] = $options;
            }
            if ($correct) {
                $engine['answer']['correctAnswer'] = $correct;
            }
        }

        return $engine;
    }

    /** The visible part of a question (never includes the answer key). */
    public static function visible(Question $question): array
    {
        $p = static::payload($question);
        unset($p['answer']);
        return $p;
    }

    public static function answer(Question $question): array
    {
        return static::payload($question)['answer'] ?? [];
    }

    public static function isGradable(array $p): bool
    {
        return count($p['options'] ?? []) || (($p['boxCount'] ?? 0) && ($p['yesNo'] ?? false)) || !empty($p['ia']);
    }

    /**
     * Grade a response the way the engine's Check Answer button does.
     * Returns the status to store: correct | incorrect | revealed | reviewed | null (unanswered).
     */
    public static function grade(Question $question, array $response): ?string
    {
        $p = static::payload($question);
        $a = $p['answer'] ?? [];
        $status = $response['status'] ?? null;

        if ($status === 'revealed' || $status === 'reviewed') {
            return $status;
        }

        if (count($p['options'] ?? [])) {
            $sel = array_values(array_unique(array_map('strval', (array) ($response['sel'] ?? []))));
            if (!count($sel)) {
                return null;
            }
            return static::sameSet($sel, $a['correctAnswer'] ?? []) ? 'correct' : 'incorrect';
        }

        if (!empty($p['ia'])) {
            $rows = (array) ($response['rows'] ?? []);
            $expected = $a['rowAnswers'] ?? [];
            foreach ($p['ia']['rows'] as $n => $_) {
                if (!isset($rows[$n]) || $rows[$n] === '') {
                    return null;
                }
            }
            foreach ($expected as $n => $value) {
                if ((string) ($rows[$n] ?? '') !== (string) $value) {
                    return 'incorrect';
                }
            }
            return count($expected) ? 'correct' : 'incorrect';
        }

        if (($p['boxCount'] ?? 0) && ($p['yesNo'] ?? false)) {
            $boxes = (array) ($response['boxes'] ?? []);
            for ($b = 1; $b <= $p['boxCount']; $b++) {
                if (empty($boxes[$b])) {
                    return null;
                }
            }
            foreach ($a['boxes'] ?? [] as $bx) {
                if (($boxes[$bx['box']] ?? '') !== trim(rtrim($bx['value'], '.'))) {
                    return 'incorrect';
                }
            }
            return count($a['boxes'] ?? []) ? 'correct' : 'incorrect';
        }

        // Not auto-gradable: only a self-assessment after revealing counts.
        if (in_array($status, ['correct', 'incorrect'], true) && !empty($response['self'])) {
            return $status;
        }
        return null;
    }

    /** A short human summary of a response, kept in test_answers.selected_option. */
    public static function summary(array $response): ?string
    {
        $parts = [];
        if (!empty($response['sel'])) {
            $parts[] = implode(',', (array) $response['sel']);
        }
        foreach (['boxes', 'rows'] as $k) {
            if (!empty($response[$k])) {
                $parts[] = implode(' | ', array_map('strval', (array) $response[$k]));
            }
        }
        $s = implode(' ; ', $parts);
        return $s === '' ? ($response['status'] ?? null) : mb_substr($s, 0, 250);
    }

    protected static function sameSet(array $a, array $b): bool
    {
        sort($a);
        sort($b);
        return $a === array_values(array_map('strval', $b));
    }

    /** Convert a question written in the admin form into the engine shape. */
    protected static function fromClassic(Question $question): array
    {
        $type = $question->question_type ?? 'single_choice';
        $data = $question->question_data ?? [];
        $correct = $question->answers->pluck('answer_value')->map(fn ($v) => trim((string) $v))->values()->all();

        $p = [
            'number'       => $question->id,
            'topic'        => $question->topic ?? '',
            'type'         => 'multiple-choice',
            'question'     => [],
            'questionHtml' => HtmlSanitizerService::sanitize($question->question_text),
            'options'      => [],
            'images'       => $question->media->where('media_type', '!=', 'answer_image')->pluck('media_url')->values()->all(),
            'boxCount'     => 0,
            'yesNo'        => false,
            'ia'           => null,
            'answer'       => [
                'correctAnswer'   => null,
                'boxes'           => [],
                'explanation'     => [],
                'explanationHtml' => HtmlSanitizerService::sanitize($question->explanation),
                'reference'       => $question->references->map(fn ($r) => $r->url ?: $r->title)->filter()->values()->all(),
                'answerImages'    => $question->media->where('media_type', 'answer_image')->pluck('media_url')->values()->all(),
                'rowAnswers'      => null,
            ],
        ];

        if (in_array($type, ['single_choice', 'multiple_choice', 'yes_no'], true)) {
            $p['type'] = $type === 'multiple_choice' ? 'multiple-select' : 'multiple-choice';
            $p['options'] = $question->options->map(fn ($o) => ['label' => $o->option_key, 'text' => $o->option_text])->values()->all();
            $p['answer']['correctAnswer'] = $correct;
        } elseif ($type === 'hotspot') {
            $boxes = $data['boxes'] ?? $data['hotspot_answers'] ?? [];
            $p['type'] = 'hotspot';
            if ($boxes) {
                $p['ia'] = [
                    'kind' => 'select',
                    'pool' => [],
                    'rows' => array_map(fn ($b, $i) => [
                        'label'   => $b['label'] ?? ('Box ' . ($i + 1)),
                        'choices' => array_values(array_map('trim', $b['options'] ?? [])),
                    ], $boxes, array_keys($boxes)),
                ];
                $p['answer']['rowAnswers'] = array_map(fn ($b) => trim((string) ($b['correct_answer'] ?? '')), $boxes);
            }
        } elseif ($type === 'drag_drop') {
            $items = $data['drag_items'] ?? [];
            $order = $data['correct_order'] ?? [];
            $p['type'] = 'drag-drop';
            if ($items) {
                $textById = collect($items)->mapWithKeys(fn ($it, $i) => [(string) ($it['id'] ?? $i) => $it['text'] ?? ''])->all();
                $ordered = $order ? array_map(fn ($id) => $textById[(string) $id] ?? (string) $id, $order) : array_values($textById);
                $p['ia'] = [
                    'kind' => 'order',
                    'pool' => array_values($textById),
                    'rows' => array_map(fn ($i) => ['label' => 'Step ' . ($i + 1)], array_keys($ordered)),
                ];
                $p['answer']['rowAnswers'] = $ordered;
            }
        }

        return $p;
    }
}
