<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TechnicalSeoMetaIndexingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@examsninja.com',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_view_meta_indexing_tab()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.seo.index', ['tab' => 'meta_indexing']));

        $response->assertStatus(200);
        $response->assertSee('Meta &amp; Indexing Directives', false);
        $response->assertSee('Primary Indexing Directives');
        $response->assertSee('Generated Output Tag');
        $response->assertSee('Open Graph &amp; Twitter Card Defaults', false);
    }

    public function test_admin_can_save_indexing_directives_and_they_apply_to_public_pages()
    {
        // 1. Submit noindex, nofollow with advanced crawler options
        $response = $this->actingAs($this->admin)->post(route('admin.seo.update'), [
            'active_tab' => 'meta_indexing',
            'seo_robots_index' => 'noindex',
            'seo_robots_follow' => 'nofollow',
            'seo_robots_noarchive' => '1',
            'seo_robots_nosnippet' => '1',
            'seo_robots_max_image_preview' => '0',
            'default_og_title' => 'Custom Staging Title',
            'default_og_description' => 'Custom Staging Description',
            'seo_twitter_card' => 'summary',
        ]);

        $response->assertRedirect(route('admin.seo.index', ['tab' => 'meta_indexing']));
        $response->assertSessionHas('success', 'Meta & Indexing directives saved and applied successfully.');

        // 2. Check Database Settings
        $this->assertEquals('noindex', Setting::get('seo_robots_index'));
        $this->assertEquals('nofollow', Setting::get('seo_robots_follow'));
        $this->assertEquals('1', Setting::get('seo_robots_noarchive'));
        $this->assertEquals('1', Setting::get('seo_robots_nosnippet'));
        $this->assertEquals('0', Setting::get('seo_robots_max_image_preview'));
        $this->assertEquals('Custom Staging Title', Setting::get('default_og_title'));
        $this->assertEquals('Custom Staging Description', Setting::get('default_og_description'));
        $this->assertEquals('summary', Setting::get('seo_twitter_card'));

        // 3. Check Public Homepage HTML head
        $publicResponse = $this->get('/');
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">', false);
        $publicResponse->assertSee('<meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet">', false);
        $publicResponse->assertSee('<meta property="twitter:card" content="summary">', false);
    }

    public function test_switching_back_to_index_follow_restores_snippet_and_image_preview()
    {
        // Switch to index, follow with max-image-preview
        $response = $this->actingAs($this->admin)->post(route('admin.seo.update'), [
            'active_tab' => 'meta_indexing',
            'seo_robots_index' => 'index',
            'seo_robots_follow' => 'follow',
            'seo_robots_noarchive' => '0',
            'seo_robots_nosnippet' => '0',
            'seo_robots_max_image_preview' => '1',
        ]);

        $response->assertRedirect(route('admin.seo.index', ['tab' => 'meta_indexing']));

        $publicResponse = $this->get('/');
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">', false);
        $publicResponse->assertSee('<meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">', false);
    }

    public function test_validation_fails_on_invalid_directives()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.seo.update'), [
            'active_tab' => 'meta_indexing',
            'seo_robots_index' => 'invalid_directive',
            'seo_robots_follow' => 'invalid_follow',
        ]);

        $response->assertSessionHasErrors(['seo_robots_index', 'seo_robots_follow']);
    }

    public function test_admin_can_save_verification_tokens_and_they_are_cleaned_and_rendered()
    {
        // User pastes full HTML meta tags with quotes or whitespace
        $rawGsc = '<meta name="google-site-verification" content="caTdpujjoxu4S825WcwZHR0frALZjuRYH4MITxURQyE" />';
        $rawBing = '<meta name="msvalidate.01" content="892348ABCDEF1234567890" />';
        $rawYandex = '   a1b2c3d4e5f6g7h8   ';
        $rawPinterest = '<meta name="p:domain_verify" content="7f8a9b12345" />';

        $response = $this->actingAs($this->admin)->post(route('admin.seo.update'), [
            'active_tab' => 'search_engines',
            'seo_gsc_verification' => $rawGsc,
            'seo_bing_verification' => $rawBing,
            'seo_yandex_verification' => $rawYandex,
            'seo_pinterest_verification' => $rawPinterest,
        ]);

        $response->assertRedirect(route('admin.seo.index', ['tab' => 'search_engines']));
        $response->assertSessionHas('success', 'Search engine verification tokens saved and cleaned successfully.');

        // Verify stored settings contain ONLY the tokens
        $this->assertEquals('caTdpujjoxu4S825WcwZHR0frALZjuRYH4MITxURQyE', Setting::get('seo_gsc_verification'));
        $this->assertEquals('892348ABCDEF1234567890', Setting::get('seo_bing_verification'));
        $this->assertEquals('a1b2c3d4e5f6g7h8', Setting::get('seo_yandex_verification'));
        $this->assertEquals('7f8a9b12345', Setting::get('seo_pinterest_verification'));

        // Verify public HTML renders clean meta tags in <head>
        $publicResponse = $this->get('/');
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('<meta name="google-site-verification" content="caTdpujjoxu4S825WcwZHR0frALZjuRYH4MITxURQyE">', false);
        $publicResponse->assertSee('<meta name="msvalidate.01" content="892348ABCDEF1234567890">', false);
        $publicResponse->assertSee('<meta name="yandex-verification" content="a1b2c3d4e5f6g7h8">', false);
        $publicResponse->assertSee('<meta name="p:domain_verify" content="7f8a9b12345">', false);
    }

    public function test_public_pages_return_index_follow_in_x_robots_tag_header()
    {
        Setting::set('seo_robots_index', 'index');
        Setting::set('seo_robots_follow', 'follow');
        Setting::clearCache();

        // 1. Homepage
        $response = $this->get('/');
        $response->assertStatus(200);
        $this->assertEquals('index, follow', $response->headers->get('X-Robots-Tag'));
        $response->assertSee('<meta name="robots" content="index, follow', false);

        // 2. Vendors directory
        $vendorResponse = $this->get('/vendors');
        $vendorResponse->assertStatus(200);
        $this->assertEquals('index, follow', $vendorResponse->headers->get('X-Robots-Tag'));

        // 3. Blog directory
        $blogResponse = $this->get('/blog');
        $blogResponse->assertStatus(200);
        $this->assertEquals('index, follow', $blogResponse->headers->get('X-Robots-Tag'));

        // 4. Certifications directory
        $certResponse = $this->get('/certifications');
        $certResponse->assertStatus(200);
        $this->assertEquals('index, follow', $certResponse->headers->get('X-Robots-Tag'));
    }

    public function test_private_pages_have_noindex_nofollow_in_headers_and_meta()
    {
        // 1. Auth / Login page
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $this->assertEquals('noindex, nofollow', $loginResponse->headers->get('X-Robots-Tag'));
        $loginResponse->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        // 2. Cart page
        $cartResponse = $this->get('/cart');
        $cartResponse->assertStatus(200);
        $this->assertEquals('noindex, nofollow', $cartResponse->headers->get('X-Robots-Tag'));
        $cartResponse->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        // 3. Admin dashboard
        $adminResponse = $this->actingAs($this->admin)->get('/admin');
        $this->assertEquals('noindex, nofollow', $adminResponse->headers->get('X-Robots-Tag'));
        $adminResponse->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_search_page_returns_noindex_follow_header()
    {
        $searchResponse = $this->get('/search?q=test');
        $searchResponse->assertStatus(200);
        $this->assertEquals('noindex, follow', $searchResponse->headers->get('X-Robots-Tag'));
        $searchResponse->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_directives_sync_with_legacy_robots_setting()
    {
        $this->actingAs($this->admin)->post(route('admin.seo.update'), [
            'active_tab' => 'meta_indexing',
            'seo_robots_index' => 'noindex',
            'seo_robots_follow' => 'nofollow',
            'seo_robots_noarchive' => '0',
            'seo_robots_nosnippet' => '0',
            'seo_robots_max_image_preview' => '1',
        ]);

        $this->assertEquals('noindex, nofollow', Setting::get('robots_setting'));

        // Public pages now return noindex, nofollow
        $response = $this->get('/');
        $this->assertEquals('noindex, nofollow', $response->headers->get('X-Robots-Tag'));
    }
}
