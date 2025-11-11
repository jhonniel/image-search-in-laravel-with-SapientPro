<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ImageMetadata;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display user dashboard with statistics
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get user's items statistics
        $userItems = ImageMetadata::where('uploader_email', $user->email)
            ->get()
            ->groupBy('upload_id');
        
        $totalItems = $userItems->count();
        $lostItems = $userItems->filter(function ($group) {
            return $group->first()->status === 'lost';
        })->count();
        $foundItems = $userItems->filter(function ($group) {
            return $group->first()->status === 'found';
        })->count();
        $claimedItems = $userItems->filter(function ($group) {
            return $group->first()->is_claimed === true;
        })->count();
        
        // Calculate success rate (claimed items / total items)
        $successRate = $totalItems > 0 ? round(($claimedItems / $totalItems) * 100) : 0;
        
        // Get recent activity (all users' items, not just current user)
        $recentActivity = ImageMetadata::orderBy('created_at', 'desc')
            ->get()
            ->groupBy('upload_id')
            ->take(10)
            ->map(function ($itemGroup) {
                $firstItem = $itemGroup->first();
                $uploader = User::where('email', $firstItem->uploader_email)->first();
                
                return [
                    'name' => $uploader ? $uploader->name : 'Unknown',
                    'username' => $uploader ? ($uploader->username ?? substr($uploader->name, 0, 10)) : 'Unknown',
                    'item_name' => $firstItem->description ? \Illuminate\Support\Str::limit($firstItem->description, 30) : 'No description',
                    'item_type' => $firstItem->status,
                    'location' => $firstItem->description ? \Illuminate\Support\Str::limit($firstItem->description, 20) : 'N/A',
                    'posted_date' => $firstItem->created_at,
                ];
            })
            ->values();
        
        return view('user.dashboard', compact(
            'totalItems',
            'lostItems',
            'foundItems',
            'claimedItems',
            'successRate',
            'recentActivity'
        ));
    }

    /**
     * Display pending claims for user's items
     */
    public function pendingClaims()
    {
        $user = Auth::user();
        
        // Get items that belong to the user and have pending claims
        // Note: is_claimed might be false for pending claims (before verification)
        $pendingClaims = ImageMetadata::where('uploader_email', $user->email)
            ->where('claim_verification_status', 'pending')
            ->get()
            ->groupBy('upload_id')
            ->map(function ($group) {
                $firstItem = $group->first();
                $claimer = User::where('email', $firstItem->claimed_by_email)->first();
                
                return [
                    'upload_id' => $firstItem->upload_id,
                    'description' => $firstItem->description,
                    'location' => $firstItem->location,
                    'status' => $firstItem->status,
                    'tags' => $firstItem->tags,
                    'claimed_at' => $firstItem->claimed_at,
                    'claimer' => $claimer ? [
                        'id' => $claimer->id,
                        'name' => $claimer->name,
                        'email' => $claimer->email,
                        'is_verified' => $claimer->is_verified ?? false,
                    ] : null,
                    'images' => $group->map(function ($item) {
                        return [
                            'path' => $item->file_path,
                            'original_name' => $item->original_name,
                        ];
                    })->toArray(),
                ];
            })
            ->values();

        // Get verified/claimed items (view-only, cannot be modified)
        $verifiedClaims = ImageMetadata::where('uploader_email', $user->email)
            ->where('is_claimed', true)
            ->where('claim_verification_status', 'verified')
            ->get()
            ->groupBy('upload_id')
            ->map(function ($group) {
                $firstItem = $group->first();
                $claimer = User::where('email', $firstItem->claimed_by_email)->first();
                
                return [
                    'upload_id' => $firstItem->upload_id,
                    'description' => $firstItem->description,
                    'location' => $firstItem->location,
                    'status' => $firstItem->status,
                    'tags' => $firstItem->tags,
                    'claimed_at' => $firstItem->claimed_at,
                    'claim_verified_at' => $firstItem->claim_verified_at,
                    'claimer' => $claimer ? [
                        'id' => $claimer->id,
                        'name' => $claimer->name,
                        'email' => $claimer->email,
                        'is_verified' => $claimer->is_verified ?? false,
                    ] : null,
                    'images' => $group->map(function ($item) {
                        return [
                            'path' => $item->file_path,
                            'original_name' => $item->original_name,
                        ];
                    })->toArray(),
                ];
            })
            ->sortByDesc(function ($claim) {
                return $claim['claim_verified_at'] ?? $claim['claimed_at'];
            })
            ->values();

        return view('user.pending-claims', compact('pendingClaims', 'verifiedClaims'));
    }

    /**
     * Verify a claim (confirm the item belongs to the claimer)
     */
    public function verifyClaim(Request $request, $uploadId)
    {
        $user = Auth::user();

        try {
            // Verify the item belongs to the current user
            $items = ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->where('claim_verification_status', 'pending')
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item not found or claim already processed'
                ], 404);
            }

            $firstItem = $items->first();
            $claimerEmail = $firstItem->claimed_by_email;

            // Now mark as claimed AND verified (this is when it becomes officially claimed)
            ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->update([
                    'is_claimed' => true, // Mark as claimed when verified
                    'claim_verification_status' => 'verified',
                    'claim_verified_at' => now()
                ]);

            // Check if user has reached 50 verified returns and auto-verify them
            $verifiedReturns = ImageMetadata::where('uploader_email', $user->email)
                ->where('claim_verification_status', 'verified')
                ->select('upload_id')
                ->distinct()
                ->count();

            $wasJustVerified = false;
            if ($verifiedReturns >= 50 && !$user->is_verified) {
                $user->is_verified = true;
                $user->save();
                $wasJustVerified = true;
                
                Log::info('User auto-verified based on verified returns', [
                    'user_email' => $user->email,
                    'user_id' => $user->id,
                    'verified_returns' => $verifiedReturns
                ]);
            }

            // Send confirmation message to claimer
            $claimer = User::where('email', $claimerEmail)->first();
            if ($claimer) {
                try {
                    \App\Models\Message::create([
                        'sender_id' => $user->id,
                        'receiver_id' => $claimer->id,
                        'message' => "Thank you! I've verified that the item belongs to you. Let's arrange the return.",
                        'item_upload_id' => $uploadId,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send verification confirmation message: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Claim verified successfully!' . ($wasJustVerified ? ' You have been automatically verified for having 50 successful item returns!' : ''),
                'was_verified' => $wasJustVerified,
                'verified_returns' => $verifiedReturns
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to verify claim: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to verify claim: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a claim (item does not belong to the claimer)
     */
    public function rejectClaim(Request $request, $uploadId)
    {
        $user = Auth::user();

        try {
            // Verify the item belongs to the current user
            $items = ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->where('claim_verification_status', 'pending')
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item not found or claim already processed'
                ], 404);
            }

            $firstItem = $items->first();
            $claimerEmail = $firstItem->claimed_by_email;

            // Update all items in this upload to rejected status and clear claim info
            // Note: is_claimed might already be false for pending claims
            ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->update([
                    'is_claimed' => false,
                    'claim_verification_status' => 'rejected',
                    'claimed_by_email' => null,
                    'claimed_at' => null,
                ]);

            // Send rejection message to claimer
            $claimer = User::where('email', $claimerEmail)->first();
            if ($claimer) {
                try {
                    \App\Models\Message::create([
                        'sender_id' => $user->id,
                        'receiver_id' => $claimer->id,
                        'message' => "I'm sorry, but this item doesn't belong to you. Thank you for trying to help though!",
                        'item_upload_id' => $uploadId,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send rejection message: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Claim rejected. The item is now available for others to claim.'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reject claim: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to reject claim: ' . $e->getMessage()
            ], 500);
        }
    }
}
