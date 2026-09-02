<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique()->index();
            $table->string('filename');
            $table->string('source_type')->default('json_import');
            $table->string('format_detected')->nullable();
            $table->foreignId('default_exam_id')->nullable()->constrained('exams')->nullOnDelete();
            $table->string('status')->default('ready_for_review'); // uploaded, processing, ready_for_review, importing, completed, completed_with_errors, failed, cancelled
            $table->json('options')->nullable();
            $table->integer('total_questions')->default(0);
            $table->integer('valid_count')->default(0);
            $table->integer('warning_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->integer('duplicate_count')->default(0);
            $table->integer('imported_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->timestamp('completed_at')->nullable();
        });

        Schema::create('question_import_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('question_import_batches')->cascadeOnDelete();
            $table->integer('source_index')->default(0);
            $table->json('raw_data');
            $table->json('normalized_data');
            $table->string('validation_status')->default('valid'); // valid, warning, error, duplicate
            $table->json('validation_errors')->nullable();
            $table->json('validation_warnings')->nullable();
            $table->string('duplicate_status')->default('none'); // none, exact, similar
            $table->foreignId('duplicate_question_id')->nullable()->constrained('questions')->nullOnDelete();
            $table->integer('similarity_score')->nullable();
            $table->string('review_status')->default('pending'); // pending, approved, rejected, needs_fix
            $table->foreignId('imported_question_id')->nullable()->constrained('questions')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_import_items');
        Schema::dropIfExists('question_import_batches');
    }
};
