<!DOCTYPE html>
<html>
<head>
    <title>taskerz. Email Verification</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Inter', Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { 
            width: 100%;
            height: 64px;
            padding: 24px 0; text-align: center; background-color: #E56B00; }
        .logo { color: #f8fafc !important; font-size: 24px; font-weight: 700; text-decoration: none; }
        .content { padding: 40px 32px; background-color: #ffffff; }
        .code { 
            font-size: 32px;
            font-weight: 600;
            color: #E56B00;
            letter-spacing: 2px;
            margin: 24px 0;
        }
        .cta-button {
            background-color: #2563eb;
            color: #ffffff !important;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin: 16px 0;
            font-weight: 500;
        }
        .search-prompt {
            background-color: #f1f5f9;
            border-radius: 8px;
            padding: 16px;
            margin: 24px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="https://taskers.rw" class="logo">taskerz.</a>
        </div>

        <div class="content">
            <h2 style="color: #1e293b; margin: 0 0 24px 0;">Verify Your Email Address</h2>
            
            <p style="color: #64748b; line-height: 1.6; margin: 0 0 24px 0;">
                Thank you for joining taskerz. Please use the following verification code to complete your registration:
            </p>

            <div class="code">{{ $code }} </div>

     
        </div>

        <div style="padding: 24px; text-align: center; color: #64748b; font-size: 14px;">
            <p style="margin: 8px 0;">
                © {{ date('Y') }} taskerz. All rights reserved
              
            </p>
            
        </div>
    </div>
</body>
</html>