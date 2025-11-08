<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
}
