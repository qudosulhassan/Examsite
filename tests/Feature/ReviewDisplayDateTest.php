<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Review;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewDisplayDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_display_date_is_always_within_the_last_15_days(): void
    {
        $vendor = Vendor::factory()->create();
        $exam = Exam::factory()->create(['vendor_id' => $vendor->id]);
        $user = User::factory()->create();
        $review = Review::create([
            'user_id' => $user->id, 'exam_id' => $exam->id, 'rating' => 5,
            'review_text' => 'Great resource.', 'is_approved' => true,
        ]);

        $this->assertTrue($review->display_date->lte(now()));
        $this->assertTrue($review->display_date->gte(now()->subDays(15)));
    }

    public function test_display_date_is_stable_and_does_not_mutate_created_at(): void
    {
        $vendor = Vendor::factory()->create();
        $exam = Exam::factory()->create(['vendor_id' => $vendor->id]);
        $user = User::factory()->create();
        $review = Review::create([
            'user_id' => $user->id, 'exam_id' => $exam->id, 'rating' => 4,
            'review_text' => 'Solid.', 'is_approved' => true,
        ]);

        $first = $review->display_date->format('Y-m-d');
        $second = $review->display_date->format('Y-m-d');
        $this->assertEquals($first, $second);
        $this->assertTrue($review->created_at->isToday());
    }
}
