<?php

namespace Tests\Feature;

use App\Models\Certification;
use App\Models\Exam;
use App\Models\Package;
use App\Models\Site;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The Microsoft-only StackCert site: its own design, built from the live catalog, never showing
 * another vendor, and free of "dumps" wording.
 */
class StackCertHomepageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('view:clear');
        Cache::flush();

        foreach ([1 => ['ExamTopicsBase', 'examtopicsbase'], 3 => ['StackCert', 'stackcert']] as $id => [$name, $code]) {
            if (! Site::query()->whereKey($id)->exists()) {
                Site::query()->insert(['id' => $id, 'name' => $name, 'code' => $code, 'default_theme' => 'default', 'default_locale' => 'en', 'is_active' => true, 'primary_color' => '#0F6CBD', 'secondary_color' => '#0B3A66', 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        $ms = Vendor::factory()->create(['slug' => 'microsoft', 'name' => 'Microsoft', 'is_active' => true, 'description' => 'ExamTopicsBase offers real exam dumps.']);
        $cisco = Vendor::factory()->create(['slug' => 'cisco', 'name' => 'Cisco', 'is_active' => true]);

        foreach ([['AZ-900', 'Azure Fundamentals'], ['AZ-104', 'Azure Administrator'], ['SC-300', 'Identity and Access Administrator'], ['PL-900', 'Power Platform Fundamentals'], ['MB-800', 'Dynamics 365 Business Central']] as [$code, $name]) {
            Exam::factory()->create(['vendor_id' => $ms->id, 'exam_code' => $code, 'exam_name' => $name, 'slug' => strtolower($code), 'is_active' => true, 'price_pdf' => 30, 'price_engine' => 40]);
        }
        Exam::factory()->create(['vendor_id' => $cisco->id, 'exam_code' => '200-301', 'exam_name' => 'CCNA Networking', 'slug' => '200-301', 'is_active' => true]);

        Package::create(['vendor_id' => $ms->id, 'type' => 'bundle', 'name' => 'All Access Bundle', 'slug' => 'all-access', 'price_lifetime' => 149, 'includes_pdf' => true, 'includes_te' => true, 'is_active' => true]);
        Certification::create(['vendor_id' => $cisco->id, 'name' => 'CCNA', 'slug' => 'ccna', 'is_active' => true]);

        config([
            'site.id' => 3, 'site.mode' => 'standalone', 'site.catalog_mode' => 'all',
            'site.vendors' => ['microsoft'], 'site.avoid_dumps' => true, 'site.show_guarantee' => false,
            'app.name' => 'StackCert', 'app.url' => 'http://stackcert.test',
        ]);
        Vendor::flushSiteVendorCache();
        app('view')->getFinder()->prependLocation(resource_path('views-sites/stackcert'));
    }

    protected function tearDown(): void
    {
        $this->artisan('view:clear');

        parent::tearDown();
    }

    public function test_homepage_uses_the_stackcert_design_and_the_whole_microsoft_catalog(): void
    {
        $this->get('http://stackcert.test/')
            ->assertOk()
            ->assertSee('Pass your Microsoft exam with')
            ->assertSee('5 Microsoft exams')
            ->assertSee('Azure &amp; Cloud', false)
            ->assertSee('Security &amp; Identity', false)
            ->assertSee('Power Platform')
            ->assertSee('Dynamics 365')
            ->assertSee('All Access Bundle')
            ->assertSee('not affiliated with', false)
            ->assertSee('FAQPage', false);
    }

    public function test_homepage_never_shows_another_vendor_or_the_word_dumps(): void
    {
        $html = $this->get('http://stackcert.test/')->assertOk()->getContent();

        $this->assertStringNotContainsString('CCNA Networking', $html);
        $this->assertStringNotContainsString('200-301', $html);
        $this->assertStringNotContainsString('/vendors/cisco', $html);
        $this->assertStringNotContainsString('Exam Topics Base', $html);
        $this->assertDoesNotMatchRegularExpression('/dumps/i', $html);
    }

    public function test_vendor_page_and_exam_page_are_rebranded_and_have_no_dumps_or_guarantee_card(): void
    {
        $vendorPage = $this->get('http://stackcert.test/vendors/microsoft')->assertOk()->getContent();
        $this->assertDoesNotMatchRegularExpression('/dumps/i', $vendorPage);
        $this->assertStringContainsString('StackCert', $vendorPage);

        $examPage = $this->get('http://stackcert.test/exams/microsoft/az-900')->assertOk()->getContent();
        $this->assertStringContainsString('Practice Exam &amp; Study Guide | StackCert', $examPage);
        $this->assertDoesNotMatchRegularExpression('/dumps/i', $examPage);
        $this->assertStringNotContainsString('100% Pass Guarantee', $examPage);
    }

    public function test_other_vendors_are_not_reachable(): void
    {
        $this->get('http://stackcert.test/vendors/cisco')->assertNotFound();
        $this->get('http://stackcert.test/exams/cisco/200-301')->assertNotFound();
        $this->get('http://stackcert.test/vendors')->assertRedirect();
    }
}
