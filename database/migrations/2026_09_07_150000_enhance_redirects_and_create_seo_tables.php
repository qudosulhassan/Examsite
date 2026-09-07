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
        // 1. Enhance redirects table if not already enhanced
        Schema::table('redirects', function (Blueprint $table) {
            if (!Schema::hasColumn('redirects', 'hits_count')) {
                $table->unsignedBigInteger('hits_count')->default(0)->after('status_code');
            }
            if (!Schema::hasColumn('redirects', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('hits_count');
            }
            if (!Schema::hasColumn('redirects', 'last_accessed_at')) {
                $table->timestamp('last_accessed_at')->nullable()->after('is_active');
            }
        });

        // 2. Create seo_not_found_logs table
        if (!Schema::hasTable('seo_not_found_logs')) {
            Schema::create('seo_not_found_logs', function (Blueprint $table) {
                $table->id();
                $table->string('url', 1000)->index();
                $table->string('referrer', 1000)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->boolean('is_crawler')->default(false);
                $table->unsignedBigInteger('hits_count')->default(1);
                $table->timestamp('last_seen_at')->nullable();
                $table->boolean('is_resolved')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_not_found_logs');

        Schema::table('redirects', function (Blueprint $table) {
            $table->dropColumn(['hits_count', 'is_active', 'last_accessed_at']);
        });
    }
};
