@extends('layouts.admin')

@section('title', 'Insights - FindITFast Admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Insights & Analytics</h1>
        <p class="text-gray-600">Comprehensive analytics and performance metrics</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">
        <!-- Total Items -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Items</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($totalItems) }}</h3>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-box text-blue-primary text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Lost Items -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Lost Items</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($lostItems) }}</h3>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-search text-red-600 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Found Items -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Found Items</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($foundItems) }}</h3>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-hand-holding text-green-600 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Claimed Items -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Claimed Items</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($claimedItems) }}</h3>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-purple-primary text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Users</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</h3>
                </div>
                <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-pink-primary text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Active Users</p>
                    <h3 class="text-2xl font-bold text-gray-900">{{ number_format($activeUsers) }}</h3>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-check text-yellow-600 text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Trends -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Trends (Last 12 Months)</h3>
            <div class="h-64 flex items-end space-x-1">
                @foreach($monthlyStats as $stat)
                <div class="flex flex-col items-center space-y-1 flex-1">
                    <div class="flex space-x-0.5 w-full justify-center">
                        @php
                            $maxValue = max(
                                max(array_column($monthlyStats, 'lost')),
                                max(array_column($monthlyStats, 'found')),
                                max(array_column($monthlyStats, 'claimed'))
                            );
                            $maxValue = $maxValue > 0 ? $maxValue : 1;
                            $lostHeight = $maxValue > 0 ? round(($stat['lost'] / $maxValue) * 200) : 0;
                            $foundHeight = $maxValue > 0 ? round(($stat['found'] / $maxValue) * 200) : 0;
                            $claimedHeight = $maxValue > 0 ? round(($stat['claimed'] / $maxValue) * 200) : 0;
                        @endphp
                        <div class="w-4 bg-red-500 rounded-t" style="height: {{ max($lostHeight, 5) }}px;" title="Lost: {{ $stat['lost'] }}"></div>
                        <div class="w-4 bg-green-500 rounded-t" style="height: {{ max($foundHeight, 5) }}px;" title="Found: {{ $stat['found'] }}"></div>
                        <div class="w-4 bg-purple-primary rounded-t" style="height: {{ max($claimedHeight, 5) }}px;" title="Claimed: {{ $stat['claimed'] }}"></div>
                    </div>
                    <span class="text-xs text-gray-600 transform -rotate-45 origin-center">{{ \Illuminate\Support\Str::limit($stat['month'], 5) }}</span>
                </div>
                @endforeach
            </div>
            <div class="flex items-center justify-center space-x-6 mt-4">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <span class="text-sm text-gray-600">Lost</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-sm text-gray-600">Found</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-purple-primary rounded-full"></div>
                    <span class="text-sm text-gray-600">Claimed</span>
                </div>
            </div>
        </div>

        <!-- Day of Week Analysis -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Activity by Day of Week</h3>
            <div class="space-y-3">
                @php
                    $maxDayValue = max($dayOfWeekStats);
                    $maxDayValue = $maxDayValue > 0 ? $maxDayValue : 1;
                @endphp
                @foreach($dayOfWeekStats as $day => $count)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $day }}</span>
                        <span class="text-sm text-gray-600">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-primary h-2 rounded-full" style="width: {{ ($count / $maxDayValue) * 100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Additional Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Peak Hours -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Peak Hours Analysis</h3>
            <div class="h-48 flex items-end space-x-1">
                @php
                    $maxHourValue = max($hourlyStats);
                    $maxHourValue = $maxHourValue > 0 ? $maxHourValue : 1;
                @endphp
                @foreach($hourlyStats as $hour => $count)
                <div class="flex flex-col items-center space-y-1 flex-1">
                    <div class="w-full bg-purple-primary rounded-t" style="height: {{ max(($count / $maxHourValue) * 150, 5) }}px;" title="{{ $hour }}:00 - {{ $count }} items"></div>
                    <span class="text-xs text-gray-600">{{ $hour }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Top Tags -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Categories/Tags</h3>
            <div class="space-y-3">
                @php
                    $maxTagValue = $topTags->max();
                    $maxTagValue = $maxTagValue > 0 ? $maxTagValue : 1;
                @endphp
                @forelse($topTags as $tag => $count)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">{{ $tag }}</span>
                        <span class="text-sm text-gray-600">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-pink-primary h-2 rounded-full" style="width: {{ ($count / $maxTagValue) * 100 }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-4">No tags available</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Success Rate Card -->
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Success Metrics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-primary mb-2">
                    {{ $totalItems > 0 ? number_format(($claimedItems / $totalItems) * 100, 1) : 0 }}%
                </div>
                <p class="text-sm text-gray-600">Claim Rate</p>
                <p class="text-xs text-gray-500 mt-1">{{ $claimedItems }} of {{ $totalItems }} items claimed</p>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600 mb-2">
                    {{ $totalUsers > 0 ? number_format(($activeUsers / $totalUsers) * 100, 1) : 0 }}%
                </div>
                <p class="text-sm text-gray-600">User Engagement</p>
                <p class="text-xs text-gray-500 mt-1">{{ $activeUsers }} of {{ $totalUsers }} users active</p>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-primary mb-2">
                    {{ $totalItems > 0 ? number_format(($foundItems / $totalItems) * 100, 1) : 0 }}%
                </div>
                <p class="text-sm text-gray-600">Found Items Ratio</p>
                <p class="text-xs text-gray-500 mt-1">{{ $foundItems }} found vs {{ $lostItems }} lost</p>
            </div>
        </div>
    </div>
</div>
@endsection



