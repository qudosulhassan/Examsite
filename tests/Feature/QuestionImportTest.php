<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Exam;
use App\Models\Vendor;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class QuestionImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Create a vendor and exam
        $vendor = Vendor::create([
            'name' => 'AWS',
            'slug' => 'aws',
            'category' => 'Cloud',
            'is_active' => true,
        ]);

        $this->exam = Exam::create([
            'vendor_id' => $vendor->id,
            'exam_code' => 'CLF-C02',
            'exam_name' => 'AWS Certified Cloud Practitioner',
            'slug' => 'clf-c02',
            'question_count' => 0,
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'is_active' => true,
        ]);
    }

    public function test_import_form_can_be_rendered(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.questions.import-form'));
        $response->assertStatus(200);
        $response->assertSee('Bulk Question Importer');
        $response->assertSee($this->exam->exam_code);
    }

    public function test_import_form_requires_admin(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $response = $this->actingAs($student)->get(route('admin.questions.import-form'));
        $response->assertStatus(302); // Redirect to homepage
    }

    public function test_import_bulk_questions_successfully(): void
    {
        $jsonContent = json_encode([
            [
                'question_text' => 'What is AWS IaaS service?',
                'option_a' => 'EC2',
                'option_b' => 'S3',
                'option_c' => 'RDS',
                'option_d' => 'Lambda',
                'correct_option' => 'A',
                'explanation' => 'EC2 is an Infrastructure as a Service offering.',
                'topic' => 'Compute Services',
                'is_active' => true,
            ],
            [
                'question_text' => 'Which service is a managed NoSQL database?',
                'option_a' => 'RDS',
                'option_b' => 'DynamoDB',
                'option_c' => 'Aurora',
                'option_d' => 'ElastiCache',
                'correct_option' => 'B',
                'explanation' => 'DynamoDB is a fully managed NoSQL database service.',
                'topic' => 'Database Services',
                'is_active' => true,
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('questions.json', $jsonContent);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import'), [
            'exam_id' => $this->exam->id,
            'json_file' => $file,
        ]);

        $response->assertRedirect(route('admin.questions.index', ['exam_id' => $this->exam->id]));
        $response->assertSessionHas('success', '2 questions imported successfully.');

        // Assert database holds the questions
        $this->assertDatabaseHas('questions', [
            'exam_id' => $this->exam->id,
            'question_text' => 'What is AWS IaaS service?',
        ]);

        $q1 = Question::where('question_text', 'What is AWS IaaS service?')->first();
        $this->assertNotNull($q1);
        $this->assertEquals('A', $q1->correct_option);
        $this->assertEquals('EC2', $q1->option_a);

        $this->assertDatabaseHas('questions', [
            'exam_id' => $this->exam->id,
            'question_text' => 'Which service is a managed NoSQL database?',
        ]);

        $q2 = Question::where('question_text', 'Which service is a managed NoSQL database?')->first();
        $this->assertNotNull($q2);
        $this->assertEquals('B', $q2->correct_option);
        $this->assertEquals('DynamoDB', $q2->option_b);

        // Verify the exam question_count is updated
        $this->exam->refresh();
        $this->assertEquals(2, $this->exam->question_count);
    }

    public function test_import_rolls_back_entirely_on_item_validation_error(): void
    {
        // First item is valid, second is missing options (option_b) which is required
        $jsonContent = json_encode([
            [
                'question_text' => 'Valid question 1',
                'option_a' => 'Opt A',
                'option_b' => 'Opt B',
                'correct_option' => 'A',
            ],
            [
                'question_text' => 'Invalid question 2',
                'option_a' => 'Opt A',
                // Missing option_b
                'correct_option' => 'A',
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('questions.json', $jsonContent);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import'), [
            'exam_id' => $this->exam->id,
            'json_file' => $file,
        ]);

        $response->assertSessionHasErrors(['json_file']);
        
        // Assert no questions were imported due to rollback
        $this->assertEquals(0, Question::count());

        $this->exam->refresh();
        $this->assertEquals(0, $this->exam->question_count);
    }

    public function test_can_download_sample_json(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.questions.import-sample'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="sample-questions-import.json"');
        $response->assertJsonStructure([
            '*' => [
                'question_text',
                'option_a',
                'option_b',
                'option_c',
                'option_d',
                'correct_option',
                'explanation',
                'topic',
                'is_active'
            ]
        ]);
    }
}
