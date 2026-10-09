<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Site;
use App\Models\SiteExamOverlay;
use App\Models\Vendor;
use App\Services\SiteContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SharedCodebaseSiteTwoTest extends TestCase
{
    use RefreshDatabase;

    private function makeExam(): Exam
    {
        $vendor = Vendor::factory()->create(['slug' => 'microsoft', 'is_active' => true]);

        return Exam::factory()->create([
            'vendor_id' => $vendor->id,
            'slug' => 'az-900',
            'exam_code' => 'AZ-900',
            'is_active' => true,
            'price_pdf' => 30,
            'price_engine' => 40,
            'price_bundle' => null,
        ]);
    }

    private function actAsSiteTwo(): Site
    {
        Cache::flush();
        Site::query()->firstOrCreate(['id' => 1], ['name' => 'ExamTopicsBase', 'code' => 'examtopicsbase', 'default_theme' => 'default', 'default_locale' => 'en', 'is_active' => true]);
        $site = Site::query()->firstOrCreate(['id' => 2], ['name' => 'ProExamTopics', 'code' => 'proexamtopics', 'default_theme' => 'default', 'default_locale' => 'en', 'is_active' => true]);
        config(['site.id' => 2, 'site.mode' => 'standalone']);
        app(SiteContext::class)->clear();

        return $site;
    }

    public function test_core_site_ignores_overlay_prices_of_other_sites(): void
    {
        $exam = $this->makeExam();
        $site2 = $this->actAsSiteTwo();
        SiteExamOverlay::create(['site_id' => $site2->id, 'exam_id' => $exam->id, 'custom_price_pdf' => 12.5, 'is_active' => true]);

        config(['site.id' => 1]);
        Cache::flush();
        app(SiteContext::class)->clear();

        $this->assertSame(30.0, $exam->fresh()->resolved_price_pdf);
    }

    public function test_site_two_uses_its_own_overlay_prices_and_update_prices(): void
    {
        $exam = $this->makeExam();
        $site2 = $this->actAsSiteTwo();
        SiteExamOverlay::create([
            'site_id' => $site2->id, 'exam_id' => $exam->id, 'is_active' => true,
            'custom_price_pdf' => 12.5, 'custom_price_engine' => 22.5, 'custom_update_price_pdf_6' => 7.25,
        ]);

        $exam = $exam->fresh();
        $this->assertSame(12.5, $exam->resolved_price_pdf);
        $this->assertSame(22.5, $exam->resolved_price_engine);
        $this->assertSame(7.25, $exam->resolveUpdatePrice('pdf', 6));
        $this->assertSame(round((12.5 + 22.5) * 0.9, 2), $exam->effective_bundle_price);
    }

    public function test_site_two_branding_replaces_site_one_branding(): void
    {
        $this->actAsSiteTwo();
        $settings = app(SiteContext::class)->getAllSettings();

        $this->assertSame('ProExamTopics', $settings['site_name']);
        $this->assertSame('support@proexamtopics.com', $settings['contact_email']);
    }

    public function test_exam_url_style_default_is_prefixed_and_root_style_appends_exam(): void
    {
        $exam = $this->makeExam();
        $this->assertStringEndsWith('/exams/microsoft/az-900', $exam->url);

        config(['site.exam_url_style' => 'root']);
        $this->assertStringEndsWith('/microsoft/az-900-exam', $exam->fresh()->url);
        $this->assertSame('microsoft/az-900-exam', Exam::publicPath('microsoft', 'az-900'));
        $this->assertSame('microsoft/az-900-exam', Exam::publicPath('microsoft', 'az-900-exam'));
    }
}
