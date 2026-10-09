<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gives every site its own blog, tags, categories, comments, subscribers and media library.
 * All existing rows belong to site 1 (ExamTopicsBase). Slugs/emails become unique per site.
 */
return new class extends Migration
{
    /** table => [old unique index name, column, new unique index name] (null = no unique change) */
    private array $tables = [
        'blog_posts'       => ['blog_posts_slug_unique', 'slug', 'blog_posts_site_slug_unique'],
        'blog_categories'  => ['blog_categories_slug_unique', 'slug', 'blog_categories_site_slug_unique'],
        'blog_tags'        => ['blog_tags_slug_unique', 'slug', 'blog_tags_site_slug_unique'],
        'blog_subscribers' => ['blog_subscribers_email_unique', 'email', 'blog_subscribers_site_email_unique'],
        'blog_comments'    => null,
        'media'            => null,
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $unique) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'site_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($unique) {
                $t->unsignedBigInteger('site_id')->default(1)->after('id')->index();

                if ($unique) {
                    [$old, $column, $new] = $unique;
                    $t->dropUnique($old);
                    $t->unique(['site_id', $column], $new);
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $unique) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'site_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($unique) {
                if ($unique) {
                    [$old, $column, $new] = $unique;
                    $t->dropUnique($new);
                    $t->unique($column, $old);
                }
                $t->dropIndex(['site_id']);
                $t->dropColumn('site_id');
            });
        }
    }
};
