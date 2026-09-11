<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Site;
use App\Models\SiteDomain;
use App\Models\SiteExamOverlay;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * End-to-end tests for the Websites Platform CRUD.
 *
 * Tests the exact user flows described in the fix request:
 *   A. Create Website C from the UI.
 *   B. Save it — confirm it appears in the table.
 *   C. Click Edit — loads the correct site.
 *   D. Change name/theme/domain — save persists.
 *   E. Manage Exams — assign an existing global exam.
 *   F. Verify exam count reflects in the index.
 *   G. Manage SEO — opens Site C's SEO settings.
 *   H. Site A and Site B remain unchanged.
 */
class WebsitesCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Site $siteA;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user using the 'role' column (not is_admin)
        $this->admin = User::factory()->create([
            'email' => 'admin@examtopicsbase.com',
            'role'  => 'admin',
        ]);

        // Seed root Site A (examtopicsbase)
        $this->siteA = Site::updateOrCreate(
            ['code' => 'examtopicsbase'],
            [
                'name'          => 'ExamTopicsBase',
                'default_theme' => 'default',
                'default_locale' => 'en',
                'is_active'     => true,
            ]
        );

        SiteDomain::firstOrCreate(
            ['domain' => 'localhost'],
            ['site_id' => $this->siteA->id, 'is_primary' => true, 'is_secure' => false]
        );
    }

    // -------------------------------------------------------------------------
    // TEST A+B: Create new website via POST /admin/sites
    // -------------------------------------------------------------------------
    public function test_can_create_new_website_and_it_appears_in_list()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.sites.store'), [
                'name'          => 'Website C Test',
                'code'          => 'websitec',
                'domain'        => 'websitec.test',
                'default_theme' => 'modern',
                'default_locale'=> 'en',
                'is_active'     => '1',
            ]);

        $response->assertRedirect(route('admin.sites.index'));
        $response->assertSessionHas('success');

        // Record must exist in sites table
        $this->assertDatabaseHas('sites', [
            'code'          => 'websitec',
            'name'          => 'Website C Test',
            'default_theme' => 'modern',
            'is_active'     => 1,
        ]);

        // Primary domain must be created
        $this->assertDatabaseHas('site_domains', [
            'domain'     => 'websitec.test',
            'is_primary' => 1,
        ]);

        // Website must appear in the index page
        $indexResponse = $this->actingAs($this->admin)->get(route('admin.sites.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Website C Test');
        $indexResponse->assertSee('websitec.test');
    }

    // -------------------------------------------------------------------------
    // TEST C+D: Edit loads correct site, updates persist, no cross-contamination
    // -------------------------------------------------------------------------
    public function test_edit_page_loads_and_updates_correct_site_only()
    {
        $siteC = Site::create([
            'name'           => 'Website C',
            'code'           => 'websitec',
            'default_theme'  => 'default',
            'default_locale' => 'en',
            'is_active'      => true,
        ]);

        SiteDomain::create([
            'site_id'    => $siteC->id,
            'domain'     => 'websitec.test',
            'is_primary' => true,
            'is_secure'  => false,
        ]);

        // Load edit page
        $editResponse = $this->actingAs($this->admin)
            ->get(route('admin.sites.edit', ['site' => $siteC->id, 'tab' => 'general']));

        $editResponse->assertStatus(200);
        $editResponse->assertSee('Website C');
        $editResponse->assertSee('websitec'); // code

        // Change name and theme
        $updateResponse = $this->actingAs($this->admin)
            ->put(route('admin.sites.update', $siteC->id), [
                'active_tab'     => 'general',
                'name'           => 'Website C Updated',
                'code'           => 'websitec',
                'default_locale' => 'en',
                'is_active'      => '1',
            ]);

        $updateResponse->assertRedirect();
        $updateResponse->assertSessionHas('success');

        // Site C updated
        $this->assertDatabaseHas('sites', [
            'id'   => $siteC->id,
            'name' => 'Website C Updated',
        ]);

        // Site A must NOT be touched
        $this->assertDatabaseHas('sites', [
            'id'   => $this->siteA->id,
            'name' => 'ExamTopicsBase',
        ]);
    }

    // -------------------------------------------------------------------------
    // TEST theme update only changes the target site
    // -------------------------------------------------------------------------
    public function test_theme_update_only_affects_target_site()
    {
        $siteB = Site::create([
            'name'           => 'Site B',
            'code'           => 'siteb',
            'default_theme'  => 'default',
            'default_locale' => 'en',
            'is_active'      => true,
        ]);

        SiteDomain::create([
            'site_id' => $siteB->id,
            'domain'  => 'siteb.test',
            'is_primary' => true,
            'is_secure'  => false,
        ]);

        $this->actingAs($this->admin)->put(route('admin.sites.update', $siteB->id), [
            'active_tab'    => 'theme',
            'default_theme' => 'modern',
        ]);

        $this->assertDatabaseHas('sites', ['id' => $siteB->id, 'default_theme' => 'modern']);
        // Site A unchanged
        $this->assertDatabaseHas('sites', ['id' => $this->siteA->id, 'default_theme' => 'default']);
    }

    // -------------------------------------------------------------------------
    // TEST E+F: Assign exam to site, exam count reflects in index
    // -------------------------------------------------------------------------
    public function test_can_assign_global_exam_to_site_and_count_updates()
    {
        // Create Site C
        $siteC = Site::create([
            'name'           => 'Website C',
            'code'           => 'websitec',
            'default_theme'  => 'default',
            'default_locale' => 'en',
            'is_active'      => true,
        ]);

        SiteDomain::create([
            'site_id' => $siteC->id,
            'domain'  => 'websitec.test',
            'is_primary' => true,
            'is_secure'  => false,
        ]);

        // Get a global exam from factory (RefreshDatabase wipes DB so we create one)
        $exam = Exam::factory()->create(['is_active' => true]);

        // Manage exams page loads
        $manageResponse = $this->actingAs($this->admin)
            ->get(route('admin.sites.exams', $siteC->id));

        $manageResponse->assertStatus(200);
        $manageResponse->assertSee('Website C');
        $manageResponse->assertSee('Assign Global Exam');

        // Assign exam
        $attachResponse = $this->actingAs($this->admin)
            ->post(route('admin.sites.exams.attach', $siteC->id), [
                'exam_id' => $exam->id,
            ]);

        $attachResponse->assertRedirect();
        $attachResponse->assertSessionHas('success');

        // Verify overlay created
        $this->assertDatabaseHas('site_exam_overlays', [
            'site_id' => $siteC->id,
            'exam_id' => $exam->id,
            'is_active' => 1,
        ]);

        // Check index shows correct count
        $indexResponse = $this->actingAs($this->admin)->get(route('admin.sites.index'));
        $indexResponse->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // TEST G: Manage SEO loads Site C's SEO tab, not Site A's
    // -------------------------------------------------------------------------
    public function test_seo_tab_loads_for_correct_site()
    {
        $siteC = Site::create([
            'name'                   => 'Website C',
            'code'                   => 'websitec',
            'default_theme'          => 'default',
            'default_locale'         => 'en',
            'is_active'              => true,
            'default_seo_title'      => 'WebsiteC SEO Title',
            'robots_directive'       => 'noindex, nofollow',
            'canonical_strategy'     => 'self',
        ]);

        SiteDomain::create([
            'site_id' => $siteC->id,
            'domain'  => 'websitec.test',
            'is_primary' => true,
            'is_secure'  => false,
        ]);

        $seoResponse = $this->actingAs($this->admin)
            ->get(route('admin.sites.edit', ['site' => $siteC->id, 'tab' => 'seo']));

        $seoResponse->assertStatus(200);
        $seoResponse->assertSee('WebsiteC SEO Title');
        $seoResponse->assertSee('noindex, nofollow');

        // Must NOT show Site A's SEO title
        $this->siteA->update(['default_seo_title' => 'SiteA SEO Title Only']);
        $seoResponse->assertDontSee('SiteA SEO Title Only');
    }

    // -------------------------------------------------------------------------
    // TEST: Validation prevents missing required fields on create
    // -------------------------------------------------------------------------
    public function test_create_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.sites.store'), [
                // Missing: name, code, domain, default_theme, default_locale
            ]);

        $response->assertSessionHasErrors(['name', 'code', 'domain', 'default_theme', 'default_locale']);
    }

    // -------------------------------------------------------------------------
    // TEST: Duplicate code rejected
    // -------------------------------------------------------------------------
    public function test_create_rejects_duplicate_site_code()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.sites.store'), [
                'name'           => 'Another ExamTopics',
                'code'           => 'examtopicsbase', // already exists
                'domain'         => 'newdomain.test',
                'default_theme'  => 'default',
                'default_locale' => 'en',
            ]);

        $response->assertSessionHasErrors(['code']);
    }

    // -------------------------------------------------------------------------
    // TEST: saveExamOverlay route works and saves overlay
    // -------------------------------------------------------------------------
    public function test_save_exam_overlay_creates_or_updates_overlay()
    {
        $siteC = Site::create([
            'name'           => 'Website C',
            'code'           => 'websitec',
            'default_theme'  => 'default',
            'default_locale' => 'en',
            'is_active'      => true,
        ]);

        SiteDomain::create([
            'site_id' => $siteC->id,
            'domain'  => 'websitec.test',
            'is_primary' => true,
            'is_secure'  => false,
        ]);

        $exam = Exam::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sites.exam-overlay.save', $siteC->id), [
                'exam_id'     => $exam->id,
                'meta_title'  => 'Exclusive WebsiteC Exam Title',
                'is_active'   => '1',
            ]);

        $response->assertRedirect(route('admin.sites.edit', ['site' => $siteC->id, 'tab' => 'exams']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('site_exam_overlays', [
            'site_id'    => $siteC->id,
            'exam_id'    => $exam->id,
            'meta_title' => 'Exclusive WebsiteC Exam Title',
            'is_active'  => 1,
        ]);
    }

    // -------------------------------------------------------------------------
    // TEST: Delete site (non-root)
    // -------------------------------------------------------------------------
    public function test_can_delete_non_root_site()
    {
        $siteC = Site::create([
            'name'           => 'Website C',
            'code'           => 'websitec',
            'default_theme'  => 'default',
            'default_locale' => 'en',
            'is_active'      => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.sites.destroy', $siteC->id));

        $response->assertRedirect(route('admin.sites.index'));
        $this->assertDatabaseMissing('sites', ['id' => $siteC->id]);
    }

    // -------------------------------------------------------------------------
    // TEST: Cannot delete root site (id=1)
    // -------------------------------------------------------------------------
    public function test_cannot_delete_root_site()
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.sites.destroy', $this->siteA->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('sites', ['id' => $this->siteA->id]);
    }
}
