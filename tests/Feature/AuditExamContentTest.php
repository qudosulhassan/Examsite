<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AuditExamContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finds_and_can_drop_a_faq_that_contradicts_the_duration_table(): void
    {
        $vendor = Vendor::create(['name' => 'Microsoft', 'slug' => 'microsoft', 'is_active' => true]);
        $exam = Exam::factory()->create([
            'vendor_id' => $vendor->id, 'exam_code' => '62-193', 'exam_name' => 'Technology Literacy', 'slug' => '62-193', 'is_active' => true,
            'article_content' => '<table><tr><td>Exam Duration</td><td>50 Minutes</td></tr></table>',
            'faqs' => [
                ['question' => 'How much time is allotted?', 'answer' => 'Candidates receive 60 minutes.'],
                ['question' => 'Passing score?', 'answer' => '700 out of 1000.'],
            ],
        ]);

        Artisan::call('exams:audit-content');
        $this->assertCount(2, $exam->fresh()->faqs, 'listing changes nothing');

        Artisan::call('exams:audit-content', ['--drop-conflicting-faqs' => true]);
        $faqs = $exam->fresh()->faqs;
        $this->assertCount(1, $faqs);
        $this->assertSame('Passing score?', $faqs[0]['question']);

        $backups = glob(storage_path('app/seo-backups/exam-faqs-*.json'));
        $this->assertNotEmpty($backups);
        Artisan::call('exams:audit-content', ['--restore' => end($backups)]);
        $this->assertCount(2, $exam->fresh()->faqs);
        array_map('unlink', $backups);
    }
}
