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
        Schema::table('reviews', function (Blueprint $table) {
            // Optional display-name override, e.g. "Sarah M." instead of the linked
            // account's full name. The review still requires a real user account
            // (user_id) to keep "Verified Customer Reviews" meaningful.
            $table->string('reviewer_name')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('reviewer_name');
        });
    }
};
