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
        
        // Get enabled cities from settings
        $enabledCitiesJson = \App\Models\Setting::get('enabled_cities', '[]');
        $enabledCities = json_decode($enabledCitiesJson, true) ?? [];
        sort($enabledCities);
        
        // Get enabled provinces from settings
        $enabledProvincesJson = \App\Models\Setting::get('enabled_provinces', '[]');
        $enabledProvinces = json_decode($enabledProvincesJson, true) ?? [];
        sort($enabledProvinces);
        
        // Get field visibility and requirement settings
        $enableProvinceField = \App\Models\Setting::get('enable_province_field', true);
        $provinceFieldRequired = \App\Models\Setting::get('province_field_required', true);
        $enableCityField = \App\Models\Setting::get('enable_city_field', true);
        $cityFieldRequired = \App\Models\Setting::get('city_field_required', true);
        
        return view('guest.post', compact('itemType', 'searchQuery', 'enabledCities', 'enabledProvinces', 
            'enableProvinceField', 'provinceFieldRequired', 'enableCityField', 'cityFieldRequired'));
    }

    public function submit(Request $request)
    {
        // Get enabled cities for validation
        $enabledCitiesJson = \App\Models\Setting::get('enabled_cities', '[]');
        $enabledCities = json_decode($enabledCitiesJson, true) ?? [];
        
        // Get enabled provinces for validation
        $enabledProvincesJson = \App\Models\Setting::get('enabled_provinces', '[]');
        $enabledProvinces = json_decode($enabledProvincesJson, true) ?? [];
        
        // Get field visibility and requirement settings
        $enableProvinceField = \App\Models\Setting::get('enable_province_field', true);
        $provinceFieldRequired = \App\Models\Setting::get('province_field_required', true);
        $enableCityField = \App\Models\Setting::get('enable_city_field', true);
        $cityFieldRequired = \App\Models\Setting::get('city_field_required', true);
        
        $rules = [
            'item_type' => 'required|in:lost,found',
            'location' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'tags' => 'required|string|max:255',
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'required|file|mimes:jpeg,jpg,png,gif,webp|max:10240',
        ];
        
        // Add province validation only if field is enabled
        if ($enableProvinceField) {
            if ($provinceFieldRequired) {
                if (!empty($enabledProvinces)) {
                    $rules['province'] = 'required|string|in:' . implode(',', $enabledProvinces);
                } else {
                    $rules['province'] = 'required|string';
                }
            } else {
                if (!empty($enabledProvinces)) {
                    $rules['province'] = 'nullable|string|in:' . implode(',', $enabledProvinces);
                } else {
                    $rules['province'] = 'nullable|string';
                }
            }
        }
        
        // Add city validation only if field is enabled
        if ($enableCityField) {
            if ($cityFieldRequired) {
                if (!empty($enabledCities)) {
                    $rules['city'] = 'required|string|in:' . implode(',', $enabledCities);
                } else {
                    $rules['city'] = 'required|string';
                }
            } else {
                if (!empty($enabledCities)) {
                    $rules['city'] = 'nullable|string|in:' . implode(',', $enabledCities);
                } else {
                    $rules['city'] = 'nullable|string';
                }
            }
        }
        
        $validated = $request->validate($rules, [
            'location.required' => 'Location is required. Please specify where the item was lost or found.',
            'description.required' => 'Description is required. Please describe the item.',
            'tags.required' => 'Tags are required. Please add at least one tag to help others find your item.',
            'tags.max' => 'Tags must not exceed 255 characters.',
            'province.required' => 'Please enter a province where the item was lost or found.',
            'province.in' => 'We\'re trying to expand our services to cover more locations. Please contact us if you\'d like to see your province added.',
            'city.required' => 'Please enter a city where the item was lost or found.',
            'city.in' => 'We\'re trying to expand our services to cover more locations. Please contact us if you\'d like to see your city added.',
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
            'province' => $validated['province'] ?? null,
            'city' => $validated['city'] ?? null,
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
                $metadataData = [
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
                ];
                
                // Only include province/city if they're provided in validated data
                if (isset($validated['province']) && $validated['province'] !== null && $validated['province'] !== '') {
                    $metadataData['province'] = $validated['province'];
                }
                if (isset($validated['city']) && $validated['city'] !== null && $validated['city'] !== '') {
                    $metadataData['city'] = $validated['city'];
                }
                
                $metadata = ImageMetadata::create($metadataData);

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
            return redirect()->route('dashboard')->with('success', "Your {$itemsSaved} item(s) have been posted successfully!");

        } catch (\Exception $e) {
            Log::error('Failed to save item for authenticated user: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return back()->withErrors(['error' => 'Failed to post item. Please try again.'])->withInput();
        }
    }
}


