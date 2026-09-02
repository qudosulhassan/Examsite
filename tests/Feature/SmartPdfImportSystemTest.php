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

class SmartPdfImportSystemTest extends TestCase
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
     * Helper to generate a valid PDF byte string.
     */
    protected function generateTestPdf(array $pagesContent): string
    {
        $pdf = "%PDF-1.4\n";
        $pageObjs = [];
        $objIndex = 4;

        $pageCount = count($pagesContent);

        foreach ($pagesContent as $idx => $content) {
            $pageObjNum = $objIndex++;
            $streamObjNum = $objIndex++;
            $pageObjs[] = $pageObjNum;

            $escapedLines = [];
            foreach (explode("\n", $content) as $line) {
                $escaped = str_replace(['(', ')', '\\'], ['\\(', '\\)', '\\\\'], $line);
                $escapedLines[] = "({$escaped}) Tj T*";
            }
            $streamText = "BT /F1 12 Tf 50 750 Td\n" . implode("\n", $escapedLines) . "\nET";
            $streamLen = strlen($streamText);

            $pdf .= "{$pageObjNum} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents {$streamObjNum} 0 R /Resources << /Font << /F1 << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> >> >> >>\nendobj\n";
            $pdf .= "{$streamObjNum} 0 obj\n<< /Length {$streamLen} >>\nstream\n{$streamText}\nendstream\nendobj\n";
        }

        $kidsStr = implode(' 0 R ', $pageObjs) . ' 0 R';
        $header = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pagesCatalog = "2 0 obj\n<< /Type /Pages /Kids [{$kidsStr}] /Count {$pageCount} >>\nendobj\n";

        return "%PDF-1.4\n" . $header . $pagesCatalog . $pdf . "xref\n0 10\ntrailer\n<< /Size 10 /Root 1 0 R >>\nstartxref\n500\n%%EOF";
    }

    /**
     * Test 1: Upload a single-choice question PDF and verify extraction into review batch.
     */
    public function test_pdf_upload_and_extraction_creates_review_batch(): void
    {
        $content = "Question 1:\nWhich AWS service provides serverless compute?\nA. Amazon EC2\nB. AWS Lambda\nC. Amazon S3\nD. Amazon RDS\nAnswer: B\nExplanation: AWS Lambda lets you run code without provisioning or managing servers.";

        $pdfBytes = $this->generateTestPdf([$content]);
        $file = UploadedFile::fake()->createWithContent('aws_exam.pdf', $pdfBytes);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-pdf-upload'), [
            'pdf_file' => $file,
            'default_exam_id' => $this->exam->id,
        ]);

        $batch = QuestionImportBatch::first();
        $this->assertNotNull($batch);
        $this->assertEquals('pdf_import', $batch->source_type);
        $this->assertEquals('aws_exam.pdf', $batch->filename);
        $this->assertEquals(1, $batch->total_questions);

        $response->assertRedirect(route('admin.questions.import-review', $batch->uuid));

        $item = $batch->items()->first();
        $this->assertEquals('valid', $item->validation_status);
        $this->assertEquals('Which AWS service provides serverless compute?', $item->normalized_data['question_text']);
        $this->assertEquals(4, count($item->normalized_data['options']));
        $this->assertEquals(['B'], $item->normalized_data['correct_answers']);
        $this->assertEquals('single_choice', $item->normalized_data['question_type']);
        $this->assertStringContainsString('AWS Lambda lets you run code', $item->normalized_data['explanation']);
    }

    /**
     * Test 2: Chapter / Topic heading detection from PDF text.
     */
    public function test_pdf_chapter_heading_detection(): void
    {
        $content = "CHAPTER 2: Cloud Security and Compliance\nQuestion 1:\nWhich AWS service helps protect against DDoS attacks?\nA. AWS Shield\nB. Amazon CloudFront\nC. AWS WAF\nD. AWS Inspector\nAnswer: A\nExplanation: AWS Shield is a managed DDoS protection service.";

        $pdfBytes = $this->generateTestPdf([$content]);
        $file = UploadedFile::fake()->createWithContent('security_chapter.pdf', $pdfBytes);

        $this->actingAs($this->admin)->post(route('admin.questions.import-pdf-upload'), [
            'pdf_file' => $file,
            'default_exam_id' => $this->exam->id,
            'topic_strategy' => 'auto',
        ]);

        $item = QuestionImportItem::first();
        $this->assertEquals('Cloud Security and Compliance', $item->normalized_data['topic']);
    }

    /**
     * Test 3: Multiple choice question detection (e.g. Answer: A, C).
     */
    public function test_pdf_multiple_choice_answer_detection(): void
    {
        $content = "Question 1:\nWhich TWO storage services are object-based?\nA. Amazon S3\nB. Amazon EBS\nC. AWS Snowball\nD. Amazon EFS\nAnswers: A, C\nExplanation: S3 and Snowball handle object storage.";

        $pdfBytes = $this->generateTestPdf([$content]);
        $file = UploadedFile::fake()->createWithContent('multi_choice.pdf', $pdfBytes);

        $this->actingAs($this->admin)->post(route('admin.questions.import-pdf-upload'), [
            'pdf_file' => $file,
            'default_exam_id' => $this->exam->id,
        ]);

        $item = QuestionImportItem::first();
        $this->assertEquals('multiple_choice', $item->normalized_data['question_type']);
        $this->assertEquals(['A', 'C'], $item->normalized_data['correct_answers']);
    }

    /**
     * Test 4: Missing answer key produces WARNING and review status pending.
     */
    public function test_pdf_missing_answer_produces_warning(): void
    {
        $content = "Question 1:\nWhat is AWS IAM used for?\nA. Identity Management\nB. Database\nC. Storage\nD. Compute";

        $pdfBytes = $this->generateTestPdf([$content]);
        $file = UploadedFile::fake()->createWithContent('no_answer.pdf', $pdfBytes);

        $this->actingAs($this->admin)->post(route('admin.questions.import-pdf-upload'), [
            'pdf_file' => $file,
            'default_exam_id' => $this->exam->id,
        ]);

        $item = QuestionImportItem::first();
        $this->assertEquals('warning', $item->validation_status);
        $this->assertEquals('pending', $item->review_status);
        $this->assertEmpty($item->normalized_data['correct_answers']);
        $this->assertContains('Correct answer could not be automatically detected in PDF.', $item->validation_warnings);
    }

    /**
     * Test 5: Multi-page question spanning pages 1 and 2.
     */
    public function test_pdf_multi_page_question_spanning_pages(): void
    {
        $page1 = "Question 1:\nWhich database engine is fully managed and compatible with MySQL and PostgreSQL?";
        $page2 = "A. Amazon RDS MySQL\nB. Amazon Aurora\nC. Amazon DynamoDB\nD. Amazon Redshift\nAnswer: B\nExplanation: Aurora provides up to 5x throughput of MySQL.";

        $pdfBytes = $this->generateTestPdf([$page1, $page2]);
        $file = UploadedFile::fake()->createWithContent('multipage.pdf', $pdfBytes);

        $this->actingAs($this->admin)->post(route('admin.questions.import-pdf-upload'), [
            'pdf_file' => $file,
            'default_exam_id' => $this->exam->id,
        ]);

        $item = QuestionImportItem::first();
        $this->assertNotNull($item);
        $this->assertEquals(1, $item->normalized_data['source_reference']['page_start']);
        $this->assertEquals(2, $item->normalized_data['source_reference']['page_end']);
        $this->assertContains('Multi-page question spanning pages 1–2.', $item->validation_warnings);
    }

    /**
     * Test 6: Reference URLs extracted from question text.
     */
    public function test_pdf_reference_urls_extraction(): void
    {
        $content = "Question 1:\nWhat is Amazon EC2?\nA. Compute\nB. Storage\nAnswer: A\nExplanation: Elastic Compute Cloud.\nReferences: https://aws.amazon.com/ec2/";

        $pdfBytes = $this->generateTestPdf([$content]);
        $file = UploadedFile::fake()->createWithContent('references.pdf', $pdfBytes);

        $this->actingAs($this->admin)->post(route('admin.questions.import-pdf-upload'), [
            'pdf_file' => $file,
            'default_exam_id' => $this->exam->id,
        ]);

        $item = QuestionImportItem::first();
        $this->assertNotEmpty($item->normalized_data['references']);
        $this->assertEquals('https://aws.amazon.com/ec2/', $item->normalized_data['references'][0]['url']);
    }

    /**
     * Test 7: Duplicate detection flags PDF question matching database question.
     */
    public function test_pdf_duplicate_detection(): void
    {
        // 1. Existing question in DB
        Question::saveFromUniversalModel([
            'exam_id' => $this->exam->id,
            'topic' => 'Compute',
            'question_type' => 'single_choice',
            'question_text' => 'Which AWS service provides serverless compute?',
            'options' => [
                ['key' => 'A', 'text' => 'Amazon EC2'],
                ['key' => 'B', 'text' => 'AWS Lambda'],
            ],
            'correct_answers' => ['B'],
        ]);

        // 2. Upload duplicate in PDF
        $content = "Question 1:\nWhich AWS service provides serverless compute?\nA. Amazon EC2\nB. AWS Lambda\nAnswer: B\nExplanation: Lambda is serverless.";

        $pdfBytes = $this->generateTestPdf([$content]);
        $file = UploadedFile::fake()->createWithContent('dup.pdf', $pdfBytes);

        $this->actingAs($this->admin)->post(route('admin.questions.import-pdf-upload'), [
            'pdf_file' => $file,
            'default_exam_id' => $this->exam->id,
        ]);

        $item = QuestionImportItem::first();
        $this->assertEquals('duplicate', $item->validation_status);
        $this->assertNotNull($item->duplicate_question_id);
    }

    /**
     * Test 8: Non-PDF or invalid files are rejected.
     */
    public function test_invalid_pdf_file_rejected(): void
    {
        $file = UploadedFile::fake()->createWithContent('fake.pdf', 'NOT_A_PDF_STREAM');

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-pdf-upload'), [
            'pdf_file' => $file,
        ]);

        $response->assertSessionHasErrors('pdf_file');
        $this->assertEquals(0, QuestionImportBatch::count());
    }

    /**
     * Test 9: Importing selected PDF items commits them as drafts.
     */
    public function test_import_selected_pdf_candidates_into_live_database(): void
    {
        $content = "Question 1:\nWhat is AWS S3?\nA. Object Storage\nB. Block Storage\nAnswer: A\nExplanation: Simple Storage Service.";

        $pdfBytes = $this->generateTestPdf([$content]);
        $file = UploadedFile::fake()->createWithContent('s3.pdf', $pdfBytes);

        $this->actingAs($this->admin)->post(route('admin.questions.import-pdf-upload'), [
            'pdf_file' => $file,
            'default_exam_id' => $this->exam->id,
        ]);

        $batch = QuestionImportBatch::first();
        $item = $batch->items()->first();

        $this->actingAs($this->admin)->post(route('admin.questions.import-confirm-selected', $batch->uuid), [
            'item_ids' => [$item->id],
        ]);

        $this->assertEquals(1, Question::where('question_text', 'What is AWS S3?')->count());
        $saved = Question::where('question_text', 'What is AWS S3?')->first();
        $this->assertEquals('draft', $saved->status);
        $this->assertFalse($saved->is_active);
    }
}
