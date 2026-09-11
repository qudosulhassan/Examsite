<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'site_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('site_id')->nullable()->after('user_id')->constrained('sites')->nullOnDelete();
            });

            // Backfill legacy orders created under ExamTopicsBase (Site A / site_id = 1)
            // Provenance confirmed: All 10 existing orders originated between 2026-06-19 and 2026-09-03
            // prior to Site B creation, bearing 'EN-' (ExamsNinja) prefix and examsninja.com emails.
            DB::table('orders')->whereNull('site_id')->update(['site_id' => 1]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'site_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['site_id']);
                $table->dropColumn('site_id');
            });
        }
    }
};
