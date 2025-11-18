@extends('layouts.user')

@section('title', 'Claim and Verify - FindITFast')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Claim and Verify</h2>
                <p class="text-gray-600">Browse items posted by other users and claim items that belong to you.</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <div class="text-2xl font-bold text-purple-600" id="total-items-count">0</div>
                    <div class="text-sm text-gray-500">Total Items</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0 lg:space-x-4">
            <!-- Search -->
            <div class="flex-1">
                <div class="relative">
                    <input type="text" id="search-input" placeholder="Search items by description, location, or tags..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex items-center space-x-4">
                <select id="type-filter" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Types</option>
                    <option value="lost">Lost Items</option>
                    <option value="found">Found Items</option>
                </select>

                <button onclick="resetFilters()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
                    <i class="fas fa-times mr-1"></i>
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Items Grid -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Available Items</h3>
            <p class="text-sm text-gray-500 mt-1">Items posted by other users that you can claim</p>
        </div>

        <div class="p-6">
            <!-- Loading State -->
            <div id="loading-state" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                <p class="mt-2 text-gray-500">Loading items...</p>
            </div>

            <!-- Items List -->
            <div id="other-users-items-list" class="hidden">
                <!-- Items will be loaded here -->
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="text-center py-12 hidden">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Items Found</h3>
                <p class="text-gray-500">No items match your current search criteria.</p>
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
let allItems = [];
let filteredItems = [];

// Load items from other users
async function loadOtherUsersItems() {
    try {
        const response = await fetch('/api/items/other-users', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            allItems = data.items;
            filteredItems = [...allItems];
            displayOtherUsersItems(filteredItems);
            updateStats();
        } else {
            console.error('Failed to load other users items:', data.message);
            showEmptyState();
        }
    } catch (error) {
        console.error('Error loading other users items:', error);
        showEmptyState();
    }
}

function displayOtherUsersItems(items) {
    const itemsContainer = document.getElementById('other-users-items-list');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');

    if (!itemsContainer) return;

    // Hide loading state
    loadingState.classList.add('hidden');
    itemsContainer.classList.remove('hidden');

    if (items.length === 0) {
        itemsContainer.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }

    emptyState.classList.add('hidden');

    itemsContainer.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            ${items.map(item => `
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
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
                                <div class="flex items-center space-x-2">
                                    <div class="flex items-center gap-2">
                                        ${item.uploader_profile_picture ? `
                                            <img src="${item.uploader_profile_picture}" alt="${item.uploader_name}" class="w-6 h-6 rounded-full object-cover border border-purple-100">
                                        ` : `
                                            <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center text-xs font-semibold text-purple-600">
                                                ${item.uploader_name.substring(0, 2).toUpperCase()}
                                            </div>
                                        `}
                                        <div class="flex items-center gap-1">
                                            <span class="text-sm font-medium text-gray-700">${item.uploader_name}</span>
                                            ${item.uploader_verified ? `
                                                <span class="inline-flex items-center justify-center w-4 h-4" title="Verified Profile">
                                                    <img src="/images/icons/verify.png" alt="Verified" class="w-4 h-4">
                                                </span>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
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
                                <div class="carousel-track flex transition-transform duration-300 ease-in-out" id="carousel-claim-${item.upload_id}">
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
                                    <button onclick="previousSlide('claim-${item.upload_id}')" class="flex items-center justify-center w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
                                        <i class="fas fa-chevron-left text-gray-600"></i>
                                    </button>

                                    <div class="flex items-center space-x-2">
                                        <div class="flex space-x-1">
                                            ${item.images.map((_, index) => `
                                                <button onclick="goToSlide('claim-${item.upload_id}', ${index})"
                                                        class="carousel-dot w-2 h-2 rounded-full bg-gray-300 transition-colors"
                                                        id="dot-claim-${item.upload_id}-${index}"></button>
                                            `).join('')}
                                        </div>
                                        <span class="carousel-counter text-sm text-gray-500 ml-2" id="counter-claim-${item.upload_id}">1 / ${item.images.length}</span>
                                    </div>

                                    <button onclick="nextSlide('claim-${item.upload_id}')" class="flex items-center justify-center w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
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
                            <div class="flex space-x-2">
                                <button onclick="messageAboutItem('${item.upload_id}', '${item.description || ''}', '${item.item_type || ''}', '${item.location || ''}')" class="px-4 py-2 bg-purple-100 text-purple-800 rounded-lg hover:bg-purple-200 transition-colors text-sm font-medium">
                                    <i class="fas fa-comments mr-1"></i>
                                    Message Owner
                                </button>
                                ${item.user_has_claimed ? `
                                    <button onclick="cancelClaim('${item.upload_id}')" class="px-4 py-2 bg-red-100 text-red-800 rounded-lg hover:bg-red-200 transition-colors text-sm font-medium">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Cancel Claim
                                    </button>
                                ` : (item.claim_status === 'pending' ? `
                                    <button disabled class="px-4 py-2 bg-gray-100 text-gray-500 rounded-lg cursor-not-allowed text-sm font-medium">
                                        <i class="fas fa-hourglass-half mr-1"></i>
                                        Pending Verification
                                    </button>
                                ` : `
                                    <button onclick="claimItem('${item.upload_id}')" class="px-4 py-2 bg-green-100 text-green-800 rounded-lg hover:bg-green-200 transition-colors text-sm font-medium">
                                        <i class="fas fa-hand-holding mr-1"></i>
                                        Claim Item
                                    </button>
                                `)}
                            </div>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

    // Initialize carousels
    items.forEach(item => {
        if (item.images.length > 1) {
            initializeCarousel(`claim-${item.upload_id}`, item.images.length);
        }
    });
}

function updateStats() {
    document.getElementById('total-items-count').textContent = allItems.length;
}

function showEmptyState() {
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('other-users-items-list').classList.add('hidden');
    document.getElementById('empty-state').classList.remove('hidden');
}

// Filter functions
function filterItems() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const typeFilter = document.getElementById('type-filter').value;

    filteredItems = allItems.filter(item => {
        const matchesSearch = !searchTerm ||
            item.description.toLowerCase().includes(searchTerm) ||
            item.location.toLowerCase().includes(searchTerm) ||
            (item.tags && item.tags.some(tag => tag.toLowerCase().includes(searchTerm)));

        const matchesType = !typeFilter || item.item_type === typeFilter;

        return matchesSearch && matchesType;
    });

    displayOtherUsersItems(filteredItems);
}

function resetFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('type-filter').value = '';
    filteredItems = [...allItems];
    displayOtherUsersItems(filteredItems);
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
    const item = allItems.find(item => item.upload_id === uploadId);
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
                <h4 class="font-semibold text-gray-900">Posted By</h4>
                <div class="flex items-center gap-2">
                    <p class="text-gray-600">${item.uploader_name}</p>
                    ${item.uploader_verified ? `
                        <span class="inline-flex items-center justify-center w-5 h-5" title="Verified Profile">
                            <img src="/images/icons/verify.png" alt="Verified" class="w-5 h-5">
                        </span>
                    ` : ''}
                </div>
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

// Message about item function
function messageAboutItem(uploadId, description, itemType, location) {
    // Get the item owner's email from the item data
    const item = allItems.find(item => item.upload_id === uploadId);
    if (!item) {
        alert('Item not found');
        return;
    }

    // Get the item owner's user ID
    const itemOwnerEmail = item.uploader_email;

    // Find the user ID for the item owner
    fetch('/chat/get-user-by-email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            email: itemOwnerEmail
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Store item context for the chat
            const itemContext = {
                uploadId: uploadId,
                description: description,
                itemType: itemType,
                location: location,
                ownerEmail: itemOwnerEmail,
                uploader_name: item.uploader_name,
                images: item.images,
                tags: item.tags
            };

            // Store in sessionStorage for the chat page
            sessionStorage.setItem('chatItemContext', JSON.stringify(itemContext));

            // Redirect to chat with the item owner
            window.location.href = `/chat?user=${data.user.id}&item=${uploadId}`;
        } else {
            alert('Could not find the item owner');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error opening chat');
    });
}

// Claim item function
async function claimItem(uploadId) {
    if (!confirm('Are you sure you want to claim this item? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`/api/items/${uploadId}/claim`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast('Item claimed successfully!', 'success');
            // Reload items to update the button to "Cancel Claim"
            loadOtherUsersItems();
        } else {
            showToast(data.error || 'Error claiming item. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error claiming item. Please try again.', 'error');
    }
}

// Cancel claim function
async function cancelClaim(uploadId) {
    if (!confirm('Are you sure you want to cancel your claim on this item?')) {
        return;
    }

    try {
        const response = await fetch(`/api/items/${uploadId}/cancel-claim`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast('Claim cancelled successfully!', 'success');
            // Reload items to update the button back to "Claim Item"
            loadOtherUsersItems();
        } else {
            showToast(data.error || 'Error cancelling claim. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error cancelling claim. Please try again.', 'error');
    }
}

// Toast notification function
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

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    loadOtherUsersItems();

    // Search and filter event listeners
    document.getElementById('search-input').addEventListener('input', filterItems);
    document.getElementById('type-filter').addEventListener('change', filterItems);

    // Close modal when clicking outside
    document.getElementById('image-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeImageModal();
        }
    });
});
</script>
@endsection
