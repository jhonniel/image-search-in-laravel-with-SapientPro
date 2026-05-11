@extends('layouts.user')

@section('title', 'Your Reported Items')

@section('content')
@csrf

@if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex items-center space-x-2">
            <i class="fas fa-check-circle text-green-600"></i>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
    </div>
@endif

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<div class="space-y-6">
    <!-- Upload Form -->
    <div id="upload-form" class="bg-white rounded-xl shadow-lg border border-gray-200 hidden overflow-hidden">
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Report New Item</h3>
                    <p class="text-sm text-gray-600 mt-1">Fill out the form below to report a lost or found item</p>
                </div>
                <button onclick="toggleUploadForm()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <form id="item-upload-form" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
            @csrf

            <!-- Item Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Item Type</label>
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                    <label class="flex items-center">
                        <input type="radio" name="item_type" value="lost" class="mr-2" checked>
                        <span class="text-sm text-gray-700">Lost Item</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="item_type" value="found" class="mr-2">
                        <span class="text-sm text-gray-700">Found Item</span>
                    </label>
                </div>
            </div>

            @if($enableProvinceField ?? true)
            <!-- Province -->
            <div>
                <label for="province" class="block text-sm font-medium text-gray-700 mb-2">
                    Province 
                    @if($provinceFieldRequired ?? true)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                <div class="relative">
                    <input type="text" 
                           id="province" 
                           name="province" 
                           @if($provinceFieldRequired ?? true) required @endif
                           autocomplete="off"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Enter your province name">
                    <!-- Autocomplete dropdown -->
                    <div id="province-autocomplete" class="hidden absolute z-50 w-full mt-1 bg-white border-2 border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                        <!-- Suggestions will be inserted here -->
                    </div>
                </div>
                <div id="province-error-message" class="hidden mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg" style="display: none;">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        We're trying to expand our services to cover more locations. Please contact us if you'd like to see your province added.
                    </p>
                </div>
            </div>
            @endif

            @if($enableCityField ?? true)
            <!-- City -->
            <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                    City 
                    @if($cityFieldRequired ?? true)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                <div class="relative">
                    <input type="text" 
                           id="city" 
                           name="city" 
                           @if($cityFieldRequired ?? true) required @endif
                           autocomplete="off"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Enter your city name">
                    <!-- Autocomplete dropdown -->
                    <div id="city-autocomplete" class="hidden absolute z-50 w-full mt-1 bg-white border-2 border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                        <!-- Suggestions will be inserted here -->
                    </div>
                </div>
                <div id="city-error-message" class="hidden mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg" style="display: none;">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        We're trying to expand our services to cover more locations. Please contact us if you'd like to see your city added.
                    </p>
                </div>
            </div>
            @endif

            <!-- Location -->
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location <span class="text-red-500">*</span></label>
                <div class="flex gap-2 mb-2 relative">
                    <div class="flex-1 relative">
                        <input type="text" id="location" name="location" required autocomplete="off"
                               class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="Where was this item found/lost? (e.g., Street name, Building, etc.)">
                        <!-- Location autocomplete dropdown -->
                        <div id="location-autocomplete" class="hidden absolute z-50 w-full mt-1 bg-white border-2 border-purple-300 rounded-lg shadow-2xl max-h-60 overflow-y-auto">
                            <!-- Suggestions will be inserted here -->
                        </div>
                    </div>
                    <button type="button" onclick="useCurrentLocation('location')" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm font-medium whitespace-nowrap">
                        <i class="fas fa-crosshairs mr-1"></i> Current Location
                    </button>
                </div>
                <!-- Hidden fields for coordinates -->
                <input type="hidden" id="location-lat" name="location_lat">
                <input type="hidden" id="location-lon" name="location_lon">
                <!-- Map container -->
                <div class="mt-3">
                    <div id="location-map" class="w-full h-64 rounded-lg border border-gray-300 relative" style="display: none;">
                        <!-- Location autocomplete overlay on map -->
                        <div id="location-autocomplete-map" class="hidden absolute top-2 left-2 right-2 z-[1000] bg-white border-2 border-purple-300 rounded-lg shadow-2xl max-h-60 overflow-y-auto">
                            <!-- Suggestions will be inserted here -->
                        </div>
                    </div>
                    <div class="mt-2 flex gap-2">
                        <button type="button" onclick="toggleLocationMap('location')" 
                                class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors text-sm font-medium">
                            <i class="fas fa-map-marker-alt mr-1"></i> Pin on Map
                        </button>
                        <button type="button" onclick="clearLocationMap('location')" 
                                class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors text-sm font-medium" 
                                style="display: none;" id="clear-location-btn">
                            <i class="fas fa-times mr-1"></i> Clear Map
                        </button>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description <span class="text-red-500">*</span></label>
                <textarea id="description" name="description" rows="3" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                          placeholder="Describe the item in detail..."></textarea>
            </div>

            <!-- Tags -->
            <div>
                <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">Tags <span class="text-red-500">*</span></label>
                
                <!-- Tag Dropdown -->
                <div class="relative mb-2">
                    <select id="tags-dropdown" 
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Select a tag...</option>
                        <!-- Options will be loaded dynamically -->
                    </select>
                </div>
                
                <!-- Add New Tag Button -->
                <div class="mb-2">
                    <button type="button" 
                            onclick="toggleNewTagInput()" 
                            class="text-sm text-purple-600 hover:text-purple-800 font-medium flex items-center gap-1">
                        <i class="fas fa-plus text-xs"></i>
                        Add another tag
                    </button>
                </div>
                
                <!-- Add New Tag Input (Hidden by default) -->
                <div id="new-tag-input-container" class="flex gap-2 mb-2 hidden">
                    <input type="text" 
                           id="new-tag-input" 
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Type a new tag">
                    <button type="button" 
                            onclick="addTagFromInput()" 
                            class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="button" 
                            onclick="toggleNewTagInput()" 
                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Selected Tags Display -->
                <div id="selected-tags-container" class="flex flex-wrap gap-2 mt-2 min-h-[20px]">
                    <!-- Selected tags will appear here -->
                </div>
                
                <!-- Hidden input to store selected tags as JSON -->
                <input type="hidden" id="tags" name="tags" required>
                
                <p class="text-xs text-gray-500 mt-1">Select tags from the dropdown or add new ones. Tags help others find your item more easily.</p>
            </div>

            <!-- Images Upload Section -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Images <span class="text-red-500">*</span></label>
                
                <!-- Drag and Drop Zone -->
                <div id="drop-zone" class="relative border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-all duration-200 hover:border-purple-400 hover:bg-purple-50 cursor-pointer">
                    <input type="file" id="item-images" name="images[]" multiple accept="image/*" class="hidden">
                    
                    <div id="drop-zone-content" class="space-y-4">
                        <div class="flex justify-center">
                            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-cloud-upload-alt text-purple-600 text-2xl"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-lg font-medium text-gray-700 mb-1">
                                <span class="text-purple-600">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-sm text-gray-500">PNG, JPG, GIF up to 10MB each (Max 5 images)</p>
                        </div>
                        <button type="button" onclick="document.getElementById('item-images').click()" 
                                class="inline-flex items-center px-4 py-2 bg-purple-primary text-white rounded-lg hover:bg-purple-600 transition-colors text-sm font-medium">
                            <i class="fas fa-folder-open mr-2"></i>
                            Browse Files
                        </button>
                    </div>
                </div>

                <!-- Image Previews -->
                <div id="image-preview-container" class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 hidden">
                    <!-- Image previews will be inserted here -->
                </div>

                <!-- Upload Progress -->
                <div id="upload-progress-container" class="mt-4 hidden">
                    <div class="rounded-xl border border-purple-200 bg-purple-50/60 p-3 sm:p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <span id="upload-stage-icon" class="inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white shadow text-purple-600 shrink-0">
                                <i class="fas fa-circle-notch fa-spin text-base sm:text-lg"></i>
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span id="upload-stage-label" class="text-sm sm:text-base font-semibold text-gray-900 truncate">Preparing your upload…</span>
                                    <span id="upload-progress-text" class="text-xs sm:text-sm font-medium text-purple-700 tabular-nums">0%</span>
                                </div>
                                <p id="upload-stage-detail" class="text-xs sm:text-sm text-gray-600 mt-0.5">Getting your images ready</p>
                                <div class="w-full bg-purple-100 rounded-full h-2 sm:h-2.5 mt-2 overflow-hidden">
                                    <div id="upload-progress-bar" class="bg-gradient-to-r from-purple-500 to-pink-500 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Match-checking note -->
                        <div id="matching-check-message" class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg hidden">
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-search text-blue-600 animate-pulse mt-0.5"></i>
                                <span class="text-xs sm:text-sm text-blue-800">
                                    <strong>Match checking continues in the background.</strong> You'll see a bell notification if we find similar items.
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Warning Note -->
                    <div id="processing-warning-note" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-start space-x-2">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>
                            <p class="text-xs sm:text-sm text-yellow-800">
                                <strong>Please don't close or refresh this page while uploading.</strong> Your images are being sent and saved to your account.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-4">
                <button type="button" onclick="toggleUploadForm()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="bg-purple-primary text-white px-6 py-2 rounded-lg hover:bg-purple-600 transition-colors">
                    <i class="fas fa-upload mr-2"></i>
                    Upload Item
                </button>
            </div>
        </form>
    </div>

    <!-- Your Reported Items -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 sm:px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-white via-gray-50 to-white">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 truncate">Your Reported Items</h3>
                        <span id="user-items-count"
                              class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 text-xs font-semibold border border-purple-100">
                            <i class="fas fa-list-ul text-[10px]"></i>
                            <span data-role="count">0</span>
                            <span class="hidden sm:inline">items</span>
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">Lost or found items you've posted</p>
                </div>

                <div class="flex flex-wrap items-center gap-2 lg:flex-nowrap">
                    @auth
                    <span class="inline-flex items-center gap-1.5 text-[11px] sm:text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-full px-2.5 py-1 max-w-full truncate">
                        <i class="fas fa-user-circle text-purple-400"></i>
                        <span class="hidden sm:inline">Signed in as</span>
                        <span class="font-medium text-gray-700 truncate">{{ auth()->user()->email }}</span>
                    </span>
                    @endauth
                    <button type="button" onclick="toggleUploadForm()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-purple-primary text-white rounded-lg hover:bg-purple-600 active:bg-purple-700 transition-colors text-sm font-medium shadow-sm whitespace-nowrap">
                        <i class="fas fa-plus text-xs"></i>
                        <span>Report New Item</span>
                    </button>
                </div>
            </div>
        </div>

        <div id="user-items-list" class="p-4 sm:p-6">
            <!-- Skeleton loading state (replaced by displayUserItems on fetch) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5" aria-busy="true" aria-label="Loading your items">
                <template id="user-items-skeleton-template">
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden animate-pulse">
                        <div class="w-full h-44 bg-gray-200"></div>
                        <div class="p-4 space-y-3">
                            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                            <div class="flex gap-2">
                                <div class="h-5 w-12 bg-gray-200 rounded-full"></div>
                                <div class="h-5 w-16 bg-gray-200 rounded-full"></div>
                            </div>
                            <div class="flex gap-2 pt-2">
                                <div class="h-9 bg-gray-200 rounded flex-1"></div>
                                <div class="h-9 bg-gray-200 rounded flex-1"></div>
                            </div>
                        </div>
                    </div>
                </template>
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden animate-pulse">
                    <div class="w-full h-44 bg-gray-200"></div>
                    <div class="p-4 space-y-3">
                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                        <div class="flex gap-2"><div class="h-5 w-12 bg-gray-200 rounded-full"></div><div class="h-5 w-16 bg-gray-200 rounded-full"></div></div>
                        <div class="flex gap-2 pt-2"><div class="h-9 bg-gray-200 rounded flex-1"></div><div class="h-9 bg-gray-200 rounded flex-1"></div></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden animate-pulse hidden sm:block">
                    <div class="w-full h-44 bg-gray-200"></div>
                    <div class="p-4 space-y-3">
                        <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                        <div class="flex gap-2"><div class="h-5 w-12 bg-gray-200 rounded-full"></div></div>
                        <div class="flex gap-2 pt-2"><div class="h-9 bg-gray-200 rounded flex-1"></div><div class="h-9 bg-gray-200 rounded flex-1"></div></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden animate-pulse hidden lg:block">
                    <div class="w-full h-44 bg-gray-200"></div>
                    <div class="p-4 space-y-3">
                        <div class="h-4 bg-gray-200 rounded w-4/5"></div>
                        <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                        <div class="flex gap-2"><div class="h-5 w-12 bg-gray-200 rounded-full"></div><div class="h-5 w-14 bg-gray-200 rounded-full"></div></div>
                        <div class="flex gap-2 pt-2"><div class="h-9 bg-gray-200 rounded flex-1"></div><div class="h-9 bg-gray-200 rounded flex-1"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="image-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg max-w-4xl max-h-full overflow-hidden relative">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Image Preview</h3>
            <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-4 relative">
            <img id="modal-image" src="" alt="Preview" class="max-w-full max-h-96 object-contain mx-auto">
            <div id="modal-image-nav" class="hidden absolute inset-0 flex items-center justify-between p-4">
                <button id="prev-image-btn" onclick="changeModalImage(-1)" class="bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button id="next-image-btn" onclick="changeModalImage(1)" class="bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div id="modal-image-counter" class="hidden absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-50 text-white px-3 py-1 rounded text-sm">
                <span id="current-image-num">1</span> / <span id="total-images-num">1</span>
            </div>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div id="edit-item-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden overflow-y-auto">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-full overflow-hidden my-8">
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-900">Edit Item</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="edit-item-form" class="p-6 space-y-6 overflow-y-auto max-h-[calc(100vh-200px)]">
            @csrf
            <input type="hidden" id="edit-upload-id" name="upload_id">
            
            <!-- Item Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Item Type</label>
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input type="radio" id="edit-item-type-lost" name="item_type" value="lost" class="mr-2">
                        <span class="text-sm text-gray-700">Lost Item</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" id="edit-item-type-found" name="item_type" value="found" class="mr-2">
                        <span class="text-sm text-gray-700">Found Item</span>
                    </label>
                </div>
            </div>

            @if($enableProvinceField ?? true)
            <!-- Province -->
            <div>
                <label for="edit-province" class="block text-sm font-medium text-gray-700 mb-2">
                    Province 
                    @if($provinceFieldRequired ?? true)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                <div class="relative">
                    <input type="text" 
                           id="edit-province" 
                           name="province" 
                           @if($provinceFieldRequired ?? true) required @endif
                           autocomplete="off"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Enter your province name">
                    <!-- Autocomplete dropdown -->
                    <div id="edit-province-autocomplete" class="hidden absolute z-50 w-full mt-1 bg-white border-2 border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                        <!-- Suggestions will be inserted here -->
                    </div>
                </div>
                <div id="edit-province-error-message" class="hidden mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg" style="display: none;">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        We're trying to expand our services to cover more locations. Please contact us if you'd like to see your province added.
                    </p>
                </div>
            </div>
            @endif

            @if($enableCityField ?? true)
            <!-- City -->
            <div>
                <label for="edit-city" class="block text-sm font-medium text-gray-700 mb-2">
                    City 
                    @if($cityFieldRequired ?? true)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                <div class="relative">
                    <input type="text" 
                           id="edit-city" 
                           name="city" 
                           @if($cityFieldRequired ?? true) required @endif
                           autocomplete="off"
                           class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Enter your city name">
                    <!-- Autocomplete dropdown -->
                    <div id="edit-city-autocomplete" class="hidden absolute z-50 w-full mt-1 bg-white border-2 border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                        <!-- Suggestions will be inserted here -->
                    </div>
                </div>
                <div id="edit-city-error-message" class="hidden mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg" style="display: none;">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        We're trying to expand our services to cover more locations. Please contact us if you'd like to see your city added.
                    </p>
                </div>
            </div>
            @endif

            <!-- Location -->
            <div>
                <label for="edit-location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                <div class="flex gap-2 mb-2 relative">
                    <div class="flex-1 relative">
                        <input type="text" id="edit-location" name="location" required autocomplete="off"
                               class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               placeholder="Where was this item found/lost? (e.g., Street name, Building, etc.)">
                        <!-- Location autocomplete dropdown -->
                        <div id="edit-location-autocomplete" class="hidden absolute z-50 w-full mt-1 bg-white border-2 border-purple-300 rounded-lg shadow-2xl max-h-60 overflow-y-auto">
                            <!-- Suggestions will be inserted here -->
                        </div>
                    </div>
                    <button type="button" onclick="useCurrentLocation('edit-location')" 
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm font-medium whitespace-nowrap">
                        <i class="fas fa-crosshairs mr-1"></i> Current Location
                    </button>
                </div>
                <!-- Hidden fields for coordinates -->
                <input type="hidden" id="edit-location-lat" name="location_lat">
                <input type="hidden" id="edit-location-lon" name="location_lon">
                <!-- Map container -->
                <div class="mt-3">
                    <div id="edit-location-map" class="w-full h-64 rounded-lg border border-gray-300 relative" style="display: none;">
                        <!-- Location autocomplete overlay on map -->
                        <div id="edit-location-autocomplete-map" class="hidden absolute top-2 left-2 right-2 z-[1000] bg-white border-2 border-purple-300 rounded-lg shadow-2xl max-h-60 overflow-y-auto">
                            <!-- Suggestions will be inserted here -->
                        </div>
                    </div>
                    <div class="mt-2 flex gap-2">
                        <button type="button" onclick="toggleLocationMap('edit-location')" 
                                class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors text-sm font-medium">
                            <i class="fas fa-map-marker-alt mr-1"></i> Pin on Map
                        </button>
                        <button type="button" onclick="clearLocationMap('edit-location')" 
                                class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors text-sm font-medium" 
                                style="display: none;" id="clear-edit-location-btn">
                            <i class="fas fa-times mr-1"></i> Clear Map
                        </button>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="edit-description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="edit-description" name="description" rows="4" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                          placeholder="Describe the item in detail..."></textarea>
            </div>

            <!-- Tags -->
            <div>
                <label for="edit-tags" class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                
                <!-- Tag Dropdown -->
                <div class="relative mb-2">
                    <select id="edit-tags-dropdown" 
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Select a tag...</option>
                        <!-- Options will be loaded dynamically -->
                    </select>
                </div>
                
                <!-- Add New Tag Button -->
                <div class="mb-2">
                    <button type="button" 
                            onclick="toggleEditNewTagInput()" 
                            class="text-sm text-purple-600 hover:text-purple-800 font-medium flex items-center gap-1">
                        <i class="fas fa-plus text-xs"></i>
                        Add another tag
                    </button>
                </div>
                
                <!-- Add New Tag Input (Hidden by default) -->
                <div id="edit-new-tag-input-container" class="flex gap-2 mb-2 hidden">
                    <input type="text" 
                           id="edit-new-tag-input" 
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="Type a new tag">
                    <button type="button" 
                            onclick="addEditTagFromInput()" 
                            class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button type="button" 
                            onclick="toggleEditNewTagInput()" 
                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Selected Tags Display -->
                <div id="edit-selected-tags-container" class="flex flex-wrap gap-2 mt-2 min-h-[20px]">
                    <!-- Selected tags will appear here -->
                </div>
                
                <!-- Hidden input to store selected tags as JSON -->
                <input type="hidden" id="edit-tags" name="tags">
                
                <p class="text-xs text-gray-500 mt-1">Select tags from the dropdown or add new ones. Tags help others find your item more easily.</p>
            </div>

            <!-- Existing Images -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Images</label>
                <div id="edit-existing-images" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <!-- Existing images will be loaded here -->
                </div>
                <p class="text-xs text-gray-500">Click on images to remove them</p>
            </div>

            <!-- New Images -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Add New Images (Optional)</label>
                
                <!-- Drag and Drop Zone -->
                <div id="edit-drop-zone" class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-all duration-200 hover:border-purple-400 hover:bg-purple-50 cursor-pointer">
                    <input type="file" id="edit-new-images" name="images[]" multiple accept="image/*" class="hidden">
                    
                    <div id="edit-drop-zone-content" class="space-y-3">
                        <div class="flex justify-center">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-cloud-upload-alt text-purple-600 text-xl"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-1">
                                <span class="text-purple-600">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB each (Max 5 images)</p>
                        </div>
                        <button type="button" onclick="document.getElementById('edit-new-images').click()" 
                                class="inline-flex items-center px-3 py-1.5 bg-purple-primary text-white rounded-lg hover:bg-purple-600 transition-colors text-xs font-medium">
                            <i class="fas fa-folder-open mr-1"></i>
                            Browse Files
                        </button>
                    </div>
                </div>

                <!-- Image Previews -->
                <div id="edit-image-preview-container" class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 hidden">
                    <!-- Image previews will be inserted here -->
                </div>
            </div>

            <!-- Hidden input for removed images -->
            <input type="hidden" id="edit-remove-images" name="remove_images">

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="bg-purple-primary text-white px-6 py-2 rounded-lg hover:bg-purple-600 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Build stamp — bump when you ship changes to this view.
// If your DevTools console doesn't print this version on load, you are
// looking at a cached HTML and need to hard-refresh (Cmd+Shift+R).
window.__REPORTED_ITEMS_VERSION__ = '2026-05-11-pin-autofill-v1';
console.info('[ReportedItems] version:', window.__REPORTED_ITEMS_VERSION__);

let selectedFiles = [];
let uploadProgress = 0;

// Toggle upload form
function toggleUploadForm() {
    const form = document.getElementById('upload-form');
    form.classList.toggle('hidden');
    
    // Initialize province and city autocomplete when form is shown
    if (!form.classList.contains('hidden')) {
        // Small delay to ensure form is fully visible
        setTimeout(function() {
            initProvinceAutocomplete();
            initCityAutocomplete();
        }, 100);
    }
    
    // Reset form when closing
    if (form.classList.contains('hidden')) {
        resetImageUpload();
    }
}

// Reset image upload
function resetImageUpload() {
    selectedFiles = [];
    document.getElementById('item-images').value = '';
    document.getElementById('image-preview-container').innerHTML = '';
    document.getElementById('image-preview-container').classList.add('hidden');
    document.getElementById('upload-progress-container').classList.add('hidden');
    document.getElementById('upload-progress-bar').style.width = '0%';
    document.getElementById('upload-progress-text').textContent = '0%';
}

// Initialize drag and drop
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('item-images');
    const previewContainer = document.getElementById('image-preview-container');

    // Click to upload
    dropZone.addEventListener('click', function(e) {
        if (e.target.closest('button')) return;
        fileInput.click();
    });

    // File input change
    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

    // Drag and drop events
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-purple-500', 'bg-purple-100');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-purple-500', 'bg-purple-100');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-purple-500', 'bg-purple-100');
        
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    // Handle selected files
    function handleFiles(files) {
        const maxFiles = 5;
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        // Filter valid image files
        const validFiles = Array.from(files).filter(file => {
            if (!file.type.startsWith('image/')) {
                showToast('Only image files are allowed', 'error');
                return false;
            }
            if (file.size > maxSize) {
                showToast(`File ${file.name} is too large. Maximum size is 10MB`, 'error');
                return false;
            }
            return true;
        });

        // Check total file count
        const totalFiles = selectedFiles.length + validFiles.length;
        if (totalFiles > maxFiles) {
            showToast(`Maximum ${maxFiles} images allowed. You selected ${totalFiles} files.`, 'error');
            validFiles.splice(maxFiles - selectedFiles.length);
        }

        // Add to selected files
        validFiles.forEach(file => {
            if (!selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
                selectedFiles.push(file);
            }
        });

        // Update file input
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;

        // Show previews
        displayImagePreviews();
    }

    // Display image previews
    function displayImagePreviews() {
        if (selectedFiles.length === 0) {
            previewContainer.classList.add('hidden');
            return;
        }

        previewContainer.classList.remove('hidden');
        previewContainer.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'relative group';
                previewDiv.innerHTML = `
                    <div class="relative aspect-square rounded-lg overflow-hidden border-2 border-gray-200 hover:border-purple-400 transition-colors" style="background-color: #f3f4f6;">
                        <img src="${e.target.result}" 
                             alt="${file.name}" 
                             class="w-full h-full object-cover"
                             style="display: block; background-color: transparent; position: relative; z-index: 1; opacity: 1;"
                             onerror="console.error('Preview image failed to load:', this.src); this.style.display='none';"
                             onload="console.log('Preview image loaded:', this.src); this.style.backgroundColor='transparent'; this.style.opacity='1';">
                        <div class="absolute inset-0 transition-all flex items-center justify-center pointer-events-none" style="background-color: transparent;">
                            <button onclick="removeImage(${index})" class="opacity-0 group-hover:opacity-100 bg-red-500 text-white px-3 py-1 rounded-lg text-sm font-medium transition-opacity hover:bg-red-600 pointer-events-auto z-10">
                                <i class="fas fa-trash mr-1"></i>Remove
                            </button>
                        </div>
                        <div class="absolute top-2 right-2 bg-white rounded-full p-1 shadow-md">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-600 truncate" title="${file.name}">${file.name}</p>
                    <p class="text-xs text-gray-500">${formatFileSize(file.size)}</p>
                `;
                previewContainer.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        });
    }

    // Make removeImage function global
    window.removeImage = function(index) {
        selectedFiles.splice(index, 1);
        
        // Update file input
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;

        displayImagePreviews();
    };

    // Format file size
    window.formatFileSize = function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    };
});

// Upload progress functions
function showUploadProgress() {
    const progressContainer = document.getElementById('upload-progress-container');
    if (!progressContainer) return;
    progressContainer.classList.remove('hidden');
    updateUploadProgress(0);
    setUploadStage('preparing');
    progressContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function hideUploadProgress() {
    const progressContainer = document.getElementById('upload-progress-container');
    if (!progressContainer) return;
    progressContainer.classList.add('hidden');
    updateUploadProgress(0);
    const matchingMessage = document.getElementById('matching-check-message');
    if (matchingMessage) {
        matchingMessage.classList.add('hidden');
        matchingMessage.classList.remove('bg-green-50', 'border-green-200');
        matchingMessage.classList.add('bg-blue-50', 'border-blue-200');
    }
}

function updateUploadProgress(percentage) {
    const progressBar = document.getElementById('upload-progress-bar');
    const progressText = document.getElementById('upload-progress-text');
    if (!progressBar || !progressText) return;
    const clamped = Math.max(0, Math.min(100, percentage));
    progressBar.style.width = clamped + '%';
    progressText.textContent = Math.round(clamped) + '%';
}

/**
 * Update the staged status (icon + label + helper text).
 * Stages: preparing | uploading | processing | done | error
 *
 * For the "uploading" and "processing" stages the helper also schedules a
 * short timeline of reassurance messages (see STAGE_TIMELINE) so the UI keeps
 * communicating progress when the underlying work takes longer than usual.
 */
const STAGE_TIMELINE = {
    // While the XHR is still sending bytes. Each progress event re-applies
    // the bytes-sent text via setUploadStage('uploading', {bytesText}), so
    // these are *appended* as a soft hint rather than replacing the detail.
    uploading: [
        { afterMs: 12000, suffix: ' · Taking a bit longer than expected — please don\'t close this page.' },
        { afterMs: 30000, suffix: ' · Still uploading — your photos are safe, hold tight.' },
        { afterMs: 60000, suffix: ' · We\'re working through a slow connection. Hang in there.' },
    ],
    // After the bytes are on the server, while the backend persists the item
    // and queues similarity checking. These cycle through to show what's
    // happening behind the scenes.
    processing: [
        { afterMs: 0,     label: 'Saving to your account…',           detail: 'Finalising your post' },
        { afterMs: 3500,  label: 'Analysing image content…',          detail: 'Running object detection on your photos' },
        { afterMs: 8000,  label: 'Matching similar items…',           detail: 'Comparing against the database for similar reports' },
        { afterMs: 14000, label: 'Almost done…',                      detail: 'Wrapping up notifications' },
        { afterMs: 25000, label: 'Taking a bit longer than expected', detail: 'Your upload is safe — we\'re still finalising' },
    ],
};

let __uploadStageTimers = [];
let __uploadStageStart = 0;
let __currentStage = null;
let __lastBytesText = '';

function __clearStageTimers() {
    __uploadStageTimers.forEach((t) => clearTimeout(t));
    __uploadStageTimers = [];
}

function setUploadStage(stage, opts = {}) {
    const iconWrap = document.getElementById('upload-stage-icon');
    const label = document.getElementById('upload-stage-label');
    const detail = document.getElementById('upload-stage-detail');
    if (!iconWrap || !label || !detail) return;

    const stages = {
        preparing: {
            icon: '<i class="fas fa-circle-notch fa-spin text-base sm:text-lg"></i>',
            label: 'Preparing your upload…',
            detail: opts.fileCount
                ? `Getting ${opts.fileCount} image${opts.fileCount === 1 ? '' : 's'} ready`
                : 'Getting your images ready',
            color: 'text-purple-600',
        },
        uploading: {
            icon: '<i class="fas fa-cloud-upload-alt text-base sm:text-lg"></i>',
            label: opts.fileCount
                ? `Uploading ${opts.fileCount} image${opts.fileCount === 1 ? '' : 's'}…`
                : 'Uploading images…',
            detail: opts.bytesText || 'Sending to FindITFast',
            color: 'text-purple-600',
        },
        processing: {
            icon: '<i class="fas fa-cog fa-spin text-base sm:text-lg"></i>',
            label: 'Saving to your account…',
            detail: 'Almost done, finalizing your post',
            color: 'text-purple-600',
        },
        done: {
            icon: '<i class="fas fa-check text-base sm:text-lg"></i>',
            label: 'Done! Item posted',
            detail: 'Match checking continues in the background',
            color: 'text-green-600',
        },
        error: {
            icon: '<i class="fas fa-exclamation-triangle text-base sm:text-lg"></i>',
            label: opts.label || 'Upload failed',
            detail: opts.detail || 'Please try again',
            color: 'text-red-600',
        },
    };

    const s = stages[stage] || stages.preparing;
    iconWrap.className = `inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white shadow shrink-0 ${s.color}`;
    iconWrap.innerHTML = s.icon;
    label.textContent = s.label;

    // Track the bytes-sent text so the long-running suffix can be appended
    // without clobbering the live byte counter on each progress event.
    if (stage === 'uploading' && opts.bytesText) {
        __lastBytesText = opts.bytesText;
    }
    detail.textContent = s.detail;

    // Reset the timeline whenever the stage actually changes. Repeated
    // setUploadStage('uploading', ...) calls during progress events just
    // re-set the bytes-sent text and let the existing timers keep running.
    if (stage !== __currentStage) {
        __clearStageTimers();
        __currentStage = stage;
        __uploadStageStart = Date.now();

        const timeline = STAGE_TIMELINE[stage];
        if (Array.isArray(timeline)) {
            timeline.forEach((step) => {
                const tid = setTimeout(() => {
                    const labelEl = document.getElementById('upload-stage-label');
                    const detailEl = document.getElementById('upload-stage-detail');
                    if (!labelEl || !detailEl) return;
                    if (__currentStage !== stage) return; // stage changed under us
                    if (step.label) labelEl.textContent = step.label;
                    if (step.detail) detailEl.textContent = step.detail;
                    if (step.suffix) {
                        // Append a hint after whatever the current detail is —
                        // typically the live "X / Y sent" string.
                        const base = __lastBytesText || detailEl.textContent || '';
                        detailEl.textContent = base + step.suffix;
                    }
                }, step.afterMs);
                __uploadStageTimers.push(tid);
            });
        }
    } else if (stage === 'uploading' && opts.bytesText) {
        // Same uploading stage, fresh progress tick — refresh the visible
        // bytes string. If a "taking longer" suffix is already showing,
        // re-apply it so it doesn't disappear on the next tick.
        const elapsed = Date.now() - __uploadStageStart;
        const activeSuffix = (STAGE_TIMELINE.uploading || [])
            .filter((step) => step.suffix && elapsed >= step.afterMs)
            .pop();
        detail.textContent = activeSuffix
            ? opts.bytesText + activeSuffix.suffix
            : opts.bytesText;
    }

    if (stage === 'done' || stage === 'error') {
        __clearStageTimers();
    }
}

function showMatchingCheckMessage() {
    const matchingMessage = document.getElementById('matching-check-message');
    if (matchingMessage) {
        matchingMessage.classList.remove('hidden');
    }
}

function formatBytes(bytes) {
    if (bytes === 0 || !Number.isFinite(bytes)) return '0 B';
    const units = ['B', 'KB', 'MB', 'GB'];
    const i = Math.min(units.length - 1, Math.floor(Math.log(bytes) / Math.log(1024)));
    return (bytes / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 1) + ' ' + units[i];
}

// City autocomplete functionality for create form
// Province autocomplete functionality for create form
function initProvinceAutocomplete() {
    const enabledProvinces = @json($enabledProvinces ?? []);
    const provinceInput = document.getElementById('province');
    const provinceAutocomplete = document.getElementById('province-autocomplete');
    const provinceErrorMessage = document.getElementById('province-error-message');

    // Remove existing event listeners by cloning and replacing the element
    if (provinceInput && provinceInput.hasAttribute('data-province-autocomplete-initialized')) {
        return; // Already initialized
    }

    if (provinceInput && enabledProvinces.length > 0) {
        provinceInput.setAttribute('data-province-autocomplete-initialized', 'true');
        provinceInput.addEventListener('input', function(e) {
            const query = e.target.value.trim().toLowerCase();
            
            // Hide error message initially
            if (provinceErrorMessage) {
                provinceErrorMessage.classList.add('hidden');
                provinceErrorMessage.style.display = 'none';
            }

            if (query.length === 0) {
                if (provinceAutocomplete) {
                    provinceAutocomplete.classList.add('hidden');
                }
                return;
            }

            // Filter provinces that match the query
            const matches = enabledProvinces.filter(province => 
                province.toLowerCase().includes(query)
            ).slice(0, 10);

            if (matches.length > 0) {
                // Show dropdown with matches
                if (provinceAutocomplete) {
                    provinceAutocomplete.innerHTML = '';
                    matches.forEach((province, index) => {
                        const div = document.createElement('div');
                        div.className = 'px-4 py-3 hover:bg-purple-50 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors province-suggestion';
                        div.setAttribute('data-index', index);
                        
                        // Highlight matching text
                        const provinceLower = province.toLowerCase();
                        const queryIndex = provinceLower.indexOf(query);
                        if (queryIndex !== -1) {
                            const beforeMatch = province.substring(0, queryIndex);
                            const match = province.substring(queryIndex, queryIndex + query.length);
                            const afterMatch = province.substring(queryIndex + query.length);
                            div.innerHTML = `${beforeMatch}<strong class="text-purple-600">${match}</strong>${afterMatch}`;
                        } else {
                            div.textContent = province;
                        }
                        
                        div.addEventListener('click', function() {
                            provinceInput.value = province;
                            if (provinceAutocomplete) {
                                provinceAutocomplete.classList.add('hidden');
                            }
                            if (provinceErrorMessage) {
                                provinceErrorMessage.classList.add('hidden');
                                provinceErrorMessage.style.display = 'none';
                            }
                        });
                        
                        div.addEventListener('mouseenter', function() {
                            div.classList.add('bg-purple-50');
                        });
                        
                        div.addEventListener('mouseleave', function() {
                            div.classList.remove('bg-purple-50');
                        });
                        
                        provinceAutocomplete.appendChild(div);
                    });
                    provinceAutocomplete.classList.remove('hidden');
                }
                // Hide error message when matches are found
                if (provinceErrorMessage) {
                    provinceErrorMessage.classList.add('hidden');
                    provinceErrorMessage.style.display = 'none';
                }
            } else {
                // No matches found - hide dropdown and show error message
                if (provinceAutocomplete) {
                    provinceAutocomplete.classList.add('hidden');
                }
                if (query.length >= 2 && provinceErrorMessage) {
                    provinceErrorMessage.classList.remove('hidden');
                    provinceErrorMessage.style.display = 'block';
                } else if (provinceErrorMessage) {
                    provinceErrorMessage.classList.add('hidden');
                    provinceErrorMessage.style.display = 'none';
                }
            }
        });

        // Hide autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            if (provinceInput && provinceAutocomplete && 
                !provinceInput.contains(e.target) && !provinceAutocomplete.contains(e.target)) {
                provinceAutocomplete.classList.add('hidden');
            }
        });
    }
}

function initCityAutocomplete() {
    const enabledCities = @json($enabledCities ?? []);
    const cityInput = document.getElementById('city');
    const cityAutocomplete = document.getElementById('city-autocomplete');
    const cityErrorMessage = document.getElementById('city-error-message');

    // Remove existing event listeners by cloning and replacing the element
    if (cityInput && cityInput.hasAttribute('data-autocomplete-initialized')) {
        return; // Already initialized
    }

    if (cityInput && enabledCities.length > 0) {
        cityInput.setAttribute('data-autocomplete-initialized', 'true');
        cityInput.addEventListener('input', function(e) {
            const query = e.target.value.trim().toLowerCase();
            
            // Hide error message initially
            if (cityErrorMessage) {
                cityErrorMessage.classList.add('hidden');
            }

            if (query.length === 0) {
                if (cityAutocomplete) {
                    cityAutocomplete.classList.add('hidden');
                }
                return;
            }

            // Filter cities that match the query
            const matches = enabledCities.filter(city => 
                city.toLowerCase().includes(query)
            ).slice(0, 10);

            if (matches.length > 0) {
                // Show dropdown with matches
                if (cityAutocomplete) {
                    cityAutocomplete.innerHTML = '';
                    matches.forEach((city, index) => {
                        const div = document.createElement('div');
                        div.className = 'px-4 py-3 hover:bg-purple-50 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors city-suggestion';
                        div.setAttribute('data-index', index);
                        
                        // Highlight matching text
                        const cityLower = city.toLowerCase();
                        const queryIndex = cityLower.indexOf(query);
                        if (queryIndex !== -1) {
                            const beforeMatch = city.substring(0, queryIndex);
                            const match = city.substring(queryIndex, queryIndex + query.length);
                            const afterMatch = city.substring(queryIndex + query.length);
                            div.innerHTML = `${beforeMatch}<strong class="text-purple-600">${match}</strong>${afterMatch}`;
                        } else {
                            div.textContent = city;
                        }
                        
                        div.addEventListener('click', function() {
                            cityInput.value = city;
                            if (cityAutocomplete) {
                                cityAutocomplete.classList.add('hidden');
                            }
                            if (cityErrorMessage) {
                                cityErrorMessage.classList.add('hidden');
                            }
                        });
                        
                        div.addEventListener('mouseenter', function() {
                            div.classList.add('bg-purple-50');
                        });
                        
                        div.addEventListener('mouseleave', function() {
                            div.classList.remove('bg-purple-50');
                        });
                        
                        cityAutocomplete.appendChild(div);
                    });
                    cityAutocomplete.classList.remove('hidden');
                }
                // Hide error message when matches are found
                if (cityErrorMessage) {
                    cityErrorMessage.classList.add('hidden');
                }
            } else {
                // No matches found - hide dropdown and show error message
                if (cityAutocomplete) {
                    cityAutocomplete.classList.add('hidden');
                }
                if (query.length >= 2 && cityErrorMessage) {
                    cityErrorMessage.classList.remove('hidden');
                    cityErrorMessage.style.display = 'block';
                } else if (cityErrorMessage) {
                    cityErrorMessage.classList.add('hidden');
                    cityErrorMessage.style.display = 'none';
                }
            }
        });

        // Hide autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            if (cityInput && cityAutocomplete && 
                !cityInput.contains(e.target) && !cityAutocomplete.contains(e.target)) {
                cityAutocomplete.classList.add('hidden');
            }
        });
    }
}

// Initialize on page load if form is visible
document.addEventListener('DOMContentLoaded', function() {
    initProvinceAutocomplete();
    initCityAutocomplete();
});

// City autocomplete functionality for edit form
(function() {
    const enabledCities = @json($enabledCities ?? []);
    const editCityInput = document.getElementById('edit-city');
    const editCityAutocomplete = document.getElementById('edit-city-autocomplete');
    const editCityErrorMessage = document.getElementById('edit-city-error-message');

    if (editCityInput && enabledCities.length > 0) {
        editCityInput.addEventListener('input', function(e) {
            const query = e.target.value.trim().toLowerCase();
            
            // Hide error message initially
            if (editCityErrorMessage) {
                editCityErrorMessage.classList.add('hidden');
            }

            if (query.length === 0) {
                if (editCityAutocomplete) {
                    editCityAutocomplete.classList.add('hidden');
                }
                return;
            }

            // Filter cities that match the query
            const matches = enabledCities.filter(city => 
                city.toLowerCase().includes(query)
            ).slice(0, 10);

            if (matches.length > 0) {
                // Show dropdown with matches
                if (editCityAutocomplete) {
                    editCityAutocomplete.innerHTML = '';
                    matches.forEach((city, index) => {
                        const div = document.createElement('div');
                        div.className = 'px-4 py-3 hover:bg-purple-50 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors city-suggestion';
                        div.setAttribute('data-index', index);
                        
                        // Highlight matching text
                        const cityLower = city.toLowerCase();
                        const queryIndex = cityLower.indexOf(query);
                        if (queryIndex !== -1) {
                            const beforeMatch = city.substring(0, queryIndex);
                            const match = city.substring(queryIndex, queryIndex + query.length);
                            const afterMatch = city.substring(queryIndex + query.length);
                            div.innerHTML = `${beforeMatch}<strong class="text-purple-600">${match}</strong>${afterMatch}`;
                        } else {
                            div.textContent = city;
                        }
                        
                        div.addEventListener('click', function() {
                            editCityInput.value = city;
                            if (editCityAutocomplete) {
                                editCityAutocomplete.classList.add('hidden');
                            }
                            if (editCityErrorMessage) {
                                editCityErrorMessage.classList.add('hidden');
                            }
                        });
                        
                        div.addEventListener('mouseenter', function() {
                            div.classList.add('bg-purple-50');
                        });
                        
                        div.addEventListener('mouseleave', function() {
                            div.classList.remove('bg-purple-50');
                        });
                        
                        editCityAutocomplete.appendChild(div);
                    });
                    editCityAutocomplete.classList.remove('hidden');
                }
                // Hide error message when matches are found
                if (editCityErrorMessage) {
                    editCityErrorMessage.classList.add('hidden');
                }
            } else {
                // No matches found - hide dropdown and show error message
                if (editCityAutocomplete) {
                    editCityAutocomplete.classList.add('hidden');
                }
                if (query.length >= 2 && editCityErrorMessage) {
                    editCityErrorMessage.classList.remove('hidden');
                    editCityErrorMessage.style.display = 'block';
                } else if (editCityErrorMessage) {
                    editCityErrorMessage.classList.add('hidden');
                    editCityErrorMessage.style.display = 'none';
                }
            }
        });

        // Hide autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            if (editCityInput && editCityAutocomplete && 
                !editCityInput.contains(e.target) && !editCityAutocomplete.contains(e.target)) {
                editCityAutocomplete.classList.add('hidden');
            }
        });
    }
})();

// Form submission
document.getElementById('item-upload-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Prevent double submission
    if (this.dataset.submitting === 'true') {
        return;
    }
    this.dataset.submitting = 'true';

    // Get field visibility settings
    const enableProvinceField = @json($enableProvinceField ?? true);
    const provinceFieldRequired = @json($provinceFieldRequired ?? true);
    const enableCityField = @json($enableCityField ?? true);
    const cityFieldRequired = @json($cityFieldRequired ?? true);

    // Get form elements
    const files = document.getElementById('item-images').files;
    const itemType = document.querySelector('input[name="item_type"]:checked');
    const cityElement = document.getElementById('city');
    const provinceElement = document.getElementById('province');
    const city = cityElement ? cityElement.value.trim() : '';
    const province = provinceElement ? provinceElement.value.trim() : '';
    const location = document.getElementById('location').value.trim();
    const description = document.getElementById('description').value.trim();
    // Get tags from selected tags array
    const tagsInput = document.getElementById('tags');
    const tags = tagsInput ? JSON.parse(tagsInput.value || '[]').join(', ') : '';

    // Client-side validation
    if (!itemType) {
        showToast('Please select an item type', 'error');
        this.dataset.submitting = 'false';
        return;
    }

    // Validate that at least one image is selected
    if (!files || files.length === 0) {
        showToast('Please select at least one image to upload.', 'error');
        // Focus the drop zone to indicate where to add images
        const dropZone = document.getElementById('drop-zone');
        if (dropZone) {
            dropZone.scrollIntoView({ behavior: 'smooth', block: 'center' });
            dropZone.style.borderColor = '#ef4444';
            dropZone.style.borderWidth = '3px';
            setTimeout(() => {
                dropZone.style.borderColor = '';
                dropZone.style.borderWidth = '';
            }, 2000);
        }
        this.dataset.submitting = 'false';
        return;
    }

    // Validate city only if field is enabled
    if (enableCityField) {
        if (cityFieldRequired && !city) {
            showToast('Please enter a city', 'error');
            this.dataset.submitting = 'false';
            return;
        }

        // Validate city is in enabled cities list (only if city is provided)
        if (city) {
            const enabledCities = @json($enabledCities ?? []);
            if (enabledCities.length > 0) {
                const isValidCity = enabledCities.some(c => c.toLowerCase() === city.toLowerCase());
                if (!isValidCity) {
                    const cityErrorMessage = document.getElementById('city-error-message');
                    if (cityErrorMessage) {
                        cityErrorMessage.classList.remove('hidden');
                    }
                    showToast('Please select a valid city from the suggestions', 'error');
                    this.dataset.submitting = 'false';
                    return;
                }
            }
        }
    }

    // Validate province only if field is enabled
    if (enableProvinceField) {
        if (provinceFieldRequired && !province) {
            showToast('Please enter a province', 'error');
            this.dataset.submitting = 'false';
            return;
        }

        // Validate province is in enabled provinces list (only if province is provided)
        if (province) {
            const enabledProvinces = @json($enabledProvinces ?? []);
            if (enabledProvinces.length > 0) {
                const isValidProvince = enabledProvinces.some(p => p.toLowerCase() === province.toLowerCase());
                if (!isValidProvince) {
                    const provinceErrorMessage = document.getElementById('province-error-message');
                    if (provinceErrorMessage) {
                        provinceErrorMessage.classList.remove('hidden');
                    }
                    showToast('Please select a valid province from the suggestions', 'error');
                    this.dataset.submitting = 'false';
                    return;
                }
            }
        }
    }

    if (!location) {
        showToast('Please enter a location', 'error');
        this.dataset.submitting = 'false';
        return;
    }

    if (!description) {
        showToast('Please enter a description', 'error');
        this.dataset.submitting = 'false';
        return;
    }

    if (!tags || tags.trim() === '') {
        showToast('Please select or add at least one tag', 'error');
        this.dataset.submitting = 'false';
        return;
    }

    if (files.length === 0) {
        showToast('Please select at least one image', 'error');
        this.dataset.submitting = 'false';
        return;
    }

    // Show upload progress UI
    showUploadProgress();

    // Build FormData (deduplicate files client-side)
    const formData = new FormData();
    formData.append('item_type', itemType.value);

    if (enableCityField) {
        formData.append('city', city || '');
    }
    if (enableProvinceField) {
        formData.append('province', province || '');
    }

    formData.append('location', location);
    formData.append('description', description);
    const tagsArray = JSON.parse(document.getElementById('tags').value || '[]');
    formData.append('tags', JSON.stringify(tagsArray));

    const uniqueFiles = new Map();
    for (let file of files) {
        const key = `${file.name}-${file.size}`;
        if (!uniqueFiles.has(key)) {
            uniqueFiles.set(key, file);
        }
    }
    let totalBytes = 0;
    for (let file of uniqueFiles.values()) {
        formData.append('images[]', file);
        totalBytes += file.size || 0;
    }
    const fileCount = uniqueFiles.size;

    // Disable submit button during upload
    const submitButton = this.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...';

    // Keep the form available via closure (we need it inside XHR callbacks where `this` differs)
    const formEl = this;

    const finalize = () => {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
        formEl.dataset.submitting = 'false';
    };

    const handleSuccess = (data) => {
        updateUploadProgress(100);
        setUploadStage('done');
        showMatchingCheckMessage();

        const successMessage = (data && data.message)
            || `Done! Your item is posted. We'll send a bell notification if we find a match.`;
        showToast(successMessage, 'success');

        formEl.reset();
        resetImageUpload();

        const matchingMessage = document.getElementById('matching-check-message');
        if (matchingMessage) {
            matchingMessage.classList.remove('bg-blue-50', 'border-blue-200');
            matchingMessage.classList.add('bg-green-50', 'border-green-200');
        }

        // Briefly let the user see "Done!" before hiding the form.
        setTimeout(() => {
            hideUploadProgress();
            loadItems().finally(() => {
                setTimeout(toggleUploadForm, 200);
            });
        }, 900);
    };

    const handleFailure = (message) => {
        setUploadStage('error', { detail: message });
        setTimeout(hideUploadProgress, 1500);
        showToast(message, 'error');
    };

    try {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/items/upload');
        xhr.withCredentials = true;
        xhr.responseType = 'text';
        xhr.timeout = 120000;
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        if (csrf) {
            xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
        }

        setUploadStage('uploading', {
            fileCount,
            bytesText: `0 / ${formatBytes(totalBytes)} sent`,
        });
        updateUploadProgress(0);

        let lastProgressUpdate = 0;
        xhr.upload.addEventListener('progress', (event) => {
            if (!event.lengthComputable) return;
            // Reserve the last 5% for server-side processing so it doesn't sit at 100%.
            const uploadedRatio = event.total > 0 ? (event.loaded / event.total) : 0;
            const percent = Math.min(95, uploadedRatio * 95);
            updateUploadProgress(percent);

            const now = Date.now();
            if (now - lastProgressUpdate > 150) {
                lastProgressUpdate = now;
                setUploadStage('uploading', {
                    fileCount,
                    bytesText: `${formatBytes(event.loaded)} / ${formatBytes(event.total)} sent`,
                });
            }
        });

        xhr.upload.addEventListener('load', () => {
            updateUploadProgress(95);
            setUploadStage('processing');
        });

        xhr.upload.addEventListener('error', () => {
            handleFailure('Network error while uploading. Please check your connection and try again.');
            finalize();
        });

        xhr.addEventListener('timeout', () => {
            handleFailure('Upload timed out. The file may be too large or the connection is slow.');
            finalize();
        });

        xhr.addEventListener('error', () => {
            handleFailure('Network error. Please check your connection and try again.');
            finalize();
        });

        xhr.addEventListener('load', () => {
            const status = xhr.status;
            const responseText = xhr.responseText || '';
            let data = null;
            try {
                data = responseText ? JSON.parse(responseText) : null;
            } catch (_) {
                data = null;
            }

            if (status >= 200 && status < 300) {
                if (data && data.success === false) {
                    handleFailure(data.message || 'Error uploading item. Please try again.');
                } else {
                    handleSuccess(data || {});
                }
            } else if (status === 401 || status === 419) {
                handleFailure('Your session expired. Please refresh the page and log in again.');
            } else if (status === 422 && data && data.errors) {
                const firstField = Object.keys(data.errors)[0];
                const firstError = firstField && Array.isArray(data.errors[firstField])
                    ? data.errors[firstField][0]
                    : (data.message || 'Validation failed. Please check the fields.');
                handleFailure(firstError);
            } else {
                handleFailure((data && data.message) || `Upload failed (status ${status}). Please try again.`);
            }
            finalize();
        });

        xhr.send(formData);
    } catch (error) {
        console.error('Upload error:', error);
        handleFailure(error?.message || 'Error uploading item. Please try again.');
        finalize();
    }
});

/**
 * Render a small skeleton grid into the items container.
 * Used both on initial load and on every refetch so the user always
 * gets a perceived-fast loading state, not a blank panel.
 */
function renderItemsSkeleton(container) {
    if (!container) return;
    const card = `
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden animate-pulse">
            <div class="w-full h-44 bg-gray-200"></div>
            <div class="p-4 space-y-3">
                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                <div class="flex gap-2">
                    <div class="h-5 w-12 bg-gray-200 rounded-full"></div>
                    <div class="h-5 w-16 bg-gray-200 rounded-full"></div>
                </div>
                <div class="flex gap-2 pt-2">
                    <div class="h-9 bg-gray-200 rounded flex-1"></div>
                    <div class="h-9 bg-gray-200 rounded flex-1"></div>
                </div>
            </div>
        </div>`;
    container.innerHTML = `
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5" aria-busy="true">
            ${card}
            <div class="hidden sm:block">${card}</div>
            <div class="hidden lg:block">${card}</div>
        </div>`;
}

/**
 * Update the small count chip in the section header.
 */
function updateUserItemsCount(n) {
    const chip = document.getElementById('user-items-count');
    if (!chip) return;
    const label = chip.querySelector('[data-role="count"]');
    if (label) label.textContent = String(n);
}

/**
 * Format an ISO date as a friendly relative string.
 *   <1 min   -> "just now"
 *   <60 min  -> "X min ago"
 *   <24 h    -> "X hr ago"
 *   <7 d     -> "X day(s) ago"
 *   else     -> "MMM D, YYYY"
 */
function formatRelativeDate(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '';
    const diff = (Date.now() - d.getTime()) / 1000; // seconds
    if (diff < 60) return 'just now';
    if (diff < 3600) return `${Math.floor(diff / 60)} min ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} hr ago`;
    if (diff < 86400 * 7) {
        const days = Math.floor(diff / 86400);
        return days <= 1 ? 'yesterday' : `${days} days ago`;
    }
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

// Load user items
async function loadItems() {
    const itemsContainer = document.getElementById('user-items-list');
    if (!itemsContainer) {
        console.error('Items container not found');
        return;
    }

    renderItemsSkeleton(itemsContainer);

    try {
        console.log('Fetching user items from /api/items');
        const response = await fetch('/api/items', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        console.log('Response status:', response.status, response.statusText);

        if (response.ok) {
            const data = await response.json();
            console.log('Response data:', data);

            if (data.success && Array.isArray(data.data)) {
                console.log('Items loaded:', data.data.length);
                displayUserItems(data.data);
            } else {
                console.error('Invalid response format:', data);
                showErrorState('Failed to load items: ' + (data.message || 'Invalid response format'));
            }
        } else {
            const errorData = await response.json().catch(() => ({ message: 'Unknown error' }));
            console.error('API error:', response.status, errorData);
            
            if (response.status === 401) {
                alert('You need to be logged in to view your items. Please log in and try again.');
                window.location.href = '/login';
            } else {
                showErrorState('Failed to load items: ' + (errorData.message || 'Please try again.'));
            }
        }
    } catch (error) {
        console.error('Error loading items:', error);
        showErrorState('Error loading items: ' + error.message);
    }
}

function displayUserItems(items) {
    const itemsContainer = document.getElementById('user-items-list');
    if (!itemsContainer) {
        console.error('Items container not found');
        return;
    }

    try {
        // Store items globally for access in other functions
        window.userItems = items;
        
        // Debug: Log items to check image paths
        console.log('Displaying items:', items);
        items.forEach(item => {
            if (item.images && item.images.length > 0) {
                console.log(`Item ${item.upload_id} images:`, item.images);
                item.images.forEach((img, idx) => {
                    console.log(`  Image ${idx}:`, img.path || img.file_path, 'Full image object:', img);
                });
            } else {
                console.warn(`Item ${item.upload_id} has no images!`, item);
            }
        });

        updateUserItemsCount(items.length);

        if (items.length === 0) {
            itemsContainer.innerHTML = `
                <div class="text-center py-12 sm:py-16">
                    <div class="relative w-20 h-20 mx-auto mb-5">
                        <div class="absolute inset-0 bg-purple-100 rounded-full"></div>
                        <div class="absolute inset-2 bg-white rounded-full flex items-center justify-center shadow-sm">
                            <i class="fas fa-box-open text-purple-500 text-2xl"></i>
                        </div>
                    </div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-1.5">No items yet</h3>
                    <p class="text-sm text-gray-500 mb-5 max-w-sm mx-auto">When you report a lost or found item, it will show up here so you can manage it.</p>
                    <button type="button" onclick="toggleUploadForm()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-primary text-white rounded-lg hover:bg-purple-600 active:bg-purple-700 transition-colors text-sm font-medium shadow-sm">
                        <i class="fas fa-plus text-xs"></i>
                        Report Your First Item
                    </button>
                </div>
            `;
            return;
        }

        itemsContainer.innerHTML = `
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 auto-rows-fr items-stretch">
            ${items.map(item => {
                const firstImage = item.images && item.images.length > 0 ? item.images[0] : null;
                let imageUrl = null;
                if (firstImage) {
                    if (firstImage.path) {
                        imageUrl = firstImage.path.startsWith('/') ? firstImage.path : '/' + firstImage.path;
                    } else if (firstImage.file_path) {
                        imageUrl = firstImage.file_path.startsWith('/') ? firstImage.file_path : '/' + firstImage.file_path;
                    } else if (firstImage.filename) {
                        imageUrl = '/storage/' + firstImage.filename;
                    }
                }

                const allImageUrls = item.images && item.images.length > 0 ?
                    item.images.map(img => {
                        if (img.path) {
                            return img.path.startsWith('/') ? img.path : '/' + img.path;
                        } else if (img.file_path) {
                            return img.file_path.startsWith('/') ? img.file_path : '/' + img.file_path;
                        } else if (img.filename) {
                            return '/storage/' + img.filename;
                        }
                        return '';
                    }).filter(url => url) : [];
                const allImageUrlsJson = JSON.stringify(allImageUrls).replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                const escapedImageUrl = imageUrl ? imageUrl.replace(/'/g, "\\'").replace(/"/g, '&quot;') : '';
                const escapedDescription = (item.description || 'Item image').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                const isLost = item.item_type === 'lost';
                const typeBadgeClass = isLost
                    ? 'bg-red-50 text-red-700 ring-1 ring-red-200'
                    : 'bg-green-50 text-green-700 ring-1 ring-green-200';
                const typeDot = isLost ? 'bg-red-500' : 'bg-green-500';
                const typeLabel = isLost ? 'Lost' : 'Found';

                const titleText = item.description && item.description.trim()
                    ? item.description.trim()
                    : (isLost ? 'Lost Item' : 'Found Item');
                const escapedTitle = String(titleText).replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                const locationText = (item.location && String(item.location).trim()) || 'Location not provided';
                const escapedLocation = String(locationText).replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                // Tags
                let tagsArray = [];
                if (item.tags) {
                    if (Array.isArray(item.tags)) {
                        tagsArray = item.tags;
                    } else if (typeof item.tags === 'string') {
                        try { tagsArray = JSON.parse(item.tags); }
                        catch (e) { tagsArray = item.tags.split(',').map(t => t.trim()).filter(Boolean); }
                    }
                }

                // Detected objects (deduped)
                let objectsArray = [];
                if (item.detected_objects) {
                    if (Array.isArray(item.detected_objects)) {
                        objectsArray = item.detected_objects;
                    } else if (typeof item.detected_objects === 'string') {
                        try { objectsArray = JSON.parse(item.detected_objects); }
                        catch (e) { objectsArray = []; }
                    }
                }
                const uniqueObjects = [];
                const seenNames = new Set();
                if (Array.isArray(objectsArray)) {
                    objectsArray.forEach(obj => {
                        const name = ((obj && typeof obj === 'object') ? obj.name : obj) || '';
                        if (name && !seenNames.has(name.toLowerCase())) {
                            seenNames.add(name.toLowerCase());
                            uniqueObjects.push(obj);
                        }
                    });
                }

                const tagPills = tagsArray.slice(0, 3).map(tag => {
                    const t = String(tag).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    return `<span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded-full text-[11px] font-medium">${t}</span>`;
                }).join('');
                const tagMore = tagsArray.length > 3
                    ? `<span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-[11px] font-medium">+${tagsArray.length - 3}</span>`
                    : '';

                const objectPills = uniqueObjects.slice(0, 3).map(obj => {
                    const name = ((obj && typeof obj === 'object') ? obj.name : obj) || '';
                    const score = (obj && typeof obj === 'object' && obj.score)
                        ? (obj.score * 100).toFixed(0) + '%' : '';
                    const escapedObj = String(name).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                    const title = 'Detected by Vision API' + (score ? ' (' + score + ' confidence)' : '');
                    return `<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded-full text-[11px] font-medium ring-1 ring-blue-100" title="${title}"><i class="fas fa-eye text-[9px]"></i>${escapedObj}</span>`;
                }).join('');
                const objectMore = uniqueObjects.length > 3
                    ? `<span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full text-[11px] font-medium ring-1 ring-blue-100">+${uniqueObjects.length - 3}</span>`
                    : '';

                // Pills row is always rendered (even when empty) so every
                // card has the exact same vertical rhythm. We cap it to a
                // single visual row via fixed height + overflow-hidden so
                // tag-heavy items don't make their card taller than the rest.
                const pillsContent = `${tagPills}${tagMore}${objectPills}${objectMore}`;
                const pillsRow = `
                    <div class="mt-2 h-7 overflow-hidden flex flex-nowrap items-center gap-1.5">
                        ${pillsContent}
                    </div>`;

                const dateLabel = formatRelativeDate(item.created_at);
                const imagesCountBadge = (item.images && item.images.length > 1)
                    ? `<span class="absolute top-2 right-2 inline-flex items-center gap-1 bg-black/55 backdrop-blur-sm text-white text-[10px] font-medium px-2 py-0.5 rounded-full"><i class="fas fa-images text-[9px]"></i>${item.images.length}</span>`
                    : '';

                const mediaBlock = imageUrl ? `
                    <button type="button"
                            onclick="openImageModal('${escapedImageUrl}', ${allImageUrlsJson})"
                            class="group relative w-full h-44 sm:h-48 bg-gray-100 overflow-hidden block focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500">
                        <img src="${imageUrl}"
                             alt="${escapedDescription}"
                             class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-300"
                             loading="lazy"
                             onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'400\\' height=\\'300\\'%3E%3Crect fill=\\'%23e5e7eb\\' width=\\'400\\' height=\\'300\\'/%3E%3Ctext fill=\\'%239ca3af\\' font-family=\\'sans-serif\\' font-size=\\'20\\' x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\' dy=\\'.3em\\'%3EImage not available%3C/text%3E%3C/svg%3E'; this.parentElement.classList.add('flex','items-center','justify-center');">
                        <div class="absolute top-2 left-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold ${typeBadgeClass} shadow-sm">
                            <span class="w-1.5 h-1.5 rounded-full ${typeDot}"></span>
                            ${typeLabel}
                        </div>
                        ${imagesCountBadge}
                    </button>
                ` : `
                    <div class="relative w-full h-44 sm:h-48 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-image text-gray-400 text-3xl mb-1"></i>
                            <p class="text-xs text-gray-500">No image</p>
                        </div>
                        <div class="absolute top-2 left-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold ${typeBadgeClass} shadow-sm">
                            <span class="w-1.5 h-1.5 rounded-full ${typeDot}"></span>
                            ${typeLabel}
                        </div>
                    </div>
                `;

                return `
                <article class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md hover:border-gray-300 transition-all duration-200 flex flex-col h-full">
                    ${mediaBlock}

                    <div class="p-4 sm:p-5 flex flex-col flex-1 min-h-0">
                        <h3 class="text-[15px] sm:text-base font-semibold text-gray-900 leading-snug line-clamp-2 min-h-[2.6em]" title="${escapedTitle}">
                            ${escapedTitle}
                        </h3>

                        <p class="mt-1.5 text-xs sm:text-sm text-gray-500 flex items-start gap-1.5 line-clamp-1">
                            <i class="fas fa-map-marker-alt mt-0.5 text-gray-400 shrink-0"></i>
                            <span class="truncate" title="${escapedLocation}">${escapedLocation}</span>
                        </p>

                        ${pillsRow}

                        <div class="mt-3 text-[11px] text-gray-400 flex items-center gap-1.5">
                            <i class="far fa-clock"></i>
                            <span title="${new Date(item.created_at).toLocaleString()}">${dateLabel}</span>
                        </div>

                        <div class="mt-auto pt-3 border-t border-gray-100 flex items-center gap-2">
                            <button type="button" onclick="viewItemDetails('${item.upload_id}')"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-purple-50 text-purple-700 hover:bg-purple-100 active:bg-purple-200 rounded-lg text-xs sm:text-sm font-medium transition-colors"
                                    aria-label="View details">
                                <i class="fas fa-eye text-xs"></i>
                                <span>Details</span>
                            </button>
                            <button type="button" onclick="editItem('${item.upload_id}')"
                                    class="inline-flex items-center justify-center px-3 py-2 bg-gray-50 text-gray-700 hover:bg-gray-100 active:bg-gray-200 rounded-lg text-xs sm:text-sm font-medium transition-colors"
                                    aria-label="Edit item" title="Edit">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                            <button type="button" onclick="deleteItem('${item.upload_id}')"
                                    class="inline-flex items-center justify-center px-3 py-2 bg-red-50 text-red-700 hover:bg-red-100 active:bg-red-200 rounded-lg text-xs sm:text-sm font-medium transition-colors"
                                    aria-label="Delete item" title="Delete">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                </article>`;
            }).join('')}
        </div>
        `;

        // Initialize maps for all items
        setTimeout(() => {
            items.forEach(item => {
                if (item.location) {
                    const mapId = 'map-' + item.upload_id;
                    initializeMap(mapId, item.location);
                }
            });
        }, 100);
    } catch (error) {
        console.error('Error displaying items:', error);
        showErrorState('Error displaying items: ' + error.message);
    }
}

function showErrorState(message) {
    const itemsContainer = document.getElementById('user-items-list');
    if (!itemsContainer) return;

    itemsContainer.innerHTML = `
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Error Loading Items</h3>
            <p class="text-gray-500 mb-4">${message}</p>
            <button onclick="loadItems()" class="bg-purple-primary text-white px-6 py-2 rounded-lg hover:bg-purple-600 transition-colors">
                <i class="fas fa-refresh mr-2"></i>
                Try Again
            </button>
        </div>
    `;
}

// Delete item function
async function deleteItem(uploadId) {
    if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
        return;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            showToast('CSRF token not found. Please refresh the page.', 'error');
            return;
        }

        console.log('Deleting item with uploadId:', uploadId);
        console.log('CSRF Token:', csrfToken);

        const response = await fetch(`/api/items/${encodeURIComponent(uploadId)}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });

        console.log('Delete response status:', response.status);
        console.log('Delete response headers:', response.headers);

        // Check if response is OK before trying to parse JSON
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Delete error response:', errorText);
            
            if (response.status === 404) {
                showToast('Item not found or you do not have permission to delete it.', 'error');
            } else if (response.status === 419) {
                showToast('Session expired. Please refresh the page and try again.', 'error');
            } else if (response.status === 500) {
                showToast('Server error. Please try again later.', 'error');
            } else {
                showToast(`Error deleting item (Status: ${response.status}). Please try again.`, 'error');
            }
            return;
        }

        const data = await response.json();
        console.log('Delete response data:', data);
        
        if (data.success) {
            showToast('Item deleted successfully!', 'success');
            loadItems(); // Reload the items list
        } else {
            showToast(data.message || data.error || 'Error deleting item. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showToast('Network error. Please check your connection and try again.', 'error');
    }
}

// Load items when the page loads
document.addEventListener('DOMContentLoaded', function() {
    loadItems();
});

// Loading Animation Functions
function showLoadingAnimation() {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'loading-overlay';
    loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    loadingOverlay.innerHTML = `
        <div class="bg-white rounded-lg p-8 flex flex-col items-center space-y-4">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
            <div class="text-lg font-medium text-gray-700">Processing your items...</div>
            <div class="text-sm text-gray-500">Please wait while we upload and analyze your images</div>
        </div>
    `;
    document.body.appendChild(loadingOverlay);

    // Disable form elements
    const form = document.getElementById('item-upload-form');
    const inputs = form.querySelectorAll('input, button, textarea, select');
    inputs.forEach(input => {
        input.disabled = true;
    });
}

function hideLoadingAnimation() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.remove();
    }

    // Re-enable form elements
    const form = document.getElementById('item-upload-form');
    const inputs = form.querySelectorAll('input, button, textarea, select');
    inputs.forEach(input => {
        input.disabled = false;
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
    toast.className = `fixed left-3 right-3 top-3 sm:left-auto sm:right-4 sm:top-4 sm:max-w-md z-[1000] px-4 sm:px-6 py-3 sm:py-4 rounded-xl shadow-2xl text-white font-medium transition-all duration-300 transform -translate-y-4 opacity-0 flex items-start gap-3`;

    let icon = 'fa-info-circle';
    if (type === 'success') {
        toast.classList.add('bg-green-600');
        icon = 'fa-check-circle';
    } else if (type === 'error') {
        toast.classList.add('bg-red-600');
        icon = 'fa-exclamation-circle';
    } else {
        toast.classList.add('bg-blue-600');
    }

    toast.innerHTML = `
        <i class="fas ${icon} text-xl flex-shrink-0 mt-0.5"></i>
        <span class="text-sm sm:text-base leading-snug">${message}</span>
    `;

    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('-translate-y-4', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    });

    const duration = type === 'success' ? 5000 : 4500;
    setTimeout(() => {
        toast.classList.add('opacity-0', '-translate-y-4');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Carousel functions
const carouselStates = {};

function initializeCarousel(carouselId, totalSlides) {
    carouselStates[carouselId] = {
        currentSlide: 0,
        totalSlides: totalSlides
    };
    updateCarouselPosition(carouselId);
    updateCarouselDots(carouselId);
    updateCarouselCounter(carouselId);
}

function nextSlide(carouselId) {
    const state = carouselStates[carouselId];
    if (state.currentSlide < state.totalSlides - 1) {
        state.currentSlide++;
        updateCarouselPosition(carouselId);
        updateCarouselDots(carouselId);
        updateCarouselCounter(carouselId);
    }
}

function previousSlide(carouselId) {
    const state = carouselStates[carouselId];
    if (state.currentSlide > 0) {
        state.currentSlide--;
        updateCarouselPosition(carouselId);
        updateCarouselDots(carouselId);
        updateCarouselCounter(carouselId);
    }
}

function goToSlide(carouselId, slideIndex) {
    const state = carouselStates[carouselId];
    state.currentSlide = slideIndex;
    updateCarouselPosition(carouselId);
    updateCarouselDots(carouselId);
    updateCarouselCounter(carouselId);
}

function updateCarouselPosition(carouselId) {
    const state = carouselStates[carouselId];
    const track = document.getElementById(`carousel-${carouselId}`);
    if (track) {
        track.style.transform = `translateX(-${state.currentSlide * 100}%)`;
    }
}

function updateCarouselDots(carouselId) {
    const state = carouselStates[carouselId];
    for (let i = 0; i < state.totalSlides; i++) {
        const dot = document.getElementById(`dot-${carouselId}-${i}`);
        if (dot) {
            dot.className = i === state.currentSlide
                ? 'carousel-dot w-2 h-2 rounded-full bg-purple-600 transition-colors'
                : 'carousel-dot w-2 h-2 rounded-full bg-gray-300 transition-colors';
        }
    }
}

function updateCarouselCounter(carouselId) {
    const state = carouselStates[carouselId];
    const counter = document.getElementById(`counter-${carouselId}`);
    if (counter) {
        counter.textContent = `${state.currentSlide + 1} / ${state.totalSlides}`;
    }
}

// Image modal state
let modalImages = [];
let currentModalImageIndex = 0;

function openImageModal(imageUrl, images = []) {
    const modal = document.getElementById('image-modal');
    const modalImage = document.getElementById('modal-image');
    const navDiv = document.getElementById('modal-image-nav');
    const counterDiv = document.getElementById('modal-image-counter');
    const currentNum = document.getElementById('current-image-num');
    const totalNum = document.getElementById('total-images-num');
    
    if (!modal || !modalImage) return;
    
    // Set images array - handle both string and array inputs
    if (Array.isArray(images) && images.length > 0) {
        modalImages = images;
    } else if (typeof images === 'string') {
        try {
            modalImages = JSON.parse(images.replace(/&quot;/g, '"'));
        } catch (e) {
            modalImages = [imageUrl];
        }
    } else {
        modalImages = [imageUrl];
    }
    
    // Find current image index
    currentModalImageIndex = modalImages.findIndex(img => img === imageUrl);
    if (currentModalImageIndex === -1) {
        currentModalImageIndex = 0;
    }
    
    // Display current image
    modalImage.src = modalImages[currentModalImageIndex];
    
    // Show/hide navigation
    if (modalImages.length > 1) {
        if (navDiv) navDiv.classList.remove('hidden');
        if (counterDiv) counterDiv.classList.remove('hidden');
        if (currentNum) currentNum.textContent = currentModalImageIndex + 1;
        if (totalNum) totalNum.textContent = modalImages.length;
    } else {
        if (navDiv) navDiv.classList.add('hidden');
        if (counterDiv) counterDiv.classList.add('hidden');
    }
    
    modal.classList.remove('hidden');
}

function changeModalImage(direction) {
    if (modalImages.length <= 1) return;
    
    currentModalImageIndex += direction;
    
    if (currentModalImageIndex < 0) {
        currentModalImageIndex = modalImages.length - 1;
    } else if (currentModalImageIndex >= modalImages.length) {
        currentModalImageIndex = 0;
    }
    
    const modalImage = document.getElementById('modal-image');
    const currentNum = document.getElementById('current-image-num');
    
    if (modalImage) {
        modalImage.src = modalImages[currentModalImageIndex];
    }
    if (currentNum) {
        currentNum.textContent = currentModalImageIndex + 1;
    }
}

function viewImage(imagePath) {
    openImageModal(imagePath, [imagePath]);
}

function closeImageModal() {
    const modal = document.getElementById('image-modal');
    if (modal) {
        modal.classList.add('hidden');
        modalImages = [];
        currentModalImageIndex = 0;
    }
}

// Item details function
function viewItemDetails(uploadId) {
    const item = window.userItems.find(item => item.upload_id === uploadId);
    if (!item) return;

    // Build image URLs safely without nested template literals
    let firstImageUrl = '';
    const allImageUrls = [];
    if (item.images && item.images.length > 0) {
        item.images.forEach(img => {
            let imgUrl = '';
            if (img.path) {
                imgUrl = img.path.startsWith('/') ? img.path : '/' + img.path;
            } else if (img.file_path) {
                imgUrl = img.file_path.startsWith('/') ? img.file_path : '/' + img.file_path;
            } else if (img.filename) {
                imgUrl = '/storage/' + img.filename;
            }
            if (imgUrl) {
                allImageUrls.push(imgUrl);
                if (!firstImageUrl) firstImageUrl = imgUrl;
            }
        });
    }
    
    const escapedFirstImageUrl = firstImageUrl.replace(/'/g, "\\'").replace(/"/g, '&quot;');
    const allImageUrlsJson = JSON.stringify(allImageUrls).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    const escapedDescription = (item.description || 'Item image').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    const content = `
        <div class="space-y-4">
            ${firstImageUrl ? '<div><h4 class="font-semibold text-gray-900 mb-2">Image</h4><div class="relative w-full h-64 bg-gray-100 rounded-lg overflow-hidden"><img src="' + firstImageUrl + '" alt="' + escapedDescription + '" class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity" onclick="openImageModal(\'' + escapedFirstImageUrl + '\', ' + allImageUrlsJson + ')">' + (item.images.length > 1 ? '<div class="absolute bottom-2 right-2 bg-black bg-opacity-60 text-white text-xs px-2 py-1 rounded"><i class="fas fa-images mr-1"></i>' + item.images.length + ' images</div>' : '') + '</div></div>' : ''}
            <div>
                <h4 class="font-semibold text-gray-900">Item Type</h4>
                <p class="text-gray-600">${item.item_type === 'lost' ? 'Lost Item' : 'Found Item'}</p>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900">Description</h4>
                <p class="text-gray-600">${item.description || 'No description provided'}</p>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900 mb-2">Location</h4>
                <p class="text-gray-600 mb-2">${item.location || 'No location specified'}</p>
                ${item.location ? '<div id="map-detail-' + item.upload_id + '" class="w-full h-64 rounded-lg border border-gray-200 mt-2" style="z-index: 1;"></div>' : ''}
            </div>
            <div>
                <h4 class="font-semibold text-gray-900">Date Posted</h4>
                <p class="text-gray-600">${new Date(item.created_at).toLocaleDateString()}</p>
            </div>
            ${(() => {
                let tagsArray = [];
                if (item.tags) {
                    if (Array.isArray(item.tags)) {
                        tagsArray = item.tags;
                    } else if (typeof item.tags === 'string') {
                        try {
                            tagsArray = JSON.parse(item.tags);
                        } catch (e) {
                            tagsArray = item.tags.split(',').map(t => t.trim()).filter(t => t);
                        }
                    }
                }
                
                let tagsHtml = '';
                if (tagsArray.length > 0) {
                    const tagsDisplay = tagsArray.map(tag => {
                        const escapedTag = String(tag).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        return '<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">' + escapedTag + '</span>';
                    }).join('');
                    tagsHtml = '<div class="mb-4"><h4 class="font-semibold text-gray-900 mb-2">Tags</h4><div class="flex flex-wrap gap-2">' + tagsDisplay + '</div></div>';
                }
                
                // Detected Objects - show below Tags
                let objectsArray = [];
                if (item.detected_objects) {
                    if (Array.isArray(item.detected_objects)) {
                        objectsArray = item.detected_objects;
                    } else if (typeof item.detected_objects === 'string') {
                        try {
                            objectsArray = JSON.parse(item.detected_objects);
                        } catch (e) {
                            objectsArray = [];
                        }
                    }
                }
                
                // Get unique objects (by name) and limit to top 5
                const uniqueObjects = [];
                const seenNames = new Set();
                if (Array.isArray(objectsArray)) {
                    objectsArray.forEach(obj => {
                        const objName = (obj && typeof obj === 'object' ? obj.name : obj) || '';
                        if (objName && !seenNames.has(objName.toLowerCase())) {
                            seenNames.add(objName.toLowerCase());
                            uniqueObjects.push(obj);
                        }
                    });
                }
                
                let objectsHtml = '';
                if (uniqueObjects.length > 0) {
                    const top5Objects = uniqueObjects.slice(0, 5);
                    const objectsDisplay = top5Objects.map(obj => {
                        const objName = (obj && typeof obj === 'object' ? obj.name : obj) || '';
                        const score = (obj && typeof obj === 'object' && obj.score) ? (obj.score * 100).toFixed(0) : '';
                        const escapedObj = String(objName).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        return '<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium" title="Detected by Google Vision API' + (score ? ' (' + score + '% confidence)' : '') + '"><i class="fas fa-eye mr-1"></i>' + escapedObj + '</span>';
                    }).join('');
                    const moreHtml = uniqueObjects.length > 5 ? '<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">+' + (uniqueObjects.length - 5) + ' more</span>' : '';
                    objectsHtml = '<div class="mb-4"><h4 class="font-semibold text-gray-900 mb-2 flex items-center"><i class="fas fa-cube mr-1 text-blue-600"></i>Detected Objects</h4><div class="flex flex-wrap gap-2">' + objectsDisplay + moreHtml + '</div></div>';
                }
                
                return tagsHtml + objectsHtml;
            })()}
        </div>
    `;

    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-full overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Item Details</h3>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-4 max-h-96 overflow-y-auto">
                ${content}
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    // Initialize map in modal after it's added to DOM
    setTimeout(() => {
        if (item.location) {
            const mapId = 'map-detail-' + item.upload_id;
            initializeMap(mapId, item.location);
        }
    }, 100);
}

// Edit item function
function editItem(uploadId) {
    const item = window.userItems.find(item => item.upload_id === uploadId);
    if (!item) {
        showToast('Item not found', 'error');
        return;
    }

    // Populate edit form
    document.getElementById('edit-upload-id').value = uploadId;
    const editCityInput = document.getElementById('edit-city');
    const editProvinceInput = document.getElementById('edit-province');
    if (editCityInput) {
        editCityInput.value = item.city || '';
    }
    if (editProvinceInput) {
        editProvinceInput.value = item.province || '';
    }
    document.getElementById('edit-location').value = item.location || '';
    
    // If location exists, try to show it on map
    if (item.location) {
        setTimeout(() => {
            const mapId = 'edit-location-map';
            const mapElement = document.getElementById(mapId);
            if (mapElement && !locationMaps[mapId]) {
                initializeLocationMap(mapId, 'edit-location');
            }
            if (locationMaps[mapId]) {
                geocodeAndShowOnMap(item.location, locationMaps[mapId], 'edit-location');
            }
        }, 100);
    }
    document.getElementById('edit-description').value = item.description || '';
    // Load tags into edit form
    if (item.tags) {
        const tagsArray = Array.isArray(item.tags) ? item.tags : (typeof item.tags === 'string' ? item.tags.split(',').map(t => t.trim()) : []);
        loadTagsIntoEditForm(tagsArray);
    } else {
        loadTagsIntoEditForm([]);
    }
    
    // Set item type radio button
    if (item.item_type === 'lost') {
        document.getElementById('edit-item-type-lost').checked = true;
    } else {
        document.getElementById('edit-item-type-found').checked = true;
    }

    // Display existing images
    const existingImagesContainer = document.getElementById('edit-existing-images');
    existingImagesContainer.innerHTML = '';
    
    const removedImages = [];
    
    if (item.images && item.images.length > 0) {
        item.images.forEach((image, index) => {
            const imageDiv = document.createElement('div');
            imageDiv.className = 'relative group';
            imageDiv.innerHTML = `
                <div class="relative">
                    <img src="${image.path}" alt="${image.original_name}" class="w-full h-32 object-cover rounded-lg border-2 border-gray-200 cursor-pointer hover:border-red-300 transition-colors" onclick="toggleRemoveImage('${image.filename}', this)">
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all rounded-lg flex items-center justify-center">
                        <span class="text-white text-xs font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-trash mr-1"></i>Click to remove
                        </span>
                    </div>
                    <div class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold hidden remove-indicator">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
            `;
            existingImagesContainer.appendChild(imageDiv);
        });
    } else {
        existingImagesContainer.innerHTML = '<p class="text-sm text-gray-500 col-span-4">No images available</p>';
    }

    // Reset remove images input
    document.getElementById('edit-remove-images').value = '';
    document.getElementById('edit-new-images').value = '';
    
    // Reset edit form image previews
    const editPreviewContainer = document.getElementById('edit-image-preview-container');
    if (editPreviewContainer) {
        editPreviewContainer.innerHTML = '';
        editPreviewContainer.classList.add('hidden');
    }

    // Initialize edit form drag and drop
    initEditFormDragDrop();

    // Show modal
    document.getElementById('edit-item-modal').classList.remove('hidden');
}

// Edit form drag and drop
let editSelectedFiles = [];

function initEditFormDragDrop() {
    const editDropZone = document.getElementById('edit-drop-zone');
    const editImageInput = document.getElementById('edit-new-images');
    const editPreviewContainer = document.getElementById('edit-image-preview-container');

    if (!editDropZone || !editImageInput) return;

    // Reset selected files
    editSelectedFiles = [];

    // Click to upload
    editDropZone.addEventListener('click', function(e) {
        if (e.target.closest('button')) return;
        editImageInput.click();
    });

    // File input change
    editImageInput.addEventListener('change', function(e) {
        handleEditFiles(e.target.files);
    });

    // Drag and drop events
    editDropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        editDropZone.classList.add('border-purple-500', 'bg-purple-100');
    });

    editDropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        editDropZone.classList.remove('border-purple-500', 'bg-purple-100');
    });

    editDropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        editDropZone.classList.remove('border-purple-500', 'bg-purple-100');
        
        const files = e.dataTransfer.files;
        handleEditFiles(files);
    });

    function handleEditFiles(files) {
        const maxFiles = 5;
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        // Filter valid image files
        const validFiles = Array.from(files).filter(file => {
            if (!file.type.startsWith('image/')) {
                showToast('Only image files are allowed', 'error');
                return false;
            }
            if (file.size > maxSize) {
                showToast(`File ${file.name} is too large. Maximum size is 10MB`, 'error');
                return false;
            }
            return true;
        });

        // Check total file count
        const totalFiles = editSelectedFiles.length + validFiles.length;
        if (totalFiles > maxFiles) {
            showToast(`Maximum ${maxFiles} images allowed. You selected ${totalFiles} files.`, 'error');
            validFiles.splice(maxFiles - editSelectedFiles.length);
        }

        // Add to selected files
        validFiles.forEach(file => {
            if (!editSelectedFiles.find(f => f.name === file.name && f.size === file.size)) {
                editSelectedFiles.push(file);
            }
        });

        // Update file input
        const dataTransfer = new DataTransfer();
        editSelectedFiles.forEach(file => dataTransfer.items.add(file));
        editImageInput.files = dataTransfer.files;

        // Show previews
        displayEditImagePreviews();
    }

    function displayEditImagePreviews() {
        if (editSelectedFiles.length === 0) {
            editPreviewContainer.classList.add('hidden');
            return;
        }

        editPreviewContainer.classList.remove('hidden');
        editPreviewContainer.innerHTML = '';

        editSelectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'relative group';
                previewDiv.innerHTML = `
                    <div class="relative aspect-square rounded-lg overflow-hidden border-2 border-gray-200 hover:border-purple-400 transition-colors" style="background-color: #f3f4f6;">
                        <img src="${e.target.result}" 
                             alt="${file.name}" 
                             class="w-full h-full object-cover"
                             style="display: block; background-color: transparent; position: relative; z-index: 1; opacity: 1;"
                             onerror="console.error('Preview image failed to load:', this.src); this.style.display='none';"
                             onload="console.log('Preview image loaded:', this.src); this.style.backgroundColor='transparent'; this.style.opacity='1';">
                        <div class="absolute inset-0 transition-all flex items-center justify-center pointer-events-none" style="background-color: transparent;">
                            <button onclick="removeEditImage(${index})" class="opacity-0 group-hover:opacity-100 bg-red-500 text-white px-3 py-1 rounded-lg text-sm font-medium transition-opacity hover:bg-red-600 pointer-events-auto z-10">
                                <i class="fas fa-trash mr-1"></i>Remove
                            </button>
                        </div>
                        <div class="absolute top-2 right-2 bg-white rounded-full p-1 shadow-md">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-600 truncate" title="${file.name}">${file.name}</p>
                    <p class="text-xs text-gray-500">${formatFileSize(file.size)}</p>
                `;
                editPreviewContainer.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        });
    }

    window.removeEditImage = function(index) {
        editSelectedFiles.splice(index, 1);
        
        // Update file input
        const dataTransfer = new DataTransfer();
        editSelectedFiles.forEach(file => dataTransfer.items.add(file));
        editImageInput.files = dataTransfer.files;

        displayEditImagePreviews();
    };
}

// Toggle remove image
function toggleRemoveImage(filename, element) {
    const removeInput = document.getElementById('edit-remove-images');
    let removedImages = removeInput.value ? removeInput.value.split(',').filter(f => f) : [];
    const existingImagesContainer = document.getElementById('edit-existing-images');
    const totalImages = existingImagesContainer.querySelectorAll('[data-filename]').length;
    const newImagesInput = document.getElementById('edit-new-images');
    const newImagesCount = newImagesInput.files ? newImagesInput.files.length : 0;
    const remainingAfterRemove = totalImages - removedImages.length - (removedImages.includes(filename) ? 0 : 1);
    const totalAfterUpdate = remainingAfterRemove + newImagesCount;
    
    if (removedImages.includes(filename)) {
        // Restore image
        removedImages = removedImages.filter(f => f !== filename);
        element.classList.remove('border-red-500');
        const indicator = element.closest('.relative').querySelector(`.remove-indicator-${filename}`);
        if (indicator) indicator.classList.add('hidden');
    } else {
        // Check if removing this image would leave no images
        if (totalAfterUpdate < 1 && newImagesCount === 0) {
            showToast('At least one image is required. Please add a new image before removing all existing ones.', 'error');
            return;
        }
        
        // Mark for removal
        removedImages.push(filename);
        element.classList.add('border-red-500');
        const indicator = element.closest('.relative').querySelector(`.remove-indicator-${filename}`);
        if (indicator) indicator.classList.remove('hidden');
    }
    
    removeInput.value = removedImages.join(',');
}

// Close edit modal
function closeEditModal() {
    document.getElementById('edit-item-modal').classList.add('hidden');
    document.getElementById('edit-item-form').reset();
    document.getElementById('edit-existing-images').innerHTML = '';
    document.getElementById('edit-remove-images').value = '';
    
    // Reset edit form image previews
    const editPreviewContainer = document.getElementById('edit-image-preview-container');
    if (editPreviewContainer) {
        editPreviewContainer.innerHTML = '';
        editPreviewContainer.classList.add('hidden');
    }
    
    // Reset selected files
    editSelectedFiles = [];
}

// Handle edit form submission
document.getElementById('edit-item-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Prevent double submission
    if (this.dataset.submitting === 'true') {
        return;
    }
    this.dataset.submitting = 'true';

    const uploadId = document.getElementById('edit-upload-id').value;
    const submitButton = this.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

    // Get field visibility settings
    const enableProvinceField = @json($enableProvinceField ?? true);
    const provinceFieldRequired = @json($provinceFieldRequired ?? true);
    const enableCityField = @json($enableCityField ?? true);
    const cityFieldRequired = @json($cityFieldRequired ?? true);

    // Get form values and validate
    const itemType = document.querySelector('input[name="item_type"]:checked');
    const cityInput = document.getElementById('edit-city');
    const provinceInput = document.getElementById('edit-province');
    const locationInput = document.getElementById('edit-location');
    const descriptionInput = document.getElementById('edit-description');
    const tagsInput = document.getElementById('edit-tags');
    
    const city = cityInput ? cityInput.value.trim() : '';
    const province = provinceInput ? provinceInput.value.trim() : '';
    const location = locationInput ? locationInput.value.trim() : '';
    const description = descriptionInput ? descriptionInput.value.trim() : '';
    const tags = tagsInput ? tagsInput.value.trim() : '';

    // Client-side validation - city only if enabled
    if (enableCityField) {
        if (cityFieldRequired && (!city || city === '')) {
            showToast('Please enter a city', 'error');
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            this.dataset.submitting = 'false';
            if (cityInput) cityInput.focus();
            return;
        }

        // Validate city is in enabled cities list (only if city is provided)
        if (city) {
            const enabledCities = @json($enabledCities ?? []);
            if (enabledCities.length > 0) {
                const isValidCity = enabledCities.some(c => c.toLowerCase() === city.toLowerCase());
                if (!isValidCity) {
                    const cityErrorMessage = document.getElementById('edit-city-error-message');
                    if (cityErrorMessage) {
                        cityErrorMessage.classList.remove('hidden');
                    }
                    showToast('Please select a valid city from the suggestions', 'error');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                    this.dataset.submitting = 'false';
                    if (cityInput) cityInput.focus();
                    return;
                }
            }
        }
    }

    // Client-side validation - province only if enabled
    if (enableProvinceField) {
        if (provinceFieldRequired && (!province || province === '')) {
            showToast('Please enter a province', 'error');
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            this.dataset.submitting = 'false';
            if (provinceInput) provinceInput.focus();
            return;
        }

        // Validate province is in enabled provinces list (only if province is provided)
        if (province) {
            const enabledProvinces = @json($enabledProvinces ?? []);
            if (enabledProvinces.length > 0) {
                const isValidProvince = enabledProvinces.some(p => p.toLowerCase() === province.toLowerCase());
                if (!isValidProvince) {
                    const provinceErrorMessage = document.getElementById('edit-province-error-message');
                    if (provinceErrorMessage) {
                        provinceErrorMessage.classList.remove('hidden');
                    }
                    showToast('Please select a valid province from the suggestions', 'error');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                    this.dataset.submitting = 'false';
                    if (provinceInput) provinceInput.focus();
                    return;
                }
            }
        }
    }

    if (!location || location === '') {
        showToast('Location is required', 'error');
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
        this.dataset.submitting = 'false';
        if (locationInput) locationInput.focus();
        return;
    }

    if (!description || description === '') {
        showToast('Description is required', 'error');
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
        this.dataset.submitting = 'false';
        if (descriptionInput) descriptionInput.focus();
        return;
    }

    // Create FormData manually to avoid conflicts
    const formData = new FormData();
    
    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    formData.append('_token', csrfToken);
    formData.append('_method', 'PUT'); // Laravel method spoofing for PUT request
    
    // Get item type
    if (itemType) {
        formData.append('item_type', itemType.value);
    }

    // Add fields - only include city/province if enabled
    if (enableCityField && city) {
        formData.append('city', city);
    }
    if (enableProvinceField && province) {
        formData.append('province', province);
    }
    formData.append('location', location);
    formData.append('description', description);

    // Get tags from hidden input (always append, even if empty)
    const editTagsInput = document.getElementById('edit-tags');
    if (editTagsInput && editTagsInput.value) {
        try {
            const tagsArray = JSON.parse(editTagsInput.value);
            formData.append('tags', JSON.stringify(tagsArray));
        } catch (e) {
            console.error('Error parsing tags:', e);
            // If parsing fails, try to send as comma-separated string
            const tagsString = editTagsInput.value.trim();
            if (tagsString) {
                formData.append('tags', tagsString);
            }
        }
    } else {
        // No tags selected, send empty array
        formData.append('tags', JSON.stringify([]));
    }

    // Get removed images
    const removeImages = document.getElementById('edit-remove-images').value;
    if (removeImages) {
        const removedArray = removeImages.split(',').filter(f => f);
        removedArray.forEach(filename => {
            formData.append('remove_images[]', filename);
        });
    }

    // Get new images
    const newImages = document.getElementById('edit-new-images').files;
    if (newImages && newImages.length > 0) {
        for (let file of newImages) {
            formData.append('images[]', file);
        }
    }

    // Debug: Log what we're sending
    console.log('Sending update request:', {
        uploadId: uploadId,
        location: location,
        description: description,
        itemType: itemType ? itemType.value : 'none',
        tags: tags,
        formDataKeys: Array.from(formData.keys())
    });
    
    // Log FormData contents for debugging
    for (let pair of formData.entries()) {
        console.log('FormData:', pair[0], '=', pair[1]);
    }

    try {
        // Use POST with method spoofing since PUT might have issues with FormData
        const response = await fetch(`/api/items/${uploadId}`, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        // Log response for debugging
        console.log('Response status:', response.status);
        
        // Get response as text first to handle both success and error cases
        const responseText = await response.text();
        console.log('Response text:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Failed to parse JSON response:', e);
            showToast('Error updating item. Invalid response from server.', 'error');
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            this.dataset.submitting = 'false';
            return;
        }

        if (!response.ok || !data.success) {
            // Handle error response
            let errorMessage = data.message || data.error || 'Error updating item. Please try again.';
            if (data.errors) {
                const errorList = Object.values(data.errors).flat().join(', ');
                errorMessage = errorMessage + ' ' + errorList;
            }
            if (data.debug) {
                console.error('Debug info:', data.debug);
                errorMessage += ' (Check console for details)';
            }
            showToast(errorMessage, 'error');
            console.error('Update error:', data);
        } else {
            // Success
            showToast('Item updated successfully!', 'success');
            closeEditModal();
            loadItems(); // Reload the items list
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error updating item. Please try again.', 'error');
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
        this.dataset.submitting = 'false';
    }
});

// Map initialization function using Leaflet and OpenStreetMap
async function initializeMap(mapId, locationText) {
    const mapElement = document.getElementById(mapId);
    if (!mapElement || !locationText) return;

    try {
        // Geocode location using Nominatim (free OpenStreetMap geocoding)
        const geocodeUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(locationText)}&limit=1`;
        
        const response = await fetch(geocodeUrl, {
            headers: {
                'User-Agent': 'FindITFast Lost and Found App'
            }
        });
        
        const data = await response.json();
        
        if (data && data.length > 0) {
            const lat = parseFloat(data[0].lat);
            const lon = parseFloat(data[0].lon);
            
            // Initialize map
            const map = L.map(mapId).setView([lat, lon], 13);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            
            // Add marker with custom icon
            const icon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            
            L.marker([lat, lon], {icon: icon})
                .addTo(map)
                .bindPopup(`<strong>${locationText}</strong>`)
                .openPopup();
        } else {
            // If geocoding fails, show a default map centered on a general location
            // Default to Philippines center, adjust coordinates as needed
            const defaultLat = 14.5995;
            const defaultLon = 120.9842;
            
            const map = L.map(mapId).setView([defaultLat, defaultLon], 6);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            
            // Show message that location couldn't be found
            mapElement.innerHTML = `
                <div class="w-full h-full flex items-center justify-center bg-gray-100 rounded-lg">
                    <div class="text-center text-gray-500 p-4">
                        <i class="fas fa-map-marker-alt text-2xl mb-2"></i>
                        <p class="text-sm">Location: ${locationText}</p>
                        <p class="text-xs mt-1">Unable to pinpoint exact location</p>
                    </div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error initializing map:', error);
        // Show error message
        mapElement.innerHTML = `
            <div class="w-full h-full flex items-center justify-center bg-gray-100 rounded-lg">
                <div class="text-center text-gray-500 p-4">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <p class="text-sm">Unable to load map</p>
                    <p class="text-xs mt-1">Location: ${locationText}</p>
                </div>
            </div>
        `;
    }
}

// Location map instances
const locationMaps = {};

// Use current location
async function useCurrentLocation(inputId) {
    const locationInput = document.getElementById(inputId);
    const latInput = document.getElementById(`${inputId}-lat`);
    const lonInput = document.getElementById(`${inputId}-lon`);
    
    if (!navigator.geolocation) {
        showToast('Geolocation is not supported by your browser', 'error');
        return;
    }
    
    locationInput.disabled = true;
    locationInput.value = 'Getting your location...';
    
    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            
            // Store coordinates
            if (latInput) latInput.value = lat;
            if (lonInput) lonInput.value = lon;
            
            // Reverse geocode to get street + city + province and auto-fill all three fields.
            try {
                const geo = await reverseGeocode(lat, lon);
                fillLocationFields(inputId, geo);
                showToast('Location set successfully!', 'success');

                const mapId = inputId === 'location' ? 'location-map' : 'edit-location-map';
                showLocationMap(mapId, lat, lon, geo.street || geo.display);
            } catch (error) {
                console.error('Reverse geocoding error:', error);
                locationInput.value = `${lat}, ${lon}`;
                showToast('Location set, but could not get address', 'warning');
            }

            locationInput.disabled = false;
        },
        (error) => {
            locationInput.disabled = false;
            locationInput.value = '';
            let errorMsg = 'Unable to get your location. ';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg += 'Please allow location access.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg += 'Location information unavailable.';
                    break;
                case error.TIMEOUT:
                    errorMsg += 'Location request timed out.';
                    break;
            }
            showToast(errorMsg, 'error');
        }
    );
}

// Toggle location map
function toggleLocationMap(inputId) {
    const mapId = inputId === 'location' ? 'location-map' : 'edit-location-map';
    const mapElement = document.getElementById(mapId);
    const clearBtn = inputId === 'location' ? 'clear-location-btn' : 'clear-edit-location-btn';
    const clearBtnElement = document.getElementById(clearBtn);
    
    const regularAutocompleteId = inputId === 'location' ? 'location-autocomplete' : 'edit-location-autocomplete';
    const mapAutocompleteId = inputId === 'location' ? 'location-autocomplete-map' : 'edit-location-autocomplete-map';
    const regularAutocomplete = document.getElementById(regularAutocompleteId);
    const mapAutocomplete = document.getElementById(mapAutocompleteId);
    
    if (mapElement.style.display === 'none' || !mapElement.style.display) {
        mapElement.style.display = 'block';
        clearBtnElement.style.display = 'inline-block';
        
        // If autocomplete is showing, move it to map overlay
        if (regularAutocomplete && !regularAutocomplete.classList.contains('hidden')) {
            const suggestions = regularAutocomplete.innerHTML;
            if (suggestions && mapAutocomplete) {
                mapAutocomplete.innerHTML = suggestions;
                mapAutocomplete.classList.remove('hidden');
                regularAutocomplete.classList.add('hidden');
            }
        }
        
        // Initialize map if not already initialized
        if (!locationMaps[mapId]) {
            initializeLocationMap(mapId, inputId);
        }
    } else {
        mapElement.style.display = 'none';
        clearBtnElement.style.display = 'none';
        
        // If autocomplete is showing on map, move it back to input field
        if (mapAutocomplete && !mapAutocomplete.classList.contains('hidden')) {
            const suggestions = mapAutocomplete.innerHTML;
            if (suggestions && regularAutocomplete) {
                regularAutocomplete.innerHTML = suggestions;
                regularAutocomplete.classList.remove('hidden');
                mapAutocomplete.classList.add('hidden');
            }
        }
    }
}

// Clear location map
function clearLocationMap(inputId) {
    const mapId = inputId === 'location' ? 'location-map' : 'edit-location-map';
    const mapElement = document.getElementById(mapId);
    const locationInput = document.getElementById(inputId);
    const latInput = document.getElementById(`${inputId}-lat`);
    const lonInput = document.getElementById(`${inputId}-lon`);
    const clearBtn = inputId === 'location' ? 'clear-location-btn' : 'clear-edit-location-btn';
    const clearBtnElement = document.getElementById(clearBtn);
    
    // Clear inputs
    locationInput.value = '';
    if (latInput) latInput.value = '';
    if (lonInput) lonInput.value = '';
    
    // Remove map
    if (locationMaps[mapId]) {
        locationMaps[mapId].remove();
        delete locationMaps[mapId];
    }
    
    mapElement.style.display = 'none';
    clearBtnElement.style.display = 'none';
}

// Initialize location selection map
function initializeLocationMap(mapId, inputId) {
    const mapElement = document.getElementById(mapId);
    if (!mapElement) return;
    
    // Default center (adjust to your region)
    const defaultLat = 14.5995;
    const defaultLon = 120.9842;
    const defaultZoom = 13;
    
    // Initialize map
    const map = L.map(mapId).setView([defaultLat, defaultLon], defaultZoom);
    locationMaps[mapId] = map;
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    let marker = null;
    
    // Handle map click
    map.on('click', async function(e) {
        const lat = e.latlng.lat;
        const lon = e.latlng.lng;
        
        // Store coordinates
        const latInput = document.getElementById(`${inputId}-lat`);
        const lonInput = document.getElementById(`${inputId}-lon`);
        const locationInput = document.getElementById(inputId);
        
        if (latInput) latInput.value = lat;
        if (lonInput) lonInput.value = lon;
        
        // Remove existing marker
        if (marker) {
            map.removeLayer(marker);
        }
        
        // Add new marker
        const icon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
        
        marker = L.marker([lat, lon], {icon: icon, draggable: true}).addTo(map);
        
        // Reverse geocode to get street + city + province and auto-fill the form.
        locationInput.value = 'Getting address...';
        locationInput.disabled = true;

        try {
            const geo = await reverseGeocode(lat, lon);
            fillLocationFields(inputId, geo);
            marker.bindPopup(`<strong>${geo.street || geo.display}</strong><br><span class="text-xs">${geo.city || ''}${geo.city && geo.province ? ', ' : ''}${geo.province || ''}</span>`).openPopup();
            showToast('Location pinned!', 'success');
        } catch (error) {
            console.error('Reverse geocoding error:', error);
            locationInput.value = `${lat}, ${lon}`;
            marker.bindPopup(`<strong>${lat.toFixed(6)}, ${lon.toFixed(6)}</strong>`).openPopup();
            showToast('Location pinned (address lookup failed)', 'warning');
        }

        locationInput.disabled = false;

        // Handle marker drag — same auto-fill behaviour as click.
        marker.on('dragend', async function(e) {
            const newLat = e.target.getLatLng().lat;
            const newLon = e.target.getLatLng().lng;

            if (latInput) latInput.value = newLat;
            if (lonInput) lonInput.value = newLon;

            locationInput.value = 'Getting address...';
            locationInput.disabled = true;

            try {
                const geo = await reverseGeocode(newLat, newLon);
                fillLocationFields(inputId, geo);
                marker.setPopupContent(`<strong>${geo.street || geo.display}</strong><br><span class="text-xs">${geo.city || ''}${geo.city && geo.province ? ', ' : ''}${geo.province || ''}</span>`).openPopup();
            } catch (error) {
                console.error('Reverse geocoding error:', error);
                locationInput.value = `${newLat}, ${newLon}`;
                marker.setPopupContent(`<strong>${newLat.toFixed(6)}, ${newLon.toFixed(6)}</strong>`).openPopup();
            }

            locationInput.disabled = false;
        });
    });
    
    // Don't auto-geocode existing location text - let user select from suggestions
    // Only pin when user explicitly selects a suggestion or clicks on map
}

// Show location on map with marker
function showLocationMap(mapId, lat, lon, address) {
    const mapElement = document.getElementById(mapId);
    if (!mapElement) return;
    
    mapElement.style.display = 'block';
    const clearBtn = mapId === 'location-map' ? 'clear-location-btn' : 'clear-edit-location-btn';
    const clearBtnElement = document.getElementById(clearBtn);
    if (clearBtnElement) clearBtnElement.style.display = 'inline-block';
    
    // Initialize map if not already initialized
    if (!locationMaps[mapId]) {
        const inputId = mapId === 'location-map' ? 'location' : 'edit-location';
        initializeLocationMap(mapId, inputId);
    }
    
    const map = locationMaps[mapId];
    map.setView([lat, lon], 15);
    
    // Remove existing markers
    map.eachLayer((layer) => {
        if (layer instanceof L.Marker) {
            map.removeLayer(layer);
        }
    });
    
    // Add marker
    const icon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    
    L.marker([lat, lon], {icon: icon})
        .addTo(map)
        .bindPopup(`<strong>${address || `${lat}, ${lon}`}</strong>`)
        .openPopup();
}

// Reverse geocode coordinates to address
/**
 * Reverse-geocode coordinates via Nominatim.
 *
 * Returns a structured result:
 *   {
 *     display:  full human-readable address (legacy behaviour),
 *     street:   street/road only (with house number if available),
 *     city:     city / town / municipality / village,
 *     province: state / region,
 *     country:  country name,
 *     raw:      original Nominatim address object,
 *   }
 *
 * Callers that previously did `const address = await reverseGeocode(...)`
 * still work — String(result) coerces to result.display.
 */
async function reverseGeocode(lat, lon) {
    const response = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`,
        {
            headers: { 'Accept': 'application/json' }
        }
    );

    const data = await response.json();
    const addr = (data && data.address) ? data.address : {};

    // Street: house number + road; fall back to pedestrian / neighbourhood / suburb if no road.
    const streetParts = [];
    if (addr.house_number) streetParts.push(addr.house_number);
    if (addr.road) streetParts.push(addr.road);
    else if (addr.pedestrian) streetParts.push(addr.pedestrian);
    else if (addr.footway) streetParts.push(addr.footway);
    else if (addr.path) streetParts.push(addr.path);
    else if (addr.neighbourhood) streetParts.push(addr.neighbourhood);
    else if (addr.suburb) streetParts.push(addr.suburb);

    const street = streetParts.join(' ').trim();
    const city = addr.city || addr.town || addr.municipality || addr.village || addr.hamlet || '';
    const province = addr.state || addr.region || addr.province || addr.state_district || '';
    const country = addr.country || '';

    // Full display: prefer composed parts, fall back to Nominatim's display_name.
    const displayParts = [];
    if (street) displayParts.push(street);
    if (addr.suburb && addr.suburb !== street) displayParts.push(addr.suburb);
    if (city) displayParts.push(city);
    if (province) displayParts.push(province);
    if (country) displayParts.push(country);
    const display = displayParts.length > 0 ? displayParts.join(', ') : (data.display_name || `${lat}, ${lon}`);

    const result = { display, street, city, province, country, raw: addr };
    // Backwards-compat: when used as a string (e.g. `popup.setContent(\`<b>${addr}</b>\`)`)
    // it serialises to the full display string.
    result.toString = () => display;
    return result;
}

/**
 * Apply a reverse-geocode result to the Location / Province / City inputs.
 * inputId is either "location" (create form) or "edit-location" (edit form).
 *
 * The Location field is set to the FULL address (street + suburb + city +
 * province + country, joined with commas) so the user can see exactly where
 * the pin landed; Province and City duplicate the broader administrative
 * components for explicit form submission.
 */
function fillLocationFields(inputId, geo) {
    const isEdit = inputId === 'edit-location';
    const locationInput = document.getElementById(inputId);
    const provinceInput = document.getElementById(isEdit ? 'edit-province' : 'province');
    const cityInput = document.getElementById(isEdit ? 'edit-city' : 'city');

    if (locationInput) {
        // Full address; falls back to street-only if Nominatim only returned a road,
        // and finally to an empty string so the field is never literally "undefined".
        locationInput.value = geo.display || geo.street || '';
    }
    if (provinceInput && geo.province) {
        provinceInput.value = geo.province;
        provinceInput.dispatchEvent(new Event('input', { bubbles: true }));
        provinceInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (cityInput && geo.city) {
        cityInput.value = geo.city;
        cityInput.dispatchEvent(new Event('input', { bubbles: true }));
        cityInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

// Geocode address and show on map
async function geocodeAndShowOnMap(address, map, inputId) {
    if (!address || !map) return;
    
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`,
            {
                headers: {
                    'User-Agent': 'FindITFast Lost and Found App'
                }
            }
        );
        
        const data = await response.json();
        
        if (data && data.length > 0) {
            const lat = parseFloat(data[0].lat);
            const lon = parseFloat(data[0].lon);
            
            const latInput = document.getElementById(`${inputId}-lat`);
            const lonInput = document.getElementById(`${inputId}-lon`);
            
            if (latInput) latInput.value = lat;
            if (lonInput) lonInput.value = lon;
            
            map.setView([lat, lon], 15);
            
            // Remove existing markers
            map.eachLayer((layer) => {
                if (layer instanceof L.Marker) {
                    map.removeLayer(layer);
                }
            });
            
            const icon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            
            L.marker([lat, lon], {icon: icon})
                .addTo(map)
                .bindPopup(`<strong>${address}</strong>`)
                .openPopup();
        }
    } catch (error) {
        console.error('Geocoding error:', error);
    }
}

// Location autocomplete functionality
let locationAutocompleteTimeout = {};
let selectedSuggestionIndex = -1;
let isSettingLocationProgrammatically = false; // Flag to track programmatic value setting

// Fetch location suggestions
async function fetchLocationSuggestions(query, inputId) {
    if (!query || query.length < 2) {
        hideLocationAutocomplete(inputId);
        return;
    }
    
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1`,
            {
                headers: {
                    'User-Agent': 'FindITFast Lost and Found App'
                }
            }
        );
        
        const data = await response.json();
        displayLocationSuggestions(data, inputId);
    } catch (error) {
        console.error('Error fetching location suggestions:', error);
        hideLocationAutocomplete(inputId);
    }
}

// Display location suggestions
function displayLocationSuggestions(suggestions, inputId) {
    if (!suggestions || suggestions.length === 0) {
        hideLocationAutocomplete(inputId);
        return;
    }
    
    // Check if map is visible
    const mapId = inputId === 'location' ? 'location-map' : 'edit-location-map';
    const mapElement = document.getElementById(mapId);
    const isMapVisible = mapElement && mapElement.style.display !== 'none';
    
    // Choose which autocomplete div to use
    let autocompleteDiv;
    if (isMapVisible) {
        // Show on top of map
        autocompleteDiv = document.getElementById(inputId === 'location' ? 'location-autocomplete-map' : 'edit-location-autocomplete-map');
        // Hide the regular autocomplete
        const regularAutocomplete = document.getElementById(inputId === 'location' ? 'location-autocomplete' : 'edit-location-autocomplete');
        if (regularAutocomplete) regularAutocomplete.classList.add('hidden');
    } else {
        // Show below input field
        autocompleteDiv = document.getElementById(inputId === 'location' ? 'location-autocomplete' : 'edit-location-autocomplete');
        // Hide the map autocomplete
        const mapAutocomplete = document.getElementById(inputId === 'location' ? 'location-autocomplete-map' : 'edit-location-autocomplete-map');
        if (mapAutocomplete) mapAutocomplete.classList.add('hidden');
    }
    
    if (!autocompleteDiv) return;
    
    // Add header
    const header = `
        <div class="px-4 py-2 bg-purple-50 border-b border-purple-200 sticky top-0">
            <div class="flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-purple-600"></i>
                <span class="text-xs font-semibold text-purple-900">Suggested Locations</span>
                <span class="text-xs text-purple-600 ml-auto">${suggestions.length} result${suggestions.length !== 1 ? 's' : ''}</span>
            </div>
        </div>
    `;
    
    autocompleteDiv.innerHTML = header + suggestions.map((suggestion, index) => {
        const displayName = suggestion.display_name;
        const lat = suggestion.lat;
        const lon = suggestion.lon;
        
        // Format address nicely
        let formattedAddress = '';
        if (suggestion.address) {
            const addr = suggestion.address;
            const parts = [];
            
            // Build address from most specific to least specific
            if (addr.house_number) parts.push(addr.house_number);
            if (addr.road) parts.push(addr.road);
            if (addr.neighbourhood || addr.suburb) parts.push(addr.neighbourhood || addr.suburb);
            if (addr.city || addr.town || addr.village) parts.push(addr.city || addr.town || addr.village);
            if (addr.state) parts.push(addr.state);
            if (addr.country) parts.push(addr.country);
            
            formattedAddress = parts.join(', ');
        }
        
        // Use formatted address if available, otherwise use display_name
        const primaryText = formattedAddress || displayName;
        const secondaryText = formattedAddress ? displayName : '';
        
        return `
            <div class="location-suggestion px-4 py-3 hover:bg-purple-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors" 
                 data-index="${index}"
                 data-lat="${lat}"
                 data-lon="${lon}"
                 data-name="${displayName.replace(/"/g, '&quot;')}"
                 onclick="selectLocationSuggestion('${inputId}', '${lat}', '${lon}', '${displayName.replace(/'/g, "\\'")}')"
                 onmouseenter="highlightLocationSuggestion('${inputId}', ${index})">
                <div class="flex items-start gap-3">
                    <div class="shrink-0 mt-0.5">
                        <i class="fas fa-map-marker-alt text-purple-500 text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-900 leading-tight">${primaryText}</div>
                        ${secondaryText ? `
                            <div class="text-xs text-gray-500 mt-1 leading-tight truncate">${secondaryText}</div>
                        ` : ''}
                    </div>
                    <div class="shrink-0 mt-0.5">
                        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    autocompleteDiv.classList.remove('hidden');
    selectedSuggestionIndex = -1;
}

// Hide location autocomplete
function hideLocationAutocomplete(inputId) {
    // Hide both regular and map autocomplete
    const regularAutocompleteId = inputId === 'location' ? 'location-autocomplete' : 'edit-location-autocomplete';
    const mapAutocompleteId = inputId === 'location' ? 'location-autocomplete-map' : 'edit-location-autocomplete-map';
    
    const regularAutocomplete = document.getElementById(regularAutocompleteId);
    const mapAutocomplete = document.getElementById(mapAutocompleteId);
    
    if (regularAutocomplete) {
        regularAutocomplete.classList.add('hidden');
    }
    if (mapAutocomplete) {
        mapAutocomplete.classList.add('hidden');
    }
    selectedSuggestionIndex = -1;
}

// Select location suggestion
function selectLocationSuggestion(inputId, lat, lon, name) {
    const locationInput = document.getElementById(inputId);
    const latInput = document.getElementById(`${inputId}-lat`);
    const lonInput = document.getElementById(`${inputId}-lon`);
    
    // Set flag to prevent autocomplete from showing
    isSettingLocationProgrammatically = true;
    
    if (locationInput) {
        locationInput.value = name;
        // Clear any pending autocomplete timeout to prevent suggestions from showing immediately
        if (locationAutocompleteTimeout[inputId]) {
            clearTimeout(locationAutocompleteTimeout[inputId]);
            delete locationAutocompleteTimeout[inputId];
        }
    }
    
    // Reset flag after a short delay
    setTimeout(() => {
        isSettingLocationProgrammatically = false;
    }, 100);
    
    if (latInput) latInput.value = lat;
    if (lonInput) lonInput.value = lon;
    
    // Hide suggestions immediately
    hideLocationAutocomplete(inputId);
    
    // Get map ID and element
    const mapId = inputId === 'location' ? 'location-map' : 'edit-location-map';
    const mapElement = document.getElementById(mapId);
    const latNum = parseFloat(lat);
    const lonNum = parseFloat(lon);
    
    // Show map if it's hidden
    if (mapElement && (mapElement.style.display === 'none' || !mapElement.style.display)) {
        toggleLocationMap(inputId);
    }
    
    // Initialize map if not already initialized, then pin the location
    const pinLocationOnMap = () => {
        // Initialize map if needed
        if (!locationMaps[mapId]) {
            initializeLocationMap(mapId, inputId);
            // Wait for map to initialize
            setTimeout(() => {
                pinMarkerOnMap();
            }, 300);
        } else {
            pinMarkerOnMap();
        }
    };
    
    const pinMarkerOnMap = () => {
        const map = locationMaps[mapId];
        if (map) {
            // Center map on selected location with animation
            map.setView([latNum, lonNum], 15, {
                animate: true,
                duration: 0.5
            });
            
            // Remove existing markers
            map.eachLayer((layer) => {
                if (layer instanceof L.Marker) {
                    map.removeLayer(layer);
                }
            });
            
            // Create custom icon
            const icon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            
            // Add marker and pin it on the map
            const marker = L.marker([latNum, lonNum], {icon: icon})
                .addTo(map)
                .bindPopup(`<strong>${name}</strong>`)
                .openPopup();
        }
    };
    
    // Pin location on map
    pinLocationOnMap();
    
    showToast('Location selected and pinned on map!', 'success');
}

// Highlight suggestion on hover
function highlightLocationSuggestion(inputId, index) {
    // Check both autocomplete divs
    const regularAutocompleteId = inputId === 'location' ? 'location-autocomplete' : 'edit-location-autocomplete';
    const mapAutocompleteId = inputId === 'location' ? 'location-autocomplete-map' : 'edit-location-autocomplete-map';
    
    const regularAutocomplete = document.getElementById(regularAutocompleteId);
    const mapAutocomplete = document.getElementById(mapAutocompleteId);
    
    const autocompleteDiv = regularAutocomplete && !regularAutocomplete.classList.contains('hidden') 
        ? regularAutocomplete 
        : (mapAutocomplete && !mapAutocomplete.classList.contains('hidden') ? mapAutocomplete : null);
    
    if (autocompleteDiv) {
        const suggestions = autocompleteDiv.querySelectorAll('.location-suggestion');
        suggestions.forEach((suggestion, i) => {
            if (i === index) {
                suggestion.classList.add('bg-purple-50');
            } else {
                suggestion.classList.remove('bg-purple-50');
            }
        });
    }
    selectedSuggestionIndex = index;
}

// Handle keyboard navigation in autocomplete
function handleLocationAutocompleteKeydown(event, inputId) {
    const regularAutocompleteId = inputId === 'location' ? 'location-autocomplete' : 'edit-location-autocomplete';
    const mapAutocompleteId = inputId === 'location' ? 'location-autocomplete-map' : 'edit-location-autocomplete-map';
    
    const regularAutocomplete = document.getElementById(regularAutocompleteId);
    const mapAutocomplete = document.getElementById(mapAutocompleteId);
    
    // Find which autocomplete is visible
    const autocompleteDiv = regularAutocomplete && !regularAutocomplete.classList.contains('hidden') 
        ? regularAutocomplete 
        : (mapAutocomplete && !mapAutocomplete.classList.contains('hidden') ? mapAutocomplete : null);
    
    if (!autocompleteDiv || autocompleteDiv.classList.contains('hidden')) {
        return;
    }
    
    const suggestions = autocompleteDiv.querySelectorAll('.location-suggestion');
    
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        selectedSuggestionIndex = Math.min(selectedSuggestionIndex + 1, suggestions.length - 1);
        suggestions[selectedSuggestionIndex].scrollIntoView({ block: 'nearest' });
        highlightLocationSuggestion(inputId, selectedSuggestionIndex);
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        selectedSuggestionIndex = Math.max(selectedSuggestionIndex - 1, -1);
        if (selectedSuggestionIndex >= 0) {
            suggestions[selectedSuggestionIndex].scrollIntoView({ block: 'nearest' });
            highlightLocationSuggestion(inputId, selectedSuggestionIndex);
        }
    } else if (event.key === 'Enter' && selectedSuggestionIndex >= 0) {
        event.preventDefault();
        const selectedSuggestion = suggestions[selectedSuggestionIndex];
        const lat = selectedSuggestion.dataset.lat;
        const lon = selectedSuggestion.dataset.lon;
        const name = selectedSuggestion.dataset.name;
        selectLocationSuggestion(inputId, lat, lon, name);
    } else if (event.key === 'Escape') {
        hideLocationAutocomplete(inputId);
    }
}

// Add event listeners for location input changes
document.addEventListener('DOMContentLoaded', function() {
    // For new item form
    const locationInput = document.getElementById('location');
    if (locationInput) {
        let geocodeTimeout;
        locationInput.addEventListener('input', function() {
            // Don't show suggestions if value was set programmatically
            if (isSettingLocationProgrammatically) {
                return;
            }
            
            const address = this.value.trim();
            
            clearTimeout(geocodeTimeout);
            clearTimeout(locationAutocompleteTimeout['location']);
            
            // Show autocomplete suggestions only if user is typing
            if (address.length >= 2) {
                locationAutocompleteTimeout['location'] = setTimeout(() => {
                    fetchLocationSuggestions(address, 'location');
                }, 300); // Wait 300ms after user stops typing
            } else {
                hideLocationAutocomplete('location');
            }
            
            // Don't auto-pin on map while typing - only pin when user selects a suggestion
        });
        
        // Handle keyboard navigation
        locationInput.addEventListener('keydown', function(e) {
            handleLocationAutocompleteKeydown(e, 'location');
        });
        
        // Hide autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            const autocompleteDiv = document.getElementById('location-autocomplete');
            if (!locationInput.contains(e.target) && (!autocompleteDiv || !autocompleteDiv.contains(e.target))) {
                hideLocationAutocomplete('location');
            }
        });
    }
    
    // For edit form
    const editLocationInput = document.getElementById('edit-location');
    if (editLocationInput) {
        let geocodeTimeout;
        
        editLocationInput.addEventListener('input', function() {
            // Don't show suggestions if value was set programmatically
            if (isSettingLocationProgrammatically) {
                return;
            }
            
            const address = this.value.trim();
            
            clearTimeout(geocodeTimeout);
            clearTimeout(locationAutocompleteTimeout['edit-location']);
            
            // Show autocomplete suggestions only if user is typing
            if (address.length >= 2) {
                locationAutocompleteTimeout['edit-location'] = setTimeout(() => {
                    fetchLocationSuggestions(address, 'edit-location');
                }, 300); // Wait 300ms after user stops typing
            } else {
                hideLocationAutocomplete('edit-location');
            }
            
            // Don't auto-pin on map while typing - only pin when user selects a suggestion
        });
        
        // Handle keyboard navigation
        editLocationInput.addEventListener('keydown', function(e) {
            handleLocationAutocompleteKeydown(e, 'edit-location');
        });
        
        // Hide autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            const autocompleteDiv = document.getElementById('edit-location-autocomplete');
            if (!editLocationInput.contains(e.target) && (!autocompleteDiv || !autocompleteDiv.contains(e.target))) {
                hideLocationAutocomplete('edit-location');
            }
        });
    }
    
    // Load tags from API
    loadTags();
});

// Tag management functionality
let availableTags = [];
let selectedTags = [];
let editSelectedTags = [];

// Load tags from API
async function loadTags() {
    try {
        const response = await fetch('/api/tags');
        const data = await response.json();
        availableTags = data;
        
        // Populate dropdowns
        populateTagDropdown('tags-dropdown', availableTags);
        populateTagDropdown('edit-tags-dropdown', availableTags);
    } catch (error) {
        console.error('Error loading tags:', error);
    }
}

// Populate tag dropdown
function populateTagDropdown(dropdownId, tags) {
    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) return;
    
    // Clear existing options except the first one
    dropdown.innerHTML = '<option value="">Select a tag...</option>';
    
    tags.forEach(tag => {
        const option = document.createElement('option');
        option.value = tag.name;
        option.textContent = `${tag.name} (${tag.usage_count} uses)`;
        option.dataset.tagId = tag.id;
        dropdown.appendChild(option);
    });
}

// Add tag from dropdown
function addTagFromDropdown() {
    const dropdown = document.getElementById('tags-dropdown');
    if (!dropdown || !dropdown.value) return;
    
    const tagName = dropdown.value.trim();
    if (tagName && !selectedTags.includes(tagName)) {
        selectedTags.push(tagName);
        updateSelectedTagsDisplay('selected-tags-container', selectedTags, 'tags');
        dropdown.value = ''; // Reset dropdown
    }
}

// Toggle new tag input field
function toggleNewTagInput() {
    const container = document.getElementById('new-tag-input-container');
    if (container) {
        container.classList.toggle('hidden');
        const input = document.getElementById('new-tag-input');
        if (input && !container.classList.contains('hidden')) {
            input.focus();
        }
    }
}

// Add tag from input field
function addTagFromInput() {
    const input = document.getElementById('new-tag-input');
    if (!input) return;
    
    const tagName = input.value.trim();
    if (tagName && !selectedTags.includes(tagName)) {
        selectedTags.push(tagName);
        updateSelectedTagsDisplay('selected-tags-container', selectedTags, 'tags');
        input.value = ''; // Clear input
        // Hide the input field after adding
        toggleNewTagInput();
    }
}

// Add tag for edit form from dropdown
function addEditTagFromDropdown() {
    const dropdown = document.getElementById('edit-tags-dropdown');
    if (!dropdown || !dropdown.value) return;
    
    const tagName = dropdown.value.trim();
    if (tagName && !editSelectedTags.includes(tagName)) {
        editSelectedTags.push(tagName);
        updateSelectedTagsDisplay('edit-selected-tags-container', editSelectedTags, 'edit-tags');
        dropdown.value = ''; // Reset dropdown
    }
}

// Toggle edit new tag input field
function toggleEditNewTagInput() {
    const container = document.getElementById('edit-new-tag-input-container');
    if (container) {
        container.classList.toggle('hidden');
        const input = document.getElementById('edit-new-tag-input');
        if (input && !container.classList.contains('hidden')) {
            input.focus();
        }
    }
}

// Add tag for edit form from input field
function addEditTagFromInput() {
    const input = document.getElementById('edit-new-tag-input');
    if (!input) return;
    
    const tagName = input.value.trim();
    if (tagName && !editSelectedTags.includes(tagName)) {
        editSelectedTags.push(tagName);
        updateSelectedTagsDisplay('edit-selected-tags-container', editSelectedTags, 'edit-tags');
        input.value = ''; // Clear input
        // Hide the input field after adding
        toggleEditNewTagInput();
    }
}

// Update selected tags display
function updateSelectedTagsDisplay(containerId, tagsArray, hiddenInputId) {
    const container = document.getElementById(containerId);
    const hiddenInput = document.getElementById(hiddenInputId);
    
    if (!container || !hiddenInput) return;
    
    // Update hidden input with JSON array
    hiddenInput.value = JSON.stringify(tagsArray);
    
    // Update display - only show tags if there are any
    if (tagsArray.length === 0) {
        container.innerHTML = '';
        container.style.display = 'none';
    } else {
        container.style.display = 'flex';
        container.innerHTML = tagsArray.map((tag, index) => `
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">
                ${tag}
                <button type="button" 
                        onclick="removeTag('${tag}', '${containerId}', '${hiddenInputId}')" 
                        class="ml-1 text-purple-600 hover:text-purple-800">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </span>
        `).join('');
    }
}

// Remove tag
function removeTag(tagName, containerId, hiddenInputId) {
    if (containerId === 'selected-tags-container') {
        selectedTags = selectedTags.filter(t => t !== tagName);
        updateSelectedTagsDisplay(containerId, selectedTags, hiddenInputId);
    } else if (containerId === 'edit-selected-tags-container') {
        editSelectedTags = editSelectedTags.filter(t => t !== tagName);
        updateSelectedTagsDisplay(containerId, editSelectedTags, hiddenInputId);
    }
}

// Load tags into edit form
function loadTagsIntoEditForm(tagsArray) {
    editSelectedTags = tagsArray;
    updateSelectedTagsDisplay('edit-selected-tags-container', editSelectedTags, 'edit-tags');
}

// Save new tag to database if it doesn't exist
async function saveNewTagIfNotExists(tagName) {
    // Check if tag already exists in available tags
    const exists = availableTags.some(tag => tag.name.toLowerCase() === tagName.toLowerCase());
    
    if (!exists) {
        try {
            const response = await fetch('/admin/tags', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value
                },
                body: JSON.stringify({ name: tagName })
            });
            
            if (response.ok) {
                const data = await response.json();
                // Reload tags to include the new one
                loadTags();
            }
        } catch (error) {
            console.error('Error saving new tag:', error);
            // Tag will still work, just won't be in the dropdown until admin adds it
        }
    }
}

// Add event listeners for dropdowns
document.addEventListener('DOMContentLoaded', function() {
    const tagsDropdown = document.getElementById('tags-dropdown');
    if (tagsDropdown) {
        tagsDropdown.addEventListener('change', function() {
            if (this.value) {
                addTagFromDropdown();
            }
        });
    }
    
    const editTagsDropdown = document.getElementById('edit-tags-dropdown');
    if (editTagsDropdown) {
        editTagsDropdown.addEventListener('change', function() {
            if (this.value) {
                addEditTagFromDropdown();
            }
        });
    }
    
    // Load tags from API
    loadTags();
});

// Toggle new tag input field
function toggleNewTagInput() {
    const container = document.getElementById('new-tag-input-container');
    if (container) {
        container.classList.toggle('hidden');
        const input = document.getElementById('new-tag-input');
        if (input && !container.classList.contains('hidden')) {
            input.focus();
        }
    }
}

// Add tag from input field
function addTagFromInput() {
    const input = document.getElementById('new-tag-input');
    if (!input) return;
    
    const tagName = input.value.trim();
    if (tagName && !selectedTags.includes(tagName)) {
        selectedTags.push(tagName);
        updateSelectedTagsDisplay('selected-tags-container', selectedTags, 'tags');
        input.value = ''; // Clear input
        // Hide the input field after adding
        toggleNewTagInput();
    }
}

// Add tag for edit form from dropdown
function addEditTagFromDropdown() {
    const dropdown = document.getElementById('edit-tags-dropdown');
    if (!dropdown || !dropdown.value) return;
    
    const tagName = dropdown.value.trim();
    if (tagName && !editSelectedTags.includes(tagName)) {
        editSelectedTags.push(tagName);
        updateSelectedTagsDisplay('edit-selected-tags-container', editSelectedTags, 'edit-tags');
        dropdown.value = ''; // Reset dropdown
    }
}

// Toggle edit new tag input field
function toggleEditNewTagInput() {
    const container = document.getElementById('edit-new-tag-input-container');
    if (container) {
        container.classList.toggle('hidden');
        const input = document.getElementById('edit-new-tag-input');
        if (input && !container.classList.contains('hidden')) {
            input.focus();
        }
    }
}

// Add tag for edit form from input field
function addEditTagFromInput() {
    const input = document.getElementById('edit-new-tag-input');
    if (!input) return;
    
    const tagName = input.value.trim();
    if (tagName && !editSelectedTags.includes(tagName)) {
        editSelectedTags.push(tagName);
        updateSelectedTagsDisplay('edit-selected-tags-container', editSelectedTags, 'edit-tags');
        input.value = ''; // Clear input
        // Hide the input field after adding
        toggleEditNewTagInput();
    }
}

// Update selected tags display
function updateSelectedTagsDisplay(containerId, tagsArray, hiddenInputId) {
    const container = document.getElementById(containerId);
    const hiddenInput = document.getElementById(hiddenInputId);
    
    if (!container || !hiddenInput) return;
    
    // Update hidden input with JSON array
    hiddenInput.value = JSON.stringify(tagsArray);
    
    // Update display - only show tags if there are any
    if (tagsArray.length === 0) {
        container.innerHTML = '';
        container.style.display = 'none';
    } else {
        container.style.display = 'flex';
        container.innerHTML = tagsArray.map((tag, index) => `
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-medium">
                ${tag}
                <button type="button" 
                        onclick="removeTag('${tag}', '${containerId}', '${hiddenInputId}')" 
                        class="ml-1 text-purple-600 hover:text-purple-800">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </span>
        `).join('');
    }
}

// Remove tag
function removeTag(tagName, containerId, hiddenInputId) {
    if (containerId === 'selected-tags-container') {
        selectedTags = selectedTags.filter(t => t !== tagName);
        updateSelectedTagsDisplay(containerId, selectedTags, hiddenInputId);
    } else if (containerId === 'edit-selected-tags-container') {
        editSelectedTags = editSelectedTags.filter(t => t !== tagName);
        updateSelectedTagsDisplay(containerId, editSelectedTags, hiddenInputId);
    }
}

// Load tags into edit form
function loadTagsIntoEditForm(tagsArray) {
    editSelectedTags = tagsArray;
    updateSelectedTagsDisplay('edit-selected-tags-container', editSelectedTags, 'edit-tags');
}


// Add event listeners for dropdowns and inputs
document.addEventListener('DOMContentLoaded', function() {
    const tagsDropdown = document.getElementById('tags-dropdown');
    if (tagsDropdown) {
        tagsDropdown.addEventListener('change', function() {
            if (this.value) {
                addTagFromDropdown();
            }
        });
    }
    
    const editTagsDropdown = document.getElementById('edit-tags-dropdown');
    if (editTagsDropdown) {
        editTagsDropdown.addEventListener('change', function() {
            if (this.value) {
                addEditTagFromDropdown();
            }
        });
    }
    
    // Handle Enter key in new tag input
    const newTagInput = document.getElementById('new-tag-input');
    if (newTagInput) {
        newTagInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addTagFromInput();
            }
        });
    }
    
    const editNewTagInput = document.getElementById('edit-new-tag-input');
    if (editNewTagInput) {
        editNewTagInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addEditTagFromInput();
            }
        });
    }
});
</script>
@endsection
