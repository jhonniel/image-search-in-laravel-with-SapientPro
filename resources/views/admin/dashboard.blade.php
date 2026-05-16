@extends('layouts.admin')

@section('title', 'Dashboard - FindITFast Admin')

@section('content')
<div class="admin-page">
    @include('admin.partials.page-header', [
        'title' => 'Dashboard',
        'description' => 'Overview of reports, claims, and community activity.',
        'eyebrow' => 'Overview',
    ])

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @include('admin.partials.stat-card', [
            'label' => 'Total Reports',
            'value' => number_format($totalReports),
            'icon' => 'fa-file-lines',
            'iconBg' => 'bg-pink-100',
            'iconColor' => 'text-pink-600',
            'subtext' => ($reportsChange >= 0 ? '↑ ' : '↓ ') . abs($reportsChange) . '% vs prior period',
        ])
        @include('admin.partials.stat-card', [
            'label' => 'Items in Progress',
            'value' => number_format($itemsInProgress),
            'icon' => 'fa-clock',
            'iconBg' => 'bg-purple-100',
            'iconColor' => 'text-purple-600',
            'subtext' => ($itemsInProgressChange >= 0 ? '↑ ' : '↓ ') . abs($itemsInProgressChange) . '% vs prior period',
        ])
        @include('admin.partials.stat-card', [
            'label' => 'Contributors',
            'value' => number_format($contributors),
            'icon' => 'fa-gift',
            'iconBg' => 'bg-emerald-100',
            'iconColor' => 'text-emerald-600',
            'subtext' => ($contributorsChange >= 0 ? '↑ ' : '↓ ') . abs($contributorsChange) . '% vs prior period',
        ])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="admin-card admin-card-body">
            <h3 class="admin-panel-title mb-4">Lost vs Found Items</h3>
            @php
                $chartTotal = collect($monthlyData)->sum(fn ($row) => $row['lost'] + $row['found']);
            @endphp
            @if($chartTotal === 0)
            <p class="flex h-48 items-center justify-center text-sm text-gray-500">No lost or found reports in the last 6 months yet.</p>
            @else
            <div class="admin-chart">
                @foreach($monthlyData as $data)
                <div class="admin-chart-column">
                    <div class="admin-chart-bars">
                        <div class="flex flex-col items-center justify-end gap-0.5">
                            @if($data['lost'] > 0)
                            <span class="admin-chart-value text-pink-600">{{ $data['lost'] }}</span>
                            @endif
                            <div
                                class="admin-chart-bar-lost"
                                style="height: {{ max($data['lost_height'], $data['lost'] > 0 ? 12 : 0) }}px;"
                                title="Lost: {{ $data['lost'] }}"></div>
                        </div>
                        <div class="flex flex-col items-center justify-end gap-0.5">
                            @if($data['found'] > 0)
                            <span class="admin-chart-value text-blue-600">{{ $data['found'] }}</span>
                            @endif
                            <div
                                class="admin-chart-bar-found"
                                style="height: {{ max($data['found_height'], $data['found'] > 0 ? 12 : 0) }}px;"
                                title="Found: {{ $data['found'] }}"></div>
                        </div>
                    </div>
                    <span class="admin-chart-month">{{ $data['month'] }}</span>
                </div>
                @endforeach
            </div>
            @endif
            <div class="flex items-center justify-center gap-6 mt-4 pt-4 border-t border-gray-100">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span> Lost
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Found
                </div>
            </div>
        </div>

        <div class="admin-card admin-card-body">
            <h3 class="admin-panel-title mb-4">Recent Activity</h3>
            <div class="space-y-4 max-h-72 overflow-y-auto pr-1">
                @forelse($recentItems as $item)
                <div class="flex gap-3">
                    <span class="mt-2 w-2 h-2 rounded-full bg-purple-500 shrink-0"></span>
                    <div class="min-w-0">
                        <p class="text-sm text-gray-800 leading-snug">
                            @if($item['is_claimed'])
                                <strong>{{ $item['user_name'] }}</strong> requested to claim <strong>{{ \Illuminate\Support\Str::limit($item['description'], 50) }}</strong>
                            @else
                                <strong>{{ $item['user_name'] }}</strong> submitted a {{ $item['status'] }} report: <strong>{{ \Illuminate\Support\Str::limit($item['description'], 50) }}</strong>
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 mt-1">{{ $item['created_at']->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-8">No recent activity</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-toolbar flex items-center justify-between">
            <div>
                <h3 class="admin-panel-title">Claimed Items</h3>
                <p class="admin-panel-subtitle">Recently completed claims</p>
            </div>
            <a href="{{ route('claimed') }}" class="text-sm font-medium text-purple-600 hover:text-purple-700">
                View all <i class="fas fa-arrow-right ml-1 text-xs"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-white">
                        <th class="admin-th">Type</th>
                        <th class="admin-th">Description</th>
                        <th class="admin-th hidden md:table-cell">Uploader</th>
                        <th class="admin-th hidden lg:table-cell">Claimed by</th>
                        <th class="admin-th">When</th>
                        <th class="admin-th text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($claimedItems as $item)
                    <tr class="admin-table-row">
                        <td class="admin-td">
                            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium {{ $item['item_type'] === 'lost' ? 'bg-red-50 text-red-700 ring-1 ring-red-600/10' : 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/10' }}">
                                {{ ucfirst($item['item_type']) }}
                            </span>
                        </td>
                        <td class="admin-td max-w-xs">
                            <span class="text-gray-900 line-clamp-2">{{ \Illuminate\Support\Str::limit($item['description'], 60) }}</span>
                        </td>
                        <td class="admin-td hidden md:table-cell">{{ $item['uploader_name'] }}</td>
                        <td class="admin-td hidden lg:table-cell">
                            <div class="font-medium text-gray-900">{{ $item['claimed_by_name'] }}</div>
                            <div class="text-xs text-gray-500">{{ $item['claimed_by_email'] }}</div>
                        </td>
                        <td class="admin-td whitespace-nowrap text-gray-500">{{ $item['claimed_at'] ? $item['claimed_at']->diffForHumans() : '—' }}</td>
                        <td class="admin-td text-right">
                            <a href="{{ route('claimed') }}?upload_id={{ $item['upload_id'] }}" class="text-sm font-medium text-purple-600 hover:text-purple-700">Details</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No claimed items yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-toolbar">
            <h3 class="admin-panel-title">Top Contributors</h3>
            <p class="admin-panel-subtitle">Most active community members</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-white">
                        <th class="admin-th w-16">Rank</th>
                        <th class="admin-th">User</th>
                        <th class="admin-th text-center whitespace-nowrap">Reports</th>
                        <th class="admin-th text-center whitespace-nowrap hidden sm:table-cell">Verified</th>
                        <th class="admin-th text-center whitespace-nowrap hidden sm:table-cell">Claimed</th>
                        <th class="admin-th whitespace-nowrap hidden md:table-cell">Last active</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($topContributors as $index => $contributor)
                    <tr class="admin-table-row">
                        <td class="admin-td">
                            @if($index === 0)
                                <i class="fas fa-medal text-amber-500"></i>
                            @elseif($index === 1)
                                <i class="fas fa-medal text-gray-400"></i>
                            @elseif($index === 2)
                                <i class="fas fa-medal text-orange-400"></i>
                            @else
                                <span class="text-gray-400 text-xs font-medium">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td class="admin-td font-medium text-gray-900">
                            {{ $contributor['username'] !== 'N/A' ? $contributor['username'] : $contributor['name'] }}
                        </td>
                        <td class="admin-td text-center tabular-nums">{{ $contributor['report_count'] }}</td>
                        <td class="admin-td text-center tabular-nums hidden sm:table-cell">{{ $contributor['verified_count'] }}</td>
                        <td class="admin-td text-center tabular-nums hidden sm:table-cell">{{ $contributor['claimed_count'] }}</td>
                        <td class="admin-td text-gray-500 hidden md:table-cell">{{ $contributor['last_active'] ? $contributor['last_active']->diffForHumans() : '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No contributors yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
