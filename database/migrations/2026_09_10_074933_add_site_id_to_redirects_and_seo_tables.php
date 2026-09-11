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
        if (Schema::hasTable('redirects') && !Schema::hasColumn('redirects', 'site_id')) {
            Schema::table('redirects', function (Blueprint $table) {
                $table->foreignId('site_id')->nullable()->after('id')->constrained('sites')->nullOnDelete();
            });
        }

        if (Schema::hasTable('seo_not_found_logs') && !Schema::hasColumn('seo_not_found_logs', 'site_id')) {
            Schema::table('seo_not_found_logs', function (Blueprint $table) {
                $table->foreignId('site_id')->nullable()->after('id')->constrained('sites')->nullOnDelete();
                $table->string('host')->nullable()->after('site_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('redirects') && Schema::hasColumn('redirects', 'site_id')) {
            Schema::table('redirects', function (Blueprint $table) {
                $table->dropForeign(['site_id']);
                $table->dropColumn('site_id');
            });
        }

        if (Schema::hasTable('seo_not_found_logs') && Schema::hasColumn('seo_not_found_logs', 'site_id')) {
            Schema::table('seo_not_found_logs', function (Blueprint $table) {
                $table->dropForeign(['site_id']);
                $table->dropColumn(['site_id', 'host']);
            });
        }
    }
};
