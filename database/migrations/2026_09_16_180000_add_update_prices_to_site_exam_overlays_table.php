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
        if (Schema::hasColumn('site_exam_overlays', 'custom_update_price_bundle_3')) {
            return;
        }

        Schema::table('site_exam_overlays', function (Blueprint $table) {
            // Bundle Updates (3, 6, 12 Months)
            $table->decimal('custom_update_price_bundle_3', 8, 2)->nullable()->after('custom_price_bundle');
            $table->decimal('custom_update_price_bundle_6', 8, 2)->nullable()->after('custom_update_price_bundle_3');
            $table->decimal('custom_update_price_bundle_12', 8, 2)->nullable()->after('custom_update_price_bundle_6');

            // PDF Guide Updates (3, 6, 12 Months)
            $table->decimal('custom_update_price_pdf_3', 8, 2)->nullable()->after('custom_price_pdf');
            $table->decimal('custom_update_price_pdf_6', 8, 2)->nullable()->after('custom_update_price_pdf_3');
            $table->decimal('custom_update_price_pdf_12', 8, 2)->nullable()->after('custom_update_price_pdf_6');

            // Test Engine Updates (3, 6, 12 Months)
            $table->decimal('custom_update_price_engine_3', 8, 2)->nullable()->after('custom_price_engine');
            $table->decimal('custom_update_price_engine_6', 8, 2)->nullable()->after('custom_update_price_engine_3');
            $table->decimal('custom_update_price_engine_12', 8, 2)->nullable()->after('custom_update_price_engine_6');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_exam_overlays', function (Blueprint $table) {
            $table->dropColumn([
                'custom_update_price_bundle_3',
                'custom_update_price_bundle_6',
                'custom_update_price_bundle_12',
                'custom_update_price_pdf_3',
                'custom_update_price_pdf_6',
                'custom_update_price_pdf_12',
                'custom_update_price_engine_3',
                'custom_update_price_engine_6',
                'custom_update_price_engine_12',
            ]);
        });
    }
};
