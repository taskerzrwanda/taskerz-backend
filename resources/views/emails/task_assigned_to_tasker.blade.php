@component('emails.layouts.base', ['title' => 'New assignment'])
    <h1>You've got a new task, {{ $tasker?->name }}</h1>
    <p>A customer has been matched to your profile. Please confirm details and reach out within 30 minutes.</p>

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
            <div class="panel-label">Phone</div>
            <div class="panel-value"><a href="tel:{{ $taskRequest->phone }}">{{ $taskRequest->phone }}</a></div>
        </div>
        @if($taskRequest->email)
            <div class="panel-row">
                <div class="panel-label">Email</div>
                <div class="panel-value">{{ $taskRequest->email }}</div>
            </div>
        @endif
        <div class="panel-row">
            <div class="panel-label">Location</div>
            <div class="panel-value">{{ $taskRequest->location }}</div>
        </div>
        <div class="panel-row">
            <div class="panel-label">Description</div>
            <div class="panel-value" style="white-space: pre-wrap;">{{ $taskRequest->description }}</div>
        </div>
    </div>

    <p style="text-align: center;">
        <a href="{{ config('notifications.frontend_url') }}/tasker/dashboard/pending-tasks" class="cta">View in dashboard</a>
    </p>
@endcomponent
