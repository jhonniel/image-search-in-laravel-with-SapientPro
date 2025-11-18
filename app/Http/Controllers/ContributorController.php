<?php

namespace App\Http\Controllers;

use App\Models\Contributor;
use Illuminate\Http\Request;

class ContributorController extends Controller
{
    /**
     * Display all active contributors (public page)
     */
    public function index()
    {
        $contributors = Contributor::where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($contributor) {
                // Normalize avatar path
                if ($contributor->avatar_path) {
                    $avatarPath = $contributor->avatar_path;
                    // Ensure path starts with /storage/ for proper asset handling
                    if (!str_starts_with($avatarPath, 'http') && !str_starts_with($avatarPath, '/storage/')) {
                        if (str_starts_with($avatarPath, 'storage/')) {
                            $avatarPath = '/' . $avatarPath;
                        } else {
                            $avatarPath = \Illuminate\Support\Facades\Storage::url($avatarPath);
                        }
                    }
                    $contributor->avatar_path = $avatarPath;
                }
                return $contributor;
            });

        return view('contributors', compact('contributors'));
    }
}

