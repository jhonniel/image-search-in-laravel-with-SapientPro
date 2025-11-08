@extends('layouts.user')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
                <p class="text-gray-600 mt-2">View and manage your account information</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('user.profile.edit') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Profile Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <div class="text-center">
                    <div class="relative inline-block">
                        @if($user->profile_picture)
                            <img src="{{ $user->profile_picture }}" alt="Profile Picture"
                                 class="w-24 h-24 rounded-full object-cover border-4 border-purple-100">
                        @else
                            <div class="w-24 h-24 rounded-full bg-purple-100 flex items-center justify-center border-4 border-purple-100">
                                <span class="text-2xl font-bold text-purple-600">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </span>
                            </div>
                        @endif
                        <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-camera text-white text-sm"></i>
                        </div>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mt-4">{{ $user->name }}</h3>
                    <p class="text-gray-600">{{ $user->email }}</p>
                    <div class="mt-4">
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            Active
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Profile Information</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">Full Name</label>
                        <p class="text-sm text-gray-900">{{ $user->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">Email Address</label>
                        <p class="text-sm text-gray-900">{{ $user->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">User ID</label>
                        <p class="text-sm text-gray-900">{{ $user->id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">Member Since</label>
                        <p class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow-sm border p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Your Statistics</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-list-alt text-purple-600"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">{{ $reportsCount }}</div>
                        <div class="text-sm text-gray-500">Total Reports</div>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-hand-holding text-green-600"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">
                            {{ \App\Models\ImageMetadata::where('uploader_email', $user->email)->where('status', 'found')->count() }}
                        </div>
                        <div class="text-sm text-gray-500">Found Items</div>
                    </div>
                    <div class="text-center">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-search text-red-600"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">
                            {{ \App\Models\ImageMetadata::where('uploader_email', $user->email)->where('status', 'lost')->count() }}
                        </div>
                        <div class="text-sm text-gray-500">Lost Items</div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            @if($recentReports->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Recent Activity</h3>

                <div class="space-y-4">
                    @foreach($recentReports as $report)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-{{ $report->status === 'lost' ? 'search' : 'hand-holding' }} text-purple-600"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $report->description }}</div>
                                <div class="text-xs text-gray-500">{{ $report->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            {{ $report->status === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ ucfirst($report->status) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
