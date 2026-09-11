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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('default_theme')->default('default');
            $table->string('default_locale')->default('en');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('site_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_secure')->default(true);
            $table->timestamps();
        });

        // Seed default site and map existing production and local domains
        $siteId = \Illuminate\Support\Facades\DB::table('sites')->insertGetId([
            'name' => 'ExamTopicsBase',
            'code' => 'examtopicsbase',
            'default_theme' => 'default',
            'default_locale' => 'en',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $appHost = parse_url(config('app.url', 'https://examtopicsbase.com'), PHP_URL_HOST) ?: 'examtopicsbase.com';
        $defaultDomains = app()->environment('production') 
            ? array_unique([$appHost, 'examtopicsbase.com', 'www.examtopicsbase.com'])
            : array_unique([$appHost, 'localhost', '127.0.0.1', 'examsninja.test', 'examtopicsbase.com']);

        foreach ($defaultDomains as $index => $domain) {
            \Illuminate\Support\Facades\DB::table('site_domains')->insert([
                'site_id' => $siteId,
                'domain' => $domain,
                'is_primary' => $domain === 'examtopicsbase.com' || ($index === 0 && app()->environment('production')),
                'is_secure' => !in_array($domain, ['localhost', '127.0.0.1']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_domains');
        Schema::dropIfExists('sites');
    }
};
