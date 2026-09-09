<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExamArticleContentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->vendor = Vendor::create([
            'name' => 'CompTIA',
            'slug' => 'comptia',
            'is_active' => true,
        ]);
    }

    public function test_can_create_exam_with_rich_article_content(): void
    {
        $richHtml = '<h2>Complete Exam Study Guide</h2>'
                  . '<p>Here is an in-depth breakdown of the <strong>Security+</strong> certification domains.</p>'
                  . '<table class="tiptap-table"><thead><tr><th>Domain</th><th>Weight</th></tr></thead><tbody><tr><td>Threats</td><td>24%</td></tr></tbody></table>';

        $response = $this->actingAs($this->admin)->post(route('admin.exams.store'), [
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'SY0-701',
            'exam_name' => 'CompTIA Security+',
            'passing_score' => 75,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'price_pdf' => 29.99,
            'price_engine' => 39.99,
            'is_pdf_available' => true,
            'is_engine_available' => true,
            'is_bundle_available' => false,
            'description' => 'Official overview description.',
            'article_content' => $richHtml,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.exams.edit', 1));

        $exam = Exam::where('exam_code', 'SY0-701')->first();
        $this->assertNotNull($exam);
        $this->assertStringContainsString('<h2>Complete Exam Study Guide</h2>', $exam->article_content);
        $this->assertStringContainsString('<strong>Security+</strong>', $exam->article_content);
        $this->assertStringContainsString('class="tiptap-table"', $exam->article_content);
    }

    public function test_article_content_is_sanitized_against_xss(): void
    {
        $maliciousHtml = '<h3>Security Guide</h3>'
                       . '<script>alert("XSS Attack!");</script>'
                       . '<p><a href="javascript:alert(1)">Click here</a></p>'
                       . '<img src="x" onerror="alert(2)">'
                       . '<p>Legitimate paragraph with <b>bold text</b>.</p>';

        $response = $this->actingAs($this->admin)->post(route('admin.exams.store'), [
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'XSS-001',
            'exam_name' => 'XSS Test Exam',
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'price_pdf' => 19.99,
            'price_engine' => 29.99,
            'is_pdf_available' => true,
            'is_engine_available' => true,
            'is_bundle_available' => false,
            'article_content' => $maliciousHtml,
        ]);

        $exam = Exam::where('exam_code', 'XSS-001')->first();
        $this->assertNotNull($exam);
        $this->assertStringNotContainsString('<script', $exam->article_content);
        $this->assertStringNotContainsString('alert("XSS Attack!")', $exam->article_content);
        $this->assertStringNotContainsString('javascript:alert(1)', $exam->article_content);
        $this->assertStringNotContainsString('onerror=', $exam->article_content);
        $this->assertStringContainsString('<h3>Security Guide</h3>', $exam->article_content);
        $this->assertStringContainsString('<b>bold text</b>', $exam->article_content);
    }

    public function test_can_update_exam_article_content(): void
    {
        $exam = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'N10-008',
            'exam_name' => 'CompTIA Network+',
            'slug' => 'n10-008',
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'price_pdf' => 29.99,
            'price_engine' => 39.99,
            'is_pdf_available' => true,
            'is_engine_available' => true,
            'is_bundle_available' => false,
            'is_active' => true,
            'article_content' => '<p>Initial article draft.</p>',
        ]);

        $updatedHtml = '<h2>Updated Network+ Blueprint</h2><p>Revised curriculum for 2026.</p>';

        $response = $this->actingAs($this->admin)->put(route('admin.exams.update', $exam->id), [
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'N10-008',
            'exam_name' => 'CompTIA Network+',
            'slug' => 'n10-008',
            'passing_score' => 72,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'price_pdf' => 29.99,
            'price_engine' => 39.99,
            'is_pdf_available' => true,
            'is_engine_available' => true,
            'is_bundle_available' => false,
            'article_content' => $updatedHtml,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.exams.edit', $exam->id));

        $exam->refresh();
        $this->assertEquals($updatedHtml, $exam->article_content);
    }

    public function test_public_exam_page_displays_rendered_article_content(): void
    {
        $exam = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'CAS-004',
            'exam_name' => 'CompTIA SecurityX',
            'slug' => 'cas-004',
            'passing_score' => 75,
            'difficulty' => 'Expert',
            'exam_type' => 'MultipleChoice',
            'price_pdf' => 49.99,
            'price_engine' => 59.99,
            'is_pdf_available' => true,
            'is_engine_available' => true,
            'is_bundle_available' => false,
            'is_active' => true,
            'article_content' => '<h2>Mastering Security Architecture</h2><p>Comprehensive guide to enterprise defense.</p>',
        ]);

        $response = $this->get('/exams/comptia/cas-004');
        $response->assertStatus(200);
        $response->assertSee('<h2>Mastering Security Architecture</h2>', false);
        $response->assertSee('Comprehensive guide to enterprise defense.', false);
        $response->assertSee('blog-content-body', false);
    }

    public function test_public_exam_page_renders_cleanly_without_article_content(): void
    {
        $exam = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'SK0-005',
            'exam_name' => 'CompTIA Server+',
            'slug' => 'sk0-005',
            'passing_score' => 70,
            'difficulty' => 'Associate',
            'exam_type' => 'MultipleChoice',
            'price_pdf' => 29.99,
            'price_engine' => 39.99,
            'is_pdf_available' => true,
            'is_engine_available' => true,
            'is_bundle_available' => false,
            'is_active' => true,
            'description' => 'Standard overview only.',
            'article_content' => null,
        ]);

        $response = $this->get('/exams/comptia/sk0-005');
        $response->assertStatus(200);
        $response->assertSee('Standard overview only.');
    }
}
