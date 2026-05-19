@component('emails.layouts.base', ['title' => 'New task request'])
    <h1>New task request needs review</h1>
    <p>A new task request was just submitted and is waiting for a tasker to be assigned.</p>

    <div class="panel">
        <div class="panel-row">
            <div class="panel-label">Customer</div>
            <div class="panel-value">{{ $taskRequest->full_name }}</div>
        </div>
        <div class="panel-row">
            <div class="panel-label">Phone</div>
            <div class="panel-value">{{ $taskRequest->phone }}</div>
        </div>
        @if($taskRequest->email)
            <div class="panel-row">
                <div class="panel-label">Email</div>
                <div class="panel-value">{{ $taskRequest->email }}</div>
            </div>
        @endif
        <div class="panel-row">
            <div class="panel-label">Service</div>
            <div class="panel-value">{{ $taskRequest->subTask?->name ?? 'General Task' }}</div>
        </div>
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
        <a href="{{ $adminUrl }}" class="cta">Review in admin</a>
    </p>
@endcomponent
