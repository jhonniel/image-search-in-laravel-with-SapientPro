@extends('layouts.user')

@section('title', 'Claim and Verify - FindITFast')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Claim and Verify</h2>
                <p class="text-gray-600">
                    These available items are automatically matched to your reported items using image and text similarity.
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <div class="text-2xl font-bold text-purple-600" id="total-items-count">0</div>
                    <div class="text-sm text-gray-500">Matched Available Items</div>
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
            <p class="text-sm text-gray-500 mt-1">Items that match your reported items (based on similarity)</p>
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
// This function is called automatically when the page loads
// It checks for similar items, creates notifications, and displays matches
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
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg flex items-center space-x-2">
            <i class="fas fa-check-circle text-green-600"></i>
            <p class="text-sm text-green-800 font-medium">
                Similar items found! These available items match your reported items based on image and text similarity.
            </p>
        </div>
        <div class="grid grid-cols-1 gap-6">
            ${items.map(item => `
                <!-- Similarity Match Container -->
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg shadow-sm border-2 border-purple-200 overflow-hidden">
                    <!-- Similarity Header -->
                    <div class="p-4 bg-purple-100 border-b border-purple-200">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center">
                                    <i class="fas fa-link text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-purple-900">Similarity Match Found</h3>
                                    <p class="text-sm text-purple-700">Match Score: <span class="font-bold text-lg">${item.similarity_score}%</span></p>
                                </div>
                            </div>
                            ${item.matched_with_upload_id ? `
                                <div class="text-xs text-purple-600 bg-white px-3 py-1 rounded-full">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Matched with your item: ${item.matched_with_upload_id}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <!-- Side by Side Items -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 p-4">
                        <!-- User's Matched Item (Left Side) -->
                        ${item.user_matched_item ? `
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex flex-col">
                                <div class="p-4 bg-blue-50 border-b border-blue-200 flex-shrink-0">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-user text-blue-600"></i>
                                        <h4 class="font-semibold text-blue-900">Your Reported Item</h4>
                                    </div>
                                </div>
                                <div class="p-4 flex flex-col flex-1 min-h-0">
                                    <div class="mb-3 flex-shrink-0">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium ${item.user_matched_item.item_type === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                                            ${item.user_matched_item.item_type === 'lost' ? 'Lost' : 'Found'}
                                        </span>
                                    </div>
                                    <p class="text-gray-700 mb-2 text-sm flex-shrink-0"><strong>Description:</strong> ${item.user_matched_item.description || 'No description provided'}</p>
                                    <p class="text-gray-700 mb-2 text-sm flex-shrink-0"><strong>Location:</strong> ${item.user_matched_item.location || 'No location specified'}</p>
                                    ${item.user_matched_item.tags && item.user_matched_item.tags.length > 0 ? `
                                        <div class="flex flex-wrap gap-2 mb-3 flex-shrink-0">
                                            <strong class="text-gray-700 text-sm">Tags:</strong>
                                            ${item.user_matched_item.tags.map(tag => `<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">${tag}</span>`).join('')}
                                        </div>
                                    ` : ''}
<<<<<<< HEAD
                                    ${(() => {
                                        let objectsArray = [];
                                        if (item.user_matched_item && item.user_matched_item.detected_objects) {
                                            if (Array.isArray(item.user_matched_item.detected_objects)) {
                                                objectsArray = item.user_matched_item.detected_objects;
                                            } else if (typeof item.user_matched_item.detected_objects === 'string') {
                                                try {
                                                    objectsArray = JSON.parse(item.user_matched_item.detected_objects);
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
                                        
                                        if (uniqueObjects.length > 0) {
                                            const top5Objects = uniqueObjects.slice(0, 5);
                                            const objectsDisplay = top5Objects.map(obj => {
                                                const objName = (obj && typeof obj === 'object' ? obj.name : obj) || '';
                                                const score = (obj && typeof obj === 'object' && obj.score) ? (obj.score * 100).toFixed(0) : '';
                                                return `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium" title="Detected by Google Vision API${score ? ' (' + score + '% confidence)' : ''}"><i class="fas fa-eye mr-1"></i>${objName}</span>`;
                                            }).join('');
                                            const moreHtml = uniqueObjects.length > 5 ? `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">+${uniqueObjects.length - 5} more</span>` : '';
                                            return `<div class="mb-3 flex-shrink-0">
                                                <strong class="text-gray-700 text-sm flex items-center mb-1">
                                                    <i class="fas fa-cube mr-1 text-blue-600"></i>
                                                    Detected Objects:
                                                </strong>
                                                <div class="flex flex-wrap gap-2">${objectsDisplay}${moreHtml}</div>
                                            </div>`;
                                        }
                                        return '';
                                    })()}
=======
                                    ${item.user_matched_item.detected_objects && item.user_matched_item.detected_objects.length > 0 ? `
                                        <div class="mb-3 flex-shrink-0">
                                            <strong class="text-gray-700 text-sm flex items-center mb-1">
                                                <i class="fas fa-cube mr-1 text-blue-600"></i>
                                                Detected Objects (${item.user_matched_item.detected_objects.length}):
                                            </strong>
                                            <div class="flex flex-wrap gap-2">
                                                ${item.user_matched_item.detected_objects.slice(0, 5).map(obj => {
                                                    const objName = obj.name || obj;
                                                    const score = obj.score ? (obj.score * 100).toFixed(0) : '';
                                                    return `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium" title="Detected by Google Vision API${score ? ' (' + score + '% confidence)' : ''}"><i class="fas fa-eye mr-1"></i>${objName}</span>`;
                                                }).join('')}
                                                ${item.user_matched_item.detected_objects.length > 5 ? `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">+${item.user_matched_item.detected_objects.length - 5} more</span>` : ''}
                                            </div>
                                        </div>
                                    ` : ''}
>>>>>>> a1d2f199b93cbeb9d643c654a733f156406a02af
                                    ${item.user_matched_item.images && item.user_matched_item.images.length > 0 ? `
                                        <div class="mt-3 flex-1 min-h-0 flex flex-col">
                                            <div class="relative flex-1 min-h-0">
                                                <div class="carousel-container overflow-hidden rounded-lg h-full">
                                                    <div class="carousel-track flex transition-transform duration-300 ease-in-out h-full" id="carousel-user-${item.upload_id}">
                                                        ${item.user_matched_item.images.map((image, index) => `
                                                            <div class="carousel-slide flex-shrink-0 w-full h-full">
                                                                <div class="relative group w-full h-full" style="background-color: #f3f4f6;">
                                                                    <img src="${image.path || image.file_path || ''}" 
                                                                         alt="${image.original_name || 'Item image'}" 
                                                                         class="w-full h-full object-contain rounded-lg border border-gray-200 cursor-pointer"
                                                                         style="background-color: #f3f4f6; width: 100%; height: 100%; object-fit: contain; display: block; position: relative; z-index: 1;"
                                                                         onclick="viewImage('${image.path || image.file_path || ''}')"
                                                                         onerror="console.error('Image failed to load:', this.src); this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                                         onload="console.log('Image loaded successfully:', this.src); this.style.backgroundColor='transparent'; this.style.opacity='1';"
                                                                         loading="lazy">
                                                                    <div class="hidden w-full h-full bg-gray-100 rounded-lg border border-gray-200 items-center justify-center">
                                                                        <div class="text-center text-gray-400">
                                                                            <i class="fas fa-image text-4xl mb-2"></i>
                                                                            <p class="text-sm">Image not available</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        `).join('')}
                                                    </div>
                                                </div>

                                                ${item.user_matched_item.images.length > 1 ? `
                                                    <!-- Carousel Navigation -->
                                                    <div class="flex items-center justify-between mt-4 flex-shrink-0">
                                                        <button onclick="previousSlide('user-${item.upload_id}')" class="flex items-center justify-center w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
                                                            <i class="fas fa-chevron-left text-gray-600"></i>
                                                        </button>

                                                        <div class="flex items-center space-x-2">
                                                            <div class="flex space-x-1">
                                                                ${item.user_matched_item.images.map((_, index) => `
                                                                    <button onclick="goToSlide('user-${item.upload_id}', ${index})"
                                                                            class="carousel-dot w-2 h-2 rounded-full bg-gray-300 transition-colors"
                                                                            id="dot-user-${item.upload_id}-${index}"></button>
                                                                `).join('')}
                                                            </div>
                                                            <span class="carousel-counter text-sm text-gray-500 ml-2" id="counter-user-${item.upload_id}">1 / ${item.user_matched_item.images.length}</span>
                                                        </div>

                                                        <button onclick="nextSlide('user-${item.upload_id}')" class="flex items-center justify-center w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
                                                            <i class="fas fa-chevron-right text-gray-600"></i>
                                                        </button>
                                                    </div>
                                                ` : ''}
                                            </div>
                                        </div>
                                    ` : `
                                        <div class="mt-3 flex-1 min-h-0">
                                            <div class="relative h-full">
                                                <div class="carousel-container overflow-hidden rounded-lg h-full">
                                                    <div class="w-full h-full bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                                                        <div class="text-center text-gray-400">
                                                            <i class="fas fa-image text-4xl mb-2"></i>
                                                            <p class="text-sm">No image available</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `}
                                </div>
                            </div>
                        ` : ''}
                        
                        <!-- Matched Item from Other User (Right Side) -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex flex-col">
                    <!-- Item Header -->
                    <div class="p-6 border-b border-gray-200 flex-shrink-0">
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

                        <div class="mb-4 flex-shrink-0">
                            ${item.similarity_score ? `
                                <div class="mb-3 p-2 bg-purple-50 border border-purple-200 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-semibold text-purple-700">
                                            <i class="fas fa-percentage mr-1"></i>Match Score
                                        </span>
                                        <span class="text-lg font-bold text-purple-600">${item.similarity_score}%</span>
                                    </div>
                                </div>
                            ` : ''}
                            ${item.matched_with_upload_id ? `
                                <p class="text-xs text-gray-500 mb-2">
                                    <i class="fas fa-link mr-1"></i>
                                    Matched with your reported item ID: 
                                    <span class="font-semibold text-gray-700">${item.matched_with_upload_id}</span>
                                </p>
                            ` : ''}
                            <p class="text-gray-700 mb-2 flex-shrink-0"><strong>Description:</strong> ${item.description || 'No description provided'}</p>
                            <p class="text-gray-700 mb-2 flex-shrink-0"><strong>Location:</strong> ${item.location || 'No location specified'}</p>
                            ${item.tags && item.tags.length > 0 ? `
                                <div class="flex flex-wrap gap-2 mb-2 flex-shrink-0">
                                    <strong class="text-gray-700">Tags:</strong>
                                    ${item.tags.map(tag => `<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">${tag}</span>`).join('')}
                                </div>
                            ` : ''}
<<<<<<< HEAD
                            ${(() => {
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
                                
                                if (uniqueObjects.length > 0) {
                                    const top5Objects = uniqueObjects.slice(0, 5);
                                    const objectsDisplay = top5Objects.map(obj => {
                                        const objName = (obj && typeof obj === 'object' ? obj.name : obj) || '';
                                        const score = (obj && typeof obj === 'object' && obj.score) ? (obj.score * 100).toFixed(0) : '';
                                        return `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium" title="Detected by Google Vision API${score ? ' (' + score + '% confidence)' : ''}"><i class="fas fa-eye mr-1"></i>${objName}</span>`;
                                    }).join('');
                                    const moreHtml = uniqueObjects.length > 5 ? `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">+${uniqueObjects.length - 5} more</span>` : '';
                                    return `<div class="mb-2 flex-shrink-0">
                                        <strong class="text-gray-700 flex items-center mb-1">
                                            <i class="fas fa-cube mr-1 text-blue-600"></i>
                                            Detected Objects:
                                        </strong>
                                        <div class="flex flex-wrap gap-2">${objectsDisplay}${moreHtml}</div>
                                    </div>`;
                                }
                                return '';
                            })()}
=======
                            ${item.detected_objects && item.detected_objects.length > 0 ? `
                                <div class="mb-2 flex-shrink-0">
                                    <strong class="text-gray-700 flex items-center mb-1">
                                        <i class="fas fa-cube mr-1 text-blue-600"></i>
                                        Detected Objects (${item.detected_objects.length}):
                                    </strong>
                                    <div class="flex flex-wrap gap-2">
                                        ${item.detected_objects.slice(0, 5).map(obj => {
                                            const objName = obj.name || obj;
                                            const score = obj.score ? (obj.score * 100).toFixed(0) : '';
                                            return `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium" title="Detected by Google Vision API${score ? ' (' + score + '% confidence)' : ''}"><i class="fas fa-eye mr-1"></i>${objName}</span>`;
                                        }).join('')}
                                        ${item.detected_objects.length > 5 ? `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">+${item.detected_objects.length - 5} more</span>` : ''}
                                    </div>
                                </div>
                            ` : ''}
>>>>>>> a1d2f199b93cbeb9d643c654a733f156406a02af
                        </div>

                        <div class="text-sm text-gray-500 flex-shrink-0">
                            <i class="fas fa-clock mr-1"></i>
                            Reported ${new Date(item.created_at).toLocaleDateString()}
                        </div>
                    </div>

                    <!-- Images Carousel -->
                    <div class="p-6 flex-1 min-h-0 flex flex-col">
                        <div class="relative flex-1 min-h-0 flex flex-col">
                            <div class="carousel-container overflow-hidden rounded-lg flex-1 min-h-0">
                                <div class="carousel-track flex transition-transform duration-300 ease-in-out h-full" id="carousel-claim-${item.upload_id}">
                                    ${item.images && item.images.length > 0 ? item.images.map((image, index) => `
                                        <div class="carousel-slide flex-shrink-0 w-full h-full">
                                            <div class="relative group w-full h-full" style="background-color: #f3f4f6;">
                                                <img src="${image.path || image.file_path || ''}" 
                                                     alt="${image.original_name || 'Item image'}" 
                                                     class="w-full h-full object-contain rounded-lg border border-gray-200 cursor-pointer"
                                                     style="background-color: #f3f4f6; width: 100%; height: 100%; object-fit: contain; display: block; position: relative; z-index: 1;"
                                                     onclick="viewImage('${image.path || image.file_path || ''}')"
                                                     onerror="console.error('Image failed to load:', this.src); this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                     onload="console.log('Image loaded successfully:', this.src); this.style.backgroundColor='transparent'; this.style.opacity='1';"
                                                     loading="lazy">
                                                <div class="hidden w-full h-full bg-gray-100 rounded-lg border border-gray-200 items-center justify-center">
                                                    <div class="text-center text-gray-400">
                                                        <i class="fas fa-image text-4xl mb-2"></i>
                                                        <p class="text-sm">Image not available</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `).join('') : `
                                        <div class="carousel-slide flex-shrink-0 w-full h-full">
                                            <div class="relative group w-full h-full">
                                                <div class="w-full h-full bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
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
                                <div class="flex items-center justify-between mt-4 flex-shrink-0">
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
                                ${(() => {
                                    // Determine if user can claim based on item types
                                    // User with LOST item can CLAIM FOUND items
                                    // User with FOUND item can only MESSAGE (not claim) LOST items
                                    const userItemType = item.user_matched_item?.item_type || '';
                                    const matchedItemType = item.item_type || '';
                                    const canClaim = userItemType === 'lost' && matchedItemType === 'found';
                                    
                                    if (item.user_has_claimed) {
                                        return `<button onclick="cancelClaim('${item.upload_id}')" class="px-4 py-2 bg-red-100 text-red-800 rounded-lg hover:bg-red-200 transition-colors text-sm font-medium">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Cancel Claim
                                        </button>`;
                                    } else if (item.claim_status === 'verified') {
                                        return `<button disabled class="px-4 py-2 bg-blue-100 text-blue-800 rounded-lg cursor-not-allowed text-sm font-medium">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Already Claimed & Verified
                                        </button>`;
                                    } else if (item.claim_status === 'pending') {
                                        return `<button disabled class="px-4 py-2 bg-gray-100 text-gray-500 rounded-lg cursor-not-allowed text-sm font-medium">
                                        <i class="fas fa-hourglass-half mr-1"></i>
                                        Pending Verification
                                        </button>`;
                                    } else if (!canClaim) {
                                        // User has FOUND item, matched item is LOST - can only message, not claim
                                        return `<button disabled class="px-4 py-2 bg-gray-100 text-gray-500 rounded-lg cursor-not-allowed text-sm font-medium" title="You can only message the owner to notify them that you found their lost item">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Message to Notify
                                        </button>`;
                                    } else {
                                        // User has LOST item, matched item is FOUND - can claim
                                        return `<button onclick="claimItem('${item.upload_id}')" class="px-4 py-2 bg-green-100 text-green-800 rounded-lg hover:bg-green-200 transition-colors text-sm font-medium">
                                        <i class="fas fa-hand-holding mr-1"></i>
                                        Claim Item
                                        </button>`;
                                    }
                                })()}
                            </div>
                        </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

    // Initialize carousels
    items.forEach(item => {
        // Initialize matched item carousel
        if (item.images && item.images.length > 1) {
            initializeCarousel(`claim-${item.upload_id}`, item.images.length);
        }
        // Initialize user's matched item carousel
        if (item.user_matched_item && item.user_matched_item.images && item.user_matched_item.images.length > 1) {
            initializeCarousel(`user-${item.upload_id}`, item.user_matched_item.images.length);
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

    // Build a safe image URL for the modal
    let firstImageUrl = '';
    if (item.images && item.images.length > 0) {
        const img = item.images[0];
        if (img.path) {
            firstImageUrl = img.path.startsWith('/') ? img.path : '/' + img.path;
        } else if (img.file_path) {
            firstImageUrl = img.file_path.startsWith('/') ? img.file_path : '/' + img.file_path;
        } else if (img.filename) {
            firstImageUrl = '/storage/' + img.filename;
        }
    }

    const escapedFirstImageUrl = firstImageUrl.replace(/'/g, "\\'").replace(/"/g, '&quot;');
    const escapedDescription = (item.description || 'Item image').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    const content = `
        <div class="space-y-4">
            ${firstImageUrl ? `
                <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Image</h4>
                    <div class="relative w-full h-64 bg-gray-100 rounded-lg overflow-hidden">
                        <img
                            src="${firstImageUrl}"
                            alt="${escapedDescription}"
                            class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity"
                            onclick="viewImage('${escapedFirstImageUrl}')"
                        >
                        ${item.images && item.images.length > 1 ? `
                            <div class="absolute bottom-2 right-2 bg-black bg-opacity-60 text-white text-xs px-2 py-1 rounded">
                                <i class="fas fa-images mr-1"></i>${item.images.length} images
                            </div>
                        ` : ''}
                    </div>
                </div>
            ` : ''}
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
<<<<<<< HEAD
                    <h4 class="font-semibold text-gray-900 mb-2">Tags</h4>
=======
                    <h4 class="font-semibold text-gray-900">Tags</h4>
>>>>>>> a1d2f199b93cbeb9d643c654a733f156406a02af
                    <div class="flex flex-wrap gap-2">
                        ${item.tags.map(tag => `<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">${tag}</span>`).join('')}
                    </div>
                </div>
            ` : ''}
<<<<<<< HEAD
            ${(() => {
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
                
                if (uniqueObjects.length > 0) {
                    const top5Objects = uniqueObjects.slice(0, 5);
                    const objectsDisplay = top5Objects.map(obj => {
                        const objName = (obj && typeof obj === 'object' ? obj.name : obj) || '';
                        const score = (obj && typeof obj === 'object' && obj.score) ? (obj.score * 100).toFixed(0) : '';
                        return `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium" title="Detected by Google Vision API${score ? ' (' + score + '% confidence)' : ''}"><i class="fas fa-eye mr-1"></i>${objName}</span>`;
                    }).join('');
                    const moreHtml = uniqueObjects.length > 5 ? `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">+${uniqueObjects.length - 5} more</span>` : '';
                    return `<div>
                        <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-cube mr-1 text-blue-600"></i>
                            Detected Objects
                        </h4>
                        <div class="flex flex-wrap gap-2">${objectsDisplay}${moreHtml}</div>
                    </div>`;
                }
                return '';
            })()}
=======
>>>>>>> a1d2f199b93cbeb9d643c654a733f156406a02af
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
            showToast('Item claimed successfully! Redirecting to chat...', 'success');
            
            // Redirect to chat with the item owner
            if (data.owner_id && data.upload_id) {
                setTimeout(() => {
                    window.location.href = `/chat?user=${data.owner_id}&item=${data.upload_id}`;
                }, 1000);
            } else {
                // Fallback: reload items if redirect info not available
            loadOtherUsersItems();
            }
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
