@component('emails.layouts.base', ['title' => 'Verified'])
    <h1>You're verified, {{ $user->name }}</h1>
    <p>Thanks for confirming your email. Your tasker profile is now under review by our admin team.</p>
    <p>Once approved you'll be visible to customers and eligible to receive task assignments. We typically review profiles within 1–2 business days.</p>

    <p style="text-align: center;">
        <a href="{{ config('notifications.frontend_url') }}/tasker/dashboard" class="cta">Open dashboard</a>
    </p>

    <p class="muted">While you wait, make sure your profession, skills and location are filled in — it's how we match you to the right jobs.</p>
@endcomponent
