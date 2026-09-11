<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Site;
use App\Models\SiteDomain;
use App\Models\Exam;
use App\Models\vendor;
use App\Models\SiteExamOverlay;
use App\Services\TechnicalSeoService;
use Illuminate\Support\Facades\Cache;

class MultiSiteIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Site $siteA;
    protected Site $siteB;
    protected vendor $vendor;
    protected Exam $exam1;
    protected Exam $exam2;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        // 1. Site A (ExamTopicsBase, id = 1)
        $this->siteA = Site::updateOrCreate(
            ['code' => 'examtopicsbase'],
            [
                'name' => 'ExamTopicsBase',
                'default_theme' => 'default',
                'default_locale' => 'en',
                'is_active' => true,
                'default_seo_title' => 'ExamTopicsBase - Verified Practice Tests',
                'default_meta_description' => 'Global IT certification exam dumps & prep.',
                'primary_keyword_strategy' => 'exam dumps, practice tests, cert prep',
                'robots_directive' => 'index, follow',
            ]
        );

        SiteDomain::firstOrCreate(['site_id' => $this->siteA->id, 'domain' => 'localhost'], ['is_primary' => true, 'is_secure' => false]);

        // 2. Site B (CertPass Master, id = 2)
        $this->siteB = Site::updateOrCreate(
            ['code' => 'certmaster'],
            [
                'name' => 'CertPass Master',
                'default_theme' => 'modern',
                'default_locale' => 'en',
                'is_active' => true,
                'default_seo_title' => 'CertPass Master - Ultimate Exam Prep',
                'default_meta_description' => 'CertPass Master exclusive exam study guides.',
                'primary_keyword_strategy' => 'certpass, pass guarantee, master test',
                'robots_directive' => 'index, follow, noarchive',
            ]
        );

        SiteDomain::firstOrCreate(['site_id' => $this->siteB->id, 'domain' => 'certmaster.test'], ['is_primary' => true, 'is_secure' => false]);

        // 3. Test vendor and exams
        $this->vendor = Vendor::create([
            'slug' => 'test-vendor',
            'name' => 'Test Vendor',
            'is_active' => true
        ]);

        $this->exam1 = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'TEST-111',
            'exam_name' => 'First Core Exam',
            'slug' => 'test-111',
            'is_active' => true,
            'price_bundle' => 29.99,
            'header_title' => 'TEST-111 Core Exam Preparation',
            'article_content' => '<p>Global article content for TEST-111.</p>',
            'faqs' => [['question' => 'Global Q1?', 'answer' => 'Global A1.']],
        ]);

        $this->exam2 = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'TEST-222',
            'exam_name' => 'Second Core Exam',
            'slug' => 'test-222',
            'is_active' => true,
            'price_bundle' => 49.99,
            'header_title' => 'TEST-222 Core Exam Preparation',
        ]);
    }

    public function test_domain_header_resolves_to_correct_site_instance()
    {
        $responseA = $this->get('http://localhost/');
        $responseA->assertStatus(200);
        $this->assertEquals($this->siteA->id, app('current_site')->id);

        $responseB = $this->get('http://certmaster.test/');
        $responseB->assertStatus(200);
        $this->assertEquals($this->siteB->id, app('current_site')->id);
        $this->assertEquals('CertPass Master', app('current_site')->name);
    }

    public function test_theme_changes_on_site_b_do_not_leak_to_site_a()
    {
        // Attach exam1 to Site B
        SiteExamOverlay::create([
            'site_id' => $this->siteB->id,
            'exam_id' => $this->exam1->id,
            'is_active' => true,
            'is_indexed' => true,
            'custom_header_title' => 'Modern CertPass TEST-111 Edition',
        ]);

        $examPath = 'exams/' . $this->vendor->slug . '/' . $this->exam1->slug;
        
        // Site B (Modern Theme)
        $responseExamB = $this->get('http://certmaster.test/' . $examPath);
        $responseExamB->assertStatus(200);
        $responseExamB->assertSee('MODERN THEME');
        $responseExamB->assertSee('CertPass Master', false);

        // Site A (Default Theme)
        $responseExamA = $this->get('http://localhost/' . $examPath);
        $responseExamA->assertStatus(200);
        $responseExamA->assertDontSee('MODERN THEME');
    }

    public function test_site_specific_overlay_does_not_alter_core_exam_data()
    {
        SiteExamOverlay::create([
            'site_id' => $this->siteB->id,
            'exam_id' => $this->exam1->id,
            'is_active' => true,
            'is_indexed' => true,
            'custom_price_bundle' => 79.99,
            'custom_article_content' => '<p>CertPass exclusive study breakdown</p>',
            'custom_faqs' => [['question' => 'Site B FAQ?', 'answer' => 'Site B Answer.']],
            'meta_title' => 'Exclusive TEST-111 Dumps | CertPass Master',
            'meta_description' => 'CertPass specific description for TEST-111.',
        ]);

        $freshExam = Exam::find($this->exam1->id);
        $this->assertEquals(29.99, (float)$freshExam->price_bundle);
        $this->assertStringContainsString('Global article content for TEST-111.', $freshExam->article_content);

        app()->instance('current_site', $this->siteB);
        $this->assertEquals(79.99, $freshExam->price);
        $this->assertEquals('<p>CertPass exclusive study breakdown</p>', $freshExam->resolved_article_content);
        $this->assertEquals('Exclusive TEST-111 Dumps | CertPass Master', $freshExam->resolved_seo_title);
        $this->assertEquals('CertPass specific description for TEST-111.', $freshExam->resolved_meta_description);

        app()->instance('current_site', $this->siteA);
        $this->assertEquals(29.99, $freshExam->price);
        $this->assertStringContainsString('Global article content for TEST-111.', $freshExam->resolved_article_content);
    }

    public function test_sitemap_is_isolated_per_domain()
    {
        // Site B: Only exam1 assigned and active
        SiteExamOverlay::create([
            'site_id' => $this->siteB->id,
            'exam_id' => $this->exam1->id,
            'is_active' => true,
            'is_indexed' => true,
        ]);

        $sitemapB = $this->get('http://certmaster.test/sitemap.xml');
        $sitemapB->assertStatus(200);
        $sitemapB->assertHeader('Content-Type', 'application/xml');
        $sitemapB->assertSee('certmaster.test/exams/' . $this->vendor->slug . '/' . $this->exam1->slug);
        $sitemapB->assertDontSee('certmaster.test/exams/' . $this->vendor->slug . '/' . $this->exam2->slug);

        // Site A (Root): Both active exams show
        $sitemapA = $this->get('http://localhost/sitemap.xml');
        $sitemapA->assertStatus(200);
        $sitemapA->assertSee('localhost/exams/' . $this->vendor->slug . '/' . $this->exam1->slug);
        $sitemapA->assertSee('localhost/exams/' . $this->vendor->slug . '/' . $this->exam2->slug);
    }

    public function test_robots_txt_contains_site_specific_sitemap_url()
    {
        $robotsA = $this->get('http://localhost/robots.txt');
        $robotsA->assertStatus(200);
        $robotsA->assertSee('Sitemap: http://localhost/sitemap.xml');

        $robotsB = $this->get('http://certmaster.test/robots.txt');
        $robotsB->assertStatus(200);
        $robotsB->assertSee('Sitemap: http://certmaster.test/sitemap.xml');
    }

    public function test_cache_keys_are_isolated_by_host()
    {
        Cache::flush();
        $this->assertFalse(Cache::has('site_domain_localhost'));
        $this->assertFalse(Cache::has('site_domain_certmaster.test'));

        $this->get('http://localhost/');
        $this->assertTrue(Cache::has('site_domain_localhost'));
        $this->assertEquals($this->siteA->id, Cache::get('site_domain_localhost')->id);

        $this->get('http://certmaster.test/');
        $this->assertTrue(Cache::has('site_domain_certmaster.test'));
        $this->assertEquals($this->siteB->id, Cache::get('site_domain_certmaster.test')->id);
    }

    public function test_schema_metadata_isolates_branding_per_site()
    {
        $seoService = app(TechnicalSeoService::class);

        app()->instance('current_site', $this->siteA);
        $schemaA = $seoService->generateOrganizationSchema();
        $this->assertEquals('ExamTopicsBase', $schemaA['name']);

        app()->instance('current_site', $this->siteB);
        $schemaB = $seoService->generateOrganizationSchema();
        $this->assertEquals('CertPass Master', $schemaB['name']);
    }

    public function test_trusted_proxy_detects_https_and_forwarded_host()
    {
        // Simulate request behind a reverse proxy (e.g., Cloudflare or Nginx)
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.195',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => 'localhost',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])->get('/');

        $response->assertStatus(200);

        // Verify request was processed under HTTPS
        $this->assertEquals('https', request()->getScheme());
        $this->assertTrue(request()->isSecure());
        $this->assertEquals('localhost', request()->getHost());
        $this->assertEquals($this->siteA->id, app('current_site')->id);
    }

    public function test_session_cookie_domain_is_host_only_for_independent_sessions()
    {
        // Ensure session domain config is null (host-only cookie), preventing cross-domain leakage
        $sessionDomain = config('session.domain');
        $this->assertNull($sessionDomain, 'SESSION_DOMAIN must be null so each satellite domain maintains its own host-only session.');

        // Test login/session persistence for Site A
        $user = \App\Models\User::factory()->create([
            'email' => 'admin@examtopicsbase.com',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('http://localhost/admin/sites');
        $response->assertStatus(200);
        $this->assertAuthenticatedAs($user);
    }
}