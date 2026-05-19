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

class AdminNewTaskRequestMail extends Mailable implements ShouldQueue
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
        return new Envelope(
            subject: '[Action] New task request from ' . ($this->taskRequest->full_name ?? 'customer')
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_new_task_request',
            with: [
                'taskRequest' => $this->taskRequest,
                'adminUrl'    => config('notifications.frontend_url') . '/admin/task-requests',
            ],
        );
    }
}
