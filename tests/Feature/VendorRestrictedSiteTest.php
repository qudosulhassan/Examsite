<?php

namespace Tests\Feature;

use App\Models\Certification;
use App\Models\Exam;
use App\Models\Package;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SITE_VENDORS=microsoft: a single-vendor site must not expose any other vendor's data anywhere.
 */
class VendorRestrictedSiteTest extends TestCase
{
    use RefreshDatabase;

    private Vendor $microsoft;
    private Vendor $cisco;
    private Exam $msExam;
    private Exam $ciscoExam;

    protected function setUp(): void
    {
        parent::setUp();

        $this->microsoft = Vendor::factory()->create(['slug' => 'microsoft', 'name' => 'Microsoft', 'is_active' => true]);
        $this->cisco = Vendor::factory()->create(['slug' => 'cisco', 'name' => 'Cisco', 'is_active' => true]);

        $this->msExam = Exam::factory()->create(['vendor_id' => $this->microsoft->id, 'exam_code' => 'AZ-900', 'exam_name' => 'Azure Fundamentals', 'slug' => 'az-900', 'is_active' => true]);
        $this->ciscoExam = Exam::factory()->create(['vendor_id' => $this->cisco->id, 'exam_code' => '200-301', 'exam_name' => 'CCNA Networking', 'slug' => '200-301', 'is_active' => true]);

        Certification::create(['vendor_id' => $this->microsoft->id, 'name' => 'Azure Fundamentals', 'slug' => 'azure-fundamentals', 'is_active' => true]);
        Certification::create(['vendor_id' => $this->cisco->id, 'name' => 'CCNA', 'slug' => 'ccna', 'is_active' => true]);

        Package::create(['vendor_id' => $this->microsoft->id, 'type' => 'bundle', 'name' => 'MS Bundle', 'slug' => 'ms-bundle', 'price_lifetime' => 99, 'is_active' => true]);
        Package::create(['vendor_id' => $this->cisco->id, 'type' => 'bundle', 'name' => 'Cisco Bundle', 'slug' => 'cisco-bundle', 'price_lifetime' => 99, 'is_active' => true]);

        \App\Models\Vendor::flushSiteVendorCache();
    }

    private function restrict(): void
    {
        config(['site.vendors' => ['microsoft']]);
        Vendor::flushSiteVendorCache();
    }

    public function test_no_restriction_by_default(): void
    {
        $this->assertSame(2, Vendor::count());
        $this->assertSame(2, Exam::count());
        $this->assertSame(2, Certification::count());
        $this->assertSame(2, Package::count());
    }

    public function test_models_only_see_the_allowed_vendor(): void
    {
        $this->restrict();

        $this->assertSame(['microsoft'], Vendor::pluck('slug')->all());
        $this->assertSame(['AZ-900'], Exam::pluck('exam_code')->all());
        $this->assertSame(['azure-fundamentals'], Certification::pluck('slug')->all());
        $this->assertSame(['ms-bundle'], Package::pluck('slug')->all());
        $this->assertNull(Exam::where('exam_code', '200-301')->first());
    }

    public function test_other_vendors_pages_do_not_exist(): void
    {
        $this->restrict();

        $this->get('/vendors/cisco')->assertNotFound();
        $this->get('/exams/cisco/200-301')->assertNotFound();
        $this->get('/exams/microsoft/az-900')->assertOk();
    }

    public function test_vendors_index_goes_straight_to_the_only_vendor(): void
    {
        $this->restrict();

        $this->get('/vendors')->assertStatus(301)->assertRedirect(route('vendors.show', 'microsoft'));
    }

    public function test_search_never_returns_other_vendors(): void
    {
        $this->restrict();

        $this->get('/search?q=200-301')->assertOk()->assertDontSee('CCNA Networking');
        $this->get('/search?q=az-900')->assertOk()->assertSee('AZ-900');
    }

    public function test_homepage_lists_only_the_allowed_vendor(): void
    {
        $this->restrict();

        // The shared marketing copy may name well-known vendors; what must never appear is
        // another vendor's data: its exams or a link to its pages.
        $this->get('/')->assertOk()
            ->assertDontSee('CCNA Networking')
            ->assertDontSee('200-301')
            ->assertDontSee('/vendors/cisco')
            ->assertDontSee('/exams/cisco');
    }

    public function test_a_vendor_slug_missing_from_the_database_shows_nothing_rather_than_everything(): void
    {
        config(['site.vendors' => ['no-such-vendor']]);
        Vendor::flushSiteVendorCache();

        $this->assertSame(0, Exam::count());
        $this->assertSame(0, Vendor::count());
    }
}
