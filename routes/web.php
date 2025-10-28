<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageComparisonController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    });

    // User Dashboard
    Route::get('/user/dashboard', function () {
        return view('user.dashboard');
    });

    // User Routes
    Route::get('/user/reported-items', function () {
        return view('user.reported-items');
    });

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
    Route::get('/admin/claim-verify', [\App\Http\Controllers\AdminController::class, 'claimVerify'])->name('admin.claim-verify');
    Route::get('/admin/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/users/{user}', [\App\Http\Controllers\AdminController::class, 'showUser'])->name('admin.users.show');
    Route::get('/admin/users/{user}/edit', [\App\Http\Controllers\AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [\App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.users.update');
        });

        // User Items API Routes (moved from API routes for better session handling)
        Route::post('/api/user/items/upload', [\App\Http\Controllers\Api\UserItemController::class, 'uploadItems']);
        Route::get('/api/user/items', [\App\Http\Controllers\Api\UserItemController::class, 'getUserItems']);
        Route::delete('/api/user/items/{uploadId}', [\App\Http\Controllers\Api\UserItemController::class, 'deleteItem']);
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


// Email test route
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
