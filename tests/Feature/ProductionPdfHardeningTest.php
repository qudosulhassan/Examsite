<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionImportBatch;
use App\Models\QuestionImportItem;
use App\Models\User;
use App\Models\Vendor;
use App\Services\QuestionImport\Pdf\PdfOcrService;
use App\Services\QuestionImport\Pdf\PdfQualityClassifier;
use App\Services\QuestionImport\Pdf\PdfTextExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductionPdfHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $vendor = Vendor::create([
            'name' => 'Google Cloud',
            'slug' => 'gcp',
            'category' => 'Cloud',
            'is_active' => true,
        ]);

        $this->exam = Exam::create([
            'vendor_id' => $vendor->id,
            'exam_code' => 'GCP-PCA',
            'exam_name' => 'Professional Cloud Architect',
            'slug' => 'gcp-pca',
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
     * Test 1: Clean text-based PDF produces TEXT_BASED classification and diagnostics.
     */
    public function test_clean_text_pdf_diagnostics_and_classification(): void
    {
        $content = "Question 1:\nWhich Google Cloud service is designed for real-time stream messaging?\nA. Pub/Sub\nB. Cloud Storage\nC. BigQuery\nD. Cloud Run\nAnswer: A\nExplanation: Pub/Sub is an asynchronous messaging service.";

        $pdfBytes = $this->generateTestPdf([$content]);
        $file = UploadedFile::fake()->createWithContent('clean_gcp.pdf', $pdfBytes);

        $this->actingAs($this->admin)->post(route('admin.questions.import-pdf-upload'), [
            'pdf_file' => $file,
            'default_exam_id' => $this->exam->id,
        ]);

        $batch = QuestionImportBatch::first();
        $this->assertNotNull($batch);
        $this->assertArrayHasKey('pdf_diagnostics', $batch->options);

        $diag = $batch->options['pdf_diagnostics'];
        $this->assertEquals('TEXT_BASED', $diag['document_classification']);
        $this->assertEquals(1, $diag['page_count']);
        $this->assertEquals(1, $diag['native_text_pages']);
        $this->assertGreaterThanOrEqual(85, $diag['quality_score']);
        $this->assertEquals('EXCELLENT', $diag['quality_tier']);
    }

    /**
     * Test 2: Page-level extraction diagnostics.
     */
    public function test_page_level_diagnostics_recorded(): void
    {
        $page1 = "Question 1:\nWhat is BigQuery?\nA. Data Warehouse\nB. Compute\nAnswer: A\nExplanation: BigQuery is a serverless enterprise data warehouse.";
        $page2 = "Question 2:\nWhat is Cloud Spanner?\nA. Relational Database\nB. NoSQL\nAnswer: A\nExplanation: Cloud Spanner is globally scalable.";

        $pdfBytes = $this->generateTestPdf([$page1, $page2]);
        $file = UploadedFile::fake()->createWithContent('pages_diag.pdf', $pdfBytes);

        $this->actingAs($this->admin)->post(route('admin.questions.import-pdf-upload'), [
            'pdf_file' => $file,
            'default_exam_id' => $this->exam->id,
        ]);

        $batch = QuestionImportBatch::first();
        $this->assertArrayHasKey('page_diagnostics', $batch->options);
        $pageDiag = $batch->options['page_diagnostics'];

        $this->assertCount(2, $pageDiag);
        $this->assertEquals(1, $pageDiag[0]['page_number']);
        $this->assertEquals(2, $pageDiag[1]['page_number']);
        $this->assertEquals('native_text', $pageDiag[0]['extraction_method']);
        $this->assertEquals('success', $pageDiag[0]['extraction_status']);
    }

    /**
     * Test 3: Raw extraction data & debug mode payloads preserved.
     */
    public function test_raw_extraction_data_and_debug_payload_preserved(): void
    {
        $content = "Question 1:\nWhat is Google Kubernetes Engine (GKE)?\nA. Managed Kubernetes\nB. Serverless Functions\nC. Object Storage\nAnswer: A\nExplanation: GKE is a managed environment for running containerized apps.";

        $pdfBytes = $this->generateTestPdf([$content]);
        $file = UploadedFile::fake()->createWithContent('debug_test.pdf', $pdfBytes);

        $this->actingAs($this->admin)->post(route('admin.questions.import-pdf-upload'), [
            'pdf_file' => $file,
            'default_exam_id' => $this->exam->id,
        ]);

        $item = QuestionImportItem::first();
        $this->assertNotNull($item);
        $this->assertNotNull($item->raw_data);
        $this->assertArrayHasKey('debug_info', $item->raw_data);

        $debug = $item->raw_data['debug_info'];
        $this->assertTrue($debug['has_question_marker']);
        $this->assertStringContainsString('Answer: A', $debug['answer_pattern_matched']);
        $this->assertGreaterThanOrEqual(80, $debug['boundary_confidence']);
        $this->assertStringContainsString('What is Google Kubernetes Engine', $debug['raw_text_block']);
    }

    /**
     * Test 4: Multi-column layout detection and unfolding.
     */
    public function test_multi_column_layout_detection(): void
    {
        // Two column text line format: "Column 1 Text       Column 2 Text"
        $rawTwoColumnText = "Question 1: What is S3?    Question 2: What is EC2?\nA. Object Storage          A. Virtual Servers\nB. Block Storage           B. Serverless\nAnswer: A                  Answer: A\nExplanation: S3.           Explanation: EC2.";

        $layout = 'single_column';
        $unfolded = PdfTextExtractor::unfoldMultiColumnText($rawTwoColumnText, $layout);

        $this->assertEquals('multi_column', $layout);
        $this->assertStringContainsString('Question 1: What is S3?', $unfolded);
        $this->assertStringContainsString('Question 2: What is EC2?', $unfolded);
    }

    /**
     * Test 5: Suspicious garbled text recognition.
     */
    public function test_suspicious_garbled_text_detection(): void
    {
        $cleanText = "Which cloud service provides global DNS management?";
        $this->assertFalse(PdfOcrService::isSuspiciousGarbledText($cleanText));

        $garbledText = "□□□□□□ □□□ □□□ \xEF\xBF\xBD\xEF\xBF\xBD\xEF\xBF\xBD";
        $this->assertTrue(PdfOcrService::isSuspiciousGarbledText($garbledText));
    }

    /**
     * Test 6: Question boundary confidence calculation.
     */
    public function test_question_boundary_confidence_calculation(): void
    {
        $content = "Question 1:\nWhich database is NoSQL?\nA. Cloud Datastore\nB. Cloud SQL\nAnswer: A\nExplanation: Datastore is NoSQL.";

        $pdfBytes = $this->generateTestPdf([$content]);
        $file = UploadedFile::fake()->createWithContent('boundary.pdf', $pdfBytes);

        $this->actingAs($this->admin)->post(route('admin.questions.import-pdf-upload'), [
            'pdf_file' => $file,
            'default_exam_id' => $this->exam->id,
        ]);

        $item = QuestionImportItem::first();
        $sourceRef = $item->normalized_data['source_reference'];

        $this->assertArrayHasKey('question_boundary_confidence', $sourceRef);
        $this->assertGreaterThanOrEqual(80, $sourceRef['question_boundary_confidence']);
    }

    /**
     * Test 7: Batch extraction quality score tier calculations.
     */
    public function test_quality_score_calculation(): void
    {
        // High quality
        $resHigh = PdfQualityClassifier::calculateQualityScore(10, 20, 95.0, 1, 0, 0);
        $this->assertEquals('EXCELLENT', $resHigh['tier']);
        $this->assertGreaterThanOrEqual(90, $resHigh['score']);

        // Medium quality with warnings
        $resMed = PdfQualityClassifier::calculateQualityScore(10, 15, 78.0, 10, 2, 2);
        $this->assertEquals('GOOD', $resMed['tier']);

        // Poor quality with errors
        $resPoor = PdfQualityClassifier::calculateQualityScore(10, 5, 50.0, 15, 10, 5);
        $this->assertEquals('POOR', $resPoor['tier']);
    }

    /**
     * Test 8: Malformed UTF-8 characters are sanitized without JSON serialization errors.
     */
    public function test_malformed_utf8_payload_is_sanitized_and_saved(): void
    {
        // Corrupted multi-byte character sequence
        $corruptString = "Question 1:\nWhat is Azure Active Directory?\xA0\xB1\xC2\xFF\nA. Identity Provider\nB. Virtual Machine\nAnswer: A\nExplanation: Identity service.";

        $pdfBytes = $this->generateTestPdf([$corruptString]);
        $file = UploadedFile::fake()->createWithContent('utf8_corrupt.pdf', $pdfBytes);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.import-pdf-upload'), [
            'pdf_file' => $file,
            'default_exam_id' => $this->exam->id,
        ]);

        $batch = QuestionImportBatch::first();
        $this->assertNotNull($batch);
        $response->assertRedirect(route('admin.questions.import-review', $batch->uuid));

        $item = QuestionImportItem::first();
        $this->assertNotNull($item);
        $this->assertNotNull($item->raw_data);
        $this->assertNotNull($item->normalized_data);

        // Verify JSON encoding works cleanly without exception
        $json = json_encode($item->toArray());
        $this->assertNotFalse($json);
        $this->assertStringContainsString('Azure Active Directory', $json);
    }
}
