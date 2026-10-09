<?php

namespace Tests\Feature;

use App\Services\PdfStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfStorageR2Test extends TestCase
{
    public function test_r2_helper_is_a_safe_no_op_when_not_configured(): void
    {
        config([
            'filesystems.disks.r2.key' => null,
            'filesystems.disks.r2.secret' => null,
            'filesystems.disks.r2.bucket' => null,
            'filesystems.disks.r2.endpoint' => null,
        ]);

        $file = UploadedFile::fake()->create('a.pdf', 10, 'application/pdf');

        $this->assertFalse(PdfStorage::configured());
        $this->assertFalse(PdfStorage::put('full', $file, 'a-full.pdf'));
        $this->assertFalse(PdfStorage::exists('full/a-full.pdf'));
        $this->assertNull(PdfStorage::temporaryUrl('full/a-full.pdf', now()->addMinutes(5)));
        PdfStorage::delete('full/a-full.pdf');
    }

    public function test_r2_helper_round_trips_when_configured(): void
    {
        config([
            'filesystems.disks.r2.key' => 'test-key',
            'filesystems.disks.r2.secret' => 'test-secret',
            'filesystems.disks.r2.bucket' => 'test-bucket',
            'filesystems.disks.r2.endpoint' => 'https://example.invalid',
        ]);
        Storage::fake('r2');

        $file = UploadedFile::fake()->create('b.pdf', 10, 'application/pdf');

        $this->assertTrue(PdfStorage::configured());
        $this->assertTrue(PdfStorage::put('full', $file, 'b-full.pdf'));
        $this->assertTrue(PdfStorage::exists('full/b-full.pdf'));

        PdfStorage::delete('full/b-full.pdf');
        $this->assertFalse(PdfStorage::exists('full/b-full.pdf'));
    }

    public function test_sync_command_refuses_to_run_without_r2(): void
    {
        config(['filesystems.disks.r2.key' => null]);

        $this->artisan('pdfs:sync-r2')->assertFailed();
    }
}
