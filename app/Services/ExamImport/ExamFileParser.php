<?php

namespace App\Services\ExamImport;

use InvalidArgumentException;

/**
 * Reads an exam file produced by the Claude conversion prompt and returns it in
 * one normalized shape.
 *
 * Accepted inputs:
 *  - The practice-test HTML (PL-900 style) that embeds `const QUESTIONS = [...]`,
 *    `const ANSWERS_B64 = "..."` (or `const ANSWERS = {...}`), `const QIMG = {...}`
 *    and `const AIMG = {...}`, plus an optional `const EXAM_META = {...}`.
 *  - A JSON file with either the same keys, or {meta, questions:[{..., answer:{...}}], images:{...}}.
 *
 * Normalized result:
 *  [
 *    'meta'      => ['code' => ?string, 'title' => ?string],
 *    'questions' => [ [id, topic, type, question[], options[], images[], boxCount, yesNo, ia, answer[...]] ],
 *    'images'    => [ key => 'data:image/...;base64,...' ],
 *  ]
 */
class ExamFileParser
{
    public const TYPES = ['multiple-choice', 'multiple-select', 'hotspot', 'drag-drop'];

    public function parse(string $contents): array
    {
        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents);
        $trimmed = ltrim($contents);

        $raw = ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '['))
            ? $this->fromJson($trimmed)
            : $this->fromHtml($contents);

        return $this->normalize($raw);
    }

    protected function fromJson(string $json): array
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new InvalidArgumentException('The JSON file could not be read: ' . json_last_error_msg());
        }

        if (array_is_list($data)) {
            return ['questions' => $data, 'answers' => [], 'images' => [], 'meta' => []];
        }

        if (isset($data['QUESTIONS'])) {
            return [
                'questions' => $data['QUESTIONS'],
                'answers'   => $data['ANSWERS'] ?? (isset($data['ANSWERS_B64']) ? $this->decodeAnswers($data['ANSWERS_B64']) : []),
                'images'    => array_merge($data['QIMG'] ?? [], $data['AIMG'] ?? []),
                'meta'      => $data['EXAM_META'] ?? [],
            ];
        }

        return [
            'questions' => $data['questions'] ?? [],
            'answers'   => $data['answers'] ?? [],
            'images'    => $data['images'] ?? [],
            'meta'      => $data['meta'] ?? [],
        ];
    }

    protected function fromHtml(string $html): array
    {
        $questions = $this->extractLiteral($html, 'QUESTIONS');
        if ($questions === null) {
            throw new InvalidArgumentException(
                'No exam data found in this HTML file. Upload the practice-test HTML generated with the ExamTopicsBase conversion prompt (it contains "const QUESTIONS = [...]").'
            );
        }

        $answers = $this->extractLiteral($html, 'ANSWERS');
        if ($answers === null) {
            $b64 = $this->extractLiteral($html, 'ANSWERS_B64');
            $answers = is_string($b64) ? $this->decodeAnswers($b64) : [];
        }

        return [
            'questions' => $questions,
            'answers'   => is_array($answers) ? $answers : [],
            'images'    => array_merge($this->extractLiteral($html, 'QIMG') ?? [], $this->extractLiteral($html, 'AIMG') ?? []),
            'meta'      => $this->extractLiteral($html, 'EXAM_META') ?? [],
        ];
    }

    protected function decodeAnswers(string $b64): array
    {
        $decoded = json_decode((string) base64_decode($b64, true), true);
        if (!is_array($decoded)) {
            throw new InvalidArgumentException('The hidden answer data (ANSWERS_B64) in this file is damaged and could not be decoded.');
        }
        return $decoded;
    }

    /**
     * Find `const NAME = <json literal>` in a script and decode the literal.
     * Scans with string awareness so brackets inside text don't end the literal early.
     */
    protected function extractLiteral(string $src, string $name): mixed
    {
        if (!preg_match('/\b(?:const|let|var)\s+' . preg_quote($name, '/') . '\s*=\s*/', $src, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $start = $m[0][1] + strlen($m[0][0]);
        $open = $src[$start] ?? '';

        if ($open === '"' || $open === "'") {
            $end = $this->skipString($src, $start);
            $literal = substr($src, $start, $end - $start + 1);
            if ($open === "'") {
                $literal = '"' . str_replace(['\\\'', '"'], ["'", '\\"'], substr($literal, 1, -1)) . '"';
            }
            return json_decode($literal, true);
        }

        if ($open !== '[' && $open !== '{') {
            return null;
        }

        $depth = 0;
        $len = strlen($src);
        for ($i = $start; $i < $len; $i++) {
            $c = $src[$i];
            if ($c === '"' || $c === "'") {
                $i = $this->skipString($src, $i);
                continue;
            }
            if ($c === '[' || $c === '{') {
                $depth++;
            } elseif ($c === ']' || $c === '}') {
                $depth--;
                if ($depth === 0) {
                    $decoded = json_decode(substr($src, $start, $i - $start + 1), true);
                    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                        throw new InvalidArgumentException("The {$name} data in this file is not valid JSON: " . json_last_error_msg());
                    }
                    return $decoded;
                }
            }
        }

        throw new InvalidArgumentException("The {$name} data in this file is incomplete (the file may be cut off).");
    }

    /** Returns the index of the closing quote of the string starting at $i. */
    protected function skipString(string $src, int $i): int
    {
        $quote = $src[$i];
        $len = strlen($src);
        for ($j = $i + 1; $j < $len; $j++) {
            if ($src[$j] === '\\') {
                $j++;
            } elseif ($src[$j] === $quote) {
                return $j;
            }
        }
        return $len - 1;
    }

    protected function normalize(array $raw): array
    {
        if (!is_array($raw['questions']) || !count($raw['questions'])) {
            throw new InvalidArgumentException('The file does not contain any questions.');
        }

        $answers = $raw['answers'] ?? [];
        $questions = [];

        foreach (array_values($raw['questions']) as $index => $q) {
            if (!is_array($q)) {
                continue;
            }
            $id = $q['id'] ?? ($index + 1);
            $answer = $q['answer'] ?? ($answers[(string) $id] ?? ($answers[$id] ?? []));

            $questions[] = [
                'id'       => $id,
                'topic'    => trim((string) ($q['topic'] ?? '')),
                'type'     => $this->normalizeType($q['type'] ?? ''),
                'question' => $this->paragraphs($q['question'] ?? []),
                'options'  => array_values(array_map(fn ($o) => [
                    'label' => trim((string) ($o['label'] ?? '')),
                    'text'  => trim((string) ($o['text'] ?? '')),
                ], array_filter($q['options'] ?? [], 'is_array'))),
                'images'   => array_values(array_map('strval', $q['images'] ?? [])),
                'boxCount' => (int) ($q['boxCount'] ?? 0),
                'yesNo'    => (bool) ($q['yesNo'] ?? false),
                'ia'       => $this->normalizeIa($q['ia'] ?? null),
                'answer'   => [
                    'correctAnswer' => isset($answer['correctAnswer']) && is_array($answer['correctAnswer'])
                        ? array_values(array_map(fn ($a) => trim((string) $a), $answer['correctAnswer']))
                        : null,
                    'boxes'         => array_values(array_map(fn ($b) => [
                        'box'   => (int) ($b['box'] ?? 0),
                        'value' => trim((string) ($b['value'] ?? '')),
                    ], array_filter($answer['boxes'] ?? [], 'is_array'))),
                    'explanation'   => array_values(array_filter(array_map(
                        fn ($e) => is_array($e) ? (isset($e['img']) ? ['img' => (string) $e['img']] : null) : trim((string) $e),
                        (array) ($answer['explanation'] ?? [])
                    ), fn ($e) => $e !== null && $e !== '')),
                    'reference'     => array_values(array_filter(array_map(fn ($r) => trim((string) $r), (array) ($answer['reference'] ?? [])))),
                    'answerImages'  => array_values(array_map('strval', $answer['answerImages'] ?? [])),
                    'rowAnswers'    => isset($answer['rowAnswers']) && is_array($answer['rowAnswers'])
                        ? array_values(array_map(fn ($a) => trim((string) $a), $answer['rowAnswers']))
                        : null,
                ],
            ];
        }

        $images = [];
        foreach ((array) ($raw['images'] ?? []) as $key => $uri) {
            if (is_string($uri) && $uri !== '') {
                $images[(string) $key] = $uri;
            }
        }

        $meta = is_array($raw['meta'] ?? null) ? $raw['meta'] : [];

        return [
            'meta' => [
                'code'  => isset($meta['code']) ? trim((string) $meta['code']) : null,
                'title' => isset($meta['title']) ? trim((string) $meta['title']) : null,
            ],
            'questions' => $questions,
            'images'    => $images,
        ];
    }

    protected function normalizeType(mixed $type): string
    {
        $t = strtolower(str_replace(['_', ' ', '&'], ['-', '-', ''], trim((string) $type)));
        return match ($t) {
            'single-choice', 'multiple-choice', 'mcq', 'single' => 'multiple-choice',
            'multiple-select', 'multi-select', 'multiple-response', 'multi' => 'multiple-select',
            'hotspot', 'hot-spot', 'yes-no' => 'hotspot',
            'drag-drop', 'dragdrop', 'drag--drop', 'drag-and-drop' => 'drag-drop',
            default => $t,
        };
    }

    protected function normalizeIa(mixed $ia): ?array
    {
        if (!is_array($ia) || empty($ia['rows']) || !is_array($ia['rows'])) {
            return null;
        }

        return [
            'kind' => in_array($ia['kind'] ?? '', ['yesno', 'select', 'match', 'order'], true) ? $ia['kind'] : 'match',
            'pool' => array_values(array_map(fn ($p) => trim((string) $p), (array) ($ia['pool'] ?? []))),
            'rows' => array_values(array_map(function ($r) {
                $row = ['label' => trim((string) ($r['label'] ?? ''))];
                if (!empty($r['choices']) && is_array($r['choices'])) {
                    $row['choices'] = array_values(array_map(fn ($c) => trim((string) $c), $r['choices']));
                }
                return $row;
            }, array_filter($ia['rows'], 'is_array'))),
        ];
    }

    protected function paragraphs(mixed $value): array
    {
        $parts = is_array($value) ? $value : preg_split('/\R{2,}/', (string) $value);
        return array_values(array_filter(array_map(fn ($p) => trim((string) $p), $parts), fn ($p) => $p !== ''));
    }
}
