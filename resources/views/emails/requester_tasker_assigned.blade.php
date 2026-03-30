<!DOCTYPE html>
<html>
<head>
    <title>Tasker Assigned</title>
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
        .tasker-card {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
            border-left: 4px solid #3B82F6;
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
        .tasker-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #3B82F6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            margin-right: 20px;
        }
        .tasker-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .rating {
            color: #F59E0B;
            margin-left: 10px;
        }
        .next-steps {
            background-color: #ECFDF5;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #10B981;
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
            <h2 style="color: #1e293b; margin: 0 0 16px 0;">
                Great News! A Tasker Has Been Assigned
            </h2>

            <p style="color: #475569; margin-bottom: 32px;">
                Hello {{ $taskRequest->full_name }}, we've found the perfect professional for your task.
                Your assigned tasker will contact you shortly to schedule the service.
            </p>

            <div class="tasker-card">
                <div class="tasker-info">
                    <div class="tasker-avatar">
                        {{ substr($taskRequest->tasker->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 style="color: #1e293b; margin: 0 0 4px 0;">{{ $taskRequest->tasker->name }}</h3>
                        <p style="color: #64748b; margin: 0 0 8px 0;">{{ $taskRequest->tasker->profession }}</p>
                        <p style="color: #475569; margin: 0;">
                            ⭐ {{ $taskRequest->tasker->rating }} Rating
                            • 📊 {{ $taskRequest->tasker->completed_tasks }} Tasks Completed
                        </p>
                    </div>
                </div>

                <p style="color: #475569; margin-bottom: 16px;">
                    <strong>Experience:</strong> {{ $taskRequest->tasker->work_experience }}
                </p>

                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    @foreach(($taskRequest->tasker->skills ?? []) as $skill)
                        <span style="background-color: #E0F2FE; color: #0369A1; padding: 4px 12px; border-radius: 16px; font-size: 14px;">
                            {{ $skill }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="details-box">
                <div class="detail-item">
                    <div class="detail-label">Task Details</div>
                    <div class="detail-value">{{ $taskRequest->subTask->name ?? 'N/A' }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Scheduled Service</div>
                    <div class="detail-value">Awaiting scheduling with tasker</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Tasker's Contact</div>
                    <div class="detail-value">{{ $taskRequest->tasker->phone }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Tasker's Email</div>
                    <div class="detail-value">{{ $taskRequest->tasker->email }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Service Location</div>
                    <div class="detail-value">{{ $taskRequest->location }}</div>
                </div>
            </div>

            <div class="next-steps">
                <h3 style="color: #059669; margin-top: 0;">📅 What Happens Next?</h3>
                <ol style="color: #475569; padding-left: 20px;">
                    <li><strong>Tasker will contact you</strong> within 24 hours to schedule the service</li>
                    <li>Discuss any specific requirements or concerns with the tasker</li>
                    <li>Agree on the exact date and time for the service</li>
                    <li>The tasker will arrive at the scheduled time with necessary equipment</li>
                    <li>After completion, you'll be asked to rate and review the service</li>
                </ol>
            </div>

            <div style="text-align: center; margin-top: 32px;">
                <a href="https://taskers.rw/my-requests/{{ $taskRequest->id }}"
                   style="background-color: #3B82F6; color: white; padding: 12px 32px; border-radius: 8px; text-decoration: none; font-weight: 500;">
                    View Request Details
                </a>
            </div>

            <div style="margin-top: 32px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 14px;">
                <p><strong>Need to reschedule or have questions?</strong> Contact support@taskers.rw or call +250 788 123 456</p>
                <p style="margin-bottom: 0;">Task assigned on {{ \Carbon\Carbon::parse($taskRequest->assigned_at)->format('F j, Y') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
