@extends('layouts.user')

@section('title', 'Dashboard - FindITFast')

@section('content')
<div class="user-page">
    @if(session('success'))
        @include('user.partials.alert', ['type' => 'success', 'message' => session('success')])
    @endif

    <div class="grid grid-cols-1 items-stretch gap-4 md:grid-cols-2 xl:grid-cols-3">
        <div class="user-action-card">
            <h3 class="mb-3 text-base font-semibold text-gray-900 sm:text-lg">Report lost item</h3>
            <div class="user-action-card-body">
                <div class="user-action-card-media">
                    <img src="{{ file_exists(public_path('images/reported-item.png')) ? asset('images/reported-item.png') : asset('images/report-lost-item-placeholder.svg') }}" alt="" class="h-full w-full object-contain">
                </div>
                <div class="user-action-card-content">
                    <p class="user-action-card-text">Can't find something? Let the community help you.</p>
                    <a href="/post?type=lost" class="user-action-card-btn">Report lost item</a>
                </div>
            </div>
        </div>
        <div class="user-action-card">
            <h3 class="mb-3 text-base font-semibold text-gray-900 sm:text-lg">Report found item</h3>
            <div class="user-action-card-body">
                <div class="user-action-card-media">
                    <img src="{{ file_exists(public_path('images/found-item.png')) ? asset('images/found-item.png') : asset('images/report-found-item-placeholder.svg') }}" alt="" class="h-full w-full object-contain">
                </div>
                <div class="user-action-card-content">
                    <p class="user-action-card-text">Found something lying around? Post it here.</p>
                    <a href="/post?type=found" class="user-action-card-btn">Report found item</a>
                </div>
            </div>
        </div>
        <div class="user-action-card md:col-span-2 xl:col-span-1">
            <h3 class="mb-3 text-base font-semibold text-gray-900 sm:text-lg">Track my reports</h3>
            <div class="user-action-card-body">
                <div class="user-action-card-media">
                    <img src="{{ file_exists(public_path('images/track-reports.png')) ? asset('images/track-reports.png') : asset('images/track-reports-placeholder.svg') }}" alt="" class="h-full w-full object-contain">
                </div>
                <div class="user-action-card-content">
                    <p class="user-action-card-text">View items you've reported — lost and found.</p>
                    <a href="{{ route('reported-items') }}" class="user-action-card-btn">Track reports</a>
                </div>
            </div>
        </div>
    </div>

    @if($hasReviewQuestions && !$hasCompletedReviews)
    <div class="user-card">
        <div class="user-card-body flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-purple-100 to-pink-100">
                    <i class="fas fa-star text-2xl text-purple-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Submit a review</h3>
                    <p class="text-sm text-gray-600">Share feedback and help us improve FindITFast.</p>
                </div>
            </div>
            <a href="{{ route('reviews.create') }}" class="user-btn-primary w-full shrink-0 sm:w-auto"><i class="fas fa-star"></i> Submit review</a>
        </div>
    </div>
    @endif

    <div class="user-card overflow-hidden border-purple-100 bg-gradient-to-br from-purple-50 via-white to-pink-50">
        <div class="user-card-body">
            <div class="flex flex-col items-center gap-6 md:flex-row md:items-center md:justify-between">
                <div class="text-center md:text-left">
                    <h2 class="user-welcome-title text-3xl font-bold sm:text-4xl md:text-5xl">
                        <span class="text-purple-600">Hi,</span> <span class="text-pink-500">{{ Auth::user()->name }}</span>
                    </h2>
                    <p class="mt-2 text-base text-gray-600 sm:text-lg">Let's help find what's missing — or return what's found.</p>
                    <a href="{{ route('reported-items') }}" class="user-btn-primary mt-4 inline-flex">Get started</a>
                </div>
                <div class="h-40 w-full max-w-xs shrink-0 sm:h-48 md:h-56 md:max-w-sm">
                    <img src="{{ file_exists(public_path('images/dashboard-banner.png')) ? asset('images/dashboard-banner.png') : asset('images/dashboard-banner-placeholder.svg') }}" alt="" class="h-full w-full object-contain">
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 lg:gap-6">
        <div class="user-card lg:col-span-2">
            <div class="user-card-header"><h3 class="text-lg font-semibold text-gray-900">Recent activity</h3></div>
            <div class="user-table-wrap">
                <table class="min-w-[32rem] w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="user-th">User</th>
                            <th class="user-th">Item</th>
                            <th class="user-th">Type</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($recentActivity as $activity)
                        <tr class="hover:bg-purple-50/30">
                            <td class="user-td font-medium text-gray-900">{{ $activity['name'] }}</td>
                            <td class="user-td max-w-[12rem] truncate sm:max-w-none">{{ $activity['item_name'] }}</td>
                            <td class="user-td">
                                <span class="{{ $activity['item_type'] === 'lost' ? 'user-badge-lost' : 'user-badge-found' }}">{{ ucfirst($activity['item_type']) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">No recent activity</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="user-card">
            <div class="user-card-header"><h3 class="text-lg font-semibold text-gray-900">Success rate</h3></div>
            <div class="user-card-body">
                @php
                    $circumference = 2 * M_PI * 40;
                    $offset = $circumference - (($successRate / 100) * $circumference);
                @endphp
                <div class="mb-6 flex justify-center">
                    <div class="relative h-28 w-28 sm:h-32 sm:w-32">
                        <svg class="h-full w-full -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="40" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                            <circle cx="50" cy="50" r="40" stroke="#ec4899" stroke-width="8" fill="none" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}" stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center"><span class="text-2xl font-bold text-pink-600">{{ $successRate }}%</span></div>
                    </div>
                </div>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-2"><dt class="text-gray-600">Lost reported</dt><dd class="font-bold text-purple-600">{{ $lostItems }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-gray-600">Found posted</dt><dd class="font-bold text-purple-600">{{ $foundItems }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-gray-600">Items claimed</dt><dd class="font-bold text-purple-600">{{ $claimedItems }}</dd></div>
                    <div class="flex justify-between gap-2 border-t border-gray-100 pt-3"><dt class="font-medium text-gray-700">Total</dt><dd class="font-bold text-gray-900">{{ $totalItems }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
