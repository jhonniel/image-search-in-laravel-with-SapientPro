@extends('layouts.user')

@section('title', 'Your Reported Items')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl shadow-md p-6 border border-purple-100">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Reported Items</h2>
                <p class="text-gray-600 text-lg">Upload and manage your lost or found items</p>
            </div>
            <button onclick="toggleUploadForm()" class="bg-gradient-to-r from-purple-primary to-pink-primary text-white px-8 py-3 rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center font-medium">
                <i class="fas fa-plus mr-2"></i>
                Report New Item
            </button>
        </div>
    </div>

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

        <form id="item-upload-form" class="p-6 space-y-6">
            @csrf

            <!-- Item Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Item Type</label>
                <div class="flex space-x-4">
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
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
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
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
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
                <input type="text" id="location" name="location" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="Where was this item found/lost? (e.g., Street name, Building, etc.)">
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
                <input type="text" id="tags" name="tags" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="Enter tags separated by commas (e.g., phone, black, case)">
                <p class="text-xs text-gray-500 mt-1">Tags help others find your item more easily</p>
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
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Uploading images...</span>
                        <span id="upload-progress-text" class="text-sm text-gray-500">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div id="upload-progress-bar" class="bg-purple-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
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
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-900">Your Reported Items</h3>
            <p class="text-sm text-gray-600 mt-1">Items you have reported</p>
        </div>

        <div id="user-items-list" class="p-6">
            <!-- Items will be loaded here -->
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                <p>Loading your items...</p>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="image-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg max-w-4xl max-h-full overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Image Preview</h3>
            <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-4">
            <img id="modal-image" src="" alt="Preview" class="max-w-full max-h-96 object-contain mx-auto">
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
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
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
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
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
                <input type="text" id="edit-location" name="location" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="Where was this item found/lost? (e.g., Street name, Building, etc.)">
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
                <input type="text" id="edit-tags" name="tags"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="Enter tags separated by commas (e.g., phone, black, case)">
                <p class="text-xs text-gray-500 mt-1">Tags help others find your item more easily</p>
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
// Image upload state
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
    progressContainer.classList.remove('hidden');
    updateUploadProgress(0);
}

function hideUploadProgress() {
    const progressContainer = document.getElementById('upload-progress-container');
    progressContainer.classList.add('hidden');
    updateUploadProgress(0);
}

function updateUploadProgress(percentage) {
    const progressBar = document.getElementById('upload-progress-bar');
    const progressText = document.getElementById('upload-progress-text');
    progressBar.style.width = percentage + '%';
    progressText.textContent = Math.round(percentage) + '%';
}

function simulateUploadProgress() {
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress >= 90) {
            progress = 90;
            clearInterval(interval);
        }
        updateUploadProgress(progress);
    }, 200);
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
    const tags = document.getElementById('tags').value.trim();

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

    if (!tags) {
        showToast('Please enter tags', 'error');
        this.dataset.submitting = 'false';
        return;
    }

    if (files.length === 0) {
        showToast('Please select at least one image', 'error');
        this.dataset.submitting = 'false';
        return;
    }

    // Show upload progress
    showUploadProgress();

    // Create FormData
    const formData = new FormData();
    formData.append('item_type', itemType.value);
    
    // Only include city/province if fields are enabled
    if (enableCityField && city) {
        formData.append('city', city);
    }
    if (enableProvinceField && province) {
        formData.append('province', province);
    }
    
    formData.append('location', location);
    formData.append('description', description);
    formData.append('tags', tags);

    // Add files (deduplicate on client side)
    const uniqueFiles = new Map();
    for (let file of files) {
        const key = `${file.name}-${file.size}`;
        if (!uniqueFiles.has(key)) {
            uniqueFiles.set(key, file);
        }
    }

    for (let file of uniqueFiles.values()) {
        formData.append('images[]', file);
    }

    // Disable form during upload
    const submitButton = this.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...';

    try {
        // Simulate progress (since we can't track actual upload progress with fetch)
        simulateUploadProgress();

        console.log('Starting upload request...');
        
        // Create abort controller for timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 120000); // 2 minute timeout
        
        const response = await fetch('/api/items/upload', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            signal: controller.signal,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        clearTimeout(timeoutId);
        console.log('Response received:', response.status, response.statusText);

        // Complete progress
        updateUploadProgress(100);

        // Check if response is OK
        if (!response.ok) {
            hideUploadProgress();
            const errorText = await response.text();
            console.error('Upload failed with status:', response.status, 'Error:', errorText);
            let errorMessage = 'Error uploading item. Please try again.';
            try {
                const errorData = JSON.parse(errorText);
                errorMessage = errorData.message || errorMessage;
            } catch (e) {
                // If not JSON, use the text or default message
                errorMessage = errorText || errorMessage;
            }
            showToast(errorMessage, 'error');
            return;
        }

        const data = await response.json();
        console.log('Upload response data:', data);

        if (data.success) {
            hideUploadProgress(); // Hide progress bar on success
            showToast('Item reported successfully!', 'success');
            
            // Reset form immediately
            this.reset();
            resetImageUpload();
            
            // Reload items and hide form
            loadItems().then(() => {
                setTimeout(() => {
                    toggleUploadForm();
                }, 300);
            }).catch(err => {
                console.error('Error loading items after upload:', err);
                // Still hide the form even if loading items fails
                setTimeout(() => {
                    toggleUploadForm();
                }, 300);
            });
        } else {
            hideUploadProgress();
            console.error('Upload returned success=false:', data);
            showToast(data.message || 'Error uploading item. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Upload error:', error);
        hideUploadProgress();
        let errorMessage = 'Error uploading item. Please try again.';
        
        if (error.name === 'AbortError') {
            errorMessage = 'Upload timed out. The file may be too large or the server is slow. Please try again.';
        } else if (error.message) {
            errorMessage = error.message;
        }
        
        showToast(errorMessage, 'error');
    } finally {
        // Always reset form state
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
        this.dataset.submitting = 'false';
    }
});

// Load user items
async function loadItems() {
    try {
        const response = await fetch('/api/items', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (response.ok) {
            const data = await response.json();

            if (data.success) {
                displayUserItems(data.data);
            } else {
                showErrorState('Failed to load items: ' + data.message);
            }
        } else {
            if (response.status === 401) {
                alert('You need to be logged in to view your items. Please log in and try again.');
                window.location.href = '/login';
            } else {
                showErrorState('Failed to load items. Please try again.');
            }
        }
    } catch (error) {
        console.error('Error loading items:', error);
        showErrorState('Error loading items. Please try again.');
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

        if (items.length === 0) {
            itemsContainer.innerHTML = `
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No items reported yet</h3>
                    <p class="text-gray-500 mb-4">Start by reporting a lost or found item to help others.</p>
                    <button onclick="toggleUploadForm()" class="bg-purple-primary text-white px-6 py-2 rounded-lg hover:bg-purple-600 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Report Your First Item
                    </button>
                </div>
            `;
            return;
        }

        itemsContainer.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            ${items.map(item => `
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Item Header -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center ${item.item_type === 'lost' ? 'bg-red-100' : 'bg-green-100'}">
                                    <i class="fas ${item.item_type === 'lost' ? 'fa-search text-red-600' : 'fa-hand-holding text-green-600'}"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">${item.item_type === 'lost' ? 'Lost Item' : 'Found Item'}</h3>
                                    <p class="text-sm text-gray-500">Reported ${new Date(item.created_at).toLocaleDateString()}</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <span class="px-3 py-1 rounded-full text-xs font-medium ${item.item_type === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                                    ${item.item_type === 'lost' ? 'Lost' : 'Found'}
                                </span>
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-user mr-1"></i>
                                    Your Item
                                </span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <p class="text-gray-700 mb-2"><strong>Description:</strong> ${item.description || 'No description provided'}</p>
                            <p class="text-gray-700 mb-2"><strong>Location:</strong> ${item.location || 'No location specified'}</p>
                            ${item.tags && item.tags.length > 0 ? `
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <strong class="text-gray-700">Tags:</strong>
                                    ${item.tags.map(tag => `<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">${tag}</span>`).join('')}
                                </div>
                            ` : ''}
                        </div>

                        <div class="text-sm text-gray-500">
                            <i class="fas fa-clock mr-1"></i>
                            Reported ${new Date(item.created_at).toLocaleDateString()}
                        </div>
                    </div>

                    <!-- Images Carousel -->
                    <div class="p-6">
                        <div class="relative">
                            <div class="carousel-container overflow-hidden rounded-lg">
                                <div class="carousel-track flex transition-transform duration-300 ease-in-out" id="carousel-${item.upload_id}">
                                    ${item.images && item.images.length > 0 ? item.images.map((image, index) => {
                                        const imgPath = image.path || image.file_path || '';
                                        console.log('Rendering image:', imgPath, 'for item:', item.upload_id);
                                        return `
                                        <div class="carousel-slide flex-shrink-0 w-full" style="background-color: #f3f4f6;">
                                            <div class="relative group" style="background-color: #f3f4f6;">
                                                <img src="${imgPath}" 
                                                     alt="${image.original_name || 'Item image'}" 
                                                     class="w-full h-48 object-cover rounded-lg border border-gray-200"
                                                     style="background-color: #f3f4f6; min-height: 192px; display: block; width: 100%; height: 192px; position: relative; z-index: 1;"
                                                     onerror="console.error('Image failed to load:', this.src); this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                     onload="console.log('Image loaded successfully:', this.src); this.style.backgroundColor='transparent'; this.style.opacity='1';"
                                                     loading="lazy">
                                                <div class="hidden w-full h-48 bg-gray-100 rounded-lg border border-gray-200 items-center justify-center">
                                                    <div class="text-center text-gray-400">
                                                        <i class="fas fa-image text-4xl mb-2"></i>
                                                        <p class="text-sm">Image not available</p>
                                                    </div>
                                                </div>
                                                <div class="absolute inset-0 transition-all duration-200 rounded-lg flex items-center justify-center pointer-events-none" style="background-color: transparent;">
                                                    <button onclick="viewImage('${imgPath}')" class="opacity-0 group-hover:opacity-100 bg-white text-gray-800 px-3 py-1 rounded-lg text-sm font-medium transition-all duration-200 pointer-events-auto z-10 shadow-lg">
                                                        <i class="fas fa-eye mr-1"></i>
                                                        View
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    }).join('') : `
                                        <div class="carousel-slide flex-shrink-0 w-full">
                                            <div class="relative group">
                                                <div class="w-full h-48 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                                                    <div class="text-center text-gray-400">
                                                        <i class="fas fa-image text-4xl mb-2"></i>
                                                        <p class="text-sm">No image available</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `}
                                </div>
                            </div>

                            ${item.images && item.images.length > 1 ? `
                                <!-- Carousel Navigation -->
                                <div class="flex items-center justify-between mt-4">
                                    <button onclick="previousSlide('${item.upload_id}')" class="flex items-center justify-center w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
                                        <i class="fas fa-chevron-left text-gray-600"></i>
                                    </button>

                                    <div class="flex items-center space-x-2">
                                        <div class="flex space-x-1">
                                            ${item.images.map((_, index) => `
                                                <button onclick="goToSlide('${item.upload_id}', ${index})"
                                                        class="carousel-dot w-2 h-2 rounded-full bg-gray-300 transition-colors"
                                                        id="dot-${item.upload_id}-${index}"></button>
                                            `).join('')}
                                        </div>
                                        <span class="carousel-counter text-sm text-gray-500 ml-2" id="counter-${item.upload_id}">1 / ${item.images.length}</span>
                                    </div>

                                    <button onclick="nextSlide('${item.upload_id}')" class="flex items-center justify-center w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
                                        <i class="fas fa-chevron-right text-gray-600"></i>
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="p-6 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <button onclick="viewItemDetails('${item.upload_id}')" class="px-4 py-2 bg-blue-100 text-blue-800 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium">
                                <i class="fas fa-info-circle mr-1"></i>
                                View Details
                            </button>
                            <div class="flex items-center gap-2">
                                <button onclick="editItem('${item.upload_id}')" class="px-4 py-2 bg-purple-100 text-purple-800 rounded-lg hover:bg-purple-200 transition-colors text-sm font-medium">
                                    <i class="fas fa-edit mr-1"></i>
                                    Edit Item
                                </button>
                                <button onclick="deleteItem('${item.upload_id}')" class="px-4 py-2 bg-red-100 text-red-800 rounded-lg hover:bg-red-200 transition-colors text-sm font-medium">
                                    <i class="fas fa-trash mr-1"></i>
                                    Delete Item
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
        `;

        // Initialize carousels
        items.forEach(item => {
            if (item.images && item.images.length > 1) {
                initializeCarousel(item.upload_id, item.images.length);
            }
        });
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
        const response = await fetch(`/api/items/${uploadId}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        const data = await response.json();
        if (data.success) {
            showToast('Item deleted successfully!', 'success');
            loadItems(); // Reload the items list
        } else {
            showToast(data.message || 'Error deleting item. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error deleting item. Please try again.', 'error');
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
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white font-medium transition-all duration-300 transform translate-x-full`;

    if (type === 'success') {
        toast.classList.add('bg-green-500');
    } else if (type === 'error') {
        toast.classList.add('bg-red-500');
    } else {
        toast.classList.add('bg-blue-500');
    }

    toast.textContent = message;
    document.body.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);

    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
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

// Image modal functions
function viewImage(imagePath) {
    document.getElementById('modal-image').src = imagePath;
    document.getElementById('image-modal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('image-modal').classList.add('hidden');
}

// Item details function
function viewItemDetails(uploadId) {
    const item = window.userItems.find(item => item.upload_id === uploadId);
    if (!item) return;

    const content = `
        <div class="space-y-4">
            <div>
                <h4 class="font-semibold text-gray-900">Item Type</h4>
                <p class="text-gray-600">${item.item_type === 'lost' ? 'Lost Item' : 'Found Item'}</p>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900">Description</h4>
                <p class="text-gray-600">${item.description || 'No description provided'}</p>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900">Location</h4>
                <p class="text-gray-600">${item.location || 'No location specified'}</p>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900">Date Posted</h4>
                <p class="text-gray-600">${new Date(item.created_at).toLocaleDateString()}</p>
            </div>
            ${item.tags && item.tags.length > 0 ? `
                <div>
                    <h4 class="font-semibold text-gray-900">Tags</h4>
                    <div class="flex flex-wrap gap-2">
                        ${item.tags.map(tag => `<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">${tag}</span>`).join('')}
                    </div>
                </div>
            ` : ''}
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
    document.getElementById('edit-description').value = item.description || '';
    document.getElementById('edit-tags').value = item.tags ? (Array.isArray(item.tags) ? item.tags.join(', ') : item.tags) : '';
    
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

    // Get tags (optional)
    if (tags) {
        formData.append('tags', tags);
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
</script>
@endsection
