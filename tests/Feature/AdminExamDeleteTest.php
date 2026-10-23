<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Question;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Deleting an exam from /admin/exams/{id}. Live regression: the "has orders?" check queried a
 * column that does not exist (order_items.item_id) so every delete ended in a 500.
 */
class AdminExamDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['email' => 'admin@examsninja.com', 'role' => 'admin']);
    }

    private function exam(): Exam
    {
        $vendor = Vendor::create(['name' => 'Cisco Systems', 'slug' => 'cisco', 'is_active' => true]);

        return Exam::factory()->create(['vendor_id' => $vendor->id, 'exam_code' => '300-435', 'slug' => '300-435', 'is_active' => true]);
    }

    public function test_an_exam_nobody_bought_is_deleted_with_its_questions(): void
    {
        $exam = $this->exam();
        Question::create(['exam_id' => $exam->id, 'question_type' => 'single_choice', 'question_text' => 'Q?', 'is_active' => true, 'status' => 'published']);

        $this->actingAs($this->admin())
            ->delete(route('admin.exams.destroy', $exam->id))
            ->assertRedirect(route('admin.exams.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('exams', ['id' => $exam->id]);
        $this->assertDatabaseMissing('questions', ['exam_id' => $exam->id]);
    }

    public function test_an_exam_that_appears_on_an_order_is_protected_not_a_500(): void
    {
        $exam = $this->exam();
        $buyer = User::factory()->create();
        $order = Order::create(['user_id' => $buyer->id, 'order_number' => 'ORD-1', 'subtotal' => 30, 'total_amount' => 30, 'payment_method' => 'stripe', 'payment_status' => 'paid', 'billing_name' => 'A', 'billing_email' => 'a@example.com']);
        OrderItem::create(['order_id' => $order->id, 'exam_id' => $exam->id, 'item_type' => 'pdf', 'price' => 30]);

        $this->actingAs($this->admin())
            ->delete(route('admin.exams.destroy', $exam->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('exams', ['id' => $exam->id]);
    }
}
