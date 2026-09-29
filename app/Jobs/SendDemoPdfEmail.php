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

        // Demo PDFs are free/public content and only ever live on the public disk
        // (see ExamAdminController) - no R2 involvement needed for this one.
        if (!Storage::disk('public')->exists('demos/' . $exam->demo_pdf_filename)) {
            \Illuminate\Support\Facades\Log::error("Demo PDF file missing from storage for exam {$exam->exam_code} (demo request #{$this->demoRequest->id})");
            return;
        }

        $downloadUrl = Storage::disk('public')->url('demos/' . $exam->demo_pdf_filename);

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
