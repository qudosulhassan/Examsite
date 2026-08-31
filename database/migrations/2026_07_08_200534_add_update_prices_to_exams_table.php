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
            $table->decimal('update_price_3_months', 8, 2)->default(0)->after('price_engine');
            $table->decimal('update_price_6_months', 8, 2)->default(10)->after('update_price_3_months');
            $table->decimal('update_price_12_months', 8, 2)->default(20)->after('update_price_6_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['update_price_3_months', 'update_price_6_months', 'update_price_12_months']);
        });
    }
};
