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
        Schema::table('packages', function (Blueprint $table) {
            $table->integer('exam_limit')->nullable()->after('features')->comment('Null means unlimited exams for the selected scope.');
            $table->integer('seat_count')->nullable()->default(1)->after('exam_limit')->comment('Number of user seats included.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['exam_limit', 'seat_count']);
        });
    }
};
