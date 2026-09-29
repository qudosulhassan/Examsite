<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\User;
use App\Models\Vendor;
use App\Models\UserExam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfSecureDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_pdf_upload_goes_to_private_disk_not_public(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vendor = Vendor::factory()->create();
        $exam = Exam::factory()->create(['vendor_id' => $vendor->id, 'exam_code' => 'PDF-TEST-100']);

        $fullPdf = UploadedFile::fake()->create('study-guide.pdf', 500, 'application/pdf');

        $this->actingAs($admin)->put(route('admin.exams.update', $exam->id), array_merge(
            $exam->only(['vendor_id', 'exam_code', 'exam_name', 'passing_score', 'difficulty', 'exam_type']),
            ['full_pdf' => $fullPdf, 'is_pdf_available' => 1, 'is_engine_available' => 1]
        ));

        $exam->refresh();
        $this->assertNotNull($exam->full_pdf_filename);
        $path = 'full/' . $exam->full_pdf_filename;

        $this->assertTrue(Storage::disk('local')->exists($path));
        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_customer_download_flow_and_limit_enforcement(): void
    {
        $vendor = Vendor::factory()->create();
        $exam = Exam::factory()->create(['vendor_id' => $vendor->id, 'exam_code' => 'PDF-TEST-200']);
        $exam->update(['full_pdf_filename' => 'pdf-test-200-full.pdf']);
        Storage::disk('local')->put('full/pdf-test-200-full.pdf', '%PDF-1.4 fake content for test');

        $customer = User::factory()->create(['role' => 'student']);
        $userExam = UserExam::create([
            'user_id' => $customer->id,
            'exam_id' => $exam->id,
            'access_type' => 'pdf',
            'download_count' => 0,
            'max_downloads' => 3,
            'purchased_at' => now(),
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $this->actingAs($customer)->get(route('dashboard.my-exams.download', $userExam->id))->assertOk();
            $userExam->refresh();
            $this->assertEquals($i, $userExam->download_count);
        }

        $blocked = $this->actingAs($customer)->get(route('dashboard.my-exams.download', $userExam->id));
        $blocked->assertRedirect();
        $blocked->assertSessionHas('error');

        // A different user cannot use this UserExam id to download someone else's purchase.
        $otherUser = User::factory()->create(['role' => 'student']);
        $this->actingAs($otherUser)->get(route('dashboard.my-exams.download', $userExam->id))->assertStatus(404);
    }

    public function test_missing_file_does_not_burn_a_download_attempt(): void
    {
        $vendor = Vendor::factory()->create();
        $exam = Exam::factory()->create(['vendor_id' => $vendor->id, 'exam_code' => 'PDF-TEST-300']);
        $exam->update(['full_pdf_filename' => 'phantom-file-does-not-exist.pdf']);

        $customer = User::factory()->create(['role' => 'student']);
        $userExam = UserExam::create([
            'user_id' => $customer->id, 'exam_id' => $exam->id, 'access_type' => 'pdf',
            'download_count' => 0, 'max_downloads' => 3, 'purchased_at' => now(),
        ]);

        $response = $this->actingAs($customer)->get(route('dashboard.my-exams.download', $userExam->id));
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $userExam->refresh();
        $this->assertEquals(0, $userExam->download_count);
    }
}
