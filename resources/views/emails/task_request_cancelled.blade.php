@component('emails.layouts.base', ['title' => 'Request cancelled'])
    <h1>Your task request was cancelled</h1>
    <p>Hi {{ $taskRequest->full_name }}, your request for "{{ $taskRequest->subTask?->name ?? 'a task' }}" has been cancelled.</p>

    @if($reason)
        <div class="panel">
            <div class="panel-label">Reason</div>
            <div class="panel-value">{{ $reason }}</div>
        </div>
    @endif

    <p>If this was a mistake or you'd like to submit a new request, you can do so any time.</p>

    <p style="text-align: center;">
        <a href="{{ config('notifications.frontend_url') }}/request-a-task" class="cta">Submit a new request</a>
    </p>
@endcomponent
