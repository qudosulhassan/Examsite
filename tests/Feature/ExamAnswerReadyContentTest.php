<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamAnswerReadyContentTest extends TestCase
{
    use RefreshDatabase;

    private function exam(string $vendorName, string $vendorSlug, string $code, string $name): Exam
    {
        $vendor = Vendor::create(['name' => $vendorName, 'slug' => $vendorSlug, 'is_active' => true]);

        return Exam::factory()->create([
            'vendor_id' => $vendor->id, 'exam_code' => $code, 'exam_name' => $name, 'slug' => strtolower($code), 'is_active' => true, 'faqs' => null,
        ]);
    }

    private function faqSchema(string $html): ?array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        foreach ($m[1] as $json) {
            $data = json_decode($json, true);
            foreach (($data['@graph'] ?? [$data]) as $block) {
                if (($block['@type'] ?? null) === 'FAQPage') {
                    return $block;
                }
            }
        }

        return null;
    }

    public function test_microsoft_exam_shows_verified_facts_and_matching_faqs(): void
    {
        $exam = $this->exam('Microsoft', 'microsoft', 'AZ-900', 'Microsoft Azure Fundamentals');
        $html = $this->get($exam->url)->assertOk()->getContent();

        $this->assertStringContainsString('AZ-900 exam facts from Microsoft Learn', $html);
        $this->assertStringContainsString('700 on a scale of 1 to 1,000', $html);
        $this->assertStringContainsString('Describe cloud concepts', $html);
        $this->assertStringContainsString('learn.microsoft.com', $html);

        $schema = $this->faqSchema($html);
        $this->assertNotNull($schema, 'FAQPage markup present');
        $names = array_column($schema['mainEntity'], 'name');
        $this->assertContains('What is the passing score for AZ-900?', $names);
        foreach ($names as $q) {
            $this->assertStringContainsString(e($q), $html, 'question is visible on the page');
        }
        $this->assertDoesNotMatchRegularExpression('/dumps|real exam|99\.\d/i', json_encode($schema));
    }

    public function test_other_vendor_exam_gets_faqs_but_no_microsoft_facts(): void
    {
        $exam = $this->exam('Cisco', 'cisco', '200-301', 'Cisco Certified Network Associate (CCNA)');
        $html = $this->get($exam->url)->assertOk()->getContent();

        $this->assertStringNotContainsString('exam facts from Microsoft Learn', $html);
        $schema = $this->faqSchema($html);
        $this->assertNotNull($schema);
        $this->assertContains('What is the 200-301 exam?', array_column($schema['mainEntity'], 'name'));
        $this->assertNotContains('What is the passing score for 200-301?', array_column($schema['mainEntity'], 'name'));
    }
}
