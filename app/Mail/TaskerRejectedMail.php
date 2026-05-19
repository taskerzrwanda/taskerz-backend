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

class TaskerRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SendsAsTransactional;

    public function __construct(public User $tasker, public ?string $reason = null)
    {
        $this->buildQueueable();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'An update on your tasker application');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tasker_rejected',
            with: [
                'tasker' => $this->tasker,
                'reason' => $this->reason,
            ],
        );
    }
}
