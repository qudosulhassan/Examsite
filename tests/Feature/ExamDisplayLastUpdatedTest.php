<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamDisplayLastUpdatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_last_updated_is_always_within_the_last_two_weeks(): void
    {
        $vendor = Vendor::factory()->create();
        $exam = Exam::factory()->create(['vendor_id' => $vendor->id, 'exam_code' => 'AZ-900']);

        $display = $exam->display_last_updated;

        $this->assertTrue($display->lte(now()));
        $this->assertTrue($display->gte(now()->subDays(14)));
    }

    public function test_display_last_updated_is_stable_across_calls(): void
    {
        $vendor = Vendor::factory()->create();
        $exam = Exam::factory()->create(['vendor_id' => $vendor->id, 'exam_code' => 'SC-200']);

        $first = $exam->display_last_updated->format('Y-m-d');
        $second = $exam->display_last_updated->format('Y-m-d');

        $this->assertEquals($first, $second);
    }

    public function test_display_last_updated_does_not_mutate_the_real_column(): void
    {
        $vendor = Vendor::factory()->create();
        $exam = Exam::factory()->create([
            'vendor_id' => $vendor->id,
            'exam_code' => 'DP-900',
            'last_updated_at' => now()->subYear(),
        ]);

        $exam->display_last_updated; // access the cosmetic accessor

        $this->assertTrue($exam->last_updated_at->lt(now()->subMonths(11)));
    }

    public function test_home_page_shows_current_month_badge(): void
    {
        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee(now()->format('F Y') . ' Verified Updates');
    }
}
