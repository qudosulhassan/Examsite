<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Vendor;
use App\Models\Exam;
use App\Models\Redirect;

class ExamUrlRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected Vendor $vendor;
    protected Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendor = Vendor::create([
            'name' => 'Microsoft',
            'slug' => 'microsoft',
            'is_active' => true,
        ]);

        $this->exam = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'AZ-104',
            'exam_name' => 'Microsoft Azure Administrator',
            'slug' => 'az-104',
            'price_pdf' => 29.99,
            'price_engine' => 39.99,
            'is_active' => true,
        ]);

        Redirect::create([
            'old_url' => 'exams/microsoft-az-104',
            'new_url' => 'exams/microsoft/az-104',
            'status_code' => 301,
        ]);
    }

    public function test_exam_nested_url_renders_successfully()
    {
        // Direct nested URL: /exams/microsoft/az-104
        $response = $this->get('/exams/microsoft/az-104');
        $response->assertStatus(200);
        $response->assertSee('AZ-104');
    }

    public function test_legacy_vendor_hyphenated_url_redirects_to_nested_url()
    {
        // /exams/microsoft-az-104 should 301 redirect to /exams/microsoft/az-104
        $response = $this->get('/exams/microsoft-az-104');
        $response->assertStatus(301);
        $response->assertRedirect('/exams/microsoft/az-104');
    }

    public function test_legacy_short_code_url_redirects_to_nested_url()
    {
        // /exams/az-104 should 301 redirect to /exams/microsoft/az-104
        $response = $this->get('/exams/az-104');
        $response->assertStatus(301);
        $response->assertRedirect('/exams/microsoft/az-104');
    }

    public function test_exam_model_url_attribute()
    {
        $this->assertStringContainsString('/exams/microsoft/az-104', $this->exam->url);
    }
}
