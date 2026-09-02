<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionImportBatch;
use App\Models\QuestionImportItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3aPreviewReviewEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Exam $exam;
    protected QuestionImportBatch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $vendor = \App\Models\Vendor::create(['name' => 'Microsoft', 'slug' => 'microsoft']);

        $this->exam = Exam::create([
            'vendor_id' => $vendor->id,
            'exam_code' => 'AZ-303',
            'exam_name' => 'Microsoft Azure Architect Technologies',
            'slug' => 'az-303',
            'status' => 'published',
            'is_active' => true,
        ]);

        $this->batch = QuestionImportBatch::create([
            'uuid' => 'IMP-PHASE3A-001',
            'filename' => 'az-303.docx.pdf',
            'total_detected' => 4,
            'valid_count' => 4,
            'warning_count' => 0,
            'error_count' => 0,
            'duplicate_count' => 0,
            'default_exam_id' => $this->exam->id,
            'status' => 'pending_review',
        ]);
    }

    /**
     * Helper to create standard candidates.
     */
    protected function createCandidates(): array
    {
        // 1. Single Choice Candidate #1
        $q1 = QuestionImportItem::create([
            'batch_id' => $this->batch->id,
            'source_index' => 1,
            'source_type' => 'pdf_import',
            'validation_status' => 'valid',
            'review_status' => 'pending',
            'normalized_data' => [
                'exam_id' => $this->exam->id,
                'topic' => 'Topic 1',
                'question_type' => 'single_choice',
                'question_text' => 'You have an Azure subscription. What storage redundancy should you use?',
                'instructions' => 'Select one answer.',
                'options' => [
                    ['key' => 'A', 'text' => 'Locally-redundant storage (LRS)'],
                    ['key' => 'B', 'text' => 'Zone-redundant storage (ZRS)'],
                    ['key' => 'C', 'text' => 'Geo-redundant storage (GRS)'],
                    ['key' => 'D', 'text' => 'Read-access geo-redundant storage (RA-GRS)'],
                ],
                'correct_answers' => ['B'],
                'explanation' => 'Zone-redundant storage (ZRS) replicates data synchronously across three Azure availability zones.',
                'references' => [
                    ['title' => 'Azure Storage Redundancy', 'url' => 'https://docs.microsoft.com/azure/storage/common/storage-redundancy']
                ],
                'question_exhibits' => [],
                'answer_exhibits' => [],
                'source_reference' => [
                    'page_start' => 1,
                    'page_end' => 1,
                    'confidence_score' => 95,
                    'confidence_level' => 'HIGH',
                ],
                'readiness_status' => 'READY',
                'field_statuses' => [
                    'question_text' => 'verified',
                    'options' => 'verified',
                    'correct_answers' => 'verified',
                ],
                'discrepancies' => [],
            ],
            'raw_data' => [
                'debug_info' => [
                    'raw_text_block' => "Question 1:\nYou have an Azure subscription...\nA. Locally-redundant...\nB. Zone-redundant...\nCorrect Answer: B\nReferences:\nhttps://docs.microsoft.com/azure/storage/common/storage-redundancy",
                ],
            ],
        ]);

        // 2. Hotspot Candidate #2
        $q2 = QuestionImportItem::create([
            'batch_id' => $this->batch->id,
            'source_index' => 2,
            'source_type' => 'pdf_import',
            'validation_status' => 'valid',
            'review_status' => 'pending',
            'normalized_data' => [
                'exam_id' => $this->exam->id,
                'topic' => 'Topic 1',
                'question_type' => 'hotspot',
                'question_text' => 'HOTSPOT - For each statement, select Yes if the statement is true. Otherwise, select No.',
                'instructions' => 'Hot Area: Select the correct choice.',
                'options' => [],
                'correct_answers' => [],
                'answer_area' => [
                    'boxes' => [
                        ['box_number' => 1, 'label' => 'Statement 1', 'correct' => 'Yes'],
                        ['box_number' => 2, 'label' => 'Statement 2', 'correct' => 'No'],
                    ]
                ],
                'explanation' => 'Statement 1 is true because App Service supports custom domains. Statement 2 is false.',
                'references' => [],
                'question_exhibits' => [
                    ['url' => '/storage/exhibits/q2_prompt.png', 'caption' => 'Hot Area Exhibit']
                ],
                'answer_exhibits' => [
                    ['url' => '/storage/exhibits/q2_answer.png', 'caption' => 'Solution Exhibit']
                ],
                'source_reference' => [
                    'page_start' => 2,
                    'page_end' => 3,
                    'confidence_score' => 90,
                    'confidence_level' => 'HIGH',
                ],
                'readiness_status' => 'READY',
                'field_statuses' => [
                    'question_text' => 'verified',
                    'answer_area' => 'verified',
                ],
                'discrepancies' => [],
            ],
            'raw_data' => [
                'debug_info' => [
                    'raw_text_block' => "Question 2:\nHOTSPOT - For each statement...\nAnswer Area:\nStatement 1: Yes\nStatement 2: No",
                ],
            ],
        ]);

        // 3. Multiple Choice Candidate #5
        $q5 = QuestionImportItem::create([
            'batch_id' => $this->batch->id,
            'source_index' => 5,
            'source_type' => 'pdf_import',
            'validation_status' => 'valid',
            'review_status' => 'pending',
            'normalized_data' => [
                'exam_id' => $this->exam->id,
                'topic' => 'Topic 1',
                'question_type' => 'multiple_choice',
                'question_text' => 'Which three Azure services should you include in the recommendation?',
                'instructions' => 'Each correct answer presents part of the solution.',
                'options' => [
                    ['key' => 'A', 'text' => 'Azure Application Gateway'],
                    ['key' => 'B', 'text' => 'Azure Front Door'],
                    ['key' => 'C', 'text' => 'Azure Traffic Manager'],
                    ['key' => 'D', 'text' => 'Azure Load Balancer'],
                    ['key' => 'E', 'text' => 'Azure Firewall'],
                ],
                'correct_answers' => ['A', 'B', 'E'],
                'explanation' => 'Application Gateway, Front Door, and Firewall provide the required perimeter security.',
                'references' => [],
                'question_exhibits' => [],
                'answer_exhibits' => [],
                'source_reference' => [
                    'page_start' => 7,
                    'page_end' => 8,
                    'confidence_score' => 95,
                    'confidence_level' => 'HIGH',
                ],
                'readiness_status' => 'READY',
                'field_statuses' => [
                    'question_text' => 'verified',
                    'options' => 'verified',
                    'correct_answers' => 'verified',
                ],
                'discrepancies' => [],
            ],
            'raw_data' => [
                'debug_info' => [
                    'raw_text_block' => "Question 5:\nWhich three Azure services...\nCorrect Answer: ABE",
                ],
            ],
        ]);

        // 4. Drag & Drop Candidate #15
        $q15 = QuestionImportItem::create([
            'batch_id' => $this->batch->id,
            'source_index' => 15,
            'source_type' => 'pdf_import',
            'validation_status' => 'valid',
            'review_status' => 'pending',
            'normalized_data' => [
                'exam_id' => $this->exam->id,
                'topic' => 'Topic 1',
                'question_type' => 'drag_drop',
                'question_text' => 'DRAG DROP - Which three actions should you perform in sequence?',
                'instructions' => 'Select and Place: Move the actions to the answer area and arrange in the correct order.',
                'options' => [],
                'correct_answers' => [],
                'answer_area' => [
                    'steps' => [
                        ['step_number' => 1, 'label' => 'Step 1', 'text' => 'Create a key vault.'],
                        ['step_number' => 2, 'label' => 'Step 2', 'text' => 'Generate an encryption key.'],
                        ['step_number' => 3, 'label' => 'Step 3', 'text' => 'Enable customer-managed keys.'],
                    ]
                ],
                'explanation' => 'First create the key vault, then create the key, then enable CMK.',
                'references' => [],
                'question_exhibits' => [],
                'answer_exhibits' => [],
                'source_reference' => [
                    'page_start' => 20,
                    'page_end' => 21,
                    'confidence_score' => 90,
                    'confidence_level' => 'HIGH',
                ],
                'readiness_status' => 'READY',
                'field_statuses' => [
                    'question_text' => 'verified',
                    'answer_area' => 'verified',
                ],
                'discrepancies' => [],
            ],
            'raw_data' => [
                'debug_info' => [
                    'raw_text_block' => "Question 15:\nDRAG DROP - Which three actions...",
                ],
            ],
        ]);

        return [$q1, $q2, $q5, $q15];
    }

    public function test_candidate_preview_shows_prompt_and_options_without_answer_leakage()
    {
        [$q1] = $this->createCandidates();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-item-preview', $q1->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.questions.import-candidate-preview');
        $response->assertSeeText('You have an Azure subscription. What storage redundancy should you use?');
        $response->assertSeeText('Locally-redundant storage (LRS)');
        $response->assertSeeText('Zone-redundant storage (ZRS)');
        
        // Strict leakage assertions
        $response->assertDontSeeText('replicates data synchronously across three Azure availability zones');
        $response->assertSeeText('Learner View Simulation');
    }

    public function test_candidate_review_shows_admin_metadata_and_source_evidence()
    {
        [$q1] = $this->createCandidates();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-item-review', $q1->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.questions.import-candidate-review');
        $response->assertSeeText('Admin Review: Candidate #1');
        $response->assertSeeText('Zone-redundant storage (ZRS)');
        $response->assertSeeText('replicates data synchronously across three Azure availability zones');
        $response->assertSee('https://docs.microsoft.com/azure/storage/common/storage-redundancy');
        $response->assertSeeText('Pages 1–1');
        $response->assertSeeText('95%');
    }

    public function test_candidate_edit_page_loads_with_editable_form()
    {
        [$q1] = $this->createCandidates();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-item-edit', $q1->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.questions.import-candidate-edit');
        $response->assertSeeText('Edit Candidate #1');
        $response->assertSeeText('Target Exam');
        $response->assertSeeText('Question Prompt Text');
        $response->assertSeeText('AZ-303');
    }

    public function test_hotspot_candidate_preview_review_and_edit()
    {
        [, $q2] = $this->createCandidates();

        // 1. Preview
        $resPreview = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-item-preview', $q2->id));
        $resPreview->assertStatus(200);
        $resPreview->assertSeeText('HOTSPOT - For each statement');
        $resPreview->assertDontSeeText('Statement 1 is true because App Service supports');

        // 2. Review
        $resReview = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-item-review', $q2->id));
        $resReview->assertStatus(200);
        $resReview->assertSeeText('Structured Hotspot Solutions');
        $resReview->assertSeeText('Statement 1');
        $resReview->assertSeeText('Yes');

        // 3. Edit
        $resEdit = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-item-edit', $q2->id));
        $resEdit->assertStatus(200);
        $resEdit->assertSeeText('Hotspot Dropdown Boxes');
    }

    public function test_drag_drop_candidate_preview_review_and_edit()
    {
        [, , , $q15] = $this->createCandidates();

        // 1. Preview
        $resPreview = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-item-preview', $q15->id));
        $resPreview->assertStatus(200);
        $resPreview->assertSeeText('DRAG DROP - Which three actions should you perform in sequence?');
        $resPreview->assertDontSeeText('First create the key vault, then create the key');

        // 2. Review
        $resReview = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-item-review', $q15->id));
        $resReview->assertStatus(200);
        $resReview->assertSeeText('Structured Drag & Drop Sequence', false);
        $resReview->assertSeeText('Create a key vault.');

        // 3. Edit
        $resEdit = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-item-edit', $q15->id));
        $resEdit->assertStatus(200);
        $resEdit->assertSeeText('Drag & Drop Sequence Steps', false);
    }

    public function test_multiple_choice_candidate_preview_review_and_edit()
    {
        [, , $q5] = $this->createCandidates();

        // 1. Preview
        $resPreview = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-item-preview', $q5->id));
        $resPreview->assertStatus(200);
        $resPreview->assertSeeText('Which three Azure services should you include');
        $resPreview->assertSeeText('Azure Application Gateway');
        $resPreview->assertSeeText('Azure Front Door');
        $resPreview->assertSeeText('Azure Firewall');
        $resPreview->assertDontSeeText('Application Gateway, Front Door, and Firewall provide');

        // 2. Review
        $resReview = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-item-review', $q5->id));
        $resReview->assertStatus(200);
        $resReview->assertSeeText('Azure Application Gateway');
        $resReview->assertSeeText('✓ Correct');

        // 3. Edit
        $resEdit = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-item-edit', $q5->id));
        $resEdit->assertStatus(200);
    }

    public function test_saving_edited_candidate_persists_and_updates_review()
    {
        [$q1] = $this->createCandidates();

        $updateData = $q1->normalized_data;
        $updateData['question_text'] = 'Updated Question Text: What storage redundancy is optimal?';
        $updateData['topic'] = 'Storage Architecture';

        $response = $this->actingAs($this->adminUser)
            ->putJson(route('admin.questions.import-update-item', $q1->id), $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $q1Fresh = $q1->fresh();
        $this->assertEquals('Updated Question Text: What storage redundancy is optimal?', $q1Fresh->normalized_data['question_text']);
        $this->assertEquals('Storage Architecture', $q1Fresh->normalized_data['topic']);

        // Assert review page reflects edited content
        $resReview = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-item-review', $q1->id));
        $resReview->assertStatus(200);
        $resReview->assertSeeText('Updated Question Text: What storage redundancy is optimal?');
        $resReview->assertSeeText('Storage Architecture');
    }

    public function test_batch_scoped_candidate_urls_resolve_correctly()
    {
        [$q1] = $this->createCandidates();

        $resPreview = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-candidate-preview', [$this->batch->uuid, $q1->id]));
        $resPreview->assertStatus(200);

        $resReview = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-candidate-review', [$this->batch->uuid, $q1->id]));
        $resReview->assertStatus(200);

        $resEdit = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-candidate-edit', [$this->batch->uuid, $q1->id]));
        $resEdit->assertStatus(200);
    }

    public function test_invalid_candidate_id_returns_404()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.import-item-preview', 999999));

        $response->assertStatus(404);
    }

    public function test_live_question_show_and_preview_endpoints()
    {
        $liveQuestion = Question::create([
            'exam_id' => $this->exam->id,
            'question_text' => 'What is Azure Active Directory?',
            'question_type' => 'single_choice',
            'topic' => 'Identity Services',
            'status' => 'published',
            'explanation' => 'Azure AD is Microsoft cloud-based identity and access management service.',
            'is_active' => true,
        ]);

        $resShow = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.show', $liveQuestion->id));
        $resShow->assertStatus(200);
        $resShow->assertSeeText('What is Azure Active Directory?');
        $resShow->assertSeeText('Identity Services');

        $resPreview = $this->actingAs($this->adminUser)
            ->get(route('admin.questions.preview', $liveQuestion->id));
        $resPreview->assertStatus(200);
        $resPreview->assertSeeText('What is Azure Active Directory?');
        $resPreview->assertDontSeeText('Azure AD is Microsoft cloud-based identity and access management service.');
    }
}
