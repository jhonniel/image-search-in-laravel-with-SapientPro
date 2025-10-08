# Email Configuration Guide

This guide explains how to configure email notifications for the similarity detection system using free email services.

## Quick Setup

### 1. For Development/Testing (Recommended)
Set in your `.env` file:
```env
MAIL_MAILER=log
SIMILARITY_ENABLED=true
```
This will save all emails to `storage/logs/laravel.log` for testing.

### 2. For Production with Free Email Services

#### Gmail (Recommended)
1. Enable 2-Factor Authentication on your Gmail account
2. Generate an App Password:
   - Go to Google Account settings
   - Security → 2-Step Verification → App passwords
   - Generate password for "Mail"
3. Update your `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Your App Name"
```

#### Outlook/Hotmail
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_USERNAME=your-email@outlook.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@outlook.com
MAIL_FROM_NAME="Your App Name"
```

#### Yahoo Mail
1. Enable 2-Factor Authentication
2. Generate an App Password
3. Update your `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mail.yahoo.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yahoo.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@yahoo.com
MAIL_FROM_NAME="Your App Name"
```

#### Mailtrap (Free Testing Service)
1. Sign up at [mailtrap.io](https://mailtrap.io)
2. Get your credentials from the inbox
3. Update your `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=test@yourdomain.com
MAIL_FROM_NAME="Your App Name"
```

## Similarity Configuration

### Environment Variables
Add these to your `.env` file to customize similarity detection:

```env
# Enable/disable similarity checking
SIMILARITY_ENABLED=true

# Similarity thresholds (0.0 to 1.0)
SIMILARITY_VISUAL_THRESHOLD=0.7
SIMILARITY_TEXT_THRESHOLD=0.5

# Weight distribution for overall similarity
SIMILARITY_TEXT_WEIGHT=0.3
SIMILARITY_VISUAL_WEIGHT=0.7

# Email settings
SIMILARITY_FROM_NAME="Image Search System"
SIMILARITY_FROM_ADDRESS="noreply@yourdomain.com"
SIMILARITY_SUBJECT_PREFIX="🔍 Similar Images Found"

# Algorithm weights for text similarity
SIMILARITY_JARO_WINKLER_WEIGHT=0.4
SIMILARITY_LEVENSHTEIN_WEIGHT=0.3
SIMILARITY_WORD_OVERLAP_WEIGHT=0.3

# Logging
SIMILARITY_LOGGING_ENABLED=true
SIMILARITY_LOG_LEVEL=info
```

### Configuration File
The system uses `config/similarity.php` for advanced configuration. You can modify this file directly or use environment variables.

## Testing Email Configuration

### 1. Test with Log Driver
```env
MAIL_MAILER=log
```
Check `storage/logs/laravel.log` for email content.

### 2. Test with SMTP
```env
MAIL_MAILER=smtp
# ... your SMTP settings
```
Upload images and check your email inbox.

### 3. Test with Mailtrap
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
# ... mailtrap settings
```
Check your Mailtrap inbox for test emails.

## Troubleshooting

### Common Issues

1. **"Connection could not be established"**
   - Check your SMTP credentials
   - Verify the host and port
   - Ensure 2FA is enabled and app password is used

2. **"Authentication failed"**
   - Use app passwords, not regular passwords
   - Check username format (full email address)

3. **"SSL/TLS connection failed"**
   - Set `MAIL_ENCRYPTION=tls` for port 587
   - Set `MAIL_ENCRYPTION=ssl` for port 465

4. **Emails not sending**
   - Check `SIMILARITY_ENABLED=true`
   - Verify email configuration
   - Check Laravel logs for errors

### Debug Mode
Enable debug logging:
```env
LOG_LEVEL=debug
SIMILARITY_LOGGING_ENABLED=true
```

## Free Email Service Limits

| Service | Daily Limit | Notes |
|---------|-------------|-------|
| Gmail | 500 emails/day | Requires app password |
| Outlook | 300 emails/day | Free tier limit |
| Yahoo | 500 emails/day | Requires app password |
| Mailtrap | 100 emails/month | Free tier |

## Security Best Practices

1. **Never commit credentials** to version control
2. **Use app passwords** instead of regular passwords
3. **Enable 2FA** on email accounts
4. **Use environment variables** for all sensitive data
5. **Regularly rotate** app passwords

## Production Recommendations

1. **Use a dedicated email service** like SendGrid, Mailgun, or AWS SES
2. **Set up proper DNS records** (SPF, DKIM, DMARC)
3. **Monitor email delivery** and bounce rates
4. **Implement rate limiting** for email sending
5. **Use queue workers** for bulk email sending

## Support

If you encounter issues:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify your `.env` configuration
3. Test with the log driver first
4. Check your email service provider's documentation
