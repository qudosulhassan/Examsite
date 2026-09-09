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

    public function test_legacy_vendor_hyphenated_url_redirects_only_if_explicit_redirect_configured()
    {
        // /exams/microsoft-az-104 has explicit redirect configured in setUp
        $response = $this->get('/exams/microsoft-az-104');
        $response->assertStatus(301);
        $response->assertRedirect('/exams/microsoft/az-104');
    }

    public function test_unmapped_legacy_urls_and_nonexistent_exams_return_404()
    {
        // /exams/az-104 without redirect rule returns 404
        $response = $this->get('/exams/az-104');
        $response->assertStatus(404);

        // /exams/microsoft-sc-900 without redirect rule returns 404
        $response = $this->get('/exams/microsoft-sc-900');
        $response->assertStatus(404);

        // Non-existent exam under valid vendor returns 404
        $response = $this->get('/exams/microsoft/nonexistent-code');
        $response->assertStatus(404);

        // Non-existent vendor returns 404
        $response = $this->get('/exams/fake-vendor/az-104');
        $response->assertStatus(404);

        // Verify no ghost exams or redirects were auto-created
        $this->assertDatabaseMissing('exams', ['slug' => 'nonexistent-code']);
        $this->assertDatabaseMissing('redirects', ['old_url' => 'exams/microsoft/nonexistent-code']);
    }

    public function test_exam_model_url_attribute()
    {
        $this->assertStringContainsString('/exams/microsoft/az-104', $this->exam->url);
    }

    public function test_creating_exam_generates_nested_url_and_opens_with_200_ok()
    {
        $adminUser = \App\Models\User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($adminUser)->post(route('admin.exams.store'), [
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'SC-900',
            'exam_name' => 'Microsoft Security Fundamentals',
            'price_pdf' => 29.00,
            'price_engine' => 39.00,
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'is_active' => '1',
            'action' => 'publish',
        ]);

        $response->assertSessionHasNoErrors();

        $newExam = Exam::where('exam_code', 'SC-900')->first();
        $this->assertNotNull($newExam);
        $this->assertEquals('sc-900', $newExam->slug);
        $this->assertEquals(url('/exams/microsoft/sc-900'), $newExam->url);

        // Confirm public URL opens with 200 OK directly, without hitting 301 or 404
        $publicResponse = $this->get('/exams/microsoft/sc-900');
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('SC-900');

        // Confirm no unnecessary redirect rule was created for this newly created exam
        $this->assertDatabaseMissing('redirects', [
            'old_url' => 'exams/microsoft/sc-900',
        ]);
    }

    public function test_exam_slug_strips_duplicate_vendor_prefix()
    {
        $adminUser = \App\Models\User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($adminUser)->post(route('admin.exams.store'), [
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'MS-900',
            'exam_name' => 'Microsoft 365 Fundamentals',
            'slug' => 'microsoft-ms-900', // Admin inadvertently enters vendor prefix
            'price_pdf' => 29.00,
            'price_engine' => 39.00,
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'is_active' => '1',
            'action' => 'publish',
        ]);

        $response->assertSessionHasNoErrors();

        $exam = Exam::where('exam_code', 'MS-900')->first();
        $this->assertNotNull($exam);
        $this->assertEquals('ms-900', $exam->slug);
        $this->assertEquals(url('/exams/microsoft/ms-900'), $exam->url);

        $publicResponse = $this->get('/exams/microsoft/ms-900');
        $publicResponse->assertStatus(200);
    }

    public function test_admin_cannot_create_redirect_to_nonexistent_exam()
    {
        $adminUser = \App\Models\User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($adminUser)->post(route('admin.seo.redirects.store'), [
            'old_url' => 'exams/old-fake-exam',
            'new_url' => 'exams/microsoft/non-existent-exam',
            'status_code' => 301,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('redirects', [
            'old_url' => 'exams/old-fake-exam',
        ]);
    }
}
