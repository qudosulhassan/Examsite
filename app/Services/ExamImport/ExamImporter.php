<?php

namespace App\Services\ExamImport;

use App\Models\Exam;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Writes a parsed + validated exam file into the question bank.
 *
 * The full engine payload (the same shape the PL-900 practice test uses) is kept in
 * questions.question_data['engine'], with image keys swapped for public URLs. The
 * classic columns (text, options, answers, explanation, references, media) are
 * filled too so admin screens and search keep working.
 */
class ExamImporter
{
    protected const IMAGE_TYPES = ['png' => 'png', 'jpeg' => 'jpg', 'jpg' => 'jpg', 'gif' => 'gif', 'webp' => 'webp'];

    protected const DB_TYPES = [
        'multiple-choice' => 'single_choice',
        'multiple-select' => 'multiple_choice',
        'hotspot'         => 'hotspot',
        'drag-drop'       => 'drag_drop',
    ];

    /**
     * @param  array  $skipIds  question ids from the file to leave out (ones with validation errors)
     * @return array{imported:int, skipped:int, deleted:int, images:int}
     */
    public function import(Exam $exam, array $parsed, bool $replaceExisting, array $skipIds = [], ?string $sourceName = null): array
    {
        $skip = array_flip(array_map('strval', $skipIds));
        $urls = $this->storeImages($exam, $parsed['images']);

        return DB::transaction(function () use ($exam, $parsed, $replaceExisting, $skip, $urls, $sourceName) {
            $deleted = 0;
            if ($replaceExisting) {
                $deleted = Question::where('exam_id', $exam->id)->count();
                Question::where('exam_id', $exam->id)->delete();
            }

            $imported = 0;
            $skipped = 0;
            foreach ($parsed['questions'] as $q) {
                if (isset($skip[(string) $q['id']])) {
                    $skipped++;
                    continue;
                }
                $this->saveQuestion($exam, $q, $urls, $sourceName);
                $imported++;
            }

            $exam->update(['question_count' => $exam->questions()->count()]);

            return ['imported' => $imported, 'skipped' => $skipped, 'deleted' => $deleted, 'images' => count($urls)];
        });
    }

    /**
     * Decode data-URI images to files on the public disk. Identical images share one
     * file (named by content hash), so re-importing the same exam doesn't duplicate them.
     *
     * @return array<string,string> image key => public URL
     */
    protected function storeImages(Exam $exam, array $images): array
    {
        $dir = 'questions/' . Str::slug($exam->exam_code ?: ('exam-' . $exam->id));
        $disk = Storage::disk('public');
        $urls = [];

        foreach ($images as $key => $uri) {
            if (!preg_match('#^data:image/([a-z+]+);base64,(.+)$#is', $uri, $m)) {
                continue;
            }
            $ext = self::IMAGE_TYPES[strtolower($m[1])] ?? null;   // SVG is refused: it can carry scripts
            $bytes = base64_decode(preg_replace('/\s+/', '', $m[2]), true);
            if (!$ext || $bytes === false || @getimagesizefromstring($bytes) === false) {
                continue;
            }
            $path = $dir . '/' . sha1($bytes) . '.' . $ext;
            if (!$disk->exists($path)) {
                $disk->put($path, $bytes);
            }
            $urls[$key] = '/storage/' . $path;
        }

        return $urls;
    }

    protected function saveQuestion(Exam $exam, array $q, array $urls, ?string $sourceName): Question
    {
        $a = $q['answer'];
        $url = fn (string $key) => $urls[$key] ?? null;

        $engine = [
            'number'   => $q['id'],
            'topic'    => $q['topic'],
            'type'     => $q['type'],
            'question' => $q['question'],
            'options'  => $q['options'],
            'images'   => array_values(array_filter(array_map($url, $q['images']))),
            'boxCount' => $q['boxCount'],
            'yesNo'    => $q['yesNo'],
            'ia'       => $q['ia'],
            'answer'   => [
                'correctAnswer' => $a['correctAnswer'] ?: null,
                'boxes'         => $a['boxes'],
                'explanation'   => array_values(array_filter(array_map(
                    fn ($e) => is_array($e) ? (($u = $url($e['img'])) ? ['img' => $u] : null) : $e,
                    $a['explanation']
                ))),
                'reference'     => $a['reference'],
                'answerImages'  => array_values(array_filter(array_map($url, $a['answerImages']))),
                'rowAnswers'    => $a['rowAnswers'],
            ],
        ];

        $correct = match (true) {
            (bool) $a['correctAnswer'] => $a['correctAnswer'],
            (bool) $q['ia'] => $a['rowAnswers'] ?? [],
            default => array_map(fn ($b) => rtrim($b['value'], '.'), $a['boxes']),
        };

        $media = [];
        foreach ($engine['images'] as $i => $src) {
            $media[] = ['type' => 'image', 'url' => $src, 'alt' => 'Exhibit ' . ($i + 1), 'sort_order' => $i + 1];
        }
        foreach ($engine['answer']['answerImages'] as $i => $src) {
            $media[] = ['type' => 'answer_image', 'url' => $src, 'alt' => 'Answer ' . ($i + 1), 'sort_order' => 100 + $i];
        }

        // The classic columns below are copies for admin screens; several are varchar(255),
        // so long values are trimmed there. The engine payload keeps everything in full.
        $fit = fn (string $s) => mb_substr($s, 0, 255);

        $question = Question::saveFromUniversalModel([
            'exam_id'         => $exam->id,
            'topic'           => $fit($q['topic']),
            'question_type'   => self::DB_TYPES[$q['type']],
            'question_text'   => $this->html($q['question']),
            'explanation'     => $this->html($engine['answer']['explanation']),
            'is_active'       => true,
            'status'          => 'published',
            'source_type'     => 'exam_file',
            'source_reference' => ['file' => $sourceName, 'number' => $q['id']],
            'options'         => array_map(fn ($o, $i) => ['key' => $fit($o['label']), 'text' => $o['text'], 'sort_order' => $i + 1], $q['options'], array_keys($q['options'])),
            'correct_answers' => array_map($fit, $correct),
            'references'      => array_map(fn ($r) => [
                'title' => $fit($r),
                'url'   => preg_match('#^https?://#i', $r) && strlen($r) <= 255 ? $r : null,
            ], $a['reference']),
            'media'           => $media,
        ]);

        $data = $question->question_data ?? [];
        $data['engine'] = $engine;
        // Lets EngineQuestion notice later edits made in the admin form (see EngineQuestion::payload).
        $data['engine_hashes'] = ['question' => md5($question->question_text), 'explanation' => md5((string) $question->explanation)];
        $data['selection_limit'] = $q['type'] === 'multiple-select' ? max(2, count($a['correctAnswer'] ?? [])) : 1;
        $question->question_data = $data;
        $question->save();

        return $question;
    }

    /** Plain paragraphs (and explanation images) as safe HTML for the classic text columns. */
    protected function html(array $parts): string
    {
        return implode("\n", array_map(
            fn ($p) => is_array($p)
                ? '<p><img src="' . e($p['img']) . '" alt="Explanation image"></p>'
                : '<p>' . nl2br(e($p)) . '</p>',
            $parts
        ));
    }
}
