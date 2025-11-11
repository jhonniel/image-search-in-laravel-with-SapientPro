<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ImageMetadata;
use App\Models\User;
use App\Services\SimilarityNotificationService;
use Illuminate\Support\Str;
use SapientPro\ImageComparator\ImageComparator;

class GuestItemController extends Controller
{
    public function showForm(Request $request)
    {
        $itemType = $request->query('type', 'lost');
        $searchQuery = $request->query('search', '');
        return view('guest.post', compact('itemType', 'searchQuery'));
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'item_type' => 'required|in:lost,found',
            'location' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'tags' => 'nullable|string|max:255',
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'required|file|mimes:jpeg,jpg,png,gif,webp|max:10240',
        ]);

        // Check if user is already logged in
        if (Auth::check()) {
            // User is logged in - save directly to their account
            return $this->saveItemForAuthenticatedUser($request, $validated);
        }

        // User is not logged in - store in session and redirect to register
        $storedFiles = [];
        foreach ($request->file('images') as $index => $image) {
            $filename = time() . '_' . $index . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('temp-guest', $filename, 'public');
            $storedFiles[] = $path; // relative to public disk
        }

        $pending = [
            'item_type' => $validated['item_type'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'tags' => $validated['tags'] ?? null,
            'files' => $storedFiles,
        ];

        // Store in session
        $request->session()->put('guest_pending_item', $pending);
        
        // Log for debugging
        Log::info('Guest item stored in session', [
            'session_id' => $request->session()->getId(),
            'item_type' => $pending['item_type'],
            'files_count' => count($storedFiles),
            'files' => $storedFiles,
            'has_session' => $request->session()->has('guest_pending_item')
        ]);
        
        // Save session explicitly to ensure it persists
        $request->session()->save();
        
        return redirect()->route('register')->with('status', 'Create your account to finish posting your item.');
    }

    /**
     * Save item directly for authenticated user
     */
    private function saveItemForAuthenticatedUser(Request $request, array $validated)
    {
        try {
            $user = Auth::user();
            $uploadId = 'user_upload_' . Str::random(10);
            $itemsSaved = 0;
            $similarityService = new SimilarityNotificationService(app(ImageComparator::class));

            Log::info('Saving item for authenticated user', [
                'user_email' => $user->email,
                'user_id' => $user->id,
                'item_type' => $validated['item_type'],
                'files_count' => count($request->file('images'))
            ]);

            foreach ($request->file('images') as $index => $image) {
                $filename = time() . '_' . $index . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('user-items', $filename, 'public');

                // Create image metadata record
                $metadata = ImageMetadata::create([
                    'filename' => $filename,
                    'file_path' => Storage::url($path),
                    'original_name' => $image->getClientOriginalName(),
                    'uploader_email' => $user->email,
                    'description' => $validated['description'],
                    'location' => $validated['location'] ?? null,
                    'tags' => !empty($validated['tags']) ? array_map('trim', explode(',', $validated['tags'])) : [],
                    'file_size' => $image->getSize(),
                    'mime_type' => $image->getMimeType(),
                    'status' => $validated['item_type'],
                    'upload_id' => $uploadId,
                ]);

                $itemsSaved++;

                Log::info('Item saved for authenticated user', [
                    'metadata_id' => $metadata->id,
                    'upload_id' => $uploadId,
                    'uploader_email' => $metadata->uploader_email,
                    'user_email' => $user->email,
                ]);

                // Check for similar images and notify involved users
                try {
                    $similarityService->checkAndNotifySimilarities($metadata, $user->email);
                } catch (\Throwable $e) {
                    Log::error('Similarity check failed: ' . $e->getMessage());
                }
            }

            Log::info('Item posting completed for authenticated user', [
                'user_email' => $user->email,
                'user_id' => $user->id,
                'upload_id' => $uploadId,
                'items_saved' => $itemsSaved
            ]);

            // Redirect to user dashboard with success message
            return redirect()->route('user.dashboard')->with('success', "Your {$itemsSaved} item(s) have been posted successfully!");

        } catch (\Exception $e) {
            Log::error('Failed to save item for authenticated user: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return back()->withErrors(['error' => 'Failed to post item. Please try again.'])->withInput();
        }
    }
}


