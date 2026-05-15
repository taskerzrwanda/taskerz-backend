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

class PasswordResetMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SendsAsTransactional;

    public function __construct(public User $user, public string $token)
    {
        $this->buildQueueable();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Reset your password');
    }

    public function content(): Content
    {
        $resetUrl = sprintf(
            '%s/reset-password?token=%s&email=%s',
            config('notifications.frontend_url'),
            urlencode($this->token),
            urlencode($this->user->email),
        );

        return new Content(
            view: 'emails.password_reset',
            with: [
                'user'     => $this->user,
                'resetUrl' => $resetUrl,
                'ttlMins'  => config('notifications.password_reset_ttl_minutes'),
            ],
        );
    }
}
