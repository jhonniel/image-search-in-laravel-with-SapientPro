@extends('layouts.user')

@section('title', 'Your Reported Items')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Reported Items</h2>
                <p class="text-gray-600">Upload and manage your lost or found items</p>
            </div>
            <button onclick="toggleUploadForm()" class="bg-purple-primary text-white px-6 py-3 rounded-lg hover:bg-purple-600 transition-colors flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Report New Item
            </button>
        </div>
    </div>

    <!-- Upload Form -->
    <div id="upload-form" class="bg-white rounded-lg shadow-sm border border-gray-200 hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Report New Item</h3>
            <p class="text-sm text-gray-500 mt-1">Fill out the form below to report a lost or found item</p>
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

            <!-- Location -->
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                <input type="text" id="location" name="location" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="Where was this item found/lost?">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="3" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                          placeholder="Describe the item in detail..."></textarea>
            </div>

            <!-- Tags -->
            <div>
                <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                <input type="text" id="tags" name="tags"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                       placeholder="Enter tags separated by commas (e.g., phone, black, case)">
                <p class="text-xs text-gray-500 mt-1">Tags help others find your item more easily</p>
            </div>

            <!-- Images -->
            <div>
                <label for="item-images" class="block text-sm font-medium text-gray-700 mb-2">Images</label>
                <input type="file" id="item-images" name="images[]" multiple accept="image/*" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">Upload multiple images to help identify the item</p>
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
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Your Reported Items</h3>
            <p class="text-sm text-gray-500 mt-1">Items you have reported</p>
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

<script>
// Toggle upload form
function toggleUploadForm() {
    const form = document.getElementById('upload-form');
    form.classList.toggle('hidden');
}

// Form submission
document.getElementById('item-upload-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Prevent double submission
    if (this.dataset.submitting === 'true') {
        return;
    }
    this.dataset.submitting = 'true';

    showLoadingAnimation();

    // Get form elements
    const files = document.getElementById('item-images').files;
    const itemType = document.querySelector('input[name="item_type"]:checked');
    const location = document.getElementById('location').value.trim();
    const description = document.getElementById('description').value.trim();
    const tags = document.getElementById('tags').value.trim();

    // Client-side validation
    if (!itemType) {
        hideLoadingAnimation();
        showToast('Please select an item type', 'error');
        this.dataset.submitting = 'false';
        return;
    }

    if (!location) {
        hideLoadingAnimation();
        showToast('Please enter a location', 'error');
        this.dataset.submitting = 'false';
        return;
    }

    if (!description) {
        hideLoadingAnimation();
        showToast('Please enter a description', 'error');
        this.dataset.submitting = 'false';
        return;
    }

    if (files.length === 0) {
        hideLoadingAnimation();
        showToast('Please select at least one image', 'error');
        this.dataset.submitting = 'false';
        return;
    }

    // Create FormData
    const formData = new FormData();
    formData.append('item_type', itemType.value);
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

    try {
        const response = await fetch('/api/user/items/upload', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast('Item reported successfully!', 'success');
            toggleUploadForm();
            this.reset();
            loadItems(); // Reload the items list
        } else {
            showToast(data.message || 'Error uploading item. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error uploading item. Please try again.', 'error');
    } finally {
        hideLoadingAnimation();
        this.dataset.submitting = 'false';
    }
});

// Load user items
async function loadItems() {
    try {
        const response = await fetch('/api/user/items', {
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
    if (!itemsContainer) return;

    // Store items globally for access in other functions
    window.userItems = items;

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
                                    ${item.images.map((image, index) => `
                                        <div class="carousel-slide flex-shrink-0 w-full">
                                            <div class="relative group">
                                                <img src="${image.path}" alt="${image.original_name}" class="w-full h-48 object-cover rounded-lg border border-gray-200">
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 rounded-lg flex items-center justify-center">
                                                    <button onclick="viewImage('${image.path}')" class="opacity-0 group-hover:opacity-100 bg-white text-gray-800 px-3 py-1 rounded-lg text-sm font-medium transition-all duration-200">
                                                        <i class="fas fa-eye mr-1"></i>
                                                        View
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>

                            ${item.images.length > 1 ? `
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
                        <div class="flex items-center justify-between">
                            <button onclick="viewItemDetails('${item.upload_id}')" class="px-4 py-2 bg-blue-100 text-blue-800 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium">
                                <i class="fas fa-info-circle mr-1"></i>
                                View Details
                            </button>
                            <button onclick="deleteItem('${item.upload_id}')" class="px-4 py-2 bg-red-100 text-red-800 rounded-lg hover:bg-red-200 transition-colors text-sm font-medium">
                                <i class="fas fa-trash mr-1"></i>
                                Delete Item
                            </button>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

    // Initialize carousels
    items.forEach(item => {
        if (item.images.length > 1) {
            initializeCarousel(item.upload_id, item.images.length);
        }
    });
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
        const response = await fetch(`/api/user/items/${uploadId}`, {
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
</script>
@endsection
