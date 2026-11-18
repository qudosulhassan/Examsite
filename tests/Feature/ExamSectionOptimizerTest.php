<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Vendor;
use App\Support\ExamSectionOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamSectionOptimizerTest extends TestCase
{
    use RefreshDatabase;

    private const ARTICLE = '<h2>Microsoft 62-193 Exam Details</h2><table><tbody>'
        . '<tr><td><p><strong>Exam Duration</strong></p></td><td><p>50 Minutes</p></td></tr>'
        . '<tr><td>Passing Score</td><td>700 out of 1000</td></tr>'
        . '<tr><td>Official Exam Fee</td><td>$127 USD</td></tr>'
        . '<tr><td>Exam Delivery</td><td>Online proctored or Certiport authorized testing center</td></tr>'
        . '</tbody></table><h2>What the Microsoft 62-193 Exam Covers?</h2><p>Domains.</p><h2>Why Choose ExamTopicsBase for Microsoft 62-193 Prep?</h2>';

    private function exam(array $extra = []): Exam
    {
        $vendor = Vendor::create(['name' => 'Microsoft', 'slug' => 'microsoft', 'is_active' => true]);

        return Exam::factory()->create(array_merge([
            'vendor_id' => $vendor->id, 'exam_code' => '62-193', 'exam_name' => 'Technology Literacy for Educators', 'slug' => '62-193', 'is_active' => true,
            'article_content' => self::ARTICLE, 'faqs' => [['question' => 'Can I take the 62-193 exam online?', 'answer' => 'Yes.']], 'topics' => ['Pedagogy (25% - 30%)', 'ICT (15% - 20%)'],
        ], $extra));
    }

    public function test_facts_are_read_from_the_pages_own_table(): void
    {
        $facts = ExamSectionOptimizer::facts($this->exam());
        $this->assertSame('50 Minutes', $facts['duration']);
        $this->assertSame('700 out of 1000', $facts['passing']);
        $this->assertSame('$127 USD', $facts['fee']);
        $this->assertStringContainsString('Certiport', $facts['delivery']);
    }

    public function test_article_gets_a_quick_answer_and_keyword_headings_without_new_facts(): void
    {
        $exam = $this->exam();
        $out = ExamSectionOptimizer::optimizeArticle(self::ARTICLE, $exam);

        $this->assertStringContainsString('Microsoft 62-193 Exam Details: Passing Score, Format and Requirements', $out);
        $this->assertStringContainsString('Microsoft 62-193 Exam Objectives: What the Exam Covers', $out);
        $this->assertStringContainsString('Quick answer: ', $out);
        $this->assertStringContainsString('a passing score of 700 out of 1000', $out);
        $this->assertStringContainsString('a duration of 50 Minutes', $out);
        $this->assertStringContainsString('Why Choose ExamTopicsBase', $out, 'other headings are left alone');
        // the quick answer comes straight after the Exam Details heading
        $this->assertLessThan(strpos($out, '<table'), strpos($out, 'Quick answer'));
        // nothing is invented: the figures in the answer all exist in the original table text
        foreach (['700 out of 1000', '50 Minutes', '$127 USD'] as $figure) {
            $this->assertStringContainsString($figure, self::ARTICLE);
        }
    }

    public function test_no_quick_answer_without_enough_table_facts(): void
    {
        $exam = $this->exam(['article_content' => '<h2>Microsoft 62-193 Exam Details</h2><p>Plain text only.</p>']);
        $out = ExamSectionOptimizer::optimizeArticle($exam->article_content, $exam);
        $this->assertStringNotContainsString('Quick answer', $out);
        $this->assertStringContainsString('Exam Details: Format and Requirements', $out);
    }

    public function test_faqs_cover_the_keyword_groups_and_keep_existing_questions(): void
    {
        $exam = $this->exam();
        $faqs = $exam->resolved_faqs;
        $questions = array_column($faqs, 'question');

        $this->assertContains('Can I take the 62-193 exam online?', $questions, 'existing question kept');
        foreach (['passing score', 'cost', 'exam objectives', 'free', 'study'] as $needle) {
            $this->assertNotEmpty(array_filter($questions, fn ($q) => stripos($q, $needle) !== false), "a question about {$needle}");
        }
        $this->assertSame(count($questions), count(array_unique($questions)), 'no duplicate questions');
        $this->assertLessThanOrEqual(12, count($faqs));
        $cost = collect($faqs)->first(fn ($f) => stripos($f['question'], 'cost') !== false);
        $this->assertStringContainsString('$127 USD', $cost['answer']);
        $this->assertStringContainsString('check the official page', $cost['answer']);
    }

    public function test_exam_page_shows_the_reworked_sections(): void
    {
        $exam = $this->exam();
        $html = $this->get($exam->url)->assertOk()->getContent();

        $this->assertStringContainsString('62-193 Exam Objectives and Topics Covered', $html);
        $this->assertStringContainsString('These are the 62-193 exam objectives and topics', $html);
        $this->assertStringContainsString('62-193 FAQs: Passing Score, Cost, Exam Objectives and Practice Tests', $html);
        $this->assertStringContainsString('Quick answer: ', $html);
        $this->assertStringNotContainsString('Blueprinted Exam Topics Covered', $html);
        $this->assertStringNotContainsString('The study guide addresses all core domains', $html);
    }
}
