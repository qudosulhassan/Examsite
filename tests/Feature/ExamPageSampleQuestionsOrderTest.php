<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ExamTopicsBase exam pages: the free "Actual Free ... Practice Questions" section sits right
 * after the hero, before the article that holds the "Exam Details" heading.
 */
class ExamPageSampleQuestionsOrderTest extends TestCase
{
    use RefreshDatabase;

    private function makeExam(bool $withSample): Exam
    {
        $vendor = Vendor::factory()->create(['slug' => 'microsoft', 'name' => 'Microsoft', 'is_active' => true]);
        $exam = Exam::factory()->create([
            'vendor_id' => $vendor->id,
            'slug' => 'az-900',
            'exam_code' => 'AZ-900',
            'is_active' => true,
            'article_content' => '<h2>Microsoft AZ-900 Exam Details</h2><p>Official specifications.</p>',
        ]);

        if ($withSample) {
            $q = Question::create([
                'exam_id' => $exam->id, 'question_type' => 'single_choice', 'question_text' => 'Which cloud model is SaaS?',
                'is_active' => true, 'status' => 'published',
            ]);
            foreach (['A' => 'Yes', 'B' => 'No', 'C' => 'Maybe', 'D' => 'Never'] as $key => $text) {
                QuestionOption::create(['question_id' => $q->id, 'option_key' => $key, 'option_text' => $text, 'sort_order' => 1]);
            }
            QuestionAnswer::create(['question_id' => $q->id, 'answer_value' => 'A', 'sort_order' => 1]);
        }

        return $exam;
    }

    public function test_free_questions_come_before_the_exam_details_when_the_exam_has_samples(): void
    {
        $exam = $this->makeExam(true);

        $html = $this->get($exam->url)->assertOk()->getContent();

        $questions = strpos($html, 'Actual Free Microsoft AZ-900 Practice Questions');
        $details = strpos($html, 'Microsoft AZ-900 Exam Details');
        $hero = strpos($html, 'Hero Section');

        $this->assertNotFalse($questions);
        $this->assertNotFalse($details);
        $this->assertLessThan($details, $questions, 'free questions must come before the exam details');
        $this->assertGreaterThan($hero, $questions, 'free questions must come after the hero');
        $this->assertSame(1, substr_count($html, 'Actual Free Microsoft AZ-900 Practice Questions'), 'section must appear once');
        $this->assertStringContainsString('Which cloud model is SaaS?', $html);
    }

    public function test_exams_without_samples_keep_the_old_layout_with_details_first(): void
    {
        $exam = $this->makeExam(false);

        $html = $this->get($exam->url)->assertOk()->getContent();

        $questions = strpos($html, 'Actual Free Microsoft AZ-900 Practice Questions');
        $details = strpos($html, 'Microsoft AZ-900 Exam Details');

        // no empty "Actual Free ... Practice Questions" heading when there is nothing to show
        $this->assertFalse($questions);
        $this->assertStringContainsString('Ready to master all', $html);
    }
}
