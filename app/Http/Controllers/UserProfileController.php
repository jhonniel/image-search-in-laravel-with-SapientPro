<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class UserProfileController extends Controller
{
    /**
     * Show user profile
     */
    public function show()
    {
        $user = Auth::user();

        // Get user's reported items count
        $reportsCount = \App\Models\ImageMetadata::where('uploader_email', $user->email)->count();

        // Get user's recent activity
        $recentReports = \App\Models\ImageMetadata::where('uploader_email', $user->email)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get user's available rewards
        try {
            $rewards = \App\Models\Reward::where('user_id', $user->id)
                ->where('is_used', false)
                ->where('status', 'active')
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>=', now());
                })
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            // If rewards table doesn't exist, return empty collection
            \Log::error('Error fetching rewards: ' . $e->getMessage());
            $rewards = collect([]);
        }

        return view('user.profile', compact('user', 'reportsCount', 'recentReports', 'rewards'));
    }

    /**
     * Show user profile edit form
     */
    public function edit()
    {
        $user = Auth::user();
        return view('user.profile-edit', compact('user'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'code_name' => 'nullable|string|max:255|unique:users,code_name,' . $user->id,
                'current_password' => 'nullable|string',
                'new_password' => 'nullable|string|min:8|confirmed',
            ], [
                'code_name.unique' => 'This code name is already taken. Please choose a different one.',
            ]);
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'code_name' => $request->code_name ?: null,
            ];

            // Handle password change if provided
            if ($request->filled('current_password') && $request->filled('new_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current password is incorrect'
                    ], 422);
                }
                $updateData['password'] = Hash::make($request->new_password);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
        ]);

        try {
            $user = Auth::user();

            // Delete old avatar if exists
            if ($user->profile_picture) {
                $oldPath = str_replace('/storage/', '', $user->profile_picture);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Store new avatar
            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');

            // Update user profile picture
            $user->update([
                'profile_picture' => Storage::url($path)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar updated successfully',
                'avatar_url' => Storage::url($path)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar: ' . $e->getMessage()
            ], 500);
        }
    }
}
