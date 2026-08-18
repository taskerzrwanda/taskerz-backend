@component('emails.layouts.base', ['title' => 'Verify your email'])
    <h1>Verify your email address</h1>
    <p>Hi {{ $user->name }},</p>
    <p>
        @if($user->role === 'tasker')
            Welcome to {{ config('notifications.brand_name') }}! Use the code below to finish setting up your tasker account so customers can book you.
        @else
            Thanks for signing up. Use the code below to confirm your email and unlock your account.
        @endif
    </p>

    <div class="code">{{ $code }}</div>

    <p class="muted">
        This code expires in {{ config('notifications.verification_code_ttl_minutes') }} minutes.
        If you didn't request this, you can safely ignore the message.
    </p>
@endcomponent
