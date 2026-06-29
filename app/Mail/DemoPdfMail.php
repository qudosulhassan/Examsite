<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\DemoRequest;
use App\Models\Exam;

class DemoPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    public $demoRequest;
    public $exam;
    public $downloadUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(DemoRequest $demoRequest, Exam $exam, string $downloadUrl)
    {
        $this->demoRequest = $demoRequest;
        $this->exam = $exam;
        $this->downloadUrl = $downloadUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Free ' . $this->exam->exam_code . ' Demo PDF is Ready',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.demo-pdf',
        );
    }
}
