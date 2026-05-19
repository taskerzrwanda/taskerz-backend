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

class TaskerWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SendsAsTransactional;

    public function __construct(public User $user)
    {
        $this->buildQueueable();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your tasker account is verified'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tasker_welcome',
            with: ['user' => $this->user],
        );
    }
}
