<?php

namespace App\Console\Commands;

use App\Models\Exam;
use App\Support\ExamKeywordPlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Writes keyword-based titles, descriptions and H1s for every exam (see App\Support\ExamKeywordPlan).
 *
 *   php artisan exams:apply-keyword-seo --dry-run     show what would change, write nothing
 *   php artisan exams:apply-keyword-seo               apply, after saving the old values to storage/app/seo-backups
 *   php artisan exams:apply-keyword-seo --restore=storage/app/seo-backups/exam-seo-YYYYmmdd-HHMMSS.json
 *
 * Updates go straight to the table (no model events), because saving an exam regenerates the sitemap each time.
 */
class ApplyExamKeywordSeo extends Command
{
    protected $signature = 'exams:apply-keyword-seo
        {--dry-run : Show the changes without writing anything}
        {--only= : Comma-separated exam slugs to process}
        {--restore= : Put back the values saved in a backup file}
        {--sample=5 : How many before/after examples to print}';

    protected $description = 'Apply keyword-based meta title, meta description and H1 to every exam, with a backup and a restore option';

    public function handle(): int
    {
        if ($this->option('restore')) {
            return $this->restore((string) $this->option('restore'));
        }

        $brand = (string) (app()->bound('current_site') && app('current_site') ? app('current_site')->name : config('app.name'));
        $auto = $this->autocomplete();
        $only = array_filter(array_map('trim', explode(',', (string) $this->option('only'))));

        $query = Exam::withoutGlobalScopes()->with('vendor')->withCount('questions')->where('is_active', true);
        if ($only) {
            $query->whereIn('slug', $only);
        }
        $exams = $query->orderBy('id')->get();

        $backup = [];
        $changes = [];
        $shown = 0;
        foreach ($exams as $exam) {
            $plan = ExamKeywordPlan::forExam([
                'code' => (string) $exam->exam_code,
                'name' => (string) $exam->exam_name,
                'vendor' => $exam->vendor?->name,
                'slug' => $exam->slug,
            ], $brand, (int) $exam->questions_count, $auto[$exam->slug] ?? []);

            if ($plan['code'] === '') {
                continue;
            }
            $new = ['meta_title' => $plan['title'], 'meta_description' => $plan['description'], 'header_title' => $plan['h1']];
            $old = ['meta_title' => $exam->meta_title, 'meta_description' => $exam->meta_description, 'header_title' => $exam->header_title];
            if ($new === $old) {
                continue;
            }
            $backup[$exam->id] = ['slug' => $exam->slug] + $old;
            $changes[$exam->id] = $new;

            if ($shown < (int) $this->option('sample')) {
                $this->line("\n{$exam->slug}\n  title: {$old['meta_title']}\n     -> {$new['meta_title']}\n  h1:    {$old['header_title']}\n     -> {$new['header_title']}");
                $shown++;
            }
        }

        $this->info(sprintf("\n%d of %d exams would change.", count($changes), $exams->count()));

        // Vendor pages and the home page defaults
        $vendorChanges = [];
        $vendorBackup = [];
        $home = null;
        $homeBackup = [];
        if (!$only) {
            $vendors = \App\Models\Vendor::withoutGlobalScopes()->where('is_active', true)
                ->withCount(['exams' => fn ($q) => $q->where('is_active', true)])->get();
            foreach ($vendors as $vendor) {
                $plan = ExamKeywordPlan::forVendor((string) $vendor->name, $brand, (int) $vendor->exams_count);
                $old = ['meta_title' => $vendor->meta_title, 'meta_description' => $vendor->meta_description];
                $new = ['meta_title' => $plan['title'], 'meta_description' => $plan['description']];
                if ($old !== $new) {
                    $vendorBackup[$vendor->id] = ['name' => $vendor->name] + $old;
                    $vendorChanges[$vendor->id] = $new;
                }
            }
            $top = $vendors->sortByDesc('exams_count')->take(3)->pluck('name')->all();
            $home = ExamKeywordPlan::forHome($brand, $exams->count(), $top);
            $homeBackup = [
                'default_seo_title' => \App\Models\Setting::get('default_seo_title'),
                'default_meta_description' => \App\Models\Setting::get('default_meta_description'),
            ];
            $this->line("\nhome title: {$home['title']}\nhome desc:  {$home['description']}\n(was: " . ($homeBackup['default_meta_description'] ?: 'the code default') . ')');
        }
        $this->info(count($vendorChanges) . ' vendor pages would change.');

        if ($this->option('dry-run') || (!$changes && !$vendorChanges && !$home)) {
            return self::SUCCESS;
        }

        $dir = storage_path('app/seo-backups');
        File::ensureDirectoryExists($dir);
        $file = $dir . '/exam-seo-' . now()->format('Ymd-His') . '.json';
        File::put($file, json_encode(['exams' => $backup, 'vendors' => $vendorBackup, 'home' => $homeBackup], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info("Backup saved: {$file}");

        DB::transaction(function () use ($changes, $vendorChanges, $home) {
            foreach ($changes as $id => $values) {
                DB::table('exams')->where('id', $id)->update($values + ['updated_at' => now()]);
            }
            foreach ($vendorChanges as $id => $values) {
                DB::table('vendors')->where('id', $id)->update($values + ['updated_at' => now()]);
            }
            if ($home) {
                \App\Models\Setting::set('default_seo_title', $home['title']);
                \App\Models\Setting::set('default_meta_description', $home['description']);
            }
        });

        $this->flush();
        $this->info('Applied. Restore with: php artisan exams:apply-keyword-seo --restore=' . $file);

        return self::SUCCESS;
    }

    protected function restore(string $path): int
    {
        if (!File::exists($path)) {
            $this->error("Backup file not found: {$path}");

            return self::FAILURE;
        }
        $data = json_decode((string) File::get($path), true) ?: [];
        DB::transaction(function () use ($data) {
            foreach (($data['exams'] ?? []) as $id => $values) {
                unset($values['slug']);
                DB::table('exams')->where('id', $id)->update($values + ['updated_at' => now()]);
            }
            foreach (($data['vendors'] ?? []) as $id => $values) {
                unset($values['name']);
                DB::table('vendors')->where('id', $id)->update($values + ['updated_at' => now()]);
            }
            foreach (($data['home'] ?? []) as $key => $value) {
                \App\Models\Setting::set($key, (string) $value);
            }
        });
        $this->flush();
        $this->info('Restored from ' . $path);

        return self::SUCCESS;
    }

    protected function flush(): void
    {
        Cache::forever('page_cache_version', (int) Cache::get('page_cache_version', 1) + 1);
        try {
            $this->callSilently('view:clear');
            $this->callSilently('sitemap:generate');   // lastmod follows updated_at
        } catch (\Throwable $e) {
            // a failed sitemap rebuild must not undo the applied change
        }
    }

    /** @return array<string, array<string>> slug => suggestions Google offered */
    protected function autocomplete(): array
    {
        $file = resource_path('seo-content/keywords/core-autocomplete.json');

        return File::exists($file) ? (json_decode((string) File::get($file), true) ?: []) : [];
    }
}
