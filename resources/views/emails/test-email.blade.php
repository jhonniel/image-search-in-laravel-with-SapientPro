<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email - FindITFast</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .success-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Email Test Successful!</h1>
    </div>
    <div class="content">
        <div style="text-align: center;">
            <div class="success-icon">🎉</div>
            <h2>Congratulations!</h2>
            <p>Your email configuration is working correctly. This is a test email from <strong>FindITFast</strong>.</p>
            
            <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea;">
                <h3 style="margin-top: 0; color: #667eea;">Email Configuration Details</h3>
                <p><strong>Sent at:</strong> {{ now()->format('F d, Y \a\t h:i A') }}</p>
                <p><strong>From:</strong> {{ config('mail.from.address') }}</p>
                <p><strong>From Name:</strong> {{ config('mail.from.name') }}</p>
                <p><strong>Mail Driver:</strong> {{ config('mail.default') }}</p>
            </div>
            
            <p>If you received this email, it means:</p>
            <ul style="text-align: left; display: inline-block;">
                <li>✅ SMTP configuration is correct</li>
                <li>✅ Email credentials are valid</li>
                <li>✅ Server can send emails</li>
                <li>✅ Notification system is ready</li>
            </ul>
            
            <p style="margin-top: 30px;">
                <strong>Your email notifications are now configured and ready to use!</strong>
            </p>
        </div>
    </div>
    <div class="footer">
        <p>This is an automated test email from FindITFast</p>
        <p>If you did not request this test, please ignore this email.</p>
    </div>
</body>
</html>



