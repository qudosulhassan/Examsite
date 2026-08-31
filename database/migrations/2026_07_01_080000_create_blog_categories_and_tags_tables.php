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
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('blog_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('blog_categories')->onDelete('set null');
            $table->unsignedBigInteger('views_count')->default(0);
            $table->boolean('is_featured')->default(false);
        });

        Schema::create('blog_post_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained('blog_posts')->onDelete('cascade');
            $table->foreignId('blog_tag_id')->constrained('blog_tags')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_post_tag');
        
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'views_count', 'is_featured']);
        });

        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_categories');
    }
};
