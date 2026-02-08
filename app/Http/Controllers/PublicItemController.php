<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImageMetadata;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class PublicItemController extends Controller
{
    /**
     * Show public item details page
     */
    public function show($uploadId)
    {
        // Get item by upload_id
        $items = ImageMetadata::where('upload_id', $uploadId)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($items->isEmpty()) {
            abort(404, 'Item not found');
        }

        $firstItem = $items->first();
        
        // Check if item is claimed or has pending claim - if so, restrict access
        $user = auth()->user();
        $isOwner = $user && $user->email === $firstItem->uploader_email;
        $isClaimer = $user && $firstItem->claimed_by_email === $user->email;
        
        if ($firstItem->is_claimed || $firstItem->claim_verification_status === 'pending') {
            // Allow access if user is admin, owner, or the claimer
            if (!$user || (!$this->isAdmin($user) && !$isOwner && !$isClaimer)) {
                if ($firstItem->is_claimed) {
                    abort(403, 'This item has been claimed and is only visible to administrators, the owner, or the claimer.');
                } else {
                    abort(403, 'This item has a pending claim and is only visible to the owner or the claimer.');
                }
            }
        }
        
        // Get uploader information
        $uploader = User::where('email', $firstItem->uploader_email)->first();

        // Format item data
        $item = [
            'upload_id' => $firstItem->upload_id,
            'item_type' => $firstItem->status,
            'description' => $firstItem->description,
            'location' => $firstItem->location ?? 'Location not specified',
            'tags' => $firstItem->tags ? (is_array($firstItem->tags) ? $firstItem->tags : json_decode($firstItem->tags, true)) : [],
            'uploader_email' => $firstItem->uploader_email,
            'uploader_name' => $uploader ? $uploader->name : 'Unknown User',
            'uploader_verified' => $uploader ? ($uploader->is_verified ?? false) : false,
            'created_at' => $firstItem->created_at,
            'is_claimed' => $firstItem->is_claimed ?? false,
            'images' => $items->map(function ($item) {
                $filePath = $item->file_path;
                if (str_starts_with($filePath, '/storage/')) {
                    $filePath = substr($filePath, 9);
                }
                return [
                    'path' => Storage::url($filePath),
                    'original_name' => $item->original_name ?? basename($filePath),
                    'filename' => $item->filename
                ];
            })->toArray()
        ];

        // Check if user is authenticated
        $user = auth()->user();
        $isOwner = $user && $user->email === $firstItem->uploader_email;
        $isClaimer = $user && $firstItem->claimed_by_email === $user->email;
        // Can claim if: user is logged in, not owner, not already claimed, and no pending claim
        $canClaim = $user && !$isOwner && !$firstItem->is_claimed && $firstItem->claim_verification_status !== 'pending';

        return view('public.item-details', compact('item', 'user', 'isOwner', 'canClaim', 'uploader'));
    }
    
    /**
     * Check if user is an admin based on email
     */
    private function isAdmin($user)
    {
        if (!$user) {
            return false;
        }
        
        $email = strtolower($user->email);
        return $email === 'admin@finditfast.com' || str_contains($email, 'admin@');
    }
}
