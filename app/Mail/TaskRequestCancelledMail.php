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

class TaskRequestCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SendsAsTransactional;

    public TaskRequest $taskRequest;

    public function __construct(TaskRequest $taskRequest, public ?string $reason = null)
    {
        $this->taskRequest = $taskRequest->loadMissing('subTask');
        $this->buildQueueable();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your task request was cancelled');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task_request_cancelled',
            with: [
                'taskRequest' => $this->taskRequest,
                'reason'      => $this->reason,
            ],
        );
    }
}
