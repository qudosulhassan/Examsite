<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Exam;
use App\Models\Site;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Renders the ProExamTopics-specific templates (resources/views-sites/proexamtopics) against
 * the shared controllers, including the "vendor with no bundle" case that production has.
 */
class SiteTwoPublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        foreach ([1 => 'examtopicsbase', 2 => 'proexamtopics'] as $id => $code) {
            Site::query()->firstOrCreate(['id' => $id], ['name' => $code, 'code' => $code, 'default_theme' => 'default', 'default_locale' => 'en', 'is_active' => true]);
        }

        config([
            'site.id' => 2, 'site.mode' => 'standalone', 'site.catalog_mode' => 'all',
            'site.exam_url_style' => 'root', 'app.name' => 'ProExamTopics', 'app.url' => 'http://proexamtopics.test',
        ]);
        // Same effect as SITE_VIEW_OVERRIDE=proexamtopics at boot.
        app('view')->getFinder()->prependLocation(resource_path('views-sites/proexamtopics'));
    }

    public function test_vendor_page_without_a_bundle_renders_the_proexamtopics_design(): void
    {
        $vendor = Vendor::factory()->create(['slug' => 'acme', 'name' => 'Acme', 'is_active' => true]);
        Exam::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true, 'exam_code' => 'AC-100', 'slug' => 'ac-100']);

        $this->assertSame(0, $vendor->packages()->count());

        $this->get('http://proexamtopics.test/vendors/acme')
            ->assertOk()
            ->assertSee('cpm-hero-system', false)
            ->assertSee('ProExamTopics')
            ->assertDontSee('Exam Topics Base')
            ->assertDontSee('examtopicsbase');

        // Viewing the page must not create bundles in the shared database.
        $this->assertSame(0, $vendor->packages()->count());
    }

    public function test_vendor_index_search_and_test_engine_pages_use_the_proexamtopics_design(): void
    {
        $vendor = Vendor::factory()->create(['slug' => 'acme', 'name' => 'Acme', 'is_active' => true]);
        Exam::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true, 'exam_code' => 'AC-100', 'slug' => 'ac-100']);

        foreach (['/vendors', '/search?q=ac-100', '/test-engine'] as $path) {
            $this->get('http://proexamtopics.test' . $path)
                ->assertOk()
                ->assertSee('ProExamTopics')
                ->assertDontSee('Exam Topics Base');
        }
    }

    public function test_blog_post_page_renders_for_the_current_site(): void
    {
        $author = User::factory()->create();
        BlogPost::create([
            'user_id' => $author->id, 'title' => 'Site two tips', 'slug' => 'site-two-tips', 'content' => '<p>Hello</p>',
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);

        $this->get('http://proexamtopics.test/blog/site-two-tips')
            ->assertOk()
            ->assertSee('Site two tips');
    }
}
