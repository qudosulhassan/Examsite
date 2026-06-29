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
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->onDelete('cascade')->after('id');
            $table->boolean('includes_pdf')->default(true)->after('is_active');
            $table->boolean('includes_te')->default(false)->after('includes_pdf');
            $table->integer('access_days')->nullable()->after('includes_te')->comment('Duration in days. Null means lifetime or subscription based.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['vendor_id', 'includes_pdf', 'includes_te', 'access_days']);
        });
    }
};
