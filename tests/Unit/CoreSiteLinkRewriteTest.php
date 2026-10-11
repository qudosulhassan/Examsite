<?php

namespace Tests\Unit;

use App\Services\SiteContext;
use Tests\TestCase;

class CoreSiteLinkRewriteTest extends TestCase
{
    private string $html = '<a href="https://examtopicsbase.com/exams/microsoft/az-104">AZ-104</a> and <a href="https://www.examtopicsbase.com/vendors/cisco">Cisco</a>';

    public function test_core_site_content_is_left_untouched(): void
    {
        config(['site.id' => 1]);

        $this->assertSame($this->html, SiteContext::rewriteCoreSiteLinks($this->html));
    }

    public function test_other_sites_get_their_own_exam_urls_and_domain(): void
    {
        config(['site.id' => 2, 'site.exam_url_style' => 'root', 'app.url' => 'https://proexamtopics.com']);

        $out = SiteContext::rewriteCoreSiteLinks($this->html);

        $this->assertStringContainsString('href="https://proexamtopics.com/microsoft/az-104-exam"', $out);
        $this->assertStringContainsString('href="https://proexamtopics.com/vendors/cisco"', $out);
        $this->assertStringNotContainsString('examtopicsbase', $out);
    }

    public function test_prefixed_style_keeps_the_exams_prefix(): void
    {
        config(['site.id' => 3, 'site.exam_url_style' => 'prefixed', 'app.url' => 'https://example.org']);

        $this->assertStringContainsString('https://example.org/exams/microsoft/az-104', SiteContext::rewriteCoreSiteLinks($this->html));
    }

    public function test_null_and_empty_content_pass_through(): void
    {
        config(['site.id' => 2]);

        $this->assertNull(SiteContext::rewriteCoreSiteLinks(null));
        $this->assertSame('', SiteContext::rewriteCoreSiteLinks(''));
    }
}
