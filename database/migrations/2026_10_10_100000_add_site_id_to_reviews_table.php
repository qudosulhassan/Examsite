<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reviews belong to the site they were written for. Every existing review is site 1's.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('reviews', 'site_id')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->unsignedBigInteger('site_id')->default(1)->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('reviews', 'site_id')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropIndex(['site_id']);
                $table->dropColumn('site_id');
            });
        }
    }
};
