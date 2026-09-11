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
        Schema::create('site_exam_overlays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->string('custom_slug')->nullable();
            $table->string('custom_header_title')->nullable();
            $table->text('custom_description')->nullable();
            $table->longText('custom_article_content')->nullable();
            $table->json('custom_faqs')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->decimal('custom_price_engine', 8, 2)->nullable();
            $table->decimal('custom_price_pdf', 8, 2)->nullable();
            $table->decimal('custom_price_bundle', 8, 2)->nullable();
            $table->boolean('is_indexed')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['site_id', 'exam_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_exam_overlays');
    }
};
