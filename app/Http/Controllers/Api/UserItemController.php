<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\ImageMetadata;
use App\Models\Tag;
use App\Services\SimilarityNotificationService;
use App\Mail\SimilarImageNotification;
use Illuminate\Support\Str;
use SapientPro\ImageComparator\ImageComparator;

class UserItemController extends Controller
{
    /**
     * Upload user reported items
     */
    public function uploadItems(Request $request)
    {
        // Check authentication first
        if (!Auth::check()) {
            Log::warning('Upload attempt by unauthenticated user', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'You must be logged in to upload items. Please log in and try again.'
            ], 401);
        }

        $user = Auth::user();
        
        // Debug: Log the incoming request data
        $files = $request->file('images');
        $fileDetails = [];
        
        // Check if files were uploaded
        if (!$files || (is_array($files) && count($files) === 0)) {
            Log::warning('Upload attempt with no files', [
                'user_id' => $user->id,
                'has_files_key' => $request->has('images'),
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'content_length' => $request->header('Content-Length')
            ]);
            
            // Check if request exceeded POST size limit
            $postMaxSize = $this->parseSize(ini_get('post_max_size'));
            $contentLength = $request->header('Content-Length');
            if ($contentLength && $contentLength > $postMaxSize) {
                return response()->json([
                    'success' => false,
                    'error' => 'File size too large',
                    'message' => 'The total size of your upload exceeds the server limit (' . ini_get('post_max_size') . '). Please reduce the file sizes and try again.'
                ], 413);
            }
            
            return response()->json([
                'success' => false,
                'error' => 'No files uploaded',
                'message' => 'Please select at least one image to upload.'
            ], 400);
        }
        
        if ($files) {
            foreach ($files as $index => $file) {
                // Check for upload errors
                if (!$file->isValid()) {
                    $errorCode = $file->getError();
                    $errorMessage = $this->getUploadErrorMessage($errorCode);
                    
                    Log::error('File upload error', [
                        'user_id' => $user->id,
                        'file_index' => $index,
                        'file_name' => $file->getClientOriginalName(),
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'error' => 'File upload error',
                        'message' => $errorMessage . ' (File: ' . $file->getClientOriginalName() . ')'
                    ], 400);
                }
                
                $fileDetails[] = [
                    'index' => $index,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'is_valid' => $file->isValid(),
                    'error' => $file->getError()
                ];
            }
        }

        Log::info('User upload request received', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'files_count' => $files ? count($files) : 0,
            'file_details' => $fileDetails,
            'file_names' => $files ? array_map(fn($f) => $f->getClientOriginalName(), $files) : [],
            'request_keys' => array_keys($request->all())
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
            'images.*' => 'required|file|mimes:jpeg,jpg,png,gif,webp|max:10240', // 10MB max per image
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
        
        $validator = Validator::make($request->all(), $rules, [
            'item_type.required' => 'Please select whether this is a lost or found item',
            'item_type.in' => 'Item type must be either lost or found',
            'location.required' => 'Location is required',
            'location.max' => 'Location must not exceed 255 characters',
            'province.required' => 'Please enter a province where the item was lost or found.',
            'province.in' => 'We\'re trying to expand our services to cover more locations. Please contact us if you\'d like to see your province added.',
            'city.required' => 'Please enter a city where the item was lost or found.',
            'city.in' => 'We\'re trying to expand our services to cover more locations. Please contact us if you\'d like to see your city added.',
            'description.required' => 'Description is required',
            'description.max' => 'Description must not exceed 1000 characters',
            'tags.required' => 'Tags are required. Please add at least one tag to help others find your item.',
            'images.required' => 'At least one image is required',
            'images.array' => 'Images must be an array',
            'images.min' => 'At least one image is required',
            'images.max' => 'Maximum 5 images allowed',
            'images.*.required' => 'Each image is required',
            'images.*.file' => 'Each file must be a valid file',
            'images.*.mimes' => 'Each file must be a valid image (JPEG, JPG, PNG, GIF, WEBP)',
            'images.*.max' => 'Each image must not exceed 10MB',
        ]);

        if ($validator->fails()) {
            Log::error('User upload validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all(),
                'file_details' => $fileDetails
            ]);

            // Try to provide more helpful error messages
            $errorMessages = [];
            foreach ($validator->errors()->toArray() as $field => $errors) {
                if (str_contains($field, 'images.')) {
                    $index = str_replace('images.', '', $field);
                    if (isset($fileDetails[$index])) {
                        $file = $fileDetails[$index];
                        $errorMessages[$field] = [
                            'message' => $errors[0],
                            'file_info' => [
                                'name' => $file['original_name'],
                                'mime_type' => $file['mime_type'],
                                'size' => $file['size'],
                                'is_valid' => $file['is_valid'],
                                'error_code' => $file['error']
                            ]
                        ];
                    } else {
                        $errorMessages[$field] = $errors;
                    }
                } else {
                    $errorMessages[$field] = $errors;
                }
            }

            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $errorMessages,
                'message' => 'Please check your uploaded files. Make sure they are valid image files (JPEG, PNG, GIF, WEBP) and not corrupted.'
            ], 400);
        }

        try {
            // User is already authenticated (checked above)
            $uploadId = 'user_upload_' . Str::random(10);
            $uploadedImages = [];
            $similarityService = new SimilarityNotificationService(app(ImageComparator::class));

            // Deduplicate files based on name, size, and content
            $uniqueFiles = [];
            $processedFiles = [];

            foreach ($request->file('images') as $index => $image) {
                $fileKey = $image->getClientOriginalName() . '_' . $image->getSize() . '_' . $image->getMimeType();
                if (!in_array($fileKey, $processedFiles)) {
                    $uniqueFiles[] = $image;
                    $processedFiles[] = $fileKey;
                } else {
                    Log::info('Duplicate file skipped', [
                        'filename' => $image->getClientOriginalName(),
                        'size' => $image->getSize(),
                        'mime_type' => $image->getMimeType()
                    ]);
                }
            }

            Log::info('File deduplication', [
                'original_count' => count($request->file('images')),
                'unique_count' => count($uniqueFiles)
            ]);

            // Process each unique uploaded image
            foreach ($uniqueFiles as $index => $image) {
                $filename = time() . '_' . $index . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('user-items', $filename, 'public');

                // Analyze image with Google Vision API to detect objects
                $detectedObjects = null;
                try {
                    $isVisionEnabled = \App\Models\Setting::get('google_vision_enabled', false);
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
                        
                        Log::info('Google Vision API analysis completed', [
                            'upload_id' => $uploadId,
                            'objects_count' => $detectedObjects ? count($detectedObjects) : 0
                        ]);
                    }
                } catch (\Exception $e) {
                    // Log error but don't fail the upload
                    Log::warning('Google Vision API analysis failed: ' . $e->getMessage(), [
                        'upload_id' => $uploadId
                    ]);
                }

                // Create image metadata record
                $metadataData = [
                    'filename' => $filename,
                    'file_path' => Storage::url($path),
                    'original_name' => $image->getClientOriginalName(),
                    'uploader_email' => $user->email, // Use authenticated user's email
                    'description' => $request->description,
                    'location' => $request->location, // Save location field
                    'tags' => $this->processTags($request->tags),
                    'detected_objects' => $detectedObjects,
                    'file_size' => $image->getSize(),
                    'mime_type' => $image->getMimeType(),
                    'status' => $request->item_type, // 'lost' or 'found'
                    'upload_id' => $uploadId,
                ];
                
                // Only include province/city if they're provided in the request
                if ($request->has('province') && $request->province !== null && $request->province !== '') {
                    $metadataData['province'] = $request->province;
                }
                if ($request->has('city') && $request->city !== null && $request->city !== '') {
                    $metadataData['city'] = $request->city;
                }
                
                $imageMetadata = ImageMetadata::create($metadataData);

                $uploadedImages[] = [
                    'filename' => $filename,
                    'path' => Storage::url($path),
                    'original_name' => $image->getClientOriginalName(),
                    'size' => $image->getSize(),
                ];

                // Check for similarities with existing items - run asynchronously
                // This prevents blocking the upload response
                try {
                    // Use register_shutdown_function to run after response is sent
                    // This allows the upload to complete immediately while similarity check runs in background
                    register_shutdown_function(function() use ($similarityService, $imageMetadata, $user) {
                        try {
                            // Set time limit for background processing
                            set_time_limit(60); // 60 seconds for background similarity check
                            
                            Log::info('Starting background similarity check', [
                                'upload_id' => $imageMetadata->upload_id,
                                'user_email' => $user->email
                            ]);
                            
                            $similarityService->checkAndNotifySimilarities($imageMetadata, $user->email);
                            
                            Log::info('Background similarity check completed', [
                                'upload_id' => $imageMetadata->upload_id
                            ]);
                        } catch (\Throwable $e) {
                            Log::error('Background similarity check failed: ' . $e->getMessage(), [
                                'upload_id' => $imageMetadata->upload_id ?? null,
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    });
                    
                    // Also try fastcgi_finish_request if available (for better async execution)
                    if (function_exists('fastcgi_finish_request')) {
                        // This will be called after the response is sent
                    }
                } catch (\Exception $e) {
                    // Log error but don't fail the upload
                    Log::error('Failed to register similarity check: ' . $e->getMessage());
                }
            }

            // Create in-app notification for successful upload
            try {
                $user = Auth::user();
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'type' => 'item_uploaded',
                    'title' => 'Item uploaded successfully',
                    'message' => 'Your ' . ($request->item_type === 'lost' ? 'lost' : 'found') . ' item has been uploaded successfully.',
                    'data' => [
                        'upload_id' => $uploadId,
                        'item_type' => $request->item_type,
                    ],
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create upload notification: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'upload_id' => $uploadId,
                    'item_type' => $request->item_type,
                    'location' => $request->location,
                    'description' => $request->description,
                    'tags' => $this->processTags($request->tags),
                    'contact_email' => $request->contact_email,
                    'contact_phone' => $request->contact_phone,
                    'images' => $uploadedImages,
                    'uploaded_at' => now()->toISOString(),
                ],
                'message' => 'Item reported successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Upload failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's reported items
     */
    public function getUserItems()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            // Get all items uploaded by the user
            // Users should see all their own items regardless of claim status
            $items = ImageMetadata::where('uploader_email', $user->email)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('upload_id');

            $formattedItems = [];
            foreach ($items as $uploadId => $itemGroup) {
                $firstItem = $itemGroup->first();
                
                // Parse tags if they're stored as JSON string
                $tags = $firstItem->tags;
                if (is_string($tags)) {
                    $decodedTags = json_decode($tags, true);
                    $tags = $decodedTags !== null ? $decodedTags : (strpos($tags, ',') !== false ? explode(',', $tags) : [$tags]);
                }
                
                $formattedItems[] = [
                    'upload_id' => $uploadId,
                    'item_type' => $firstItem->status ?? 'lost',
                    'location' => $firstItem->location ?? 'Location not specified',
                    'province' => $firstItem->province ?? null,
                    'city' => $firstItem->city ?? null,
                    'description' => $firstItem->description ?? '',
                    'tags' => $tags ?? [],
                    'contact_email' => $firstItem->uploader_email,
                    'images' => $itemGroup->map(function ($item) {
                        // Handle file path - ensure it's a valid URL
                        $filePath = $item->file_path;
                        
                        // Normalize the path - ensure it starts with /storage/
                        if (empty($filePath)) {
                            $imagePath = '';
                        } elseif (str_starts_with($filePath, '/storage/')) {
                            // Already in correct format, use as is
                            $imagePath = $filePath;
                        } elseif (str_starts_with($filePath, 'storage/')) {
                            // Missing leading slash, add it
                            $imagePath = '/' . $filePath;
                        } elseif (str_starts_with($filePath, 'http')) {
                            // Full URL, use as is
                            $imagePath = $filePath;
                        } else {
                            // Relative path, use Storage::url to generate proper path
                            $imagePath = Storage::url($filePath);
                        }

                        return [
                            'filename' => $item->filename ?? basename($filePath),
                            'path' => $imagePath,
                            'original_name' => $item->original_name ?? basename($filePath),
                            'size' => $item->file_size ?? 0,
                        ];
                    })->toArray(),
                    // Use ISO 8601 string for created_at to be safely consumed by JS
                    'created_at' => $firstItem->created_at ? $firstItem->created_at->toIso8601String() : now()->toIso8601String(),
                ];
            }

            \Log::info('User items loaded', [
                'user_email' => $user->email,
                'items_count' => count($formattedItems),
                'upload_ids' => array_column($formattedItems, 'upload_id')
            ]);

            return response()->json([
                'success' => true,
                'data' => $formattedItems
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load items',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user item
     */
    public function updateItem(Request $request, $uploadId)
    {
        try {
            $user = Auth::user();

            // Get all request data - handle both PUT and POST with method spoofing
            $requestData = $request->all();
            
            // If using method spoofing, the actual method might be POST
            $actualMethod = $request->method();
            $isMethodSpoofed = $request->has('_method');
            
            // Log request data for debugging
            \Log::info('Update item request received', [
                'upload_id' => $uploadId,
                'method' => $actualMethod,
                'is_method_spoofed' => $isMethodSpoofed,
                'content_type' => $request->header('Content-Type'),
                'has_location' => $request->has('location'),
                'has_description' => $request->has('description'),
                'location_value' => $request->input('location'),
                'description_value' => $request->input('description'),
                'all_keys' => array_keys($requestData),
                'request_all' => $requestData,
                'raw_input' => file_get_contents('php://input')
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
                'item_type' => 'sometimes|required|in:lost,found',
                'location' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'tags' => 'nullable|string', // JSON array string
                'images' => 'nullable|array|max:5',
                'images.*' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:10240',
                'remove_images' => 'nullable|array',
                'remove_images.*' => 'nullable|string',
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
            
            // Validate request - location and description are always sent from form
            $validator = Validator::make($requestData, $rules, [
                'item_type.required' => 'Item type is required',
                'item_type.in' => 'Item type must be either lost or found',
                'location.required' => 'Location is required',
                'location.max' => 'Location must not exceed 255 characters',
                'province.required' => 'Please select a province where the item was lost or found.',
                'province.in' => 'Please select a valid province from the list.',
                'city.required' => 'Please select a city where the item was lost or found.',
                'city.in' => 'Please select a valid city from the list.',
                'description.required' => 'Description is required',
                'description.max' => 'Description must not exceed 1000 characters',
                'images.array' => 'Images must be an array',
                'images.max' => 'Maximum 5 images allowed',
                'images.*.file' => 'Each file must be a valid file',
                'images.*.mimes' => 'Each file must be a valid image (JPEG, PNG, GIF, WEBP)',
                'images.*.max' => 'Each image must not exceed 10MB',
            ]);

            if ($validator->fails()) {
                \Log::error('Item update validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->except(['images', 'remove_images']),
                    'upload_id' => $uploadId,
                    'location_received' => $request->input('location'),
                    'description_received' => $request->input('description'),
                    'all_request_data' => $requestData
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'message' => 'Please check your input. ' . implode(' ', $validator->errors()->all()),
                    'errors' => $validator->errors(),
                    'debug' => [
                        'has_location' => $request->has('location'),
                        'has_description' => $request->has('description'),
                        'location_value' => $request->input('location'),
                        'description_value' => $request->input('description'),
                        'all_keys' => array_keys($requestData)
                    ]
                ], 400);
            }

            // Find items by upload_id and user email
            $items = ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item not found or access denied'
                ], 404);
            }

            $firstItem = $items->first();

            // Update basic fields if provided
            $updateData = [];
            
            if ($request->has('item_type') && $request->filled('item_type')) {
                $updateData['status'] = $request->item_type;
            }
            
            // Province is required, so always update it if present
            if ($request->has('province')) {
                $updateData['province'] = trim($request->province);
            }
            
            // City is required, so always update it if present
            if ($request->has('city')) {
                $updateData['city'] = trim($request->city);
            }
            
            // Location is required, so always update it if present
            if ($request->has('location')) {
                $updateData['location'] = trim($request->location);
            }
            
            // Description is required, so always update it if present
            if ($request->has('description')) {
                $updateData['description'] = trim($request->description);
            }
            
            if ($request->has('tags')) {
                $tagsValue = $request->input('tags');
                if ($tagsValue !== null) {
                    $updateData['tags'] = $this->processTags($tagsValue);
                }
            }

            // Update all items with this upload_id
            if (!empty($updateData)) {
                ImageMetadata::where('upload_id', $uploadId)
                    ->where('uploader_email', $user->email)
                    ->update($updateData);
            }

            // Get current image count
            $currentImageCount = $items->count();
            $removeImagesCount = 0;
            $newImagesCount = 0;

            // Handle image removal
            if ($request->has('remove_images') && is_array($request->remove_images) && !empty($request->remove_images)) {
                // Ensure user doesn't remove all images
                if (count($request->remove_images) >= $currentImageCount && !$request->hasFile('images')) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Cannot remove all images. Please add at least one new image before removing all existing ones, or keep at least one image.'
                    ], 400);
                }

                $removeImagesCount = count($request->remove_images);
                
                foreach ($request->remove_images as $filename) {
                    $itemToRemove = ImageMetadata::where('upload_id', $uploadId)
                        ->where('uploader_email', $user->email)
                        ->where('filename', $filename)
                        ->first();
                    
                    if ($itemToRemove) {
                        // Delete physical file
                        if ($itemToRemove->file_path) {
                            $relativePath = str_replace('/storage/', '', $itemToRemove->file_path);
                            if (Storage::disk('public')->exists($relativePath)) {
                                Storage::disk('public')->delete($relativePath);
                            }
                        }
                        
                        // Soft delete the image record
                        $itemToRemove->delete();
                    }
                }
            }

            // Handle new image uploads
            if ($request->hasFile('images')) {
                $newImagesCount = count($request->file('images'));
                
                // Validate total image count (current - removed + new) doesn't exceed 5
                $remainingImages = $currentImageCount - $removeImagesCount;
                $totalAfterUpdate = $remainingImages + $newImagesCount;
                
                if ($totalAfterUpdate > 5) {
                    return response()->json([
                        'success' => false,
                        'error' => "Maximum 5 images allowed. You currently have {$remainingImages} image(s) remaining. You can only add " . (5 - $remainingImages) . " more image(s)."
                    ], 400);
                }
                
                // Ensure at least one image remains
                if ($totalAfterUpdate < 1) {
                    return response()->json([
                        'success' => false,
                        'error' => 'At least one image is required for each item.'
                    ], 400);
                }

                foreach ($request->file('images') as $index => $image) {
                    $filename = time() . '_' . $index . '_' . $image->getClientOriginalName();
                    $path = $image->storeAs('user-items', $filename, 'public');

                    // Create new image metadata record
                    $newMetadataData = [
                        'filename' => $filename,
                        'file_path' => Storage::url($path),
                        'original_name' => $image->getClientOriginalName(),
                        'uploader_email' => $user->email,
                        'description' => $updateData['description'] ?? $firstItem->description,
                        'location' => $updateData['location'] ?? $firstItem->location,
                        'tags' => $updateData['tags'] ?? $firstItem->tags,
                        'file_size' => $image->getSize(),
                        'mime_type' => $image->getMimeType(),
                        'status' => $updateData['status'] ?? $firstItem->status,
                        'upload_id' => $uploadId,
                    ];
                    
                    // Only include province/city if they exist in updateData or firstItem
                    if (isset($updateData['province']) && $updateData['province'] !== null && $updateData['province'] !== '') {
                        $newMetadataData['province'] = $updateData['province'];
                    } elseif ($firstItem->province) {
                        $newMetadataData['province'] = $firstItem->province;
                    }
                    
                    if (isset($updateData['city']) && $updateData['city'] !== null && $updateData['city'] !== '') {
                        $newMetadataData['city'] = $updateData['city'];
                    } elseif ($firstItem->city) {
                        $newMetadataData['city'] = $firstItem->city;
                    }
                    
                    $newImageMetadata = ImageMetadata::create($newMetadataData);
                }
            } else {
                // If no new images and all images are being removed, check if at least one remains
                if ($removeImagesCount > 0 && ($currentImageCount - $removeImagesCount) < 1) {
                    return response()->json([
                        'success' => false,
                        'error' => 'At least one image is required for each item. Please add a new image before removing all existing ones.'
                    ], 400);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update item: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to update item',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user item (soft delete)
     */
    public function deleteItem(Request $request, $uploadId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized',
                    'message' => 'You must be logged in to delete items'
                ], 401);
            }

            Log::info('Delete item request', [
                'upload_id' => $uploadId,
                'user_email' => $user->email,
                'user_id' => $user->id
            ]);

            // Find items by upload_id and user email
            $items = ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->get();

            if ($items->isEmpty()) {
                Log::warning('Delete item - not found or access denied', [
                    'upload_id' => $uploadId,
                    'user_email' => $user->email
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Item not found or access denied',
                    'message' => 'The item you are trying to delete does not exist or you do not have permission to delete it.'
                ], 404);
            }

            // Soft delete database records (files are kept for potential restore)
            $deletedCount = ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->delete(); // This will perform soft delete automatically

            Log::info('Items soft deleted', [
                'upload_id' => $uploadId,
                'deleted_count' => $deletedCount,
                'user_email' => $user->email
            ]);

            // Broadcast item deleted event for real-time updates in chat
            try {
                broadcast(new \App\Events\ItemDeleted($uploadId, $user->id))->toOthers();
            } catch (\Exception $e) {
                Log::warning('Failed to broadcast item deleted event', [
                    'error' => $e->getMessage()
                ]);
                // Don't fail the delete if broadcast fails
            }

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully. It can be restored from the trash.',
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete item', [
                'upload_id' => $uploadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete item',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Restore deleted user item
     */
    public function restoreItem(Request $request, $uploadId)
    {
        try {
            $user = Auth::user();

            // Find trashed items by upload_id and user email
            $items = ImageMetadata::onlyTrashed()
                ->where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Deleted item not found or access denied'
                ], 404);
            }

            // Restore all items with this upload_id
            ImageMetadata::onlyTrashed()
                ->where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->restore();

            return response()->json([
                'success' => true,
                'message' => 'Item restored successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to restore item',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Force delete user item (permanent delete with files)
     */
    public function forceDeleteItem(Request $request, $uploadId)
    {
        try {
            $user = Auth::user();

            // Find trashed items by upload_id and user email
            $items = ImageMetadata::onlyTrashed()
                ->where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Deleted item not found or access denied'
                ], 404);
            }

            // Delete physical files
            foreach ($items as $item) {
                if ($item->file_path) {
                    $relativePath = str_replace('/storage/', '', $item->file_path);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }

            // Permanently delete database records
            ImageMetadata::onlyTrashed()
                ->where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Item permanently deleted'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to permanently delete item',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trashed items for the current user
     */
    public function getTrashedItems(Request $request)
    {
        try {
            $user = Auth::user();

            // Get trashed items for the user
            $items = ImageMetadata::onlyTrashed()
                ->where('uploader_email', $user->email)
                ->orderBy('deleted_at', 'desc')
                ->get()
                ->groupBy('upload_id');

            $formattedItems = [];
            foreach ($items as $uploadId => $itemGroup) {
                $firstItem = $itemGroup->first();
                $tags = $firstItem->tags ? (is_string($firstItem->tags) ? json_decode($firstItem->tags, true) : $firstItem->tags) : [];

                $formattedItems[] = [
                    'upload_id' => $uploadId,
                    'item_type' => $firstItem->status,
                    'description' => $firstItem->description,
                    'tags' => is_array($tags) ? $tags : [],
                    'contact_email' => $firstItem->uploader_email,
                    'images' => $itemGroup->map(function ($item) {
                        // Handle file path - ensure it's a valid URL
                        $filePath = $item->file_path;
                        
                        // Normalize the path - ensure it starts with /storage/
                        if (empty($filePath)) {
                            $imagePath = '';
                        } elseif (str_starts_with($filePath, '/storage/')) {
                            // Already in correct format, use as is
                            $imagePath = $filePath;
                        } elseif (str_starts_with($filePath, 'storage/')) {
                            // Missing leading slash, add it
                            $imagePath = '/' . $filePath;
                        } elseif (str_starts_with($filePath, 'http')) {
                            // Full URL, use as is
                            $imagePath = $filePath;
                        } else {
                            // Relative path, use Storage::url to generate proper path
                            $imagePath = Storage::url($filePath);
                        }

                        return [
                            'filename' => $item->filename,
                            'path' => $imagePath,
                            'original_name' => $item->original_name,
                            'size' => $item->file_size,
                        ];
                    })->toArray(),
                    'created_at' => $firstItem->created_at->toISOString(),
                    'deleted_at' => $firstItem->deleted_at->toISOString(),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $formattedItems
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load trashed items',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get items from other users (not current user) that match user's reported items
     */
    public function getOtherUsersItems(Request $request)
    {
        $user = Auth::user();

        try {
            // IMPORTANT: This function is called every time a user visits the claim-verify page
            // It checks all user's reported items against all other users' items for matches
            // If matches are found, notifications are created for both users
            // All matched items are then listed in "Available Items" section
            
            // First, get user's reported items
            $userItems = ImageMetadata::where('uploader_email', $user->email)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('upload_id');
            
            // If user has no reported items, return empty array
            if ($userItems->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'items' => [],
                    'message' => 'No items to match. Report a lost or found item first to see matching items.'
                ]);
            }
            
            // Get items from other users, grouped by upload_id
            // For matching purposes, include ALL items (even claimed ones) so both users can see matches
            // We'll filter by claim status later when displaying, but include all for similarity checking
            $allOtherItems = ImageMetadata::where('uploader_email', '!=', $user->email)
                ->whereNotNull('uploader_email')
                ->whereNotNull('file_path')
                ->whereNotNull('filename')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('upload_id');
            
            // Filter items to only show those that match user's reported items
            // This includes both directions: user's items matching others AND others' items matching user's items
            $similarityService = new SimilarityNotificationService(app(ImageComparator::class));
            $matchedItems = [];
            $matchedUploadIds = [];
            
            // First pass: Check if user's items match other users' items (existing logic)
            foreach ($userItems as $uploadId => $userItemGroup) {
                $userItem = $userItemGroup->first();

                foreach ($allOtherItems as $otherUploadId => $otherItemGroup) {
                    // Skip if already matched
                    if (in_array($otherUploadId, $matchedUploadIds)) {
                        continue;
                    }
                    
                    $otherItem = $otherItemGroup->first();
                    
                    // Only match Lost with Found and Found with Lost (opposite types)
                    $userItemStatus = $userItem->status;
                    $otherItemStatus = $otherItem->status;
                    
                    // Skip if both items have the same status (Lost-Lost or Found-Found)
                    if ($userItemStatus === $otherItemStatus) {
                        continue;
                    }
                    
                    // Ensure opposite types: Lost ↔ Found
                    if (!(($userItemStatus === 'lost' && $otherItemStatus === 'found') || 
                          ($userItemStatus === 'found' && $otherItemStatus === 'lost'))) {
                        continue;
                    }
                    
                    // Calculate similarity - compare all images from user item against all images from other item
                    try {
                        $maxVisualSimilarity = 0.0;
                        $userItemImages = $userItemGroup->filter(function($item) {
                            return $this->getItemFilePath($item) !== null;
                        });
                        $otherItemImages = $otherItemGroup->filter(function($item) {
                            return $this->getItemFilePath($item) !== null;
                        });
                        
                        if ($userItemImages->isEmpty() || $otherItemImages->isEmpty()) {
                            Log::debug('Skipping similarity check - no valid images', [
                                'user_item' => $userItem->upload_id,
                                'user_images_count' => $userItemImages->count(),
                                'other_item' => $otherItem->upload_id,
                                'other_images_count' => $otherItemImages->count(),
                            ]);
                            continue;
                        }
                        
                        // Compare all images and take the maximum similarity
                        foreach ($userItemImages as $userImg) {
                            $userItemPath = $this->getItemFilePath($userImg);
                            if (!$userItemPath) continue;
                            
                            foreach ($otherItemImages as $otherImg) {
                                $otherItemPath = $this->getItemFilePath($otherImg);
                                if (!$otherItemPath) continue;
                                
                                $visualSimilarity = $this->calculateImageSimilarity($userItemPath, $otherItemPath);
                                $maxVisualSimilarity = max($maxVisualSimilarity, $visualSimilarity);
                            }
                        }
                        
                        if ($maxVisualSimilarity === 0.0) {
                            Log::debug('Skipping - visual similarity is 0', [
                                'user_item' => $userItem->upload_id,
                                'other_item' => $otherItem->upload_id,
                            ]);
                            continue;
                        }
                        
                        Log::debug('Similarity calculation', [
                            'user_item' => $userItem->upload_id,
                            'other_item' => $otherItem->upload_id,
                            'visual_similarity' => $maxVisualSimilarity,
                        ]);
                        
                        // Calculate text similarity
                        $userTags = is_array($userItem->tags) ? $userItem->tags : (is_string($userItem->tags) ? json_decode($userItem->tags, true) : []);
                        $otherTags = is_array($otherItem->tags) ? $otherItem->tags : (is_string($otherItem->tags) ? json_decode($otherItem->tags, true) : []);
                        
                        // Check for tag overlap
                        $tagOverlap = 0;
                        if (!empty($userTags) && !empty($otherTags)) {
                            $commonTags = array_intersect($userTags, $otherTags);
                            $tagOverlap = count($commonTags) / max(count($userTags), count($otherTags));
                        }
                        
                        // Check description similarity (simple word overlap)
                        $userDescWords = str_word_count(strtolower($userItem->description ?? ''), 1);
                        $otherDescWords = str_word_count(strtolower($otherItem->description ?? ''), 1);
                        $descOverlap = 0;
                        if (!empty($userDescWords) && !empty($otherDescWords)) {
                            $commonWords = array_intersect($userDescWords, $otherDescWords);
                            $descOverlap = count($commonWords) / max(count($userDescWords), count($otherDescWords));
                        }
                        
                        // Calculate overall similarity (weighted average)
                        $textSimilarity = ($tagOverlap * 0.4) + ($descOverlap * 0.6);
                        $overallSimilarity = ($textSimilarity * 0.3) + ($maxVisualSimilarity * 0.7);
                        
                        // Use a lower threshold (0.5) for claim-verify to ensure all notified items show
                        // Notification service uses 0.7-0.8, so items that trigger notifications (>=0.7) will definitely show here (>=0.5)
                        // This ensures items that were notified also appear on claim-verify page
                        $visualThreshold = 0.5; // Lower threshold to show more matches including all notified ones
                        
                        // If similarity meets threshold, add to matched items
                        // This works bidirectionally: when User A views the page, they see User B's items that match User A's items
                        // When User B views the page, they see User A's items that match User B's items
                        // Since similarity is symmetric, both users will see the match
                        if ($overallSimilarity >= $visualThreshold) {
                            Log::info('Match found on claim-verify', [
                                'current_user' => $user->email,
                                'user_item' => $userItem->upload_id,
                                'user_item_status' => $userItem->status,
                                'matched_item' => $otherUploadId,
                                'matched_item_status' => $otherItem->status,
                                'matched_item_owner' => $otherItem->uploader_email,
                                'similarity' => $overallSimilarity,
                                'visual_similarity' => $maxVisualSimilarity,
                                'text_similarity' => $textSimilarity,
                                'threshold' => $visualThreshold
                            ]);
                            
                            $matchedUploadIds[] = $otherUploadId;
                            $matchedItems[$otherUploadId] = [
                                'item' => $otherItemGroup,
                                'similarity' => $overallSimilarity,
                                'matched_with' => $userItem->upload_id
                            ];
                            
                            // Notify both users about the match when viewing claim-verify
                            try {
                                // Notify the other user (owner of matched item) about the match
                                if ($otherItem->uploader_email && $otherItem->uploader_email !== $user->email) {
                                    $otherUser = \App\Models\User::where('email', $otherItem->uploader_email)->first();
                                    if ($otherUser) {
                                        $similarityPercent = round($overallSimilarity * 100, 2);
                                        $matchType = ($userItem->status === 'lost' && $otherItem->status === 'found') ? 
                                                    'Someone found an item that matches your lost item!' : 
                                                    'Someone lost an item that matches your found item!';
                                        
                                        // Check if notification already exists to avoid duplicates
                                        $existingNotification = \App\Models\Notification::where('user_id', $otherUser->id)
                                            ->where('type', 'item_matched')
                                            ->where('data->new_item_upload_id', $userItem->upload_id)
                                            ->where('data->matched_item_upload_id', $otherItem->upload_id)
                                            ->first();
                                        
                                        if (!$existingNotification) {
                                            \App\Models\Notification::create([
                                                'user_id' => $otherUser->id,
                                                'type' => 'item_matched',
                                                'title' => 'Item Match Found!',
                                                'message' => $matchType . ' (Similarity: ' . $similarityPercent . '%)',
                                                'data' => [
                                                    'matched_item_upload_id' => $otherItem->upload_id,
                                                    'matched_item_id' => $otherItem->id,
                                                    'matched_item_type' => $otherItem->status,
                                                    'matched_item_description' => $otherItem->description,
                                                    'matched_item_location' => $otherItem->location,
                                                    'new_item_upload_id' => $userItem->upload_id,
                                                    'new_item_id' => $userItem->id,
                                                    'new_item_type' => $userItem->status,
                                                    'new_item_description' => $userItem->description,
                                                    'new_item_location' => $userItem->location,
                                                    'similarity_score' => $overallSimilarity,
                                                    'similarity_percent' => $similarityPercent,
                                                ],
                                            ]);
                                            
                                            Log::info('Match notification created for other user', [
                                                'owner_email' => $otherItem->uploader_email,
                                                'matched_item' => $otherItem->upload_id,
                                                'user_item' => $userItem->upload_id,
                                                'similarity' => $overallSimilarity
                                            ]);
                                        }
                                    }
                                }
                                
                                // Also notify the current user about the match
                                $similarityPercent = round($overallSimilarity * 100, 2);
                                $matchType = ($userItem->status === 'lost' && $otherItem->status === 'found') ? 
                                            'Found item matches your lost item!' : 
                                            'Lost item matches your found item!';
                                
                                // Check if notification already exists
                                $existingNotification = \App\Models\Notification::where('user_id', $user->id)
                                    ->where('type', 'item_matched')
                                    ->where('data->new_item_upload_id', $otherItem->upload_id)
                                    ->where('data->matched_item_upload_id', $userItem->upload_id)
                                    ->first();
                                
                                if (!$existingNotification) {
                                    \App\Models\Notification::create([
                                        'user_id' => $user->id,
                                        'type' => 'item_matched',
                                        'title' => 'Item Match Found!',
                                        'message' => $matchType . ' (Similarity: ' . $similarityPercent . '%)',
                                        'data' => [
                                            'matched_item_upload_id' => $userItem->upload_id,
                                            'matched_item_id' => $userItem->id,
                                            'matched_item_type' => $userItem->status,
                                            'matched_item_description' => $userItem->description,
                                            'matched_item_location' => $userItem->location,
                                            'new_item_upload_id' => $otherItem->upload_id,
                                            'new_item_id' => $otherItem->id,
                                            'new_item_type' => $otherItem->status,
                                            'new_item_description' => $otherItem->description,
                                            'new_item_location' => $otherItem->location,
                                            'similarity_score' => $overallSimilarity,
                                            'similarity_percent' => $similarityPercent,
                                        ],
                                    ]);
                                    
                                    Log::info('Match notification created for current user', [
                                        'user_email' => $user->email,
                                        'matched_item' => $userItem->upload_id,
                                        'other_item' => $otherItem->upload_id,
                                        'similarity' => $overallSimilarity
                                    ]);
                                }
                            } catch (\Exception $e) {
                                Log::warning('Failed to create match notification', [
                                    'error' => $e->getMessage(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                            }
                        } else {
                            Log::debug('Similarity below threshold', [
                                'user_item' => $userItem->upload_id,
                                'other_item' => $otherItem->upload_id,
                                'overall_similarity' => $overallSimilarity,
                                'threshold' => $visualThreshold,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Error calculating similarity for matching items', [
                            'user_item' => $userItem->upload_id,
                            'other_item' => $otherItem->upload_id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        continue;
                    }
                }
            }
            
            // Log matching results for debugging
            Log::info('Claim-verify matching results', [
                'current_user' => $user->email,
                'user_items_count' => $userItems->count(),
                'other_items_count' => $allOtherItems->count(),
                'matches_found' => count($matchedItems),
                'matched_upload_ids' => array_keys($matchedItems),
                'timestamp' => now()->toDateTimeString()
            ]);
            
            // Summary: Every time user visits claim-verify, we check all their items against all other items
            // and create notifications for both users if matches are found
            if (count($matchedItems) > 0) {
                Log::info('Matches found on claim-verify page visit', [
                    'user' => $user->email,
                    'total_matches' => count($matchedItems),
                    'notifications_created' => 'Both users notified if notifications did not already exist'
                ]);
            }
            
            // If no matches found, return empty array
            if (empty($matchedItems)) {
                return response()->json([
                    'success' => true,
                    'items' => [],
                    'message' => 'No matching items found. Keep checking back as new items are posted!'
                ]);
            }
            
            // Also check for items that have "Item Match Found!" notifications for the current user
            // This ensures items that triggered notifications are always shown on claim-verify
            $userNotifications = \App\Models\Notification::where('type', 'item_matched')
                ->where('user_id', $user->id)
                ->whereNotNull('data')
                ->get();
            
            // Add notified matches to the matched items list if they're not already included
            foreach ($userNotifications as $notification) {
                $notifData = $notification->data ?? [];
                $newItemUploadId = $notifData['new_item_upload_id'] ?? null; // Other user's item
                $matchedItemUploadId = $notifData['matched_item_upload_id'] ?? null; // Current user's item
                
                // The "new_item_upload_id" is the other user's item that we want to show on claim-verify
                if ($newItemUploadId && !isset($matchedItems[$newItemUploadId])) {
                    // Get the other user's item group
                    $otherItemGroup = $allOtherItems->get($newItemUploadId);
                    if ($otherItemGroup) {
                        $otherItem = $otherItemGroup->first();
                        
                        // Verify it's still a valid match (opposite types)
                        $userItemForMatch = null;
                        if ($matchedItemUploadId && isset($userItems[$matchedItemUploadId])) {
                            $userItemForMatch = $userItems[$matchedItemUploadId]->first();
                        }
                        
                        // Only add if it's a valid match (Lost ↔ Found) or if we can't verify (include anyway)
                        if (!$userItemForMatch || 
                            ($userItemForMatch->status === 'lost' && $otherItem->status === 'found') ||
                            ($userItemForMatch->status === 'found' && $otherItem->status === 'lost')) {
                            
                            $matchedItems[$newItemUploadId] = [
                                'item' => $otherItemGroup,
                                'similarity' => $notifData['similarity_score'] ?? 0.5,
                                'matched_with' => $matchedItemUploadId ?? $userItems->keys()->first(),
                                'from_notification' => true // Flag to indicate this came from a notification
                            ];
                            
                            Log::info('Added notified match to claim-verify', [
                                'notification_id' => $notification->id,
                                'other_item_upload_id' => $newItemUploadId,
                                'user_item_upload_id' => $matchedItemUploadId,
                                'user_id' => $user->id,
                                'similarity' => $notifData['similarity_score'] ?? 0.5
                            ]);
                        }
                    }
                }
            }
            
            // Show ALL matched items regardless of claim status
            // This ensures all similar and matched items are visible on the claim-verify page
            // The frontend will display the claim status so users know which items are available
            $claimableMatchedItems = [];
            foreach ($matchedItems as $otherUploadId => $matchData) {
                $group = $matchData['item'];
                $firstItem = $group->first();
                
                // Include ALL matched items - don't filter by claim status
                // This ensures all similar items are listed, even if they're claimed/verified
                // The UI will show the status so users can see which items are available
                $claimableMatchedItems[$otherUploadId] = $matchData;
                
                Log::debug('Including matched item in claim-verify', [
                    'upload_id' => $otherUploadId,
                    'claim_status' => $firstItem->claim_verification_status,
                    'is_claimed' => $firstItem->is_claimed ?? false,
                    'similarity' => $matchData['similarity'] ?? 0
                ]);
            }
            
            // Format matched items
            $items = collect($claimableMatchedItems)
                ->map(function ($matchData) use ($user, $userItems) {
                    $group = $matchData['item'];
                    $firstItem = $group->first();
                    $tags = $firstItem->tags ? (is_string($firstItem->tags) ? json_decode($firstItem->tags, true) : $firstItem->tags) : [];
                    $detectedObjects = $firstItem->detected_objects ? (is_string($firstItem->detected_objects) ? json_decode($firstItem->detected_objects, true) : $firstItem->detected_objects) : [];

                    // Get the user who uploaded this item
                    $uploader = \App\Models\User::where('email', $firstItem->uploader_email)->first();

                    // Check if current user has claimed this item
                    $userHasClaimed = $firstItem->claim_verification_status === 'pending' 
                        && $firstItem->claimed_by_email === $user->email;

                    // Get the user's matched item details
                    $matchedWithUploadId = $matchData['matched_with'];
                    $userMatchedItem = null;
                    if ($matchedWithUploadId && isset($userItems[$matchedWithUploadId])) {
                        $userItemGroup = $userItems[$matchedWithUploadId];
                        $userFirstItem = $userItemGroup->first();
                        $userTags = $userFirstItem->tags ? (is_string($userFirstItem->tags) ? json_decode($userFirstItem->tags, true) : $userFirstItem->tags) : [];
                        
                        $userDetectedObjects = $userFirstItem->detected_objects ? (is_string($userFirstItem->detected_objects) ? json_decode($userFirstItem->detected_objects, true) : $userFirstItem->detected_objects) : [];
                        
                        $userMatchedItem = [
                            'upload_id' => $matchedWithUploadId,
                            'item_type' => $userFirstItem->status,
                            'description' => $userFirstItem->description,
                            'location' => $userFirstItem->location ?? 'Location not specified',
                            'province' => $userFirstItem->province ?? null,
                            'city' => $userFirstItem->city ?? null,
                            'tags' => is_array($userTags) ? $userTags : [],
                            'detected_objects' => is_array($userDetectedObjects) ? $userDetectedObjects : [],
                            'created_at' => $userFirstItem->created_at,
                            'images' => $userItemGroup->map(function ($item) {
                                $filePath = $item->file_path;
                                
                                if (empty($filePath)) {
                                    $imagePath = '';
                                } elseif (str_starts_with($filePath, '/storage/')) {
                                    $imagePath = $filePath;
                                } elseif (str_starts_with($filePath, 'storage/')) {
                                    $imagePath = '/' . $filePath;
                                } elseif (str_starts_with($filePath, 'http')) {
                                    $imagePath = $filePath;
                                } else {
                                    $imagePath = Storage::url($filePath);
                                }

                                return [
                                    'path' => $imagePath,
                                    'original_name' => $item->original_name ?? basename($filePath)
                                ];
                            })->toArray()
                        ];
                    }

                    return [
                        'upload_id' => $firstItem->upload_id,
                        'item_type' => $firstItem->status,
                        'description' => $firstItem->description,
                        'location' => $firstItem->location ?? 'Location not specified',
                        'province' => $firstItem->province ?? null,
                        'city' => $firstItem->city ?? null,
                        'tags' => is_array($tags) ? $tags : [],
                        'detected_objects' => is_array($detectedObjects) ? $detectedObjects : [],
                        'uploader_email' => $firstItem->uploader_email,
                        'uploader_name' => $uploader ? $uploader->name : 'Unknown User',
                        'uploader_profile_picture' => $uploader ? $uploader->profile_picture : null,
                        'uploader_verified' => $uploader ? ($uploader->is_verified ?? false) : false,
                        'created_at' => $firstItem->created_at,
                        'user_has_claimed' => $userHasClaimed,
                        'claim_status' => $firstItem->claim_verification_status,
                        'claimed_by_email' => $firstItem->claimed_by_email,
                        'similarity_score' => round($matchData['similarity'] * 100, 2),
                        'matched_with_upload_id' => $matchedWithUploadId,
                        'user_matched_item' => $userMatchedItem, // Add user's matched item details
                        'images' => $group->map(function ($item) {
                            // Handle file path - ensure it's a valid URL
                            $filePath = $item->file_path;
                            
                            // Normalize the path - ensure it starts with /storage/
                            if (empty($filePath)) {
                                $imagePath = '';
                            } elseif (str_starts_with($filePath, '/storage/')) {
                                // Already in correct format, use as is
                                $imagePath = $filePath;
                            } elseif (str_starts_with($filePath, 'storage/')) {
                                // Missing leading slash, add it
                                $imagePath = '/' . $filePath;
                            } elseif (str_starts_with($filePath, 'http')) {
                                // Full URL, use as is
                                $imagePath = $filePath;
                            } else {
                                // Relative path, use Storage::url to generate proper path
                                $imagePath = Storage::url($filePath);
                            }

                            return [
                                'path' => $imagePath,
                                'original_name' => $item->original_name ?? basename($filePath)
                            ];
                        })->toArray()
                    ];
                })
                ->sortByDesc('similarity_score')
                ->values()
                ->toArray();
            
            return response()->json([
                'success' => true,
                'items' => $items,
                'message' => count($items) . ' matching item(s) found based on your reported items.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching matching items: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch matching items: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get file path for an item
     */
    private function getItemFilePath(ImageMetadata $item): ?string
    {
        // Try multiple path formats to find the file
        $possiblePaths = [];
        
        // If we have a filename, try direct path
        if ($item->filename) {
            $possiblePaths[] = storage_path('app/public/user-items/' . $item->filename);
            $possiblePaths[] = storage_path('app/public/reference-images/' . $item->filename);
        }
        
        // If we have file_path, extract filename and try
        if ($item->file_path) {
            // Handle different path formats: /storage/user-items/file.jpg, storage/user-items/file.jpg, user-items/file.jpg
            $filePath = $item->file_path;
            
            // Remove /storage/ prefix if present
            if (str_starts_with($filePath, '/storage/')) {
                $filePath = substr($filePath, 9); // Remove '/storage/'
            } elseif (str_starts_with($filePath, 'storage/')) {
                $filePath = substr($filePath, 8); // Remove 'storage/'
            }
            
            // Extract just the filename
            $filename = basename($filePath);
            
            // Try user-items first (most common)
            $possiblePaths[] = storage_path('app/public/user-items/' . $filename);
            $possiblePaths[] = storage_path('app/public/reference-images/' . $filename);
            
            // Also try with the full relative path
            if (str_contains($filePath, 'user-items')) {
                $possiblePaths[] = storage_path('app/public/' . $filePath);
            }
        }
        
        // Check each possible path
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                Log::debug('Found file path for item', [
                    'item_id' => $item->id,
                    'upload_id' => $item->upload_id,
                    'file_path' => $item->file_path,
                    'filename' => $item->filename,
                    'resolved_path' => $path
                ]);
                return $path;
            }
        }
        
        Log::warning('Could not find file path for item', [
            'item_id' => $item->id,
            'upload_id' => $item->upload_id,
            'file_path' => $item->file_path,
            'filename' => $item->filename,
            'tried_paths' => $possiblePaths
        ]);
        
        return null;
    }
    
    /**
     * Calculate image similarity using ImageComparator
     */
    private function calculateImageSimilarity(string $image1Path, string $image2Path): float
    {
        try {
            $imageComparator = app(ImageComparator::class);
            $similarity = $imageComparator->compare($image1Path, $image2Path);
            return $similarity > 1 ? $similarity / 100 : $similarity;
        } catch (\Exception $e) {
            Log::warning('Could not compare images: ' . $e->getMessage());
            return 0.0;
        }
    }

    public function claimItem($uploadId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            // First check if item exists
            $itemExists = ImageMetadata::where('upload_id', $uploadId)->exists();
            if (!$itemExists) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item not found'
                ], 404);
            }

            // Check if user is trying to claim their own item
            $ownItem = ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->exists();
            if ($ownItem) {
                return response()->json([
                    'success' => false,
                    'error' => 'You cannot claim your own item'
                ], 400);
            }
            
            // Verify that the user has a LOST item and the item to claim is FOUND
            // Users with FOUND items can only message, not claim
            $itemToClaim = ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', '!=', $user->email)
                ->first();
            
            if (!$itemToClaim) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item not found.'
                ], 404);
            }
            
            // Check if user has a LOST item that matches this FOUND item
            $userItems = ImageMetadata::where('uploader_email', $user->email)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('upload_id');
            
            $hasLostItem = false;
            foreach ($userItems as $userUploadId => $userItemGroup) {
                $userItem = $userItemGroup->first();
                if ($userItem->status === 'lost' && $itemToClaim->status === 'found') {
                    $hasLostItem = true;
                    break;
                }
            }
            
            if (!$hasLostItem) {
                return response()->json([
                    'success' => false,
                    'error' => 'You can only claim items if you have a lost item. If you found an item, please message the owner to notify them.'
                ], 400);
            }

            // Check if item already has a pending claim
            $pendingClaim = ImageMetadata::where('upload_id', $uploadId)
                ->where('claim_verification_status', 'pending')
                ->exists();
            if ($pendingClaim) {
                return response()->json([
                    'success' => false,
                    'error' => 'This item already has a pending claim. Please wait for the owner to verify it.'
                ], 400);
            }

            // Check if item is already verified/claimed
            $verifiedClaim = ImageMetadata::where('upload_id', $uploadId)
                ->where('is_claimed', true)
                ->where('claim_verification_status', 'verified')
                ->exists();
            if ($verifiedClaim) {
                return response()->json([
                    'success' => false,
                    'error' => 'This item has already been claimed and verified.'
                ], 400);
            }

            // Find the item (must not be owned by the current user and not already claimed/verified/pending)
            $items = ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', '!=', $user->email)
                ->where(function($query) {
                    // Only allow claiming if:
                    // 1. Not claimed at all (including NULL), OR
                    // 2. Claimed but rejected (can be claimed again)
                    $query->where(function($q) {
                        $q->where(function($r){
                            $r->where('is_claimed', false)
                              ->orWhereNull('is_claimed');
                        })
                          ->where(function($subQ) {
                              $subQ->whereNull('claim_verification_status')
                                   ->orWhere('claim_verification_status', '!=', 'pending');
                          });
                    })
                    ->orWhere(function($q) {
                        $q->where('is_claimed', true)
                          ->where('claim_verification_status', 'rejected');
                    });
                })
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item cannot be claimed at this time. It may already have a pending claim or be verified.'
                ], 400);
            }

            // Get the original owner of the item (the person who posted it)
            $firstItem = $items->first();
            $itemOwnerEmail = $firstItem->uploader_email;
            $itemOwner = \App\Models\User::where('email', $itemOwnerEmail)->first();

            if (!$itemOwner) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item owner not found'
                ], 404);
            }

            // Set pending claim status WITHOUT marking as claimed yet
            // Item will only be marked as claimed when owner verifies
            ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', '!=', $user->email)
                ->update([
                    'claimed_by_email' => $user->email,
                    'claimed_at' => now(),
                    'claim_verification_status' => 'pending'
                    // Note: is_claimed remains false until owner verifies
                ]);

            // Broadcast item claimed event for real-time updates in chat
            broadcast(new \App\Events\ItemClaimed($uploadId, $user->id, $itemOwner->id, 'pending'))->toOthers();

            // Send notification message to the item owner
            try {
                // Get all images for this item
                $images = $items->map(function ($item) {
                    // Handle file path - ensure it's a valid URL
                    $filePath = $item->file_path;
                    
                    // Normalize the path - ensure it starts with /storage/
                    if (empty($filePath)) {
                        $imagePath = '';
                    } elseif (str_starts_with($filePath, '/storage/')) {
                        // Already in correct format, use as is
                        $imagePath = $filePath;
                    } elseif (str_starts_with($filePath, 'storage/')) {
                        // Missing leading slash, add it
                        $imagePath = '/' . $filePath;
                    } elseif (str_starts_with($filePath, 'http')) {
                        // Full URL, use as is
                        $imagePath = $filePath;
                    } else {
                        // Relative path, use Storage::url to generate proper path
                        $imagePath = \Illuminate\Support\Facades\Storage::url($filePath);
                    }
                    
                    return [
                        'path' => $imagePath,
                        'original_name' => $item->original_name ?? basename($filePath),
                        'filename' => $item->filename
                    ];
                })->toArray();

                // Create comprehensive item context for both users
                $itemContext = [
                    'upload_id' => $uploadId,
                    'uploadId' => $uploadId,
                    'description' => $firstItem->description,
                    'location' => $firstItem->location ?? 'Location not specified',
                    'item_type' => $firstItem->status,
                    'itemType' => $firstItem->status,
                    'status' => $firstItem->status,
                    'tags' => $firstItem->tags ? (is_string($firstItem->tags) ? json_decode($firstItem->tags, true) : $firstItem->tags) : [],
                    'uploader_name' => $itemOwner->name,
                    'uploader_email' => $firstItem->uploader_email,
                    'images' => $images,
                    'claim_status' => 'pending',
                    'claimed_by_id' => $user->id,
                    'created_at' => $firstItem->created_at->toIso8601String(),
                ];

                $claimMessage = "Hello! I believe I found your {$firstItem->status} item. Please verify if this item belongs to me so I can return it to you.";

                \App\Models\Message::create([
                    'sender_id' => $user->id,
                    'receiver_id' => $itemOwner->id,
                    'message' => $claimMessage,
                    'item_upload_id' => $uploadId,
                    'item_context' => json_encode($itemContext),
                ]);

                // Also update any existing messages with this item to include claim status
                \App\Models\Message::where('item_upload_id', $uploadId)
                    ->where(function($query) use ($user, $itemOwner) {
                        $query->where(function($q) use ($user, $itemOwner) {
                            $q->where('sender_id', $user->id)
                              ->where('receiver_id', $itemOwner->id);
                        })->orWhere(function($q) use ($user, $itemOwner) {
                            $q->where('sender_id', $itemOwner->id)
                              ->where('receiver_id', $user->id);
                        });
                    })
                    ->whereNotNull('item_context')
                    ->get()
                    ->each(function($message) use ($itemContext) {
                        $existingContext = json_decode($message->item_context, true);
                        if ($existingContext) {
                            $existingContext['claim_status'] = 'pending';
                            $existingContext['claimed_by_id'] = $itemContext['claimed_by_id'];
                            $message->update(['item_context' => json_encode($existingContext)]);
                        }
                    });

                Log::info('Claim notification message sent', [
                    'claimer_id' => $user->id,
                    'owner_id' => $itemOwner->id,
                    'upload_id' => $uploadId
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send claim notification message: ' . $e->getMessage());
            }

            // In-app notification for the owner
            try {
                \App\Models\Notification::create([
                    'user_id' => $itemOwner->id,
                    'type' => 'item_claimed',
                    'title' => 'Someone claimed your item',
                    'message' => $user->name . ' requested to claim your ' . ($firstItem->status ?? 'item') . '.',
                    'data' => [
                        'upload_id' => $uploadId,
                        'claimer_id' => $user->id,
                        'claimer_name' => $user->name,
                    ],
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed creating in-app notification: ' . $e->getMessage());
            }

            // Send email notification to the item owner
            try {
                // Check if email notifications are enabled
                $emailNotificationsEnabled = \App\Models\Setting::get('email_notifications', true);
                
                if ($emailNotificationsEnabled) {
                    // Apply mail configuration from settings
                    $this->applyMailConfigurationFromSettings();

                    $emailData = [
                        'notification_type' => 'item_claimed',
                        'item_type' => $firstItem->status,
                        'item_description' => $firstItem->description,
                        'item_location' => $firstItem->location ?? 'Location not specified',
                        'item_tags' => $firstItem->tags ? (is_array($firstItem->tags) ? $firstItem->tags : json_decode($firstItem->tags, true)) : [],
                        'user_email' => $itemOwnerEmail,
                        'owner_name' => $itemOwner->name,
                        'claimer_name' => $user->name,
                        'claimer_email' => $user->email,
                        'claimer_id' => $user->id,
                        'upload_id' => $uploadId,
                        'upload_date' => $firstItem->created_at->format('M d, Y'),
                        'claimed_at' => now()->format('M d, Y h:i A'),
                    ];

                    Mail::to($itemOwnerEmail)->send(new \App\Mail\UserItemNotification($emailData));

                    Log::info('Claim notification email sent', [
                        'claimer_id' => $user->id,
                        'owner_email' => $itemOwnerEmail,
                        'upload_id' => $uploadId
                    ]);
                } else {
                    Log::info('Email notifications disabled - skipping claim notification email', [
                        'owner_email' => $itemOwnerEmail
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send claim notification email: ' . $e->getMessage());
                // Don't fail the claim if email fails
            }

            return response()->json([
                'success' => true,
                'message' => 'Item claim request sent! The item owner has been notified and will verify your claim. You can message them to discuss the details.',
                'owner_name' => $itemOwner->name,
                'owner_id' => $itemOwner->id,
                'upload_id' => $uploadId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to claim item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a claim on an item
     */
    public function cancelClaim($uploadId)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            // Find items with pending claim by current user
            $items = ImageMetadata::where('upload_id', $uploadId)
                ->where('claim_verification_status', 'pending')
                ->where('claimed_by_email', $user->email)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'No pending claim found for this item'
                ], 404);
            }

            // Clear the claim
            ImageMetadata::where('upload_id', $uploadId)
                ->where('claim_verification_status', 'pending')
                ->where('claimed_by_email', $user->email)
                ->update([
                    'claimed_by_email' => null,
                    'claimed_at' => null,
                    'claim_verification_status' => null
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Claim cancelled successfully. The item is now available for others to claim.'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cancel claim: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to cancel claim: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply mail configuration from database settings
     */
    private function applyMailConfigurationFromSettings(): void
    {
        try {
            // Get mail settings from database
            $mailMailer = \App\Models\Setting::get('mail_mailer', env('MAIL_MAILER', 'log'));
            $mailHost = \App\Models\Setting::get('mail_host', env('MAIL_HOST'));
            $mailPort = \App\Models\Setting::get('mail_port', env('MAIL_PORT', 587));
            $mailUsername = \App\Models\Setting::get('mail_username', env('MAIL_USERNAME'));
            $mailPassword = \App\Models\Setting::get('mail_password', env('MAIL_PASSWORD'));
            $mailEncryption = \App\Models\Setting::get('mail_encryption', env('MAIL_ENCRYPTION', 'tls'));
            $mailFromAddress = \App\Models\Setting::get('mail_from_address', env('MAIL_FROM_ADDRESS'));
            $mailFromName = \App\Models\Setting::get('mail_from_name', env('MAIL_FROM_NAME'));
            
            // Update config dynamically
            if ($mailMailer && $mailMailer !== 'log') {
                config([
                    'mail.default' => $mailMailer,
                    'mail.mailers.smtp.host' => $mailHost ?? config('mail.mailers.smtp.host'),
                    'mail.mailers.smtp.port' => $mailPort ?? config('mail.mailers.smtp.port'),
                    'mail.mailers.smtp.username' => $mailUsername ?? config('mail.mailers.smtp.username'),
                    'mail.mailers.smtp.password' => $mailPassword ?? config('mail.mailers.smtp.password'),
                    'mail.mailers.smtp.encryption' => $mailEncryption ?? config('mail.mailers.smtp.encryption'),
                    'mail.from.address' => $mailFromAddress ?? config('mail.from.address'),
                    'mail.from.name' => $mailFromName ?? config('mail.from.name'),
                ]);
                
                // Reconfigure mailer if SMTP settings are available
                if ($mailMailer === 'smtp' && $mailHost && $mailUsername && $mailPassword) {
                    \Illuminate\Support\Facades\Config::set('mail.default', 'smtp');
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to apply mail configuration from settings: ' . $e->getMessage());
        }
    }

    /**
     * Parse size string (e.g., "8M", "2M") to bytes
     */
    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }
    
    /**
     * Get human-readable upload error message
     */
    private function getUploadErrorMessage($errorCode)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize (' . ini_get('upload_max_filesize') . ')',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        ];
        
        return $errors[$errorCode] ?? 'Unknown upload error (code: ' . $errorCode . ')';
    }
    
    /**
     * Analyze image with Google Vision API to detect objects
     */
    private function analyzeImageWithGoogleVision(string $imagePath): array
    {
        try {
            // Check if Google Vision is enabled
            $isEnabled = \App\Models\Setting::get('google_vision_enabled', false);
            if (!$isEnabled) {
                throw new \Exception('Google Vision API is not enabled.');
            }

            // Get API key from settings
            $apiKey = \App\Models\Setting::get('google_vision_api_key', '');
            
            if (empty($apiKey)) {
                throw new \Exception('Google Vision API key not configured.');
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
}
