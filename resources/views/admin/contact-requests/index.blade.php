@extends('layouts.admin')

@section('title', 'Contact Requests - FindITFast Admin')

@section('content')
@php
    $statusLabels = [
        'pending' => ['label' => 'Pending', 'class' => 'bg-amber-50 text-amber-800 ring-amber-600/10'],
        'in_progress' => ['label' => 'In progress', 'class' => 'bg-blue-50 text-blue-800 ring-blue-600/10'],
        'resolved' => ['label' => 'Resolved', 'class' => 'bg-emerald-50 text-emerald-800 ring-emerald-600/10'],
    ];
@endphp
<div class="admin-page">
    @include('admin.partials.page-header', [
        'title' => 'Contact Requests',
        'description' => 'Review messages from the public Contact Us form and manage landing-page help sections.',
    ])

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.partials.stat-card', ['label' => 'Total', 'value' => number_format($stats['total']), 'icon' => 'fa-inbox', 'iconBg' => 'bg-purple-100', 'iconColor' => 'text-purple-600'])
        @include('admin.partials.stat-card', ['label' => 'Pending', 'value' => number_format($stats['pending']), 'icon' => 'fa-clock', 'iconBg' => 'bg-amber-100', 'iconColor' => 'text-amber-600'])
        @include('admin.partials.stat-card', ['label' => 'In progress', 'value' => number_format($stats['in_progress']), 'icon' => 'fa-spinner', 'iconBg' => 'bg-blue-100', 'iconColor' => 'text-blue-600'])
        @include('admin.partials.stat-card', ['label' => 'Resolved', 'value' => number_format($stats['resolved']), 'icon' => 'fa-circle-check', 'iconBg' => 'bg-emerald-100', 'iconColor' => 'text-emerald-600'])
    </div>

    @include('admin.partials.alert')

    {{-- Inbox --}}
    <div class="admin-card">
        <div class="admin-toolbar flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="admin-panel-title">Inbox</h2>
                <p class="admin-panel-subtitle">{{ $contactRequests->total() }} message{{ $contactRequests->total() === 1 ? '' : 's' }} in this view</p>
            </div>
            <form method="GET" class="flex flex-wrap items-center gap-2">
                @foreach(['all' => 'All', 'pending' => 'Pending', 'in_progress' => 'In progress', 'resolved' => 'Resolved'] as $key => $label)
                    <a href="{{ route('contact-requests.index', ['status' => $key]) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $status === $key ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-white">
                        <th class="admin-th">From</th>
                        <th class="admin-th">Subject</th>
                        <th class="admin-th hidden lg:table-cell max-w-xs">Preview</th>
                        <th class="admin-th">Status</th>
                        <th class="admin-th hidden md:table-cell">Received</th>
                        <th class="admin-th text-right w-28">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contactRequests as $contact)
                    @php
                        $badge = $statusLabels[$contact->status] ?? $statusLabels['pending'];
                        $initials = strtoupper(substr(preg_replace('/\s+/', '', $contact->name) ?: 'U', 0, 2));
                    @endphp
                    <tr class="admin-table-row">
                        <td class="admin-td">
                            <div class="flex items-center gap-3 min-w-[180px]">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center text-xs font-semibold text-white shrink-0">
                                    {{ $initials }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $contact->name }}</p>
                                    <a href="mailto:{{ $contact->email }}" class="text-xs text-purple-600 hover:text-purple-700 truncate block">{{ $contact->email }}</a>
                                </div>
                            </div>
                        </td>
                        <td class="admin-td">
                            <p class="text-sm font-medium text-gray-900">{{ $contact->subject ?: 'General inquiry' }}</p>
                        </td>
                        <td class="admin-td hidden lg:table-cell max-w-xs">
                            <p class="text-sm text-gray-600 line-clamp-2">{{ $contact->message }}</p>
                        </td>
                        <td class="admin-td">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium ring-1 {{ $badge['class'] }}">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                        <td class="admin-td hidden md:table-cell whitespace-nowrap">
                            <p class="text-sm text-gray-900">{{ $contact->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $contact->created_at->format('g:i A') }}</p>
                        </td>
                        <td class="admin-td text-right">
                            <button type="button"
                                    class="admin-btn-secondary !py-2 !px-3 text-xs manage-request-btn"
                                    data-request="{{ htmlspecialchars(json_encode([
                                        'name' => $contact->name,
                                        'email' => $contact->email,
                                        'subject' => $contact->subject,
                                        'message' => $contact->message,
                                        'status' => $contact->status,
                                        'admin_notes' => $contact->admin_notes,
                                        'created_at' => $contact->created_at->toIso8601String(),
                                        'resolved_at' => $contact->resolved_at?->toIso8601String(),
                                        'update_url' => route('contact-requests.update', $contact),
                                    ]), ENT_QUOTES, 'UTF-8') }}">
                                <i class="fas fa-reply text-xs"></i>
                                Manage
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-inbox text-2xl text-gray-400"></i>
                            </div>
                            <p class="text-base font-semibold text-gray-900">No messages here</p>
                            <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">
                                @if($status === 'all')
                                    When visitors use Contact Us, messages will show up in this inbox.
                                @else
                                    No {{ str_replace('_', ' ', $status) }} requests. Try another filter.
                                @endif
                            </p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contactRequests->hasPages())
        <div class="px-4 sm:px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $contactRequests->links() }}
        </div>
        @endif
    </div>

    {{-- Help sections --}}
    <div class="admin-card">
        <div class="admin-toolbar flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="admin-panel-title">Contact help sections</h2>
                <p class="admin-panel-subtitle">Content cards shown above the Contact Us form on the homepage.</p>
            </div>
            <button type="button" onclick="openHelpModal()" class="admin-btn-primary shrink-0">
                <i class="fas fa-plus text-xs"></i>
                Add section
            </button>
        </div>

        @if($helpSections->isEmpty())
        <div class="admin-card-body text-center py-12">
            <div class="w-14 h-14 rounded-2xl bg-purple-50 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-layer-group text-xl text-purple-600"></i>
            </div>
            <p class="text-sm font-semibold text-gray-900">No help sections yet</p>
            <p class="text-sm text-gray-500 mt-1">Add cards to guide visitors before they send a message.</p>
        </div>
        @else
        <div class="p-4 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($helpSections as $section)
            <article class="rounded-xl border border-gray-200 bg-gray-50/50 p-5 flex flex-col h-full">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ $section->is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10' : 'bg-gray-100 text-gray-600 ring-1 ring-gray-500/10' }}">
                        {{ $section->is_active ? 'Visible' : 'Hidden' }}
                    </span>
                    <span class="text-xs text-gray-400">Order {{ $section->display_order }}</span>
                </div>
                <h3 class="text-base font-semibold text-gray-900">{{ $section->heading }}</h3>
                <p class="text-sm text-gray-600 mt-2 line-clamp-3 flex-1 whitespace-pre-line">{{ $section->body }}</p>
                @if($section->cta_label && $section->cta_url)
                <p class="text-xs text-purple-600 mt-3 truncate">
                    <i class="fas fa-link mr-1"></i>{{ $section->cta_label }}
                </p>
                @endif
                <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-200/80">
                    <button type="button" onclick='openHelpModal(@json($section))' class="admin-btn-secondary flex-1 !py-2 text-xs">
                        <i class="fas fa-pen text-xs"></i> Edit
                    </button>
                    <form method="POST" action="{{ route('contact-requests.help.delete', $section) }}" class="flex-1" onsubmit="return confirm('Remove this section?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn-danger w-full !py-2 text-xs">
                            <i class="fas fa-trash text-xs"></i> Delete
                        </button>
                    </form>
                </div>
            </article>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Manage request modal --}}
<div id="request-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeRequestModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 shrink-0">
                <h3 class="text-lg font-semibold text-gray-900">Message details</h3>
                <button type="button" onclick="closeRequestModal()" class="admin-icon-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="request-form" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                @csrf
                @method('PUT')
                <div class="px-6 py-5 overflow-y-auto flex-1 space-y-4">
                    <div id="request-meta" class="rounded-xl bg-gray-50 border border-gray-100 p-4 text-sm space-y-1"></div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Message</p>
                        <p id="request-message" class="text-sm text-gray-800 whitespace-pre-line leading-relaxed"></p>
                    </div>
                    <div>
                        <label for="request-status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="request-status" class="admin-select w-full">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In progress</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                    <div>
                        <label for="request-notes" class="block text-sm font-medium text-gray-700 mb-1">Admin notes</label>
                        <textarea name="admin_notes" id="request-notes" rows="4" class="admin-input py-2.5 resize-y" placeholder="Follow-up actions, reply summary, etc."></textarea>
                    </div>
                    <p id="request-resolved" class="text-xs text-emerald-700 hidden">
                        <i class="fas fa-circle-check mr-1"></i>
                        <span></span>
                    </p>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-2 shrink-0 bg-gray-50/80">
                    <button type="button" onclick="closeRequestModal()" class="admin-btn-secondary">Cancel</button>
                    <button type="submit" class="admin-btn-primary">
                        <i class="fas fa-save text-xs"></i>
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Help section modal --}}
<div id="help-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeHelpModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
                <h3 class="text-lg font-semibold text-gray-900" id="help-modal-title">Add section</h3>
                <button type="button" onclick="closeHelpModal()" class="admin-icon-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('contact-requests.help.upsert') }}" id="help-form" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="id" id="help-id">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Heading <span class="text-red-500">*</span></label>
                    <input type="text" name="heading" id="help-heading" required class="admin-input py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Body <span class="text-red-500">*</span></label>
                    <textarea name="body" id="help-body" rows="4" required class="admin-input py-2.5 resize-y"></textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CTA label</label>
                        <input type="text" name="cta_label" id="help-cta-label" class="admin-input py-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CTA URL</label>
                        <input type="url" name="cta_url" id="help-cta-url" class="admin-input py-2.5">
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" id="help-is-active" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-sm text-gray-700">Show on landing page</span>
                    </label>
                    <div class="flex-1 sm:max-w-[140px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display order</label>
                        <input type="number" name="display_order" id="help-display-order" min="0" value="0" class="admin-input py-2.5">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="closeHelpModal()" class="admin-btn-secondary">Cancel</button>
                    <button type="submit" class="admin-btn-primary">Save section</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openRequestModal(data) {
    const modal = document.getElementById('request-modal');
    const form = document.getElementById('request-form');
    form.action = data.update_url;

    const created = new Date(data.created_at);
    document.getElementById('request-meta').innerHTML = `
        <p class="font-semibold text-gray-900">${escapeHtml(data.name)}</p>
        <p><a href="mailto:${escapeHtml(data.email)}" class="text-purple-600 hover:text-purple-700">${escapeHtml(data.email)}</a></p>
        <p class="text-gray-600 mt-2"><span class="font-medium text-gray-700">Subject:</span> ${escapeHtml(data.subject || 'General inquiry')}</p>
        <p class="text-xs text-gray-500 mt-1">Received ${created.toLocaleString()}</p>
    `;
    document.getElementById('request-message').textContent = data.message || '';
    document.getElementById('request-status').value = data.status || 'pending';
    document.getElementById('request-notes').value = data.admin_notes || '';

    const resolvedEl = document.getElementById('request-resolved');
    if (data.resolved_at) {
        resolvedEl.classList.remove('hidden');
        resolvedEl.querySelector('span').textContent = 'Resolved ' + new Date(data.resolved_at).toLocaleString();
    } else {
        resolvedEl.classList.add('hidden');
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeRequestModal() {
    document.getElementById('request-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

function openHelpModal(section = null) {
    const modal = document.getElementById('help-modal');
    const title = document.getElementById('help-modal-title');
    document.getElementById('help-id').value = section?.id ?? '';
    document.getElementById('help-heading').value = section?.heading ?? '';
    document.getElementById('help-body').value = section?.body ?? '';
    document.getElementById('help-cta-label').value = section?.cta_label ?? '';
    document.getElementById('help-cta-url').value = section?.cta_url ?? '';
    document.getElementById('help-display-order').value = section?.display_order ?? 0;
    document.getElementById('help-is-active').checked = section ? !!section.is_active : true;
    title.textContent = section ? 'Edit section' : 'Add section';
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeHelpModal() {
    document.getElementById('help-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

function escapeHtml(text) {
    const d = document.createElement('div');
    d.textContent = text ?? '';
    return d.innerHTML;
}

document.querySelectorAll('.manage-request-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        openRequestModal(JSON.parse(btn.dataset.request));
    });
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeRequestModal();
        closeHelpModal();
    }
});
</script>
@endpush
