@extends('layouts.admin')

@section('title', 'Contributors Management - FindITFast Admin')

@section('content')
<div class="admin-page">
    @include('admin.partials.page-header', [
        'title' => 'Contributors Management',
        'description' => 'Manage developers and contributors who helped build the system.',
    ])

    @include('admin.partials.alert')

    <div class="admin-card admin-card-body mb-6">
        <h2 class="admin-panel-title mb-4">Add New Contributor</h2>
        <form id="contributorForm" action="{{ route('contributors.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                    <input type="text" id="name" name="name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary"
                           placeholder="Full name">
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <input type="text" id="role" name="role"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary"
                           placeholder="e.g., Lead Developer, UI/UX Designer">
                </div>
                <div class="md:col-span-2">
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                    <textarea id="bio" name="bio" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary"
                              placeholder="Brief description of contributions"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Avatar</label>
                    <div id="avatar-drop-zone" class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-all duration-200 hover:border-purple-400 hover:bg-purple-50 cursor-pointer">
                        <input type="file" id="avatar" name="avatar" accept="image/*" class="hidden">
                        <div id="avatar-drop-zone-content" class="space-y-3">
                            <div class="flex justify-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-purple-600 text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-1">
                                    <span class="text-purple-600">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500">Max 5MB, JPEG, PNG, GIF, WEBP</p>
                            </div>
                            <button type="button" onclick="document.getElementById('avatar').click()" 
                                    class="inline-flex items-center px-3 py-1.5 bg-purple-primary text-white rounded-lg hover:bg-purple-600 transition-colors text-xs font-medium">
                                <i class="fas fa-folder-open mr-1"></i>
                                Browse
                            </button>
                        </div>
                    </div>
                    <div id="avatar-preview-container" class="mt-4 hidden">
                        <div class="relative inline-block">
                            <img id="avatar-preview" src="" alt="Preview" class="w-24 h-24 object-cover rounded-full border-2 border-gray-200">
                            <button type="button" onclick="removeAvatarPreview()" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div>
                    <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
                    <input type="number" id="order" name="order" min="0" value="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary"
                           placeholder="0">
                    <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" name="email"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary"
                           placeholder="email@example.com">
                </div>
                <div>
                    <label for="github" class="block text-sm font-medium text-gray-700 mb-2">GitHub</label>
                    <input type="url" id="github" name="github"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary"
                           placeholder="https://github.com/username">
                </div>
                <div>
                    <label for="linkedin" class="block text-sm font-medium text-gray-700 mb-2">LinkedIn</label>
                    <input type="url" id="linkedin" name="linkedin"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary"
                           placeholder="https://linkedin.com/in/username">
                </div>
                <div>
                    <label for="twitter" class="block text-sm font-medium text-gray-700 mb-2">Twitter/X</label>
                    <input type="url" id="twitter" name="twitter"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary"
                           placeholder="https://twitter.com/username">
                </div>
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                    <input type="url" id="website" name="website"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary"
                           placeholder="https://example.com">
                </div>
            </div>
            <div class="mt-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" checked
                           class="rounded border-gray-300 text-purple-primary focus:ring-purple-primary">
                    <span class="ml-2 text-sm text-gray-700">Active (show contributor)</span>
                </label>
            </div>
            <div class="mt-6">
                <button type="submit" id="submitBtn" class="bg-purple-primary text-white px-6 py-2 rounded-lg hover:bg-purple-600 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add Contributor
                </button>
                <button type="button" id="cancelBtn" onclick="resetForm()" class="ml-3 bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors hidden">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <div class="admin-toolbar flex items-center justify-between">
            <h2 class="admin-panel-title">Current Contributors</h2>
            <a href="{{ route('contributors.index', ['trashed' => !$showTrashed]) }}" 
               class="text-sm text-purple-primary hover:text-purple-600">
                {{ $showTrashed ? 'Show Active' : 'Show Trashed' }}
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-white">
                        <th class="admin-th">Avatar</th>
                        <th class="admin-th">Name</th>
                        <th class="admin-th">Role</th>
                        <th class="admin-th">Order</th>
                        <th class="admin-th">Status</th>
                        <th class="admin-th">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contributors as $contributor)
                    <tr class="admin-table-row">
                        <td class="admin-td whitespace-nowrap">
                            @if($contributor->avatar_path)
                                <img src="{{ $contributor->avatar_path }}" alt="{{ $contributor->name }}" 
                                     class="w-12 h-12 object-cover rounded-full border-2 border-gray-200">
                            @else
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full flex items-center justify-center">
                                    <span class="text-white font-semibold text-sm">{{ strtoupper(substr($contributor->name, 0, 2)) }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="admin-td whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $contributor->name }}</div>
                            @if($contributor->bio)
                                <div class="text-xs text-gray-500 mt-1">{{ Str::limit($contributor->bio, 50) }}</div>
                            @endif
                        </td>
                        <td class="admin-td whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $contributor->role ?? 'N/A' }}</div>
                        </td>
                        <td class="admin-td whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $contributor->order }}</div>
                        </td>
                        <td class="admin-td whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $contributor->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $contributor->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="admin-td">
                            @if($showTrashed)
                                <form action="{{ route('contributors.restore', $contributor->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900 mr-3">
                                        <i class="fas fa-undo"></i> Restore
                                    </button>
                                </form>
                                <form action="{{ route('contributors.force-delete', $contributor->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete this contributor?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i> Delete Permanently
                                    </button>
                                </form>
                            @else
                                <button onclick="editContributor({{ $contributor->id }})" 
                                        class="text-purple-primary hover:text-purple-600 mr-4">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form action="{{ route('contributors.destroy', $contributor) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this contributor?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            {{ $showTrashed ? 'No trashed contributors found.' : 'No contributors added yet.' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-900">Edit Contributor</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <form id="editForm" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                    <input type="text" id="edit_name" name="name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary">
                </div>
                <div>
                    <label for="edit_role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <input type="text" id="edit_role" name="role"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary">
                </div>
                <div class="md:col-span-2">
                    <label for="edit_bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                    <textarea id="edit_bio" name="bio" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Avatar</label>
                    <div id="edit-avatar-preview-container" class="mb-4">
                        <img id="edit-avatar-preview" src="" alt="Current avatar" class="w-24 h-24 object-cover rounded-full border-2 border-gray-200 hidden">
                    </div>
                    <input type="file" id="edit_avatar" name="avatar" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep current avatar</p>
                </div>
                <div>
                    <label for="edit_order" class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
                    <input type="number" id="edit_order" name="order" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary">
                </div>
                <div>
                    <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="edit_email" name="email"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary">
                </div>
                <div>
                    <label for="edit_github" class="block text-sm font-medium text-gray-700 mb-2">GitHub</label>
                    <input type="url" id="edit_github" name="github"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary">
                </div>
                <div>
                    <label for="edit_linkedin" class="block text-sm font-medium text-gray-700 mb-2">LinkedIn</label>
                    <input type="url" id="edit_linkedin" name="linkedin"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary">
                </div>
                <div>
                    <label for="edit_twitter" class="block text-sm font-medium text-gray-700 mb-2">Twitter/X</label>
                    <input type="url" id="edit_twitter" name="twitter"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary">
                </div>
                <div>
                    <label for="edit_website" class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                    <input type="url" id="edit_website" name="website"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary">
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" id="edit_is_active" name="is_active"
                               class="rounded border-gray-300 text-purple-primary focus:ring-purple-primary">
                        <span class="ml-2 text-sm text-gray-700">Active (show contributor)</span>
                    </label>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeEditModal()" 
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2 bg-purple-primary text-white rounded-lg hover:bg-purple-600 transition-colors">
                    <i class="fas fa-save mr-2"></i>Update Contributor
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Avatar upload preview
document.getElementById('avatar')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
            document.getElementById('avatar-preview-container').classList.remove('hidden');
            document.getElementById('avatar-drop-zone-content').classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
});

function removeAvatarPreview() {
    document.getElementById('avatar').value = '';
    document.getElementById('avatar-preview-container').classList.add('hidden');
    document.getElementById('avatar-drop-zone-content').classList.remove('hidden');
}

// Edit contributor
let contributorsData = @json($contributors->keyBy('id'));

function editContributor(id) {
    const contributor = contributorsData[id];
    if (!contributor) return;

    document.getElementById('edit_name').value = contributor.name || '';
    document.getElementById('edit_role').value = contributor.role || '';
    document.getElementById('edit_bio').value = contributor.bio || '';
    document.getElementById('edit_order').value = contributor.order || 0;
    document.getElementById('edit_email').value = contributor.email || '';
    document.getElementById('edit_github').value = contributor.github || '';
    document.getElementById('edit_linkedin').value = contributor.linkedin || '';
    document.getElementById('edit_twitter').value = contributor.twitter || '';
    document.getElementById('edit_website').value = contributor.website || '';
    document.getElementById('edit_is_active').checked = contributor.is_active || false;

    // Show current avatar if exists
    const avatarPreview = document.getElementById('edit-avatar-preview');
    if (contributor.avatar_path) {
        avatarPreview.src = contributor.avatar_path;
        avatarPreview.classList.remove('hidden');
    } else {
        avatarPreview.classList.add('hidden');
    }

    document.getElementById('editForm').action = `/contributors/${id}`;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function resetForm() {
    document.getElementById('contributorForm').reset();
    removeAvatarPreview();
    document.getElementById('cancelBtn').classList.add('hidden');
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-plus mr-2"></i>Add Contributor';
}
</script>
@endsection

