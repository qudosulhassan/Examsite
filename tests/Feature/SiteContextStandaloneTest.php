<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Question;
use App\Models\Site;
use App\Models\SiteDomain;
use App\Models\SiteExamOverlay;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Vendor;
use App\Services\SiteContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteContextStandaloneTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Site $site1;
    private Site $site2;
    private Vendor $vendor;
    private Exam $exam1;

    protected function setUp(): void
    {
        parent::setUp();

        // Admin user
        $this->admin = User::factory()->create([
            'email' => 'admin@examtopicsbase.com',
            'role'  => 'admin',
        ]);

        // Seed Site 1 (ExamTopicsBase - Root)
        $this->site1 = Site::updateOrCreate(
            ['id' => 1],
            [
                'name'                     => 'ExamTopicsBase',
                'code'                     => 'examtopicsbase',
                'default_theme'            => 'default',
                'default_locale'           => 'en',
                'is_active'                => true,
                'default_seo_title'        => 'ExamTopicsBase Master Title',
                'default_meta_description' => 'Master description for ExamTopicsBase',
            ]
        );

        SiteDomain::firstOrCreate(
            ['domain' => 'localhost'],
            ['site_id' => $this->site1->id, 'is_primary' => true, 'is_secure' => false]
        );

        // Seed Site 2 (CertPass Master - Secondary)
        $this->site2 = Site::updateOrCreate(
            ['id' => 2],
            [
                'name'                     => 'CertPass Master',
                'code'                     => 'certmaster',
                'default_theme'            => 'modern',
                'default_locale'           => 'en',
                'is_active'                => true,
                'default_seo_title'        => 'CertMaster Satellite Title',
                'default_meta_description' => 'Secondary description for CertMaster',
            ]
        );

        SiteDomain::firstOrCreate(
            ['domain' => 'certmaster.test'],
            ['site_id' => $this->site2->id, 'is_primary' => true, 'is_secure' => false]
        );

        // Seed a global Vendor and Exam
        $this->vendor = Vendor::create([
            'name'       => 'Amazon Web Services',
            'slug'       => 'amazon',
            'category'   => 'Cloud',
            'is_active'  => true,
            'exam_count' => 1,
        ]);

        $this->exam1 = Exam::create([
            'vendor_id'           => $this->vendor->id,
            'exam_code'           => 'SAA-C03',
            'exam_name'           => 'AWS Solutions Architect Associate',
            'slug'                => 'saa-c03',
            'description'         => 'Global description for AWS SAA-C03.',
            'question_count'      => 65,
            'passing_score'       => 720,
            'difficulty'          => 'Associate',
            'price_bundle'        => 49.99,
            'price_engine'        => 39.99,
            'price_pdf'           => 29.99,
            'is_active'           => true,
            'is_featured'         => true,
            'is_pdf_available'    => true,
            'is_engine_available' => true,
            'is_bundle_available' => true,
            'article_content'     => '<p>Global article content for SAA-C03.</p>',
            'faqs'                => [['question' => 'Global FAQ?', 'answer' => 'Global Answer.']],
        ]);
    }

    /**
     * 1. Prove APP_SITE_ID=1 standalone mode resolves Site 1 regardless of host header.
     */
    public function test_standalone_mode_resolves_site_1_regardless_of_host_header()
    {
        config([
            'site.id'   => 1,
            'site.mode' => 'standalone',
        ]);

        /** @var SiteContext $context */
        $context = app(SiteContext::class);
        $context->clear();

        // Request with foreign host header
        $response = $this->get('http://random-host.com/');
        $response->assertStatus(200);

        // Should resolve to Site 1
        $this->assertEquals(1, $context->id());
        $this->assertEquals('ExamTopicsBase', $context->site()->name);
        $this->assertTrue($context->isRoot());
        $this->assertTrue($context->isStandalone());
        $this->assertEquals(1, app('current_site')->id);
    }

    /**
     * 2. Prove site-specific settings use Site 1.
     */
    public function test_site_specific_settings_use_site_1()
    {
        config([
            'site.id'   => 1,
            'site.mode' => 'standalone',
        ]);

        SiteSetting::create([
            'site_id' => 1,
            'key'     => 'custom_support_hours',
            'value'   => '24/7 Available for ExamTopicsBase',
        ]);

        SiteSetting::create([
            'site_id' => 2,
            'key'     => 'custom_support_hours',
            'value'   => 'Mon-Fri 9-5 for Site 2',
        ]);

        /** @var SiteContext $context */
        $context = app(SiteContext::class);
        $context->clear();

        $this->assertEquals('24/7 Available for ExamTopicsBase', $context->setting('custom_support_hours'));
        $this->assertEquals('ExamTopicsBase Master Title', $context->seo()['default_title']);
        $this->assertEquals('default', $context->theme());
    }

    /**
     * 3. Prove site-specific exam overlays use Site 1.
     */
    public function test_site_specific_exam_overlays_use_site_1()
    {
        config([
            'site.id'   => 1,
            'site.mode' => 'standalone',
        ]);

        SiteExamOverlay::create([
            'site_id'                => 1,
            'exam_id'                => $this->exam1->id,
            'custom_header_title'    => 'ExamTopicsBase Exclusive SAA-C03',
            'custom_article_content' => '<p>Custom article for Site 1.</p>',
            'custom_faqs'            => [['question' => 'Site 1 FAQ?', 'answer' => 'Site 1 Answer.']],
            'custom_price_bundle'    => 35.00,
            'is_active'              => true,
            'is_indexed'             => true,
        ]);

        // Overlay for Site 2 should not leak
        SiteExamOverlay::create([
            'site_id'             => 2,
            'exam_id'             => $this->exam1->id,
            'custom_header_title' => 'Site 2 Exclusive Title',
            'custom_price_bundle' => 99.00,
            'is_active'           => true,
        ]);

        /** @var SiteContext $context */
        $context = app(SiteContext::class);
        $context->clear();

        $overlay = $context->getOverlayForExam($this->exam1);
        $this->assertNotNull($overlay);
        $this->assertEquals(1, $overlay->site_id);
        $this->assertEquals('ExamTopicsBase Exclusive SAA-C03', $overlay->custom_header_title);
        $this->assertEquals('<p>Custom article for Site 1.</p>', $this->exam1->resolved_article_content);
        $this->assertEquals(35.00, $this->exam1->price);
    }

    /**
     * 4. Prove global exams and questions remain unchanged in master catalog.
     */
    public function test_global_exams_and_questions_remain_unchanged()
    {
        $question = Question::create([
            'exam_id'       => $this->exam1->id,
            'question_type' => 'SingleChoice',
            'question_text' => 'What AWS service provides object storage?',
            'difficulty'    => 'Associate',
            'is_active'     => true,
            'sort_order'    => 1,
        ]);

        // Refresh model from database
        $this->exam1->refresh();

        $this->assertEquals('SAA-C03', $this->exam1->exam_code);
        $this->assertEquals('AWS Solutions Architect Associate', $this->exam1->exam_name);
        $this->assertEquals('<p>Global article content for SAA-C03.</p>', $this->exam1->article_content);
        $this->assertEquals('What AWS service provides object storage?', $question->question_text);
        $this->assertDatabaseCount('exams', 1);
        $this->assertDatabaseCount('questions', 1);
    }

    /**
     * 5. Prove existing ExamTopicsBase routes still work.
     */
    public function test_existing_examtopicsbase_routes_still_work()
    {
        config(['site.id' => 1, 'site.mode' => 'standalone']);

        $this->get('/')->assertStatus(200);
        $this->get('/vendors')->assertStatus(200);
        $this->get('/vendors/amazon')->assertStatus(200);
        $this->get('/exams/amazon/saa-c03')->assertStatus(200);
        $this->get('/faq')->assertStatus(200);
        $this->get('/about')->assertStatus(200);
        $this->get('/contact')->assertStatus(200);
        $this->get('/search?q=AWS')->assertStatus(200);
    }

    /**
     * 6. Prove existing admin panel still works.
     */
    public function test_existing_admin_still_works()
    {
        config(['site.id' => 1, 'site.mode' => 'standalone']);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.exams.index'))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('admin.sites.index'))->assertStatus(200);
    }

    /**
     * 7. Prove existing SEO still works (sitemap and robots).
     */
    public function test_existing_seo_still_works()
    {
        config(['site.id' => 1, 'site.mode' => 'standalone']);

        $sitemapResponse = $this->get('/sitemap.xml');
        $sitemapResponse->assertStatus(200);
        $this->assertStringContainsString('urlset', $sitemapResponse->getContent());
        $this->assertStringContainsString('/exams/amazon/saa-c03', $sitemapResponse->getContent());

        $robotsResponse = $this->get('/robots.txt');
        $robotsResponse->assertStatus(200);
        $this->assertStringContainsString('User-agent: *', $robotsResponse->getContent());
        $this->assertStringContainsString('sitemap.xml', $robotsResponse->getContent());
    }

    /**
     * 8. Prove existing test engine works.
     */
    public function test_existing_test_engine_works()
    {
        config(['site.id' => 1, 'site.mode' => 'standalone']);

        $response = $this->get('/test-engine');
        $response->assertStatus(200);
    }

    /**
     * 9. Prove existing checkout/cart works.
     */
    public function test_existing_checkout_and_cart_work()
    {
        config(['site.id' => 1, 'site.mode' => 'standalone']);

        $response = $this->get('/cart');
        $response->assertStatus(200);
    }
}
