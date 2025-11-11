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

    // Pending Claims Management
    Route::get('/user/pending-claims', [\App\Http\Controllers\UserController::class, 'pendingClaims'])->name('user.pending-claims');
    Route::post('/user/claims/{uploadId}/verify', [\App\Http\Controllers\UserController::class, 'verifyClaim'])->name('user.claims.verify');
    Route::post('/user/claims/{uploadId}/reject', [\App\Http\Controllers\UserController::class, 'rejectClaim'])->name('user.claims.reject');

    // Admin routes (admin only)
    Route::middleware(['admin'])->group(function () {
        // Image Comparison (admin only - testing feature)
        Route::get('/image-comparison', function () {
            return view('image-comparison');
        });

        Route::get('/admin/reported-items', [\App\Http\Controllers\AdminController::class, 'reportedItems'])->name('admin.reported-items');
    Route::delete('/admin/reported-items/{uploadId}', [\App\Http\Controllers\AdminController::class, 'deleteItem'])->name('admin.delete-item');
    Route::post('/admin/reported-items/{uploadId}/restore', [\App\Http\Controllers\AdminController::class, 'restoreItem'])->name('admin.restore-item');
    Route::delete('/admin/reported-items/{uploadId}/force', [\App\Http\Controllers\AdminController::class, 'forceDeleteItem'])->name('admin.force-delete-item');
    Route::get('/admin/claimed', [\App\Http\Controllers\AdminController::class, 'claimVerify'])->name('admin.claimed');
    Route::get('/admin/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/users/{user}', [\App\Http\Controllers\AdminController::class, 'showUser'])->name('admin.users.show');
    Route::get('/admin/users/{user}/edit', [\App\Http\Controllers\AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [\App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/admin/users/{user}/toggle-verification', [\App\Http\Controllers\AdminController::class, 'toggleVerification'])->name('admin.users.toggle-verification');
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
        
        // Rewards Management
        Route::get('/admin/rewards', [\App\Http\Controllers\Admin\RewardController::class, 'index'])->name('admin.rewards.index');
        Route::get('/admin/rewards/create', [\App\Http\Controllers\Admin\RewardController::class, 'create'])->name('admin.rewards.create');
        Route::post('/admin/rewards', [\App\Http\Controllers\Admin\RewardController::class, 'store'])->name('admin.rewards.store');
        Route::get('/admin/rewards/send', [\App\Http\Controllers\Admin\RewardController::class, 'showSendForm'])->name('admin.rewards.send');
        Route::post('/admin/rewards/send', [\App\Http\Controllers\Admin\RewardController::class, 'send'])->name('admin.rewards.send.post');
        Route::post('/admin/rewards/auto-assign', [\App\Http\Controllers\Admin\RewardController::class, 'checkAutoAssign'])->name('admin.rewards.auto-assign');
        Route::delete('/admin/rewards/{id}', [\App\Http\Controllers\Admin\RewardController::class, 'destroy'])->name('admin.rewards.destroy');
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
    Route::post('/api/user/items/{uploadId}/cancel-claim', [\App\Http\Controllers\Api\UserItemController::class, 'cancelClaim']);

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

// Guest posting routes
Route::get('/post', [\App\Http\Controllers\GuestItemController::class, 'showForm'])->name('guest.post.form');
Route::post('/post', [\App\Http\Controllers\GuestItemController::class, 'submit'])->name('guest.post.submit');

// Image comparison routes
Route::post('/api/compare-images', [ImageComparisonController::class, 'compare']);
Route::post('/api/compare-urls', [ImageComparisonController::class, 'compareUrls']);

// Notifications (user)
Route::get('/api/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
Route::post('/api/notifications/mark-read', [\App\Http\Controllers\NotificationController::class, 'markRead']);

// Admin routes (admin only)
Route::middleware(['auth','admin'])->group(function () {
    Route::get('/admin/notifications/create', [\App\Http\Controllers\Admin\NotificationController::class, 'create'])->name('admin.notifications.create');
    Route::post('/admin/notifications/send', [\App\Http\Controllers\Admin\NotificationController::class, 'send'])->name('admin.notifications.send');
});
