<?php

namespace App\Http\Controllers\Api;

use App\Events\ItemClaimed;
use App\Events\ItemDeleted;
use App\Http\Controllers\Controller;
use App\Mail\UserItemNotification;
use App\Models\ImageMetadata;
use App\Models\ItemMatch;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use App\Services\GoogleVisionService;
use App\Services\SimilarityNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
        if (! Auth::check()) {
            Log::warning('Upload attempt by unauthenticated user', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Unauthorized',
                'message' => 'You must be logged in to upload items. Please log in and try again.',
            ], 401);
        }

        $user = Auth::user();

        // Debug: Log the incoming request data
        $files = $request->file('images');
        $fileDetails = [];

        // Check if files were uploaded
        if (! $files || (is_array($files) && count($files) === 0)) {
            Log::warning('Upload attempt with no files', [
                'user_id' => $user->id,
                'has_files_key' => $request->has('images'),
                'post_max_size' => ini_get('post_max_size'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'content_length' => $request->header('Content-Length'),
            ]);

            // Check if request exceeded POST size limit
            $postMaxSize = $this->parseSize(ini_get('post_max_size'));
            $contentLength = $request->header('Content-Length');
            if ($contentLength && $contentLength > $postMaxSize) {
                return response()->json([
                    'success' => false,
                    'error' => 'File size too large',
                    'message' => 'The total size of your upload exceeds the server limit ('.ini_get('post_max_size').'). Please reduce the file sizes and try again.',
                ], 413);
            }

            return response()->json([
                'success' => false,
                'error' => 'No files uploaded',
                'message' => 'Please select at least one image to upload.',
            ], 400);
        }

        if ($files) {
            foreach ($files as $index => $file) {
                // Check for upload errors
                if (! $file->isValid()) {
                    $errorCode = $file->getError();
                    $errorMessage = $this->getUploadErrorMessage($errorCode);

                    Log::error('File upload error', [
                        'user_id' => $user->id,
                        'file_index' => $index,
                        'file_name' => $file->getClientOriginalName(),
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage,
                    ]);

                    return response()->json([
                        'success' => false,
                        'error' => 'File upload error',
                        'message' => $errorMessage.' (File: '.$file->getClientOriginalName().')',
                    ], 400);
                }

                $fileDetails[] = [
                    'index' => $index,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'is_valid' => $file->isValid(),
                    'error' => $file->getError(),
                ];
            }
        }

        Log::info('User upload request received', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'files_count' => $files ? count($files) : 0,
            'file_details' => $fileDetails,
            'file_names' => $files ? array_map(fn ($f) => $f->getClientOriginalName(), $files) : [],
            'request_keys' => array_keys($request->all()),
        ]);

        // Get enabled cities for validation
        $enabledCitiesJson = Setting::get('enabled_cities', '[]');
        $enabledCities = json_decode($enabledCitiesJson, true) ?? [];

        // Get enabled provinces for validation
        $enabledProvincesJson = Setting::get('enabled_provinces', '[]');
        $enabledProvinces = json_decode($enabledProvincesJson, true) ?? [];

        // Get field visibility and requirement settings
        $enableProvinceField = Setting::get('enable_province_field', true);
        $provinceFieldRequired = Setting::get('province_field_required', true);
        $enableCityField = Setting::get('enable_city_field', true);
        $cityFieldRequired = Setting::get('city_field_required', true);

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
                if (! empty($enabledProvinces)) {
                    $rules['province'] = 'required|string|in:'.implode(',', $enabledProvinces);
                } else {
                    $rules['province'] = 'required|string';
                }
            } else {
                if (! empty($enabledProvinces)) {
                    $rules['province'] = 'nullable|string|in:'.implode(',', $enabledProvinces);
                } else {
                    $rules['province'] = 'nullable|string';
                }
            }
        }

        // Add city validation only if field is enabled
        if ($enableCityField) {
            if ($cityFieldRequired) {
                if (! empty($enabledCities)) {
                    $rules['city'] = 'required|string|in:'.implode(',', $enabledCities);
                } else {
                    $rules['city'] = 'required|string';
                }
            } else {
                if (! empty($enabledCities)) {
                    $rules['city'] = 'nullable|string|in:'.implode(',', $enabledCities);
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
                'file_details' => $fileDetails,
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
                                'error_code' => $file['error'],
                            ],
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
                'message' => 'Please check your uploaded files. Make sure they are valid image files (JPEG, PNG, GIF, WEBP) and not corrupted.',
            ], 400);
        }

        try {
            // User is already authenticated (checked above)
            $uploadId = 'user_upload_'.Str::random(10);
            $uploadedImages = [];

            // Deduplicate files based on name, size, and content
            $uniqueFiles = [];
            $processedFiles = [];

            foreach ($request->file('images') as $index => $image) {
                $fileKey = $image->getClientOriginalName().'_'.$image->getSize().'_'.$image->getMimeType();
                if (! in_array($fileKey, $processedFiles)) {
                    $uniqueFiles[] = $image;
                    $processedFiles[] = $fileKey;
                } else {
                    Log::info('Duplicate file skipped', [
                        'filename' => $image->getClientOriginalName(),
                        'size' => $image->getSize(),
                        'mime_type' => $image->getMimeType(),
                    ]);
                }
            }

            Log::info('File deduplication', [
                'original_count' => count($request->file('images')),
                'unique_count' => count($uniqueFiles),
            ]);

            // Process each unique uploaded image
            foreach ($uniqueFiles as $index => $image) {
                $filename = time().'_'.$index.'_'.$image->getClientOriginalName();
                $path = $image->storeAs('user-items', $filename, 'public');

                // Google Vision: object localization + label detection (merged in service).
                $detectedObjects = app(GoogleVisionService::class)->detectObjects($image->getPathname());

                // Create image metadata record
                $metadataData = [
                    'filename' => $filename,
                    'file_path' => Storage::url($path),
                    'original_name' => $image->getClientOriginalName(),
                    'uploader_email' => $user->email, // Use authenticated user's email
                    'user_id' => $user->id, // Stable owner reference, robust to email changes
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

                // Defer similarity until after JSON response (faster perceived upload on mobile).
                SimilarityNotificationService::queueSimilarityCheckAfterResponse($imageMetadata->id, $user->email);
            }

            // Create in-app notification for successful upload
            try {
                $user = Auth::user();
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'item_uploaded',
                    'title' => 'Item uploaded successfully',
                    'message' => 'Your '.($request->item_type === 'lost' ? 'lost' : 'found').' item has been uploaded successfully.',
                    'data' => [
                        'upload_id' => $uploadId,
                        'item_type' => $request->item_type,
                    ],
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create upload notification: '.$e->getMessage());
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
                'message' => 'Item reported successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Upload failed',
                'message' => $e->getMessage(),
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

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated',
                ], 401);
            }

            // Get all items uploaded by the user.
            // Match by user_id (preferred) OR uploader_email so legacy rows
            // and guest-uploaded-then-attached items both show up.
            $items = ImageMetadata::ownedBy($user)
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

                // Parse detected_objects if they're stored as JSON string
                $detectedObjects = $firstItem->detected_objects;
                if (is_string($detectedObjects)) {
                    $decodedObjects = json_decode($detectedObjects, true);
                    $detectedObjects = $decodedObjects !== null ? $decodedObjects : [];
                } elseif (! is_array($detectedObjects)) {
                    $detectedObjects = [];
                }

                $formattedItems[] = [
                    'upload_id' => $uploadId,
                    'item_type' => $firstItem->status ?? 'lost',
                    'location' => $firstItem->location ?? 'Location not specified',
                    'province' => $firstItem->province ?? null,
                    'city' => $firstItem->city ?? null,
                    'description' => $firstItem->description ?? '',
                    'tags' => $tags ?? [],
                    'detected_objects' => $detectedObjects ?? [],
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
                            $imagePath = '/'.$filePath;
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
                'upload_ids' => array_column($formattedItems, 'upload_id'),
            ]);

            return response()->json([
                'success' => true,
                'data' => $formattedItems,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load items',
                'message' => $e->getMessage(),
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
                'raw_input' => file_get_contents('php://input'),
            ]);

            // Get enabled cities for validation
            $enabledCitiesJson = Setting::get('enabled_cities', '[]');
            $enabledCities = json_decode($enabledCitiesJson, true) ?? [];

            // Get enabled provinces for validation
            $enabledProvincesJson = Setting::get('enabled_provinces', '[]');
            $enabledProvinces = json_decode($enabledProvincesJson, true) ?? [];

            // Get field visibility and requirement settings
            $enableProvinceField = Setting::get('enable_province_field', true);
            $provinceFieldRequired = Setting::get('province_field_required', true);
            $enableCityField = Setting::get('enable_city_field', true);
            $cityFieldRequired = Setting::get('city_field_required', true);

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
                    if (! empty($enabledProvinces)) {
                        $rules['province'] = 'required|string|in:'.implode(',', $enabledProvinces);
                    } else {
                        $rules['province'] = 'required|string';
                    }
                } else {
                    if (! empty($enabledProvinces)) {
                        $rules['province'] = 'nullable|string|in:'.implode(',', $enabledProvinces);
                    } else {
                        $rules['province'] = 'nullable|string';
                    }
                }
            }

            // Add city validation only if field is enabled
            if ($enableCityField) {
                if ($cityFieldRequired) {
                    if (! empty($enabledCities)) {
                        $rules['city'] = 'required|string|in:'.implode(',', $enabledCities);
                    } else {
                        $rules['city'] = 'required|string';
                    }
                } else {
                    if (! empty($enabledCities)) {
                        $rules['city'] = 'nullable|string|in:'.implode(',', $enabledCities);
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
                    'all_request_data' => $requestData,
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'message' => 'Please check your input. '.implode(' ', $validator->errors()->all()),
                    'errors' => $validator->errors(),
                    'debug' => [
                        'has_location' => $request->has('location'),
                        'has_description' => $request->has('description'),
                        'location_value' => $request->input('location'),
                        'description_value' => $request->input('description'),
                        'all_keys' => array_keys($requestData),
                    ],
                ], 400);
            }

            // Find items by upload_id and user email
            $items = ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item not found or access denied',
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
            if (! empty($updateData)) {
                ImageMetadata::where('upload_id', $uploadId)
                    ->where('uploader_email', $user->email)
                    ->update($updateData);
            }

            // Get current image count
            $currentImageCount = $items->count();
            $removeImagesCount = 0;
            $newImagesCount = 0;

            // Handle image removal
            if ($request->has('remove_images') && is_array($request->remove_images) && ! empty($request->remove_images)) {
                // Ensure user doesn't remove all images
                if (count($request->remove_images) >= $currentImageCount && ! $request->hasFile('images')) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Cannot remove all images. Please add at least one new image before removing all existing ones, or keep at least one image.',
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
                        'error' => "Maximum 5 images allowed. You currently have {$remainingImages} image(s) remaining. You can only add ".(5 - $remainingImages).' more image(s).',
                    ], 400);
                }

                // Ensure at least one image remains
                if ($totalAfterUpdate < 1) {
                    return response()->json([
                        'success' => false,
                        'error' => 'At least one image is required for each item.',
                    ], 400);
                }

                foreach ($request->file('images') as $index => $image) {
                    $filename = time().'_'.$index.'_'.$image->getClientOriginalName();
                    $path = $image->storeAs('user-items', $filename, 'public');
                    $detectedObjects = app(GoogleVisionService::class)->detectObjects($image->getPathname());

                    // Create new image metadata record
                    $newMetadataData = [
                        'filename' => $filename,
                        'file_path' => Storage::url($path),
                        'original_name' => $image->getClientOriginalName(),
                        'uploader_email' => $user->email,
                        'user_id' => $user->id,
                        'detected_objects' => $detectedObjects,
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
                        'error' => 'At least one image is required for each item. Please add a new image before removing all existing ones.',
                    ], 400);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update item: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to update item',
                'message' => $e->getMessage(),
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

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized',
                    'message' => 'You must be logged in to delete items',
                ], 401);
            }

            Log::info('Delete item request', [
                'upload_id' => $uploadId,
                'user_email' => $user->email,
                'user_id' => $user->id,
            ]);

            // Find items by upload_id and user email
            $items = ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->get();

            if ($items->isEmpty()) {
                Log::warning('Delete item - not found or access denied', [
                    'upload_id' => $uploadId,
                    'user_email' => $user->email,
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Item not found or access denied',
                    'message' => 'The item you are trying to delete does not exist or you do not have permission to delete it.',
                ], 404);
            }

            // Soft delete database records (files are kept for potential restore)
            $deletedCount = ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->delete(); // This will perform soft delete automatically

            Log::info('Items soft deleted', [
                'upload_id' => $uploadId,
                'deleted_count' => $deletedCount,
                'user_email' => $user->email,
            ]);

            // Broadcast item deleted event for real-time updates in chat
            try {
                broadcast(new ItemDeleted($uploadId, $user->id))->toOthers();
            } catch (\Exception $e) {
                Log::warning('Failed to broadcast item deleted event', [
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the delete if broadcast fails
            }

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully. It can be restored from the trash.',
                'deleted_count' => $deletedCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete item', [
                'upload_id' => $uploadId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete item',
                'message' => $e->getMessage(),
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
                    'error' => 'Deleted item not found or access denied',
                ], 404);
            }

            // Restore all items with this upload_id
            ImageMetadata::onlyTrashed()
                ->where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->restore();

            return response()->json([
                'success' => true,
                'message' => 'Item restored successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to restore item',
                'message' => $e->getMessage(),
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
                    'error' => 'Deleted item not found or access denied',
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
                'message' => 'Item permanently deleted',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to permanently delete item',
                'message' => $e->getMessage(),
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

            // Get trashed items for the user (match by user_id or email).
            $items = ImageMetadata::onlyTrashed()
                ->ownedBy($user)
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
                            $imagePath = '/'.$filePath;
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
                'data' => $formattedItems,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load trashed items',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get items from other users (not current user) that match user's reported items
     * OPTIMIZED: Now uses pre-stored matches from database instead of real-time matching
     */
    public function getOtherUsersItems(Request $request)
    {
        $user = Auth::user();

        try {
            // OPTIMIZED APPROACH: Get pre-stored matches from database instead of doing real-time matching
            // Matches are stored when items are uploaded (in SimilarityNotificationService)
            // This makes the claim-verify page load much faster

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
                    'message' => 'No items to match. Report a lost or found item first to see matching items.',
                ]);
            }

            // Get all stored matches for this user's items
            $userUploadIds = $userItems->keys()->toArray();
            $storedMatches = ItemMatch::where('user_email', $user->email)
                ->whereIn('user_item_upload_id', $userUploadIds)
                ->where('similarity_score', '>=', 0.5) // Only show matches above threshold
                ->orderBy('similarity_score', 'desc')
                ->get();

            Log::info('Loading stored matches for claim-verify', [
                'user_email' => $user->email,
                'user_items_count' => count($userUploadIds),
                'stored_matches_count' => $storedMatches->count(),
            ]);

            // Get the matched item upload IDs
            $matchedUploadIds = $storedMatches->pluck('matched_item_upload_id')->unique()->toArray();

            // Get the matched items from database
            $matchedItemGroups = ImageMetadata::whereIn('upload_id', $matchedUploadIds)
                ->where('uploader_email', '!=', $user->email)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('upload_id');

            $matchedItems = [];

            // Build matched items array from stored matches
            foreach ($storedMatches as $match) {
                $matchedUploadId = $match->matched_item_upload_id;

                // Skip if we already have this match
                if (isset($matchedItems[$matchedUploadId])) {
                    continue;
                }

                // Get the matched item group
                $matchedItemGroup = $matchedItemGroups->get($matchedUploadId);
                if (! $matchedItemGroup) {
                    continue;
                }

                // Get the user's item that was matched
                $userItemGroup = $userItems->get($match->user_item_upload_id);
                if (! $userItemGroup) {
                    continue;
                }

                $matchedItems[$matchedUploadId] = [
                    'item' => $matchedItemGroup,
                    'similarity' => (float) $match->similarity_score,
                    'matched_with' => $match->user_item_upload_id,
                    'visual_similarity' => (float) $match->visual_similarity,
                    'text_similarity' => (float) $match->text_similarity,
                ];
            }

            Log::info('Claim-verify loaded from stored matches', [
                'user_email' => $user->email,
                'stored_matches_count' => $storedMatches->count(),
                'matched_items_count' => count($matchedItems),
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Also include matches that came from notifications (older matches or matches
            // created before ItemMatch was introduced). This ensures that if the user
            // sees "Item Match Found!" notifications, those items also appear here.
            $userNotifications = Notification::where('type', 'item_matched')
                ->where('user_id', $user->id)
                ->whereNotNull('data')
                ->get();

            if ($userNotifications->isNotEmpty()) {
                // Collect upload IDs for other users' items referenced in notifications
                $notificationMatches = [];
                foreach ($userNotifications as $notification) {
                    $data = $notification->data ?? [];
                    $otherUploadId = $data['new_item_upload_id'] ?? null;      // Other user's item
                    $userUploadIdForMatch = $data['matched_item_upload_id'] ?? null; // Current user's item

                    if (! $otherUploadId || isset($matchedItems[$otherUploadId])) {
                        // Skip if no upload id or already included from ItemMatch
                        continue;
                    }

                    $notificationMatches[$otherUploadId] = [
                        'other_upload_id' => $otherUploadId,
                        'user_upload_id' => $userUploadIdForMatch,
                        'similarity' => $data['similarity_score'] ?? 0.5,
                    ];
                }

                if (! empty($notificationMatches)) {
                    $otherUploadIds = array_keys($notificationMatches);

                    // Load the matched items for these upload IDs
                    $notificationItemGroups = ImageMetadata::whereIn('upload_id', $otherUploadIds)
                        ->where('uploader_email', '!=', $user->email)
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->groupBy('upload_id');

                    foreach ($notificationMatches as $otherUploadId => $matchInfo) {
                        // Skip if we still don't have the group or it was added in the meantime
                        if (isset($matchedItems[$otherUploadId])) {
                            continue;
                        }

                        $matchedItemGroup = $notificationItemGroups->get($otherUploadId);
                        if (! $matchedItemGroup) {
                            continue;
                        }

                        // Get the user's item group this was matched with (if available)
                        $userItemGroup = $matchInfo['user_upload_id']
                            ? ($userItems->get($matchInfo['user_upload_id']) ?? null)
                            : null;

                        $matchedItems[$otherUploadId] = [
                            'item' => $matchedItemGroup,
                            'similarity' => (float) $matchInfo['similarity'],
                            'matched_with' => $matchInfo['user_upload_id'] ?? ($userItems->keys()->first() ?? null),
                            'from_notification' => true,
                        ];
                    }

                    Log::info('Added notification-based matches to claim-verify', [
                        'user_email' => $user->email,
                        'notification_matches_count' => count($notificationMatches),
                        'matched_items_count' => count($matchedItems),
                    ]);
                }
            }

            // If no matches found, return empty array
            if (empty($matchedItems)) {
                return response()->json([
                    'success' => true,
                    'items' => [],
                    'message' => 'No matching items found. Keep checking back as new items are posted!',
                ]);
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
                    'similarity' => $matchData['similarity'] ?? 0,
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
                    $uploader = User::where('email', $firstItem->uploader_email)->first();

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
                                    $imagePath = '/'.$filePath;
                                } elseif (str_starts_with($filePath, 'http')) {
                                    $imagePath = $filePath;
                                } else {
                                    $imagePath = Storage::url($filePath);
                                }

                                return [
                                    'path' => $imagePath,
                                    'original_name' => $item->original_name ?? basename($filePath),
                                ];
                            })->toArray(),
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
                                $imagePath = '/'.$filePath;
                            } elseif (str_starts_with($filePath, 'http')) {
                                // Full URL, use as is
                                $imagePath = $filePath;
                            } else {
                                // Relative path, use Storage::url to generate proper path
                                $imagePath = Storage::url($filePath);
                            }

                            return [
                                'path' => $imagePath,
                                'original_name' => $item->original_name ?? basename($filePath),
                            ];
                        })->toArray(),
                    ];
                })
                ->sortByDesc('similarity_score')
                ->values()
                ->toArray();

            return response()->json([
                'success' => true,
                'items' => $items,
                'message' => count($items).' matching item(s) found based on your reported items.',
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching matching items: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch matching items: '.$e->getMessage(),
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
            $possiblePaths[] = storage_path('app/public/user-items/'.$item->filename);
            $possiblePaths[] = storage_path('app/public/reference-images/'.$item->filename);
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
            $possiblePaths[] = storage_path('app/public/user-items/'.$filename);
            $possiblePaths[] = storage_path('app/public/reference-images/'.$filename);

            // Also try with the full relative path
            if (str_contains($filePath, 'user-items')) {
                $possiblePaths[] = storage_path('app/public/'.$filePath);
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
                    'resolved_path' => $path,
                ]);

                return $path;
            }
        }

        Log::warning('Could not find file path for item', [
            'item_id' => $item->id,
            'upload_id' => $item->upload_id,
            'file_path' => $item->file_path,
            'filename' => $item->filename,
            'tried_paths' => $possiblePaths,
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
            Log::warning('Could not compare images: '.$e->getMessage());

            return 0.0;
        }
    }

    public function claimItem($uploadId)
    {
        try {
            $user = Auth::user();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated',
                ], 401);
            }

            // First check if item exists
            $itemExists = ImageMetadata::where('upload_id', $uploadId)->exists();
            if (! $itemExists) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item not found',
                ], 404);
            }

            // Check if user is trying to claim their own item
            $ownItem = ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->exists();
            if ($ownItem) {
                return response()->json([
                    'success' => false,
                    'error' => 'You cannot claim your own item',
                ], 400);
            }

            // Verify that the user has a LOST item and the item to claim is FOUND
            // Users with FOUND items can only message, not claim
            $itemToClaim = ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', '!=', $user->email)
                ->first();

            if (! $itemToClaim) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item not found.',
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

            if (! $hasLostItem) {
                return response()->json([
                    'success' => false,
                    'error' => 'You can only claim items if you have a lost item. If you found an item, please message the owner to notify them.',
                ], 400);
            }

            // Check if item already has a pending claim
            $pendingClaim = ImageMetadata::where('upload_id', $uploadId)
                ->where('claim_verification_status', 'pending')
                ->exists();
            if ($pendingClaim) {
                return response()->json([
                    'success' => false,
                    'error' => 'This item already has a pending claim. Please wait for the owner to verify it.',
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
                    'error' => 'This item has already been claimed and verified.',
                ], 400);
            }

            // Find the item (must not be owned by the current user and not already claimed/verified/pending)
            $items = ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', '!=', $user->email)
                ->where(function ($query) {
                    // Only allow claiming if:
                    // 1. Not claimed at all (including NULL), OR
                    // 2. Claimed but rejected (can be claimed again)
                    $query->where(function ($q) {
                        $q->where(function ($r) {
                            $r->where('is_claimed', false)
                                ->orWhereNull('is_claimed');
                        })
                            ->where(function ($subQ) {
                                $subQ->whereNull('claim_verification_status')
                                    ->orWhere('claim_verification_status', '!=', 'pending');
                            });
                    })
                        ->orWhere(function ($q) {
                            $q->where('is_claimed', true)
                                ->where('claim_verification_status', 'rejected');
                        });
                })
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item cannot be claimed at this time. It may already have a pending claim or be verified.',
                ], 400);
            }

            // Get the original owner of the item (the person who posted it)
            $firstItem = $items->first();
            $itemOwnerEmail = $firstItem->uploader_email;
            $itemOwner = User::where('email', $itemOwnerEmail)->first();

            if (! $itemOwner) {
                return response()->json([
                    'success' => false,
                    'error' => 'Item owner not found',
                ], 404);
            }

            // Set pending claim status WITHOUT marking as claimed yet
            // Item will only be marked as claimed when owner verifies
            ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', '!=', $user->email)
                ->update([
                    'claimed_by_email' => $user->email,
                    'claimed_at' => now(),
                    'claim_verification_status' => 'pending',
                    // Note: is_claimed remains false until owner verifies
                ]);

            // Broadcast item claimed event for real-time updates in chat
            broadcast(new ItemClaimed($uploadId, $user->id, $itemOwner->id, 'pending'))->toOthers();

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
                        $imagePath = '/'.$filePath;
                    } elseif (str_starts_with($filePath, 'http')) {
                        // Full URL, use as is
                        $imagePath = $filePath;
                    } else {
                        // Relative path, use Storage::url to generate proper path
                        $imagePath = Storage::url($filePath);
                    }

                    return [
                        'path' => $imagePath,
                        'original_name' => $item->original_name ?? basename($filePath),
                        'filename' => $item->filename,
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

                Message::create([
                    'sender_id' => $user->id,
                    'receiver_id' => $itemOwner->id,
                    'message' => $claimMessage,
                    'item_upload_id' => $uploadId,
                    'item_context' => json_encode($itemContext),
                ]);

                // Also update any existing messages with this item to include claim status
                Message::where('item_upload_id', $uploadId)
                    ->where(function ($query) use ($user, $itemOwner) {
                        $query->where(function ($q) use ($user, $itemOwner) {
                            $q->where('sender_id', $user->id)
                                ->where('receiver_id', $itemOwner->id);
                        })->orWhere(function ($q) use ($user, $itemOwner) {
                            $q->where('sender_id', $itemOwner->id)
                                ->where('receiver_id', $user->id);
                        });
                    })
                    ->whereNotNull('item_context')
                    ->get()
                    ->each(function ($message) use ($itemContext) {
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
                    'upload_id' => $uploadId,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send claim notification message: '.$e->getMessage());
            }

            // In-app notification for the owner
            try {
                Notification::create([
                    'user_id' => $itemOwner->id,
                    'type' => 'item_claimed',
                    'title' => 'Someone claimed your item',
                    'message' => $user->name.' requested to claim your '.($firstItem->status ?? 'item').'.',
                    'data' => [
                        'upload_id' => $uploadId,
                        'claimer_id' => $user->id,
                        'claimer_name' => $user->name,
                    ],
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed creating in-app notification: '.$e->getMessage());
            }

            // Send email notification to the item owner
            try {
                // Check if email notifications are enabled
                $emailNotificationsEnabled = Setting::get('email_notifications', true);

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

                    Mail::to($itemOwnerEmail)->send(new UserItemNotification($emailData));

                    Log::info('Claim notification email sent', [
                        'claimer_id' => $user->id,
                        'owner_email' => $itemOwnerEmail,
                        'upload_id' => $uploadId,
                    ]);
                } else {
                    Log::info('Email notifications disabled - skipping claim notification email', [
                        'owner_email' => $itemOwnerEmail,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send claim notification email: '.$e->getMessage());
                // Don't fail the claim if email fails
            }

            return response()->json([
                'success' => true,
                'message' => 'Item claim request sent! The item owner has been notified and will verify your claim. You can message them to discuss the details.',
                'owner_name' => $itemOwner->name,
                'owner_id' => $itemOwner->id,
                'upload_id' => $uploadId,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to claim item: '.$e->getMessage(),
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

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated',
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
                    'error' => 'No pending claim found for this item',
                ], 404);
            }

            // Clear the claim
            ImageMetadata::where('upload_id', $uploadId)
                ->where('claim_verification_status', 'pending')
                ->where('claimed_by_email', $user->email)
                ->update([
                    'claimed_by_email' => null,
                    'claimed_at' => null,
                    'claim_verification_status' => null,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Claim cancelled successfully. The item is now available for others to claim.',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cancel claim: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to cancel claim: '.$e->getMessage(),
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
            $mailMailer = Setting::get('mail_mailer', env('MAIL_MAILER', 'log'));
            $mailHost = Setting::get('mail_host', env('MAIL_HOST'));
            $mailPort = Setting::get('mail_port', env('MAIL_PORT', 587));
            $mailUsername = Setting::get('mail_username', env('MAIL_USERNAME'));
            $mailPassword = Setting::get('mail_password', env('MAIL_PASSWORD'));
            $mailEncryption = Setting::get('mail_encryption', env('MAIL_ENCRYPTION', 'tls'));
            $mailFromAddress = Setting::get('mail_from_address', env('MAIL_FROM_ADDRESS'));
            $mailFromName = Setting::get('mail_from_name', env('MAIL_FROM_NAME'));

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
                    Config::set('mail.default', 'smtp');
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to apply mail configuration from settings: '.$e->getMessage());
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
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize ('.ini_get('upload_max_filesize').')',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        ];

        return $errors[$errorCode] ?? 'Unknown upload error (code: '.$errorCode.')';
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
        $tagsArray = array_filter($tagsArray, function ($tag) {
            return ! empty(trim($tag));
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
