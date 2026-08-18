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

class TaskAssignedToTaskerMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, SendsAsTransactional;

    public TaskRequest $taskRequest;

    public function __construct(TaskRequest $taskRequest)
    {
        $this->taskRequest = $taskRequest->loadMissing(['subTask', 'tasker']);
        $this->buildQueueable();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New task assignment — action required');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task_assigned_to_tasker',
            with: [
                'taskRequest' => $this->taskRequest,
                'tasker'      => $this->taskRequest->tasker,
            ],
        );
    }
}
