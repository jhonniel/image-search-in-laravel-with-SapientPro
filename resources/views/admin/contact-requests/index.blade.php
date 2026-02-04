@extends('layouts.admin')

@section('title', 'Contact Requests - FindITFast Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Contact Requests</h1>
            <p class="text-gray-600">View and respond to messages submitted via the public Contact Us form.</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm flex items-center gap-3">
            <div class="p-2 bg-purple-50 rounded-lg">
                <i class="fas fa-inbox text-purple-primary"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Requests</p>
                <p class="text-lg font-semibold text-gray-900">{{ \App\Models\ContactRequest::count() }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Inbox</h2>
                <p class="text-sm text-gray-500">Filter by status to focus on messages that need attention.</p>
            </div>
            <form method="GET" class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700">Status</label>
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent text-sm">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ $status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Requester</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Subject</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Message</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status &amp; Notes</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Received</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($contactRequests as $request)
                    <tr class="hover:bg-gray-50 align-top">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-primary font-semibold mr-3">
                                    {{ strtoupper(substr($request->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $request->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $request->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $request->subject ?: 'General Inquiry' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700 max-w-md">
                            <p class="whitespace-pre-line break-words">{{ $request->message }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <form method="POST" action="{{ route('contact-requests.update', $request) }}" class="space-y-3">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
                                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent text-sm">
                                        <option value="pending" {{ $request->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ $request->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="resolved" {{ $request->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Admin Notes</label>
                                    <textarea name="admin_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent text-sm" placeholder="Document follow-up actions or replies">{{ old('admin_notes', $request->admin_notes) }}</textarea>
                                </div>
                                <button type="submit" class="w-full inline-flex items-center justify-center px-3 py-2 bg-purple-primary text-white rounded-lg font-medium hover:bg-purple-700 transition-colors text-xs uppercase tracking-wide">
                                    Update
                                </button>
                            </form>
                            @if($request->resolved_at)
                            <p class="text-xs text-gray-500 mt-2 flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                Resolved {{ $request->resolved_at->format('M d, Y h:i A') }}
                            </p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                            <p class="font-semibold">{{ $request->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $request->created_at->format('h:i A') }}</p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-comment-slash text-gray-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No contact requests yet</h3>
                            <p class="text-sm text-gray-500 max-w-md mx-auto">Once visitors send messages through the Contact Us form, they will appear here for follow-up.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contactRequests->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $contactRequests->links() }}
        </div>
        @endif
    </div>
    
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Contact Help Sections</h2>
                <p class="text-sm text-gray-500">These cards appear above the Contact Us form on the landing page.</p>
            </div>
            <button type="button" onclick="openHelpModal()" class="inline-flex items-center px-4 py-2 bg-purple-primary text-white rounded-lg font-medium hover:bg-purple-700 transition-colors text-sm">
                <i class="fas fa-plus mr-2"></i>Add Section
            </button>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($helpSections as $section)
            <div class="p-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="uppercase text-xs font-semibold {{ $section->is_active ? 'text-green-600' : 'text-gray-400' }}">{{ $section->is_active ? 'Visible' : 'Hidden' }}</p>
                    <h3 class="text-xl font-bold text-gray-900">{{ $section->heading }}</h3>
                    <p class="text-gray-600 mt-2 whitespace-pre-line">{{ $section->body }}</p>
                    @if($section->cta_label && $section->cta_url)
                    <a href="{{ $section->cta_url }}" target="_blank" class="inline-flex items-center text-purple-primary hover:text-purple-700 font-semibold text-sm mt-3">
                        {{ $section->cta_label }} <i class="fas fa-arrow-up-right-from-square ml-1 text-xs"></i>
                    </a>
                    @endif
                </div>
                <div class="flex items-center space-x-3">
                    <button type="button" onclick='openHelpModal(@json($section))' class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50">Edit</button>
                    <form method="POST" action="{{ route('contact-requests.help.delete', $section) }}" onsubmit="return confirm('Remove this section?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50">Delete</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="p-6 text-center text-gray-500">
                No custom sections yet. Click “Add Section” to create one.
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Help Section Modal -->
<div id="help-modal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-semibold text-gray-900" id="help-modal-title">Add Section</h3>
            <button type="button" onclick="closeHelpModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('contact-requests.help.upsert') }}" id="help-form" class="space-y-4">
            @csrf
            <input type="hidden" name="id" id="help-id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Heading <span class="text-red-500">*</span></label>
                <input type="text" name="heading" id="help-heading" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Body <span class="text-red-500">*</span></label>
                <textarea name="body" id="help-body" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CTA Label</label>
                    <input type="text" name="cta_label" id="help-cta-label" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CTA URL</label>
                    <input type="url" name="cta_url" id="help-cta-url" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" id="help-is-active" class="rounded border-gray-300 text-purple-primary focus:ring-purple-primary">
                    <span class="ml-2 text-sm text-gray-700">Show on landing page</span>
                </label>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                    <input type="number" name="display_order" id="help-display-order" min="0" value="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" onclick="closeHelpModal()" class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-purple-primary text-white rounded-lg text-sm font-semibold hover:bg-purple-700">Save Section</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openHelpModal(section = null) {
    const modal = document.getElementById('help-modal');
    const title = document.getElementById('help-modal-title');
    const fieldId = document.getElementById('help-id');
    const heading = document.getElementById('help-heading');
    const body = document.getElementById('help-body');
    const ctaLabel = document.getElementById('help-cta-label');
    const ctaUrl = document.getElementById('help-cta-url');
    const displayOrder = document.getElementById('help-display-order');
    const isActive = document.getElementById('help-is-active');

    if (section) {
        title.textContent = 'Edit Section';
        fieldId.value = section.id || '';
        heading.value = section.heading || '';
        body.value = section.body || '';
        ctaLabel.value = section.cta_label || '';
        ctaUrl.value = section.cta_url || '';
        displayOrder.value = section.display_order ?? 0;
        isActive.checked = section.is_active ?? false;
    } else {
        title.textContent = 'Add Section';
        fieldId.value = '';
        heading.value = '';
        body.value = '';
        ctaLabel.value = '';
        ctaUrl.value = '';
        displayOrder.value = 0;
        isActive.checked = true;
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeHelpModal() {
    const modal = document.getElementById('help-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endpush

