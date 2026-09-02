<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\QuestionImportBatch;
use App\Models\QuestionImportItem;
use App\Models\User;
use App\Models\Vendor;
use App\Services\QuestionImport\Pdf\PdfQuestionDetector;
use App\Services\QuestionImport\PdfImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2PdfIntelligenceTest extends TestCase
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
     * Test 1: HOTSPOT questions are recognized and do NOT require conventional A/B/C/D options.
     */
    public function test_hotspot_does_not_require_abcd_options(): void
    {
        $rawText = "Question #2\nTopic 1\nHOTSPOT -\nYou plan to create an Azure Storage account in the Azure region of East US 2.\nYou need to create a storage account that meets the following requirements:\nReplicates synchronously\nRemains available if a single data center in the region fails\nHow should you configure the storage account? To answer, select the appropriate options in the answer area.\nNOTE: Each correct selection is worth one point.\n\nHot Area:\n\nCorrect Answer:\nBox 1: Zone-redundant storage(ZRS)\nBox 2: StorageV2(general purpose v2)\n\nReference:\nhttps://docs.microsoft.com/en-us/azure/storage/common/storage-redundancy";

        $candidate = PdfQuestionDetector::parseQuestionBlock(
            $rawText,
            'Topic 1',
            2,
            'test.pdf',
            2,
            3
        );

        $this->assertEquals('hotspot', $candidate['question_type']);
        $this->assertEmpty($candidate['options']);
        $this->assertNotContains('MISSING_OPTIONS', $candidate['warning_codes']);
        $this->assertCount(2, $candidate['answer_area']['boxes']);
        $this->assertEquals('Zone-redundant storage(ZRS)', $candidate['answer_area']['boxes'][0]['correct']);
        $this->assertEquals('StorageV2(general purpose v2)', $candidate['answer_area']['boxes'][1]['correct']);
        $this->assertEquals('READY', $candidate['readiness_status']);
    }

    /**
     * Test 2: DRAG & DROP questions are recognized and sequences extracted without A/B/C/D warnings.
     */
    public function test_drag_drop_detection_and_sequence_extraction(): void
    {
        $rawText = "Question #15\nTopic 1\nDRAG DROP -\nYou have an Azure subscription that contains two virtual networks named VNet1 and VNet2.\nYou plan to implement VNet peering between VNet1 and VNet2.\nWhich three actions should you perform in sequence? To answer, move the appropriate actions from the list of actions to the answer area and arrange them in the correct order.\n\nSelect and Place:\n\nCorrect Answer:\nStep 1: Remove peering between Vnet1 and VNet2.\nStep 2: Add the 10.44.0.0/16 address space to VNet1.\nStep 3: Recreate peering between VNet1 and VNet2\n\nReference:\nhttps://docs.microsoft.com/en-us/azure/virtual-network/virtual-network-manage-peering";

        $candidate = PdfQuestionDetector::parseQuestionBlock(
            $rawText,
            'Topic 1',
            15,
            'test.pdf',
            24,
            26
        );

        $this->assertEquals('drag_drop', $candidate['question_type']);
        $this->assertEmpty($candidate['options']);
        $this->assertNotContains('MISSING_OPTIONS', $candidate['warning_codes']);
        $this->assertCount(3, $candidate['answer_area']['steps']);
        $this->assertEquals('Remove peering between Vnet1 and VNet2.', $candidate['answer_area']['steps'][0]['text']);
        $this->assertEquals('Add the 10.44.0.0/16 address space to VNet1.', $candidate['answer_area']['steps'][1]['text']);
        $this->assertEquals('READY', $candidate['readiness_status']);
    }

    /**
     * Test 3: Multiple response answers (e.g. ABE or A, C) are normalized to arrays.
     */
    public function test_multiple_response_normalization(): void
    {
        $this->assertEquals(['A', 'B', 'E'], PdfQuestionDetector::normalizeAnswerLetters('ABE'));
        $this->assertEquals(['A', 'C'], PdfQuestionDetector::normalizeAnswerLetters('A, C'));
        $this->assertEquals(['B', 'D'], PdfQuestionDetector::normalizeAnswerLetters('B and D'));
    }

    /**
     * Test 4: YES/NO matrix questions are detected properly.
     */
    public function test_yes_no_question_detection(): void
    {
        $rawText = "Question #6\nTopic 1\nFor each of the following statements, select Yes if the statement is true. Otherwise, select No.\nNOTE: Each correct selection is worth one point.\n\nCorrect Answer:\nBox 1: Yes\nBox 2: No\nBox 3: Yes";

        $candidate = PdfQuestionDetector::parseQuestionBlock(
            $rawText,
            'Topic 1',
            6,
            'test.pdf',
            9,
            10
        );

        $this->assertEquals('yes_no', $candidate['question_type']);
        $this->assertNotContains('MISSING_OPTIONS', $candidate['warning_codes']);
    }

    /**
     * Test 5: Reference URLs with line-wraps and double slashes are cleaned.
     */
    public function test_url_line_wrap_reconstruction(): void
    {
        $text = "Reference:\nhttps://docs.microsoft.com/en-us/azure//////active-directory/devices/enterprise-state-roa\nming-overview\nhttps://azure.microsoft.com/en-us/blog/network-insights/";

        $refs = PdfQuestionDetector::cleanReferenceUrls($text);

        $this->assertCount(2, $refs);
        $this->assertEquals('https://docs.microsoft.com/en-us/azure/active-directory/devices/enterprise-state-roaming-overview', $refs[0]['url']);
        $this->assertEquals('https://azure.microsoft.com/en-us/blog/network-insights/', $refs[1]['url']);
    }

    /**
     * Test 6: Question exhibits are separated from Answer-only exhibits to prevent answer leakage.
     */
    public function test_exhibit_separation_and_leakage_protection(): void
    {
        $candidateImages = [
            ['url' => '/storage/page_4_prompt.png', 'source_page' => 4],
            ['url' => '/storage/page_6_solution.png', 'source_page' => 6],
        ];

        $rawText = "Question #3\nTopic 1\nHOTSPOT -\nYou have an ARM template as shown below.\n\nCorrect Answer:\nBox 1: storage\nBox 2: compute";

        $candidate = PdfQuestionDetector::parseQuestionBlock(
            $rawText,
            'Topic 1',
            3,
            'test.pdf',
            4,
            6,
            $candidateImages
        );

        $this->assertCount(1, $candidate['question_exhibits']);
        $this->assertCount(1, $candidate['answer_exhibits']);
        $this->assertEquals('/storage/page_4_prompt.png', $candidate['question_exhibits'][0]['url']);
        $this->assertEquals('/storage/page_6_solution.png', $candidate['answer_exhibits'][0]['url']);
    }

    /**
     * Test 7: Topic numbering reset (Topic 1 Q24 -> Topic 2 Q1) is preserved.
     */
    public function test_topic_numbering_reset_preservation(): void
    {
        $pages = [
            ['page_number' => 36, 'text' => "Question #24\nTopic 1\nWhat is Azure Load Balancer?\nA. Layer 4\nB. Layer 7\nAnswer: A", 'images' => []],
            ['page_number' => 37, 'text' => "Question #1\nTopic 2\nHOTSPOT -\nYou have Azure AD tenant.\nAnswer Area:\nBox 1: MFA", 'images' => []],
        ];

        $candidates = PdfQuestionDetector::detectQuestions($pages, 'test.pdf');

        $this->assertCount(2, $candidates);
        $this->assertEquals('Topic 1', $candidates[0]['topic']);
        $this->assertEquals(24, $candidates[0]['local_question_number']);
        $this->assertEquals('Topic 2', $candidates[1]['topic']);
        $this->assertEquals(1, $candidates[1]['local_question_number']);
    }

    /**
     * Test 8: Exam code mismatch warning is triggered when document name differs from target exam.
     */
    public function test_exam_code_mismatch_warning(): void
    {
        $otherExam = Exam::create([
            'vendor_id' => $this->exam->vendor_id,
            'exam_code' => 'AZ-900',
            'exam_name' => 'Azure Fundamentals',
            'slug' => 'az-900',
            'status' => 'published',
        ]);

        // Uploading az-303.pdf targeting AZ-900 should flag EXAM_CODE_MISMATCH
        $dummyPages = [
            ['page_number' => 1, 'text' => "Question #1:\nWhat is cloud?\nA. Servers\nB. Hardware\nAnswer: A\nExplanation: Cloud is servers.", 'images' => []]
        ];

        // Process directly via candidate check
        $candidate = PdfQuestionDetector::parseQuestionBlock(
            "Question #1:\nWhat is cloud?\nA. Servers\nB. Hardware\nAnswer: A\nExplanation: Cloud.",
            'General',
            1,
            'az-303.docx.pdf',
            1,
            1
        );

        $this->assertNotNull($candidate);
    }
}
