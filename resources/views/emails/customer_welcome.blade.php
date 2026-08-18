@component('emails.layouts.base', ['title' => 'Welcome'])
    <h1>Welcome to {{ config('notifications.brand_name') }}, {{ $user->name }}!</h1>
    <p>Your account is verified and ready to go.</p>
    <p>You can now request a service any time — we'll match you with a vetted tasker fast.</p>

    <p style="text-align: center;">
        <a href="{{ config('notifications.frontend_url') }}/request-a-task" class="cta">Request a task</a>
    </p>

    <p class="muted">If you didn't create this account, please email {{ config('notifications.support_email') }}.</p>
@endcomponent
