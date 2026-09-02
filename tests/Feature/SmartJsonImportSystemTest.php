<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionImportBatch;
use App\Models\QuestionImportItem;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SmartJsonImportSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Exam $examA;
    protected Exam $examB;

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

        $this->examA = Exam::create([
            'vendor_id' => $vendor->id,
            'exam_code' => 'AZ-900',
            'exam_name' => 'Azure Fundamentals',
            'slug' => 'az-900',
            'question_count' => 0,
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'is_active' => true,
        ]);

        $this->examB = Exam::create([
            'vendor_id' => $vendor->id,
            'exam_code' => 'AZ-104',
            'exam_name' => 'Azure Administrator',
            'slug' => 'az-104',
            'question_count' => 0,
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'is_active' => true,
        ]);
    }

    /**
     * Test 1: Upload Legacy JSON V1 file.
     */
    public function test_legacy_v1_json_import_normalizes_and_creates_batch(): void
    {
        $json = json_encode([
            [
                'question_text' => 'What is Azure Blob Storage?',
                'option_a' => 'Object storage',
                'option_b' => 'File storage',
                'option_c' => 'Block storage',
                'option_d' => 'Table storage',
                'correct_option' => 'A',
                'explanation' => 'Blob storage is optimized for storing massive amounts of unstructured data.',
                'topic' => 'Core Storage Services',
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('legacy_v1.json', $json);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-upload'), [
            'json_file' => $file,
            'default_exam_id' => $this->examA->id,
        ]);

        $batch = QuestionImportBatch::first();
        $this->assertNotNull($batch);
        $this->assertEquals('Legacy Question Format V1', $batch->format_detected);
        $this->assertEquals(1, $batch->total_questions);
        $this->assertEquals(1, $batch->valid_count);

        $response->assertRedirect(route('admin.questions.import-review', $batch->uuid));

        $item = $batch->items()->first();
        $this->assertEquals('valid', $item->validation_status);
        $this->assertEquals('single_choice', $item->normalized_data['question_type']);
        $this->assertEquals('Object storage', $item->normalized_data['options'][0]['text']);
        $this->assertEquals(['A'], $item->normalized_data['correct_answers']);
    }

    /**
     * Test 2: Upload Universal JSON V2 file.
     */
    public function test_universal_v2_json_import(): void
    {
        $json = json_encode([
            'version' => '2.0',
            'questions' => [
                [
                    'exam_id' => $this->examA->id,
                    'topic' => 'Security',
                    'question_type' => 'multiple_choice',
                    'question_text' => 'Which two security controls protect Azure VMs?',
                    'instructions' => 'Select two answers.',
                    'options' => [
                        ['key' => 'A', 'text' => 'Network Security Groups (NSGs)'],
                        ['key' => 'B', 'text' => 'Azure Bastion'],
                        ['key' => 'C', 'text' => 'Azure Cost Management'],
                    ],
                    'correct_answers' => ['A', 'B'],
                    'explanation' => 'NSGs and Bastion provide perimeter and management security.',
                ]
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('universal_v2.json', $json);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-upload'), [
            'json_file' => $file,
        ]);

        $batch = QuestionImportBatch::first();
        $this->assertNotNull($batch);
        $this->assertEquals('Universal Question Format V2', $batch->format_detected);
        $this->assertEquals(1, $batch->valid_count);

        $item = $batch->items()->first();
        $this->assertEquals('valid', $item->validation_status);
        $this->assertEquals('multiple_choice', $item->normalized_data['question_type']);
        $this->assertEquals(['A', 'B'], $item->normalized_data['correct_answers']);
    }

    /**
     * Test 3: Mixed V1 + V2 questions in same file.
     */
    public function test_mixed_json_file_normalizes_individually(): void
    {
        $json = json_encode([
            // V1 item
            [
                'question_text' => 'Legacy Question 1',
                'option_a' => 'Yes',
                'option_b' => 'No',
                'correct_option' => 'A',
                'topic' => 'General',
                'explanation' => 'Exp',
            ],
            // V2 item
            [
                'question_text' => 'Universal Question 2',
                'question_type' => 'single_choice',
                'options' => [
                    ['key' => 'A', 'text' => 'Alpha'],
                    ['key' => 'B', 'text' => 'Beta']
                ],
                'correct_answers' => ['B'],
                'topic' => 'General',
                'explanation' => 'Exp',
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('mixed.json', $json);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-upload'), [
            'json_file' => $file,
            'default_exam_id' => $this->examA->id,
        ]);

        $batch = QuestionImportBatch::first();
        $this->assertNotNull($batch);
        $this->assertEquals('Mixed (V1 + V2)', $batch->format_detected);
        $this->assertEquals(2, $batch->total_questions);
        $this->assertEquals(2, $batch->valid_count);
    }

    /**
     * Test 4: Malformed JSON returns line-specific error.
     */
    public function test_malformed_json_returns_error(): void
    {
        $malformedJson = "{\n  \"questions\": [\n    {\n      \"question_text\": \"Incomplete json\"\n";

        $file = UploadedFile::fake()->createWithContent('broken.json', $malformedJson);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-upload'), [
            'json_file' => $file,
        ]);

        $response->assertSessionHasErrors('json_file');
        $this->assertEquals(0, QuestionImportBatch::count());
    }

    /**
     * Test 5: Invalid question marked as ERROR.
     */
    public function test_invalid_question_marked_as_error(): void
    {
        $json = json_encode([
            [
                'question_text' => 'Invalid Question with missing answer option',
                'options' => [
                    ['key' => 'A', 'text' => 'Option A'],
                    ['key' => 'B', 'text' => 'Option B'],
                ],
                'correct_answers' => ['F'], // Key F does not exist
                'topic' => 'Testing',
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('error_q.json', $json);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-upload'), [
            'json_file' => $file,
            'default_exam_id' => $this->examA->id,
        ]);

        $batch = QuestionImportBatch::first();
        $this->assertEquals(1, $batch->error_count);
        $item = $batch->items()->first();
        $this->assertEquals('error', $item->validation_status);
        $this->assertContains('Correct answer "F" does not exist in available options.', $item->validation_errors);
    }

    /**
     * Test 6: Warning question marked on missing explanation.
     */
    public function test_missing_explanation_marked_as_warning(): void
    {
        $json = json_encode([
            [
                'question_text' => 'Question without explanation',
                'option_a' => 'A',
                'option_b' => 'B',
                'correct_option' => 'A',
                'topic' => 'Compute',
                'explanation' => '', // missing
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('warn_q.json', $json);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-upload'), [
            'json_file' => $file,
            'default_exam_id' => $this->examA->id,
        ]);

        $batch = QuestionImportBatch::first();
        $this->assertEquals(1, $batch->warning_count);
        $item = $batch->items()->first();
        $this->assertEquals('warning', $item->validation_status);
        $this->assertContains('No answer explanation provided.', $item->validation_warnings);
    }

    /**
     * Test 7: Duplicate detection flags matching question.
     */
    public function test_duplicate_question_detected_and_flagged(): void
    {
        // 1. Create an existing question in the DB
        Question::saveFromUniversalModel([
            'exam_id' => $this->examA->id,
            'topic' => 'Storage',
            'question_type' => 'single_choice',
            'question_text' => 'What is Azure Blob Storage?',
            'options' => [
                ['key' => 'A', 'text' => 'Object storage'],
                ['key' => 'B', 'text' => 'File storage'],
            ],
            'correct_answers' => ['A'],
        ]);

        // 2. Upload duplicate question
        $json = json_encode([
            [
                'question_text' => 'What   is   Azure   Blob  Storage ?', // Same text with spaces
                'option_a' => 'Object storage',
                'option_b' => 'File storage',
                'correct_option' => 'A',
                'topic' => 'Storage',
                'explanation' => 'Exp',
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('dup_q.json', $json);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-upload'), [
            'json_file' => $file,
            'default_exam_id' => $this->examA->id,
        ]);

        $batch = QuestionImportBatch::first();
        $this->assertEquals(1, $batch->duplicate_count);
        $item = $batch->items()->first();
        $this->assertEquals('duplicate', $item->validation_status);
        $this->assertNotNull($item->duplicate_question_id);
    }

    /**
     * Test 8: Exam resolution priorities.
     */
    public function test_exam_resolution_priorities(): void
    {
        $json = json_encode([
            // 1. Has direct exam_id
            [
                'exam_id' => $this->examB->id,
                'question_text' => 'Question with exam_id',
                'option_a' => 'A',
                'option_b' => 'B',
                'correct_option' => 'A',
                'topic' => 'Topic',
                'explanation' => 'Exp',
            ],
            // 2. Has exam_code
            [
                'exam_code' => 'AZ-104',
                'question_text' => 'Question with exam_code',
                'option_a' => 'A',
                'option_b' => 'B',
                'correct_option' => 'A',
                'topic' => 'Topic',
                'explanation' => 'Exp',
            ],
            // 3. Fallback to default
            [
                'question_text' => 'Question with default exam',
                'option_a' => 'A',
                'option_b' => 'B',
                'correct_option' => 'A',
                'topic' => 'Topic',
                'explanation' => 'Exp',
            ],
        ]);

        $file = UploadedFile::fake()->createWithContent('resolution.json', $json);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-upload'), [
            'json_file' => $file,
            'default_exam_id' => $this->examA->id,
        ]);

        $batch = QuestionImportBatch::first();
        $items = $batch->items;

        $this->assertEquals($this->examB->id, $items[0]->normalized_data['exam_id']);
        $this->assertEquals($this->examB->id, $items[1]->normalized_data['exam_id']);
        $this->assertEquals($this->examA->id, $items[2]->normalized_data['exam_id']);
    }

    /**
     * Test 9: Edit question during review and re-validate.
     */
    public function test_edit_imported_item_revalidates(): void
    {
        // 1. Create item with error
        $json = json_encode([
            [
                'question_text' => 'Broken question',
                'option_a' => 'A',
                'option_b' => 'B',
                'correct_option' => 'Z', // Invalid answer
                'topic' => 'Topic',
                'explanation' => 'Exp',
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('broken.json', $json);
        $this->actingAs($this->admin)->post(route('admin.questions.import-upload'), [
            'json_file' => $file,
            'default_exam_id' => $this->examA->id,
        ]);

        $item = QuestionImportItem::first();
        $this->assertEquals('error', $item->validation_status);

        // 2. Fix via PUT request
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.questions.import-update-item', $item->id), [
            'exam_id' => $this->examA->id,
            'topic' => 'Fixed Topic',
            'question_type' => 'single_choice',
            'question_text' => 'Fixed question text',
            'options' => [
                ['key' => 'A', 'text' => 'Option A'],
                ['key' => 'B', 'text' => 'Option B'],
            ],
            'correct_answers' => ['A'],
            'explanation' => 'Now with explanation',
        ]);

        $updateResponse->assertOk();
        $updateResponse->assertJsonPath('item.validation_status', 'valid');

        $item->refresh();
        $this->assertEquals('valid', $item->validation_status);
        $this->assertEquals('Fixed question text', $item->normalized_data['question_text']);
    }

    /**
     * Test 10: Import selected items into database.
     */
    public function test_import_selected_items_into_live_database(): void
    {
        $json = json_encode([
            [
                'question_text' => 'Approved Question 1',
                'option_a' => 'A',
                'option_b' => 'B',
                'correct_option' => 'A',
                'topic' => 'Topic 1',
                'explanation' => 'Exp',
            ],
            [
                'question_text' => 'Skipped Question 2',
                'option_a' => 'A',
                'option_b' => 'B',
                'correct_option' => 'A',
                'topic' => 'Topic 2',
                'explanation' => 'Exp',
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('batch.json', $json);
        $this->actingAs($this->admin)->post(route('admin.questions.import-upload'), [
            'json_file' => $file,
            'default_exam_id' => $this->examA->id,
        ]);

        $batch = QuestionImportBatch::first();
        $item1 = $batch->items[0];

        // Only select item 1
        $confirmResponse = $this->actingAs($this->admin)->post(route('admin.questions.import-confirm-selected', $batch->uuid), [
            'item_ids' => [$item1->id],
        ]);

        $confirmResponse->assertOk();
        $confirmResponse->assertJsonPath('success', true);
        $confirmResponse->assertJsonPath('imported_count', 1);

        $this->assertEquals(1, Question::where('question_text', 'Approved Question 1')->count());
        $this->assertEquals(0, Question::where('question_text', 'Skipped Question 2')->count());

        $savedQ = Question::where('question_text', 'Approved Question 1')->first();
        $this->assertEquals('draft', $savedQ->status);
        $this->assertFalse($savedQ->is_active);
    }

    /**
     * Test 11: Error report download returns valid CSV stream.
     */
    public function test_error_report_download(): void
    {
        $json = json_encode([
            [
                'question_text' => 'Error Item for Report',
                'options' => [
                    ['key' => 'A', 'text' => 'A'],
                    ['key' => 'B', 'text' => 'B']
                ],
                'correct_answers' => ['X'],
                'topic' => 'Topic',
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('error_report.json', $json);
        $this->actingAs($this->admin)->post(route('admin.questions.import-upload'), [
            'json_file' => $file,
            'default_exam_id' => $this->examA->id,
        ]);

        $batch = QuestionImportBatch::first();
        $response = $this->actingAs($this->admin)->get(route('admin.questions.import-error-report', $batch->uuid));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
