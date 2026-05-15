@component('emails.layouts.base', ['title' => 'Application update'])
    <h1>An update on your application</h1>
    <p>Hi {{ $tasker->name }},</p>
    <p>Thank you for applying to become a tasker on {{ config('notifications.brand_name') }}. After reviewing your profile, we're unable to approve it at this time.</p>

    @if($reason)
        <div class="panel">
            <div class="panel-label">Reason</div>
            <div class="panel-value">{{ $reason }}</div>
        </div>
    @endif

    <p>If you'd like clarification or would like to re-apply with updated information, please reach out to our team — we're happy to help.</p>

    <p style="text-align: center;">
        <a href="mailto:{{ config('notifications.support_email') }}" class="cta secondary">Contact support</a>
    </p>
@endcomponent
