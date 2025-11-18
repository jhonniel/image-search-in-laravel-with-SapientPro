<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImageMetadata;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display admin dashboard with statistics
     */
    public function dashboard()
    {
        // Total Reports - count unique upload_ids
        $totalReports = ImageMetadata::select('upload_id')->distinct()->count('upload_id');

        // Items in progress - unclaimed items (grouped by upload_id)
        $itemsInProgress = ImageMetadata::where('is_claimed', false)
            ->select('upload_id')
            ->distinct()
            ->count('upload_id');

        // Contributors - unique users who have uploaded items
        $contributors = ImageMetadata::select('uploader_email')->distinct()->count('uploader_email');

        // Calculate percentage changes (comparing last 30 days to previous 30 days)
        $last30Days = now()->subDays(30);
        $previous30Days = now()->subDays(60);

        $reportsLast30Days = ImageMetadata::where('created_at', '>=', $last30Days)
            ->select('upload_id')
            ->distinct()
            ->count('upload_id');
        $reportsPrevious30Days = ImageMetadata::whereBetween('created_at', [$previous30Days, $last30Days])
            ->select('upload_id')
            ->distinct()
            ->count('upload_id');

        $reportsChange = $reportsPrevious30Days > 0
            ? round((($reportsLast30Days - $reportsPrevious30Days) / $reportsPrevious30Days) * 100)
            : 0;

        $itemsInProgressLast30Days = ImageMetadata::where('is_claimed', false)
            ->where('created_at', '>=', $last30Days)
            ->select('upload_id')
            ->distinct()
            ->count('upload_id');
        $itemsInProgressPrevious30Days = ImageMetadata::where('is_claimed', false)
            ->whereBetween('created_at', [$previous30Days, $last30Days])
            ->select('upload_id')
            ->distinct()
            ->count('upload_id');

        $itemsInProgressChange = $itemsInProgressPrevious30Days > 0
            ? round((($itemsInProgressLast30Days - $itemsInProgressPrevious30Days) / $itemsInProgressPrevious30Days) * 100)
            : 0;

        $contributorsLast30Days = ImageMetadata::where('created_at', '>=', $last30Days)
            ->select('uploader_email')
            ->distinct()
            ->count('uploader_email');
        $contributorsPrevious30Days = ImageMetadata::whereBetween('created_at', [$previous30Days, $last30Days])
            ->select('uploader_email')
            ->distinct()
            ->count('uploader_email');

        $contributorsChange = $contributorsPrevious30Days > 0
            ? round((($contributorsLast30Days - $contributorsPrevious30Days) / $contributorsPrevious30Days) * 100)
            : 0;

        // Lost vs Found items by month (last 9 months)
        $monthlyData = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'];

        for ($i = 8; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();

            $lostCount = ImageMetadata::where('status', 'lost')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->select('upload_id')
                ->distinct()
                ->count('upload_id');

            $foundCount = ImageMetadata::where('status', 'found')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->select('upload_id')
                ->distinct()
                ->count('upload_id');

            $monthlyData[] = [
                'month' => $months[8 - $i],
                'lost' => $lostCount,
                'found' => $foundCount,
            ];
        }

        // Calculate max value for chart scaling
        $lostMax = !empty($monthlyData) ? max(array_column($monthlyData, 'lost')) : 0;
        $foundMax = !empty($monthlyData) ? max(array_column($monthlyData, 'found')) : 0;
        $maxValue = max($lostMax, $foundMax);
        $maxValue = $maxValue > 0 ? $maxValue : 1; // Prevent division by zero

        // Normalize heights for chart (max height 150px)
        foreach ($monthlyData as &$data) {
            $data['lost_height'] = $maxValue > 0 ? round(($data['lost'] / $maxValue) * 150) : 0;
            $data['found_height'] = $maxValue > 0 ? round(($data['found'] / $maxValue) * 150) : 0;
        }

        // Recent Activity - last 5 items (grouped by upload_id)
        $recentItems = ImageMetadata::orderBy('created_at', 'desc')
            ->get()
            ->groupBy('upload_id')
            ->take(5)
            ->map(function ($itemGroup) {
                $firstItem = $itemGroup->first();
                $user = User::where('email', $firstItem->uploader_email)->first();
                return [
                    'upload_id' => $firstItem->upload_id,
                    'description' => $firstItem->description ?? 'No description',
                    'status' => $firstItem->status,
                    'user_name' => $user ? $user->name : 'Unknown User',
                    'created_at' => $firstItem->created_at,
                    'is_claimed' => $firstItem->is_claimed,
                    'claimed_by_email' => $firstItem->claimed_by_email,
                ];
            })
            ->values();

        // Top Contributors - users with most reports
        $topContributors = ImageMetadata::select('uploader_email')
            ->selectRaw('COUNT(DISTINCT upload_id) as report_count')
            ->selectRaw('SUM(CASE WHEN COALESCE(is_claimed, 0) = 1 THEN 1 ELSE 0 END) as claimed_count')
            ->selectRaw('MAX(created_at) as last_active')
            ->whereNotNull('uploader_email')
            ->groupBy('uploader_email')
            ->orderBy('report_count', 'desc')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $user = User::where('email', $item->uploader_email)->first();
                return [
                    'email' => $item->uploader_email,
                    'name' => $user ? ($user->name ?? 'Unknown') : 'Unknown',
                    'username' => $user ? ($user->username ?? 'N/A') : 'N/A',
                    'report_count' => $item->report_count,
                    'claimed_count' => $item->claimed_count,
                    'verified_count' => 0, // Placeholder - add if you have verification logic
                    'last_active' => $item->last_active ? \Carbon\Carbon::parse($item->last_active) : null,
                ];
            });

        // Get claimed items for admin dashboard
        $claimedItems = ImageMetadata::where('is_claimed', true)
            ->orderBy('claimed_at', 'desc')
            ->get()
            ->groupBy('upload_id')
            ->take(10)
            ->map(function ($itemGroup) {
                $firstItem = $itemGroup->first();
                $claimedByUser = null;
                if ($firstItem->claimed_by_email) {
                    $claimedByUser = User::where('email', $firstItem->claimed_by_email)->first();
                }
                $uploader = User::where('email', $firstItem->uploader_email)->first();

                return [
                    'upload_id' => $firstItem->upload_id,
                    'item_type' => $firstItem->status,
                    'description' => $firstItem->description ?? 'No description',
                    'uploader_name' => $uploader ? $uploader->name : 'Unknown User',
                    'claimed_by_name' => $claimedByUser ? $claimedByUser->name : 'Unknown',
                    'claimed_by_email' => $firstItem->claimed_by_email,
                    'claimed_at' => $firstItem->claimed_at,
                    'created_at' => $firstItem->created_at,
                ];
            })
            ->values();

        return view('admin.dashboard', compact(
            'totalReports',
            'itemsInProgress',
            'contributors',
            'reportsChange',
            'itemsInProgressChange',
            'contributorsChange',
            'monthlyData',
            'recentItems',
            'topContributors',
            'claimedItems'
        ));
    }

    /**
     * Display all reported items for admin
     */
    public function reportedItems()
    {
        // Get all items from all users, grouped by upload_id
        $items = ImageMetadata::orderBy('created_at', 'desc')
            ->get()
            ->groupBy('upload_id');

        $formattedItems = [];
        foreach ($items as $uploadId => $itemGroup) {
            $firstItem = $itemGroup->first();
            $formattedItems[] = [
                'upload_id' => $uploadId,
                'item_type' => $firstItem->status,
                'location' => $firstItem->description, // Using description as location for now
                'description' => $firstItem->description,
                'tags' => $this->parseTags($firstItem->tags),
                'uploader_email' => $firstItem->uploader_email,
                'created_at' => $firstItem->created_at,
                'images' => $itemGroup->map(function ($item) {
                    return [
                        'filename' => $item->filename,
                        'original_name' => $item->original_name,
                        'path' => $item->file_path,
                        'file_size' => $item->file_size,
                        'mime_type' => $item->mime_type,
                    ];
                })->toArray()
            ];
        }

        return view('admin.reported-items', compact('formattedItems'));
    }

    /**
     * Safely parse tags from database
     */
    private function parseTags($tags)
    {
        if (empty($tags)) {
            return [];
        }

        if (is_array($tags)) {
            return $tags;
        }

        if (is_string($tags)) {
            $decoded = json_decode($tags, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * Delete an item and all its associated images
     */
    public function deleteItem($uploadId)
    {
        try {
            // Debug: Log the received uploadId
            \Log::info('Admin delete request received', [
                'uploadId' => $uploadId,
                'request_method' => request()->method(),
                'request_url' => request()->url()
            ]);

            // Get all items with this upload_id
            $items = ImageMetadata::where('upload_id', $uploadId)->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            // Soft delete database records (files are kept for potential restore)
            $deletedCount = ImageMetadata::where('upload_id', $uploadId)->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} item(s). They can be restored from the trash."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore deleted item (admin only)
     */
    public function restoreItem($uploadId)
    {
        try {
            // Find trashed items with this upload_id
            $items = ImageMetadata::onlyTrashed()
                ->where('upload_id', $uploadId)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deleted item not found'
                ], 404);
            }

            // Restore all items with this upload_id
            $restoredCount = ImageMetadata::onlyTrashed()
                ->where('upload_id', $uploadId)
                ->restore();

            return response()->json([
                'success' => true,
                'message' => "Successfully restored {$restoredCount} item(s)"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete item (permanent delete with files) - admin only
     */
    public function forceDeleteItem($uploadId)
    {
        try {
            // Find trashed items with this upload_id
            $items = ImageMetadata::onlyTrashed()
                ->where('upload_id', $uploadId)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Deleted item not found'
                ], 404);
            }

            // Delete physical files
            foreach ($items as $item) {
                if ($item->file_path) {
                    $relativePath = str_replace('/storage/', '', $item->file_path);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }

            // Permanently delete database records
            $deletedCount = ImageMetadata::onlyTrashed()
                ->where('upload_id', $uploadId)
                ->forceDelete();

            return response()->json([
                'success' => true,
                'message' => "Successfully permanently deleted {$deletedCount} item(s) and associated files"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display all users for admin
     */
    public function users()
    {
        $users = \App\Models\User::orderBy('created_at', 'desc')->get(); // Soft deletes automatically excludes deleted users

        return view('admin.users', compact('users'));
    }

    /**
     * Get trashed items (deleted items) for admin
     */
    public function trashedItems()
    {
        $trashedItems = ImageMetadata::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get()
            ->groupBy('upload_id');

        return view('admin.trashed-items', compact('trashedItems'));
    }

    /**
     * Restore user (admin only)
     */
    public function restoreUser($id)
    {
        try {
            $user = \App\Models\User::onlyTrashed()->findOrFail($id);
            $user->restore();

            return redirect()->route('users')
                ->with('success', 'User restored successfully!');
        } catch (\Exception $e) {
            return redirect()->route('users')
                ->with('error', 'Failed to restore user: ' . $e->getMessage());
        }
    }

    /**
     * Force delete user (admin only)
     */
    public function forceDeleteUser($id)
    {
        try {
            $user = \App\Models\User::onlyTrashed()->findOrFail($id);
            $user->forceDelete();

            return redirect()->route('users')
                ->with('success', 'User permanently deleted!');
        } catch (\Exception $e) {
            return redirect()->route('users')
                ->with('error', 'Failed to permanently delete user: ' . $e->getMessage());
        }
    }

    /**
     * Show user profile details
     */
    public function showUser(\App\Models\User $user)
    {
        // Get user's reported items count
        $reportsCount = \App\Models\ImageMetadata::where('uploader_email', $user->email)->count();

        // Get user's recent activity
        $recentReports = \App\Models\ImageMetadata::where('uploader_email', $user->email)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'reports_count' => $reportsCount,
                'recent_reports' => $recentReports->map(function($report) {
                    return [
                        'id' => $report->id,
                        'description' => $report->description,
                        'status' => $report->status,
                        'created_at' => $report->created_at,
                        'file_path' => $report->file_path
                    ];
                })
            ]
        ]);
    }

    /**
     * Show user edit form
     */
    public function editUser(\App\Models\User $user)
    {
        return view('admin.user-edit', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateUser(\Illuminate\Http\Request $request, \App\Models\User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User profile updated successfully',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle user verification status
     */
    public function toggleVerification(\App\Models\User $user)
    {
        try {
            $user->is_verified = !$user->is_verified;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => $user->is_verified ? 'User verified successfully' : 'User verification removed',
                'is_verified' => $user->is_verified
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle verification: ' . $e->getMessage()
            ], 500);
        }
    }

    public function claimVerify()
    {
        // Get all claimed items grouped by upload_id
        $claimedItems = ImageMetadata::claimed()
            ->orderBy('claimed_at', 'desc')
            ->get()
            ->groupBy('upload_id');

        $formattedItems = [];
        foreach ($claimedItems as $uploadId => $itemGroup) {
            $firstItem = $itemGroup->first();

            // Get the user who claimed the item
            $claimedByUser = null;
            if ($firstItem->claimed_by_email) {
                $claimedByUser = User::where('email', $firstItem->claimed_by_email)->first();
            }

            $formattedItems[] = [
                'upload_id' => $uploadId,
                'item_type' => $firstItem->status,
                'description' => $firstItem->description,
                'location' => $firstItem->description, // You might want to add a location field
                'tags' => $this->parseTags($firstItem->tags),
                'uploader_email' => $firstItem->uploader_email,
                'claimed_by_email' => $firstItem->claimed_by_email,
                'claimed_by_name' => $claimedByUser ? $claimedByUser->name : 'Unknown',
                'claimed_at' => $firstItem->claimed_at,
                'created_at' => $firstItem->created_at,
                'images' => $itemGroup->map(function ($item) {
                    // Fix file path - remove /storage/ prefix if it exists
                    $filePath = $item->file_path;
                    if (str_starts_with($filePath, '/storage/')) {
                        $filePath = substr($filePath, 9); // Remove '/storage/' prefix
                    }

                    return [
                        'filename' => $item->filename,
                        'path' => Storage::url($filePath),
                        'original_name' => $item->original_name,
                        'size' => $item->file_size,
                    ];
                })->toArray(),
            ];
        }

        return view('admin.claimed', compact('formattedItems'));
    }

    /**
     * Display insights and analytics
     */
    public function insights()
    {
        // Get analytics data
        $totalItems = ImageMetadata::select('upload_id')->distinct()->count('upload_id');
        $lostItems = ImageMetadata::where('status', 'lost')->select('upload_id')->distinct()->count('upload_id');
        $foundItems = ImageMetadata::where('status', 'found')->select('upload_id')->distinct()->count('upload_id');
        $claimedItems = ImageMetadata::where('is_claimed', true)->select('upload_id')->distinct()->count('upload_id');
        $totalUsers = User::count();
        $activeUsers = ImageMetadata::select('uploader_email')->distinct()->count('uploader_email');

        // Get items by status over time (last 12 months)
        $monthlyStats = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();

            $monthlyStats[] = [
                'month' => now()->subMonths($i)->format('M Y'),
                'lost' => ImageMetadata::where('status', 'lost')
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->select('upload_id')->distinct()->count('upload_id'),
                'found' => ImageMetadata::where('status', 'found')
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->select('upload_id')->distinct()->count('upload_id'),
                'claimed' => ImageMetadata::where('is_claimed', true)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->select('upload_id')->distinct()->count('upload_id'),
            ];
        }

        // Top categories/tags
        $topTags = ImageMetadata::whereNotNull('tags')
            ->get()
            ->flatMap(function ($item) {
                $tags = is_array($item->tags) ? $item->tags : json_decode($item->tags, true) ?? [];
                return is_array($tags) ? $tags : [];
            })
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(10);

        // Items by day of week (simplified - using day name from created_at)
        $dayOfWeekStats = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $allItems = ImageMetadata::select('upload_id', 'created_at')
            ->distinct()
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('l'); // Day name
            });

        foreach ($days as $day) {
            $dayOfWeekStats[$day] = $allItems->get($day, collect())->unique('upload_id')->count();
        }

        // Peak hours analysis
        $hourlyStats = [];
        $itemsByHour = ImageMetadata::select('upload_id', 'created_at')
            ->distinct()
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('H'); // Hour (00-23)
            });

        for ($hour = 0; $hour < 24; $hour++) {
            $hourKey = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $hourlyStats[$hour] = $itemsByHour->get($hourKey, collect())->unique('upload_id')->count();
        }

        return view('admin.insights', compact(
            'totalItems',
            'lostItems',
            'foundItems',
            'claimedItems',
            'totalUsers',
            'activeUsers',
            'monthlyStats',
            'topTags',
            'dayOfWeekStats',
            'hourlyStats'
        ));
    }

    /**
     * Display settings page
     */
    public function settings()
    {
        // Get current email configuration from env/config
        $emailSettings = [
            'mail_mailer' => Setting::get('mail_mailer', config('mail.default')),
            'mail_host' => Setting::get('mail_host', config('mail.mailers.smtp.host')),
            'mail_port' => Setting::get('mail_port', config('mail.mailers.smtp.port')),
            'mail_username' => Setting::get('mail_username', config('mail.mailers.smtp.username')),
            'mail_password' => Setting::get('mail_password', config('mail.mailers.smtp.password')),
            'mail_encryption' => Setting::get('mail_encryption', env('MAIL_ENCRYPTION', 'tls')),
            'mail_from_address' => Setting::get('mail_from_address', config('mail.from.address')),
            'mail_from_name' => Setting::get('mail_from_name', config('mail.from.name')),
            'email_notifications' => Setting::get('email_notifications', true),
            'similarity_alerts' => Setting::get('similarity_alerts', true),
            'notification_email' => Setting::get('notification_email', config('mail.from.address')),
        ];

        // Get enabled cities
        $enabledCitiesJson = Setting::get('enabled_cities', '[]');
        $enabledCities = json_decode($enabledCitiesJson, true) ?? [];

        // Get custom cities (manually added)
        $customCitiesJson = Setting::get('custom_cities', '[]');
        $customCities = json_decode($customCitiesJson, true) ?? [];

        // Get all Philippine cities
        $philippineCities = $this->getPhilippineCities();

        // Merge custom cities with predefined cities
        foreach ($customCities as $region => $cities) {
            if (isset($philippineCities[$region])) {
                // Merge with existing region
                $philippineCities[$region] = array_merge($philippineCities[$region], $cities);
                // Remove duplicates
                $philippineCities[$region] = array_unique($philippineCities[$region]);
                sort($philippineCities[$region]);
            } else {
                // Add new region
                $philippineCities[$region] = $cities;
            }
        }

        // Get all available regions for dropdown
        $allRegions = array_keys($philippineCities);
        sort($allRegions);

        // Get enabled provinces
        $enabledProvincesJson = Setting::get('enabled_provinces', '[]');
        $enabledProvinces = json_decode($enabledProvincesJson, true) ?? [];

        // Get custom provinces (manually added)
        $customProvincesJson = Setting::get('custom_provinces', '[]');
        $customProvinces = json_decode($customProvincesJson, true) ?? [];

        // Get all Philippine provinces
        $philippineProvinces = $this->getPhilippineProvinces();

        // Merge custom provinces with predefined provinces
        foreach ($customProvinces as $region => $provinces) {
            if (isset($philippineProvinces[$region])) {
                // Merge with existing region
                $philippineProvinces[$region] = array_merge($philippineProvinces[$region], $provinces);
                // Remove duplicates
                $philippineProvinces[$region] = array_unique($philippineProvinces[$region]);
                sort($philippineProvinces[$region]);
            } else {
                // Add new region
                $philippineProvinces[$region] = $provinces;
            }
        }

        // Get all available regions for provinces dropdown
        $allProvinceRegions = array_keys($philippineProvinces);
        sort($allProvinceRegions);

        return view('admin.settings', compact('emailSettings', 'enabledCities', 'philippineCities', 'customCities', 'allRegions', 'enabledProvinces', 'philippineProvinces', 'customProvinces', 'allProvinceRegions'));
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'mail_mailer' => 'required|in:smtp,log,sendmail,mailgun,ses,postmark,array',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|in:tls,ssl,null',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
            'email_notifications' => 'boolean',
            'similarity_alerts' => 'boolean',
            'notification_email' => 'nullable|email|max:255',
        ]);

        // Save email settings to database
        Setting::set('mail_mailer', $request->mail_mailer, 'string', 'Mail driver (smtp, log, etc.)');
        Setting::set('mail_host', $request->mail_host, 'string', 'SMTP host address');
        Setting::set('mail_port', $request->mail_port ?? 587, 'integer', 'SMTP port number');
        Setting::set('mail_username', $request->mail_username, 'string', 'SMTP username');
        Setting::set('mail_password', $request->mail_password, 'string', 'SMTP password');
        Setting::set('mail_encryption', $request->mail_encryption ?? 'tls', 'string', 'Mail encryption (tls, ssl)');
        Setting::set('mail_from_address', $request->mail_from_address, 'string', 'Default from email address');
        Setting::set('mail_from_name', $request->mail_from_name, 'string', 'Default from name');
        Setting::set('email_notifications', $request->has('email_notifications'), 'boolean', 'Enable email notifications');
        Setting::set('similarity_alerts', $request->has('similarity_alerts'), 'boolean', 'Enable similarity alerts');
        Setting::set('notification_email', $request->notification_email, 'string', 'Notification recipient email');

        // Save enabled cities
        if ($request->has('enabled_cities')) {
            $enabledCities = is_array($request->enabled_cities) ? $request->enabled_cities : [];
            Setting::set('enabled_cities', json_encode($enabledCities), 'string', 'List of enabled cities in the Philippines');
        }

        // Handle adding new custom city
        if ($request->has('new_city_name') && $request->has('new_city_region')) {
            $newCityName = trim($request->new_city_name);
            $newCityRegion = trim($request->new_city_region);

            if (!empty($newCityName) && !empty($newCityRegion)) {
                // Get existing custom cities
                $customCitiesJson = Setting::get('custom_cities', '[]');
                $customCities = json_decode($customCitiesJson, true) ?? [];

                // Initialize region if it doesn't exist
                if (!isset($customCities[$newCityRegion])) {
                    $customCities[$newCityRegion] = [];
                }

                // Add city if it doesn't already exist
                if (!in_array($newCityName, $customCities[$newCityRegion])) {
                    $customCities[$newCityRegion][] = $newCityName;
                    sort($customCities[$newCityRegion]);
                    Setting::set('custom_cities', json_encode($customCities), 'string', 'Custom cities manually added by admin');
                }
            }
        }

        // Handle editing city
        if ($request->has('edit_city_original_name') && $request->has('edit_city_new_name') &&
            $request->has('edit_city_original_region') && $request->has('edit_city_new_region')) {

            $originalName = trim($request->edit_city_original_name);
            $newName = trim($request->edit_city_new_name);
            $originalRegion = trim($request->edit_city_original_region);
            $newRegion = trim($request->edit_city_new_region);
            $isCustom = $request->has('edit_city_is_custom') && $request->edit_city_is_custom === '1';

            if (!empty($originalName) && !empty($newName) && !empty($originalRegion) && !empty($newRegion)) {
                // Get existing custom cities
                $customCitiesJson = Setting::get('custom_cities', '[]');
                $customCities = json_decode($customCitiesJson, true) ?? [];

                // Get enabled cities
                $enabledCitiesJson = Setting::get('enabled_cities', '[]');
                $enabledCities = json_decode($enabledCitiesJson, true) ?? [];

                // If it's a custom city, update it in custom cities
                if ($isCustom && isset($customCities[$originalRegion])) {
                    // Remove old city from original region
                    $customCities[$originalRegion] = array_filter(
                        $customCities[$originalRegion],
                        fn($city) => $city !== $originalName
                    );
                    $customCities[$originalRegion] = array_values($customCities[$originalRegion]);

                    // Remove region if empty
                    if (empty($customCities[$originalRegion])) {
                        unset($customCities[$originalRegion]);
                    }

                    // Add new city to new region
                    if (!isset($customCities[$newRegion])) {
                        $customCities[$newRegion] = [];
                    }
                    if (!in_array($newName, $customCities[$newRegion])) {
                        $customCities[$newRegion][] = $newName;
                        sort($customCities[$newRegion]);
                    }

                    Setting::set('custom_cities', json_encode($customCities), 'string', 'Custom cities manually added by admin');
                } else {
                    // If it's a predefined city, convert it to custom city
                    if (!isset($customCities[$newRegion])) {
                        $customCities[$newRegion] = [];
                    }
                    if (!in_array($newName, $customCities[$newRegion])) {
                        $customCities[$newRegion][] = $newName;
                        sort($customCities[$newRegion]);
                    }
                    Setting::set('custom_cities', json_encode($customCities), 'string', 'Custom cities manually added by admin');
                }

                // Update enabled cities list if the old city was enabled
                if (in_array($originalName, $enabledCities)) {
                    $enabledCities = array_filter($enabledCities, fn($city) => $city !== $originalName);
                    if (!in_array($newName, $enabledCities)) {
                        $enabledCities[] = $newName;
                    }
                    Setting::set('enabled_cities', json_encode(array_values($enabledCities)), 'string', 'List of enabled cities in the Philippines');
                }
            }
        }

        // Handle deleting city
        if ($request->has('delete_city') && $request->has('delete_city_region')) {
            $deleteCityName = trim($request->delete_city);
            $deleteCityRegion = trim($request->delete_city_region);
            $isCustom = $request->has('delete_city_is_custom') && $request->delete_city_is_custom === '1';

            if (!empty($deleteCityName) && !empty($deleteCityRegion)) {
                // Get existing custom cities
                $customCitiesJson = Setting::get('custom_cities', '[]');
                $customCities = json_decode($customCitiesJson, true) ?? [];

                // Get enabled cities
                $enabledCitiesJson = Setting::get('enabled_cities', '[]');
                $enabledCities = json_decode($enabledCitiesJson, true) ?? [];

                // Remove from enabled cities if it was enabled
                if (in_array($deleteCityName, $enabledCities)) {
                    $enabledCities = array_filter($enabledCities, fn($city) => $city !== $deleteCityName);
                    Setting::set('enabled_cities', json_encode(array_values($enabledCities)), 'string', 'List of enabled cities in the Philippines');
                }

                // If it's a custom city, remove it from custom cities
                if ($isCustom && isset($customCities[$deleteCityRegion])) {
                    $customCities[$deleteCityRegion] = array_filter(
                        $customCities[$deleteCityRegion],
                        fn($city) => $city !== $deleteCityName
                    );
                    $customCities[$deleteCityRegion] = array_values($customCities[$deleteCityRegion]);

                    // Remove region if empty
                    if (empty($customCities[$deleteCityRegion])) {
                        unset($customCities[$deleteCityRegion]);
                    }

                    Setting::set('custom_cities', json_encode($customCities), 'string', 'Custom cities manually added by admin');
                }
            }
        }

        // Save enabled provinces
        if ($request->has('enabled_provinces')) {
            $enabledProvinces = is_array($request->enabled_provinces) ? $request->enabled_provinces : [];
            Setting::set('enabled_provinces', json_encode($enabledProvinces), 'string', 'List of enabled provinces in the Philippines');
        }

        // Handle adding new custom province
        if ($request->has('new_province_name') && $request->has('new_province_region')) {
            $newProvinceName = trim($request->new_province_name);
            $newProvinceRegion = trim($request->new_province_region);

            if (!empty($newProvinceName) && !empty($newProvinceRegion)) {
                // Get existing custom provinces
                $customProvincesJson = Setting::get('custom_provinces', '[]');
                $customProvinces = json_decode($customProvincesJson, true) ?? [];

                // Initialize region if it doesn't exist
                if (!isset($customProvinces[$newProvinceRegion])) {
                    $customProvinces[$newProvinceRegion] = [];
                }

                // Add province if it doesn't already exist
                if (!in_array($newProvinceName, $customProvinces[$newProvinceRegion])) {
                    $customProvinces[$newProvinceRegion][] = $newProvinceName;
                    sort($customProvinces[$newProvinceRegion]);
                    Setting::set('custom_provinces', json_encode($customProvinces), 'string', 'Custom provinces manually added by admin');
                }
            }
        }

        // Handle editing province
        if ($request->has('edit_province_original_name') && $request->has('edit_province_new_name') &&
            $request->has('edit_province_original_region') && $request->has('edit_province_new_region')) {

            $originalName = trim($request->edit_province_original_name);
            $newName = trim($request->edit_province_new_name);
            $originalRegion = trim($request->edit_province_original_region);
            $newRegion = trim($request->edit_province_new_region);
            $isCustom = $request->has('edit_province_is_custom') && $request->edit_province_is_custom === '1';

            if (!empty($originalName) && !empty($newName) && !empty($originalRegion) && !empty($newRegion)) {
                // Get existing custom provinces
                $customProvincesJson = Setting::get('custom_provinces', '[]');
                $customProvinces = json_decode($customProvincesJson, true) ?? [];

                // Get enabled provinces
                $enabledProvincesJson = Setting::get('enabled_provinces', '[]');
                $enabledProvinces = json_decode($enabledProvincesJson, true) ?? [];

                // If it's a custom province, update it in custom provinces
                if ($isCustom && isset($customProvinces[$originalRegion])) {
                    // Remove old province from original region
                    $customProvinces[$originalRegion] = array_filter(
                        $customProvinces[$originalRegion],
                        fn($province) => $province !== $originalName
                    );
                    $customProvinces[$originalRegion] = array_values($customProvinces[$originalRegion]);

                    // Remove region if empty
                    if (empty($customProvinces[$originalRegion])) {
                        unset($customProvinces[$originalRegion]);
                    }

                    // Add new province to new region
                    if (!isset($customProvinces[$newRegion])) {
                        $customProvinces[$newRegion] = [];
                    }
                    if (!in_array($newName, $customProvinces[$newRegion])) {
                        $customProvinces[$newRegion][] = $newName;
                        sort($customProvinces[$newRegion]);
                    }

                    Setting::set('custom_provinces', json_encode($customProvinces), 'string', 'Custom provinces manually added by admin');
                } else {
                    // If it's a predefined province, convert it to custom province
                    if (!isset($customProvinces[$newRegion])) {
                        $customProvinces[$newRegion] = [];
                    }
                    if (!in_array($newName, $customProvinces[$newRegion])) {
                        $customProvinces[$newRegion][] = $newName;
                        sort($customProvinces[$newRegion]);
                    }
                    Setting::set('custom_provinces', json_encode($customProvinces), 'string', 'Custom provinces manually added by admin');
                }

                // Update enabled provinces list if the old province was enabled
                if (in_array($originalName, $enabledProvinces)) {
                    $enabledProvinces = array_filter($enabledProvinces, fn($province) => $province !== $originalName);
                    if (!in_array($newName, $enabledProvinces)) {
                        $enabledProvinces[] = $newName;
                    }
                    Setting::set('enabled_provinces', json_encode(array_values($enabledProvinces)), 'string', 'List of enabled provinces in the Philippines');
                }
            }
        }

        // Handle deleting province
        if ($request->has('delete_province') && $request->has('delete_province_region')) {
            $deleteProvinceName = trim($request->delete_province);
            $deleteProvinceRegion = trim($request->delete_province_region);
            $isCustom = $request->has('delete_province_is_custom') && $request->delete_province_is_custom === '1';

            if (!empty($deleteProvinceName) && !empty($deleteProvinceRegion)) {
                // Get existing custom provinces
                $customProvincesJson = Setting::get('custom_provinces', '[]');
                $customProvinces = json_decode($customProvincesJson, true) ?? [];

                // Get enabled provinces
                $enabledProvincesJson = Setting::get('enabled_provinces', '[]');
                $enabledProvinces = json_decode($enabledProvincesJson, true) ?? [];

                // Remove from enabled provinces if it was enabled
                if (in_array($deleteProvinceName, $enabledProvinces)) {
                    $enabledProvinces = array_filter($enabledProvinces, fn($province) => $province !== $deleteProvinceName);
                    Setting::set('enabled_provinces', json_encode(array_values($enabledProvinces)), 'string', 'List of enabled provinces in the Philippines');
                }

                // If it's a custom province, remove it from custom provinces
                if ($isCustom && isset($customProvinces[$deleteProvinceRegion])) {
                    $customProvinces[$deleteProvinceRegion] = array_filter(
                        $customProvinces[$deleteProvinceRegion],
                        fn($province) => $province !== $deleteProvinceName
                    );
                    $customProvinces[$deleteProvinceRegion] = array_values($customProvinces[$deleteProvinceRegion]);

                    // Remove region if empty
                    if (empty($customProvinces[$deleteProvinceRegion])) {
                        unset($customProvinces[$deleteProvinceRegion]);
                    }

                    Setting::set('custom_provinces', json_encode($customProvinces), 'string', 'Custom provinces manually added by admin');
                }
            }
        }

        // Save field visibility and requirement settings
        Setting::set('enable_province_field', $request->has('enable_province_field'), 'boolean', 'Enable province field in forms');
        Setting::set('province_field_required', $request->has('province_field_required'), 'boolean', 'Make province field required');
        Setting::set('enable_city_field', $request->has('enable_city_field'), 'boolean', 'Enable city field in forms');
        Setting::set('city_field_required', $request->has('city_field_required'), 'boolean', 'Make city field required');

        // Save social media links
        Setting::set('social_facebook', $request->input('social_facebook', ''), 'string', 'Facebook page URL');
        Setting::set('social_instagram', $request->input('social_instagram', ''), 'string', 'Instagram page URL');
        Setting::set('social_twitter', $request->input('social_twitter', ''), 'string', 'Twitter/X page URL');
        Setting::set('social_linkedin', $request->input('social_linkedin', ''), 'string', 'LinkedIn page URL');
        Setting::set('social_youtube', $request->input('social_youtube', ''), 'string', 'YouTube channel URL');
        Setting::set('social_tiktok', $request->input('social_tiktok', ''), 'string', 'TikTok page URL');

        // Clear config cache to apply new settings
        \Artisan::call('config:clear');

        return redirect()->route('settings')
            ->with('success', 'Settings updated successfully!');
    }

    /**
     * Get list of all Philippine cities
     */
    private function getPhilippineCities()
    {
        return [
            // Metro Manila
            'Metro Manila' => [
                'Caloocan', 'Las Piñas', 'Makati', 'Malabon', 'Mandaluyong',
                'Manila', 'Marikina', 'Muntinlupa', 'Navotas', 'Parañaque',
                'Pasay', 'Pasig', 'Pateros', 'Quezon City', 'San Juan',
                'Taguig', 'Valenzuela'
            ],
            // Luzon - Region I (Ilocos)
            'Ilocos Region' => [
                'Alaminos', 'Batac', 'Candon', 'Dagupan', 'Laoag',
                'San Carlos', 'San Fernando', 'Urdaneta', 'Vigan'
            ],
            // Luzon - Region II (Cagayan Valley)
            'Cagayan Valley' => [
                'Cauayan', 'Ilagan', 'Santiago', 'Tuguegarao'
            ],
            // Luzon - Region III (Central Luzon)
            'Central Luzon' => [
                'Angeles', 'Balanga', 'Cabanatuan', 'Gapan', 'Malolos',
                'Meycauayan', 'Mabalacat', 'Olongapo', 'Palayan', 'San Fernando',
                'San Jose', 'Tarlac'
            ],
            // Luzon - Region IV-A (CALABARZON)
            'CALABARZON' => [
                'Antipolo', 'Bacoor', 'Batangas', 'Binan', 'Cabuyao',
                'Calamba', 'Cavite', 'Dasmarinas', 'General Trias', 'Imus',
                'Lipa', 'Lucena', 'San Pablo', 'San Pedro', 'Santa Rosa',
                'Tagaytay', 'Tanauan', 'Tayabas'
            ],
            // Luzon - Region IV-B (MIMAROPA)
            'MIMAROPA' => [
                'Calapan', 'Puerto Princesa'
            ],
            // Luzon - Region V (Bicol)
            'Bicol Region' => [
                'Iriga', 'Legazpi', 'Ligao', 'Masbate', 'Naga', 'Sorsogon', 'Tabaco'
            ],
            // Visayas - Region VI (Western Visayas)
            'Western Visayas' => [
                'Bacolod', 'Bago', 'Cadiz', 'Escalante', 'Himamaylan',
                'Iloilo', 'Kabankalan', 'La Carlota', 'Passi', 'Roxas',
                'Sagay', 'San Carlos', 'Silay', 'Sipalay', 'Talisay',
                'Victorias'
            ],
            // Visayas - Region VII (Central Visayas)
            'Central Visayas' => [
                'Bais', 'Bayawan', 'Bogo', 'Carcar', 'Cebu',
                'Danao', 'Dumaguete', 'Guihulngan', 'Lapu-Lapu', 'Mandaue',
                'Tagbilaran', 'Talisay', 'Toledo'
            ],
            // Visayas - Region VIII (Eastern Visayas)
            'Eastern Visayas' => [
                'Baybay', 'Borongan', 'Calbayog', 'Catbalogan', 'Maasin',
                'Ormoc', 'Tacloban'
            ],
            // Mindanao - Region IX (Zamboanga Peninsula)
            'Zamboanga Peninsula' => [
                'Dapitan', 'Dipolog', 'Isabela', 'Pagadian', 'Zamboanga'
            ],
            // Mindanao - Region X (Northern Mindanao)
            'Northern Mindanao' => [
                'Cagayan de Oro', 'El Salvador', 'Gingoog', 'Iligan', 'Malaybalay',
                'Oroquieta', 'Ozamiz', 'Tangub', 'Valencia'
            ],
            // Mindanao - Region XI (Davao)
            'Davao Region' => [
                'Davao', 'Digos', 'Mati', 'Panabo', 'Samal', 'Tagum'
            ],
            // Mindanao - Region XII (SOCCSKSARGEN)
            'SOCCSKSARGEN' => [
                'Cotabato', 'General Santos', 'Kidapawan', 'Koronadal', 'Tacurong'
            ],
            // Mindanao - Region XIII (Caraga)
            'Caraga' => [
                'Bayugan', 'Butuan', 'Cabadbaran', 'Surigao', 'Tandag'
            ],
            // ARMM (Autonomous Region in Muslim Mindanao)
            'ARMM' => [
                'Lamitan', 'Marawi'
            ],
            // CAR (Cordillera Administrative Region)
            'Cordillera' => [
                'Baguio', 'Tabuk'
            ]
        ];
    }

    /**
     * Get list of all Philippine provinces
     */
    private function getPhilippineProvinces()
    {
        return [
            // Metro Manila (No provinces, only cities)
            'Metro Manila' => [],

            // Luzon - Region I (Ilocos)
            'Ilocos Region' => [
                'Ilocos Norte', 'Ilocos Sur', 'La Union', 'Pangasinan'
            ],

            // Luzon - Region II (Cagayan Valley)
            'Cagayan Valley' => [
                'Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino'
            ],

            // Luzon - Region III (Central Luzon)
            'Central Luzon' => [
                'Aurora', 'Bataan', 'Bulacan', 'Nueva Ecija', 'Pampanga', 'Tarlac', 'Zambales'
            ],

            // Luzon - Region IV-A (CALABARZON)
            'CALABARZON' => [
                'Batangas', 'Cavite', 'Laguna', 'Quezon', 'Rizal'
            ],

            // Luzon - Region IV-B (MIMAROPA)
            'MIMAROPA' => [
                'Marinduque', 'Occidental Mindoro', 'Oriental Mindoro', 'Palawan', 'Romblon'
            ],

            // Luzon - Region V (Bicol)
            'Bicol Region' => [
                'Albay', 'Camarines Norte', 'Camarines Sur', 'Catanduanes', 'Masbate', 'Sorsogon'
            ],

            // Visayas - Region VI (Western Visayas)
            'Western Visayas' => [
                'Aklan', 'Antique', 'Capiz', 'Guimaras', 'Iloilo', 'Negros Occidental'
            ],

            // Visayas - Region VII (Central Visayas)
            'Central Visayas' => [
                'Bohol', 'Cebu', 'Negros Oriental', 'Siquijor'
            ],

            // Visayas - Region VIII (Eastern Visayas)
            'Eastern Visayas' => [
                'Biliran', 'Eastern Samar', 'Leyte', 'Northern Samar', 'Samar', 'Southern Leyte'
            ],

            // Mindanao - Region IX (Zamboanga Peninsula)
            'Zamboanga Peninsula' => [
                'Zamboanga del Norte', 'Zamboanga del Sur', 'Zamboanga Sibugay'
            ],

            // Mindanao - Region X (Northern Mindanao)
            'Northern Mindanao' => [
                'Bukidnon', 'Camiguin', 'Lanao del Norte', 'Misamis Occidental', 'Misamis Oriental'
            ],

            // Mindanao - Region XI (Davao)
            'Davao Region' => [
                'Davao de Oro', 'Davao del Norte', 'Davao del Sur', 'Davao Occidental', 'Davao Oriental'
            ],

            // Mindanao - Region XII (SOCCSKSARGEN)
            'SOCCSKSARGEN' => [
                'Cotabato', 'Sarangani', 'South Cotabato', 'Sultan Kudarat'
            ],

            // Mindanao - Region XIII (Caraga)
            'Caraga' => [
                'Agusan del Norte', 'Agusan del Sur', 'Dinagat Islands', 'Surigao del Norte', 'Surigao del Sur'
            ],

            // BARMM (Bangsamoro Autonomous Region in Muslim Mindanao)
            'BARMM' => [
                'Basilan', 'Lanao del Sur', 'Maguindanao', 'Sulu', 'Tawi-Tawi'
            ],

            // CAR (Cordillera Administrative Region)
            'Cordillera' => [
                'Abra', 'Apayao', 'Benguet', 'Ifugao', 'Kalinga', 'Mountain Province'
            ]
        ];
    }

    /**
     * Test email notification
     */
    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email|max:255',
        ]);

        try {
            // Apply mail configuration from database settings
            $this->applyMailConfiguration();

            // Check if mail is configured
            $mailMailer = config('mail.default');
            $mailHost = config('mail.mailers.smtp.host');
            $mailUsername = config('mail.mailers.smtp.username');

            if ($mailMailer === 'log') {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is set to "log" mode. Change to SMTP in settings to send actual emails. Log mode only saves emails to logs.'
                ], 400);
            }

            if ($mailMailer === 'smtp' && (empty($mailHost) || empty($mailUsername))) {
                return response()->json([
                    'success' => false,
                    'message' => 'SMTP configuration is incomplete. Please fill in all SMTP settings (Host, Username, Password, Port, Encryption).'
                ], 400);
            }

            // Get test email address
            $testEmail = $request->input('test_email');

            // Send test email
            \Mail::to($testEmail)->send(new \App\Mail\TestEmailNotification());

            return response()->json([
                'success' => true,
                'message' => "Test email sent successfully to {$testEmail}. Please check your inbox (and spam folder)."
            ]);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $helpMessage = '';

            // Check if it's a transport/authentication error
            $isTransportError = str_contains($errorMessage, '535') ||
                               str_contains($errorMessage, 'BadCredentials') ||
                               str_contains($errorMessage, 'Username and Password not accepted') ||
                               str_contains($errorMessage, 'Authentication') ||
                               str_contains($errorMessage, 'SMTP') ||
                               str_contains($errorMessage, 'Connection') ||
                               str_contains($errorMessage, 'timeout');

            // Provide helpful error messages for common issues
            if (str_contains($errorMessage, '535') || str_contains($errorMessage, 'BadCredentials') || str_contains($errorMessage, 'Username and Password not accepted')) {
                $helpMessage = "\n\n❌ Gmail Authentication Error:\n" .
                    "This error means Gmail rejected your username/password.\n\n" .
                    "✅ Solution for Gmail Users:\n" .
                    "1. Enable 2-Factor Authentication on your Google account\n" .
                    "2. Go to: https://myaccount.google.com/apppasswords\n" .
                    "3. Generate a new App Password for 'Mail'\n" .
                    "4. Copy the 16-character App Password (it looks like: xxxx xxxx xxxx xxxx)\n" .
                    "5. Paste it in the 'SMTP Password' field (remove spaces)\n" .
                    "6. Make sure you're using:\n" .
                    "   - Host: smtp.gmail.com\n" .
                    "   - Port: 587\n" .
                    "   - Encryption: TLS\n" .
                    "   - Username: your Gmail address\n" .
                    "   - Password: the App Password (NOT your regular password)\n\n" .
                    "⚠️ Important: Regular Gmail passwords will NOT work! You MUST use an App Password.";
            } elseif (str_contains($errorMessage, 'Connection') || str_contains($errorMessage, 'timeout') || str_contains($errorMessage, 'Connection timed out')) {
                $helpMessage = "\n\n❌ Connection Error:\n" .
                    "Cannot connect to the SMTP server.\n\n" .
                    "✅ Please check:\n" .
                    "1. SMTP Host is correct (e.g., smtp.gmail.com)\n" .
                    "2. SMTP Port is correct (587 for TLS, 465 for SSL)\n" .
                    "3. Your firewall allows outbound connections on the SMTP port\n" .
                    "4. Your internet connection is working";
            } elseif (str_contains($errorMessage, 'tls') || str_contains($errorMessage, 'ssl') || str_contains($errorMessage, 'encryption')) {
                $helpMessage = "\n\n❌ Encryption Error:\n" .
                    "There's an issue with the encryption settings.\n\n" .
                    "✅ For Gmail:\n" .
                    "- Use TLS encryption with port 587\n" .
                    "- OR use SSL encryption with port 465\n" .
                    "- Make sure the encryption setting matches the port";
            } else {
                $helpMessage = "\n\n✅ Please check:\n" .
                    "1. SMTP host, port, and encryption settings are correct\n" .
                    "2. Username and password are correct\n" .
                    "3. Your email provider allows SMTP access\n" .
                    "4. For Gmail: Make sure you're using an App Password, not your regular password";
            }

            \Log::error('Test email failed', [
                'error' => $errorMessage,
                'mail_config' => [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'username' => config('mail.mailers.smtp.username'),
                    'encryption' => config('mail.mailers.smtp.encryption'),
                ]
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $errorMessage . $helpMessage
            ], 500);
        }
    }

    /**
     * Apply mail configuration from database settings
     */
    private function applyMailConfiguration(): void
    {
        try {
            // Get mail settings from database
            $mailMailer = Setting::get('mail_mailer', config('mail.default'));
            $mailHost = Setting::get('mail_host', config('mail.mailers.smtp.host'));
            $mailPort = Setting::get('mail_port', config('mail.mailers.smtp.port'));
            $mailUsername = Setting::get('mail_username', config('mail.mailers.smtp.username'));
            $mailPassword = Setting::get('mail_password', config('mail.mailers.smtp.password'));
            $mailEncryption = Setting::get('mail_encryption', config('mail.mailers.smtp.encryption'));
            $mailFromAddress = Setting::get('mail_from_address', config('mail.from.address'));
            $mailFromName = Setting::get('mail_from_name', config('mail.from.name'));

            // Update config dynamically
            if ($mailMailer) {
                \Config::set('mail.default', $mailMailer);
            }

            if ($mailHost) {
                \Config::set('mail.mailers.smtp.host', $mailHost);
            }

            if ($mailPort) {
                \Config::set('mail.mailers.smtp.port', $mailPort);
            }

            if ($mailUsername) {
                \Config::set('mail.mailers.smtp.username', $mailUsername);
            }

            if ($mailPassword) {
                \Config::set('mail.mailers.smtp.password', $mailPassword);
            }

            if ($mailEncryption && $mailEncryption !== 'null') {
                \Config::set('mail.mailers.smtp.encryption', $mailEncryption);
            } else {
                \Config::set('mail.mailers.smtp.encryption', null);
            }

            if ($mailFromAddress) {
                \Config::set('mail.from.address', $mailFromAddress);
            }

            if ($mailFromName) {
                \Config::set('mail.from.name', $mailFromName);
            }

            // Clear mail cache
            \Config::set('mail.mailers.smtp.auth', true);

        } catch (\Exception $e) {
            \Log::error('Failed to apply mail configuration: ' . $e->getMessage());
        }
    }

    /**
     * Export database as SQL file
     */
    public function exportDatabase(Request $request)
    {
        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();
            $filename = 'database_backup_' . date('Y-m-d_His') . '.sql';
            $returnJson = $request->has('json') || $request->wantsJson(); // Check if JSON response is requested

            if ($driver === 'sqlite') {
                // For SQLite, we need to read the database file
                $databasePath = config('database.connections.sqlite.database');

                if (!file_exists($databasePath)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Database file not found'
                    ], 404);
                }

                // Read SQLite database and convert to SQL
                $sql = $this->sqliteToSql($databasePath);

                if ($returnJson) {
                    // Return as JSON for AJAX requests
                    return response()->json([
                        'success' => true,
                        'filename' => $filename,
                        'content' => base64_encode($sql),
                        'message' => 'Database exported successfully'
                    ]);
                }

                return response($sql)
                    ->header('Content-Type', 'application/sql')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

            } elseif ($driver === 'mysql') {
                // For MySQL, use mysqldump if available, otherwise generate SQL manually
                $host = config('database.connections.mysql.host');
                $database = config('database.connections.mysql.database');
                $username = config('database.connections.mysql.username');
                $password = config('database.connections.mysql.password');

                // Try to use mysqldump command if available
                $mysqldumpPath = $this->findMysqldumpPath();
                if ($mysqldumpPath) {
                    // Build command with password handling
                    $command = escapeshellarg($mysqldumpPath);
                    $command .= ' -h ' . escapeshellarg($host);
                    $command .= ' -u ' . escapeshellarg($username);
                    if (!empty($password)) {
                        $command .= ' -p' . escapeshellarg($password);
                    }
                    $command .= ' ' . escapeshellarg($database);
                    $command .= ' 2>&1';

                    $output = [];
                    $returnVar = 0;
                    @exec($command, $output, $returnVar);

                    if ($returnVar === 0 && !empty($output)) {
                        $sql = implode("\n", $output);
                    } else {
                        // Fallback: Generate SQL manually from database
                        $sql = $this->generateMysqlDump($connection);
                    }
                } else {
                    // Generate SQL manually from database
                    $sql = $this->generateMysqlDump($connection);
                }

                if ($returnJson) {
                    // Return as JSON for AJAX requests
                    return response()->json([
                        'success' => true,
                        'filename' => $filename,
                        'content' => base64_encode($sql),
                        'message' => 'Database exported successfully'
                    ]);
                }

                return response($sql)
                    ->header('Content-Type', 'application/sql')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Database driver not supported for export'
                ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Database export failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to export database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import database from SQL file
     */
    public function importDatabase(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|max:10240', // Max 10MB - allow any file type for SQL
        ]);

        // Validate file extension manually
        $file = $request->file('sql_file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['sql', 'txt'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file type. Please upload a .sql or .txt file.'
            ], 400);
        }

        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();

            $sqlContent = file_get_contents($file->getRealPath());

            if (empty($sqlContent)) {
                return response()->json([
                    'success' => false,
                    'message' => 'SQL file is empty'
                ], 400);
            }

            // For SQLite, disable foreign key constraints during import
            if ($driver === 'sqlite') {
                $connection->statement('PRAGMA foreign_keys = OFF');
            }

            // Begin transaction
            DB::beginTransaction();

            try {
                if ($driver === 'sqlite') {
                    $this->importSqlite($sqlContent, $connection);
                } elseif ($driver === 'mysql') {
                    $this->importMysql($sqlContent, $connection);
                } else {
                    throw new \Exception('Database driver not supported for import');
                }

                DB::commit();

                // Re-enable foreign key constraints for SQLite
                if ($driver === 'sqlite') {
                    $connection->statement('PRAGMA foreign_keys = ON');
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Database imported successfully. Please refresh the page to see changes.'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();

                // Re-enable foreign key constraints for SQLite even on error
                if ($driver === 'sqlite') {
                    try {
                        $connection->statement('PRAGMA foreign_keys = ON');
                    } catch (\Exception $fkError) {
                        // Ignore if this fails
                    }
                }

                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Database import failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to import database: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert SQLite database to SQL
     */
    private function sqliteToSql($databasePath)
    {
        $pdo = new \PDO('sqlite:' . $databasePath);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $sql = "-- SQLite Database Export\n";
        $sql .= "-- Exported on: " . date('Y-m-d H:i:s') . "\n\n";

        // Get all tables
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            // Get table structure
            $createTable = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'")->fetchColumn();
            $sql .= "\n-- Table structure for `$table`\n";
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql .= $createTable . ";\n\n";

            // Get table data
            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $sql .= "-- Data for table `$table`\n";
                foreach ($rows as $row) {
                    $columns = array_keys($row);
                    $values = array_map(function($value) use ($pdo) {
                        return $value === null ? 'NULL' : $pdo->quote($value);
                    }, array_values($row));

                    $sql .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        return $sql;
    }

    /**
     * Generate MySQL dump manually
     */
    private function generateMysqlDump($connection)
    {
        $sql = "-- MySQL Database Export\n";
        $sql .= "-- Exported on: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "SET time_zone = \"+00:00\";\n\n";

        // Get all tables
        $tables = $connection->select("SHOW TABLES");
        $tableKey = 'Tables_in_' . config('database.connections.mysql.database');

        foreach ($tables as $tableObj) {
            $table = $tableObj->$tableKey;

            // Get table structure
            $createTable = $connection->select("SHOW CREATE TABLE `$table`");
            if (!empty($createTable)) {
                $createTableSql = $createTable[0]->{'Create Table'};
                $sql .= "\n-- Table structure for `$table`\n";
                $sql .= "DROP TABLE IF EXISTS `$table`;\n";
                $sql .= $createTableSql . ";\n\n";
            }

            // Get table data
            $rows = $connection->select("SELECT * FROM `$table`");
            if (!empty($rows)) {
                $sql .= "-- Data for table `$table`\n";
                foreach ($rows as $row) {
                    $columns = array_keys((array)$row);
                    $values = array_map(function($value) use ($connection) {
                        return $value === null ? 'NULL' : $connection->getPdo()->quote($value);
                    }, array_values((array)$row));

                    $sql .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        return $sql;
    }

    /**
     * Find mysqldump executable path
     */
    private function findMysqldumpPath()
    {
        $paths = [
            'mysqldump',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\wamp\\bin\\mysql\\mysql8.0.xx\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
        ];

        foreach ($paths as $path) {
            $output = [];
            $returnVar = 0;
            @exec("$path --version 2>&1", $output, $returnVar);
            if ($returnVar === 0) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Import SQL into SQLite
     */
    private function importSqlite($sqlContent, $connection)
    {
        // Remove MySQL-specific syntax and comments
        $sqlContent = preg_replace('/SET\s+[^;]+;/i', '', $sqlContent);
        $sqlContent = preg_replace('/--.*$/m', '', $sqlContent);
        $sqlContent = preg_replace('/\/\*.*?\*\//s', '', $sqlContent);

        // Split SQL into individual statements
        // Handle multi-line statements better
        $sqlContent = preg_replace('/\r\n|\r/', "\n", $sqlContent);
        $statements = [];
        $currentStatement = '';

        $lines = explode("\n", $sqlContent);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || preg_match('/^--/', $line)) {
                continue;
            }

            $currentStatement .= $line . "\n";

            // Check if statement ends with semicolon
            if (substr(rtrim($line), -1) === ';') {
                $statement = trim($currentStatement);
                if (!empty($statement) && strlen($statement) > 1) {
                    $statements[] = $statement;
                }
                $currentStatement = '';
            }
        }

        // Execute statements
        foreach ($statements as $statement) {
            try {
                // Remove trailing semicolon for SQLite
                $statement = rtrim($statement, ';');
                if (!empty($statement)) {
                    $connection->statement($statement);
                }
            } catch (\Exception $e) {
                // Log error but continue
                \Log::warning('SQL statement failed during import: ' . $e->getMessage() . ' | Statement: ' . substr($statement, 0, 100));
            }
        }
    }

    /**
     * Import SQL into MySQL
     */
    private function importMysql($sqlContent, $connection)
    {
        // Remove comments and split into statements
        $sqlContent = preg_replace('/--.*$/m', '', $sqlContent);
        $sqlContent = preg_replace('/\/\*.*?\*\//s', '', $sqlContent);

        $statements = array_filter(
            array_map('trim', explode(';', $sqlContent)),
            function($statement) {
                return !empty($statement);
            }
        );

        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                try {
                    $connection->statement($statement);
                } catch (\Exception $e) {
                    // Log error but continue with other statements
                    \Log::warning('SQL statement failed: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Update .env file with new settings (optional - use with caution)
     */
    private function updateEnvFile(Request $request)
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        $updates = [
            'MAIL_MAILER' => $request->mail_mailer,
            'MAIL_HOST' => $request->mail_host,
            'MAIL_PORT' => $request->mail_port ?? 587,
            'MAIL_USERNAME' => $request->mail_username,
            'MAIL_PASSWORD' => $request->mail_password,
            'MAIL_ENCRYPTION' => $request->mail_encryption ?? 'tls',
            'MAIL_FROM_ADDRESS' => $request->mail_from_address,
            'MAIL_FROM_NAME' => $request->mail_from_name,
        ];

        foreach ($updates as $key => $value) {
            if ($value !== null) {
                $pattern = "/^{$key}=.*/m";
                $replacement = "{$key}={$value}";

                if (preg_match($pattern, $envContent)) {
                    $envContent = preg_replace($pattern, $replacement, $envContent);
                } else {
                    $envContent .= "\n{$replacement}";
                }
            }
        }

        file_put_contents($envPath, $envContent);
    }
}
