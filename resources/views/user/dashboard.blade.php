@extends('layouts.user')

@section('title', 'Dashboard - FindITFast')

@section('content')
<div class="space-y-6">
    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <p class="text-green-700 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif
    <!-- Welcome Section -->
    <div class="bg-purple-50 rounded-lg p-8 border border-purple-200">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <h2 class="text-3xl font-bold text-purple-primary mb-2">Hi, {{ Auth::user()->name }}</h2>
                <p class="text-gray-700 text-lg mb-4">Let's help find what's missing — or return what's found.</p>
                <button class="bg-blue-primary text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                    Learn more
                </button>
            </div>
            <div class="ml-8">
                <!-- Illustration placeholder - you can add SVG here -->
                <div class="w-32 h-32 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-purple-primary text-4xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Report Lost Item Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-question-circle text-blue-primary text-2xl"></i>
                </div>
                <p class="text-gray-700 mb-4">Can't find something? Let the community help you.</p>
                <a href="/post?type=lost" class="block w-full bg-blue-primary text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition-colors text-center">
                    Report Lost Item
                </a>
            </div>
        </div>

        <!-- Report Found Item Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-binoculars text-blue-primary text-2xl"></i>
                </div>
                <p class="text-gray-700 mb-4">Found something lying around? Post it here.</p>
                <a href="/post?type=found" class="block w-full bg-blue-primary text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition-colors text-center">
                    Report Found Item
                </a>
            </div>
        </div>

        <!-- Track My Reports Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-blue-primary text-2xl"></i>
                </div>
                <p class="text-gray-700 mb-4">View the items you've reported both lost and found.</p>
                <a href="{{ route('user.reported-items') }}" class="block w-full bg-blue-primary text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition-colors text-center">
                    Track My Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Bottom Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activity Table -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posted Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentActivity as $activity)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">@{{ $activity['username'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $activity['item_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $activity['item_type'] === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($activity['item_type']) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $activity['location'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $activity['posted_date']->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No recent activity</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Success Rate Card -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Success Rate</h3>

            <!-- Circular Progress -->
            <div class="flex justify-center mb-6">
                <div class="relative w-32 h-32">
                    <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 100 100">
                        <!-- Background circle -->
                        <circle cx="50" cy="50" r="40" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                        <!-- Progress circle -->
                        @php
                            $circumference = 2 * M_PI * 40; // 2πr where r=40
                            $offset = $circumference - (($successRate / 100) * $circumference);
                        @endphp
                        <circle cx="50" cy="50" r="40" stroke="#EC4899" stroke-width="8" fill="none"
                                stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}" stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl font-bold text-pink-primary">{{ $successRate }}%</span>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Lost Items Reported:</span>
                    <span class="text-lg font-bold text-pink-primary">{{ $lostItems }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Found Items Posted:</span>
                    <span class="text-lg font-bold text-pink-primary">{{ $foundItems }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Items Claimed:</span>
                    <span class="text-lg font-bold text-pink-primary">{{ $claimedItems }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total Reports:</span>
                    <span class="text-lg font-bold text-pink-primary">{{ $totalItems }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
