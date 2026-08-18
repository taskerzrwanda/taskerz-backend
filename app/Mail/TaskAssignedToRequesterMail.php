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

class TaskAssignedToRequesterMail extends Mailable implements ShouldQueue
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
        return new Envelope(subject: 'A tasker has been assigned to your request');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task_assigned_to_requester',
            with: [
                'taskRequest' => $this->taskRequest,
                'tasker'      => $this->taskRequest->tasker,
            ],
        );
    }
}
