<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets several websites share one database without sharing customers or coupons.
 * Every existing row belongs to site 1 (ExamTopicsBase).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'site_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('site_id')->default(1)->after('id');
                $table->dropUnique('users_email_unique');
                $table->unique(['site_id', 'email'], 'users_site_email_unique');
            });
        }

        if (! Schema::hasColumn('coupons', 'site_id')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->unsignedBigInteger('site_id')->default(1)->after('id');
                $table->dropUnique('coupons_code_unique');
                $table->unique(['site_id', 'code'], 'coupons_site_code_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('coupons', 'site_id')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->dropUnique('coupons_site_code_unique');
                $table->unique('code', 'coupons_code_unique');
                $table->dropColumn('site_id');
            });
        }

        if (Schema::hasColumn('users', 'site_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_site_email_unique');
                $table->unique('email', 'users_email_unique');
                $table->dropColumn('site_id');
            });
        }
    }
};
