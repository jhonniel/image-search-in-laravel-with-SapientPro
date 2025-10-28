<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImageMetadata;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
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

            // Delete physical files
            foreach ($items as $item) {
                if ($item->file_path) {
                    // Convert URL to relative path if needed
                    $relativePath = str_replace('/storage/', '', $item->file_path);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }

            // Delete database records
            $deletedCount = ImageMetadata::where('upload_id', $uploadId)->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} item(s) and associated files"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display all users for admin
     */
    public function users()
    {
        $users = \App\Models\User::orderBy('created_at', 'desc')->get();

        return view('admin.users', compact('users'));
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

        return view('admin.claim-verify', compact('formattedItems'));
    }
}
