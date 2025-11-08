<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageComparisonController;
use App\Http\Controllers\AuthController;

Route::get('/', [\App\Http\Controllers\WelcomeController::class, 'index'])->name('welcome');
Route::get('/search', [\App\Http\Controllers\WelcomeController::class, 'index'])->name('search');
Route::get('/api/search', [\App\Http\Controllers\WelcomeController::class, 'searchApi'])->name('api.search');

// Public item viewing (no auth required)
Route::get('/item/{uploadId}', [\App\Http\Controllers\PublicItemController::class, 'show'])->name('public.item.show');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');

    // User Dashboard
    Route::get('/user/dashboard', [\App\Http\Controllers\UserController::class, 'dashboard'])->name('user.dashboard');

    // User Routes
    Route::get('/user/reported-items', function () {
        return view('user.reported-items');
    })->name('user.reported-items');

    Route::get('/user/claim-verify', function () {
        return view('user.claim-verify');
    });

        // Image Comparison (admin only - testing feature)
        Route::middleware(['admin'])->group(function () {
            Route::get('/image-comparison', function () {
                return view('image-comparison');
            });

    Route::get('/admin/reported-items', [\App\Http\Controllers\AdminController::class, 'reportedItems'])->name('admin.reported-items');
    Route::delete('/admin/reported-items/{uploadId}', [\App\Http\Controllers\AdminController::class, 'deleteItem'])->name('admin.delete-item');
    Route::post('/admin/reported-items/{uploadId}/restore', [\App\Http\Controllers\AdminController::class, 'restoreItem'])->name('admin.restore-item');
    Route::delete('/admin/reported-items/{uploadId}/force', [\App\Http\Controllers\AdminController::class, 'forceDeleteItem'])->name('admin.force-delete-item');
    Route::get('/admin/claim-verify', [\App\Http\Controllers\AdminController::class, 'claimVerify'])->name('admin.claim-verify');
    Route::get('/admin/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/users/{user}', [\App\Http\Controllers\AdminController::class, 'showUser'])->name('admin.users.show');
    Route::get('/admin/users/{user}/edit', [\App\Http\Controllers\AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [\App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::get('/admin/insights', [\App\Http\Controllers\AdminController::class, 'insights'])->name('admin.insights');
        Route::get('/admin/settings', [\App\Http\Controllers\AdminController::class, 'settings'])->name('admin.settings');
        Route::post('/admin/settings', [\App\Http\Controllers\AdminController::class, 'updateSettings'])->name('admin.settings.update');
        Route::post('/admin/settings/test-email', [\App\Http\Controllers\AdminController::class, 'testEmail'])->name('admin.settings.test-email');
        Route::get('/admin/settings/export-database', [\App\Http\Controllers\AdminController::class, 'exportDatabase'])->name('admin.settings.export-database');
        Route::post('/admin/settings/import-database', [\App\Http\Controllers\AdminController::class, 'importDatabase'])->name('admin.settings.import-database');
        Route::get('/admin/sponsors', [\App\Http\Controllers\Admin\SponsorController::class, 'index'])->name('admin.sponsors.index');
        Route::post('/admin/sponsors', [\App\Http\Controllers\Admin\SponsorController::class, 'store'])->name('admin.sponsors.store');
        Route::put('/admin/sponsors/{sponsor}', [\App\Http\Controllers\Admin\SponsorController::class, 'update'])->name('admin.sponsors.update');
        Route::delete('/admin/sponsors/{sponsor}', [\App\Http\Controllers\Admin\SponsorController::class, 'destroy'])->name('admin.sponsors.destroy');
        Route::post('/admin/sponsors/{id}/restore', [\App\Http\Controllers\Admin\SponsorController::class, 'restore'])->name('admin.sponsors.restore');
        Route::delete('/admin/sponsors/{id}/force', [\App\Http\Controllers\Admin\SponsorController::class, 'forceDelete'])->name('admin.sponsors.force-delete');
        Route::post('/admin/sponsors/toggle-show', [\App\Http\Controllers\Admin\SponsorController::class, 'toggleShow'])->name('admin.sponsors.toggle-show');
        });

        // User Items API Routes (moved from API routes for better session handling)
        Route::post('/api/user/items/upload', [\App\Http\Controllers\Api\UserItemController::class, 'uploadItems']);
        Route::get('/api/user/items', [\App\Http\Controllers\Api\UserItemController::class, 'getUserItems']);
        Route::match(['PUT', 'POST'], '/api/user/items/{uploadId}', [\App\Http\Controllers\Api\UserItemController::class, 'updateItem']);
        Route::delete('/api/user/items/{uploadId}', [\App\Http\Controllers\Api\UserItemController::class, 'deleteItem']);
        Route::post('/api/user/items/{uploadId}/restore', [\App\Http\Controllers\Api\UserItemController::class, 'restoreItem']);
        Route::delete('/api/user/items/{uploadId}/force', [\App\Http\Controllers\Api\UserItemController::class, 'forceDeleteItem']);
        Route::get('/api/user/items/trashed', [\App\Http\Controllers\Api\UserItemController::class, 'getTrashedItems']);
        Route::get('/api/user/items/other-users', [\App\Http\Controllers\Api\UserItemController::class, 'getOtherUsersItems']);
        Route::post('/api/user/items/{uploadId}/claim', [\App\Http\Controllers\Api\UserItemController::class, 'claimItem']);

        // User Profile Routes
        Route::get('/user/profile', [\App\Http\Controllers\UserProfileController::class, 'show'])->name('user.profile');
        Route::get('/user/profile/edit', [\App\Http\Controllers\UserProfileController::class, 'edit'])->name('user.profile.edit');
        Route::put('/user/profile', [\App\Http\Controllers\UserProfileController::class, 'update'])->name('user.profile.update');
        Route::post('/user/profile/avatar', [\App\Http\Controllers\UserProfileController::class, 'uploadAvatar'])->name('user.profile.avatar');

        // Chat Routes
        Route::get('/user/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('user.chat');
        Route::get('/user/chat/messages/{userId}', [\App\Http\Controllers\ChatController::class, 'getMessages'])->name('user.chat.messages');
        Route::post('/user/chat/send', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('user.chat.send');
        Route::get('/user/chat/unread-count', [\App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('user.chat.unread-count');
        Route::post('/user/chat/mark-read/{userId}', [\App\Http\Controllers\ChatController::class, 'markAsRead'])->name('user.chat.mark-read');
        Route::post('/user/chat/get-user-by-email', [\App\Http\Controllers\ChatController::class, 'getUserByEmail'])->name('user.chat.get-user-by-email');
    });

Route::get('/debug-test', function () {
    return view('debug-test');
});

// Debug route to check guest items
Route::get('/debug-guest-items', function (Illuminate\Http\Request $request) {
    $sessionData = [
        'session_id' => $request->session()->getId(),
        'has_guest_pending_item' => $request->session()->has('guest_pending_item'),
        'guest_pending_item' => $request->session()->get('guest_pending_item'),
        'all_session_keys' => array_keys($request->session()->all()),
    ];
    
    $user = Auth::user();
    $userItems = [];
    if ($user) {
        $userItems = \App\Models\ImageMetadata::where('uploader_email', $user->email)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'upload_id' => $item->upload_id,
                    'uploader_email' => $item->uploader_email,
                    'description' => $item->description,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                ];
            });
    }
    
    return response()->json([
        'session' => $sessionData,
        'user' => $user ? [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ] : null,
        'user_items' => $userItems,
        'total_items' => $userItems->count(),
    ]);
})->middleware('web');

// Guest posting routes
Route::get('/post', [\App\Http\Controllers\GuestItemController::class, 'showForm'])->name('guest.post.form');
Route::post('/post', [\App\Http\Controllers\GuestItemController::class, 'submit'])->name('guest.post.submit');

Route::post('/debug-upload', function (Illuminate\Http\Request $request) {
    $result = [
        'files_received' => $request->allFiles(),
        'has_image1' => $request->hasFile('image1'),
        'has_image2' => $request->hasFile('image2'),
        'image1_valid' => $request->file('image1') ? $request->file('image1')->isValid() : false,
        'image2_valid' => $request->file('image2') ? $request->file('image2')->isValid() : false,
        'image1_error' => $request->file('image1') ? $request->file('image1')->getError() : 'no file',
        'image2_error' => $request->file('image2') ? $request->file('image2')->getError() : 'no file',
        'image1_size' => $request->file('image1') ? $request->file('image1')->getSize() : 0,
        'image2_size' => $request->file('image2') ? $request->file('image2')->getSize() : 0,
        'php_upload_max_filesize' => ini_get('upload_max_filesize'),
        'php_post_max_size' => ini_get('post_max_size'),
    ];

    return response()->json($result);
});


// Email test route - Test using .env file directly (bypasses database settings)
Route::get('/test-email-env', function () {
    $email = request()->query('email', 'devjry@gmail.com'); // Default to devjry@gmail.com
    $clearCache = request()->query('clear', false); // Optional: ?clear=true to clear cache
    
    // Initialize config array
    $config = [];
    
    try {
        // Optionally clear config cache if requested
        if ($clearCache) {
            \Artisan::call('config:clear');
        }
        
        // Read directly from .env file (bypass database settings)
        $mailMailer = env('MAIL_MAILER', 'log');
        $mailHost = env('MAIL_HOST');
        $mailPort = env('MAIL_PORT', 587);
        $mailUsername = env('MAIL_USERNAME');
        $mailPassword = env('MAIL_PASSWORD');
        $mailEncryption = env('MAIL_ENCRYPTION', 'tls');
        $mailFromAddress = env('MAIL_FROM_ADDRESS');
        $mailFromName = env('MAIL_FROM_NAME');
        
        // Force config to use .env values (bypass database settings)
        config([
            'mail.default' => $mailMailer,
            'mail.mailers.smtp.host' => $mailHost,
            'mail.mailers.smtp.port' => $mailPort,
            'mail.mailers.smtp.username' => $mailUsername,
            'mail.mailers.smtp.password' => $mailPassword,
            'mail.mailers.smtp.encryption' => $mailEncryption === 'null' ? null : $mailEncryption,
            'mail.from.address' => $mailFromAddress,
            'mail.from.name' => $mailFromName,
        ]);
        
        // Get current config for display
        $config = [
            'mailer' => $mailMailer,
            'host' => $mailHost,
            'port' => $mailPort,
            'username' => $mailUsername ? substr($mailUsername, 0, 3) . '***' : null, // Mask username
            'password' => $mailPassword ? '***' : null, // Don't show password
            'encryption' => $mailEncryption,
            'from_address' => $mailFromAddress,
            'from_name' => $mailFromName,
            'source' => '.env file',
        ];
        
        // Check if mail is configured
        if ($mailMailer === 'log') {
            return response()->json([
                'success' => false,
                'message' => 'Email is set to "log" mode in .env. Change MAIL_MAILER=smtp to send actual emails.',
                'config' => $config,
                'help' => 'Add to .env: MAIL_MAILER=smtp'
            ], 400);
        }
        
        if ($mailMailer === 'smtp' && (empty($mailHost) || empty($mailUsername))) {
            return response()->json([
                'success' => false,
                'message' => 'SMTP configuration is incomplete in .env file.',
                'config' => $config,
                'help' => 'Make sure you have set MAIL_HOST, MAIL_USERNAME, and MAIL_PASSWORD in .env',
                'required' => [
                    'MAIL_MAILER=smtp',
                    'MAIL_HOST=smtp.gmail.com',
                    'MAIL_PORT=587',
                    'MAIL_USERNAME=your-email@gmail.com',
                    'MAIL_PASSWORD=your-app-password',
                    'MAIL_ENCRYPTION=tls',
                    'MAIL_FROM_ADDRESS="your-email@gmail.com"',
                    'MAIL_FROM_NAME="Your App Name"'
                ]
            ], 400);
        }
        
        // Send test email using .env settings
        \Mail::to($email)->send(new \App\Mail\TestEmailNotification());
        
        return response()->json([
            'success' => true,
            'message' => "Test email sent successfully to {$email} using .env settings!",
            'config' => $config,
            'note' => 'Check your inbox (and spam folder). If using Gmail, make sure you\'re using an App Password, not your regular password.',
            'tip' => 'If email not received, try: /test-email-env?email=your-email@example.com&clear=true'
        ]);
        
    } catch (\Exception $e) {
        $errorMessage = $e->getMessage();
        $helpMessage = '';
        
        // Provide helpful error messages
        if (str_contains($errorMessage, '535') || str_contains($errorMessage, 'BadCredentials') || str_contains($errorMessage, 'Username and Password not accepted')) {
            $helpMessage = "\n\n❌ Gmail Authentication Error:\n" .
                "Gmail rejected your username/password from .env file.\n\n" .
                "✅ Solution:\n" .
                "1. Enable 2-Factor Authentication on your Google account\n" .
                "2. Go to: https://myaccount.google.com/apppasswords\n" .
                "3. Generate a new App Password for 'Mail'\n" .
                "4. Update .env file:\n" .
                "   MAIL_MAILER=smtp\n" .
                "   MAIL_HOST=smtp.gmail.com\n" .
                "   MAIL_PORT=587\n" .
                "   MAIL_USERNAME=your-email@gmail.com\n" .
                "   MAIL_PASSWORD=your-16-character-app-password\n" .
                "   MAIL_ENCRYPTION=tls\n" .
                "   MAIL_FROM_ADDRESS=\"your-email@gmail.com\"\n" .
                "   MAIL_FROM_NAME=\"Your App Name\"\n" .
                "5. Clear cache: php artisan config:clear\n" .
                "6. Test again: /test-email-env?email=your-email@gmail.com&clear=true\n\n" .
                "⚠️ Important: Use App Password, NOT your regular Gmail password!";
        } elseif (str_contains($errorMessage, 'Connection') || str_contains($errorMessage, 'timeout')) {
            $helpMessage = "\n\n❌ Connection Error:\n" .
                "Cannot connect to SMTP server.\n\n" .
                "✅ Check:\n" .
                "1. MAIL_HOST is correct (e.g., smtp.gmail.com)\n" .
                "2. MAIL_PORT is correct (587 for TLS, 465 for SSL)\n" .
                "3. Firewall allows outbound connections\n" .
                "4. Internet connection is working";
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to send test email: ' . $errorMessage . $helpMessage,
            'config' => $config,
            'error_details' => $errorMessage
        ], 500);
    }
})->name('test.email.env');

// Email test route (old - keeps for compatibility)
Route::get('/test-email', function () {
    $results = [];

    // Test 1: Check configuration
    $results['config'] = [
        'mail_driver' => config('mail.default'),
        'mail_from' => config('mail.from.address'),
        'similarity_enabled' => config('similarity.enabled'),
        'visual_threshold' => config('similarity.thresholds.visual'),
    ];

    // Test 2: Check database
    $results['database'] = [
        'total_images' => \App\Models\ImageMetadata::count(),
        'images_with_email' => \App\Models\ImageMetadata::whereNotNull('uploader_email')->count(),
    ];

    // Test 3: Test email template
    try {
        $testData = [
            'email' => 'test@example.com',
            'similar_images' => [
                [
                    'image' => (object) [
                        'original_name' => 'test-image.jpg',
                        'description' => 'Test image description',
                        'tags' => ['test', 'image'],
                        'created_at' => now()
                    ],
                    'visual_similarity' => 0.85,
                    'text_similarity' => 0.75,
                    'overall_similarity' => 0.82
                ]
            ],
            'new_image_metadata' => [
                'description' => 'New test image',
                'tags' => ['new', 'test'],
                'original_name' => 'new-test.jpg'
            ],
            'total_similar' => 1
        ];

        $mail = new \App\Mail\SimilarImageNotification($testData);
        $results['email_template'] = [
            'status' => 'success',
            'subject' => $mail->envelope()->subject,
            'from' => $mail->envelope()->from->address,
        ];
    } catch (Exception $e) {
        $results['email_template'] = [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }

    // Test 4: Send test email to devjry@gmail.com
    try {
        $devjryTestData = [
            'email' => 'devjry@gmail.com',
            'similar_images' => [
                [
                    'image' => (object) [
                        'original_name' => 'landscape-photo.jpg',
                        'description' => 'Beautiful mountain landscape with trees',
                        'tags' => ['landscape', 'mountain', 'nature'],
                        'created_at' => now()
                    ],
                    'visual_similarity' => 0.87,
                    'text_similarity' => 0.82,
                    'overall_similarity' => 0.85
                ]
            ],
            'new_image_metadata' => [
                'description' => 'New landscape photo uploaded by another user',
                'tags' => ['landscape', 'nature', 'outdoor'],
                'original_name' => 'new-landscape-upload.jpg'
            ],
            'total_similar' => 1
        ];

        \Illuminate\Support\Facades\Mail::to('devjry@gmail.com')->send(new \App\Mail\SimilarImageNotification($devjryTestData));
        $results['email_sending'] = [
            'status' => 'success',
            'message' => 'Test email sent successfully to devjry@gmail.com (check logs if using log driver)',
            'recipient' => 'devjry@gmail.com'
        ];
    } catch (Exception $e) {
        $results['email_sending'] = [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }

    // Test 5: Check recent logs
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        $logContent = file_get_contents($logFile);
        $results['logs'] = [
            'file_exists' => true,
            'email_notifications' => substr_count($logContent, 'Similarity notification sent'),
            'recent_size' => strlen(substr($logContent, -1000))
        ];
    } else {
        $results['logs'] = [
            'file_exists' => false
        ];
    }

    return response()->json([
        'success' => true,
        'message' => 'Email system test completed',
        'results' => $results,
        'timestamp' => now()->toISOString()
    ]);
});

// Image comparison routes
Route::post('/api/compare-images', [ImageComparisonController::class, 'compare']);
Route::post('/api/compare-urls', [ImageComparisonController::class, 'compareUrls']);
