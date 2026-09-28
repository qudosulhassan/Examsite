<?php

namespace App\Services\TestEngine;

use App\Models\TestAnswer;
use App\Models\TestAttempt;
use Illuminate\Support\Collection;

/**
 * Glue between a TestAttempt and the practice-test engine page: builds the page config
 * and records answers. Used by both the dashboard and the free demo engine.
 */
class EngineSession
{
    /**
     * @param  Collection<TestAnswer>  $answers  answers of the attempt, with question (+options, answers, media, references) loaded
     * @param  array  $urls  save, flag, submit, restart
     */
    public static function config(TestAttempt $attempt, Collection $answers, array $urls): array
    {
        $isExam = $attempt->mode === 'exam';
        $questions = [];
        $keys = [];
        $state = [];

        foreach ($answers as $ans) {
            $question = $ans->question;
            if (!$question) {
                continue;
            }
            $payload = EngineQuestion::payload($question);
            $answer = $payload['answer'] ?? [];
            unset($payload['answer']);

            $payload['qid'] = $question->id;
            if (!empty($payload['questionHtml'])) {
                $payload['searchText'] = mb_strtolower(strip_tags($payload['questionHtml']));
            }
            $questions[] = $payload;
            $keys[$question->id] = $answer;

            $response = is_array($ans->response) ? $ans->response : [];
            if ($isExam && !empty($response['status'])) {
                $response['status'] = 'answered';   // never reveal correctness during an exam
            }
            $state[$question->id] = $response + ['flagged' => (bool) $ans->is_flagged];
        }

        return [
            'mode'           => $attempt->mode,
            'attemptId'      => $attempt->id,
            'csrf'           => csrf_token(),
            'storeKey'       => 'etb-attempt-' . $attempt->id,
            'elapsedSeconds' => (int) max(0, now()->diffInSeconds($attempt->created_at, true)),
            'questions'      => $questions,
            // Shipped encoded (as in the PL-900 file) and decoded per question on Check/Reveal.
            'answers'        => $isExam ? null : base64_encode(json_encode($keys, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            'state'          => (object) $state,
            'urls'           => $urls,
        ];
    }

    /**
     * Grade and store one question's engine state. Returns the stored status.
     */
    public static function record(TestAttempt $attempt, TestAnswer $answer, array $response): ?string
    {
        $question = $answer->question;
        $clean = [
            'sel'    => array_values(array_slice(array_map('strval', (array) ($response['sel'] ?? [])), 0, 12)),
            'boxes'  => static::cleanMap($response['boxes'] ?? []),
            'rows'   => static::cleanMap($response['rows'] ?? []),
            'status' => in_array($response['status'] ?? null, ['correct', 'incorrect', 'revealed', 'reviewed', 'answered'], true) ? $response['status'] : null,
            'self'   => !empty($response['self']),
        ];

        // In exam mode the page only says "answered"; the server decides correctness.
        if ($attempt->mode === 'exam' && $clean['status'] === 'answered') {
            $clean['status'] = null;
        }
        $status = $clean['status'] === null && $attempt->mode !== 'exam'
            ? null
            : EngineQuestion::grade($question, $clean);
        $clean['status'] = $status;

        $answer->update([
            'response'        => $clean,
            'selected_option' => $status ? EngineQuestion::summary($clean) : null,
            'is_correct'      => $status === 'correct',
        ]);

        $attempt->update([
            'answered' => TestAnswer::where('attempt_id', $attempt->id)->whereNotNull('selected_option')->count(),
        ]);

        return $status;
    }

    protected static function cleanMap(mixed $map): array
    {
        $out = [];
        foreach ((array) $map as $k => $v) {
            if (count($out) >= 50) {
                break;
            }
            if (is_scalar($v) && $v !== '') {
                $out[(string) $k] = mb_substr((string) $v, 0, 300);
            }
        }
        return $out;
    }
}
