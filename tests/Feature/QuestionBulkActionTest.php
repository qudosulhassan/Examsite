<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionBulkActionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $vendor = \App\Models\Vendor::create(['name' => 'Microsoft', 'slug' => 'microsoft']);
        $this->exam = Exam::create([
            'vendor_id' => $vendor->id,
            'exam_code' => 'AZ-303',
            'exam_name' => 'Microsoft Azure Architect Technologies',
            'slug' => 'az-303',
            'price' => 49.99,
            'question_count' => 0,
        ]);
    }

    public function test_bulk_delete_selected_questions(): void
    {
        $q1 = Question::create(['exam_id' => $this->exam->id, 'question_text' => 'Q1', 'is_active' => true]);
        $q2 = Question::create(['exam_id' => $this->exam->id, 'question_text' => 'Q2', 'is_active' => true]);
        $q3 = Question::create(['exam_id' => $this->exam->id, 'question_text' => 'Q3', 'is_active' => true]);

        $this->assertEquals(3, Question::count());

        $response = $this->actingAs($this->admin)->post(route('admin.questions.bulk-action'), [
            'action' => 'delete',
            'question_ids' => [$q1->id, $q2->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(1, Question::count());
        $this->assertDatabaseMissing('questions', ['id' => $q1->id]);
        $this->assertDatabaseMissing('questions', ['id' => $q2->id]);
        $this->assertDatabaseHas('questions', ['id' => $q3->id]);

        $this->exam->refresh();
        $this->assertEquals(1, $this->exam->question_count);
    }

    public function test_bulk_delete_all_questions_for_an_exam(): void
    {
        Question::create(['exam_id' => $this->exam->id, 'question_text' => 'Q1']);
        Question::create(['exam_id' => $this->exam->id, 'question_text' => 'Q2']);
        Question::create(['exam_id' => $this->exam->id, 'question_text' => 'Q3']);

        $this->assertEquals(3, Question::where('exam_id', $this->exam->id)->count());

        $response = $this->actingAs($this->admin)->post(route('admin.questions.bulk-action'), [
            'action' => 'delete_all_exam',
            'exam_id' => $this->exam->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(0, Question::where('exam_id', $this->exam->id)->count());
        $this->exam->refresh();
        $this->assertEquals(0, $this->exam->question_count);
    }

    public function test_bulk_activate_and_deactivate(): void
    {
        $q1 = Question::create(['exam_id' => $this->exam->id, 'question_text' => 'Q1', 'is_active' => false]);
        $q2 = Question::create(['exam_id' => $this->exam->id, 'question_text' => 'Q2', 'is_active' => false]);

        // Activate
        $response = $this->actingAs($this->admin)->post(route('admin.questions.bulk-action'), [
            'action' => 'activate',
            'question_ids' => [$q1->id, $q2->id],
        ]);
        $response->assertRedirect();
        $this->assertTrue($q1->fresh()->is_active);
        $this->assertTrue($q2->fresh()->is_active);

        // Deactivate
        $response = $this->actingAs($this->admin)->post(route('admin.questions.bulk-action'), [
            'action' => 'deactivate',
            'question_ids' => [$q1->id],
        ]);
        $response->assertRedirect();
        $this->assertFalse($q1->fresh()->is_active);
        $this->assertTrue($q2->fresh()->is_active);
    }
}
