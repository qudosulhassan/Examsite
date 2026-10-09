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
        Schema::table('site_exam_overlays', function (Blueprint $table) {
            if (!Schema::hasColumn('site_exam_overlays', 'custom_topics')) {
                $table->json('custom_topics')->nullable()->after('custom_faqs');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_exam_overlays', function (Blueprint $table) {
            if (Schema::hasColumn('site_exam_overlays', 'custom_topics')) {
                $table->dropColumn('custom_topics');
            }
        });
    }
};
