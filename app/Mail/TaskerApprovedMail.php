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

class TaskerApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SendsAsTransactional;

    public function __construct(public User $tasker)
    {
        $this->buildQueueable();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your tasker profile has been approved');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tasker_approved',
            with: ['tasker' => $this->tasker],
        );
    }
}
