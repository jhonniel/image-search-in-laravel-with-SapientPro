@extends('layouts.user')

@section('content')
<div class="user-page">
    @include('user.partials.page-header', [
        'eyebrow' => 'Account',
        'title' => 'My profile',
        'description' => 'View and manage your account information',
        'actions' => '<a href="'.route('profile.edit').'" class="user-btn-primary w-full sm:w-auto"><i class="fas fa-edit"></i> Edit profile</a>',
    ])

<!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-4 lg:gap-6 h-full">
        <!-- Left Column: Profile Card & Statistics -->
        <div class="lg:col-span-3 flex flex-col space-y-3 sm:space-y-4 lg:space-y-4">
            <!-- Profile Card -->
            <div class="user-card user-card-body flex-shrink-0">
                <div class="text-center">
                    <div class="relative inline-block mb-4">
                        @if($user->profile_picture)
                            <img src="{{ $user->profile_picture }}" alt="Profile Picture"
                                 class="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border-4 border-purple-100 shadow-sm">
                        @else
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center border-4 border-purple-100 shadow-sm">
                                <span class="text-xl sm:text-2xl font-bold text-white">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </span>
                            </div>
                        @endif
                        <div class="absolute -bottom-1 -right-1 w-7 h-7 sm:w-8 sm:h-8 bg-purple-600 rounded-full flex items-center justify-center shadow-md border-2 border-white">
                            <i class="fas fa-camera text-white text-xs"></i>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-2 mb-1 flex-wrap">
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900">{{ $user->name }}</h3>
                        @if($user->is_verified ?? false)
                        <span class="inline-flex items-center justify-center w-5 h-5 flex-shrink-0" title="Verified Profile">
                            <img src="{{ asset('images/icons/verify.png') }}" alt="Verified" class="w-5 h-5">
                        </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 mb-3 break-all">{{ $user->email }}</p>
                    <div class="flex items-center justify-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1.5"></i>
                            Active
                        </span>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="user-card user-card-body flex-shrink-0">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Statistics</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-list-alt text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Reports</p>
                                <p class="text-lg font-bold text-gray-900">{{ $reportsCount }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-hand-holding text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Found Items</p>
                                <p class="text-lg font-bold text-gray-900">
                                    {{ \App\Models\ImageMetadata::where('uploader_email', $user->email)->where('status', 'found')->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gradient-to-r from-red-50 to-rose-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-search text-red-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Lost Items</p>
                                <p class="text-lg font-bold text-gray-900">
                                    {{ \App\Models\ImageMetadata::where('uploader_email', $user->email)->where('status', 'lost')->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Profile Details, Activity & Rewards -->
        <div class="lg:col-span-9 flex flex-col space-y-3 sm:space-y-4 lg:space-y-4">
            <!-- Profile Information -->
            <div class="user-card user-card-body flex-shrink-0">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Profile Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div class="pb-3 border-b border-gray-100 sm:border-b-0 sm:border-r sm:pr-3 lg:pr-4">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Full Name</label>
                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                    </div>
                    <div class="pb-3 border-b border-gray-100 sm:border-b-0 sm:pl-3 lg:pl-4 sm:border-r lg:border-r-0 lg:pr-0">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Email Address</label>
                        <p class="text-sm font-medium text-gray-900 break-all">{{ $user->email }}</p>
                    </div>
                    <div class="pt-3 sm:pt-3 sm:border-t sm:border-r sm:pr-3 lg:pr-4 sm:border-gray-100">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Code Name</label>
                        <p class="text-sm font-medium text-gray-900">{{ $user->code_name ?? 'Not set' }}</p>
                    </div>
                    <div class="pt-3 sm:pt-3 sm:border-t sm:pl-3 lg:pl-4 sm:border-gray-100">
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Member Since</label>
                        <p class="text-sm font-medium text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            @if($recentReports->count() > 0)
            <div class="user-card user-card-body flex-shrink-0">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Recent Activity</h3>
                <div class="space-y-2 sm:space-y-3">
                    @foreach($recentReports as $report)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <div class="w-9 h-9 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-{{ $report->status === 'lost' ? 'search' : 'hand-holding' }} text-purple-600 text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $report->description }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $report->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full ml-3 flex-shrink-0
                            {{ $report->status === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ ucfirst($report->status) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Rewards Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-5 flex-1 flex flex-col min-h-0">
                <div class="flex items-center justify-between mb-3 sm:mb-4 flex-shrink-0">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Rewards</h3>
                    @if($rewards->count() > 0)
                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                        {{ $rewards->count() }} available
                    </span>
                    @endif
                </div>

                @if($rewards->count() > 0)
                <div class="space-y-3 sm:space-y-4 overflow-y-auto flex-1">
                    @foreach($rewards as $reward)
                    <div class="group border border-gray-200 rounded-xl p-4 sm:p-5 hover:border-purple-300 hover:shadow-md transition-all {{ $reward->isExpired() ? 'opacity-60' : '' }}">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <h4 class="font-semibold text-gray-900 text-sm sm:text-base truncate">{{ $reward->title }}</h4>
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
                                        {{ $reward->type === 'discount' ? 'bg-blue-100 text-blue-800' : 
                                           ($reward->type === 'free_item' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">
                                        {{ ucfirst(str_replace('_', ' ', $reward->type)) }}
                                    </span>
                                </div>
                                @if($reward->description)
                                <p class="text-xs sm:text-sm text-gray-600">{{ $reward->description }}</p>
                                @endif
                            </div>
                            @if($reward->value)
                            <div class="flex-shrink-0 text-base font-bold text-purple-600">
                                @if($reward->type === 'discount')
                                    {{ $reward->value }}% OFF
                                @else
                                    ${{ number_format($reward->value, 2) }}
                                @endif
                            </div>
                            @endif
                        </div>

                        <div class="bg-gray-50 rounded-lg p-3 flex flex-wrap items-center justify-between gap-3 mb-3">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Code</p>
                                <p class="font-mono font-semibold text-sm text-gray-900">{{ $reward->code }}</p>
                            </div>
                            <button type="button" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md border border-purple-200 text-purple-700 hover:bg-purple-50 transition">
                                <i class="fas fa-copy text-xs mr-2"></i>Copy Code
                            </button>
                        </div>

                        <div class="flex items-center text-xs text-gray-500">
                            @if($reward->expires_at)
                                <i class="fas fa-clock mr-1.5"></i>
                                <span>Expires {{ $reward->expires_at->format('M d, Y') }}</span>
                            @else
                                <i class="fas fa-infinity mr-1.5 text-green-500"></i>
                                <span>Does not expire</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-6 sm:py-8 flex-shrink-0">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-gift text-gray-400 text-lg"></i>
                    </div>
                    <h4 class="text-sm font-medium text-gray-900 mb-1">No Rewards Available</h4>
                    <p class="text-xs text-gray-500">Keep participating to earn rewards!</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
