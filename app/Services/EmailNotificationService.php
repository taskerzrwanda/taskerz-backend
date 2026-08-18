<?php

namespace App\Services;

use App\Mail\AdminNewTaskRequestMail;
use App\Mail\EmailVerificationMail;
use App\Mail\PasswordResetMail;
use App\Mail\TaskAssignedToRequesterMail;
use App\Mail\TaskAssignedToTaskerMail;
use App\Mail\TaskerApprovedMail;
use App\Mail\TaskerInviteMail;
use App\Mail\TaskerRejectedMail;
use App\Mail\TaskerWelcomeMail;
use App\Mail\TaskRequestApprovedMail;
use App\Mail\TaskRequestCancelledMail;
use App\Mail\TaskRequestCompletedMail;
use App\Mail\TaskRequestSubmittedMail;
use App\Mail\WelcomeMail;
use App\Models\TaskRequest;
use App\Models\User;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Single entry point for every transactional email.
 *
 * Controllers and services call methods here instead of `Mail::to(...)->send(...)`
 * directly. Every send is wrapped in try/catch + structured logging so a
 * misconfigured mail driver, a queue outage, or a malformed recipient can
 * never break the originating user-facing action.
 *
 * Mailables remain responsible for content + view. This class owns dispatch.
 */
class EmailNotificationService
{
    // -----------------------------------------------------------------------
    // Auth flows
    // -----------------------------------------------------------------------

    public function sendVerificationCode(User $user, string $code): void
    {
        $this->send(
            $user->email,
            new EmailVerificationMail($user, $code),
            'auth.verification_code',
            ['user_id' => $user->id, 'role' => $user->role]
        );
    }

    public function sendCustomerWelcome(User $user): void
    {
        $this->send(
            $user->email,
            new WelcomeMail($user),
            'auth.customer_welcome',
            ['user_id' => $user->id]
        );
    }

    public function sendTaskerWelcome(User $user): void
    {
        $this->send(
            $user->email,
            new TaskerWelcomeMail($user),
            'auth.tasker_welcome',
            ['user_id' => $user->id]
        );
    }

    public function sendPasswordReset(User $user, string $token): void
    {
        $this->send(
            $user->email,
            new PasswordResetMail($user, $token),
            'auth.password_reset',
            ['user_id' => $user->id]
        );
    }

    // -----------------------------------------------------------------------
    // Admin actions on taskers
    // -----------------------------------------------------------------------

    public function sendTaskerApproved(User $tasker): void
    {
        $this->send(
            $tasker->email,
            new TaskerApprovedMail($tasker),
            'admin.tasker_approved',
            ['user_id' => $tasker->id]
        );
    }

    public function sendTaskerRejected(User $tasker, ?string $reason = null): void
    {
        $this->send(
            $tasker->email,
            new TaskerRejectedMail($tasker, $reason),
            'admin.tasker_rejected',
            ['user_id' => $tasker->id]
        );
    }

    public function sendTaskerInvite(User $tasker, string $inviteUrl): void
    {
        $this->send(
            $tasker->email,
            new TaskerInviteMail($tasker, $inviteUrl),
            'admin.tasker_invite',
            ['user_id' => $tasker->id]
        );
    }

    // -----------------------------------------------------------------------
    // Task request lifecycle
    // -----------------------------------------------------------------------

    public function sendTaskRequestSubmitted(TaskRequest $request): void
    {
        if (!$request->email) {
            return;
        }
        $this->send(
            $request->email,
            new TaskRequestSubmittedMail($request),
            'task_request.submitted',
            ['task_request_id' => $request->id]
        );
    }

    public function notifyAdminsOfNewTaskRequest(TaskRequest $request): void
    {
        foreach ($this->adminRecipients() as $email) {
            $this->send(
                $email,
                new AdminNewTaskRequestMail($request),
                'task_request.admin_alert',
                ['task_request_id' => $request->id]
            );
        }
    }

    public function sendTaskerAssigned(TaskRequest $request): void
    {
        $tasker = $request->tasker;
        if (!$tasker || !$tasker->email) {
            return;
        }
        $this->send(
            $tasker->email,
            new TaskAssignedToTaskerMail($request),
            'task_request.tasker_assigned',
            ['task_request_id' => $request->id, 'tasker_id' => $tasker->id]
        );
    }

    public function sendRequesterAssigned(TaskRequest $request): void
    {
        if (!$request->email) {
            return;
        }
        $this->send(
            $request->email,
            new TaskAssignedToRequesterMail($request),
            'task_request.requester_assigned',
            ['task_request_id' => $request->id]
        );
    }

    public function sendTaskRequestApproved(TaskRequest $request): void
    {
        if (!$request->email) {
            return;
        }
        $this->send(
            $request->email,
            new TaskRequestApprovedMail($request),
            'task_request.approved',
            ['task_request_id' => $request->id]
        );
    }

    public function sendTaskRequestCompleted(TaskRequest $request): void
    {
        if (!$request->email) {
            return;
        }
        $this->send(
            $request->email,
            new TaskRequestCompletedMail($request),
            'task_request.completed',
            ['task_request_id' => $request->id]
        );
    }

    public function sendTaskRequestCancelled(TaskRequest $request, ?string $reason = null): void
    {
        if (!$request->email) {
            return;
        }
        $this->send(
            $request->email,
            new TaskRequestCancelledMail($request, $reason),
            'task_request.cancelled',
            ['task_request_id' => $request->id]
        );
    }

    // -----------------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------------

    /**
     * Queue (or send) a single transactional message.
     *
     * Failures here represent queue-dispatch failures (DB down, invalid driver),
     * not delivery failures — the latter are handled in Mailable::failed().
     */
    private function send(?string $recipient, Mailable $mailable, string $type, array $context = []): void
    {
        $recipient = $recipient ? trim($recipient) : null;

        if (!$recipient || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            Log::warning("Skipped {$type} email: invalid recipient", array_merge($context, [
                'recipient' => $recipient,
                'mailable'  => $mailable::class,
            ]));
            return;
        }

        try {
            Mail::to($recipient)->send($mailable);

            Log::info("Queued {$type} email", array_merge($context, [
                'recipient' => $recipient,
                'mailable'  => $mailable::class,
            ]));
        } catch (\Throwable $e) {
            Log::error("Failed to dispatch {$type} email", array_merge($context, [
                'recipient' => $recipient,
                'mailable'  => $mailable::class,
                'exception' => $e->getMessage(),
            ]));
        }
    }

    /**
     * Admin recipients = configured ADMIN_NOTIFICATION_EMAILS, or fall back to
     * every user with role=admin in the database if the env var is empty.
     */
    private function adminRecipients(): array
    {
        $configured = config('notifications.admin_emails', []);
        if (!empty($configured)) {
            return $configured;
        }

        return User::query()
            ->where('role', 'admin')
            ->whereNotNull('email')
            ->pluck('email')
            ->all();
    }
}
