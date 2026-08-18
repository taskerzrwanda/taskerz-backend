@component('emails.layouts.base', ['title' => "You're invited to join " . config('notifications.brand_name')])
    <h1>Welcome to {{ config('notifications.brand_name') }}, {{ $tasker->name }}!</h1>
    <p>
        An admin has registered you as a tasker on {{ config('notifications.brand_name') }}.
        Your account is already approved — all you need to do is choose a password,
        and you'll be ready to start receiving task requests.
    </p>

    <p style="text-align: center;">
        <a href="{{ $inviteUrl }}" class="cta">Set your password</a>
    </p>

    <p class="muted">
        This invite link expires in {{ $ttlMins }} minutes. If the button doesn't work,
        copy and paste this URL into your browser:
    </p>
    <p class="muted" style="word-break: break-all;">{{ $inviteUrl }}</p>

    <p class="muted">
        After setting your password, you can sign in at
        <a href="{{ config('notifications.frontend_url') }}/login">{{ config('notifications.frontend_url') }}/login</a>.
        Questions? Reach us at {{ config('notifications.support_email') }}.
    </p>
@endcomponent
