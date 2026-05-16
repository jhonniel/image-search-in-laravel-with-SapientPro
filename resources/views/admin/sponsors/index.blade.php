@extends('layouts.admin')

@section('title', 'Sponsors Management - FindITFast Admin')

@section('content')
<div class="admin-page">
    @include('admin.partials.page-header', [
        'title' => 'Sponsors Management',
        'description' => 'Manage sponsors displayed on the landing page.',
    ])

    @include('admin.partials.alert')

    <!-- Toggle Show Sponsors Carousel -->
    <div class="admin-card admin-card-body mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Show Sponsors Carousel on Landing Page</h3>
                <p class="text-sm text-gray-600">Enable or disable the sponsors carousel visibility on the landing page</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="toggleShowSponsors" class="sr-only peer" {{ $showSponsors ? 'checked' : '' }}>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-primary"></div>
            </label>
        </div>
    </div>

    <!-- Add New Sponsor Form -->
    <div class="admin-card admin-card-body mb-6">
        <h2 class="admin-panel-title mb-4">Add New Sponsor</h2>
        <form action="{{ route('sponsors.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Sponsor Name *</label>
                    <input type="text" id="name" name="name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary"
                           placeholder="Enter sponsor name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Sponsor Logo <span class="text-red-500">*</span></label>
                    
                    <!-- Drag and Drop Zone -->
                    <div id="sponsor-drop-zone" class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-all duration-200 hover:border-purple-400 hover:bg-purple-50 cursor-pointer">
                        <input type="file" id="image" name="image" accept="image/*" class="hidden" required>
                        
                        <div id="sponsor-drop-zone-content" class="space-y-3">
                            <div class="flex justify-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-cloud-upload-alt text-purple-600 text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-1">
                                    <span class="text-purple-600">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500">Max 5MB, JPEG, PNG, GIF, WEBP</p>
                            </div>
                            <button type="button" onclick="document.getElementById('image').click()" 
                                    class="inline-flex items-center px-3 py-1.5 bg-purple-primary text-white rounded-lg hover:bg-purple-600 transition-colors text-xs font-medium">
                                <i class="fas fa-folder-open mr-1"></i>
                                Browse
                            </button>
                        </div>
                    </div>

                    <!-- Image Preview -->
                    <div id="sponsor-preview-container" class="mt-4 hidden">
                        <div class="relative inline-block">
                            <img id="sponsor-preview" src="" alt="Preview" class="max-w-xs h-32 object-contain rounded-lg border-2 border-gray-200">
                            <button type="button" onclick="removeSponsorPreview()" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
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
            </div>
            <div class="mt-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" checked
                           class="rounded border-gray-300 text-purple-primary focus:ring-purple-primary">
                    <span class="ml-2 text-sm text-gray-700">Active (show on landing page)</span>
                </label>
            </div>
            <div class="mt-4">
                <button type="submit" class="bg-purple-primary text-white px-6 py-2 rounded-lg hover:bg-purple-600 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add Sponsor
                </button>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <div class="admin-toolbar">
            <h2 class="admin-panel-title">Current Sponsors</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-white">
                        <th class="admin-th">Logo</th>
                        <th class="admin-th">Name</th>
                        <th class="admin-th">Order</th>
                        <th class="admin-th">Status</th>
                        <th class="admin-th">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sponsors as $sponsor)
                    <tr class="admin-table-row">
                        <td class="admin-td whitespace-nowrap">
                            <img src="{{ $sponsor->image_path }}" alt="{{ $sponsor->name }}" 
                                 class="h-16 w-16 object-contain bg-gray-50 rounded-lg p-2">
                        </td>
                        <td class="admin-td whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $sponsor->name }}</div>
                        </td>
                        <td class="admin-td whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $sponsor->order }}</div>
                        </td>
                        <td class="admin-td whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sponsor->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $sponsor->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button type="button" onclick="editSponsor({{ $sponsor->id }})" class="admin-icon-btn-purple mr-1" title="Edit">
                                <i class="fas fa-pen text-sm"></i>
                            </button>
                            <form action="{{ route('sponsors.destroy', $sponsor) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this sponsor?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Delete">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No sponsors added yet. Add your first sponsor above.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Sponsor Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Sponsor</h3>
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">Sponsor Name *</label>
                    <input type="text" id="edit_name" name="name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Sponsor Logo</label>
                    
                    <!-- Drag and Drop Zone -->
                    <div id="edit-sponsor-drop-zone" class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center transition-all duration-200 hover:border-purple-400 hover:bg-purple-50 cursor-pointer">
                        <input type="file" id="edit_image" name="image" accept="image/*" class="hidden">
                        
                        <div id="edit-sponsor-drop-zone-content" class="space-y-3">
                            <div class="flex justify-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-cloud-upload-alt text-purple-600 text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-1">
                                    <span class="text-purple-600">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500">Max 5MB, JPEG, PNG, GIF, WEBP</p>
                            </div>
                            <button type="button" onclick="document.getElementById('edit_image').click()" 
                                    class="inline-flex items-center px-3 py-1.5 bg-purple-primary text-white rounded-lg hover:bg-purple-600 transition-colors text-xs font-medium">
                                <i class="fas fa-folder-open mr-1"></i>
                                Browse
                            </button>
                        </div>
                    </div>

                    <!-- Image Preview -->
                    <div id="edit-sponsor-preview-container" class="mt-4 hidden">
                        <div class="relative inline-block">
                            <img id="edit-sponsor-preview" src="" alt="Preview" class="max-w-xs h-32 object-contain rounded-lg border-2 border-gray-200">
                            <button type="button" onclick="removeEditSponsorPreview()" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep current logo</p>
                </div>
                <div class="mb-4">
                    <label for="edit_order" class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
                    <input type="number" id="edit_order" name="order" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary">
                </div>
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="edit_is_active" name="is_active"
                               class="rounded border-gray-300 text-purple-primary focus:ring-purple-primary">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-primary text-white rounded-lg hover:bg-purple-600">
                        Update Sponsor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const sponsors = @json($sponsors);

function editSponsor(id) {
    const sponsor = sponsors.find(s => s.id === id);
    if (!sponsor) return;

    document.getElementById('edit_name').value = sponsor.name;
    document.getElementById('edit_order').value = sponsor.order;
    document.getElementById('edit_is_active').checked = sponsor.is_active;
    document.getElementById('editForm').action = `/sponsors/${id}`;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Toggle show sponsors carousel
document.getElementById('toggleShowSponsors').addEventListener('change', function() {
    const show = this.checked;
    fetch('{{ route("sponsors.toggle-show") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ show: show })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update setting');
    });
});

// Sponsor image upload handlers
document.addEventListener('DOMContentLoaded', function() {
    // Add sponsor form
    const sponsorDropZone = document.getElementById('sponsor-drop-zone');
    const sponsorImageInput = document.getElementById('image');
    const sponsorPreviewContainer = document.getElementById('sponsor-preview-container');
    const sponsorPreview = document.getElementById('sponsor-preview');

    if (sponsorDropZone && sponsorImageInput) {
        sponsorDropZone.addEventListener('click', function(e) {
            if (e.target.closest('button')) return;
            sponsorImageInput.click();
        });

        sponsorImageInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const file = e.target.files[0];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        sponsorPreview.src = e.target.result;
                        sponsorPreviewContainer.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        // Drag and drop
        sponsorDropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            sponsorDropZone.classList.add('border-purple-500', 'bg-purple-100');
        });

        sponsorDropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            sponsorDropZone.classList.remove('border-purple-500', 'bg-purple-100');
        });

        sponsorDropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            sponsorDropZone.classList.remove('border-purple-500', 'bg-purple-100');
            const files = e.dataTransfer.files;
            if (files && files[0]) {
                sponsorImageInput.files = files;
                sponsorImageInput.dispatchEvent(new Event('change'));
            }
        });
    }

    // Edit sponsor form
    const editSponsorDropZone = document.getElementById('edit-sponsor-drop-zone');
    const editSponsorImageInput = document.getElementById('edit_image');
    const editSponsorPreviewContainer = document.getElementById('edit-sponsor-preview-container');
    const editSponsorPreview = document.getElementById('edit-sponsor-preview');

    if (editSponsorDropZone && editSponsorImageInput) {
        editSponsorDropZone.addEventListener('click', function(e) {
            if (e.target.closest('button')) return;
            editSponsorImageInput.click();
        });

        editSponsorImageInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const file = e.target.files[0];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        editSponsorPreview.src = e.target.result;
                        editSponsorPreviewContainer.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            }
        });

        // Drag and drop
        editSponsorDropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            editSponsorDropZone.classList.add('border-purple-500', 'bg-purple-100');
        });

        editSponsorDropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            editSponsorDropZone.classList.remove('border-purple-500', 'bg-purple-100');
        });

        editSponsorDropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            editSponsorDropZone.classList.remove('border-purple-500', 'bg-purple-100');
            const files = e.dataTransfer.files;
            if (files && files[0]) {
                editSponsorImageInput.files = files;
                editSponsorImageInput.dispatchEvent(new Event('change'));
            }
        });
    }
});

function removeSponsorPreview() {
    document.getElementById('image').value = '';
    document.getElementById('sponsor-preview-container').classList.add('hidden');
}

function removeEditSponsorPreview() {
    document.getElementById('edit_image').value = '';
    document.getElementById('edit-sponsor-preview-container').classList.add('hidden');
}
</script>
@endsection



