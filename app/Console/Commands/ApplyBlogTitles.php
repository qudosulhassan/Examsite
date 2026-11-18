<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Sets a clean search title (60 characters at most) for each blog post listed in
 * resources/seo-content/blog-titles-core.json. The brand is added only when it still fits.
 *
 *   php artisan blog:apply-titles --dry-run
 *   php artisan blog:apply-titles
 *   php artisan blog:apply-titles --restore=storage/app/seo-backups/blog-titles-YYYYmmdd-HHMMSS.json
 */
class ApplyBlogTitles extends Command
{
    protected $signature = 'blog:apply-titles {--dry-run} {--restore=}';

    protected $description = 'Apply the reviewed search titles to the blog posts, with a backup and restore';

    public function handle(): int
    {
        if ($this->option('restore')) {
            $rows = json_decode((string) File::get((string) $this->option('restore')), true) ?: [];
            foreach ($rows as $id => $old) {
                DB::table('blog_posts')->where('id', $id)->update(['meta_title' => $old['meta_title'], 'updated_at' => now()]);
            }
            Cache::forever('page_cache_version', (int) Cache::get('page_cache_version', 1) + 1);
            $this->info('Restored ' . count($rows) . ' posts.');

            return self::SUCCESS;
        }

        $map = json_decode((string) File::get(resource_path('seo-content/blog-titles-core.json')), true) ?: [];
        $brand = (string) (app()->bound('current_site') && app('current_site') ? app('current_site')->name : config('app.name'));
        $backup = [];
        $changes = [];

        foreach (BlogPost::where('status', 'published')->get() as $post) {
            $base = $map[$post->slug] ?? null;
            if (!$base) {
                continue;
            }
            $title = mb_strlen("{$base} | {$brand}") <= 60 ? "{$base} | {$brand}" : $base;
            if ($post->meta_title === $title) {
                continue;
            }
            $backup[$post->id] = ['slug' => $post->slug, 'meta_title' => $post->meta_title];
            $changes[$post->id] = $title;
            $this->line(sprintf("%-60s %s\n   was: %s", $title, '(' . mb_strlen($title) . ')', $post->meta_title ?: $post->title));
        }
        $this->info(count($changes) . ' of ' . count($map) . ' titles would change.');
        if ($this->option('dry-run') || !$changes) {
            return self::SUCCESS;
        }

        File::ensureDirectoryExists(storage_path('app/seo-backups'));
        $file = storage_path('app/seo-backups/blog-titles-' . now()->format('Ymd-His') . '.json');
        File::put($file, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        foreach ($changes as $id => $title) {
            DB::table('blog_posts')->where('id', $id)->update(['meta_title' => $title, 'updated_at' => now()]);
        }
        Cache::forever('page_cache_version', (int) Cache::get('page_cache_version', 1) + 1);
        $this->info("Applied. Backup: {$file}");

        return self::SUCCESS;
    }
}
