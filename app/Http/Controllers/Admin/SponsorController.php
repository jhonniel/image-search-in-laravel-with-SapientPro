<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SponsorController extends Controller
{
    /**
     * Display a listing of sponsors
     */
    public function index(Request $request)
    {
        $showTrashed = $request->get('trashed', false);
        
        if ($showTrashed) {
            $sponsors = Sponsor::onlyTrashed()
                ->orderBy('deleted_at', 'desc')
                ->get();
        } else {
            $sponsors = Sponsor::orderBy('order', 'asc')
                ->orderBy('created_at', 'desc')
                ->get(); // Soft deletes automatically excludes deleted sponsors
        }
        
        // Get the setting for showing sponsors on landing page
        $showSponsors = \App\Models\Setting::get('show_sponsors_carousel', false);
        
        return view('admin.sponsors.index', compact('sponsors', 'showSponsors', 'showTrashed'));
    }

    /**
     * Store a newly created sponsor
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max
            'order' => 'nullable|integer|min:0',
        ]);

        try {
            // Upload image
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('sponsors', $filename, 'public');

            // Create sponsor
            $sponsor = Sponsor::create([
                'name' => $request->name,
                'image_path' => Storage::url($path),
                'is_active' => $request->has('is_active'),
                'order' => $request->order ?? 0,
            ]);

            return redirect()->route('admin.sponsors.index')
                ->with('success', 'Sponsor added successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to create sponsor: ' . $e->getMessage());
            return redirect()->route('admin.sponsors.index')
                ->with('error', 'Failed to add sponsor: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified sponsor
     */
    public function update(Request $request, Sponsor $sponsor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'order' => 'nullable|integer|min:0',
        ]);

        try {
            $data = [
                'name' => $request->name,
                'is_active' => $request->has('is_active'),
                'order' => $request->order ?? 0,
            ];

            // Update image if provided
            if ($request->hasFile('image')) {
                // Delete old image
                if ($sponsor->image_path) {
                    $oldPath = str_replace('/storage/', '', $sponsor->image_path);
                    Storage::disk('public')->delete($oldPath);
                }

                // Upload new image
                $image = $request->file('image');
                $filename = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('sponsors', $filename, 'public');
                $data['image_path'] = Storage::url($path);
            }

            $sponsor->update($data);

            return redirect()->route('admin.sponsors.index')
                ->with('success', 'Sponsor updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update sponsor: ' . $e->getMessage());
            return redirect()->route('admin.sponsors.index')
                ->with('error', 'Failed to update sponsor: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified sponsor (soft delete)
     */
    public function destroy(Sponsor $sponsor)
    {
        try {
            // Soft delete (image is kept for potential restore)
            $sponsor->delete(); // This will perform soft delete automatically

            return redirect()->route('admin.sponsors.index')
                ->with('success', 'Sponsor deleted successfully! It can be restored from the trash.');
        } catch (\Exception $e) {
            Log::error('Failed to delete sponsor: ' . $e->getMessage());
            return redirect()->route('admin.sponsors.index')
                ->with('error', 'Failed to delete sponsor: ' . $e->getMessage());
        }
    }
    
    /**
     * Restore deleted sponsor
     */
    public function restore($id)
    {
        try {
            $sponsor = Sponsor::onlyTrashed()->findOrFail($id);
            $sponsor->restore();

            return redirect()->route('admin.sponsors.index')
                ->with('success', 'Sponsor restored successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to restore sponsor: ' . $e->getMessage());
            return redirect()->route('admin.sponsors.index')
                ->with('error', 'Failed to restore sponsor: ' . $e->getMessage());
        }
    }
    
    /**
     * Force delete sponsor (permanent delete with image)
     */
    public function forceDelete($id)
    {
        try {
            $sponsor = Sponsor::onlyTrashed()->findOrFail($id);

            // Delete image
            if ($sponsor->image_path) {
                $oldPath = str_replace('/storage/', '', $sponsor->image_path);
                Storage::disk('public')->delete($oldPath);
            }

            $sponsor->forceDelete();

            return redirect()->route('admin.sponsors.index')
                ->with('success', 'Sponsor permanently deleted!');
        } catch (\Exception $e) {
            Log::error('Failed to permanently delete sponsor: ' . $e->getMessage());
            return redirect()->route('admin.sponsors.index')
                ->with('error', 'Failed to permanently delete sponsor: ' . $e->getMessage());
        }
    }

    /**
     * Toggle show sponsors carousel on landing page
     */
    public function toggleShow(Request $request)
    {
        $show = $request->input('show', false);
        \App\Models\Setting::set('show_sponsors_carousel', $show ? '1' : '0', 'boolean', 'Show sponsors carousel on landing page');
        
        return response()->json([
            'success' => true,
            'message' => $show ? 'Sponsors carousel enabled' : 'Sponsors carousel disabled'
        ]);
    }
}
