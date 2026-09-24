<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\QuestionImportBatch;
use App\Models\QuestionImportItem;
use App\Models\User;
use App\Models\Vendor;
use App\Services\QuestionImport\Pdf\PdfQuestionDetector;
use App\Services\QuestionImport\Pdf\PdfSourceConsistencyValidator;
use App\Services\QuestionImport\PdfImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3SourceAccuracyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $vendor = Vendor::create(['name' => 'Microsoft', 'slug' => 'microsoft']);
        $this->exam = Exam::create([
            'vendor_id' => $vendor->id,
            'exam_code' => 'AZ-303',
            'exam_name' => 'Microsoft Azure Architect Technologies',
            'slug' => 'az-303',
            'status' => 'published',
        ]);
    }

    /**
     * Permanent Regression Test for Question #5: ABE must produce ['A', 'B', 'E'] and NEVER ['A', 'E'].
     */
    public function test_regression_q5_lossless_abe_answer_extraction(): void
    {
        $rawQ5 = "Question #5\nTopic 1\nYou have an Azure subscription that contains 100 virtual machines.\nYou have a set of Pester tests in PowerShell that validate the virtual machine environment.\nYou need to run the tests whenever there is an operating system update on the virtual machines. The solution must minimize implementation time and recurring costs.\nWhich three resources should you use to implement the tests? Each correct answer presents part of the solution.\nNOTE: Each correct selection is worth one point.\n\nɣ\nA. Azure Automation runbook\nɣ\nB. an alert rule\nɣ\nC. an Azure Monitor query\nɣ\nD. a virtual machine that has network access to the 100 virtual machines\nɣ\nE. an alert action group\n\nCorrect Answer:\nABE\n\nAE: You can call Azure Automation runbooks by using action groups or by using classic alerts to automate tasks based on alerts.\nB: Alerts are one of the key features of Azure Monitor. They allow us to alert on actions within an Azure subscription.\n\nReference:\nhttps://docs.microsoft.com/en-us/azure/automation/automation-create-alert-triggered-runbook";

        $candidate = PdfQuestionDetector::parseQuestionBlock(
            $rawQ5,
            'Topic 1',
            5,
            'az-303.docx.pdf',
            8,
            9
        );

        $this->assertEquals(['A', 'B', 'E'], $candidate['correct_answers']);
        $this->assertNotEquals(['A', 'E'], $candidate['correct_answers'], 'Critical regression: Middle answer B was dropped!');
        $this->assertNotEquals(['ABE'], $candidate['correct_answers'], 'Answer letters were not split into distinct array entries!');
        $this->assertCount(5, $candidate['options']);
        $this->assertEquals('multiple_choice', $candidate['question_type']);
        $this->assertEquals('READY', $candidate['readiness_status']);
    }

    /**
     * Test all required answer format variations.
     */
    public function test_lossless_answer_format_variations(): void
    {
        // Single
        $this->assertEquals(['A'], PdfQuestionDetector::normalizeAnswerLetters('A'));
        $this->assertEquals(['B'], PdfQuestionDetector::normalizeAnswerLetters('B'));

        // Consecutive letters without commas
        $this->assertEquals(['A', 'B'], PdfQuestionDetector::normalizeAnswerLetters('AB'));
        $this->assertEquals(['A', 'B', 'C'], PdfQuestionDetector::normalizeAnswerLetters('ABC'));
        $this->assertEquals(['A', 'B', 'E'], PdfQuestionDetector::normalizeAnswerLetters('ABE'));
        $this->assertEquals(['C', 'D'], PdfQuestionDetector::normalizeAnswerLetters('CD'));

        // Comma separated
        $this->assertEquals(['A', 'C'], PdfQuestionDetector::normalizeAnswerLetters('A, C'));
        $this->assertEquals(['A', 'B', 'E'], PdfQuestionDetector::normalizeAnswerLetters('A, B, E'));

        // Word 'and' / '&'
        $this->assertEquals(['B', 'D'], PdfQuestionDetector::normalizeAnswerLetters('B and D'));
        $this->assertEquals(['A', 'E'], PdfQuestionDetector::normalizeAnswerLetters('A & E'));

        // Whitespace and lowercase
        $this->assertEquals(['A', 'B', 'E'], PdfQuestionDetector::normalizeAnswerLetters(" a  b  e "));
        $this->assertEquals(['A', 'B', 'C', 'D'], PdfQuestionDetector::normalizeAnswerLetters("A B C D"));
    }

    /**
     * Test source consistency validator flags answer discrepancy and marks FAILED.
     */
    public function test_source_consistency_flags_answer_mismatch(): void
    {
        $mockCandidate = [
            'question_type' => 'multiple_choice',
            'question_text' => 'Which three settings should you configure?',
            'options' => [
                ['key' => 'A', 'text' => 'Option A'],
                ['key' => 'B', 'text' => 'Option B'],
                ['key' => 'C', 'text' => 'Option C'],
                ['key' => 'D', 'text' => 'Option D'],
                ['key' => 'E', 'text' => 'Option E'],
            ],
            'correct_answers' => ['A', 'E'], // Artificially drop B to simulate discrepancy
            'answer_area' => [],
            'question_exhibits' => [],
            'answer_exhibits' => [],
            'source_metadata' => ['page_start' => 1, 'page_end' => 2],
            'debug_info' => [
                'raw_text_block' => "Question #5\nWhich three resources?\nA. Option A\nB. Option B\nC. Option C\nD. Option D\nE. Option E\nCorrect Answer:\nABE\nExplanation...",
            ],
        ];

        $result = PdfSourceConsistencyValidator::validateCandidate($mockCandidate);

        $this->assertEquals('FAILED', $result['readiness_status']);
        $this->assertEquals('failed', $result['field_statuses']['answer_status']);
        $this->assertFalse($result['is_lossless_verified']);

        $codes = array_column($result['discrepancies'], 'code');
        $this->assertContains('CRITICAL_ANSWER_MISMATCH', $codes);
    }

    /**
     * Test source consistency validator flags missing options in multiple choice.
     */
    public function test_source_consistency_flags_missing_option(): void
    {
        $mockCandidate = [
            'question_type' => 'multiple_choice',
            'question_text' => 'Which resources should you use?',
            'options' => [
                ['key' => 'A', 'text' => 'Option A'],
                ['key' => 'B', 'text' => 'Option B'],
                ['key' => 'C', 'text' => 'Option C'],
                ['key' => 'D', 'text' => 'Option D'],
                // Missing option E
            ],
            'correct_answers' => ['A', 'B'],
            'answer_area' => [],
            'question_exhibits' => [],
            'answer_exhibits' => [],
            'source_metadata' => ['page_start' => 1, 'page_end' => 2],
            'debug_info' => [
                'raw_text_block' => "Question #5\nWhich resources?\nA. Option A\nB. Option B\nC. Option C\nD. Option D\nE. Option E\nCorrect Answer: AB",
            ],
        ];

        $result = PdfSourceConsistencyValidator::validateCandidate($mockCandidate);

        $this->assertEquals('FAILED', $result['readiness_status']);
        $this->assertEquals('failed', $result['field_statuses']['options_status']);

        $codes = array_column($result['discrepancies'], 'code');
        $this->assertContains('MISSING_SOURCE_OPTIONS', $codes);
    }

    /**
     * Test invalid answer letter not present in options fails validation.
     */
    public function test_invalid_answer_letter_rejected(): void
    {
        $mockCandidate = [
            'question_type' => 'single_choice',
            'question_text' => 'What is Azure?',
            'options' => [
                ['key' => 'A', 'text' => 'Cloud'],
                ['key' => 'B', 'text' => 'On-prem'],
            ],
            'correct_answers' => ['F'], // Invalid letter F
            'answer_area' => [],
            'question_exhibits' => [],
            'answer_exhibits' => [],
            'source_metadata' => ['page_start' => 1, 'page_end' => 1],
            'debug_info' => [
                'raw_text_block' => "Question #1\nWhat is Azure?\nA. Cloud\nB. On-prem\nCorrect Answer: F",
            ],
        ];

        $result = PdfSourceConsistencyValidator::validateCandidate($mockCandidate);

        $this->assertEquals('FAILED', $result['readiness_status']);
        $codes = array_column($result['discrepancies'], 'code');
        $this->assertContains('INVALID_ANSWER_LETTER', $codes);
    }

    /**
     * Test learner preview safety: no answer leakage.
     */
    public function test_learner_preview_answer_leakage_safety(): void
    {
        $mockCandidate = [
            'question_type' => 'hotspot',
            'question_text' => 'Configure storage redundancy.',
            'options' => [],
            'correct_answers' => [],
            'answer_area' => [
                'type' => 'dropdown_boxes',
                'boxes' => [['box_number' => 1, 'label' => 'Box 1', 'correct' => 'ZRS']],
            ],
            'question_exhibits' => [['url' => '/storage/prompt_diagram.png', 'caption' => 'Exhibit 1']],
            'answer_exhibits' => [['url' => '/storage/prompt_diagram.png', 'caption' => 'Solution']], // Leak!
            'source_metadata' => ['page_start' => 2, 'page_end' => 3],
            'debug_info' => ['raw_text_block' => 'HOTSPOT'],
        ];

        $result = PdfSourceConsistencyValidator::validateCandidate($mockCandidate);

        $this->assertEquals('FAILED', $result['readiness_status']);
        $codes = array_column($result['discrepancies'], 'code');
        $this->assertContains('POSSIBLE_ANSWER_LEAK_IN_EXHIBIT', $codes);
    }
}
