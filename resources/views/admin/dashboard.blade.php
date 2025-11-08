@extends('layouts.admin')

@section('title', 'Dashboard - FindITFast Admin')

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Reports Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-file-search text-pink-primary text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ number_format($totalReports) }}</h3>
                            <p class="text-sm text-gray-600">Total Reports</p>
                        </div>
                    </div>
                    <div class="flex items-center {{ $reportsChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        <i class="fas fa-arrow-{{ $reportsChange >= 0 ? 'up' : 'down' }} text-sm mr-1"></i>
                        <span class="text-sm font-medium">{{ abs($reportsChange) }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items in Progress Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-purple-primary text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ number_format($itemsInProgress) }}</h3>
                            <p class="text-sm text-gray-600">Items in progress</p>
                        </div>
                    </div>
                    <div class="flex items-center {{ $itemsInProgressChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        <i class="fas fa-arrow-{{ $itemsInProgressChange >= 0 ? 'up' : 'down' }} text-sm mr-1"></i>
                        <span class="text-sm font-medium">{{ abs($itemsInProgressChange) }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contributors Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-gift text-pink-primary text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ number_format($contributors) }}</h3>
                            <p class="text-sm text-gray-600">Contributors</p>
                        </div>
                    </div>
                    <div class="flex items-center {{ $contributorsChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        <i class="fas fa-arrow-{{ $contributorsChange >= 0 ? 'up' : 'down' }} text-sm mr-1"></i>
                        <span class="text-sm font-medium">{{ abs($contributorsChange) }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Activity Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Bar Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Lost vs Found Items</h3>
            <div class="h-64 flex items-end space-x-2">
                <!-- Chart bars for each month -->
                @foreach($monthlyData as $data)
                <div class="flex flex-col items-center space-y-2">
                    <div class="flex space-x-1">
                        <div class="w-6 bg-pink-primary rounded-t" style="height: {{ max($data['lost_height'], 5) }}px;" title="Lost: {{ $data['lost'] }}"></div>
                        <div class="w-6 bg-blue-primary rounded-t" style="height: {{ max($data['found_height'], 5) }}px;" title="Found: {{ $data['found'] }}"></div>
                    </div>
                    <span class="text-xs text-gray-600">{{ $data['month'] }}</span>
                </div>
                @endforeach
            </div>
            <!-- Legend -->
            <div class="flex items-center justify-center space-x-6 mt-4">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-pink-primary rounded-full"></div>
                    <span class="text-sm text-gray-600">Lost Items</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-blue-primary rounded-full"></div>
                    <span class="text-sm text-gray-600">Found Items</span>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
            <div class="space-y-4">
                @forelse($recentItems as $item)
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-pink-primary rounded-full mt-2"></div>
                    <div>
                        <p class="text-sm text-gray-900">
                            @if($item['is_claimed'])
                                <strong>{{ $item['user_name'] }}</strong> requested to claim the item: <strong>{{ \Illuminate\Support\Str::limit($item['description'], 50) }}</strong>
                            @else
                                <strong>{{ $item['user_name'] }}</strong> submitted a {{ $item['status'] }} item report: <strong>{{ \Illuminate\Support\Str::limit($item['description'], 50) }}</strong>
                            @endif
                        </p>
                        <p class="text-xs text-gray-500">{{ $item['created_at']->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-gray-300 rounded-full mt-2"></div>
                    <div>
                        <p class="text-sm text-gray-500">No recent activity</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Top Contributors Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Top Contributors</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reports</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verified</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Claimed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Active</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($topContributors as $index => $contributor)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($index === 0)
                                    <i class="fas fa-medal text-yellow-500 text-lg mr-2"></i>
                                @elseif($index === 1)
                                    <i class="fas fa-medal text-gray-400 text-lg mr-2"></i>
                                @elseif($index === 2)
                                    <i class="fas fa-medal text-orange-500 text-lg mr-2"></i>
                                @else
                                    <i class="fas fa-circle text-gray-300 text-xs mr-2"></i>
                                @endif
                                <span class="text-sm font-medium text-gray-900">{{ $index + 1 }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-purple-primary text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $contributor['username'] !== 'N/A' ? $contributor['username'] : $contributor['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $contributor['report_count'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $contributor['verified_count'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $contributor['claimed_count'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contributor['last_active'] ? $contributor['last_active']->diffForHumans() : 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <i class="fas fa-ellipsis-v"></i>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No contributors yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
