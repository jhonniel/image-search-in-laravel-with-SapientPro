@extends('layouts.user')

@section('title', 'Claim and Verify - FindITFast')

@section('content')
<div class="user-page">
    @include('user.partials.page-header', [
        'eyebrow' => 'Matching',
        'title' => 'Claim and verify',
        'description' => 'Every item you reported, plus the similar listings matched to them.',
        'actions' => '<div class="text-right shrink-0"><div class="text-2xl font-bold text-purple-600" id="total-items-count">0</div><div class="text-xs text-gray-500 sm:text-sm">Your reported items</div></div>',
    ])

    <div class="user-card">
        <div class="user-card-header flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h3 class="user-card-title">Your items</h3>
                <p class="user-card-subtitle">Every item you reported. Open one to see matches you can claim, message, or report</p>
            </div>
            <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:justify-end">
                <div class="relative min-w-0 flex-1 sm:w-56 sm:flex-none">
                    <input type="text" id="search-input" placeholder="Search your items…"
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

            <div id="item-matches-view" class="hidden space-y-4"></div>

            <div id="empty-state" class="hidden py-12 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900">No reported items yet</h3>
                <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500">Report a lost or found item and it will show up here with any matches we find.</p>
                <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                    <a href="/post?type=lost" class="user-btn-primary !py-2 text-sm">Report lost item</a>
                    <a href="/post?type=found" class="user-btn-ghost !py-2 text-sm">Report found item</a>
                </div>
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
let allUserItems = [];
let filteredUserItems = [];

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
            allItems = data.items || [];
            allUserItems = data.user_items || [];
            filteredItems = [...allItems];
            filteredUserItems = [...allUserItems];
            displayOtherUsersItems(filteredItems, filteredUserItems);
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

function panelImage(images, alt, sizeClass = '') {
    const path = firstImagePath(images);
    const extra = images && images.length > 1 ? images.length - 1 : 0;
    const wrapClass = sizeClass ? `cv-compare-image-wrap ${sizeClass}` : 'cv-compare-image-wrap';
    if (!path) {
        return `<div class="${wrapClass}"><div class="cv-compare-image-empty"><i class="fas fa-image text-xl"></i></div></div>`;
    }
    return `
        <div class="${wrapClass}">
            <img src="${escapeHtml(path)}" alt="${escapeHtml(alt || 'Item')}" class="cv-compare-image"
                 onclick="event.stopPropagation(); viewImage('${escapeJs(path)}')" loading="lazy"
                 onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.classList.add('flex');">
            <div class="cv-compare-image-empty hidden"><i class="fas fa-image text-xl"></i></div>
            ${extra > 0 ? `<span class="absolute right-1.5 top-1.5 rounded-md bg-black/55 px-1.5 py-0.5 text-[10px] font-semibold text-white">+${extra}</span>` : ''}
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
        <div class="cv-compare-actions !border-0 !bg-transparent !px-0 !py-1.5">
            <button type="button" onclick="viewItemDetails('${id}')" class="cv-compare-btn-secondary" title="Details"><i class="fas fa-info-circle"></i></button>
            <button type="button" onclick="openReportModal('${id}')" class="cv-compare-btn-report" title="Report"><i class="fas fa-flag"></i></button>
            <button type="button" onclick="messageAboutItem('${id}', '${escapeJs(item.description || '')}', '${escapeJs(item.item_type || '')}', '${escapeJs(item.location || '')}')" class="cv-compare-btn-message">Message</button>
            ${claimBtn}
        </div>
    `;
}

function yoursSummaryCard(yours) {
    if (!yours) {
        return `
            <div class="cv-group-yours">
                <div class="cv-compare-panel-label cv-compare-panel-label-yours">Your item</div>
                <p class="text-sm text-gray-400">No linked item</p>
            </div>
        `;
    }

    return `
        <div class="cv-group-yours">
            <div class="cv-group-yours-media">
                ${panelImage(yours.images, yours.description, 'cv-tile-thumb')}
            </div>
            <div class="cv-group-yours-body">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="cv-compare-panel-label cv-compare-panel-label-yours !mb-0">Your item</span>
                    ${typeBadge(yours.item_type)}
                </div>
                <p class="cv-group-title">${escapeHtml(yours.description || 'No description')}</p>
                <p class="cv-compare-meta !mt-0.5">${escapeHtml(yours.location || 'No location')}</p>
                ${tagsHtml(yours.tags)}
            </div>
        </div>
    `;
}

function matchRow(item) {
    const score = item.similarity_score != null ? `${Number(item.similarity_score)}%` : '—';
    const uploader = escapeHtml(item.uploader_name || 'Unknown');

    return `
        <div class="cv-match-row">
            <div class="cv-match-row-media">
                ${panelImage(item.images, item.description)}
            </div>
            <div class="cv-match-row-body min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    ${typeBadge(item.item_type)}
                    <span class="text-[10px] font-semibold text-purple-700">Score ${escapeHtml(score)}</span>
                </div>
                <p class="cv-group-title">${escapeHtml(item.description || 'No description')}</p>
                <p class="cv-compare-meta !mt-0.5">${escapeHtml(item.location || 'No location')} · ${uploader}</p>
                ${matchActions(item)}
            </div>
        </div>
    `;
}

function groupMatchesByUserItem(items, userItems = []) {
    const groups = new Map();

    // Seed a group for every item the user reported so items without matches stay visible.
    (userItems || []).forEach(yours => {
        if (!yours?.upload_id) return;
        groups.set(yours.upload_id, {
            key: yours.upload_id,
            yours,
            matches: [],
        });
    });

    items.forEach(item => {
        const yours = item.user_matched_item || null;
        const groupKey = yours?.upload_id
            || item.matched_with_upload_id
            || item.matched_with
            || `unknown-${item.upload_id}`;

        if (!groups.has(groupKey)) {
            groups.set(groupKey, {
                key: groupKey,
                yours,
                matches: [],
            });
        }

        const group = groups.get(groupKey);
        if (!group.yours && yours) {
            group.yours = yours;
        }
        group.matches.push(item);
    });

    return Array.from(groups.values()).map(group => {
        group.matches.sort((a, b) => (Number(b.similarity_score) || 0) - (Number(a.similarity_score) || 0));
        group.bestScore = group.matches[0]?.similarity_score != null
            ? Number(group.matches[0].similarity_score)
            : null;
        return group;
    }).sort((a, b) => {
        const scoreDiff = (b.bestScore || 0) - (a.bestScore || 0);
        if (scoreDiff !== 0) return scoreDiff;
        // Items without matches: newest report first.
        const aDate = Date.parse(a.yours?.created_at || '') || 0;
        const bDate = Date.parse(b.yours?.created_at || '') || 0;
        return bDate - aDate;
    });
}

function groupDomId(key) {
    return String(key).replace(/[^a-zA-Z0-9_-]/g, '_');
}

let currentGroups = [];
let activeGroupKey = null;
// Per-item state of the on-demand system rescan: { status: 'loading'|'done'|'error', newMatches, at }
const matchRefreshState = {};
const MATCH_REFRESH_COOLDOWN_MS = 60000;

function matchRefreshBanner(groupKey) {
    const state = matchRefreshState[groupKey];
    if (!state) return '';

    if (state.status === 'loading') {
        return `
            <div class="flex items-center gap-2 rounded-lg border border-purple-100 bg-purple-50 px-3 py-2 text-xs text-purple-700">
                <span class="inline-block h-3 w-3 animate-spin rounded-full border-2 border-purple-200 border-t-purple-600"></span>
                Scanning the system for new matches…
            </div>
        `;
    }

    if (state.status === 'error') {
        return `
            <div class="rounded-lg border border-amber-100 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                <i class="fas fa-triangle-exclamation mr-1"></i>Couldn't scan for new matches. Showing the matches we already have.
            </div>
        `;
    }

    return `
        <div class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-xs text-gray-600">
            <i class="fas fa-circle-check mr-1 text-green-500"></i>
            ${state.newMatches > 0
                ? `Scan complete — ${state.newMatches} new match${state.newMatches === 1 ? '' : 'es'} found.`
                : 'Scan complete — no new matches right now.'}
        </div>
    `;
}

function groupMatchKey(item) {
    return item.matched_with_upload_id || item.user_matched_item?.upload_id || null;
}

// Ask the server to re-run similarity for this item against every listing in the system.
async function refreshMatchesForItem(uploadId) {
    const state = matchRefreshState[uploadId];
    if (state?.status === 'loading') return;
    if (state?.at && (Date.now() - state.at) < MATCH_REFRESH_COOLDOWN_MS) return;

    matchRefreshState[uploadId] = { status: 'loading', at: Date.now() };
    const banner = document.getElementById('match-refresh-banner');
    if (banner) banner.innerHTML = matchRefreshBanner(uploadId);

    try {
        const response = await fetch(`/api/items/${encodeURIComponent(uploadId)}/refresh-matches`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (!data.success) {
            matchRefreshState[uploadId] = { status: 'error', at: Date.now() };
        } else {
            matchRefreshState[uploadId] = {
                status: 'done',
                newMatches: Number(data.new_matches) || 0,
                at: Date.now()
            };

            const fresh = data.items || [];
            allItems = allItems.filter(item => groupMatchKey(item) !== uploadId).concat(fresh);
        }
    } catch (error) {
        console.error('Error refreshing matches:', error);
        matchRefreshState[uploadId] = { status: 'error', at: Date.now() };
    }

    // Re-applies current filters and re-renders the open item without triggering another scan.
    filterItems();
}

function showItemsList() {
    activeGroupKey = null;
    document.getElementById('item-matches-view')?.classList.add('hidden');
    document.getElementById('other-users-items-list')?.classList.remove('hidden');
    const subtitle = document.querySelector('.user-card-subtitle');
    if (subtitle) subtitle.textContent = 'Every item you reported. Open one to see matches you can claim, message, or report';
    const title = document.querySelector('.user-card-title');
    if (title) title.textContent = 'Your items';
}

function openItemMatches(groupKey, options = {}) {
    const { refresh = true } = options;
    const group = currentGroups.find(g => String(g.key) === String(groupKey));
    const detailView = document.getElementById('item-matches-view');
    const listView = document.getElementById('other-users-items-list');
    if (!group || !detailView) return;

    activeGroupKey = String(groupKey);
    listView?.classList.add('hidden');
    detailView.classList.remove('hidden');

    const title = document.querySelector('.user-card-title');
    if (title) title.textContent = 'Possible matches';
    const subtitle = document.querySelector('.user-card-subtitle');
    if (subtitle) {
        subtitle.textContent = group.matches.length
            ? `${group.matches.length} possible match${group.matches.length === 1 ? '' : 'es'} for this item`
            : 'No matches for this item yet';
    }

    const best = group.bestScore != null ? `${group.bestScore}%` : '—';
    const matchesHtml = group.matches.length
        ? `<div class="cv-detail-matches">${group.matches.map(matchRow).join('')}</div>`
        : `<p class="py-8 text-center text-sm text-gray-500">No possible matches yet. We'll add them here as similar items are posted.</p>`;

    detailView.innerHTML = `
        <div class="flex flex-wrap items-center justify-between gap-2">
            <button type="button" onclick="showItemsList()" class="user-btn-ghost !py-2 text-sm">
                <i class="fas fa-arrow-left text-xs"></i>
                Back to your items
            </button>
            <p class="text-xs text-purple-700">Best score <span class="font-bold">${escapeHtml(best)}</span></p>
        </div>
        <div id="match-refresh-banner">${matchRefreshBanner(group.key)}</div>
        <section class="cv-detail-yours">
            ${yoursSummaryCard(group.yours)}
        </section>
        <section>
            <h4 class="mb-3 text-[10px] font-bold uppercase tracking-wider text-gray-500">All possible matches</h4>
            ${matchesHtml}
        </section>
    `;

    detailView.scrollIntoView({ behavior: 'smooth', block: 'start' });

    // Opening an item rescans the whole system so newly posted listings can match right away.
    if (refresh && group.yours?.upload_id) {
        refreshMatchesForItem(group.yours.upload_id);
    }
}

function displayOtherUsersItems(items, userItems = []) {
    const itemsContainer = document.getElementById('other-users-items-list');
    const detailView = document.getElementById('item-matches-view');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');

    if (!itemsContainer) return;

    loadingState.classList.add('hidden');

    const groups = groupMatchesByUserItem(items, userItems);

    if (groups.length === 0) {
        currentGroups = [];
        activeGroupKey = null;
        itemsContainer.innerHTML = '';
        itemsContainer.classList.add('hidden');
        detailView?.classList.add('hidden');
        emptyState.classList.remove('hidden');
        document.getElementById('total-items-count').textContent = '0';
        return;
    }

    emptyState.classList.add('hidden');
    currentGroups = groups;
    document.getElementById('total-items-count').textContent = String(currentGroups.length);

    // Keep detail view open after claim/refresh if that group still exists
    if (activeGroupKey && currentGroups.some(g => String(g.key) === activeGroupKey)) {
        openItemMatches(activeGroupKey, { refresh: false });
        return;
    }

    activeGroupKey = null;
    detailView?.classList.add('hidden');
    itemsContainer.classList.remove('hidden');

    itemsContainer.innerHTML = currentGroups.map(group => {
        const domId = groupDomId(group.key);
        const matchCount = group.matches.length;
        const best = group.bestScore != null ? `${group.bestScore}%` : '—';
        const summary = matchCount
            ? `<p class="text-sm font-semibold text-gray-900">${matchCount} match${matchCount === 1 ? '' : 'es'}</p>
               <p class="text-xs text-purple-700">Best score <span class="font-bold">${escapeHtml(best)}</span></p>`
            : `<p class="text-sm font-semibold text-gray-500">No matches yet</p>
               <p class="text-xs text-gray-400">We'll list matches here as new items are posted</p>`;

        return `
            <article class="cv-group cursor-pointer" data-group-key="${escapeHtml(domId)}" onclick="openItemMatches('${escapeJs(String(group.key))}')">
                <div class="cv-group-header">
                    ${yoursSummaryCard(group.yours)}
                    <div class="cv-group-summary">
                        <div class="cv-group-summary-stats">
                            ${summary}
                        </div>
                        <button type="button"
                            class="cv-group-toggle"
                            onclick="event.stopPropagation(); openItemMatches('${escapeJs(String(group.key))}')">
                            <span>${matchCount ? 'View matches' : 'View item'}</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </div>
            </article>
        `;
    }).join('');
}

function updateStats() {
    const groups = groupMatchesByUserItem(allItems, allUserItems);
    document.getElementById('total-items-count').textContent = groups.length;
}

function showEmptyState() {
    document.getElementById('loading-state').classList.add('hidden');
    document.getElementById('other-users-items-list').classList.add('hidden');
    document.getElementById('item-matches-view')?.classList.add('hidden');
    document.getElementById('empty-state').classList.remove('hidden');
    document.getElementById('total-items-count').textContent = '0';
    activeGroupKey = null;
}

// Filter functions
function itemMatchesSearch(item, searchTerm) {
    if (!searchTerm) return true;
    return (item.description || '').toLowerCase().includes(searchTerm) ||
        (item.location || '').toLowerCase().includes(searchTerm) ||
        (Array.isArray(item.tags) && item.tags.some(tag => String(tag).toLowerCase().includes(searchTerm)));
}

function filterItems() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const typeFilter = document.getElementById('type-filter').value;

    filteredUserItems = allUserItems.filter(yours => {
        const matchesType = !typeFilter || yours.item_type === typeFilter;
        const matchesSearch = itemMatchesSearch(yours, searchTerm) ||
            allItems.some(item => item.matched_with_upload_id === yours.upload_id && itemMatchesSearch(item, searchTerm));

        return matchesSearch && matchesType;
    });

    const visibleUploadIds = new Set(filteredUserItems.map(yours => yours.upload_id));

    filteredItems = allItems.filter(item => {
        const yours = item.user_matched_item || {};
        const groupKey = yours.upload_id || item.matched_with_upload_id;

        // Keep matches whose parent item survived filtering, plus orphan matches that match on their own.
        if (groupKey && visibleUploadIds.has(groupKey)) return true;
        if (groupKey && allUserItems.some(own => own.upload_id === groupKey)) return false;

        const matchesType = !typeFilter || item.item_type === typeFilter;

        return matchesType && itemMatchesSearch(item, searchTerm);
    });

    displayOtherUsersItems(filteredItems, filteredUserItems);
}

function resetFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('type-filter').value = '';
    filteredItems = [...allItems];
    filteredUserItems = [...allUserItems];
    displayOtherUsersItems(filteredItems, filteredUserItems);
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
