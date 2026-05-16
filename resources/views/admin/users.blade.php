@extends('layouts.admin')

@section('title', 'Users - FindITFast Admin')

@section('content')
@php
    $unverifiedCount = $totalUsers - $verifiedCount;
@endphp
<div class="admin-page" id="users-admin-page">
    @include('admin.partials.page-header', [
        'title' => 'Users Management',
        'description' => 'View accounts, verification status, and reported items across the platform.',
    ])

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @include('admin.partials.stat-card', [
            'label' => 'Total Users',
            'value' => number_format($totalUsers),
            'icon' => 'fa-users',
            'iconBg' => 'bg-purple-100',
            'iconColor' => 'text-purple-600',
        ])
        @include('admin.partials.stat-card', [
            'label' => 'Verified',
            'value' => number_format($verifiedCount),
            'icon' => 'fa-circle-check',
            'iconBg' => 'bg-blue-100',
            'iconColor' => 'text-blue-600',
        ])
        @include('admin.partials.stat-card', [
            'label' => 'Not Verified',
            'value' => number_format($unverifiedCount),
            'icon' => 'fa-user-clock',
            'iconBg' => 'bg-gray-100',
            'iconColor' => 'text-gray-600',
        ])
    </div>    <div class="admin-card">
        <div class="admin-toolbar">
            <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                <div class="relative flex-1 min-w-0">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                    <input type="text" id="search-users" placeholder="Search by name or email…"
                           class="admin-input pl-9 pr-4 py-2.5">
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select id="filter-status"
                            class="admin-select min-w-[140px]">
                        <option value="">All users</option>
                        <option value="verified">Verified only</option>
                        <option value="unverified">Not verified</option>
                    </select>
                    <button type="button" id="export-users"
                            class="admin-btn-primary">
                        <i class="fas fa-download text-xs"></i>
                        Export
                    </button>
                </div>
            </div>
            <p id="users-filter-summary" class="text-xs text-gray-500 mt-3 hidden"></p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-white">
                        <th scope="col" class="w-12 px-4 py-3.5">
                            <input type="checkbox" id="select-all" aria-label="Select all users"
                                   class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        </th>
                        <th scope="col" class="admin-th">User</th>
                        <th scope="col" class="admin-th hidden md:table-cell">Email</th>
                        <th scope="col" class="admin-th">Status</th>
                        <th scope="col" class="admin-th hidden lg:table-cell">Joined</th>
                        <th scope="col" class="admin-th text-center w-24">Reports</th>
                        <th scope="col" class="admin-th text-right w-36">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="users-table-body">
                    @forelse($users as $user)
                    @php
                        $reportsCount = (int) ($reportCounts[$user->email] ?? 0);
                        $initials = strtoupper(substr(preg_replace('/\s+/', '', $user->name) ?: 'U', 0, 2));
                    @endphp
                    <tr class="user-row admin-table-row"
                        data-user-id="{{ $user->id }}"
                        data-name="{{ strtolower($user->name) }}"
                        data-email="{{ strtolower($user->email) }}"
                        data-verified="{{ $user->is_verified ? 'verified' : 'unverified' }}">
                        <td class="admin-td">
                            <input type="checkbox" class="user-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                   value="{{ $user->id }}" aria-label="Select {{ $user->name }}">
                        </td>
                        <td class="admin-td">
                            <div class="flex items-center gap-3 min-w-[200px]">
                                @if($user->profile_picture)
                                    <img src="{{ $user->profile_picture }}" alt=""
                                         class="h-10 w-10 rounded-full object-cover ring-2 ring-white shadow-sm border border-gray-100">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center shadow-sm ring-2 ring-purple-100 shrink-0">
                                        <span class="text-xs font-semibold text-white tracking-wide">{{ $initials }}</span>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="user-name text-sm font-semibold text-gray-900 truncate">{{ $user->name }}</span>
                                        @if($user->is_verified)
                                        <img src="{{ asset('images/icons/verify.png') }}" alt="Verified" class="w-4 h-4 shrink-0" title="Verified profile">
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">ID #{{ $user->id }}</p>
                                    <p class="user-email-mobile text-xs text-gray-600 mt-0.5 truncate md:hidden">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="admin-td hidden md:table-cell">
                            <span class="user-email text-sm text-gray-700">{{ $user->email }}</span>
                        </td>
                        <td class="admin-td">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                    Active
                                </span>
                                @if($user->is_verified)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700 ring-1 ring-blue-600/10">
                                    <i class="fas fa-check text-[10px] mr-1"></i>
                                    Verified
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-600 ring-1 ring-gray-500/10">
                                    Unverified
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="admin-td hidden lg:table-cell whitespace-nowrap">
                            <p class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $user->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="admin-td text-center">
                            <span class="inline-flex min-w-[2rem] items-center justify-center px-2 py-1 rounded-lg bg-gray-50 text-sm font-semibold text-gray-800 tabular-nums ring-1 ring-gray-200/80">
                                {{ $reportsCount }}
                            </span>
                        </td>
                        <td class="admin-td">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" onclick="viewUser({{ $user->id }})"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-purple-50 hover:text-purple-600 transition-colors"
                                        title="View details">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                <button type="button" onclick="editUser({{ $user->id }})"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                                        title="Edit user">
                                    <i class="fas fa-pen text-sm"></i>
                                </button>
                                <button type="button" onclick="toggleVerification({{ $user->id }})"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition-colors {{ $user->is_verified ? 'text-emerald-600 hover:bg-emerald-50' : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600' }}"
                                        title="{{ $user->is_verified ? 'Remove verification' : 'Verify user' }}">
                                    <i class="fas fa-{{ $user->is_verified ? 'certificate' : 'user-check' }} text-sm"></i>
                                </button>
                                <button type="button" onclick="deleteUser({{ $user->id }})"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors"
                                        title="Delete user">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="mx-auto w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                                <i class="fas fa-users text-2xl text-gray-400"></i>
                            </div>
                            <p class="text-base font-semibold text-gray-900">No users yet</p>
                            <p class="text-sm text-gray-500 mt-1">Registered users will appear here.</p>
                        </td>
                    </tr>
                    @endforelse
                    <tr id="users-no-results" class="hidden">
                        <td colspan="7" class="px-6 py-12 text-center">
                            <i class="fas fa-search text-3xl text-gray-300 mb-3"></i>
                            <p class="text-sm font-medium text-gray-900">No users match your search</p>
                            <p class="text-xs text-gray-500 mt-1">Try a different name, email, or filter.</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($totalUsers > 0)
        <div class="px-4 sm:px-6 py-4 border-t border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-sm text-gray-600" id="users-results-text">
                Showing <span class="font-medium text-gray-900" id="visible-count">{{ $totalUsers }}</span>
                of <span class="font-medium text-gray-900">{{ $totalUsers }}</span> users
            </p>
            <nav class="inline-flex items-center rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden" aria-label="Pagination">
                <button type="button" disabled
                        class="px-3 py-2 text-sm text-gray-300 cursor-not-allowed border-r border-gray-200">
                    Previous
                </button>
                <span class="px-4 py-2 text-sm font-medium text-white bg-purple-600 border-r border-purple-500">1</span>
                <button type="button" disabled
                        class="px-3 py-2 text-sm text-gray-300 cursor-not-allowed">
                    Next
                </button>
            </nav>
        </div>
        @endif
    </div>
</div>

<!-- User details modal -->
<div id="user-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeUserModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-hidden flex flex-col border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 id="user-modal-title" class="text-lg font-semibold text-gray-900">User details</h3>
                <button type="button" onclick="closeUserModal()"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="px-6 py-5 overflow-y-auto flex-1" id="user-details-content">
                <div class="flex items-center justify-center py-8 text-gray-400">
                    <i class="fas fa-spinner fa-spin text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const searchInput = document.getElementById('search-users');
    const filterSelect = document.getElementById('filter-status');
    const rows = () => Array.from(document.querySelectorAll('.user-row'));
    const noResultsRow = document.getElementById('users-no-results');
    const visibleCountEl = document.getElementById('visible-count');
    const filterSummary = document.getElementById('users-filter-summary');

    function applyFilters() {
        const term = (searchInput?.value || '').trim().toLowerCase();
        const status = filterSelect?.value || '';
        let visible = 0;

        rows().forEach(row => {
            const name = row.dataset.name || '';
            const email = row.dataset.email || '';
            const verified = row.dataset.verified || '';
            const matchesSearch = !term || name.includes(term) || email.includes(term);
            const matchesStatus = !status || verified === status;
            const show = matchesSearch && matchesStatus;
            row.classList.toggle('hidden', !show);
            if (show) visible++;
        });

        if (noResultsRow) {
            noResultsRow.classList.toggle('hidden', visible > 0 || rows().length === 0);
        }
        if (visibleCountEl) {
            visibleCountEl.textContent = String(visible);
        }
        if (filterSummary) {
            const parts = [];
            if (term) parts.push('search: “' + term + '”');
            if (status) parts.push(status === 'verified' ? 'verified only' : 'not verified');
            if (parts.length) {
                filterSummary.textContent = 'Filtered by ' + parts.join(' · ');
                filterSummary.classList.remove('hidden');
            } else {
                filterSummary.classList.add('hidden');
            }
        }
    }

    searchInput?.addEventListener('input', applyFilters);
    filterSelect?.addEventListener('change', applyFilters);

    const selectAll = document.getElementById('select-all');
    selectAll?.addEventListener('change', function (e) {
        document.querySelectorAll('.user-checkbox').forEach(cb => {
            const row = cb.closest('.user-row');
            if (row && !row.classList.contains('hidden')) {
                cb.checked = e.target.checked;
            }
        });
    });

    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('user-checkbox')) return;
        const visible = document.querySelectorAll('.user-row:not(.hidden) .user-checkbox');
        const checked = document.querySelectorAll('.user-row:not(.hidden) .user-checkbox:checked');
        if (selectAll) {
            selectAll.checked = visible.length > 0 && visible.length === checked.length;
        }
    });

    window.viewUser = function (userId) {
        const modal = document.getElementById('user-modal');
        const content = document.getElementById('user-details-content');
        modal.classList.remove('hidden');
        content.innerHTML = '<div class="flex items-center justify-center py-10 text-purple-500"><i class="fas fa-spinner fa-spin text-2xl"></i></div>';

        fetch(`/users/${userId}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error('Failed to load');
                const u = data.user;
                const initials = (u.name || 'U').substring(0, 2).toUpperCase();
                const recentReports = (u.recent_reports || []).map(report => `
                    <div class="flex items-center justify-between gap-3 p-3 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">${report.description || 'No description'}</p>
                            <p class="text-xs text-gray-500 mt-0.5">${new Date(report.created_at).toLocaleDateString()}</p>
                        </div>
                        <span class="shrink-0 inline-flex px-2 py-0.5 text-xs font-medium rounded-md ${report.status === 'lost' ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700'}">
                            ${(report.status || '').charAt(0).toUpperCase() + (report.status || '').slice(1)}
                        </span>
                    </div>
                `).join('');

                content.innerHTML = `
                    <div class="space-y-5">
                        <div class="flex items-center gap-4">
                            <div class="h-14 w-14 rounded-full bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center text-white font-semibold text-lg">${initials}</div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">${u.name}</h4>
                                <p class="text-sm text-gray-500">${u.email}</p>
                            </div>
                        </div>
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div><dt class="text-gray-500 text-xs font-medium uppercase tracking-wide">User ID</dt><dd class="mt-1 font-medium text-gray-900">#${u.id}</dd></div>
                            <div><dt class="text-gray-500 text-xs font-medium uppercase tracking-wide">Reports</dt><dd class="mt-1 font-medium text-gray-900">${u.reports_count || 0}</dd></div>
                            <div class="col-span-2"><dt class="text-gray-500 text-xs font-medium uppercase tracking-wide">Joined</dt><dd class="mt-1 font-medium text-gray-900">${new Date(u.created_at).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' })}</dd></div>
                        </dl>
                        <div class="pt-4 border-t border-gray-100">
                            <h5 class="text-sm font-semibold text-gray-900 mb-3">Recent reports</h5>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                ${(u.recent_reports || []).length ? recentReports : '<p class="text-sm text-gray-500 text-center py-6">No reports yet.</p>'}
                            </div>
                        </div>
                    </div>
                `;
            })
            .catch(() => {
                content.innerHTML = '<p class="text-sm text-red-600 text-center py-6">Could not load user details.</p>';
                showNotification('Error loading user details', 'error');
            });
    };

    window.toggleVerification = function (userId) {
        if (!confirm('Change verification status for this user?')) return;
        fetch(`/users/${userId}/toggle-verification`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    showNotification(data.message || 'Failed to update verification', 'error');
                }
            })
            .catch(() => showNotification('Error updating verification', 'error'));
    };

    window.editUser = function (userId) {
        window.location.href = `/users/${userId}/edit`;
    };

    window.deleteUser = function (userId) {
        if (confirm('Delete this user? This cannot be undone.')) {
            showNotification('Delete user is not enabled yet.', 'error');
        }
    };

    window.closeUserModal = function () {
        document.getElementById('user-modal').classList.add('hidden');
    };

    document.getElementById('export-users')?.addEventListener('click', () => {
        showNotification('Export coming soon.', 'info');
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeUserModal();
    });
})();

function showNotification(message, type = 'info') {
    document.querySelectorAll('.toast-notification').forEach(t => t.remove());
    const colors = { success: 'bg-emerald-600', error: 'bg-red-600', info: 'bg-gray-800' };
    const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle' };
    const toast = document.createElement('div');
    toast.className = 'toast-notification fixed top-4 right-4 z-[60] max-w-sm';
    toast.innerHTML = `
        <div class="${colors[type] || colors.info} text-white px-4 py-3 rounded-xl shadow-lg flex items-start gap-3 text-sm">
            <i class="fas ${icons[type] || icons.info} mt-0.5"></i>
            <span class="flex-1 font-medium">${message}</span>
            <button type="button" onclick="this.closest('.toast-notification').remove()" class="opacity-80 hover:opacity-100">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}
</script>
@endsection
