<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Vendor;
use App\Models\Exam;
use App\Models\User;
use App\Services\TechnicalSeoService;

class SeoHealthAuditTest extends TestCase
{
    use RefreshDatabase;

    protected TechnicalSeoService $seoService;
    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seoService = app(TechnicalSeoService::class);

        $this->vendor = Vendor::create([
            'name' => 'Microsoft',
            'slug' => 'microsoft',
            'is_active' => true,
        ]);
    }

    public function test_exam_with_custom_seo_title_resolves_and_is_not_missing()
    {
        $exam = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'SC-900',
            'exam_name' => 'Microsoft Security Fundamentals',
            'slug' => 'sc-900',
            'meta_title' => 'Custom SC-900 Study Material & Practice Tests',
            'meta_description' => 'Custom description with plenty of detail for SC-900 test preparation.',
            'is_active' => true,
        ]);

        $this->assertEquals('Custom SC-900 Study Material & Practice Tests', $this->seoService->resolveExamSeoTitle($exam));
        $this->assertEquals('Custom SC-900 Study Material & Practice Tests', $exam->resolved_seo_title);

        $missingTitles = $this->seoService->auditExamTitles();
        $flaggedIds = array_column($missingTitles, 'id');
        $this->assertNotContains($exam->id, $flaggedIds);
    }

    public function test_exam_with_generated_title_resolves_and_is_not_flagged_missing()
    {
        $exam = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'AZ-104',
            'exam_name' => 'Microsoft Azure Administrator',
            'slug' => 'az-104',
            'meta_title' => null,
            'meta_description' => null,
            'is_active' => true,
        ]);

        $expectedTitle = 'AZ-104 Exam Dumps & Study Guide | Exam Topics Base';
        $this->assertEquals($expectedTitle, $this->seoService->resolveExamSeoTitle($exam));
        $this->assertEquals($expectedTitle, $exam->resolved_seo_title);

        $missingTitles = $this->seoService->auditExamTitles();
        $flaggedIds = array_column($missingTitles, 'id');
        $this->assertNotContains($exam->id, $flaggedIds);
    }

    public function test_exam_with_empty_code_and_meta_title_is_flagged_as_missing()
    {
        $exam = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => '',
            'exam_name' => '',
            'slug' => 'empty-exam',
            'meta_title' => '',
            'meta_description' => '',
            'is_active' => true,
        ]);

        $missingTitles = $this->seoService->auditExamTitles();
        $flaggedIds = array_column($missingTitles, 'id');
        $this->assertContains($exam->id, $flaggedIds);

        $missingDesc = $this->seoService->auditExamDescriptions();
        $flaggedDescIds = array_column($missingDesc, 'id');
        $this->assertContains($exam->id, $flaggedDescIds);
    }

    public function test_duplicate_titles_detects_identical_rendered_titles_not_just_exam_name()
    {
        $exam1 = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'AZ-103',
            'exam_name' => 'Microsoft Azure Administrator',
            'slug' => 'az-103',
            'meta_title' => null,
            'is_active' => true,
        ]);

        $exam2 = Exam::create([
            'vendor_id' => $this->vendor->id,
            'exam_code' => 'AZ-104',
            'exam_name' => 'Microsoft Azure Administrator',
            'slug' => 'az-104',
            'meta_title' => null,
            'is_active' => true,
        ]);

        $this->assertNotEquals($this->seoService->resolveExamSeoTitle($exam1), $this->seoService->resolveExamSeoTitle($exam2));
        $clusters = $this->seoService->auditDuplicateTitles();
        $this->assertEmpty($clusters);

        $exam1->update(['meta_title' => 'Microsoft Azure Administrator Complete Study Guide']);
        $exam2->update(['meta_title' => 'Microsoft Azure Administrator Complete Study Guide']);

        $clustersWithDup = $this->seoService->auditDuplicateTitles();
        $this->assertCount(1, $clustersWithDup);
        $this->assertEquals(2, $clustersWithDup[0]['count']);
        $this->assertEquals('Microsoft Azure Administrator Complete Study Guide', $clustersWithDup[0]['title']);
    }

    public function test_admin_exams_filtering_by_seo_issue()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.exams.index', ['seo_issue' => 'missing_title']));
        $response->assertStatus(200);
        $response->assertSee('Missing SEO Titles');
        $response->assertSee('SEO Diagnostic Filter');

        $response = $this->actingAs($admin)->get(route('admin.exams.index', ['seo_issue' => 'missing_description']));
        $response->assertStatus(200);
        $response->assertSee('Missing Meta Descriptions');

        $response = $this->actingAs($admin)->get(route('admin.exams.index', ['seo_issue' => 'duplicate_title']));
        $response->assertStatus(200);
        $response->assertSee('Duplicate SEO Titles');
    }

    public function test_health_score_audit_counts_match_audit_methods_exactly()
    {
        $healthAudit = $this->seoService->runHealthAudit();

        $this->assertEquals(
            count($this->seoService->auditExamTitles()),
            $healthAudit['checks']['titles']['count']
        );

        $this->assertEquals(
            count($this->seoService->auditExamDescriptions()),
            $healthAudit['checks']['descriptions']['count']
        );

        $this->assertEquals(
            count($this->seoService->auditDuplicateTitles()),
            $healthAudit['checks']['duplicate_content']['count']
        );

        $this->assertEquals(route('admin.exams.index', ['seo_issue' => 'missing_title']), $healthAudit['checks']['titles']['action_url']);
        $this->assertEquals(route('admin.exams.index', ['seo_issue' => 'missing_description']), $healthAudit['checks']['descriptions']['action_url']);
        $this->assertEquals(route('admin.exams.index', ['seo_issue' => 'duplicate_title']), $healthAudit['checks']['duplicate_content']['action_url']);
    }
}
