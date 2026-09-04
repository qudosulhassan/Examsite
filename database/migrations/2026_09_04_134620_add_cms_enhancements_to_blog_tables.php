<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_posts', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('blog_posts', 'featured_image_alt')) {
                $table->string('featured_image_alt')->nullable()->after('featured_image');
            }
            if (!Schema::hasColumn('blog_posts', 'canonical_url')) {
                $table->string('canonical_url')->nullable()->after('meta_description');
            }
            if (!Schema::hasColumn('blog_posts', 'og_title')) {
                $table->string('og_title')->nullable()->after('canonical_url');
            }
            if (!Schema::hasColumn('blog_posts', 'og_description')) {
                $table->text('og_description')->nullable()->after('og_title');
            }
            if (!Schema::hasColumn('blog_posts', 'og_image')) {
                $table->string('og_image')->nullable()->after('og_description');
            }
        });

        Schema::table('blog_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('blog_comments', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'featured_image_alt',
                'canonical_url',
                'og_title',
                'og_description',
                'og_image',
            ]);
        });

        Schema::table('blog_comments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
