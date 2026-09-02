<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Exam;
use App\Models\Vendor;
use App\Models\Question;
use App\Models\ImportHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class QuestionAdvancedImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $vendor = Vendor::create([
            'name' => 'Microsoft',
            'slug' => 'microsoft',
            'category' => 'Cloud',
            'is_active' => true,
        ]);

        $this->exam = Exam::create([
            'vendor_id' => $vendor->id,
            'exam_code' => 'AZ-900',
            'exam_name' => 'Microsoft Azure Fundamentals',
            'slug' => 'az-900',
            'question_count' => 0,
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'is_active' => true,
        ]);
    }

    public function test_validate_import_legacy_compatibility(): void
    {
        $legacyJson = json_encode([
            [
                'question_text' => 'Which service is cloud storage?',
                'option_a' => 'Azure VM',
                'option_b' => 'Azure Blob Storage',
                'option_c' => 'Azure SQL',
                'option_d' => 'Azure Functions',
                'correct_option' => 'B',
                'explanation' => 'Blob storage stores unstructured data.',
                'topic' => 'Storage Services',
                'is_active' => true,
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('legacy.json', $legacyJson);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-validate'), [
            'exam_id' => $this->exam->id,
            'json_file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonPath('summary.valid_count', 1);
        $response->assertJsonPath('summary.questions.0.universal.question_type', 'single_choice');
        $response->assertJsonPath('summary.questions.0.universal.options.1.text', 'Azure Blob Storage');
        $response->assertJsonPath('summary.questions.0.universal.correct_answers.0', 'B');
    }

    public function test_validate_import_drag_and_drop_validation(): void
    {
        $dragJson = json_encode([
            [
                'question_type' => 'drag_drop',
                'question_text' => 'Arrange the deployment sequence.',
                'drag_items' => [
                    ['id' => 'item_1', 'text' => 'Plan'],
                    ['id' => 'item_2', 'text' => 'Code']
                ],
                // Item IDs must exist in drag_items
                'correct_order' => ['item_1', 'item_2']
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('drag.json', $dragJson);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-validate'), [
            'exam_id' => $this->exam->id,
            'json_file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonPath('summary.valid_count', 1);
        $response->assertJsonPath('summary.questions.0.status', 'warning'); // warning due to missing explanation
    }

    public function test_validate_import_hotspot_box_error_checking(): void
    {
        $hotspotJson = json_encode([
            [
                'question_type' => 'hotspot',
                'question_text' => 'Configure the values.',
                'hotspot_answers' => [
                    [
                        'id' => 'box_1',
                        'label' => 'Subnet select',
                        'options' => ['10.0.1.0/24', '10.0.2.0/24'],
                        'correct_answer' => '192.168.1.0/24' // Incorrect because it's not in options
                    ]
                ]
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('hotspot.json', $hotspotJson);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-validate'), [
            'exam_id' => $this->exam->id,
            'json_file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonPath('summary.error_count', 1);
        $response->assertJsonPath('summary.questions.0.status', 'error');
        $response->assertJsonFragment([
            "Hotspot box correct answer '192.168.1.0/24' is not in dropdown options."
        ]);
    }

    public function test_duplicate_detection_warns_on_hash_match(): void
    {
        // Save an existing question in the DB
        $existing = Question::saveFromUniversalModel([
            'exam_id' => $this->exam->id,
            'question_text' => 'What is Azure Active Directory?',
            'question_type' => 'single_choice',
            'options' => [
                ['key' => 'A', 'text' => 'IAM Service'],
                ['key' => 'B', 'text' => 'Compute Service']
            ],
            'correct_answers' => ['A']
        ]);

        // Upload same question text (spacing/case variation)
        $dupJson = json_encode([
            [
                'question_text' => '  WHAT is Azure active directory? ',
                'option_a' => 'IAM Service',
                'option_b' => 'Compute Service',
                'correct_option' => 'A'
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('dup.json', $dupJson);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-validate'), [
            'exam_id' => $this->exam->id,
            'json_file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonPath('summary.duplicate_count', 1);
        $response->assertJsonPath('summary.questions.0.status', 'duplicate');
    }

    public function test_confirm_import_saves_to_database_and_records_history(): void
    {
        $universalQuestions = [
            [
                'question_text' => 'Which model is SaaS?',
                'question_type' => 'single_choice',
                'topic' => 'Cloud Concepts',
                'options' => [
                    ['key' => 'A', 'text' => 'Office 365'],
                    ['key' => 'B', 'text' => 'VMs']
                ],
                'correct_answers' => ['A']
            ]
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-confirm'), [
            'exam_id' => $this->exam->id,
            'filename' => 'saas_questions.json',
            'questions' => $universalQuestions
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        // Verify Question database count
        $q = Question::where('question_text', 'Which model is SaaS?')->first();
        $this->assertNotNull($q);
        $this->assertEquals('A', $q->correct_option);
        $this->assertEquals('Office 365', $q->option_a);

        // Verify history logger
        $this->assertDatabaseHas('import_histories', [
            'filename' => 'saas_questions.json',
            'imported_count' => 1,
            'status' => 'completed',
        ]);
    }

    public function test_validate_import_resolves_exam_code_and_warns_on_invalid(): void
    {
        $json = json_encode([
            [
                'question_text' => 'Resolved via code.',
                'exam_code' => 'AZ-900',
                'option_a' => 'Yes',
                'option_b' => 'No',
                'correct_option' => 'A'
            ],
            [
                'question_text' => 'Unresolved code.',
                'exam_code' => 'NON-EXIST',
                'option_a' => 'Yes',
                'option_b' => 'No',
                'correct_option' => 'A'
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('code_resolve.json', $json);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-validate'), [
            'exam_id' => $this->exam->id,
            'json_file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonPath('summary.error_count', 1);
        $response->assertJsonFragment([
            "Exam code 'NON-EXIST' could not be resolved to a valid exam."
        ]);
        $response->assertJsonPath('summary.questions.0.universal.exam_id', $this->exam->id);
    }

    public function test_validate_import_splits_comma_less_multi_options(): void
    {
        $json = json_encode([
            [
                'question_text' => 'Multiple options test.',
                'option_a' => 'Ans A',
                'option_b' => 'Ans B',
                'option_c' => 'Ans C',
                'correct_option' => 'AC'
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('comma_less.json', $json);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-validate'), [
            'exam_id' => $this->exam->id,
            'json_file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonPath('summary.valid_count', 1);
        $response->assertJsonPath('summary.questions.0.universal.question_type', 'multiple_choice');
        $response->assertJsonPath('summary.questions.0.universal.correct_answers', ['A', 'C']);
    }

    public function test_validate_import_correct_answer_must_exist_in_options(): void
    {
        $json = json_encode([
            [
                'question_text' => 'Incorrect answer mapping.',
                'option_a' => 'Ans A',
                'option_b' => 'Ans B',
                'correct_option' => 'C'
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('invalid_ans.json', $json);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-validate'), [
            'exam_id' => $this->exam->id,
            'json_file' => $file,
        ]);

        $response->assertOk();
        $response->assertJsonPath('summary.error_count', 1);
        $response->assertJsonFragment([
            'Correct answer "C" does not exist in available options.'
        ]);
    }
}
