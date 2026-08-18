@component('emails.layouts.base', ['title' => 'Password changed'])
    <h1>Your password was changed</h1>
    <p>Hi {{ $user->name }},</p>
    <p>
        This is a confirmation that the password for your {{ config('notifications.brand_name') }} account
        ({{ $user->email }}) was just changed.
    </p>

    <p class="muted">
        If you made this change, no further action is needed. If you did <strong>not</strong> change your
        password, your account may be at risk — please reset it right away and contact us at
        {{ config('notifications.support_email') }}.
    </p>
@endcomponent
