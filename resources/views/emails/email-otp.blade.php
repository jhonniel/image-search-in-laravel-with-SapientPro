<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your email</title>
</head>
<body style="margin:0;padding:0;background:#f5f4fe;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f4fe;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:480px;background:#ffffff;border-radius:16px;padding:32px;box-shadow:0 8px 24px rgba(33,58,143,0.08);">
                    <tr>
                        <td>
                            <h1 style="margin:0 0 8px;font-size:28px;line-height:1.2;">
                                <span style="color:#7c3aed;">FindIT</span><span style="color:#ec4899;">Fast</span>
                            </h1>
                            <p style="margin:0 0 24px;color:#6b7280;font-size:15px;">Verify your email to finish creating your account.</p>

                            <p style="margin:0 0 8px;font-size:15px;">Hi {{ $user->name }},</p>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.5;">
                                Use this one-time code to verify <strong>{{ $user->email }}</strong>:
                            </p>

                            <div style="margin:0 0 24px;padding:18px 16px;background:#f5f4fe;border-radius:12px;text-align:center;">
                                <span style="font-size:32px;letter-spacing:8px;font-weight:700;color:#213a8f;">{{ $otp }}</span>
                            </div>

                            <p style="margin:0 0 8px;font-size:14px;color:#6b7280;">
                                This code expires in <strong>{{ $expiresMinutes }} minutes</strong>.
                            </p>
                            <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.5;">
                                If you did not create a FindITFast account, you can ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
