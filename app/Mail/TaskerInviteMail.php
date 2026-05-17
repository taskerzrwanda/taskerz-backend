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

class TaskerInviteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SendsAsTransactional;

    public function __construct(public User $tasker, public string $inviteUrl)
    {
        $this->buildQueueable();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're invited to join " . config('notifications.brand_name') . ' as a tasker',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tasker_invite',
            with: [
                'tasker'    => $this->tasker,
                'inviteUrl' => $this->inviteUrl,
                'ttlMins'   => config('notifications.password_reset_ttl_minutes'),
            ],
        );
    }
}
