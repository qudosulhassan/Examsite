<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LlmsTxtTest extends TestCase
{
    use RefreshDatabase;

    public function test_llms_txt_describes_the_site_and_lists_its_pages(): void
    {
        Cache::flush();
        $vendor = Vendor::create(['name' => 'Microsoft', 'slug' => 'microsoft', 'is_active' => true]);
        Exam::factory()->create(['vendor_id' => $vendor->id, 'exam_code' => 'AZ-900', 'exam_name' => 'Microsoft Azure Fundamentals', 'slug' => 'az-900', 'is_active' => true]);

        $res = $this->get('/llms.txt')->assertOk();
        $this->assertStringStartsWith('text/plain', $res->headers->get('Content-Type'));
        $body = $res->getContent();
        $this->assertStringStartsWith('# ', $body);
        $this->assertStringContainsString('not affiliated with', $body);
        $this->assertStringContainsString('AZ-900 Microsoft Azure Fundamentals', $body);
        $this->assertStringContainsString('/vendors/microsoft', $body);
        $this->assertStringNotContainsStringIgnoringCase('dumps', $body);
    }
}
