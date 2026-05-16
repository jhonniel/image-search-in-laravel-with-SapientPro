@extends('layouts.admin')

@section('title', 'Insights - FindITFast Admin')

@section('content')
@php
    $claimRate = $totalItems > 0 ? number_format(($claimedItems / $totalItems) * 100, 1) : 0;
    $engagementRate = $totalUsers > 0 ? number_format(($activeUsers / $totalUsers) * 100, 1) : 0;
    $foundRatio = $totalItems > 0 ? number_format(($foundItems / $totalItems) * 100, 1) : 0;
@endphp
<div class="admin-page">
    @include('admin.partials.page-header', [
        'title' => 'Insights & Analytics',
        'description' => 'Comprehensive analytics and performance metrics across the platform.',
        'eyebrow' => 'Analytics',
    ])

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        @include('admin.partials.stat-card', ['label' => 'Total Items', 'value' => number_format($totalItems), 'icon' => 'fa-box', 'iconBg' => 'bg-blue-100', 'iconColor' => 'text-blue-600'])
        @include('admin.partials.stat-card', ['label' => 'Lost Items', 'value' => number_format($lostItems), 'icon' => 'fa-search', 'iconBg' => 'bg-red-100', 'iconColor' => 'text-red-600'])
        @include('admin.partials.stat-card', ['label' => 'Found Items', 'value' => number_format($foundItems), 'icon' => 'fa-hand-holding', 'iconBg' => 'bg-emerald-100', 'iconColor' => 'text-emerald-600'])
        @include('admin.partials.stat-card', ['label' => 'Claimed Items', 'value' => number_format($claimedItems), 'icon' => 'fa-check-circle', 'iconBg' => 'bg-purple-100', 'iconColor' => 'text-purple-600'])
        @include('admin.partials.stat-card', ['label' => 'Total Users', 'value' => number_format($totalUsers), 'icon' => 'fa-users', 'iconBg' => 'bg-pink-100', 'iconColor' => 'text-pink-600'])
        @include('admin.partials.stat-card', ['label' => 'Active Users', 'value' => number_format($activeUsers), 'icon' => 'fa-user-check', 'iconBg' => 'bg-amber-100', 'iconColor' => 'text-amber-600'])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="admin-card admin-card-body">
            <h3 class="admin-panel-title mb-4">Monthly Trends (Last 12 Months)</h3>
            <div class="h-64 flex items-end justify-between gap-1 px-1">
                @foreach($monthlyStats as $stat)
                @php
                    $maxValue = max(
                        max(array_column($monthlyStats, 'lost')),
                        max(array_column($monthlyStats, 'found')),
                        max(array_column($monthlyStats, 'claimed'))
                    );
                    $maxValue = $maxValue > 0 ? $maxValue : 1;
                    $lostHeight = round(($stat['lost'] / $maxValue) * 200);
                    $foundHeight = round(($stat['found'] / $maxValue) * 200);
                    $claimedHeight = round(($stat['claimed'] / $maxValue) * 200);
                @endphp
                <div class="flex flex-col items-center gap-2 flex-1 min-w-0">
                    <div class="flex gap-0.5 items-end h-48">
                        <div class="w-full max-w-[12px] bg-red-500 rounded-t" style="height: {{ max($lostHeight, 5) }}px;" title="Lost: {{ $stat['lost'] }}"></div>
                        <div class="w-full max-w-[12px] bg-emerald-500 rounded-t" style="height: {{ max($foundHeight, 5) }}px;" title="Found: {{ $stat['found'] }}"></div>
                        <div class="w-full max-w-[12px] bg-purple-600 rounded-t" style="height: {{ max($claimedHeight, 5) }}px;" title="Claimed: {{ $stat['claimed'] }}"></div>
                    </div>
                    <span class="text-[10px] sm:text-xs text-gray-500 truncate w-full text-center">{{ \Illuminate\Support\Str::limit($stat['month'], 5) }}</span>
                </div>
                @endforeach
            </div>
            <div class="flex items-center justify-center gap-6 mt-4 pt-4 border-t border-gray-100">
                <div class="flex items-center gap-2 text-sm text-gray-600"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Lost</div>
                <div class="flex items-center gap-2 text-sm text-gray-600"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Found</div>
                <div class="flex items-center gap-2 text-sm text-gray-600"><span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span> Claimed</div>
            </div>
        </div>

        <div class="admin-card admin-card-body">
            <h3 class="admin-panel-title mb-4">Activity by Day of Week</h3>
            <div class="space-y-3">
                @php $maxDayValue = max($dayOfWeekStats) ?: 1; @endphp
                @foreach($dayOfWeekStats as $day => $count)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $day }}</span>
                        <span class="text-sm text-gray-600 tabular-nums">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: {{ ($count / $maxDayValue) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="admin-card admin-card-body">
            <h3 class="admin-panel-title mb-4">Peak Hours Analysis</h3>
            <div class="h-48 flex items-end gap-1">
                @php $maxHourValue = max($hourlyStats) ?: 1; @endphp
                @foreach($hourlyStats as $hour => $count)
                <div class="flex flex-col items-center gap-1 flex-1 min-w-0">
                    <div class="w-full bg-purple-600 rounded-t" style="height: {{ max(($count / $maxHourValue) * 150, 5) }}px;" title="{{ $hour }}:00 — {{ $count }} items"></div>
                    <span class="text-[10px] text-gray-500">{{ $hour }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="admin-card admin-card-body">
            <h3 class="admin-panel-title mb-4">Top Categories / Tags</h3>
            <div class="space-y-3">
                @php $maxTagValue = $topTags->max() ?: 1; @endphp
                @forelse($topTags as $tag => $count)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $tag }}</span>
                        <span class="text-sm text-gray-600 tabular-nums">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-pink-500 h-2 rounded-full" style="width: {{ ($count / $maxTagValue) * 100 }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-8">No tags available</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="admin-card admin-card-body">
        <h3 class="admin-panel-title mb-6">Success Metrics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center rounded-xl border border-gray-100 bg-gray-50/50 p-6">
                <p class="text-3xl font-bold text-purple-600 tabular-nums mb-2">{{ $claimRate }}%</p>
                <p class="text-sm font-medium text-gray-900">Claim Rate</p>
                <p class="text-xs text-gray-500 mt-1">{{ $claimedItems }} of {{ $totalItems }} items claimed</p>
            </div>
            <div class="text-center rounded-xl border border-gray-100 bg-gray-50/50 p-6">
                <p class="text-3xl font-bold text-emerald-600 tabular-nums mb-2">{{ $engagementRate }}%</p>
                <p class="text-sm font-medium text-gray-900">User Engagement</p>
                <p class="text-xs text-gray-500 mt-1">{{ $activeUsers }} of {{ $totalUsers }} users active</p>
            </div>
            <div class="text-center rounded-xl border border-gray-100 bg-gray-50/50 p-6">
                <p class="text-3xl font-bold text-blue-600 tabular-nums mb-2">{{ $foundRatio }}%</p>
                <p class="text-sm font-medium text-gray-900">Found Items Ratio</p>
                <p class="text-xs text-gray-500 mt-1">{{ $foundItems }} found vs {{ $lostItems }} lost</p>
            </div>
        </div>
    </div>
</div>
@endsection
