@component('emails.layouts.base', ['title' => 'Request approved'])
    <h1>Your request is approved 👍</h1>
    <p>Hi {{ $taskRequest->full_name }}, your request for "{{ $taskRequest->subTask?->name ?? 'a task' }}" has been approved by our team.</p>
    <p>We're now matching you with the right tasker. You'll receive another email as soon as one is assigned.</p>

    <div class="panel">
        <div class="panel-row">
            <div class="panel-label">Service</div>
            <div class="panel-value">{{ $taskRequest->subTask?->name ?? 'General Task' }}</div>
        </div>
        <div class="panel-row">
            <div class="panel-label">Location</div>
            <div class="panel-value">{{ $taskRequest->location }}</div>
        </div>
    </div>
@endcomponent
