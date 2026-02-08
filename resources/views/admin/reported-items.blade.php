@extends('layouts.admin')

@section('title', 'Reported Items')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Reported Items</h1>
        <p class="text-gray-600">View and manage all items reported by users</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-inbox text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Items</p>
                    <p class="text-2xl font-bold text-gray-900">{{ count($formattedItems) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-search text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Lost Items</p>
                    <p class="text-2xl font-bold text-gray-900">{{ collect($formattedItems)->where('item_type', 'lost')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-hand-holding text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Found Items</p>
                    <p class="text-2xl font-bold text-gray-900">{{ collect($formattedItems)->where('item_type', 'found')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Unique Users</p>
                    <p class="text-2xl font-bold text-gray-900">{{ collect($formattedItems)->pluck('uploader_email')->unique()->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Filter by type:</label>
                <select id="typeFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                    <option value="">All Items</option>
                    <option value="lost">Lost Items</option>
                    <option value="found">Found Items</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Sort by:</label>
                <select id="sortFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="email">User Email</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <input type="text" id="searchInput" placeholder="Search by description, email, or tags..."
                       class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent w-64">
            </div>
        </div>
    </div>

    <!-- Items Grid -->
    <div id="itemsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($formattedItems as $item)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden item-card"
             data-type="{{ $item['item_type'] }}"
             data-email="{{ $item['uploader_email'] }}"
             data-description="{{ strtolower($item['description']) }}"
             data-tags="{{ strtolower(implode(' ', $item['tags'])) }}"
             data-date="{{ $item['created_at'] }}"
             data-upload-id="{{ $item['upload_id'] }}">

            <!-- Item Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $item['item_type'] === 'lost' ? 'bg-red-100' : 'bg-green-100' }}">
                            <i class="fas {{ $item['item_type'] === 'lost' ? 'fa-search text-red-600' : 'fa-hand-holding text-green-600' }}"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $item['item_type'] === 'lost' ? 'Lost Item' : 'Found Item' }}</h3>
                            <p class="text-sm text-gray-500">{{ $item['uploader_email'] }}</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-medium {{ $item['item_type'] === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                        {{ $item['item_type'] === 'lost' ? 'Lost' : 'Found' }}
                    </span>
                </div>

                <div class="mb-4">
                    <p class="text-gray-700 mb-2"><strong>Description:</strong> {{ $item['description'] ?: 'No description provided' }}</p>
                    <p class="text-gray-700 mb-2"><strong>Location:</strong> {{ $item['location'] ?: 'No location specified' }}</p>
                    @if(!empty($item['tags']))
                    <div class="flex flex-wrap gap-2 mb-2">
                        <strong class="text-gray-700">Tags:</strong>
                        @foreach($item['tags'] as $tag)
                        <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                    @if(!empty($item['detected_objects']) && is_array($item['detected_objects']) && count($item['detected_objects']) > 0)
                    <div class="mb-2">
                        <strong class="text-gray-700 flex items-center mb-1">
                            <i class="fas fa-cube mr-1 text-blue-600"></i>
                            Detected Objects ({{ count($item['detected_objects']) }}):
                        </strong>
                        <div class="flex flex-wrap gap-2">
                            @foreach(array_slice($item['detected_objects'], 0, 5) as $obj)
                            @php
                                $objName = is_array($obj) ? ($obj['name'] ?? '') : (is_string($obj) ? $obj : '');
                                $objScore = is_array($obj) ? ($obj['score'] ?? 0) : 0;
                                $confidence = $objScore > 0 ? ' (' . round($objScore * 100) . '% confidence)' : '';
                            @endphp
                            @if(!empty($objName))
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium" title="Detected by Google Vision API{{ $confidence }}">
                                <i class="fas fa-eye mr-1"></i>{{ $objName }}
                            </span>
                            @endif
                            @endforeach
                            @if(count($item['detected_objects']) > 5)
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">+{{ count($item['detected_objects']) - 5 }} more</span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <div class="text-sm text-gray-500">
                    <i class="fas fa-clock mr-1"></i>
                    Reported {{ \Carbon\Carbon::parse($item['created_at'])->diffForHumans() }}
                </div>
            </div>

            <!-- Images Carousel -->
            <div class="p-6">
                <div class="relative">
                    <div class="carousel-container overflow-hidden rounded-lg">
                        <div class="carousel-track flex transition-transform duration-300 ease-in-out" id="carousel-{{ $item['upload_id'] }}">
                            @forelse($item['images'] as $index => $image)
                            <div class="carousel-slide flex-shrink-0 w-full">
                                <div class="relative group">
                                    @php
                                        $imgPath = $image['path'] ?? $image['file_path'] ?? '';
                                        // Use path directly if it starts with /storage/ or http, otherwise use asset()
                                        if (empty($imgPath)) {
                                            $imgSrc = '';
                                        } elseif (str_starts_with($imgPath, 'http')) {
                                            $imgSrc = $imgPath;
                                        } elseif (str_starts_with($imgPath, '/storage/')) {
                                            // Path already correct, use directly
                                            $imgSrc = $imgPath;
                                        } elseif (str_starts_with($imgPath, 'storage/')) {
                                            // Missing leading slash
                                            $imgSrc = '/' . $imgPath;
                                        } else {
                                            // Use asset() for relative paths
                                            $imgSrc = asset($imgPath);
                                        }
                                    @endphp
                                    <img src="{{ $imgSrc }}" 
                                         alt="{{ $image['original_name'] ?? 'Item image' }}" 
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
                                        @php
                                            $viewPath = $image['path'] ?? $image['file_path'] ?? '';
                                            // Use path directly if it starts with /storage/ or http, otherwise use asset()
                                            if (empty($viewPath)) {
                                                $viewSrc = '';
                                            } elseif (str_starts_with($viewPath, 'http')) {
                                                $viewSrc = $viewPath;
                                            } elseif (str_starts_with($viewPath, '/storage/')) {
                                                // Path already correct, use directly
                                                $viewSrc = $viewPath;
                                            } elseif (str_starts_with($viewPath, 'storage/')) {
                                                // Missing leading slash
                                                $viewSrc = '/' . $viewPath;
                                            } else {
                                                // Use asset() for relative paths
                                                $viewSrc = asset($viewPath);
                                            }
                                        @endphp
                                        <button onclick="viewImage('{{ $viewSrc }}')" class="opacity-0 group-hover:opacity-100 bg-white text-gray-800 px-3 py-1 rounded-lg text-sm font-medium transition-all duration-200 pointer-events-auto z-10 shadow-lg">
                                            <i class="fas fa-eye mr-1"></i>
                                            View
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @empty
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
                            @endforelse
                        </div>
                    </div>

                    @if(count($item['images']) > 1)
                        <!-- Carousel Navigation -->
                        <div class="flex items-center justify-between mt-4">
                            <button onclick="previousSlide('{{ $item['upload_id'] }}')" class="flex items-center justify-center w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
                                <i class="fas fa-chevron-left text-gray-600"></i>
                            </button>

                            <div class="flex space-x-2">
                                @foreach($item['images'] as $index => $image)
                                <button onclick="goToSlide('{{ $item['upload_id'] }}', {{ $index }})"
                                        class="carousel-dot w-2 h-2 rounded-full bg-gray-300 hover:bg-gray-400 transition-colors"
                                        id="dot-{{ $item['upload_id'] }}-{{ $index }}"></button>
                                @endforeach
                            </div>

                            <button onclick="nextSlide('{{ $item['upload_id'] }}')" class="flex items-center justify-center w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
                                <i class="fas fa-chevron-right text-gray-600"></i>
                            </button>
                        </div>

                        <!-- Image Counter -->
                        <div class="text-center mt-2">
                            <span class="text-sm text-gray-500" id="counter-{{ $item['upload_id'] }}">1 of {{ count($item['images']) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-images mr-1"></i>
                        {{ count($item['images']) }} image(s)
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="viewItemDetails('{{ $item['upload_id'] }}')"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <i class="fas fa-info-circle mr-1"></i>
                            Details
                        </button>
                        <button onclick="contactUser('{{ $item['uploader_email'] }}')"
                                class="text-green-600 hover:text-green-800 text-sm font-medium">
                            <i class="fas fa-envelope mr-1"></i>
                            Contact
                        </button>
                        <button onclick="deleteAdminItem('{{ $item['upload_id'] }}')"
                                class="text-red-600 hover:text-red-800 text-sm font-medium">
                            <i class="fas fa-trash mr-1"></i>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-inbox text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No items reported yet</h3>
            <p class="text-gray-500">When users start reporting items, they will appear here.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-4xl max-h-full overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Image Preview</h3>
            <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-4">
            <img id="modalImage" src="" alt="Preview" class="max-w-full max-h-96 object-contain mx-auto">
        </div>
    </div>
</div>

<!-- Item Details Modal -->
<div id="itemDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-full overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Item Details</h3>
            <button onclick="closeItemDetailsModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="itemDetailsContent" class="p-4 max-h-96 overflow-y-auto">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<script>
// Filter and search functionality
document.addEventListener('DOMContentLoaded', function() {
    const typeFilter = document.getElementById('typeFilter');
    const sortFilter = document.getElementById('sortFilter');
    const searchInput = document.getElementById('searchInput');
    const itemsGrid = document.getElementById('itemsGrid');

    function filterItems() {
        const typeValue = typeFilter.value;
        const searchValue = searchInput.value.toLowerCase();
        const sortValue = sortFilter.value;

        const items = Array.from(itemsGrid.querySelectorAll('.item-card'));

        // Filter items
        let filteredItems = items.filter(item => {
            const itemType = item.dataset.type;
            const email = item.dataset.email.toLowerCase();
            const description = item.dataset.description;
            const tags = item.dataset.tags;

            const typeMatch = !typeValue || itemType === typeValue;
            const searchMatch = !searchValue ||
                email.includes(searchValue) ||
                description.includes(searchValue) ||
                tags.includes(searchValue);

            return typeMatch && searchMatch;
        });

        // Sort items
        filteredItems.sort((a, b) => {
            switch(sortValue) {
                case 'oldest':
                    return new Date(a.dataset.date) - new Date(b.dataset.date);
                case 'email':
                    return a.dataset.email.localeCompare(b.dataset.email);
                case 'newest':
                default:
                    return new Date(b.dataset.date) - new Date(a.dataset.date);
            }
        });

        // Update display
        items.forEach(item => item.style.display = 'none');
        filteredItems.forEach(item => item.style.display = 'block');
    }

    typeFilter.addEventListener('change', filterItems);
    sortFilter.addEventListener('change', filterItems);
    searchInput.addEventListener('input', filterItems);
});

// Image modal functions
function viewImage(imagePath) {
    document.getElementById('modalImage').src = imagePath;
    document.getElementById('imageModal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
}

// Item details modal functions
function viewItemDetails(uploadId) {
    // This would typically fetch more details from the server
    const content = `
        <div class="space-y-4">
            <div>
                <h4 class="font-semibold text-gray-900">Upload ID</h4>
                <p class="text-gray-600">${uploadId}</p>
            </div>
            <div>
                <h4 class="font-semibold text-gray-900">Actions</h4>
                <div class="flex space-x-2 mt-2">
                    <button class="px-3 py-1 bg-blue-100 text-blue-800 rounded text-sm">View Full Details</button>
                    <button class="px-3 py-1 bg-green-100 text-green-800 rounded text-sm">Mark as Resolved</button>
                    <button class="px-3 py-1 bg-red-100 text-red-800 rounded text-sm">Remove Item</button>
                </div>
            </div>
        </div>
    `;

    document.getElementById('itemDetailsContent').innerHTML = content;
    document.getElementById('itemDetailsModal').classList.remove('hidden');
}

function closeItemDetailsModal() {
    document.getElementById('itemDetailsModal').classList.add('hidden');
}

// Contact user function
function contactUser(email) {
    if (confirm(`Send email to ${email}?`)) {
        // This would typically open email client or send notification
        alert(`Email functionality would open for: ${email}`);
    }
}

// Delete admin item function
function deleteAdminItem(uploadId) {
    console.log('Delete admin item called with uploadId:', uploadId);

    if (confirm('Are you sure you want to delete this item? This action cannot be undone and will remove all associated images.')) {
        // Show loading state
        const deleteButton = event.target.closest('button');
        const originalContent = deleteButton.innerHTML;
        deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Deleting...';
        deleteButton.disabled = true;

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('CSRF Token:', csrfToken);

        if (!csrfToken) {
            showNotification('CSRF token not found. Please refresh the page.', 'error');
            deleteButton.innerHTML = originalContent;
            deleteButton.disabled = false;
            return;
        }

        // Make delete request
        const deleteUrl = `/admin/reported-items/${encodeURIComponent(uploadId)}`;
        console.log('Delete URL:', deleteUrl);
        console.log('Original uploadId:', uploadId);

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (response.status === 405) {
                showNotification('Method not allowed. Please check the URL and try again.', 'error');
                deleteButton.innerHTML = originalContent;
                deleteButton.disabled = false;
                return;
            }
            if (response.status === 419) {
                showNotification('CSRF token mismatch. Please refresh the page and try again.', 'error');
                deleteButton.innerHTML = originalContent;
                deleteButton.disabled = false;
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                // Remove the item card from the DOM
                const itemCard = document.querySelector(`[data-upload-id="${uploadId}"]`);
                if (itemCard) {
                    itemCard.remove();
                }

                // Show success message
                showNotification('Item deleted successfully', 'success');

                // Update stats if needed
                updateStats();
            } else {
                showNotification(data?.message || 'Failed to delete item', 'error');
                // Restore button state
                deleteButton.innerHTML = originalContent;
                deleteButton.disabled = false;
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            showNotification('An error occurred while deleting the item', 'error');
            // Restore button state
            deleteButton.innerHTML = originalContent;
            deleteButton.disabled = false;
        });
    }
}

// Show notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' :
        type === 'error' ? 'bg-red-100 text-red-800 border border-red-200' :
        'bg-blue-100 text-blue-800 border border-blue-200'
    }`;

    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} mr-2"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Update stats function
function updateStats() {
    // This would typically reload the page or update stats via AJAX
    // For now, we'll just reload the page to get updated stats
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

// Close modals when clicking outside
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

document.getElementById('itemDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeItemDetailsModal();
    }
});

// Carousel functionality
let carouselStates = {};

function initializeCarousel(uploadId, totalImages) {
    carouselStates[uploadId] = {
        currentSlide: 0,
        totalSlides: totalImages
    };
    updateCarouselDots(uploadId);
}

function nextSlide(uploadId) {
    const state = carouselStates[uploadId];
    if (!state) return;

    state.currentSlide = (state.currentSlide + 1) % state.totalSlides;
    updateCarouselPosition(uploadId);
    updateCarouselDots(uploadId);
    updateCarouselCounter(uploadId);
}

function previousSlide(uploadId) {
    const state = carouselStates[uploadId];
    if (!state) return;

    state.currentSlide = state.currentSlide === 0 ? state.totalSlides - 1 : state.currentSlide - 1;
    updateCarouselPosition(uploadId);
    updateCarouselDots(uploadId);
    updateCarouselCounter(uploadId);
}

function goToSlide(uploadId, slideIndex) {
    const state = carouselStates[uploadId];
    if (!state) return;

    state.currentSlide = slideIndex;
    updateCarouselPosition(uploadId);
    updateCarouselDots(uploadId);
    updateCarouselCounter(uploadId);
}

function updateCarouselPosition(uploadId) {
    const carousel = document.getElementById(`carousel-${uploadId}`);
    const state = carouselStates[uploadId];
    if (!carousel || !state) return;

    const translateX = -state.currentSlide * 100;
    carousel.style.transform = `translateX(${translateX}%)`;
}

function updateCarouselDots(uploadId) {
    const state = carouselStates[uploadId];
    if (!state) return;

    for (let i = 0; i < state.totalSlides; i++) {
        const dot = document.getElementById(`dot-${uploadId}-${i}`);
        if (dot) {
            dot.className = i === state.currentSlide
                ? 'carousel-dot w-2 h-2 rounded-full bg-purple-primary transition-colors'
                : 'carousel-dot w-2 h-2 rounded-full bg-gray-300 hover:bg-gray-400 transition-colors';
        }
    }
}

function updateCarouselCounter(uploadId) {
    const state = carouselStates[uploadId];
    if (!state) return;

    const counter = document.getElementById(`counter-${uploadId}`);
    if (counter) {
        counter.textContent = `${state.currentSlide + 1} of ${state.totalSlides}`;
    }
}

// Initialize carousels on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize carousels for items with multiple images
    const carousels = document.querySelectorAll('[id^="carousel-"]');
    carousels.forEach(carousel => {
        const uploadId = carousel.id.replace('carousel-', '');
        const slides = carousel.querySelectorAll('.carousel-slide');
        if (slides.length > 1) {
            initializeCarousel(uploadId, slides.length);
        }
    });
});
</script>
@endsection
