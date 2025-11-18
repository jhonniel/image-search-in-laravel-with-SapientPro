@extends('layouts.admin')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit User Profile</h1>
                <p class="text-gray-600 mt-2">Update user information and settings</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('users') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Users
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <form id="edit-user-form" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- User Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ $user->name }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           required>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ $user->email }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           required>
                </div>
            </div>

            <!-- User Statistics -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">User Statistics</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-500">User ID</div>
                        <div class="text-lg font-semibold text-gray-900">{{ $user->id }}</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-500">Reports Count</div>
                        <div class="text-lg font-semibold text-gray-900" id="reports-count">
                            {{ \App\Models\ImageMetadata::where('uploader_email', $user->email)->count() }}
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-sm text-gray-500">Member Since</div>
                        <div class="text-lg font-semibold text-gray-900">{{ $user->created_at->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                <div id="recent-activity" class="space-y-3">
                    @php
                        $recentReports = \App\Models\ImageMetadata::where('uploader_email', $user->email)
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
                    @endphp

                    @forelse($recentReports as $report)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-{{ $report->status === 'lost' ? 'search' : 'hand-holding' }} text-purple-600 text-xs"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $report->description }}</div>
                                <div class="text-xs text-gray-500">{{ $report->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            {{ $report->status === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ ucfirst($report->status) }}
                        </span>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <div>No recent activity</div>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Form Actions -->
            <div class="border-t pt-6 flex items-center justify-end space-x-4">
                <button type="button" onclick="window.location.href='{{ route('users') }}'"
                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('edit-user-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;

    // Show loading state
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    submitButton.disabled = true;

    try {
        const response = await fetch('{{ route("users.update", $user->id) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-HTTP-Method-Override': 'PUT'
            }
        });

        const data = await response.json();

        if (data.success) {
            // Show success message
            showNotification('User profile updated successfully!', 'success');

            // Update the page data
            document.getElementById('reports-count').textContent = data.user.reports_count ||
                document.getElementById('reports-count').textContent;
        } else {
            showNotification(data.message || 'Failed to update user profile', 'error');
        }
    } catch (error) {
        console.error('Error updating user:', error);
        showNotification('An error occurred while updating the user profile', 'error');
    } finally {
        // Reset button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }
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
