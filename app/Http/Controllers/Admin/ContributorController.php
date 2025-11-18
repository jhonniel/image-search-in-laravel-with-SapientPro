<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ContributorController extends Controller
{
    /**
     * Display a listing of contributors
     */
    public function index(Request $request)
    {
        $showTrashed = $request->get('trashed', false);
        
        if ($showTrashed) {
            $contributors = Contributor::onlyTrashed()
                ->orderBy('deleted_at', 'desc')
                ->get();
        } else {
            $contributors = Contributor::orderBy('order', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('admin.contributors.index', compact('contributors', 'showTrashed'));
    }

    /**
     * Store a newly created contributor
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max
            'email' => 'nullable|email|max:255',
            'github' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'role' => $request->role,
                'bio' => $request->bio,
                'email' => $request->email,
                'github' => $request->github,
                'linkedin' => $request->linkedin,
                'twitter' => $request->twitter,
                'website' => $request->website,
                'is_active' => $request->has('is_active'),
                'order' => $request->order ?? 0,
            ];

            // Upload avatar if provided
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $filename = time() . '_' . $avatar->getClientOriginalName();
                $path = $avatar->storeAs('contributors', $filename, 'public');
                $data['avatar_path'] = Storage::url($path);
            }

            // Create contributor
            $contributor = Contributor::create($data);

            return redirect()->route('contributors.index')
                ->with('success', 'Contributor added successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create contributor: ' . $e->getMessage());
            return redirect()->route('contributors.index')
                ->with('error', 'Failed to add contributor: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified contributor
     */
    public function update(Request $request, Contributor $contributor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'email' => 'nullable|email|max:255',
            'github' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'role' => $request->role,
                'bio' => $request->bio,
                'email' => $request->email,
                'github' => $request->github,
                'linkedin' => $request->linkedin,
                'twitter' => $request->twitter,
                'website' => $request->website,
                'is_active' => $request->has('is_active'),
                'order' => $request->order ?? 0,
            ];

            // Update avatar if provided
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($contributor->avatar_path) {
                    $oldPath = str_replace('/storage/', '', $contributor->avatar_path);
                    Storage::disk('public')->delete($oldPath);
                }

                // Upload new avatar
                $avatar = $request->file('avatar');
                $filename = time() . '_' . $avatar->getClientOriginalName();
                $path = $avatar->storeAs('contributors', $filename, 'public');
                $data['avatar_path'] = Storage::url($path);
            }

            $contributor->update($data);

            return redirect()->route('contributors.index')
                ->with('success', 'Contributor updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update contributor: ' . $e->getMessage());
            return redirect()->route('contributors.index')
                ->with('error', 'Failed to update contributor: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified contributor
     */
    public function destroy(Contributor $contributor)
    {
        try {
            $contributor->delete();
            return redirect()->route('contributors.index')
                ->with('success', 'Contributor deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete contributor: ' . $e->getMessage());
            return redirect()->route('contributors.index')
                ->with('error', 'Failed to delete contributor: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft-deleted contributor
     */
    public function restore($id)
    {
        try {
            $contributor = Contributor::onlyTrashed()->findOrFail($id);
            $contributor->restore();
            return redirect()->route('contributors.index')
                ->with('success', 'Contributor restored successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to restore contributor: ' . $e->getMessage());
            return redirect()->route('contributors.index')
                ->with('error', 'Failed to restore contributor: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a contributor
     */
    public function forceDelete($id)
    {
        try {
            $contributor = Contributor::onlyTrashed()->findOrFail($id);
            
            // Delete avatar if exists
            if ($contributor->avatar_path) {
                $oldPath = str_replace('/storage/', '', $contributor->avatar_path);
                Storage::disk('public')->delete($oldPath);
            }
            
            $contributor->forceDelete();
            return redirect()->route('contributors.index')
                ->with('success', 'Contributor permanently deleted!');
        } catch (\Exception $e) {
            Log::error('Failed to force delete contributor: ' . $e->getMessage());
            return redirect()->route('contributors.index')
                ->with('error', 'Failed to permanently delete contributor: ' . $e->getMessage());
        }
    }
}
