<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Exam;
use App\Models\User;

class ExamUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Exam $exam;
    public User $user;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Exam $exam)
    {
        $this->user = $user;
        $this->exam = $exam;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Updated Questions Available: ' . $this->exam->exam_code . ' Study Guide',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.exam-updated',
        );
    }
}
