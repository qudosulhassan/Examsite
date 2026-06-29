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

        // Generate temporary URL from Cloudflare R2 bucket (expires in 24 hours for demo requests)
        // Note: For local testing we check if the file exists on the disk first, or generate fallback URL
        try {
            $downloadUrl = Storage::disk('r2')->temporaryUrl(
                'demos/' . $exam->demo_pdf_filename,
                now()->addHours(24)
            );
        } catch (\Exception $e) {
            // Fallback for local testing if R2 is not fully configured yet
            $downloadUrl = url('/storage/demos/' . $exam->demo_pdf_filename);
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
