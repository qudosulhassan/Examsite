<?php

namespace App\Console\Commands;

use App\Models\Exam;
use App\Services\PdfStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncPdfsToR2 extends Command
{
    protected $signature = 'pdfs:sync-r2 {--dry-run : Only report what would be uploaded}';

    protected $description = 'Upload every exam PDF that exists on this server to the shared Cloudflare R2 bucket';

    public function handle(): int
    {
        if (!PdfStorage::configured()) {
            $this->error('R2 is not configured. Set AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_BUCKET and AWS_ENDPOINT in .env (and install league/flysystem-aws-s3-v3).');
            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $uploaded = $already = $missing = $failed = 0;

        $jobs = [];
        foreach (Exam::query()->get(['id', 'exam_code', 'demo_pdf_filename', 'full_pdf_filename']) as $exam) {
            if ($exam->full_pdf_filename) {
                $jobs[] = ['local', 'full/' . $exam->full_pdf_filename, $exam->exam_code];
            }
            if ($exam->demo_pdf_filename) {
                $jobs[] = ['public', 'demos/' . $exam->demo_pdf_filename, $exam->exam_code];
            }
        }

        foreach ($jobs as [$disk, $path, $code]) {
            if (PdfStorage::exists($path)) {
                $already++;
                continue;
            }

            if (!Storage::disk($disk)->exists($path)) {
                $missing++;
                $this->warn("  missing on this server: {$path} ({$code})");
                continue;
            }

            if ($dry) {
                $this->line("  would upload: {$path}");
                $uploaded++;
                continue;
            }

            try {
                Storage::disk('r2')->put($path, Storage::disk($disk)->readStream($path));
                $uploaded++;
                $this->info("  uploaded: {$path}");
            } catch (\Throwable $e) {
                $failed++;
                $this->error("  failed: {$path} - " . $e->getMessage());
            }
        }

        $verb = $dry ? 'would upload' : 'uploaded';
        $this->newLine();
        $this->info("Done. {$verb}: {$uploaded}, already in R2: {$already}, missing on this server: {$missing}, failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
