<?php

namespace App\Services\ExamImport;

/**
 * Checks a parsed exam file before import.
 *
 * Errors describe questions the test engine cannot run correctly (they are skipped
 * on import); warnings are content gaps worth fixing but safe to import.
 */
class ExamFileValidator
{
    public function validate(array $parsed): array
    {
        $errors = [];
        $warnings = [];
        $byType = [];
        $topics = [];
        $seenIds = [];
        $seenStems = [];
        $images = $parsed['images'];
        $usedImages = [];

        foreach ($parsed['questions'] as $q) {
            $id = $q['id'];
            $a = $q['answer'];
            $add = function (array &$list, string $msg) use ($id) {
                $list[] = ['id' => $id, 'message' => $msg];
            };

            $byType[$q['type']] = ($byType[$q['type']] ?? 0) + 1;
            if ($q['topic'] !== '') {
                $topics[$q['topic']] = ($topics[$q['topic']] ?? 0) + 1;
            }

            if (isset($seenIds[(string) $id])) {
                $add($errors, 'Duplicate question number.');
            }
            $seenIds[(string) $id] = true;

            if (!in_array($q['type'], ExamFileParser::TYPES, true)) {
                $add($errors, "Unknown question type \"{$q['type']}\".");
                continue;
            }
            if (!count($q['question']) && !count($q['images'])) {
                $add($errors, 'Question text is empty.');
            }

            foreach ($q['images'] as $key) {
                $usedImages[$key] = true;
                if (!isset($images[$key])) {
                    $add($errors, "Exhibit image \"{$key}\" is missing from the file.");
                }
            }
            foreach ($a['answerImages'] as $key) {
                $usedImages[$key] = true;
                if (!isset($images[$key])) {
                    $add($errors, "Answer image \"{$key}\" is missing from the file.");
                }
            }
            foreach ($a['explanation'] as $e) {
                if (is_array($e)) {
                    $usedImages[$e['img']] = true;
                    if (!isset($images[$e['img']])) {
                        $add($errors, "Explanation image \"{$e['img']}\" is missing from the file.");
                    }
                }
            }

            $labels = array_column($q['options'], 'label');
            $correct = $a['correctAnswer'] ?? [];

            if (in_array($q['type'], ['multiple-choice', 'multiple-select'], true)) {
                if (count($labels) < 2) {
                    $add($errors, 'Has fewer than two answer options.');
                }
                if (count($labels) !== count(array_unique($labels))) {
                    $add($errors, 'Two options share the same letter.');
                }
                foreach ($q['options'] as $o) {
                    if ($o['text'] === '') {
                        $add($warnings, "Option {$o['label']} has no text.");
                    }
                }
                if (!count($correct)) {
                    $add($errors, 'No correct answer given.');
                }
                foreach ($correct as $letter) {
                    if (!in_array($letter, $labels, true)) {
                        $add($errors, "Correct answer \"{$letter}\" is not one of the options (" . implode(', ', $labels) . ').');
                    }
                }
                if ($q['type'] === 'multiple-choice' && count($correct) > 1) {
                    $add($warnings, 'Marked multiple-choice but has ' . count($correct) . ' correct answers — should it be multiple-select?');
                }
                if ($q['type'] === 'multiple-select' && count($correct) === 1) {
                    $add($warnings, 'Marked multiple-select but has only one correct answer.');
                }
                if (preg_match('/choose (two|three|four)/i', implode(' ', $q['question']), $m)) {
                    $n = ['two' => 2, 'three' => 3, 'four' => 4][strtolower($m[1])];
                    if (count($correct) && count($correct) !== $n) {
                        $add($warnings, "Question says \"Choose {$m[1]}\" but " . count($correct) . ' answer(s) are marked correct.');
                    }
                }
            } elseif ($q['ia']) {
                $rows = count($q['ia']['rows']);
                $rowAnswers = $a['rowAnswers'] ?? [];
                if ($rowAnswers === [] || count($rowAnswers) !== $rows) {
                    $add($errors, "Answer area has {$rows} row(s) but " . count($rowAnswers) . ' row answer(s).');
                } else {
                    foreach ($q['ia']['rows'] as $n => $row) {
                        $choices = $q['ia']['kind'] === 'yesno' ? ['Yes', 'No'] : ($row['choices'] ?? $q['ia']['pool']);
                        if (!in_array($rowAnswers[$n], $choices, true)) {
                            $add($errors, 'Row ' . ($n + 1) . " answer \"{$rowAnswers[$n]}\" is not one of its choices.");
                        }
                    }
                }
                if ($q['ia']['kind'] !== 'yesno' && $q['ia']['kind'] !== 'select' && !count($q['ia']['pool'])) {
                    $add($errors, 'Drag & drop answer area has no items to drag.');
                }
            } elseif ($q['boxCount'] && $q['yesNo']) {
                if (count($a['boxes']) !== $q['boxCount']) {
                    $add($errors, "Has {$q['boxCount']} Yes/No boxes but " . count($a['boxes']) . ' box answer(s).');
                }
            } elseif (!count($a['answerImages']) && !count($a['explanation']) && !count($a['boxes'])) {
                $add($warnings, 'No interactive answer area and no answer image or explanation — it can only be revealed, not checked.');
            }

            if (!count($a['explanation']) && !count($a['reference'])) {
                $add($warnings, 'No explanation or reference link.');
            }

            $stem = strtolower(preg_replace('/\W+/', ' ', implode(' ', $q['question'])));
            // Same wording and same options (and no exhibit that could differ) = a true repeat.
            if (strlen($stem) > 60 && !count($q['images'])) {
                $signature = $stem . '|' . json_encode($q['options']);
                if (isset($seenStems[$signature])) {
                    $add($warnings, "Looks like a repeat of question {$seenStems[$signature]}.");
                }
                $seenStems[$signature] ??= $id;
            }

            $text = implode(' ', array_merge($q['question'], array_column($q['options'], 'text'), array_filter($a['explanation'], 'is_string')));
            if (preg_match('/\x{FFFD}|â€|Ã[\x{80}-\x{BF}]/u', $text)) {
                $add($warnings, 'Contains broken characters (encoding problem from the PDF).');
            }
        }

        $unused = array_diff(array_keys($images), array_keys($usedImages));

        return [
            'errors'   => $errors,
            'warnings' => $warnings,
            'stats'    => [
                'questions'     => count($parsed['questions']),
                'by_type'       => $byType,
                'topics'        => $topics,
                'images'        => count($images),
                'unused_images' => count($unused),
                'bad_ids'       => array_values(array_unique(array_column($errors, 'id'))),
            ],
        ];
    }
}
