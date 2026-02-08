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
    <!-- Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Report Lost Item Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex flex-col h-full">
                <h3 class="text-lg font-semibold text-gray-900 mb-5">Report Lost Item</h3>
                <div class="flex items-start gap-5 mb-6 flex-1">
                    <div class="shrink-0 w-24 h-24">
                        @if(file_exists(public_path('images/reported-item.png')))
                            <img src="{{ asset('images/reported-item.png') }}"
                                 alt="Report Lost Item Illustration"
                                 class="w-full h-full object-contain">
                        @else
                            <img src="{{ asset('images/report-lost-item-placeholder.svg') }}"
                                 alt="Report Lost Item Illustration"
                                 class="w-full h-full object-contain">
                        @endif
                    </div>
                    <div class="flex-1 flex flex-col">
                        <p class="text-gray-700 text-sm leading-relaxed mb-5">Can't find something? Let the community help you.</p>
                        <div class="mt-auto">
                            <a href="/post?type=lost" class="inline-block bg-blue-primary text-white py-2.5 px-5 rounded-lg hover:bg-blue-600 transition-colors text-center font-medium text-sm">
                                Report Lost Item
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Found Item Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex flex-col h-full">
                <h3 class="text-lg font-semibold text-gray-900 mb-5">Report Found Item</h3>
                <div class="flex items-start gap-5 mb-6 flex-1">
                    <div class="shrink-0 w-24 h-24">
                        @if(file_exists(public_path('images/found-item.png')))
                            <img src="{{ asset('images/found-item.png') }}"
                                 alt="Report Found Item Illustration"
                                 class="w-full h-full object-contain">
                        @else
                            <img src="{{ asset('images/report-found-item-placeholder.svg') }}"
                                 alt="Report Found Item Illustration"
                                 class="w-full h-full object-contain">
                        @endif
                    </div>
                    <div class="flex-1 flex flex-col">
                        <p class="text-gray-700 text-sm leading-relaxed mb-5">Found something lying around? Post it here.</p>
                        <div class="mt-auto">
                            <a href="/post?type=found" class="inline-block bg-blue-primary text-white py-2.5 px-5 rounded-lg hover:bg-blue-600 transition-colors text-center font-medium text-sm">
                                Report Found Item
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Track My Reports Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200">
            <div class="flex flex-col h-full">
                <h3 class="text-lg font-semibold text-gray-900 mb-5">Track My Reports</h3>
                <div class="flex items-start gap-5 mb-6 flex-1">
                    <div class="shrink-0 w-24 h-24">
                        @if(file_exists(public_path('images/track-reports.png')))
                            <img src="{{ asset('images/track-reports.png') }}"
                                 alt="Track Reports Illustration"
                                 class="w-full h-full object-contain">
                        @else
                            <img src="{{ asset('images/track-reports-placeholder.svg') }}"
                                 alt="Track Reports Illustration"
                                 class="w-full h-full object-contain">
                        @endif
                    </div>
                    <div class="flex-1 flex flex-col">
                        <p class="text-gray-700 text-sm leading-relaxed mb-5">View the items you've reported both lost and found.</p>
                        <div class="mt-auto">
                            <a href="{{ route('reported-items') }}" class="inline-block bg-blue-primary text-white py-2.5 px-5 rounded-lg hover:bg-blue-600 transition-colors text-center font-medium text-sm">
                                Track My Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit a Review Section -->
    @if($hasReviewQuestions && !$hasCompletedReviews)
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-200 mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-5">
                <div class="shrink-0 w-16 h-16">
                    <div class="w-full h-full bg-gradient-to-br from-purple-100 to-pink-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-star text-purple-primary text-3xl"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Submit a Review</h3>
                    <p class="text-gray-600 text-sm">Share your feedback and help us improve FindITFast.</p>
                </div>
            </div>
            <a href="{{ route('reviews.create') }}" class="inline-flex items-center justify-center px-6 py-3 bg-purple-primary text-white font-semibold rounded-lg shadow-md hover:bg-purple-700 transition-colors">
                <i class="fas fa-star mr-2"></i>
                Submit Review
            </a>
        </div>
    </div>
    @endif

    <!-- Welcome Section -->
    <div class="bg-purple-50 rounded-xl p-8 md:p-10 border-2 border-blue-500 mb-8">
        <div class="flex flex-col md:flex-row items-center md:items-center justify-between gap-6 md:gap-10">
            <div class="flex-1 text-center md:text-left">
                <h2 class="text-5xl md:text-6xl font-bold mb-4 leading-tight">
                    <span class="text-blue-600">Hi,</span>
                    <span class="text-pink-500">{{ Auth::user()->name }}</span>
                </h2>
                <p class="text-gray-700 text-lg mb-6">Let's help find what's missing — or return what's found.</p>
                <button class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors font-medium text-base">
                    Learn more
                </button>
            </div>
            <div class="shrink-0 w-48 sm:w-56 md:w-64 lg:w-72 h-48 sm:h-56 md:h-64 lg:h-72 flex items-center justify-center overflow-hidden">
                <!-- Illustration - using uploaded image -->
                @if(file_exists(public_path('images/dashboard-banner.png')))
                    <img src="{{ asset('images/dashboard-banner.png') }}"
                         alt="Lost and Found Illustration"
                         class="w-full h-full object-contain">
                @else
                    <img src="{{ asset('images/dashboard-banner-placeholder.svg') }}"
                         alt="Lost and Found Illustration Placeholder"
                         class="w-full h-full object-contain">
                @endif
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
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentActivity as $activity)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $activity['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $activity['item_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $activity['item_type'] === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($activity['item_type']) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No recent activity</td>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Make star ratings interactive with hover effects
    document.querySelectorAll('input[type="radio"][name^="question_"]').forEach(function(radio) {
        const questionId = radio.name;
        const starContainer = radio.closest('.flex.items-center.space-x-2');
        if (!starContainer) return;
        
        const stars = Array.from(starContainer.querySelectorAll('input[type="radio"]'));
        const starIcons = Array.from(starContainer.querySelectorAll('i.fa-star'));
        
        stars.forEach(function(star, index) {
            const starIcon = starIcons[index];
            
            // Update stars on change
            star.addEventListener('change', function() {
                updateStarDisplay(stars, starIcons);
            });
            
            // Hover effect
            star.addEventListener('mouseenter', function() {
                starIcons.forEach(function(icon, i) {
                    if (i <= index) {
                        icon.classList.add('text-yellow-300');
                    }
                });
            });
            
            star.addEventListener('mouseleave', function() {
                updateStarDisplay(stars, starIcons);
            });
        });
        
        function updateStarDisplay(stars, icons) {
            const checkedIndex = stars.findIndex(s => s.checked);
            icons.forEach(function(icon, i) {
                icon.classList.remove('text-yellow-300');
                if (checkedIndex !== -1 && i <= checkedIndex) {
                    icon.classList.remove('text-gray-300');
                    icon.classList.add('text-yellow-400');
                } else if (checkedIndex === -1 || i > checkedIndex) {
                    icon.classList.remove('text-yellow-400');
                    icon.classList.add('text-gray-300');
                }
            });
        }
        
        // Initialize display
        const checkedIndex = stars.findIndex(s => s.checked);
        if (checkedIndex !== -1) {
            starIcons.forEach(function(icon, i) {
                if (i <= checkedIndex) {
                    icon.classList.remove('text-gray-300');
                    icon.classList.add('text-yellow-400');
                }
            });
        }
    });
});
</script>
@endpush
@endsection
