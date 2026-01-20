<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageComparisonController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;

Route::get('/', [\App\Http\Controllers\WelcomeController::class, 'index'])->name('welcome');
Route::get('/search', [\App\Http\Controllers\WelcomeController::class, 'index'])->name('search');
Route::get('/api/search', [\App\Http\Controllers\WelcomeController::class, 'searchApi'])->name('api.search');
Route::post('/contact', [\App\Http\Controllers\ContactRequestController::class, 'store'])->name('contact.store');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms-and-conditions', [PageController::class, 'terms'])->name('terms');

// Public Contributors Page
Route::get('/contributors', [\App\Http\Controllers\ContributorController::class, 'index'])->name('contributors.public');

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
    // Unified Dashboard - redirects based on role
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return app(\App\Http\Controllers\AdminController::class)->dashboard();
        }
        return app(\App\Http\Controllers\UserController::class)->dashboard();
    })->name('dashboard');

    // User Routes
    Route::get('/reported-items', function () {
        // Get enabled cities from settings
        $enabledCitiesJson = \App\Models\Setting::get('enabled_cities', '[]');
        $enabledCities = json_decode($enabledCitiesJson, true) ?? [];
        sort($enabledCities);

        // Get enabled provinces from settings
        $enabledProvincesJson = \App\Models\Setting::get('enabled_provinces', '[]');
        $enabledProvinces = json_decode($enabledProvincesJson, true) ?? [];
        sort($enabledProvinces);

        // Get field visibility and requirement settings
        $enableProvinceField = \App\Models\Setting::get('enable_province_field', true);
        $provinceFieldRequired = \App\Models\Setting::get('province_field_required', true);
        $enableCityField = \App\Models\Setting::get('enable_city_field', true);
        $cityFieldRequired = \App\Models\Setting::get('city_field_required', true);

        return view('user.reported-items', compact('enabledCities', 'enabledProvinces',
            'enableProvinceField', 'provinceFieldRequired', 'enableCityField', 'cityFieldRequired'));
    })->name('reported-items');

    Route::get('/claim-verify', function () {
        return view('user.claim-verify');
    })->name('claim-verify');

    // Pending Claims Management
    Route::get('/pending-claims', [\App\Http\Controllers\UserController::class, 'pendingClaims'])->name('pending-claims');
    Route::post('/claims/{uploadId}/verify', [\App\Http\Controllers\UserController::class, 'verifyClaim'])->name('claims.verify');
    Route::post('/claims/{uploadId}/reject', [\App\Http\Controllers\UserController::class, 'rejectClaim'])->name('claims.reject');

    // Admin routes (admin only)
    Route::middleware(['admin'])->group(function () {
        // Image Comparison (admin only - testing feature)
        Route::get('/image-comparison', function () {
            return view('image-comparison');
        });

        Route::get('/reported-items-admin', [\App\Http\Controllers\AdminController::class, 'reportedItems'])->name('reported-items-admin');
    Route::delete('/reported-items-admin/{uploadId}', [\App\Http\Controllers\AdminController::class, 'deleteItem'])->name('delete-item');
    Route::post('/reported-items-admin/{uploadId}/restore', [\App\Http\Controllers\AdminController::class, 'restoreItem'])->name('restore-item');
    Route::delete('/reported-items-admin/{uploadId}/force', [\App\Http\Controllers\AdminController::class, 'forceDeleteItem'])->name('force-delete-item');
    Route::get('/claimed', [\App\Http\Controllers\AdminController::class, 'claimVerify'])->name('claimed');
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}', [\App\Http\Controllers\AdminController::class, 'showUser'])->name('users.show');
    Route::get('/users/{user}/edit', [\App\Http\Controllers\AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [\App\Http\Controllers\AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}/toggle-verification', [\App\Http\Controllers\AdminController::class, 'toggleVerification'])->name('users.toggle-verification');
    Route::get('/insights', [\App\Http\Controllers\AdminController::class, 'insights'])->name('insights');
        Route::get('/settings', [\App\Http\Controllers\AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\AdminController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/test-email', [\App\Http\Controllers\AdminController::class, 'testEmail'])->name('settings.test-email');
        Route::get('/settings/export-database', [\App\Http\Controllers\AdminController::class, 'exportDatabase'])->name('settings.export-database');
        Route::post('/settings/import-database', [\App\Http\Controllers\AdminController::class, 'importDatabase'])->name('settings.import-database');
        Route::get('/sponsors', [\App\Http\Controllers\Admin\SponsorController::class, 'index'])->name('sponsors.index');
        Route::post('/sponsors', [\App\Http\Controllers\Admin\SponsorController::class, 'store'])->name('sponsors.store');
        Route::put('/sponsors/{sponsor}', [\App\Http\Controllers\Admin\SponsorController::class, 'update'])->name('sponsors.update');
        Route::delete('/sponsors/{sponsor}', [\App\Http\Controllers\Admin\SponsorController::class, 'destroy'])->name('sponsors.destroy');
        Route::post('/sponsors/{id}/restore', [\App\Http\Controllers\Admin\SponsorController::class, 'restore'])->name('sponsors.restore');
        Route::delete('/sponsors/{id}/force', [\App\Http\Controllers\Admin\SponsorController::class, 'forceDelete'])->name('sponsors.force-delete');
        Route::post('/sponsors/toggle-show', [\App\Http\Controllers\Admin\SponsorController::class, 'toggleShow'])->name('sponsors.toggle-show');

        // Contributors Management (Admin)
        Route::get('/admin/contributors', [\App\Http\Controllers\Admin\ContributorController::class, 'index'])->name('contributors.index');
        Route::post('/admin/contributors', [\App\Http\Controllers\Admin\ContributorController::class, 'store'])->name('contributors.store');
        Route::put('/admin/contributors/{contributor}', [\App\Http\Controllers\Admin\ContributorController::class, 'update'])->name('contributors.update');
        Route::delete('/admin/contributors/{contributor}', [\App\Http\Controllers\Admin\ContributorController::class, 'destroy'])->name('contributors.destroy');
        Route::post('/admin/contributors/{id}/restore', [\App\Http\Controllers\Admin\ContributorController::class, 'restore'])->name('contributors.restore');
        Route::delete('/admin/contributors/{id}/force', [\App\Http\Controllers\Admin\ContributorController::class, 'forceDelete'])->name('contributors.force-delete');

        // Contact Requests
        Route::get('/contact-requests', [\App\Http\Controllers\Admin\ContactRequestController::class, 'index'])->name('contact-requests.index');
        Route::put('/contact-requests/{contactRequest}', [\App\Http\Controllers\Admin\ContactRequestController::class, 'update'])->name('contact-requests.update');
        Route::post('/contact-requests/help-section', [\App\Http\Controllers\Admin\ContactRequestController::class, 'upsertHelpSection'])->name('contact-requests.help.upsert');
        Route::delete('/contact-requests/help-section/{section}', [\App\Http\Controllers\Admin\ContactRequestController::class, 'deleteHelpSection'])->name('contact-requests.help.delete');

        // Review Questions Management
        Route::get('/review-questions', [\App\Http\Controllers\Admin\ReviewQuestionController::class, 'index'])->name('review-questions.index');
        Route::post('/review-questions', [\App\Http\Controllers\Admin\ReviewQuestionController::class, 'store'])->name('review-questions.store');
        Route::put('/review-questions/{reviewQuestion}', [\App\Http\Controllers\Admin\ReviewQuestionController::class, 'update'])->name('review-questions.update');
        Route::delete('/review-questions/{reviewQuestion}', [\App\Http\Controllers\Admin\ReviewQuestionController::class, 'destroy'])->name('review-questions.destroy');
        Route::delete('/review-questions/reset-user/{userId}', [\App\Http\Controllers\Admin\ReviewQuestionController::class, 'resetUserReviews'])->name('review-questions.reset-user');

        // Rewards Management
        Route::get('/rewards', [\App\Http\Controllers\Admin\RewardController::class, 'index'])->name('rewards.index');
        Route::get('/rewards/create', [\App\Http\Controllers\Admin\RewardController::class, 'create'])->name('rewards.create');
        Route::post('/rewards', [\App\Http\Controllers\Admin\RewardController::class, 'store'])->name('rewards.store');
        Route::get('/rewards/send', [\App\Http\Controllers\Admin\RewardController::class, 'showSendForm'])->name('rewards.send');
        Route::post('/rewards/send', [\App\Http\Controllers\Admin\RewardController::class, 'send'])->name('rewards.send.post');
        Route::post('/rewards/auto-assign', [\App\Http\Controllers\Admin\RewardController::class, 'checkAutoAssign'])->name('rewards.auto-assign');
        Route::delete('/rewards/{id}', [\App\Http\Controllers\Admin\RewardController::class, 'destroy'])->name('rewards.destroy');
    });

    // User Items API Routes (moved from API routes for better session handling)
    Route::post('/api/items/upload', [\App\Http\Controllers\Api\UserItemController::class, 'uploadItems']);
    Route::get('/api/items', [\App\Http\Controllers\Api\UserItemController::class, 'getUserItems']);
    Route::match(['PUT', 'POST'], '/api/items/{uploadId}', [\App\Http\Controllers\Api\UserItemController::class, 'updateItem']);
    Route::delete('/api/items/{uploadId}', [\App\Http\Controllers\Api\UserItemController::class, 'deleteItem']);
    Route::post('/api/items/{uploadId}/restore', [\App\Http\Controllers\Api\UserItemController::class, 'restoreItem']);
    Route::delete('/api/items/{uploadId}/force', [\App\Http\Controllers\Api\UserItemController::class, 'forceDeleteItem']);
    Route::get('/api/items/trashed', [\App\Http\Controllers\Api\UserItemController::class, 'getTrashedItems']);
    Route::get('/api/items/other-users', [\App\Http\Controllers\Api\UserItemController::class, 'getOtherUsersItems']);
    Route::post('/api/items/{uploadId}/claim', [\App\Http\Controllers\Api\UserItemController::class, 'claimItem']);
    Route::post('/api/items/{uploadId}/cancel-claim', [\App\Http\Controllers\Api\UserItemController::class, 'cancelClaim']);

    // User Profile Routes
    Route::get('/profile', [\App\Http\Controllers\UserProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [\App\Http\Controllers\UserProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\UserProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [\App\Http\Controllers\UserProfileController::class, 'uploadAvatar'])->name('profile.avatar');

    // Chat Routes
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat');
    Route::get('/chat/messages/{userId}', [\App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/unread-count', [\App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('chat.unread-count');
    Route::post('/chat/mark-read/{userId}', [\App\Http\Controllers\ChatController::class, 'markAsRead'])->name('chat.mark-read');
    Route::post('/chat/get-user-by-email', [\App\Http\Controllers\ChatController::class, 'getUserByEmail'])->name('chat.get-user-by-email');
    Route::post('/chat/image-view/{messageId}', [\App\Http\Controllers\ChatController::class, 'recordImageView'])->name('chat.image-view');

    // Review Routes
    Route::get('/reviews', [\App\Http\Controllers\ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
});

// Guest posting routes
Route::get('/post', [\App\Http\Controllers\GuestItemController::class, 'showForm'])->name('guest.post.form');
Route::post('/post', [\App\Http\Controllers\GuestItemController::class, 'submit'])->name('guest.post.submit');

// Image comparison routes
Route::post('/api/compare-images', [ImageComparisonController::class, 'compare']);
Route::post('/api/compare-urls', [ImageComparisonController::class, 'compareUrls']);

// Notifications (user)
Route::get('/api/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
Route::post('/api/notifications/mark-read', [\App\Http\Controllers\NotificationController::class, 'markRead']);

// Tags API (public - for dropdown)
Route::get('/api/tags', [\App\Http\Controllers\Admin\TagController::class, 'getAllTags'])->name('api.tags');

// Admin routes (admin only)
Route::middleware(['auth','admin'])->group(function () {
    Route::get('/notifications/create', [\App\Http\Controllers\Admin\NotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notifications/send', [\App\Http\Controllers\Admin\NotificationController::class, 'send'])->name('notifications.send');
    
    // Tags management (admin only)
    Route::get('/admin/tags', [\App\Http\Controllers\Admin\TagController::class, 'index'])->name('tags.index');
    Route::post('/admin/tags', [\App\Http\Controllers\Admin\TagController::class, 'store'])->name('tags.store');
    Route::put('/admin/tags/{tag}', [\App\Http\Controllers\Admin\TagController::class, 'update'])->name('tags.update');
    Route::delete('/admin/tags/{tag}', [\App\Http\Controllers\Admin\TagController::class, 'destroy'])->name('tags.destroy');
});
