@component('emails.layouts.base', ['title' => 'Task complete'])
    <h1>Your task is complete ✅</h1>
    <p>Hi {{ $taskRequest->full_name }}, your "{{ $taskRequest->subTask?->name ?? 'task' }}" has been marked complete by {{ $tasker?->name ?? 'your tasker' }}.</p>

    <p>We hope it went well. If something wasn't right, please reach out and we'll make it right.</p>

    <p style="text-align: center;">
        <a href="mailto:{{ config('notifications.support_email') }}" class="cta secondary">Share feedback</a>
    </p>

    <p class="muted">Thanks for choosing {{ config('notifications.brand_name') }}.</p>
@endcomponent
