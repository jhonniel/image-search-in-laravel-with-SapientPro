<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ImageMetadata;
use App\Models\Tag;
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
        // Set execution time limit to prevent hanging
        set_time_limit(300); // 5 minutes max
        
        Log::info('Guest post submit started', [
            'has_files' => $request->hasFile('images'),
            'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
            'is_authenticated' => Auth::check(),
            'user_id' => Auth::id()
        ]);
        
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
            'tags' => 'required|string', // JSON array string
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
        try {
            foreach ($request->file('images') as $index => $image) {
                if (!$image->isValid()) {
                    Log::error('Invalid file uploaded', [
                        'index' => $index,
                        'error' => $image->getError(),
                        'file_name' => $image->getClientOriginalName()
                    ]);
                    return back()->withErrors(['images' => 'One or more files failed to upload. Please try again.'])->withInput();
                }
                
                $filename = time() . '_' . $index . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('temp-guest', $filename, 'public');
                $storedFiles[] = $path; // relative to public disk
            }
        } catch (\Exception $e) {
            Log::error('Failed to store guest files', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['images' => 'Failed to upload files. Please try again.'])->withInput();
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

                // Analyze image with Google Vision API to detect objects
                $detectedObjects = null;
                try {
                    $isVisionEnabled = $this->isGoogleVisionEnabled();
                    if ($isVisionEnabled) {
                        $imagePath = $image->getPathname();
                        $visionData = $this->analyzeImageWithGoogleVision($imagePath);
                        
                        // Extract detected objects
                        if (isset($visionData['objects']) && !empty($visionData['objects'])) {
                            $detectedObjects = array_map(function($obj) {
                                return [
                                    'name' => $obj['name'] ?? '',
                                    'score' => $obj['score'] ?? 0.0,
                                ];
                            }, $visionData['objects']);
                        }
                    }
                } catch (\Exception $e) {
                    // Log error but don't fail the upload
                    \Illuminate\Support\Facades\Log::warning('Google Vision API analysis failed: ' . $e->getMessage());
                }

                // Create image metadata record
                $metadataData = [
                    'filename' => $filename,
                    'file_path' => Storage::url($path),
                    'original_name' => $image->getClientOriginalName(),
                    'uploader_email' => $user->email,
                    'description' => $validated['description'],
                    'location' => $validated['location'] ?? null,
                    'tags' => $this->processTags($validated['tags'] ?? ''),
                    'detected_objects' => $detectedObjects,
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

                // Check for similar images and notify involved users immediately.
                // Running this inline is more reliable than shutdown callbacks.
                try {
                    $similarityResult = $similarityService->checkAndNotifySimilarities($metadata, $user->email);

                    Log::info('Similarity check completed for guest post', [
                        'upload_id' => $uploadId,
                        'metadata_id' => $metadata->id,
                        'user_email' => $user->email,
                        'similar_items_found' => $similarityResult['similar_items_found'] ?? 0,
                        'notifications_sent' => $similarityResult['notifications_sent'] ?? 0,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Similarity check failed for guest post: ' . $e->getMessage(), [
                        'upload_id' => $uploadId,
                        'metadata_id' => $metadata->id ?? null,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            Log::info('Item posting completed for authenticated user', [
                'user_email' => $user->email,
                'user_id' => $user->id,
                'upload_id' => $uploadId,
                'items_saved' => $itemsSaved
            ]);

            // Redirect to reported items page with success message
            return redirect()->route('reported-items')->with('success', "Your {$itemsSaved} item(s) have been posted successfully!");

        } catch (\Exception $e) {
            Log::error('Failed to save item for authenticated user: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return back()->withErrors(['error' => 'Failed to post item. Please try again.'])->withInput();
        }
    }

    /**
     * Analyze image with Google Vision API to detect objects
     */
    private function analyzeImageWithGoogleVision(string $imagePath): array
    {
        try {
            // Check if Google Vision is enabled
            $isEnabled = $this->isGoogleVisionEnabled();
            if (!$isEnabled) {
                throw new \Exception('Google Vision API is not enabled. Enable it in admin settings or set GOOGLE_VISION_ENABLED=true.');
            }

            // Get API key from settings with env fallback
            $apiKey = $this->getGoogleVisionApiKey();
            
            if (empty($apiKey)) {
                throw new \Exception('Google Vision API key not configured. Save it in admin settings or set GOOGLE_VISION_API_KEY in .env.');
            }

            // Use REST API with API key
            $url = 'https://vision.googleapis.com/v1/images:annotate?key=' . urlencode($apiKey);
            
            $imageContent = file_get_contents($imagePath);
            
            $data = [
                'requests' => [
                    [
                        'image' => [
                            'content' => base64_encode($imageContent)
                        ],
                        'features' => [
                            ['type' => 'OBJECT_LOCALIZATION', 'maxResults' => 20],
                            ['type' => 'LABEL_DETECTION', 'maxResults' => 10],
                        ]
                    ]
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode !== 200) {
                $errorData = json_decode($response, true);
                $errorMessage = $errorData['error']['message'] ?? ($curlError ?: 'Unknown error');
                throw new \Exception('Google Vision API error: ' . $errorMessage);
            }

            $responseData = json_decode($response, true);
            $annotations = $responseData['responses'][0] ?? [];

            // Extract objects
            $objects = [];
            if (isset($annotations['localizedObjectAnnotations'])) {
                foreach ($annotations['localizedObjectAnnotations'] as $object) {
                    $objects[] = [
                        'name' => $object['name'] ?? '',
                        'score' => $object['score'] ?? 0.0,
                    ];
                }
            }

            // Extract labels as fallback
            $labels = [];
            if (isset($annotations['labelAnnotations'])) {
                foreach ($annotations['labelAnnotations'] as $label) {
                    $labels[] = [
                        'description' => $label['description'] ?? '',
                        'score' => $label['score'] ?? 0.0,
                    ];
                }
            }

            return [
                'objects' => $objects,
                'labels' => $labels,
            ];
        } catch (\Exception $e) {
            Log::error('Google Vision API analysis error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process tags from request (handles both JSON array and comma-separated string)
     * Also increments tag usage counts
     */
    private function processTags($tagsInput)
    {
        if (empty($tagsInput)) {
            return [];
        }

        $tagsArray = [];
        
        // Try to decode as JSON first
        $decoded = json_decode($tagsInput, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $tagsArray = array_map('trim', $decoded);
        } else {
            // Fallback to comma-separated string
            $tagsArray = array_map('trim', explode(',', $tagsInput));
        }

        // Filter out empty tags
        $tagsArray = array_filter($tagsArray, function($tag) {
            return !empty(trim($tag));
        });

        // Increment usage count for each tag
        foreach ($tagsArray as $tagName) {
            $tag = Tag::firstOrCreate(
                ['name' => trim($tagName)],
                ['usage_count' => 0]
            );
            $tag->incrementUsage();
        }

        return array_values($tagsArray);
    }

    /**
     * Determine whether Google Vision is enabled via settings or env.
     */
    private function isGoogleVisionEnabled(): bool
    {
        $dbEnabled = \App\Models\Setting::get('google_vision_enabled', null);
        if ($dbEnabled !== null) {
            return (bool) $dbEnabled;
        }

        return filter_var(env('GOOGLE_VISION_ENABLED', false), FILTER_VALIDATE_BOOL);
    }

    /**
     * Read Google Vision API key from settings, fallback to env.
     */
    private function getGoogleVisionApiKey(): string
    {
        $dbKey = \App\Models\Setting::get('google_vision_api_key', '');
        if (!empty($dbKey)) {
            return trim((string) $dbKey);
        }

        return trim((string) env('GOOGLE_VISION_API_KEY', ''));
    }
}


