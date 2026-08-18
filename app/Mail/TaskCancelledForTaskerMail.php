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

class TaskCancelledForTaskerMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SendsAsTransactional;

    public TaskRequest $taskRequest;

    public function __construct(TaskRequest $taskRequest, public ?string $reason = null)
    {
        $this->taskRequest = $taskRequest->loadMissing(['subTask', 'tasker']);
        $this->buildQueueable();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'A task assigned to you was cancelled');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task_cancelled_for_tasker',
            with: [
                'taskRequest' => $this->taskRequest,
                'tasker'      => $this->taskRequest->tasker,
                'reason'      => $this->reason,
            ],
        );
    }
}
