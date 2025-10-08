<?php

// Final email test for devjry@gmail.com
echo "📧 FINAL EMAIL TEST - Sending to devjry@gmail.com\n";
echo "=================================================\n\n";

try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "✅ Laravel loaded successfully\n\n";

    // Check configuration
    echo "📋 Configuration:\n";
    echo "MAIL_MAILER: " . config('mail.default') . "\n";
    echo "MAIL_FROM: " . config('mail.from.address') . "\n";
    echo "SIMILARITY_ENABLED: " . (config('similarity.enabled') ? 'true' : 'false') . "\n\n";

    // Test 1: Send similarity notification email
    echo "📧 Test 1: Sending Similarity Notification Email\n";
    echo "------------------------------------------------\n";

    $testData = [
        'email' => 'devjry@gmail.com',
        'similar_images' => [
            [
                'image' => (object) [
                    'original_name' => 'mountain-landscape.jpg',
                    'description' => 'Beautiful mountain landscape with snow-capped peaks and crystal clear lake',
                    'tags' => ['landscape', 'mountain', 'nature', 'snow', 'lake'],
                    'created_at' => now()
                ],
                'visual_similarity' => 0.92,
                'text_similarity' => 0.88,
                'overall_similarity' => 0.90
            ],
            [
                'image' => (object) [
                    'original_name' => 'forest-scene.jpg',
                    'description' => 'Similar forest landscape with mountains in the background',
                    'tags' => ['forest', 'landscape', 'mountain', 'green', 'nature'],
                    'created_at' => now()->subDays(1)
                ],
                'visual_similarity' => 0.85,
                'text_similarity' => 0.82,
                'overall_similarity' => 0.84
            ]
        ],
        'new_image_metadata' => [
            'description' => 'New landscape photo uploaded by another user - very similar to your images',
            'tags' => ['landscape', 'nature', 'outdoor', 'scenic', 'mountain'],
            'original_name' => 'new-similar-landscape.jpg'
        ],
        'total_similar' => 2
    ];

    $mail = new \App\Mail\SimilarImageNotification($testData);
    \Illuminate\Support\Facades\Mail::to('devjry@gmail.com')->send($mail);

    echo "✅ Similarity notification email sent to devjry@gmail.com\n";
    echo "Subject: " . $mail->envelope()->subject . "\n";
    echo "From: " . $mail->envelope()->from->address . "\n\n";

    // Test 2: Send simple test email
    echo "📧 Test 2: Sending Simple Test Email\n";
    echo "------------------------------------\n";

    \Illuminate\Support\Facades\Mail::raw('This is a simple test email from the Image Search System. If you receive this, the email system is working correctly!', function($message) {
        $message->to('devjry@gmail.com')
                ->subject('🧪 Simple Test Email - Image Search System');
    });

    echo "✅ Simple test email sent to devjry@gmail.com\n\n";

    // Test 3: Send HTML test email
    echo "📧 Test 3: Sending HTML Test Email\n";
    echo "----------------------------------\n";

    \Illuminate\Support\Facades\Mail::html('<h1>Test Email</h1><p>This is an HTML test email from the Image Search System.</p><p>If you receive this, the email system is working correctly!</p>', function($message) {
        $message->to('devjry@gmail.com')
                ->subject('🧪 HTML Test Email - Image Search System');
    });

    echo "✅ HTML test email sent to devjry@gmail.com\n\n";

    // Summary
    echo "📊 Test Summary:\n";
    echo "================\n";
    echo "Recipient: devjry@gmail.com\n";
    echo "Emails sent: 3\n";
    echo "1. Similarity notification (HTML template)\n";
    echo "2. Simple text email\n";
    echo "3. HTML test email\n\n";

    if (config('mail.default') === 'log') {
        echo "📝 Using LOG driver - all emails saved to storage/logs/laravel.log\n";
        echo "📍 To view emails: tail -100 storage/logs/laravel.log\n";
    } else {
        echo "📧 Using SMTP driver - emails sent to devjry@gmail.com inbox\n";
        echo "📍 Check devjry@gmail.com inbox for all 3 emails\n";
    }

    echo "\n🎉 All test emails sent successfully to devjry@gmail.com!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\nTest completed!\n";
