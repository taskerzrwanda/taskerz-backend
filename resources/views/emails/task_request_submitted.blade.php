@component('emails.layouts.base', ['title' => 'Request received'])
    <h1>We've got your request, {{ $taskRequest->full_name }} ✅</h1>
    <p>Thanks for choosing {{ config('notifications.brand_name') }}. We're reviewing your request and will match you with a vetted tasker shortly — usually within 30 minutes during business hours.</p>

    <div class="panel">
        <div class="panel-row">
            <div class="panel-label">Service</div>
            <div class="panel-value">{{ $taskRequest->subTask?->name ?? 'General Task' }}</div>
        </div>
        <div class="panel-row">
            <div class="panel-label">Location</div>
            <div class="panel-value">{{ $taskRequest->location }}</div>
        </div>
        <div class="panel-row">
            <div class="panel-label">Phone</div>
            <div class="panel-value">{{ $taskRequest->phone }}</div>
        </div>
        <div class="panel-row">
            <div class="panel-label">Description</div>
            <div class="panel-value" style="white-space: pre-wrap;">{{ $taskRequest->description }}</div>
        </div>
    </div>

    <p class="muted">A team member will call you at {{ $taskRequest->phone }} to confirm details before dispatching a tasker.</p>
@endcomponent
