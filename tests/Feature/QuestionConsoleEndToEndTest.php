<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Exam;
use App\Models\Vendor;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestionConsoleEndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $vendor = Vendor::create([
            'name' => 'Amazon Web Services',
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

    /**
     * Test 1: Manually create a Single Choice question and verify database persistence.
     */
    public function test_manual_single_choice_creation(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.questions.store'), [
            'exam_id' => $this->exam->id,
            'topic' => 'Cloud Architecture',
            'question_type' => 'single_choice',
            'status' => 'draft',
            'question_text' => 'Which AWS service is a managed relational database?',
            'instructions' => 'Select one answer.',
            'options' => [
                ['key' => 'A', 'text' => 'Amazon RDS'],
                ['key' => 'B', 'text' => 'Amazon DynamoDB'],
                ['key' => 'C', 'text' => 'Amazon S3'],
                ['key' => 'D', 'text' => 'Amazon EC2'],
            ],
            'correct_answers' => ['A'],
            'explanation' => 'Amazon RDS is a managed relational database service.',
        ]);

        $response->assertRedirect(route('admin.questions.index', ['exam_id' => $this->exam->id]));
        $response->assertSessionHas('success');

        $question = Question::where('question_text', 'Which AWS service is a managed relational database?')->first();
        $this->assertNotNull($question);
        $this->assertEquals('single_choice', $question->question_type);
        $this->assertEquals('draft', $question->status);
        $this->assertEquals('Amazon RDS', $question->option_a);
        $this->assertEquals('A', $question->correct_option);
        $this->assertEquals(4, $question->options()->count());
        $this->assertEquals(1, $question->answers()->count());
    }

    /**
     * Test 2: Create a Multiple Choice question with 5 options (A, B, C, D, E) and select A, C, E.
     */
    public function test_manual_multiple_choice_creation_with_five_options(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.questions.store'), [
            'exam_id' => $this->exam->id,
            'topic' => 'Storage & Networking',
            'question_type' => 'multiple_choice',
            'status' => 'approved',
            'question_text' => 'Which THREE services provide persistent storage in AWS?',
            'instructions' => 'Select three answers.',
            'options' => [
                ['key' => 'A', 'text' => 'Amazon EBS'],
                ['key' => 'B', 'text' => 'Amazon Lambda'],
                ['key' => 'C', 'text' => 'Amazon S3'],
                ['key' => 'D', 'text' => 'Amazon CloudFront'],
                ['key' => 'E', 'text' => 'Amazon EFS'],
            ],
            'correct_answers' => ['A', 'C', 'E'],
            'explanation' => 'EBS, S3, and EFS are storage solutions.',
        ]);

        $response->assertRedirect(route('admin.questions.index', ['exam_id' => $this->exam->id]));

        $question = Question::where('question_text', 'Which THREE services provide persistent storage in AWS?')->first();
        $this->assertNotNull($question);
        $this->assertEquals('multiple_choice', $question->question_type);
        $this->assertEquals('approved', $question->status);
        $this->assertEquals(5, $question->options()->count());
        $this->assertEquals(3, $question->answers()->count());
        $this->assertEquals('A,C,E', $question->correct_option);
    }

    /**
     * Test 3: Create a Drag & Drop question and verify sequence storage.
     */
    public function test_manual_drag_and_drop_creation(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.questions.store'), [
            'exam_id' => $this->exam->id,
            'topic' => 'CI/CD Pipeline',
            'question_type' => 'drag_drop',
            'status' => 'draft',
            'question_text' => 'Order the continuous delivery stages chronologically.',
            'drag_items' => [
                ['id' => 'item_1', 'text' => 'Source Code Commit'],
                ['id' => 'item_2', 'text' => 'Build & Unit Test'],
                ['id' => 'item_3', 'text' => 'Deploy to Staging'],
                ['id' => 'item_4', 'text' => 'Production Release'],
            ],
            'correct_order' => ['item_1', 'item_2', 'item_3', 'item_4'],
            'explanation' => 'Standard CI/CD delivery lifecycle.',
        ]);

        $response->assertRedirect(route('admin.questions.index', ['exam_id' => $this->exam->id]));

        $question = Question::where('question_text', 'Order the continuous delivery stages chronologically.')->first();
        $this->assertNotNull($question);
        $this->assertEquals('drag_drop', $question->question_type);
        $this->assertCount(4, $question->question_data['drag_items']);
        $this->assertEquals(['item_1', 'item_2', 'item_3', 'item_4'], $question->question_data['correct_order']);
    }

    /**
     * Test 4: Add three references and verify they save and reload.
     */
    public function test_multiple_references_saved_and_reloaded(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.questions.store'), [
            'exam_id' => $this->exam->id,
            'topic' => 'Security',
            'question_type' => 'single_choice',
            'question_text' => 'Which service handles IAM policy evaluation?',
            'options' => [
                ['key' => 'A', 'text' => 'AWS IAM'],
                ['key' => 'B', 'text' => 'AWS STS'],
            ],
            'correct_answers' => ['A'],
            'references' => [
                ['title' => 'AWS IAM Policy Guide', 'url' => 'https://docs.aws.amazon.com/iam/policies.html'],
                ['title' => 'AWS Security Best Practices', 'url' => 'https://aws.amazon.com/security/best-practices/'],
                ['title' => 'Whitepaper on Access Management', 'url' => 'https://aws.amazon.com/whitepapers/iam.pdf'],
            ],
        ]);

        $response->assertRedirect();

        $question = Question::where('question_text', 'Which service handles IAM policy evaluation?')->first();
        $this->assertNotNull($question);
        $this->assertEquals(3, $question->references()->count());
        $this->assertEquals('AWS IAM Policy Guide', $question->references->first()->title);
        $this->assertEquals('https://docs.aws.amazon.com/iam/policies.html', $question->references->first()->url);
    }

    /**
     * Test 5: Upload an exhibit image and verify physical file, URL, and caption storage.
     */
    public function test_media_upload_physical_storage_and_caption(): void
    {
        $image = UploadedFile::fake()->image('architecture_diagram.png', 600, 400);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.store'), [
            'exam_id' => $this->exam->id,
            'topic' => 'Networking',
            'question_type' => 'single_choice',
            'question_text' => 'Refer to the exhibit. Which subnet is public?',
            'options' => [
                ['key' => 'A', 'text' => 'Subnet A (10.0.1.0/24)'],
                ['key' => 'B', 'text' => 'Subnet B (10.0.2.0/24)'],
            ],
            'correct_answers' => ['A'],
            'media_file' => $image,
            'media_caption' => 'VPC Architecture Diagram with Internet Gateway',
        ]);

        $response->assertRedirect();

        $question = Question::where('question_text', 'Refer to the exhibit. Which subnet is public?')->first();
        $this->assertNotNull($question);
        $this->assertEquals(1, $question->media()->count());
        
        $media = $question->media->first();
        $this->assertEquals('VPC Architecture Diagram with Internet Gateway', $media->caption);
        $this->assertStringContainsString('architecture_diagram.png', $media->media_url);
    }

    /**
     * Test 6: Edit Question flow (load, modify, save again).
     */
    public function test_edit_question_lifecycle(): void
    {
        // 1. Initial creation
        $question = Question::saveFromUniversalModel([
            'exam_id' => $this->exam->id,
            'topic' => 'Compute',
            'question_type' => 'single_choice',
            'question_text' => 'Initial question text?',
            'options' => [
                ['key' => 'A', 'text' => 'Initial Option A'],
                ['key' => 'B', 'text' => 'Initial Option B'],
            ],
            'correct_answers' => ['A'],
        ]);

        // 2. Render edit view
        $editResponse = $this->actingAs($this->admin)->get(route('admin.questions.edit', $question->id));
        $editResponse->assertOk();
        $editResponse->assertSee('Initial question text?');

        // 3. Update via PUT request
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.questions.update', $question->id), [
            'exam_id' => $this->exam->id,
            'topic' => 'Serverless Compute',
            'question_type' => 'single_choice',
            'status' => 'published',
            'question_text' => 'Updated question text about AWS Lambda?',
            'options' => [
                ['key' => 'A', 'text' => 'AWS Lambda Functions'],
                ['key' => 'B', 'text' => 'Amazon ECS Containers'],
                ['key' => 'C', 'text' => 'Amazon EKS Kubernetes'],
            ],
            'correct_answers' => ['A'],
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect(route('admin.questions.index', ['exam_id' => $this->exam->id]));

        $question->refresh();
        $this->assertEquals('Updated question text about AWS Lambda?', $question->question_text);
        $this->assertEquals('Serverless Compute', $question->topic);
        $this->assertEquals('published', $question->status);
        $this->assertTrue($question->is_active);
        $this->assertEquals(3, $question->options()->count());
    }

    /**
     * Test 7: Attempt invalid submission and verify validation errors.
     */
    public function test_attempt_invalid_submission_rejected(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.questions.store'), [
            // Missing exam_id and question_text
            'topic' => 'Missing Fields',
            'question_type' => 'single_choice',
        ]);

        $response->assertSessionHasErrors(['exam_id', 'question_text']);
    }
}
