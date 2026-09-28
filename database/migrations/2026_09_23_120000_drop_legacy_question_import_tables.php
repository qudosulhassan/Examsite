<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * The old PDF/JSON smart-import pipeline was replaced by the exam-file importer
 * (App\Services\ExamImport). Its staging tables are no longer used.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('question_import_items');
        Schema::dropIfExists('question_import_batches');
        Schema::dropIfExists('import_histories');
    }

    public function down(): void
    {
        // The legacy importer was removed; recreating its tables would serve no code.
    }
};
