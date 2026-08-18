@component('emails.layouts.base', ['title' => 'Approved'])
    <h1>You're approved, {{ $tasker->name }} 🎉</h1>
    <p>Great news — your tasker profile has been reviewed and approved by our admin team. You're now eligible to receive task assignments through {{ config('notifications.brand_name') }}.</p>

    <p>Here's what happens next:</p>
    <div class="panel">
        <div class="panel-row">
            <div class="panel-label">1. Stay reachable</div>
            <div class="panel-value">Keep your phone on — we'll text you when a customer is matched to your profile.</div>
        </div>
        <div class="panel-row">
            <div class="panel-label">2. Watch your dashboard</div>
            <div class="panel-value">New assignments and customer details appear in your dashboard.</div>
        </div>
        <div class="panel-row">
            <div class="panel-label">3. Keep your profile fresh</div>
            <div class="panel-value">Update skills and location so you keep showing up in matches.</div>
        </div>
    </div>

    <p style="text-align: center;">
        <a href="{{ config('notifications.frontend_url') }}/tasker/dashboard" class="cta">Open dashboard</a>
    </p>
@endcomponent
