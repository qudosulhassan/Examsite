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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->text('description')->nullable();
            $table->enum('category', ['Cloud', 'Security', 'Networking', 'Database', 'DevOps', 'ITSM', 'Other'])->default('Other');
            $table->integer('exam_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->onDelete('cascade');
            $table->string('exam_code')->unique();
            $table->string('exam_name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('topics')->nullable();
            $table->integer('question_count')->default(0);
            $table->integer('passing_score')->default(70);
            $table->enum('difficulty', ['Associate', 'Professional', 'Expert'])->default('Associate');
            $table->enum('exam_type', ['MultipleChoice', 'MultiSelect', 'LabBased'])->default('MultipleChoice');
            $table->decimal('price_pdf', 8, 2)->default(0.00);
            $table->decimal('price_engine', 8, 2)->default(0.00);
            $table->string('demo_pdf_filename')->nullable();
            $table->string('full_pdf_filename')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->longText('question_text');
            $table->text('option_a');
            $table->text('option_b');
            $table->text('option_c')->nullable();
            $table->text('option_d')->nullable();
            $table->string('correct_option'); // A, B, C, D (or comma-separated for multi-select)
            $table->longText('explanation')->nullable();
            $table->string('image_filename')->nullable();
            $table->string('topic')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('vendors');
    }
};
