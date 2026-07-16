@extends('layouts.admin')

@section('title', 'Reported Items - FindITFast Admin')

@section('content')
@php
    $statusLabels = [
        'pending' => ['label' => 'Pending', 'class' => 'bg-amber-50 text-amber-800 ring-amber-600/10'],
        'reviewed' => ['label' => 'Reviewed', 'class' => 'bg-emerald-50 text-emerald-800 ring-emerald-600/10'],
        'dismissed' => ['label' => 'Dismissed', 'class' => 'bg-gray-50 text-gray-700 ring-gray-500/10'],
    ];
    $itemLabelColors = [
        'scam' => 'bg-red-100 text-red-800',
        'fake' => 'bg-orange-100 text-orange-800',
        'inappropriate' => 'bg-pink-100 text-pink-800',
        'spam' => 'bg-yellow-100 text-yellow-800',
        'stolen' => 'bg-purple-100 text-purple-800',
        'other' => 'bg-gray-100 text-gray-800',
    ];
    $userLabelColors = [
        'false_claim' => 'bg-red-100 text-red-800',
        'scam_claimer' => 'bg-orange-100 text-orange-800',
        'impersonation' => 'bg-pink-100 text-pink-800',
        'harassment' => 'bg-purple-100 text-purple-800',
        'other' => 'bg-gray-100 text-gray-800',
    ];
@endphp
<div class="admin-page">
    @include('admin.partials.page-header', [
        'title' => 'Reported Items',
        'description' => 'Review scam listings and users reported for false claims from Claim & Verify and Messages.',
    ])

    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('item-reports.index', ['tab' => 'items', 'status' => $status]) }}"
           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium {{ $tab === 'items' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
            <i class="fas fa-box mr-2"></i> Reported listings
            <span class="ml-2 text-xs opacity-80">({{ number_format($itemStats['total_reports']) }})</span>
        </a>
        <a href="{{ route('item-reports.index', ['tab' => 'users', 'status' => $status]) }}"
           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium {{ $tab === 'users' ? 'bg-purple-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
            <i class="fas fa-user-slash mr-2"></i> Reported users
            <span class="ml-2 text-xs opacity-80">({{ number_format($userStats['total_reports']) }})</span>
        </a>
    </div>

    @if($tab === 'users')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @include('admin.partials.stat-card', ['label' => 'Total Reports', 'value' => number_format($userStats['total_reports']), 'icon' => 'fa-flag', 'iconBg' => 'bg-red-100', 'iconColor' => 'text-red-600'])
            @include('admin.partials.stat-card', ['label' => 'Flagged Users', 'value' => number_format($userStats['flagged_users']), 'icon' => 'fa-users', 'iconBg' => 'bg-purple-100', 'iconColor' => 'text-purple-600'])
            @include('admin.partials.stat-card', ['label' => 'Pending', 'value' => number_format($userStats['pending']), 'icon' => 'fa-clock', 'iconBg' => 'bg-amber-100', 'iconColor' => 'text-amber-600'])
            @include('admin.partials.stat-card', ['label' => 'Reviewed', 'value' => number_format($userStats['reviewed']), 'icon' => 'fa-check', 'iconBg' => 'bg-emerald-100', 'iconColor' => 'text-emerald-600'])
            @include('admin.partials.stat-card', ['label' => 'Dismissed', 'value' => number_format($userStats['dismissed']), 'icon' => 'fa-ban', 'iconBg' => 'bg-gray-100', 'iconColor' => 'text-gray-600'])
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @include('admin.partials.stat-card', ['label' => 'Total Reports', 'value' => number_format($itemStats['total_reports']), 'icon' => 'fa-flag', 'iconBg' => 'bg-red-100', 'iconColor' => 'text-red-600'])
            @include('admin.partials.stat-card', ['label' => 'Flagged Items', 'value' => number_format($itemStats['flagged_items']), 'icon' => 'fa-box', 'iconBg' => 'bg-purple-100', 'iconColor' => 'text-purple-600'])
            @include('admin.partials.stat-card', ['label' => 'Pending', 'value' => number_format($itemStats['pending']), 'icon' => 'fa-clock', 'iconBg' => 'bg-amber-100', 'iconColor' => 'text-amber-600'])
            @include('admin.partials.stat-card', ['label' => 'Reviewed', 'value' => number_format($itemStats['reviewed']), 'icon' => 'fa-check', 'iconBg' => 'bg-emerald-100', 'iconColor' => 'text-emerald-600'])
            @include('admin.partials.stat-card', ['label' => 'Dismissed', 'value' => number_format($itemStats['dismissed']), 'icon' => 'fa-ban', 'iconBg' => 'bg-gray-100', 'iconColor' => 'text-gray-600'])
        </div>
    @endif

    @include('admin.partials.alert')

    <div class="admin-card">
        <div class="admin-toolbar flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="admin-panel-title">{{ $tab === 'users' ? 'Reported claimers / users' : 'Flagged listings' }}</h2>
                <p class="admin-panel-subtitle">
                    @if($tab === 'users')
                        {{ $userReports->count() }} user report{{ $userReports->count() === 1 ? '' : 's' }}
                    @else
                        {{ $reportedItems->count() }} item{{ $reportedItems->count() === 1 ? '' : 's' }} with user reports
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @foreach(['all' => 'All', 'pending' => 'Pending', 'reviewed' => 'Reviewed', 'dismissed' => 'Dismissed'] as $key => $label)
                    <a href="{{ route('item-reports.index', ['tab' => $tab, 'status' => $key]) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $status === $key ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if($tab === 'users')
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-white">
                            <th class="admin-th">Reported user</th>
                            <th class="admin-th">Reason</th>
                            <th class="admin-th hidden lg:table-cell">Explanation</th>
                            <th class="admin-th hidden md:table-cell">Related item</th>
                            <th class="admin-th">Status</th>
                            <th class="admin-th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($userReports as $report)
                        <tr class="admin-table-row">
                            <td class="admin-td">
                                <p class="text-sm font-semibold text-gray-900">{{ $report['reported_name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $report['reported_email'] }}</p>
                                <p class="text-xs text-gray-400 mt-1">Reported by {{ $report['reporter_name'] }}</p>
                            </td>
                            <td class="admin-td">
                                <span class="px-2 py-0.5 rounded-md text-xs font-medium {{ $userLabelColors[$report['label']] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $report['label_name'] }}
                                </span>
                                <p class="text-xs text-gray-500 mt-1">{{ $report['created_at'] }}</p>
                            </td>
                            <td class="admin-td hidden lg:table-cell max-w-xs">
                                <p class="text-sm text-gray-700 line-clamp-3">{{ $report['explanation'] }}</p>
                            </td>
                            <td class="admin-td hidden md:table-cell">
                                @if($report['item'])
                                    <p class="text-xs font-medium text-gray-900">{{ \Illuminate\Support\Str::limit($report['item']['description'] ?: 'Item', 40) }}</p>
                                    <p class="text-xs text-gray-500">{{ ucfirst($report['item']['item_type'] ?? '') }} · {{ $report['upload_id'] }}</p>
                                    @if($report['item']['deleted'])
                                        <span class="text-xs text-gray-500">Deleted</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">No linked item</span>
                                @endif
                            </td>
                            <td class="admin-td">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium ring-1 {{ ($statusLabels[$report['status']] ?? $statusLabels['pending'])['class'] }}">
                                    {{ ($statusLabels[$report['status']] ?? $statusLabels['pending'])['label'] }}
                                </span>
                            </td>
                            <td class="admin-td text-right">
                                <form method="POST" action="{{ route('user-reports.update', $report['id']) }}" class="inline-flex flex-wrap items-center justify-end gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="redirect_status" value="{{ $status }}">
                                    <select name="status" class="admin-select !py-1.5 !text-xs min-w-[120px]">
                                        <option value="pending" @selected($report['status'] === 'pending')>Pending</option>
                                        <option value="reviewed" @selected($report['status'] === 'reviewed')>Reviewed</option>
                                        <option value="dismissed" @selected($report['status'] === 'dismissed')>Dismissed</option>
                                    </select>
                                    <button type="submit" class="admin-btn-secondary !py-1.5 !px-3 text-xs">Update</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-user-slash text-2xl text-gray-400"></i>
                                </div>
                                <p class="text-base font-semibold text-gray-900">No user reports</p>
                                <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">
                                    When owners report claimers from Messages, those reports will appear here.
                                </p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="space-y-4 p-4 sm:p-6">
                @forelse($reportedItems as $item)
                <div class="border border-gray-200 rounded-xl overflow-hidden bg-white" data-upload-id="{{ $item['upload_id'] }}">
                    <div class="p-4 sm:p-5 flex flex-col lg:flex-row gap-4">
                        <div class="w-full lg:w-28 h-28 rounded-lg bg-gray-100 overflow-hidden shrink-0">
                            @if(!empty($item['images'][0]['path']))
                                <img src="{{ $item['images'][0]['path'] }}" alt="Reported item" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <i class="fas fa-image text-2xl"></i>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ ($item['item_type'] ?? '') === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            {{ ucfirst($item['item_type'] ?? 'item') }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-600 text-white">
                                            <i class="fas fa-flag mr-1 text-[10px]"></i>
                                            {{ $item['report_count'] }} report{{ $item['report_count'] === 1 ? '' : 's' }}
                                        </span>
                                        @if($item['pending_count'] > 0)
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                {{ $item['pending_count'] }} pending
                                            </span>
                                        @endif
                                        @if($item['deleted_at'])
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-700">Deleted</span>
                                        @endif
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $item['description'] ?: 'No description' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Posted by {{ $item['uploader_name'] }}
                                        @if($item['uploader_email'])
                                            &lt;{{ $item['uploader_email'] }}&gt;
                                        @endif
                                    </p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button"
                                            class="admin-btn-secondary !py-2 !px-3 text-xs"
                                            onclick="toggleReports('{{ $item['upload_id'] }}')">
                                        <i class="fas fa-list mr-1"></i> View reports
                                    </button>
                                    @unless($item['deleted_at'])
                                    <button type="button"
                                            class="inline-flex items-center px-3 py-2 rounded-lg text-xs font-medium bg-red-50 text-red-700 border border-red-200 hover:bg-red-100"
                                            onclick="deleteReportedItem('{{ $item['upload_id'] }}')">
                                        <i class="fas fa-trash mr-1"></i> Delete item
                                    </button>
                                    @endunless
                                </div>
                            </div>

                            @if($item['location'])
                                <p class="text-xs text-gray-600"><i class="fas fa-map-marker-alt mr-1"></i>{{ $item['location'] }}</p>
                            @endif
                        </div>
                    </div>

                    <div id="reports-{{ $item['upload_id'] }}" class="hidden border-t border-gray-100 bg-gray-50">
                        <div class="p-4 sm:p-5 space-y-3">
                            <h3 class="text-sm font-semibold text-gray-900">Reports for this item</h3>
                            @foreach($item['reports'] as $report)
                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-md text-xs font-medium {{ $itemLabelColors[$report['label']] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $report['label_name'] }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium ring-1 {{ ($statusLabels[$report['status']] ?? $statusLabels['pending'])['class'] }}">
                                            {{ ($statusLabels[$report['status']] ?? $statusLabels['pending'])['label'] }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $report['created_at'] }}</span>
                                </div>
                                <p class="text-sm text-gray-800 mb-2">{{ $report['explanation'] }}</p>
                                <p class="text-xs text-gray-500 mb-3">
                                    Reported by {{ $report['reporter_name'] }}
                                    @if($report['reporter_email'])
                                        ({{ $report['reporter_email'] }})
                                    @endif
                                </p>
                                <form method="POST" action="{{ route('item-reports.update', $report['id']) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="admin-select !py-1.5 !text-xs min-w-[140px]">
                                        <option value="pending" @selected($report['status'] === 'pending')>Pending</option>
                                        <option value="reviewed" @selected($report['status'] === 'reviewed')>Reviewed</option>
                                        <option value="dismissed" @selected($report['status'] === 'dismissed')>Dismissed</option>
                                    </select>
                                    <button type="submit" class="admin-btn-secondary !py-1.5 !px-3 text-xs">Update status</button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-16 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-flag text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-base font-semibold text-gray-900">No reported items</p>
                    <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto">
                        When users report scam or suspicious listings from Claim & Verify, they will appear here.
                    </p>
                </div>
                @endforelse
            </div>
        @endif
    </div>
</div>

<script>
function toggleReports(uploadId) {
    const el = document.getElementById('reports-' + uploadId);
    if (el) el.classList.toggle('hidden');
}

async function deleteReportedItem(uploadId) {
    if (!confirm('Delete this item? All images for this listing will be soft-deleted. Reports will be marked reviewed.')) {
        return;
    }

    try {
        const response = await fetch(`/admin/item-reports/${uploadId}/delete-item`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
        });
        const data = await response.json();
        if (data.success) {
            alert(data.message || 'Item deleted.');
            window.location.reload();
        } else {
            alert(data.message || 'Failed to delete item.');
        }
    } catch (e) {
        console.error(e);
        alert('Failed to delete item. Please try again.');
    }
}
</script>
@endsection
