<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email - FindITFast</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #8B5CF6 0%, #EC4899 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">FindITFast</h1>
    </div>
    
    <div style="background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e5e7eb;">
        <h2 style="color: #1f2937; margin-top: 0;">Email Configuration Test</h2>
        <p>This is a test email to verify that your email configuration is working correctly.</p>
        <p>If you received this email, your SMTP settings are configured properly!</p>
        
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #8B5CF6;">
            <p style="margin: 0;"><strong>Test Details:</strong></p>
            <p style="margin: 5px 0;">Sent at: {{ now()->format('Y-m-d H:i:s') }}</p>
            <p style="margin: 5px 0;">From: {{ config('mail.from.address') }}</p>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 20px; color: #6b7280; font-size: 12px;">
        <p>&copy; {{ date('Y') }} FindITFast. All rights reserved.</p>
    </div>
</body>
</html>

