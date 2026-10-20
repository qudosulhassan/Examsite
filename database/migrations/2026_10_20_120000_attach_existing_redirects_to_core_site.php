<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Redirect rules created before sites existed have no site_id, which means "applies to every site".
 * They were all written for ExamTopicsBase, so attach them to it; other sites then stop inheriting
 * its old URL rules (e.g. /exams/az-900 -> /exams/microsoft/az-900).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('redirects') && Schema::hasColumn('redirects', 'site_id')) {
            DB::table('redirects')->whereNull('site_id')->update(['site_id' => 1]);
        }
    }

    public function down(): void
    {
        // Intentionally empty: "global" rows cannot be told apart from site 1's afterwards.
    }
};
