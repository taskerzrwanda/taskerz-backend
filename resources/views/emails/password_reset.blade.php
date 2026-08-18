@component('emails.layouts.base', ['title' => 'Reset your password'])
    <h1>Reset your password</h1>
    <p>Hi {{ $user->name }},</p>
    <p>
        We received a request to reset the password on your {{ config('notifications.brand_name') }} account.
        Click the button below to choose a new one.
    </p>

    <p style="text-align: center;">
        <a href="{{ $resetUrl }}" class="cta">Reset password</a>
    </p>

    <p class="muted">
        This link expires in {{ $ttlMins }} minutes. If the button doesn't work, copy and paste this URL into your browser:
    </p>
    <p class="muted" style="word-break: break-all;">{{ $resetUrl }}</p>

    <p class="muted">
        Didn't request a reset? You can ignore this email — your password won't change.
    </p>
@endcomponent
