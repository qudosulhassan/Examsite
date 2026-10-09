<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\DemoRequest;
use App\Mail\DemoPdfMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendDemoPdfEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $demoRequest;

    /**
     * Create a new job instance.
     */
    public function __construct(DemoRequest $demoRequest)
    {
        $this->demoRequest = $demoRequest;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $exam = $this->demoRequest->exam;

        if (!$exam || !$exam->demo_pdf_filename) {
            return;
        }

        // Demo PDFs are free/public content: served from this server's public disk, or - when the
        // sample was uploaded on another site - from the shared R2 bucket via a signed link.
        $demoPath = 'demos/' . $exam->demo_pdf_filename;
        if (Storage::disk('public')->exists($demoPath)) {
            $downloadUrl = Storage::disk('public')->url($demoPath);
        } elseif ($r2Url = \App\Services\PdfStorage::temporaryUrl($demoPath, now()->addDays(6))) {
            $downloadUrl = $r2Url;
        } else {
            \Illuminate\Support\Facades\Log::error("Demo PDF file missing from storage for exam {$exam->exam_code} (demo request #{$this->demoRequest->id})");
            return;
        }

        // Send email
        Mail::to($this->demoRequest->email)->send(
            new DemoPdfMail($this->demoRequest, $exam, $downloadUrl)
        );

        // Update delivered timestamp
        $this->demoRequest->update([
            'delivered_at' => now(),
        ]);
    }
}
