@extends('layouts.admin')

@section('title', 'Tags Management - FindITFast Admin')

@section('content')
<div class="admin-page">
    @include('admin.partials.page-header', [
        'title' => 'Tags Management',
        'description' => 'Manage item tags and categories used across reports.',
        'actions' => '<button type="button" onclick="showAddTagModal()" class="admin-btn-primary"><i class="fas fa-plus text-xs"></i> Add New Tag</button>',
    ])

    @include('admin.partials.alert')

    <div class="admin-card">
        <div class="admin-toolbar">
            <div>
                <h3 class="admin-panel-title">All Tags</h3>
                <p class="admin-panel-subtitle">{{ $tags->total() }} tag(s) in the system</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-white">
                        <th class="admin-th w-16">ID</th>
                        <th class="admin-th">Tag Name</th>
                        <th class="admin-th text-center">Usage</th>
                        <th class="admin-th hidden sm:table-cell">Created</th>
                        <th class="admin-th text-right w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="tags-table-body">
                    @forelse($tags as $tag)
                    <tr class="admin-table-row" data-tag-id="{{ $tag->id }}">
                        <td class="admin-td text-gray-500 tabular-nums">{{ $tag->id }}</td>
                        <td class="admin-td">
                            <span class="tag-name font-medium text-gray-900">{{ $tag->name }}</span>
                        </td>
                        <td class="admin-td text-center">
                            <span class="inline-flex min-w-[2rem] items-center justify-center px-2 py-1 rounded-lg bg-gray-50 text-sm font-semibold text-gray-800 tabular-nums ring-1 ring-gray-200/80">{{ $tag->usage_count }}</span>
                        </td>
                        <td class="admin-td text-gray-500 hidden sm:table-cell whitespace-nowrap">{{ $tag->created_at->format('M d, Y') }}</td>
                        <td class="admin-td">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" onclick="editTag({{ $tag->id }}, @json($tag->name))" class="admin-icon-btn-purple" title="Edit tag">
                                    <i class="fas fa-pen text-sm"></i>
                                </button>
                                <button type="button" onclick="deleteTag({{ $tag->id }}, @json($tag->name))" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Delete tag">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="mx-auto w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                                <i class="fas fa-tags text-2xl text-gray-400"></i>
                            </div>
                            <p class="text-base font-semibold text-gray-900">No tags yet</p>
                            <p class="text-sm text-gray-500 mt-1">Create your first tag to get started.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tags->hasPages())
        <div class="px-4 sm:px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $tags->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Add/Edit Tag Modal -->
<div id="tag-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeTagModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900" id="modal-title">Add New Tag</h3>
                <button type="button" onclick="closeTagModal()" class="admin-icon-btn"><i class="fas fa-times"></i></button>
            </div>
            <form id="tag-form" onsubmit="saveTag(event)" class="p-6 space-y-4">
                <input type="hidden" id="tag-id" name="tag_id">
                <div>
                    <label for="tag-name" class="block text-sm font-medium text-gray-700 mb-2">Tag Name</label>
                    <input type="text" id="tag-name" name="name" required class="admin-input px-4 py-2.5" placeholder="Enter tag name">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeTagModal()" class="admin-btn-secondary">Cancel</button>
                    <button type="submit" class="admin-btn-primary">Save</button>
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
    if (!tagName) { alert('Please enter a tag name'); return; }
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
        if (data.success) location.reload();
        else alert(data.message || 'Error saving tag');
    } catch (error) {
        console.error('Error:', error);
        alert('Error saving tag');
    }
}

async function deleteTag(id, name) {
    if (!confirm(`Are you sure you want to delete the tag "${name}"?`)) return;
    try {
        const response = await fetch(`/admin/tags/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await response.json();
        if (data.success) {
            const row = document.querySelector(`tr[data-tag-id="${id}"]`);
            if (row) row.remove();
            const tbody = document.getElementById('tags-table-body');
            if (tbody && tbody.children.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-16 text-center text-sm text-gray-500">No tags found</td></tr>';
            }
        } else alert(data.message || 'Error deleting tag');
    } catch (error) {
        console.error('Error:', error);
        alert('Error deleting tag');
    }
}
</script>
@endsection
