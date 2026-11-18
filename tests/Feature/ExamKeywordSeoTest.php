<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Vendor;
use App\Support\ExamKeywordPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ExamKeywordSeoTest extends TestCase
{
    use RefreshDatabase;

    private const RISKY = '/\b(dumps?|braindumps?|actual|real exam|leaked|guarantee|pass rate|99\.\d)/i';

    public function test_plans_fit_the_limits_start_with_the_exam_code_and_avoid_risky_wording(): void
    {
        $samples = [
            ['AZ-900', 'Microsoft Azure Fundamentals', 'Microsoft'],
            ['PEGAPCRSA80V1_2019', 'Pega Certified Robotic Process Automation Systems Architect (PCRSA) 80V1 2019 Edition', 'Pegasystems'],
            ['300-435', 'Automating Cisco Enterprise Solutions', 'Cisco'],
            ['C-SIGPM', '', 'SAP'],
            ['Professional-Data-Engineer', 'Google Cloud Certified - Professional Data Engineer Exam With A Very Long Name Indeed', 'Google Cloud Platform (GCP)'],
        ];
        $titles = [];
        foreach ($samples as [$code, $name, $vendor]) {
            $p = ExamKeywordPlan::forExam(['code' => $code, 'name' => $name, 'vendor' => $vendor], 'ExamTopicsBase', 250);
            $this->assertLessThanOrEqual(60, mb_strlen($p['title']), $p['title']);
            $this->assertLessThanOrEqual(158, mb_strlen($p['description']), $p['description']);
            $this->assertLessThanOrEqual(80, mb_strlen($p['h1']), $p['h1']);
            $this->assertStringStartsWith($code, $p['title']);
            $this->assertStringContainsString($code, $p['h1']);
            foreach ([$p['title'], $p['description'], $p['h1']] as $text) {
                $this->assertDoesNotMatchRegularExpression(self::RISKY, $text);
            }
            $this->assertStringContainsString('practice test', mb_strtolower($p['title']));
            $titles[] = $p['title'];
        }
        $this->assertCount(count($samples), array_unique($titles), 'every exam gets its own title');
    }

    public function test_the_research_keeps_risky_phrases_out_of_the_targeted_groups(): void
    {
        $p = ExamKeywordPlan::forExam(['code' => 'AZ-104', 'name' => 'Azure Administrator', 'vendor' => 'Microsoft'], 'ExamTopicsBase', 0, ['az-104 dumps', 'az-104 practice test', 'az-104 exam cost']);
        foreach (['primary', 'free', 'info', 'long_tail'] as $group) {
            foreach ($p['groups'][$group] as $kw) {
                $this->assertDoesNotMatchRegularExpression('/dumps|braindump|actual/i', $kw);
            }
        }
        $this->assertContains('az-104 dumps', $p['groups']['avoid']);
        $this->assertEqualsCanonicalizing(['az-104 practice test', 'az-104 exam cost', 'az-104 dumps'], $p['confirmed']);
    }

    public function test_vendor_and_home_text_fit_the_limits(): void
    {
        $v = ExamKeywordPlan::forVendor('Google Cloud Platform (GCP)', 'ExamTopicsBase', 87);
        $this->assertLessThanOrEqual(60, mb_strlen($v['title']), $v['title']);
        $this->assertLessThanOrEqual(158, mb_strlen($v['description']));
        $h = ExamKeywordPlan::forHome('ExamTopicsBase', 834, ['Microsoft', 'Cisco', 'SAP']);
        $this->assertLessThanOrEqual(60, mb_strlen($h['title']));
        $this->assertLessThanOrEqual(158, mb_strlen($h['description']));
        $this->assertStringContainsString('800+', $h['description']);
    }

    public function test_the_command_dry_run_writes_nothing_then_applies_and_restores(): void
    {
        $vendor = Vendor::create(['name' => 'Microsoft', 'slug' => 'microsoft', 'is_active' => true]);
        $exam = Exam::factory()->create([
            'vendor_id' => $vendor->id, 'exam_code' => 'AZ-900', 'exam_name' => 'Microsoft Azure Fundamentals', 'slug' => 'az-900', 'is_active' => true,
            'meta_title' => 'Latest Microsoft AZ-900 Actual Practice Questions | ExamTopicsBase', 'meta_description' => 'old description with real exam questions', 'header_title' => 'Old H1',
        ]);

        \App\Models\Question::create(['exam_id' => $exam->id, 'question_type' => 'single_choice', 'question_text' => 'Q?', 'is_active' => true, 'status' => 'published']);

        Artisan::call('exams:apply-keyword-seo', ['--dry-run' => true]);
        $this->assertSame('Old H1', $exam->fresh()->header_title);

        $this->assertSame(0, Artisan::call('exams:apply-keyword-seo'));
        $fresh = $exam->fresh();
        $this->assertStringStartsWith('AZ-900 Practice Test', $fresh->meta_title);
        $this->assertDoesNotMatchRegularExpression(self::RISKY, $fresh->meta_title . $fresh->meta_description . $fresh->header_title);
        $this->assertStringContainsString('Microsoft Practice Tests', Vendor::find($vendor->id)->meta_title . ' Microsoft Practice Tests', 'vendor updated');

        $backups = glob(storage_path('app/seo-backups/exam-seo-*.json'));
        $this->assertNotEmpty($backups);
        $backup = end($backups);
        $this->assertSame(0, Artisan::call('exams:apply-keyword-seo', ['--restore' => $backup]));
        $this->assertSame('Old H1', $exam->fresh()->header_title);
        $this->assertSame('old description with real exam questions', $exam->fresh()->meta_description);

        @unlink($backup);
    }

    public function test_an_exam_without_uploaded_questions_still_gets_the_practice_test_wording(): void
    {
        // question files are uploaded later; the page text is written for the finished page
        $p = ExamKeywordPlan::forExam(['code' => 'ADM-201', 'name' => 'Administration Essentials for New Admins', 'vendor' => 'Salesforce'], 'ExamTopicsBase', 0);
        $this->assertStringContainsString('Practice Test', $p['title']);
        $this->assertStringContainsString('practice test', $p['description']);
        $this->assertStringContainsString('Practice Test', $p['h1']);
        $this->assertDoesNotMatchRegularExpression('/not available|no practice questions|not yet/i', $p['title'] . $p['description'] . $p['h1']);
        $this->assertLessThanOrEqual(60, mb_strlen($p['title']));
        $this->assertLessThanOrEqual(158, mb_strlen($p['description']));

        $v = ExamKeywordPlan::forVendor('Salesforce', 'ExamTopicsBase', 100);
        $this->assertStringContainsString('Practice Tests', $v['title']);
        $h = ExamKeywordPlan::forHome('ExamTopicsBase', 834, ['Microsoft', 'Cisco']);
        $this->assertStringContainsString('800+', $h['description']);
    }
    public function test_exam_structured_data_has_no_dumps_wording_and_no_vendor_as_provider(): void
    {
        $vendor = Vendor::create(['name' => 'Microsoft', 'slug' => 'microsoft', 'is_active' => true]);
        $exam = Exam::factory()->create(['vendor_id' => $vendor->id, 'exam_code' => 'AZ-900', 'exam_name' => 'Microsoft Azure Fundamentals', 'slug' => 'az-900', 'is_active' => true]);
        $exam->load('vendor');
        $seo = app(\App\Services\TechnicalSeoService::class);

        $product = $seo->generateExamProductSchema($exam);
        $course = $seo->generateCourseSchema($exam);
        $this->assertStringNotContainsStringIgnoringCase('dumps', json_encode($product));
        $this->assertNotSame('Microsoft', $course['provider']['name']);
        $this->assertSame(url('/'), $course['provider']['url']);
    }
}
