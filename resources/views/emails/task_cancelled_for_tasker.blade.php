@component('emails.layouts.base', ['title' => 'Task cancelled'])
    <h1>A task was cancelled, {{ $tasker?->name }}</h1>
    <p>Unfortunately, a task that was assigned to you has been cancelled. You don't need to take any further action.</p>

    <div class="panel">
        <div class="panel-row">
            <div class="panel-label">Service</div>
            <div class="panel-value">{{ $taskRequest->subTask?->name ?? 'General Task' }}</div>
        </div>
        <div class="panel-row">
            <div class="panel-label">Customer</div>
            <div class="panel-value">{{ $taskRequest->full_name }}</div>
        </div>
        <div class="panel-row">
            <div class="panel-label">Location</div>
            <div class="panel-value">{{ $taskRequest->location }}</div>
        </div>
        @if($reason)
            <div class="panel-row">
                <div class="panel-label">Reason</div>
                <div class="panel-value" style="white-space: pre-wrap;">{{ $reason }}</div>
            </div>
        @endif
    </div>

    <p style="text-align: center;">
        <a href="{{ config('notifications.frontend_url') }}/tasker/dashboard/pending-tasks" class="cta">View your dashboard</a>
    </p>
@endcomponent
