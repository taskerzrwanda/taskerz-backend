@php
    $brand        = config('notifications.brand_name', 'Taskerz');
    $supportEmail = config('notifications.support_email');
    $supportPhone = config('notifications.support_phone');
    $frontendUrl  = config('notifications.frontend_url');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? $brand }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Inter', Arial, sans-serif; color: #1e293b; }
        .wrapper { width: 100%; padding: 24px 12px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04); }
        .header { padding: 24px 32px; background-color: #E56B00; text-align: center; }
        .header a { color: #ffffff !important; font-size: 22px; font-weight: 700; text-decoration: none; letter-spacing: -0.02em; }
        .content { padding: 32px; line-height: 1.6; }
        .content h1 { font-size: 22px; margin: 0 0 16px; color: #0f172a; font-weight: 700; }
        .content p { margin: 0 0 16px; color: #334155; font-size: 15px; }
        .cta { display: inline-block; background-color: #E56B00; color: #ffffff !important; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; margin: 8px 0; }
        .cta.secondary { background-color: #1e293b; }
        .code { font-size: 32px; font-weight: 700; color: #E56B00; letter-spacing: 6px; text-align: center; padding: 20px; background-color: #fff7ed; border-radius: 8px; margin: 24px 0; }
        .panel { background-color: #f8fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #e2e8f0; }
        .panel-row { display: block; margin-bottom: 12px; }
        .panel-row:last-child { margin-bottom: 0; }
        .panel-label { color: #64748b; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
        .panel-value { color: #0f172a; font-size: 15px; margin-top: 2px; }
        .footer { padding: 24px 32px; text-align: center; background-color: #f8fafc; border-top: 1px solid #e2e8f0; }
        .footer p { margin: 4px 0; color: #64748b; font-size: 13px; }
        .footer a { color: #E56B00; text-decoration: none; }
        .muted { color: #64748b; font-size: 13px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <a href="{{ $frontendUrl }}">{{ $brand }}.</a>
            </div>

            <div class="content">
                {{ $slot }}
            </div>

            <div class="footer">
                <p>Need help? Email <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a> or call {{ $supportPhone }}.</p>
                <p class="muted">© {{ date('Y') }} {{ $brand }}. All rights reserved.</p>
                <p class="muted">This is an automated message — please do not reply directly.</p>
            </div>
        </div>
    </div>
</body>
</html>
