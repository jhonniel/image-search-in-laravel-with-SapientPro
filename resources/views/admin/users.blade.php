@extends('layouts.admin')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Users Management</h1>
                <p class="text-gray-600 mt-2">Manage all registered users in the system</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="bg-white rounded-lg px-4 py-2 shadow-sm border">
                    <div class="text-sm text-gray-500">Total Users</div>
                    <div class="text-2xl font-bold text-purple-600">{{ $users->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" id="search-users" placeholder="Search users by name or email..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <select id="filter-status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Users</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button id="export-users" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Export
                </button>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto -mx-3 sm:-mx-6">
            <div class="inline-block min-w-full align-middle">
                <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                                <input type="checkbox" id="select-all" class="mr-3 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                User
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reports</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="users-table-body">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 user-row" data-user-id="{{ $user->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <input type="checkbox" class="user-checkbox mr-3 rounded border-gray-300 text-purple-600 focus:ring-purple-500" value="{{ $user->id }}">
                                <div class="shrink-0 h-10 w-10">
                                    @if($user->profile_picture)
                                        <img src="{{ $user->profile_picture }}" alt="{{ $user->name }}" class="h-10 w-10 rounded-full object-cover border border-purple-100">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-purple-600">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="flex items-center gap-1.5">
                                        <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        @if($user->is_verified ?? false)
                                        <span class="inline-flex items-center justify-center w-4 h-4 flex-shrink-0" title="Verified Profile">
                                            <img src="{{ asset('images/icons/verify.png') }}" alt="Verified" class="w-4 h-4">
                                        </span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500">ID: {{ $user->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                                @if($user->is_verified)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Verified
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Not Verified
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->created_at->format('M d, Y') }}
                            <div class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \App\Models\ImageMetadata::where('uploader_email', $user->email)->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <button onclick="viewUser({{ $user->id }})" class="text-purple-600 hover:text-purple-900 transition-colors" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editUser({{ $user->id }})" class="text-blue-600 hover:text-blue-900 transition-colors" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="toggleVerification({{ $user->id }})" class="{{ $user->is_verified ? 'text-green-600 hover:text-green-900' : 'text-gray-600 hover:text-gray-900' }} transition-colors" title="{{ $user->is_verified ? 'Unverify' : 'Verify' }}">
                                    <i class="fas fa-{{ $user->is_verified ? 'check-circle' : 'circle' }}"></i>
                                </button>
                                <button onclick="deleteUser({{ $user->id }})" class="text-red-600 hover:text-red-900 transition-colors" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-users text-4xl mb-4"></i>
                                <div class="text-lg font-medium">No users found</div>
                                <div class="text-sm">No users have registered yet.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($users->count() > 0)
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700">
            Showing <span class="font-medium">1</span> to <span class="font-medium">{{ $users->count() }}</span> of <span class="font-medium">{{ $users->count() }}</span> results
        </div>
        <div class="flex items-center space-x-2">
            <button class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Previous
            </button>
            <button class="px-3 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md hover:bg-purple-700">
                1
            </button>
            <button class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Next
            </button>
        </div>
    </div>
    @endif
</div>

<!-- User Details Modal -->
<div id="user-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">User Details</h3>
                    <button onclick="closeUserModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="user-details-content">
                    <!-- User details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('search-users').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.user-row');

    rows.forEach(row => {
        const name = row.querySelector('.text-sm.font-medium').textContent.toLowerCase();
        const email = row.querySelector('.text-sm.text-gray-900').textContent.toLowerCase();

        if (name.includes(searchTerm) || email.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Select all functionality
document.getElementById('select-all').addEventListener('change', function(e) {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = e.target.checked;
    });
});

// Individual checkbox change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('user-checkbox')) {
        const allCheckboxes = document.querySelectorAll('.user-checkbox');
        const checkedCheckboxes = document.querySelectorAll('.user-checkbox:checked');

        document.getElementById('select-all').checked = allCheckboxes.length === checkedCheckboxes.length;
    }
});

// User actions
function viewUser(userId) {
    // Load user details and show modal
    fetch(`/users/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const recentReports = data.user.recent_reports.map(report => `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-${report.status === 'lost' ? 'search' : 'hand-holding'} text-purple-600 text-xs"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${report.description}</div>
                                <div class="text-xs text-gray-500">${new Date(report.created_at).toLocaleDateString()}</div>
                            </div>
                        </div>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            ${report.status === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                            ${report.status.charAt(0).toUpperCase() + report.status.slice(1)}
                        </span>
                    </div>
                `).join('');

                document.getElementById('user-details-content').innerHTML = `
                    <div class="space-y-6">
                        <div class="flex items-center space-x-4">
                            <div class="h-16 w-16 rounded-full bg-purple-100 flex items-center justify-center">
                                <span class="text-xl font-medium text-purple-600">
                                    ${data.user.name.substring(0, 2).toUpperCase()}
                                </span>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold">${data.user.name}</h4>
                                <p class="text-gray-600">${data.user.email}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">User ID</label>
                                <p class="text-sm text-gray-900">${data.user.id}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Status</label>
                                <p class="text-sm text-gray-900">Active</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Joined</label>
                                <p class="text-sm text-gray-900">${new Date(data.user.created_at).toLocaleDateString()}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Reports</label>
                                <p class="text-sm text-gray-900">${data.user.reports_count || 0}</p>
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <h5 class="text-sm font-medium text-gray-900 mb-3">Recent Activity</h5>
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                ${data.user.recent_reports.length > 0 ? recentReports :
                                    '<div class="text-center py-4 text-gray-500"><i class="fas fa-inbox text-2xl mb-2"></i><div>No recent activity</div></div>'}
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('user-modal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error loading user details:', error);
            showNotification('Error loading user details', 'error');
        });
}

function toggleVerification(userId) {
    if (!confirm('Are you sure you want to toggle verification status for this user?')) {
        return;
    }

    fetch(`/users/${userId}/toggle-verification`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            // Reload the page to update the UI
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Failed to toggle verification', 'error');
        }
    })
    .catch(error => {
        console.error('Error toggling verification:', error);
        showNotification('Error toggling verification', 'error');
    });
}

function editUser(userId) {
    // Redirect to edit page
    window.location.href = `/users/${userId}/edit`;
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        // Implement delete user functionality
        alert('Delete user functionality coming soon!');
    }
}

function closeUserModal() {
    document.getElementById('user-modal').classList.add('hidden');
}

// Export functionality
document.getElementById('export-users').addEventListener('click', function() {
    alert('Export functionality coming soon!');
});

// Toast notification function
function showNotification(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(toast => toast.remove());

    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'toast-notification fixed top-4 right-4 z-50 transform transition-all duration-300 ease-in-out';

    // Set toast styles based on type
    let bgColor, icon, iconColor;
    switch (type) {
        case 'success':
            bgColor = 'bg-green-500';
            icon = 'fas fa-check-circle';
            iconColor = 'text-green-100';
            break;
        case 'error':
            bgColor = 'bg-red-500';
            icon = 'fas fa-exclamation-circle';
            iconColor = 'text-red-100';
            break;
        default:
            bgColor = 'bg-blue-500';
            icon = 'fas fa-info-circle';
            iconColor = 'text-blue-100';
    }

    toast.innerHTML = `
        <div class="${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 max-w-md">
            <i class="${icon} ${iconColor} text-xl"></i>
            <div class="flex-1">
                <div class="font-medium">${message}</div>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    // Add to page
    document.body.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.transform = 'translateX(100%)';
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, 5000);
}
</script>
@endsection
