@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Tags Management</h1>
            <button onclick="showAddTagModal()" class="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 transition-colors">
                <i class="fas fa-plus mr-2"></i>Add New Tag
            </button>
        </div>

        <!-- Tags Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tag Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage Count</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="tags-table-body">
                    @forelse($tags as $tag)
                    <tr data-tag-id="{{ $tag->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $tag->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="tag-name text-sm font-medium text-gray-900">{{ $tag->name }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tag->usage_count }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tag->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="editTag({{ $tag->id }}, '{{ $tag->name }}')" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="deleteTag({{ $tag->id }}, '{{ $tag->name }}')" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No tags found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $tags->links() }}
        </div>
    </div>
</div>

<!-- Add/Edit Tag Modal -->
<div id="tag-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="modal-title">Add New Tag</h3>
            <form id="tag-form" onsubmit="saveTag(event)">
                <input type="hidden" id="tag-id" name="tag_id">
                <div class="mb-4">
                    <label for="tag-name" class="block text-sm font-medium text-gray-700 mb-2">Tag Name</label>
                    <input type="text" id="tag-name" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="Enter tag name">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeTagModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentEditingTagId = null;

function showAddTagModal() {
    currentEditingTagId = null;
    document.getElementById('modal-title').textContent = 'Add New Tag';
    document.getElementById('tag-id').value = '';
    document.getElementById('tag-name').value = '';
    document.getElementById('tag-modal').classList.remove('hidden');
}

function editTag(id, name) {
    currentEditingTagId = id;
    document.getElementById('modal-title').textContent = 'Edit Tag';
    document.getElementById('tag-id').value = id;
    document.getElementById('tag-name').value = name;
    document.getElementById('tag-modal').classList.remove('hidden');
}

function closeTagModal() {
    document.getElementById('tag-modal').classList.add('hidden');
    currentEditingTagId = null;
}

async function saveTag(event) {
    event.preventDefault();
    
    const tagId = document.getElementById('tag-id').value;
    const tagName = document.getElementById('tag-name').value.trim();
    
    if (!tagName) {
        alert('Please enter a tag name');
        return;
    }
    
    const url = tagId ? `/admin/tags/${tagId}` : '/admin/tags';
    const method = tagId ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ name: tagName })
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload(); // Reload page to show updated tags
        } else {
            alert(data.message || 'Error saving tag');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error saving tag');
    }
}

async function deleteTag(id, name) {
    if (!confirm(`Are you sure you want to delete the tag "${name}"?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/admin/tags/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Remove row from table
            const row = document.querySelector(`tr[data-tag-id="${id}"]`);
            if (row) {
                row.remove();
            }
            
            // Check if table is empty
            const tbody = document.getElementById('tags-table-body');
            if (tbody && tbody.children.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No tags found</td></tr>';
            }
        } else {
            alert(data.message || 'Error deleting tag');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error deleting tag');
    }
}

// Close modal when clicking outside
document.getElementById('tag-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTagModal();
    }
});
</script>
@endsection
