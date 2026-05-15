<?php

namespace App\Mail;

use App\Mail\Concerns\SendsAsTransactional;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SendsAsTransactional;

    public function __construct(public User $user, public string $code)
    {
        $this->buildQueueable();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verify your email address');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email_verification',
            with: [
                'user' => $this->user,
                'code' => $this->code,
            ],
        );
    }
}
