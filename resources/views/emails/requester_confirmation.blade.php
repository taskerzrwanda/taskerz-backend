<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Request Confirmation - Taskerz</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            background: #f5f7fa;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .email-wrapper {
            width: 100%;
            padding: 40px 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .header {
            background: linear-gradient(135deg, #E56B00 0%, #FF8C00 100%);
            padding: 32px;
            text-align: center;
        }

        .logo {
            color: #ffffff !important;
            font-size: 28px;
            font-weight: 700;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .content {
            padding: 40px 32px;
        }

        .text-center {
            text-align: center;
        }

        .main-heading {
            color: #1e293b;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .sub-heading {
            color: #64748b;
            font-size: 15px;
            line-height: 1.5;
            margin-bottom: 32px;
        }

        .request-id {
            display: inline-block;
            background: #f1f5f9;
            color: #475569;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 32px;
        }

        .details-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
        }

        .detail-item {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .detail-label {
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .detail-value {
            color: #1e293b;
            font-size: 15px;
            font-weight: 500;
            line-height: 1.5;
        }

        .info-box {
            background: #FEF3C7;
            padding: 20px;
            border-radius: 8px;
            border-left: 3px solid #F59E0B;
            margin: 24px 0;
        }

        .info-box p {
            color: #92400E;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        .contact-section {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin: 24px 0;
            text-align: center;
        }

        .contact-section h3 {
            color: #1e293b;
            font-size: 16px;
            margin-bottom: 12px;
        }

        .contact-info {
            color: #475569;
            font-size: 14px;
            line-height: 1.8;
        }

        .contact-info strong {
            color: #1e293b;
        }

        .footer {
            text-align: center;
            padding: 24px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }

        .footer p {
            color: #94a3b8;
            font-size: 12px;
            margin: 0;
        }

        @media only screen and (max-width: 600px) {
            .email-wrapper { padding: 20px 10px; }
            .content { padding: 32px 24px; }
            .header { padding: 24px; }
            .details-box { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="container">
            <div class="header">
                <a href="https://taskers.rw" class="logo">taskerz.</a>
            </div>

            <div class="content">
                <div class="text-center">
                    <h1 class="main-heading">Request Received!</h1>
                    <p class="sub-heading">We'll get back to you shortly</p>
                </div>

                <div class="details-box">
                    <div class="detail-item">
                        <div class="detail-label">Service</div>
                        <div class="detail-value">{{ $taskRequest->subTask->name ?? 'N/A' }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Name</div>
                        <div class="detail-value">{{ $taskRequest->full_name }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">{{ $taskRequest->email }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value">{{ $taskRequest->phone }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Location</div>
                        <div class="detail-value">{{ $taskRequest->location }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Description</div>
                        <div class="detail-value" style="white-space: pre-wrap;">{{ $taskRequest->description }}</div>
                    </div>
                </div>

                <div class="info-box">
                    <p>Our team will review your request and assign a qualified tasker within 30 minutes during business hours.</p>
                </div>

                <div class="contact-section">
                    <h3>Need Help?</h3>
                    <div class="contact-info">
                        <strong>Email:</strong> taskerzrwanda@gmail.com<br>
                        <strong>Phone:</strong> +250 785 775 280
                    </div>
                </div>
            </div>

            <div class="footer">
                <p>This is an automated email. Please do not reply to this message.</p>
            </div>
        </div>
    </div>
</body>
</html>
