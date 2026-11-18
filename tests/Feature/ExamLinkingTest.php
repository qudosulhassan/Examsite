<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Question;
use App\Models\Vendor;
use App\Support\ExamBankFacts;
use App\Support\InternalLinker;
use App\Support\OfficialSources;
use App\Support\RelatedExams;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ExamLinkingTest extends TestCase
{
    use RefreshDatabase;

    private Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->vendor = Vendor::create(['name' => 'Microsoft', 'slug' => 'microsoft', 'is_active' => true]);
    }

    private function exam(string $code, string $name): Exam
    {
        return Exam::factory()->create(['vendor_id' => $this->vendor->id, 'exam_code' => $code, 'exam_name' => $name, 'slug' => strtolower($code), 'is_active' => true]);
    }

    public function test_related_exams_follow_the_family_in_a_fixed_order(): void
    {
        $az104 = $this->exam('AZ-104', 'Microsoft Azure Administrator');
        $this->exam('AZ-900', 'Microsoft Azure Fundamentals');
        $this->exam('AZ-305', 'Designing Microsoft Azure Infrastructure Solutions');
        $this->exam('SC-900', 'Security, Compliance, and Identity Fundamentals');
        $this->exam('PL-300', 'Power BI Data Analyst');

        $first = RelatedExams::exams($az104, 4)->pluck('exam_code')->all();
        $second = RelatedExams::exams($az104, 4)->pluck('exam_code')->all();

        $this->assertSame($first, $second, 'same order on every request');
        $this->assertEqualsCanonicalizing(['AZ-900', 'AZ-305'], array_slice($first, 0, 2), 'AZ exams (the usual path) come first');
        $this->assertNotContains('AZ-104', $first);
    }

    public function test_internal_links_are_added_once_to_plain_text_only(): void
    {
        $this->exam('AZ-900', 'Microsoft Azure Fundamentals');
        $this->exam('AZ-305', 'Designing Microsoft Azure Infrastructure Solutions');
        $own = $this->exam('AZ-104', 'Microsoft Azure Administrator');

        $html = '<p>Take AZ-900 first, then AZ-305. See AZ-900 again and AZ-104 itself.</p><h2>AZ-900 topics</h2><p><a href="/x">AZ-900</a></p>';
        $out = InternalLinker::linkHtml($html, $own);

        $this->assertSame(1, substr_count($out, '/exams/microsoft/az-900"'), 'AZ-900 linked once, in the paragraph');
        $this->assertStringContainsString('/exams/microsoft/az-305"', $out);
        $this->assertStringNotContainsString('/exams/microsoft/az-104', $out, 'own code is not linked');
        $this->assertStringContainsString('<h2>AZ-900 topics</h2>', $out, 'headings are untouched');
        $this->assertStringContainsString('<a href="/x">AZ-900</a>', $out, 'existing links are untouched');
    }

    public function test_faq_text_is_escaped_and_linked(): void
    {
        $this->exam('AZ-900', 'Microsoft Azure Fundamentals');
        $own = $this->exam('AZ-104', 'Microsoft Azure Administrator');

        $out = InternalLinker::linkText('<b>Start with AZ-900</b> & practise', $own);
        $this->assertStringContainsString('&lt;b&gt;Start with ', $out);
        $this->assertStringContainsString('&amp; practise', $out);
        $this->assertStringContainsString('<a href=', $out);
        $this->assertStringNotContainsString('<b>', $out);
    }

    public function test_official_sources_only_list_known_good_pages(): void
    {
        $az900 = $this->exam('AZ-900', 'Microsoft Azure Fundamentals');
        $az900->load('vendor');
        $urls = array_column(OfficialSources::for($az900), 'url');
        $this->assertContains('https://learn.microsoft.com/en-us/credentials/certifications/resources/study-guides/az-900', $urls);
        $this->assertContains('https://learn.microsoft.com/en-us/credentials/browse/', $urls);

        $other = Vendor::create(['name' => 'Mystery Vendor', 'slug' => 'mystery', 'is_active' => true]);
        $e = Exam::factory()->create(['vendor_id' => $other->id, 'exam_code' => 'X-100', 'exam_name' => 'X', 'slug' => 'x-100', 'is_active' => true]);
        $e->load('vendor');
        $this->assertSame([], OfficialSources::for($e), 'no link is guessed for a vendor we have not checked');

        foreach (OfficialSources::VENDOR_PAGES as $url) {
            $this->assertStringStartsWith('https://', $url);
        }
    }

    public function test_bank_facts_come_from_the_questions(): void
    {
        $exam = $this->exam('AZ-104', 'Microsoft Azure Administrator');
        foreach ([['Networking', 'single_choice', 'Because.'], ['Networking', 'multiple_choice', ''], ['Storage', 'single_choice', 'Yes.']] as $i => [$topic, $type, $why]) {
            Question::create(['exam_id' => $exam->id, 'question_type' => $type, 'question_text' => "Question {$i}?", 'topic' => $topic, 'explanation' => $why, 'is_active' => true, 'status' => 'published']);
        }

        $bank = ExamBankFacts::for($exam);
        $this->assertSame(3, $bank['total']);
        $this->assertSame(2, $bank['explained']);
        $this->assertSame(['Networking' => 2, 'Storage' => 1], $bank['topics']);
        $this->assertSame(2, $bank['types']['single-answer multiple choice']);

        $empty = $this->exam('AZ-305', 'Designing');
        $this->assertNull(ExamBankFacts::for($empty));
    }

    public function test_exam_page_shows_the_new_blocks_and_not_the_old_random_one(): void
    {
        $exam = $this->exam('AZ-104', 'Microsoft Azure Administrator');
        $this->exam('AZ-900', 'Microsoft Azure Fundamentals');
        Question::create(['exam_id' => $exam->id, 'question_type' => 'single_choice', 'question_text' => 'Q?', 'topic' => 'Networking', 'is_active' => true, 'status' => 'published']);

        $html = $this->get($exam->url)->assertOk()->getContent();
        $this->assertStringContainsString('AZ-104 practice question bank at a glance', $html);
        $this->assertStringContainsString('Confirm the official AZ-104 requirements', $html);
        $this->assertMatchesRegularExpression('/(Related|Popular) Microsoft Exams/', $html);
        $this->assertStringNotContainsString('Frequently Bought Together', $html);
        $this->assertStringContainsString('rel="noopener"', $html);

        $again = $this->get($exam->url)->getContent();
        preg_match('#(?:Related|Popular) Microsoft Exams.*?</section>#s', $html, $a);
        preg_match('#(?:Related|Popular) Microsoft Exams.*?</section>#s', $again, $b);
        $this->assertSame($a[0] ?? null, $b[0] ?? '', 'related block is identical between requests');
    }
}
