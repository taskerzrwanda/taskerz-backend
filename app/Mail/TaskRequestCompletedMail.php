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

class TaskRequestCompletedMail extends Mailable implements ShouldQueue
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
        return new Envelope(subject: 'Your task is complete');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task_request_completed',
            with: [
                'taskRequest' => $this->taskRequest,
                'tasker'      => $this->taskRequest->tasker,
            ],
        );
    }
}
