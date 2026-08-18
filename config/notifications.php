<?php

/*
|--------------------------------------------------------------------------
| App notification settings
|--------------------------------------------------------------------------
| Single source of truth for transactional-email knobs that the
| EmailNotificationService and Mailable classes consume.
*/

return [

    // Comma-separated list in env, e.g. ADMIN_NOTIFICATION_EMAILS="ops@taskers.rw,founder@taskers.rw"
    'admin_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ADMIN_NOTIFICATION_EMAILS', ''))
    ))),

    // Public-facing support address rendered in email templates.
    'support_email' => env('SUPPORT_EMAIL', 'support@taskers.rw'),
    'support_phone' => env('SUPPORT_PHONE', '+250 785 775 280'),

    // Used to build absolute links in emails (password reset, etc.).
    // Keep trailing slash off.
    'frontend_url' => rtrim(env('FRONTEND_URL', 'https://taskers.rw'), '/'),

    // Brand name shown in templates.
    'brand_name' => env('APP_BRAND_NAME', 'Taskerz'),

    // Queue + retry settings for transactional mailables.
    'queue' => [
        'connection' => env('MAIL_QUEUE_CONNECTION', config('queue.default')),
        'name'       => env('MAIL_QUEUE_NAME', 'emails'),
        'tries'      => (int) env('MAIL_QUEUE_TRIES', 3),
        // Seconds to wait between attempts (1 min, 5 min, 15 min).
        'backoff'    => [60, 300, 900],
    ],

    // Lifetime of password-reset tokens in minutes.
    'password_reset_ttl_minutes' => (int) env('PASSWORD_RESET_TTL_MINUTES', 60),

    // Lifetime of email verification codes in minutes.
    // Codes older than this are rejected during verify-code.
    'verification_code_ttl_minutes' => (int) env('VERIFICATION_CODE_TTL_MINUTES', 30),
];
