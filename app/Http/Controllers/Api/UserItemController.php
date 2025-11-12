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
        // Debug: Log the incoming request data
        $files = $request->file('images');
        $fileDetails = [];
        if ($files) {
            foreach ($files as $index => $file) {
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
            'request_data' => $request->all(),
            'files_count' => $files ? count($files) : 0,
            'file_details' => $fileDetails,
            'file_names' => $files ? array_map(fn($f) => $f->getClientOriginalName(), $files) : [],
            'user' => Auth::user() ? Auth::user()->email : 'not authenticated'
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
            'tags' => 'required|string|max:255',
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
            'tags.max' => 'Tags must not exceed 255 characters',
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
            $user = Auth::user();
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

                // Create image metadata record
                $imageMetadata = ImageMetadata::create([
                    'filename' => $filename,
                    'file_path' => Storage::url($path),
                    'original_name' => $image->getClientOriginalName(),
                    'uploader_email' => $user->email, // Use authenticated user's email
                    'description' => $request->description,
                    'location' => $request->location, // Save location field
                    'province' => $request->province, // Save province field
                    'city' => $request->city, // Save city field
                    'tags' => $request->tags ? explode(',', $request->tags) : [],
                    'file_size' => $image->getSize(),
                    'mime_type' => $image->getMimeType(),
                    'status' => $request->item_type, // 'lost' or 'found'
                    'upload_id' => $uploadId,
                ]);

                $uploadedImages[] = [
                    'filename' => $filename,
                    'path' => Storage::url($path),
                    'original_name' => $image->getClientOriginalName(),
                    'size' => $image->getSize(),
                ];

                // Check for similarities with existing items
                try {
                    $similarityService->checkAndNotifySimilarities($imageMetadata, $user->email);
                } catch (\Exception $e) {
                    // Log error but don't fail the upload
                    \Log::error('Similarity check failed for user upload: ' . $e->getMessage());
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
                    'tags' => $request->tags ? explode(',', $request->tags) : [],
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

            // Get all items uploaded by the user, excluding verified claims
            // Verified claims should only appear in Claims Management
            $items = ImageMetadata::where('uploader_email', $user->email)
                ->where(function($query) {
                    // Exclude items that are verified claims (is_claimed = true AND claim_verification_status = 'verified')
                    $query->where(function($q) {
                        // Not claimed at all
                        $q->where('is_claimed', false)
                          ->orWhereNull('is_claimed');
                    })
                    ->orWhere(function($q) {
                        // Claimed but not verified
                        $q->where('is_claimed', true)
                          ->where(function($statusQ) {
                              $statusQ->where('claim_verification_status', '!=', 'verified')
                                      ->orWhereNull('claim_verification_status');
                          });
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('upload_id');

            $formattedItems = [];
            foreach ($items as $uploadId => $itemGroup) {
                $firstItem = $itemGroup->first();
                   $formattedItems[] = [
                       'upload_id' => $uploadId,
                       'item_type' => $firstItem->status,
                       'location' => $firstItem->location ?? 'Location not specified',
                       'province' => $firstItem->province ?? null,
                       'city' => $firstItem->city ?? null,
                       'description' => $firstItem->description,
                       'tags' => $firstItem->tags,
                       'contact_email' => $firstItem->uploader_email,
                       'images' => $itemGroup->map(function ($item) {
                        // Fix file path - remove /storage/ prefix if it exists
                        $filePath = $item->file_path;
                        if (str_starts_with($filePath, '/storage/')) {
                            $filePath = substr($filePath, 9); // Remove '/storage/' prefix
                        }

                        return [
                            'filename' => $item->filename,
                            'path' => Storage::url($filePath),
                            'original_name' => $item->original_name,
                            'size' => $item->file_size,
                        ];
                    })->toArray(),
                    'created_at' => $firstItem->created_at->toISOString(),
                ];
            }

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
                'tags' => 'nullable|string|max:255',
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
                'tags.max' => 'Tags must not exceed 255 characters',
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
                    $updateData['tags'] = !empty(trim($tagsValue)) ? explode(',', trim($tagsValue)) : [];
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
                    $newImageMetadata = ImageMetadata::create([
                        'filename' => $filename,
                        'file_path' => Storage::url($path),
                        'original_name' => $image->getClientOriginalName(),
                        'uploader_email' => $user->email,
                        'description' => $updateData['description'] ?? $firstItem->description,
                        'location' => $updateData['location'] ?? $firstItem->location,
                        'province' => $updateData['province'] ?? $firstItem->province,
                        'city' => $updateData['city'] ?? $firstItem->city,
                        'tags' => $updateData['tags'] ?? $firstItem->tags,
                        'file_size' => $image->getSize(),
                        'mime_type' => $image->getMimeType(),
                        'status' => $updateData['status'] ?? $firstItem->status,
                        'upload_id' => $uploadId,
                    ]);
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

            // Soft delete database records (files are kept for potential restore)
            ImageMetadata::where('upload_id', $uploadId)
                ->where('uploader_email', $user->email)
                ->delete(); // This will perform soft delete automatically

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully. It can be restored from the trash.'
            ]);

        } catch (\Exception $e) {
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
                        $filePath = $item->file_path;
                        if (str_starts_with($filePath, '/storage/')) {
                            $filePath = substr($filePath, 9);
                        }

                        return [
                            'filename' => $item->filename,
                            'path' => Storage::url($filePath),
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
     * Get items from other users (not current user)
     */
    public function getOtherUsersItems(Request $request)
    {
        $user = Auth::user();

        try {
            // Get items from other users, grouped by upload_id
            // Include items that are unclaimed, rejected, OR have a pending claim by the current user
            $items = ImageMetadata::where('uploader_email', '!=', $user->email)
                ->where(function($query) use ($user) {
                    $query->where(function($q) {
                        // Not claimed (including NULL) and no pending claim
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
                        // Claimed but rejected (can be claimed again)
                        $q->where('is_claimed', true)
                          ->where('claim_verification_status', 'rejected');
                    })
                    ->orWhere(function($q) {
                        // Pending claim by anyone (visible, but not claimable unless it's yours)
                        $q->where(function($r){
                            $r->where('is_claimed', false)
                              ->orWhereNull('is_claimed');
                        })
                          ->where('claim_verification_status', 'pending');
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('upload_id')
                ->map(function ($group) use ($user) {
                    $firstItem = $group->first();
                    $tags = $firstItem->tags ? (is_string($firstItem->tags) ? json_decode($firstItem->tags, true) : $firstItem->tags) : [];

                    // Get the user who uploaded this item
                    $uploader = \App\Models\User::where('email', $firstItem->uploader_email)->first();

                    // Check if current user has claimed this item
                    $userHasClaimed = $firstItem->claim_verification_status === 'pending' 
                        && $firstItem->claimed_by_email === $user->email;

                    return [
                        'upload_id' => $firstItem->upload_id,
                        'item_type' => $firstItem->status,
                        'description' => $firstItem->description,
                        'location' => $firstItem->location ?? 'Location not specified',
                        'province' => $firstItem->province ?? null,
                        'city' => $firstItem->city ?? null,
                        'tags' => is_array($tags) ? $tags : [],
                        'uploader_email' => $firstItem->uploader_email,
                        'uploader_name' => $uploader ? $uploader->name : 'Unknown User',
                        'uploader_profile_picture' => $uploader ? $uploader->profile_picture : null,
                        'uploader_verified' => $uploader ? ($uploader->is_verified ?? false) : false,
                        'created_at' => $firstItem->created_at,
                        'user_has_claimed' => $userHasClaimed,
                        'claim_status' => $firstItem->claim_verification_status,
                        'claimed_by_email' => $firstItem->claimed_by_email, // Added for checking who claimed
                        'images' => $group->map(function ($item) {
                            // Fix file path - remove /storage/ prefix if it exists
                            $filePath = $item->file_path;
                            if (str_starts_with($filePath, '/storage/')) {
                                $filePath = substr($filePath, 9); // Remove '/storage/' prefix
                            }

                            return [
                                'path' => Storage::url($filePath),
                                'original_name' => basename($filePath)
                            ];
                        })->toArray()
                    ];
                })
                ->values()
                ->toArray();

            return response()->json([
                'success' => true,
                'items' => $items
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch other users items: ' . $e->getMessage()
            ], 500);
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

            // Send notification message to the item owner
            try {
                $itemContext = [
                    'upload_id' => $uploadId,
                    'description' => $firstItem->description,
                    'location' => $firstItem->location,
                    'status' => $firstItem->status,
                    'tags' => $firstItem->tags,
                ];

                $claimMessage = "Hello! I believe I found your {$firstItem->status} item. Please verify if this item belongs to me so I can return it to you.";

                \App\Models\Message::create([
                    'sender_id' => $user->id,
                    'receiver_id' => $itemOwner->id,
                    'message' => $claimMessage,
                    'item_upload_id' => $uploadId,
                    'item_context' => json_encode($itemContext),
                ]);

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
                'owner_name' => $itemOwner->name
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
}
