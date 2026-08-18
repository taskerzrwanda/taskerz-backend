<?php

namespace App\Mail;

use App\Mail\Concerns\SendsAsTransactional;
use App\Models\TaskRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskRequestSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SendsAsTransactional;

    public TaskRequest $taskRequest;

    public function __construct(TaskRequest $taskRequest)
    {
        $this->taskRequest = $taskRequest->loadMissing('subTask');
        $this->buildQueueable();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'We received your task request');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task_request_submitted',
            with: ['taskRequest' => $this->taskRequest],
        );
    }
}
