<?php

namespace App\Console\Commands;

use App\Models\Exam;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Finds exam pages whose FAQ contradicts the exam details table on the exam duration (for example the table says
 * 50 minutes and a FAQ answer says 60).
 *
 *   php artisan exams:audit-content                           list the conflicts
 *   php artisan exams:audit-content --drop-conflicting-faqs   remove the conflicting FAQ entries (backup saved)
 *   php artisan exams:audit-content --restore=<backup file>
 *
 * It cannot know which figure is right. Dropping the FAQ entry leaves one figure on the page (the table), and the
 * listed exams should be checked against the vendor's own page.
 */
class AuditExamContent extends Command
{
    protected $signature = 'exams:audit-content {--drop-conflicting-faqs} {--restore=}';

    protected $description = 'Report exam pages whose FAQ contradicts the details table on duration; optionally drop the conflicting FAQ entries';

    public function handle(): int
    {
        if ($this->option('restore')) {
            $rows = json_decode((string) File::get((string) $this->option('restore')), true) ?: [];
            foreach ($rows as $id => $row) {
                DB::table('exams')->where('id', $id)->update(['faqs' => json_encode($row['faqs'], JSON_UNESCAPED_UNICODE), 'updated_at' => now()]);
            }
            Cache::forever('page_cache_version', (int) Cache::get('page_cache_version', 1) + 1);
            $this->info('Restored ' . count($rows) . ' exams.');

            return self::SUCCESS;
        }

        $backup = [];
        $found = 0;
        foreach (Exam::withoutGlobalScopes()->where('is_active', true)->whereNotNull('article_content')->get() as $exam) {
            // cells hold nested tags (<p><span><strong>), so compare on the text with a separator between cells
            $cells = strip_tags(str_replace(['</td>', '</th>'], ' | ', (string) $exam->article_content));
            if (!preg_match('/Exam Duration\s*\|\s*(\d{2,3})\s*min/i', $cells, $m)) {
                continue;
            }
            $table = $m[1];
            $faqs = is_array($exam->faqs) ? $exam->faqs : (json_decode((string) $exam->faqs, true) ?: []);
            $keep = [];
            $conflict = [];
            foreach ($faqs as $faq) {
                $text = ($faq['question'] ?? '') . ' ' . ($faq['answer'] ?? '');
                if (preg_match('/\bhours?\b/i', $text)) {
                    $keep[] = $faq;   // "5 hours 18 minutes" is not comparable with a plain minutes figure
                    continue;
                }
                preg_match_all('/(\d{2,3})\s*(?:-|to)?\s*(?:minutes|min)\b/i', $text, $nums);
                if ($nums[1] && !in_array($table, $nums[1], true)) {
                    $conflict[] = $faq;
                    continue;
                }
                $keep[] = $faq;
            }
            if (!$conflict) {
                continue;
            }
            $found++;
            $this->line(sprintf('%-16s table says %s min; FAQ says: %s', $exam->slug, $table, mb_substr(implode(' | ', array_map(fn ($f) => $f['answer'] ?? '', $conflict)), 0, 90)));
            $backup[$exam->id] = ['slug' => $exam->slug, 'faqs' => $faqs];
            if ($this->option('drop-conflicting-faqs')) {
                DB::table('exams')->where('id', $exam->id)->update(['faqs' => json_encode($keep, JSON_UNESCAPED_UNICODE), 'updated_at' => now()]);
            }
        }

        $this->info("{$found} exams with a duration conflict.");
        if ($found && $this->option('drop-conflicting-faqs')) {
            File::ensureDirectoryExists(storage_path('app/seo-backups'));
            $file = storage_path('app/seo-backups/exam-faqs-' . now()->format('Ymd-His') . '.json');
            File::put($file, json_encode($backup, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            Cache::forever('page_cache_version', (int) Cache::get('page_cache_version', 1) + 1);
            $this->info("Conflicting FAQ entries removed. Backup: {$file}");
        }

        return self::SUCCESS;
    }
}
