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
        // 1. Enhance sites table with identity, branding, SEO defaults, contact info
        Schema::table('sites', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('name');
            $table->string('favicon')->nullable()->after('logo');
            $table->string('primary_color')->default('#00D4AA')->after('default_theme');
            $table->string('secondary_color')->default('#0A1628')->after('primary_color');
            $table->string('contact_email')->nullable()->after('secondary_color');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->text('contact_address')->nullable()->after('contact_phone');

            // Default SEO Strategy
            $table->string('default_seo_title')->nullable()->after('contact_address');
            $table->text('default_meta_description')->nullable()->after('default_seo_title');
            $table->string('default_h1')->nullable()->after('default_meta_description');
            $table->string('primary_keyword_strategy')->nullable()->after('default_h1');
            $table->text('secondary_keyword_strategy')->nullable()->after('primary_keyword_strategy');
            $table->string('robots_directive')->default('index, follow')->after('secondary_keyword_strategy');
            $table->string('canonical_strategy')->default('self')->after('robots_directive');
            $table->string('og_image')->nullable()->after('canonical_strategy');
            $table->string('twitter_handle')->nullable()->after('og_image');
            $table->string('google_search_console_code')->nullable()->after('twitter_handle');
            $table->string('google_analytics_id')->nullable()->after('google_search_console_code');
            $table->text('custom_head_scripts')->nullable()->after('google_analytics_id');
            $table->text('custom_footer_scripts')->nullable()->after('custom_head_scripts');
        });

        // 2. Enhance site_exam_overlays with H1, keywords, intro, CTA, featured status
        Schema::table('site_exam_overlays', function (Blueprint $table) {
            $table->string('custom_h1')->nullable()->after('custom_header_title');
            $table->string('primary_keyword')->nullable()->after('meta_keywords');
            $table->text('secondary_keywords')->nullable()->after('primary_keyword');
            $table->text('custom_intro')->nullable()->after('custom_description');
            $table->string('custom_cta_text')->nullable()->after('custom_intro');
            $table->string('custom_cta_url')->nullable()->after('custom_cta_text');
            $table->boolean('is_featured')->default(false)->after('is_indexed');
        });

        // 3. Site Vendor visibility mappings
        Schema::create('site_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->unique(['site_id', 'vendor_id']);
        });

        // 4. Site Certification visibility mappings
        if (Schema::hasTable('certifications')) {
            Schema::create('site_certifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
                $table->foreignId('certification_id')->constrained('certifications')->cascadeOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['site_id', 'certification_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_certifications');
        Schema::dropIfExists('site_vendors');

        Schema::table('site_exam_overlays', function (Blueprint $table) {
            $table->dropColumn([
                'custom_h1', 'primary_keyword', 'secondary_keywords',
                'custom_intro', 'custom_cta_text', 'custom_cta_url', 'is_featured'
            ]);
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'logo', 'favicon', 'primary_color', 'secondary_color',
                'contact_email', 'contact_phone', 'contact_address',
                'default_seo_title', 'default_meta_description', 'default_h1',
                'primary_keyword_strategy', 'secondary_keyword_strategy',
                'robots_directive', 'canonical_strategy', 'og_image',
                'twitter_handle', 'google_search_console_code', 'google_analytics_id',
                'custom_head_scripts', 'custom_footer_scripts'
            ]);
        });
    }
};
