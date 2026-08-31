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
            $table->decimal('update_price_3_months', 8, 2)->nullable()->after('price_lifetime');
            $table->decimal('update_price_6_months', 8, 2)->nullable()->after('update_price_3_months');
            $table->decimal('update_price_12_months', 8, 2)->nullable()->after('update_price_6_months');
            $table->decimal('license_price_individual', 8, 2)->nullable()->after('update_price_12_months');
            $table->decimal('license_price_corporate', 8, 2)->nullable()->after('license_price_individual');
            $table->decimal('license_price_trainer', 8, 2)->nullable()->after('license_price_corporate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'update_price_3_months',
                'update_price_6_months',
                'update_price_12_months',
                'license_price_individual',
                'license_price_corporate',
                'license_price_trainer',
            ]);
        });
    }
};
