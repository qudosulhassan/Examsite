<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Exam;

class ExamCustomHeaderTitleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Vendor $cisco;
    protected Vendor $microsoft;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@examsninja.com',
            'role' => 'admin',
        ]);

        $this->cisco = Vendor::create([
            'name' => 'Cisco',
            'slug' => 'cisco',
            'is_active' => true,
        ]);

        $this->microsoft = Vendor::create([
            'name' => 'Microsoft',
            'slug' => 'microsoft',
            'is_active' => true,
        ]);
    }

    public function test_public_exam_displays_custom_header_title_in_h1(): void
    {
        $exam = Exam::create([
            'vendor_id' => $this->microsoft->id,
            'exam_code' => 'AZ-900',
            'exam_name' => 'Microsoft Azure Fundamentals',
            'header_title' => 'Microsoft Azure Fundamentals Check Status Done Publish ho raha ha',
            'slug' => 'az-900',
            'price_pdf' => 29.00,
            'price_engine' => 39.00,
            'passing_score' => 70,
            'is_active' => true,
        ]);

        $response = $this->get("/exams/microsoft/{$exam->slug}");

        $response->assertStatus(200);
        $response->assertSee('Microsoft Azure Fundamentals Check Status Done Publish ho raha ha');
        $response->assertDontSee('Study Guide &amp; Practice Questions', false);
    }

    public function test_public_exam_falls_back_to_default_h1_when_header_title_is_null(): void
    {
        $exam = Exam::create([
            'vendor_id' => $this->microsoft->id,
            'exam_code' => 'AZ-900',
            'exam_name' => 'Microsoft Azure Fundamentals',
            'header_title' => null,
            'slug' => 'az-900',
            'price_pdf' => 29.00,
            'price_engine' => 39.00,
            'passing_score' => 70,
            'is_active' => true,
        ]);

        $response = $this->get("/exams/microsoft/{$exam->slug}");

        $response->assertStatus(200);
        $response->assertSee('Microsoft AZ-900');
        $response->assertSee('Study Guide &amp; Practice Questions', false);
    }

    public function test_public_exam_falls_back_to_default_h1_when_header_title_is_whitespace_only(): void
    {
        $exam = Exam::create([
            'vendor_id' => $this->microsoft->id,
            'exam_code' => 'AZ-900',
            'exam_name' => 'Microsoft Azure Fundamentals',
            'header_title' => '     ',
            'slug' => 'az-900',
            'price_pdf' => 29.00,
            'price_engine' => 39.00,
            'passing_score' => 70,
            'is_active' => true,
        ]);

        $response = $this->get("/exams/microsoft/{$exam->slug}");

        $response->assertStatus(200);
        $response->assertSee('Microsoft AZ-900');
        $response->assertSee('Study Guide &amp; Practice Questions', false);
    }

    public function test_custom_header_title_does_not_leak_across_exams(): void
    {
        $examCisco = Exam::create([
            'vendor_id' => $this->cisco->id,
            'exam_code' => '200-301',
            'exam_name' => 'Cisco Certified Network Associate',
            'header_title' => 'TEST H1 — CISCO UNIQUE TITLE',
            'slug' => '200-301',
            'price_pdf' => 29.00,
            'price_engine' => 39.00,
            'passing_score' => 70,
            'is_active' => true,
        ]);

        $examAzure = Exam::create([
            'vendor_id' => $this->microsoft->id,
            'exam_code' => 'AZ-900',
            'exam_name' => 'Microsoft Azure Fundamentals',
            'header_title' => 'TEST H1 — AZURE UNIQUE TITLE',
            'slug' => 'az-900',
            'price_pdf' => 29.00,
            'price_engine' => 39.00,
            'passing_score' => 70,
            'is_active' => true,
        ]);

        // Cisco page must only show Cisco title
        $responseCisco = $this->get("/exams/cisco/{$examCisco->slug}");
        $responseCisco->assertStatus(200);
        $responseCisco->assertSee('TEST H1 — CISCO UNIQUE TITLE');
        $responseCisco->assertDontSee('TEST H1 — AZURE UNIQUE TITLE');

        // Azure page must only show Azure title
        $responseAzure = $this->get("/exams/microsoft/{$examAzure->slug}");
        $responseAzure->assertStatus(200);
        $responseAzure->assertSee('TEST H1 — AZURE UNIQUE TITLE');
        $responseAzure->assertDontSee('TEST H1 — CISCO UNIQUE TITLE');
    }

    public function test_custom_header_title_escapes_html_and_prevents_xss(): void
    {
        $exam = Exam::create([
            'vendor_id' => $this->microsoft->id,
            'exam_code' => 'AZ-104',
            'exam_name' => 'Microsoft Azure Administrator',
            'header_title' => '<script>alert("xss")</script>Azure Admin Title',
            'slug' => 'az-104',
            'price_pdf' => 29.00,
            'price_engine' => 39.00,
            'passing_score' => 70,
            'is_active' => true,
        ]);

        $response = $this->get("/exams/microsoft/{$exam->slug}");

        $response->assertStatus(200);
        $response->assertDontSee('<script>alert("xss")</script>', false);
        $response->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;Azure Admin Title', false);
    }

    public function test_admin_update_persists_custom_header_title(): void
    {
        $exam = Exam::create([
            'vendor_id' => $this->cisco->id,
            'exam_code' => '200-301',
            'exam_name' => 'Cisco CCNA',
            'header_title' => null,
            'slug' => '200-301',
            'price_pdf' => 29.00,
            'price_engine' => 39.00,
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'is_active' => true,
        ]);

        $updatePayload = [
            'vendor_id' => $this->cisco->id,
            'exam_code' => '200-301',
            'exam_name' => 'Cisco CCNA',
            'header_title' => 'Custom CCNA Title 2026',
            'slug' => '200-301',
            'price_pdf' => 29.00,
            'price_engine' => 39.00,
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'availability_configured' => '1',
            'is_pdf_available' => '1',
            'is_engine_available' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->put(route('admin.exams.update', $exam->id), $updatePayload);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('exams', [
            'id' => $exam->id,
            'header_title' => 'Custom CCNA Title 2026',
        ]);
    }
}
