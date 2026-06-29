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
        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->enum('mode', ['practice', 'exam', 'review'])->default('practice');
            $table->integer('total_questions')->default(0);
            $table->integer('answered')->default(0);
            $table->integer('correct')->default(0);
            $table->integer('skipped')->default(0);
            $table->decimal('score_percentage', 5, 2)->default(0.00);
            $table->boolean('passed')->default(false);
            $table->integer('time_taken_seconds')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('test_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('test_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->string('selected_option')->nullable(); // A/B/C/D or comma-separated
            $table->boolean('is_correct')->default(false);
            $table->boolean('is_flagged')->default(false);
            $table->integer('time_spent_seconds')->default(0);
            $table->timestamps();
        });

        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
        Schema::dropIfExists('test_answers');
        Schema::dropIfExists('test_attempts');
    }
};
