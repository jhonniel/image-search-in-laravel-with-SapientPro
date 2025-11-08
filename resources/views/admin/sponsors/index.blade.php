@extends('layouts.admin')

@section('title', 'Sponsors Management - FindITFast Admin')

@section('content')
<div class="p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Sponsors Management</h1>
        <p class="text-gray-600">Manage sponsors displayed on the landing page</p>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- Toggle Show Sponsors Carousel -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6 border border-gray-200">
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
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6 border border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Add New Sponsor</h2>
        <form action="{{ route('admin.sponsors.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Sponsor Name *</label>
                    <input type="text" id="name" name="name" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary"
                           placeholder="Enter sponsor name">
                </div>
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Sponsor Logo *</label>
                    <input type="file" id="image" name="image" required accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary">
                    <p class="text-xs text-gray-500 mt-1">Max 5MB, JPEG, PNG, GIF, WEBP</p>
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

    <!-- Sponsors List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Current Sponsors</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($sponsors as $sponsor)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <img src="{{ $sponsor->image_path }}" alt="{{ $sponsor->name }}" 
                                 class="h-16 w-16 object-contain bg-gray-50 rounded-lg p-2">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $sponsor->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $sponsor->order }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sponsor->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $sponsor->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="editSponsor({{ $sponsor->id }})" 
                                    class="text-purple-primary hover:text-purple-600 mr-4">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form action="{{ route('admin.sponsors.destroy', $sponsor) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this sponsor?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i> Delete
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
                    <label for="edit_image" class="block text-sm font-medium text-gray-700 mb-2">Sponsor Logo</label>
                    <input type="file" id="edit_image" name="image" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary">
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
    document.getElementById('editForm').action = `/admin/sponsors/${id}`;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Toggle show sponsors carousel
document.getElementById('toggleShowSponsors').addEventListener('change', function() {
    const show = this.checked;
    fetch('{{ route("admin.sponsors.toggle-show") }}', {
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
</script>
@endsection



