<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Exam;
use App\Models\Certification;
use App\Models\Question;
use App\Models\AuditLog;

class AdminExamWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Vendor $vendor;
    protected Certification $cert;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@examsninja.com',
            'role' => 'admin',
        ]);

        $this->vendor = Vendor::create([
            'name' => 'Cisco Systems',
            'slug' => 'cisco',
            'is_active' => true,
        ]);

        $this->cert = Certification::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'CCNA Routing and Switching',
            'slug' => 'ccna-routing-and-switching',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_exam_create_workspace(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.exams.create'));

        $response->assertStatus(200);
        $response->assertSee('Create New Exam');
        $response->assertSee('Basic Information');
        $response->assertSee('Pricing & Commercial Settings', false);
        $response->assertSee('Exam Syllabus Topics');
        $response->assertSee('Publishing Console');
        $response->assertSee('Cisco Systems');
    }

    public function test_admin_can_view_exam_edit_workspace_with_details(): void
    {
        $exam = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => '200-301',
            'exam_name' => 'Cisco Certified Network Associate',
            'header_title' => 'Cisco 200-301 CCNA Guide',
            'slug' => '200-301',
            'price_pdf' => 29.00,
            'price_engine' => 39.00,
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'topics' => ['Network Fundamentals', 'IP Connectivity'],
            'is_active' => true,
        ]);

        // Add 2 questions to check real question count
        Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'What is the default administrative distance of OSPF?',
            'question_type' => 'single_choice',
            'options' => ['110', '90', '120', '100'],
            'correct_answer' => '110',
            'is_active' => true,
        ]);

        Question::create([
            'exam_id' => $exam->id,
            'question_text' => 'Which protocol provides secure terminal access?',
            'question_type' => 'single_choice',
            'options' => ['SSH', 'Telnet', 'HTTP', 'FTP'],
            'correct_answer' => 'SSH',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.exams.edit', $exam->id));

        $response->assertStatus(200);
        $response->assertSee('200-301');
        $response->assertSee('Cisco Certified Network Associate');
        $response->assertSee('Cisco 200-301 CCNA Guide');
        $response->assertSee('Network Fundamentals');
        $response->assertSee('IP Connectivity');
        $response->assertSee('Database Questions');
    }

    public function test_admin_can_create_exam_as_published_and_audit_logged(): void
    {
        Storage::fake('public');
        Storage::fake('r2');

        $pdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF";
        $demoFile = UploadedFile::fake()->createWithContent('cisco-demo.pdf', $pdfContent);

        $response = $this->actingAs($this->adminUser)->post(route('admin.exams.store'), [
            'vendor_id' => $this->vendor->id,
            'exam_code' => '350-401',
            'exam_name' => 'Implementing Cisco Enterprise Network Core Technologies',
            'header_title' => 'Cisco ENCOR 350-401 Certification',
            'price_pdf' => 49.00,
            'price_engine' => 59.00,
            'update_price_3_months' => 0.00,
            'update_price_6_months' => 15.00,
            'update_price_12_months' => 25.00,
            'passing_score' => 75,
            'difficulty' => 'Professional',
            'exam_type' => 'MultipleChoice',
            'topics' => ['Architecture', 'Virtualization', 'Infrastructure'],
            'description' => '<p>Comprehensive study material for 350-401.</p>',
            'demo_pdf' => $demoFile,
            'is_active' => '1',
            'action' => 'publish',
            'is_featured' => '1',
            'meta_title' => 'Cisco 350-401 ENCOR Practice Exam Questions',
            'meta_description' => 'Pass Cisco 350-401 with updated questions and explanations.',
        ]);

        $response->assertSessionHasNoErrors();

        $exam = Exam::where('exam_code', '350-401')->first();
        $this->assertNotNull($exam);
        $this->assertTrue($exam->is_active);
        $this->assertTrue($exam->is_featured);
        $this->assertEquals('Cisco ENCOR 350-401 Certification', $exam->header_title);
        $this->assertEquals(75, $exam->passing_score);
        $this->assertCount(3, $exam->topics);
        $this->assertNotNull($exam->demo_pdf_filename);

        $response->assertRedirect(route('admin.exams.edit', $exam->id));

        // Verify audit log
        $this->assertDatabaseHas('user_audit_logs', [
            'action' => 'exam_created',
        ]);
    }

    public function test_admin_can_update_exam_and_remove_pdf(): void
    {
        Storage::fake('public');

        // Create a dummy file in storage
        Storage::disk('public')->put('demos/test-demo.pdf', 'dummy content');

        $exam = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => '200-301',
            'exam_name' => 'CCNA Exam',
            'slug' => '200-301',
            'price_pdf' => 29.00,
            'price_engine' => 39.00,
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'demo_pdf_filename' => 'test-demo.pdf',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->put(route('admin.exams.update', $exam->id), [
            'vendor_id' => $this->vendor->id,
            'exam_code' => '200-301',
            'exam_name' => 'CCNA Routing & Switching Updated',
            'header_title' => 'Updated Header Title',
            'price_pdf' => 34.00,
            'price_engine' => 44.00,
            'passing_score' => 75,
            'difficulty' => 'Professional',
            'exam_type' => 'MultiSelect',
            'remove_demo_pdf' => '1',
            'is_active' => '1',
            'action' => 'publish',
        ]);

        $response->assertRedirect(route('admin.exams.edit', $exam->id));

        $exam->refresh();
        $this->assertEquals('CCNA Routing & Switching Updated', $exam->exam_name);
        $this->assertEquals(34.00, (float)$exam->price_pdf);
        $this->assertEquals(75, $exam->passing_score);
        $this->assertNull($exam->demo_pdf_filename);

        // Verify audit log
        $this->assertDatabaseHas('user_audit_logs', [
            'action' => 'exam_updated',
        ]);
    }
}