@extends('layouts.user')

@section('content')
<div class="user-page">
    @include('user.partials.page-header', [
        'eyebrow' => 'Account',
        'title' => 'Edit profile',
        'description' => 'Update your account information and settings',
        'actions' => '<a href="'.route('profile').'" class="user-btn-secondary w-full sm:w-auto"><i class="fas fa-arrow-left"></i> Back to profile</a>',
    ])

    <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3">
        <!-- Profile Picture Section -->
        <div class="lg:col-span-1">
            <div class="user-card user-card-body">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Profile Picture</h3>

                <div class="text-center">
                    <div class="relative inline-block mb-4">
                        <div id="avatar-preview" class="w-24 h-24 rounded-full overflow-hidden border-4 border-purple-100">
                            @if($user->profile_picture)
                                <img src="{{ $user->profile_picture }}" alt="Current Avatar"
                                     class="w-full h-full object-cover" id="current-avatar">
                            @else
                                <div class="w-full h-full bg-purple-100 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-purple-600" id="avatar-initials">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center cursor-pointer"
                             onclick="document.getElementById('avatar-input').click()">
                            <i class="fas fa-camera text-white text-sm"></i>
                        </div>
                    </div>

                    <!-- Avatar Upload Zone -->
                    <div id="avatar-drop-zone" class="mt-4 border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-all duration-200 hover:border-purple-400 hover:bg-purple-50 cursor-pointer">
                        <input type="file" id="avatar-input" accept="image/*" class="hidden" onchange="uploadAvatar(this)">
                        
                        <div id="avatar-drop-zone-content" class="space-y-3">
                            <div class="flex justify-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-cloud-upload-alt text-purple-600 text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-1">
                                    <span class="text-purple-600">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500">JPG, PNG, GIF up to 2MB</p>
                            </div>
                            <button type="button" onclick="document.getElementById('avatar-input').click()"
                                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                                <i class="fas fa-folder-open mr-2"></i>
                                Browse Photo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="lg:col-span-2">
            <div class="user-card user-card-body">
                <form id="profile-form" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
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
                            <div>
                                <label for="code_name" class="block text-sm font-medium text-gray-700 mb-2">Code Name <span class="text-gray-500 text-xs">(Unique)</span></label>
                                <input type="text" id="code_name" name="code_name" value="{{ $user->code_name }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       placeholder="Enter your unique code name">
                                <p class="text-xs text-gray-500 mt-1">Your code name must be unique and cannot be duplicated by other users.</p>
                                <div id="code_name_error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Password Change -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                <input type="password" id="current_password" name="current_password"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       placeholder="Enter current password">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                    <input type="password" id="new_password" name="new_password"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="Enter new password">
                                </div>
                                <div>
                                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                    <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                           placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="border-t pt-6 flex items-center justify-end space-x-4">
                        <button type="button" onclick="window.location.href='{{ route('profile') }}'"
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
    </div>
</div>

<script>
// Avatar drag and drop
document.addEventListener('DOMContentLoaded', function() {
    const avatarDropZone = document.getElementById('avatar-drop-zone');
    const avatarInput = document.getElementById('avatar-input');

    if (avatarDropZone && avatarInput) {
        avatarDropZone.addEventListener('click', function(e) {
            if (e.target.closest('button')) return;
            avatarInput.click();
        });

        avatarDropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            avatarDropZone.classList.add('border-purple-500', 'bg-purple-100');
        });

        avatarDropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            avatarDropZone.classList.remove('border-purple-500', 'bg-purple-100');
        });

        avatarDropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            avatarDropZone.classList.remove('border-purple-500', 'bg-purple-100');
            const files = e.dataTransfer.files;
            if (files && files[0]) {
                avatarInput.files = files;
                uploadAvatar(avatarInput);
            }
        });
    }
});
// Profile form submission
document.getElementById('profile-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;

    // Show loading state
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    submitButton.disabled = true;

    try {
        const response = await fetch('{{ route("profile.update") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-HTTP-Method-Override': 'PUT'
            }
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Profile updated successfully!', 'success');
            
            // Clear any code name errors
            const codeNameError = document.getElementById('code_name_error');
            if (codeNameError) {
                codeNameError.classList.add('hidden');
                codeNameError.textContent = '';
            }

            // Update the page data if needed
            if (data.user) {
                document.getElementById('avatar-initials').textContent = data.user.name.substring(0, 2).toUpperCase();
            }
        } else {
            // Handle validation errors
            if (data.errors && data.errors.code_name) {
                const codeNameError = document.getElementById('code_name_error');
                if (codeNameError) {
                    codeNameError.textContent = data.errors.code_name[0];
                    codeNameError.classList.remove('hidden');
                }
            }
            showNotification(data.message || 'Failed to update profile', 'error');
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        showNotification('An error occurred while updating the profile', 'error');
    } finally {
        // Reset button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }
});

// Avatar upload
function uploadAvatar(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            showNotification('File size must be less than 2MB', 'error');
            return;
        }

        // Validate file type
        if (!file.type.startsWith('image/')) {
            showNotification('Please select a valid image file', 'error');
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            preview.innerHTML = `<img src="${e.target.result}" alt="Avatar Preview" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(file);

        // Upload file
        const formData = new FormData();
        formData.append('avatar', file);

        fetch('{{ route("profile.avatar") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Avatar updated successfully!', 'success');
                // Update the current avatar
                document.getElementById('current-avatar').src = data.avatar_url;
            } else {
                showNotification(data.message || 'Failed to upload avatar', 'error');
                // Reset to original avatar
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error uploading avatar:', error);
            showNotification('An error occurred while uploading the avatar', 'error');
            // Reset to original avatar
            location.reload();
        });
    }
}

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
