<!DOCTYPE html>
<html>
<head>
    <title>New Task Assignment</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Inter', Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; }
        .header {
            width: 100%;
            height: 64px;
            padding: 24px 0;
            text-align: center;
            background-color: #E56B00;
        }
        .logo { color: #f8fafc !important; font-size: 24px; font-weight: 700; text-decoration: none; }
        .content { padding: 40px 32px; background-color: #ffffff; }
        .assignment-badge {
            display: inline-block;
            background-color: #3B82F6;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .details-box {
            background-color: #f1f5f9;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
        }
        .detail-item { margin-bottom: 16px; }
        .detail-label {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 4px;
        }
        .detail-value {
            color: #1e293b;
            font-size: 16px;
            font-weight: 500;
        }
        .important-info {
            background-color: #FEF3C7;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #F59E0B;
            margin: 20px 0;
        }
        .contact-info {
            background-color: #ECFDF5;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #10B981;
            margin: 20px 0;
        }
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
        }
        .btn-primary {
            background-color: #3B82F6;
            color: white !important;
        }
        .btn-secondary {
            background-color: #1e293b;
            color: white !important;
        }
        .btn-outline {
            background-color: transparent;
            border: 2px solid #3B82F6;
            color: #3B82F6 !important;
        }
        .payment-info {
            background-color: #F0F9FF;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid #0EA5E9;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="https://taskers.rw" class="logo">taskerz.</a>
        </div>

        <div class="content">
            <div class="assignment-badge">NEW ASSIGNMENT</div>

            <h2 style="color: #1e293b; margin: 0 0 16px 0;">
                Congratulations! You've Been Assigned a New Task
            </h2>

            <p style="color: #475569; margin-bottom: 32px;">
                Hello {{ $taskRequest->tasker->name }}, you have been selected for a new task.
                Please review the details below and contact the client to schedule the service.
            </p>

            <div class="details-box">
                <div class="detail-item">
                    <div class="detail-label">Task ID</div>
                    <div class="detail-value">TR{{ str_pad($taskRequest->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Service Type</div>
                    <div class="detail-value">{{ $taskRequest->subTask->name ?? 'N/A' }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Client Name</div>
                    <div class="detail-value">{{ $taskRequest->full_name }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Client Phone</div>
                    <div class="detail-value">{{ $taskRequest->phone }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Client Email</div>
                    <div class="detail-value">{{ $taskRequest->email }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Service Location</div>
                    <div class="detail-value">{{ $taskRequest->location }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Service Description</div>
                    <div class="detail-value" style="white-space: pre-wrap;">{{ $taskRequest->description }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Assigned At</div>
                    <div class="detail-value">{{ $taskRequest->assigned_at ? \Carbon\Carbon::parse($taskRequest->assigned_at)->format('Y-m-d H:i') : 'N/A' }}</div>
                </div>
            </div>

            <div class="payment-info">
                <h3 style="color: #0EA5E9; margin-top: 0;">Payment Information</h3>
                <p style="color: #475569; margin-bottom: 8px;">
                    <strong>Service Price:</strong> RWF {{ number_format($taskRequest->subTask->price ?? 0, 0) }}
                </p>
                <p style="color: #475569; margin-bottom: 0;">
                    <strong>Your Commission:</strong> RWF {{ number_format(($taskRequest->subTask->price ?? 0) * 0.8, 0) }} (80%)
                </p>
            </div>

            <div class="contact-info">
                <h3 style="color: #059669; margin-top: 0;">📞 Contact the Client</h3>
                <p style="color: #475569;">
                    Please contact the client within <strong>24 hours</strong> to:
                </p>
                <ol style="color: #475569; padding-left: 20px;">
                    <li>Confirm the assignment</li>
                    <li>Schedule the service date & time</li>
                    <li>Discuss any specific requirements</li>
                    <li>Provide your estimated arrival time</li>
                </ol>
            </div>

            <div class="important-info">
                <h3 style="color: #D97706; margin-top: 0;">⚠️ Important Notes</h3>
                <ul style="color: #475569; padding-left: 20px;">
                    <li>Always maintain professional communication</li>
                    <li>Confirm the appointment in writing (SMS/Email)</li>
                    <li>Bring necessary tools and equipment</li>
                    <li>Wear your Taskerz ID badge if available</li>
                    <li>Complete the service within estimated duration: <strong>{{ $taskRequest->subTask->duration ?? 'N/A' }}</strong></li>
                </ul>
            </div>

            <div class="action-buttons">
                <a href="tel:{{ $taskRequest->phone }}" class="btn btn-primary">
                    📞 Call Client Now
                </a>
                <a href="https://taskers.rw/tasker/dashboard/tasks/{{ $taskRequest->id }}" class="btn btn-secondary">
                    📋 View Task Details
                </a>
                <a href="sms:{{ $taskRequest->phone }}?body=Hello%20{{ urlencode($taskRequest->full_name) }}%2C%20this%20is%20{{ urlencode($taskRequest->tasker->name) }}%20from%20Taskerz.%20I%20have%20been%20assigned%20to%20your%20{{ urlencode($taskRequest->subTask->name ?? 'task') }}%20request.%20Let%27s%20schedule%20a%20suitable%20time." class="btn btn-outline">
                    💬 Send Intro SMS
                </a>
            </div>

            <div style="margin-top: 32px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 14px;">
                <p><strong>Need Support?</strong> Contact our team at support@taskers.rw or call +250 788 123 456</p>
                <p style="margin-bottom: 0;">This assignment was made on {{ \Carbon\Carbon::parse($taskRequest->assigned_at)->format('F j, Y \a\t g:i A') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
