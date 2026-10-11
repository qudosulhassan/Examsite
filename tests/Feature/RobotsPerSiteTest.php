<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RobotsPerSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_secondary_site_robots_names_its_own_sitemap_not_the_core_sites(): void
    {
        config(['site.id' => 2, 'site.mode' => 'standalone']);

        $body = $this->get('http://proexamtopics.test/robots.txt')->assertOk()->getContent();

        $this->assertStringContainsString('Sitemap: http://proexamtopics.test/sitemap.xml', $body);
        $this->assertStringNotContainsString('examtopicsbase.com', $body);
        $this->assertStringContainsString('Disallow: /admin', $body);
    }

    public function test_secondary_site_uses_its_own_edited_robots_content(): void
    {
        config(['site.id' => 2, 'site.mode' => 'standalone']);
        foreach ([1 => 'examtopicsbase', 2 => 'proexamtopics'] as $id => $code) {
            \App\Models\Site::query()->firstOrCreate(['id' => $id], ['name' => $code, 'code' => $code, 'default_theme' => 'default', 'default_locale' => 'en', 'is_active' => true]);
        }
        Setting::set('seo_robots_txt_content', "User-agent: *\nDisallow: /\nSitemap: https://proexamtopics.com/sitemap.xml");

        $this->get('http://proexamtopics.test/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /', false);

        // ...and does not touch the core site's global setting.
        config(['site.id' => 1]);
        $this->assertNull(Setting::get('seo_robots_txt_content'));
    }
}
