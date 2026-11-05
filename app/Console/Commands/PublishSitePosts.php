<?php

namespace App\Console\Commands;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Exam;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Publishes the posts in resources/seo-content/blog (posts.json + one HTML file each) to the blog of the site this
 * server runs as (config site.id). Safe to run again: posts are matched by slug and updated in place.
 *
 * The byline is a team account ("<Brand> Editorial Team"), not a person. It is created without a usable password.
 */
class PublishSitePosts extends Command
{
    protected $signature = 'blog:publish-site-posts {--dry-run : Show what would happen without writing} {--author= : Email of the byline account}';

    protected $description = 'Create or update the site\'s launch blog posts from resources/seo-content/blog';

    public function handle(): int
    {
        $dir = resource_path('seo-content/blog');
        $posts = json_decode((string) @file_get_contents($dir . '/posts.json'), true);
        if (!is_array($posts) || !$posts) {
            $this->error('No posts found in ' . $dir . '/posts.json');

            return self::FAILURE;
        }

        $siteId = (int) config('site.id', 1);
        $brand = (string) config('app.name');
        $dry = (bool) $this->option('dry-run');
        $email = $this->option('author') ?: 'editorial@' . (parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'example.com');

        $author = User::withoutGlobalScopes()->where('email', $email)->first();
        if (!$author && !$dry) {
            $author = User::withoutGlobalScopes()->create([
                'site_id'  => $siteId,
                'name'     => "{$brand} Editorial Team",
                'email'    => $email,
                'password' => bcrypt(Str::random(64)),   // nobody signs in as the byline account
                'role'     => 'user',
                'status'   => 'active',
            ]);
        }
        $this->line("Site {$siteId}, byline: " . ($author->name ?? "{$brand} Editorial Team (would be created)") . " <{$email}>");

        foreach ($posts as $p) {
            $html = (string) @file_get_contents($dir . '/' . $p['file']);
            if ($html === '') {
                $this->warn("Missing body for {$p['slug']}");
                continue;
            }
            $words = str_word_count(strip_tags($html));
            $examId = Exam::where('slug', $p['related_exam'] ?? '')->value('id');

            $this->line(sprintf('%s %s (%d words)', $dry ? 'would publish' : 'publishing', $p['slug'], $words));
            if ($dry) {
                continue;
            }

            $category = BlogCategory::firstOrCreate(['slug' => Str::slug($p['category'])], ['name' => $p['category']]);

            $existing = BlogPost::withTrashed()->where('slug', $p['slug'])->first();
            $data = [
                'user_id'          => $author->id,
                'category_id'      => $category->id,
                'title'            => $p['title'],
                'excerpt'          => $p['excerpt'],
                'content'          => $html,
                'meta_title'       => $p['meta_title'],
                'meta_description' => $p['meta_description'],
                'og_title'         => $p['meta_title'],
                'og_description'   => $p['meta_description'],
                'status'           => 'published',
                'reading_time'     => max(1, (int) ceil($words / 200)),
                'related_exam_id'  => $examId,
            ];

            if ($existing) {
                $existing->update($data);
            } else {
                BlogPost::create($data + ['slug' => $p['slug'], 'published_at' => now()]);
            }
        }

        return self::SUCCESS;
    }
}
