<?php

namespace App\Mail;

use App\Models\TaskRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskerEmailSender extends Mailable
{
    use Queueable, SerializesModels;

    public $taskRequest;
    public $emailType; // 'admin', 'requester', 'tasker', 'requester_assigned'

    /**
     * Create a new message instance.
     */
    public function __construct(TaskRequest $taskRequest, $emailType = 'admin')
    {
        $this->taskRequest = $taskRequest->load(['subTask', 'tasker']);
        $this->emailType = $emailType;
    }

    public function build()
    {
        return $this->subject($this->getSubject())
            ->view($this->getViewName())
            ->with([
                'taskRequest' => $this->taskRequest,
                'emailType' => $this->emailType
            ]);
    }

    /**
     * Get the subject based on email type
     */
    protected function getSubject()
    {
        switch ($this->emailType) {
            case 'requester':
                return 'Task Request Confirmation - Taskerz';
            case 'tasker':
                return 'New Task Assignment - Taskerz';
            case 'requester_assigned':
                return 'Tasker Assigned to Your Request - Taskerz';
            default:
                return 'New Task Request Received';
        }
    }

    /**
     * Get the view name based on email type
     */
    protected function getViewName()
    {
        switch ($this->emailType) {
            case 'requester':
                return 'emails.requester_confirmation';
            case 'tasker':
                return 'emails.tasker_assignment';
            case 'requester_assigned':
                return 'emails.requester_tasker_assigned';
            default:
                return 'emails.admin_notification';
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getSubject(),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: $this->getViewName(),
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
