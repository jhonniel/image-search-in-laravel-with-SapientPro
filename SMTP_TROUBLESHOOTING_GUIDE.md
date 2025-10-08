# 🔧 SMTP Troubleshooting Guide

## ❌ Problem: SMTP Configured But Emails Not Sending

If you've configured SMTP but emails still can't be sent to devjry@gmail.com, here are the most common issues and solutions:

## 🔍 Common SMTP Issues

### **1. Authentication Failed**
**Error**: "Authentication failed" or "Invalid credentials"

**Solutions**:
- **Gmail**: Use App Password instead of regular password
  - Enable 2-Factor Authentication
  - Generate App Password: Google Account → Security → 2-Step Verification → App passwords
  - Use the app password in `MAIL_PASSWORD`
- **Outlook**: Check if account requires app password
- **Yahoo**: Enable 2FA and generate app password

### **2. Connection Refused**
**Error**: "Connection refused" or "Could not connect to SMTP server"

**Solutions**:
- Check host and port settings
- Verify firewall settings
- Try different ports:
  - Port 587 (TLS)
  - Port 465 (SSL)
  - Port 25 (unencrypted, not recommended)

### **3. SSL/TLS Errors**
**Error**: "SSL/TLS error" or "Certificate verification failed"

**Solutions**:
- For port 587: Use `MAIL_ENCRYPTION=tls`
- For port 465: Use `MAIL_ENCRYPTION=ssl`
- Try disabling encryption: `MAIL_ENCRYPTION=null`

### **4. Timeout Issues**
**Error**: "Connection timeout" or "Operation timed out"

**Solutions**:
- Check network connection
- Verify SMTP server is accessible
- Try different SMTP server
- Increase timeout settings

## 🔧 Step-by-Step Troubleshooting

### **Step 1: Verify SMTP Configuration**

Check your `.env` file has these settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="Image Search System"
```

### **Step 2: Test SMTP Connection**

Create a test script to verify SMTP connection:

```php
<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
        config('mail.mailers.smtp.host'),
        config('mail.mailers.smtp.port'),
        config('mail.mailers.smtp.encryption') === 'tls'
    );
    
    if (config('mail.mailers.smtp.username')) {
        $transport->setUsername(config('mail.mailers.smtp.username'));
    }
    
    if (config('mail.mailers.smtp.password')) {
        $transport->setPassword(config('mail.mailers.smtp.password'));
    }
    
    echo "✅ SMTP connection successful\n";
} catch (Exception $e) {
    echo "❌ SMTP connection failed: " . $e->getMessage() . "\n";
}
?>
```

### **Step 3: Test Email Sending**

```php
<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    \Illuminate\Support\Facades\Mail::raw('Test email', function($message) {
        $message->to('devjry@gmail.com')
                ->subject('SMTP Test');
    });
    echo "✅ Email sent successfully\n";
} catch (Exception $e) {
    echo "❌ Email sending failed: " . $e->getMessage() . "\n";
}
?>
```

## 📧 Gmail SMTP Configuration

### **Correct Gmail Settings**:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="Image Search System"
```

### **Gmail Setup Steps**:
1. **Enable 2-Factor Authentication** on your Gmail account
2. **Generate App Password**:
   - Go to Google Account settings
   - Security → 2-Step Verification → App passwords
   - Generate password for "Mail"
   - Use this 16-character password in `MAIL_PASSWORD`
3. **Save .env file**
4. **Clear cache**: `php artisan config:clear`
5. **Restart server**: `php artisan serve`

## 📧 Outlook SMTP Configuration

### **Correct Outlook Settings**:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_USERNAME=your-email@outlook.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@outlook.com"
MAIL_FROM_NAME="Image Search System"
```

## 📧 Yahoo SMTP Configuration

### **Correct Yahoo Settings**:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mail.yahoo.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yahoo.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@yahoo.com"
MAIL_FROM_NAME="Image Search System"
```

### **Yahoo Setup Steps**:
1. **Enable 2-Factor Authentication**
2. **Generate App Password**
3. **Use app password in `MAIL_PASSWORD`**

## 🔍 Debug Commands

### **Check Current Configuration**:
```bash
php artisan config:show mail
```

### **Clear Configuration Cache**:
```bash
php artisan config:clear
```

### **Test Email Sending**:
```bash
php artisan tinker
# Then run:
Mail::raw('Test', function($message) {
    $message->to('devjry@gmail.com')->subject('Test');
});
```

## 🧪 Alternative Testing Methods

### **Method 1: Web Interface**
1. Visit: `http://localhost:8001/api-email-test.html`
2. Click: "Send Test Email to devjry@gmail.com"
3. Check: devjry@gmail.com inbox

### **Method 2: Direct API**
1. Visit: `http://localhost:8001/test-email`
2. Check: JSON response for errors
3. Verify: Email in devjry@gmail.com inbox

### **Method 3: Upload Test**
1. Visit: `http://localhost:8001/image-comparison`
2. Upload images with email addresses
3. Check: devjry@gmail.com inbox for notifications

## 📋 Checklist

- [ ] SMTP host is correct
- [ ] SMTP port is correct (587 for TLS, 465 for SSL)
- [ ] Username is correct
- [ ] Password is correct (use app password for Gmail/Yahoo)
- [ ] Encryption matches port (tls for 587, ssl for 465)
- [ ] 2-Factor Authentication is enabled (for Gmail/Yahoo)
- [ ] App password is generated and used
- [ ] .env file is saved
- [ ] Configuration cache is cleared
- [ ] Server is restarted

## 🎯 Quick Fix for Gmail

If you're using Gmail and emails aren't sending:

1. **Enable 2FA** on Gmail
2. **Generate App Password**:
   - Google Account → Security → 2-Step Verification → App passwords
   - Generate password for "Mail"
3. **Update .env**:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-16-character-app-password
   MAIL_ENCRYPTION=tls
   ```
4. **Clear cache**: `php artisan config:clear`
5. **Restart server**: `php artisan serve`
6. **Test**: Visit `http://localhost:8001/test-email`

## 📧 Expected Results

After fixing SMTP configuration:
- ✅ Emails will be sent to devjry@gmail.com
- ✅ Similarity notifications will work
- ✅ All email features will function properly
- ✅ devjry@gmail.com will receive emails in inbox

## 🔍 Still Having Issues?

If emails still aren't sending after following this guide:

1. **Check error logs**: `tail -f storage/logs/laravel.log`
2. **Verify SMTP settings** with your email provider
3. **Try different email service** (Gmail, Outlook, Yahoo)
4. **Check network/firewall settings**
5. **Contact your email provider** for SMTP support

**The most common issue is using regular password instead of app password for Gmail/Yahoo!**
