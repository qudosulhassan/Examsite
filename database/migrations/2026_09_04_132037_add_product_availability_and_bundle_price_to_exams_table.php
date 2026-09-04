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
        Schema::table('exams', function (Blueprint $table) {
            $table->decimal('price_bundle', 8, 2)->nullable()->after('price_engine');
            $table->boolean('is_pdf_available')->default(true)->after('price_bundle');
            $table->boolean('is_engine_available')->default(true)->after('is_pdf_available');
            $table->boolean('is_bundle_available')->default(true)->after('is_engine_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn([
                'price_bundle',
                'is_pdf_available',
                'is_engine_available',
                'is_bundle_available',
            ]);
        });
    }
};
