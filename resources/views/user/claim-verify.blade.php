@extends('layouts.user')

@section('title', 'Claim and Verify - FindITFast')

@section('content')
<div class="user-page">
    @include('user.partials.page-header', [
        'eyebrow' => 'Matching',
        'title' => 'Claim and verify',
        'description' => 'Items automatically matched to your reports using image and text similarity.',
        'actions' => '<div class="text-right shrink-0"><div class="text-2xl font-bold text-purple-600" id="total-items-count">0</div><div class="text-xs text-gray-500 sm:text-sm">Matched items</div></div>',
    ])

    <div class="user-card">
        <div class="user-card-header flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h3 class="user-card-title">Matched items</h3>
                <p class="user-card-subtitle">Compare your item with similar listings, then claim or message the owner</p>
            </div>
            <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:justify-end">
                <div class="relative min-w-0 flex-1 sm:w-56 sm:flex-none">
                    <input type="text" id="search-input" placeholder="Search matches…"
                           class="user-input !py-2 pl-9 text-sm">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                </div>
                <select id="type-filter" class="user-input !py-2 !w-auto text-sm">
                    <option value="">All types</option>
                    <option value="lost">Lost</option>
                    <option value="found">Found</option>
                </select>
                <button type="button" onclick="resetFilters()" class="user-btn-ghost !py-2 text-sm" title="Clear filters">
                    <i class="fas fa-undo text-xs"></i>
                    <span class="hidden sm:inline">Reset</span>
                </button>
            </div>
        </div>
        <div class="user-card-body !p-3 sm:!p-5">
            <div id="loading-state" class="text-center py-12">
                <div class="inline-block h-8 w-8 animate-spin rounded-full border-2 border-purple-200 border-t-purple-600"></div>
                <p class="mt-3 text-sm text-gray-500">Finding matches…</p>
            </div>

            <div id="other-users-items-list" class="hidden cv-compare-list"></div>

            <div id="empty-state" class="hidden py-12 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900">No matches yet</h3>
                <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">When similar lost or found items appear, they’ll show up here for comparison.</p>
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

<!-- Report Item Modal -->
<div id="report-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-flag text-red-500 mr-2"></i>Report this item
            </h3>
            <button type="button" onclick="closeReportModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="report-item-form" class="p-4 sm:p-6 space-y-4" onsubmit="submitItemReport(event)">
            <input type="hidden" id="report-upload-id" name="upload_id" value="">
            <p class="text-sm text-gray-600">Help us keep FindITFast safe. Choose a reason and explain what you noticed.</p>

            <div>
                <label for="report-label" class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-red-500">*</span></label>
                <select id="report-label" name="label" required class="user-input">
                    <option value="">Select a reason…</option>
                    <option value="scam">Scam</option>
                    <option value="fake">Fake / Misleading</option>
                    <option value="inappropriate">Inappropriate Content</option>
                    <option value="spam">Spam</option>
                    <option value="stolen">Stolen Goods</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div>
                <label for="report-explanation" class="block text-sm font-medium text-gray-700 mb-1">Explanation <span class="text-red-500">*</span></label>
                <textarea id="report-explanation" name="explanation" rows="4" required minlength="10" maxlength="2000"
                          class="user-input"
                          placeholder="Describe why this listing looks like a scam or why you selected that reason…"></textarea>
                <p class="text-xs text-gray-500 mt-1">Minimum 10 characters.</p>
            </div>

            <p id="report-form-error" class="hidden text-sm text-red-600"></p>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeReportModal()" class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-medium">
                    Cancel
                </button>
                <button type="submit" id="report-submit-btn" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm font-medium">
                    <i class="fas fa-paper-plane mr-1"></i> Submit report
                </button>
            </div>
        </form>
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

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function escapeJs(value) {
    return String(value ?? '')
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/\n/g, ' ')
        .replace(/\r/g, '');
}

function firstImagePath(images) {
    if (!images || !images.length) return '';
    return images[0].path || images[0].file_path || '';
}

function typeBadge(type) {
    const isLost = (type || '').toLowerCase() === 'lost';
    return `<span class="cv-compare-badge ${isLost ? 'cv-compare-badge-lost' : 'cv-compare-badge-found'}">${isLost ? 'Lost' : 'Found'}</span>`;
}

function tagsHtml(tags) {
    const list = Array.isArray(tags) ? tags.slice(0, 4) : [];
    if (!list.length) return '';
    return `<div class="cv-compare-tags">${list.map(t => `<span class="cv-compare-tag">${escapeHtml(t)}</span>`).join('')}</div>`;
}

function panelImage(images, alt) {
    const path = firstImagePath(images);
    const extra = images && images.length > 1 ? images.length - 1 : 0;
    if (!path) {
        return `<div class="cv-compare-image-wrap"><div class="cv-compare-image-empty"><i class="fas fa-image text-3xl"></i></div></div>`;
    }
    return `
        <div class="cv-compare-image-wrap">
            <img src="${escapeHtml(path)}" alt="${escapeHtml(alt || 'Item')}" class="cv-compare-image"
                 onclick="viewImage('${escapeJs(path)}')" loading="lazy"
                 onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.classList.add('flex');">
            <div class="cv-compare-image-empty hidden"><i class="fas fa-image text-3xl"></i></div>
            ${extra > 0 ? `<span class="absolute right-2 top-2 rounded-md bg-black/55 px-1.5 py-0.5 text-[10px] font-semibold text-white">+${extra}</span>` : ''}
        </div>
    `;
}

function yoursPanel(yours) {
    if (!yours) {
        return `
            <div class="cv-compare-panel">
                <div class="cv-compare-panel-label cv-compare-panel-label-yours">Yours</div>
                <p class="text-[11px] text-gray-400">No linked item</p>
            </div>
        `;
    }

    return `
        <div class="cv-compare-panel">
            <div class="cv-compare-panel-label cv-compare-panel-label-yours">Yours</div>
            ${typeBadge(yours.item_type)}
            <p class="cv-compare-desc">${escapeHtml(yours.description || 'No description')}</p>
            <p class="cv-compare-meta">${escapeHtml(yours.location || 'No location')}</p>
            ${panelImage(yours.images, yours.description)}
        </div>
    `;
}

function matchActions(item) {
    const userItemType = item.user_matched_item?.item_type || '';
    const matchedItemType = item.item_type || '';
    const canClaim = userItemType === 'lost' && matchedItemType === 'found';
    const id = escapeJs(item.upload_id);

    let claimBtn = '';
    if (item.user_has_claimed) {
        claimBtn = `<button type="button" onclick="cancelClaim('${id}')" class="cv-compare-btn-danger">Cancel</button>`;
    } else if (item.claim_status === 'verified') {
        claimBtn = `<button type="button" disabled class="cv-compare-btn-muted">Verified</button>`;
    } else if (item.claim_status === 'pending') {
        claimBtn = `<button type="button" disabled class="cv-compare-btn-muted">Pending</button>`;
    } else if (canClaim) {
        claimBtn = `<button type="button" onclick="claimItem('${id}')" class="cv-compare-btn-claim">Claim</button>`;
    } else {
        claimBtn = `<button type="button" disabled class="cv-compare-btn-muted">Notify</button>`;
    }

    return `
        <div class="cv-compare-actions">
            <button type="button" onclick="viewItemDetails('${id}')" class="cv-compare-btn-secondary" title="Details"><i class="fas fa-info-circle"></i></button>
            <button type="button" onclick="openReportModal('${id}')" class="cv-compare-btn-report" title="Report"><i class="fas fa-flag"></i></button>
            <button type="button" onclick="messageAboutItem('${id}', '${escapeJs(item.description || '')}', '${escapeJs(item.item_type || '')}', '${escapeJs(item.location || '')}')" class="cv-compare-btn-message">Message</button>
            ${claimBtn}
        </div>
    `;
}

function matchPanel(item) {
    const uploader = escapeHtml(item.uploader_name || 'Unknown');
    return `
        <div class="cv-compare-panel">
            <div class="cv-compare-panel-label cv-compare-panel-label-match">Match</div>
            ${typeBadge(item.item_type)}
            <p class="cv-compare-desc">${escapeHtml(item.description || 'No description')}</p>
            <p class="cv-compare-meta">${escapeHtml(item.location || 'No location')} · ${uploader}</p>
            ${panelImage(item.images, item.description)}
        </div>
    `;
}

function displayOtherUsersItems(items) {
    const itemsContainer = document.getElementById('other-users-items-list');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');

    if (!itemsContainer) return;

    loadingState.classList.add('hidden');
    itemsContainer.classList.remove('hidden');

    if (items.length === 0) {
        itemsContainer.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }

    emptyState.classList.add('hidden');

    itemsContainer.innerHTML = items.map(item => {
        const score = item.similarity_score != null ? `${Number(item.similarity_score)}%` : '—';

        return `
            <article class="cv-compare">
                <div class="cv-compare-header">
                    <div class="cv-compare-header-title">
                        <div class="cv-compare-header-icon"><i class="fas fa-link text-[10px]"></i></div>
                        <div class="min-w-0">
                            <h3 class="truncate text-xs font-bold text-purple-900">Similarity match</h3>
                            <p class="text-[10px] text-purple-700">Score <span class="font-bold">${escapeHtml(score)}</span></p>
                        </div>
                    </div>
                </div>
                <div class="cv-compare-panels">
                    ${yoursPanel(item.user_matched_item)}
                    ${matchPanel(item)}
                </div>
                ${matchActions(item)}
            </article>
        `;
    }).join('');
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
            (item.description || '').toLowerCase().includes(searchTerm) ||
            (item.location || '').toLowerCase().includes(searchTerm) ||
            (item.tags && item.tags.some(tag => String(tag).toLowerCase().includes(searchTerm)));

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
                    <h4 class="font-semibold text-gray-900 mb-2">Tags</h4>
                    <div class="flex flex-wrap gap-2">
                        ${item.tags.map(tag => `<span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">${tag}</span>`).join('')}
                    </div>
                </div>
            ` : ''}
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
                    const top3Objects = uniqueObjects.slice(0, 3);
                    const objectsDisplay = top3Objects.map(obj => {
                        const objName = (obj && typeof obj === 'object' ? obj.name : obj) || '';
                        const score = (obj && typeof obj === 'object' && obj.score) ? (obj.score * 100).toFixed(0) : '';
                        return `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium" title="Detected from image${score ? ' (' + score + '% confidence)' : ''}"><i class="fas fa-eye mr-1"></i>${objName}</span>`;
                    }).join('');
                    return `<div>
                        <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-cube mr-1 text-blue-600"></i>
                            Detected Objects (${top3Objects.length}):
                        </h4>
                        <div class="flex flex-wrap gap-2">${objectsDisplay}</div>
                    </div>`;
                }
                return '';
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

function openReportModal(uploadId) {
    document.getElementById('report-upload-id').value = uploadId;
    document.getElementById('report-label').value = '';
    document.getElementById('report-explanation').value = '';
    const err = document.getElementById('report-form-error');
    err.classList.add('hidden');
    err.textContent = '';
    document.getElementById('report-modal').classList.remove('hidden');
}

function closeReportModal() {
    document.getElementById('report-modal').classList.add('hidden');
}

async function submitItemReport(event) {
    event.preventDefault();

    const uploadId = document.getElementById('report-upload-id').value;
    const label = document.getElementById('report-label').value;
    const explanation = document.getElementById('report-explanation').value.trim();
    const err = document.getElementById('report-form-error');
    const btn = document.getElementById('report-submit-btn');

    err.classList.add('hidden');
    err.textContent = '';

    if (!label || explanation.length < 10) {
        err.textContent = 'Please select a reason and write an explanation (at least 10 characters).';
        err.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Submitting…';

    try {
        const response = await fetch(`/api/items/${uploadId}/report`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ label, explanation }),
        });

        const data = await response.json();

        if (data.success) {
            closeReportModal();
            showToast(data.message || 'Report submitted. Thank you.', 'success');
        } else {
            err.textContent = data.error || 'Could not submit report.';
            err.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error:', error);
        err.textContent = 'Could not submit report. Please try again.';
        err.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Submit report';
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

    document.getElementById('report-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeReportModal();
        }
    });
});
</script>
@endsection
