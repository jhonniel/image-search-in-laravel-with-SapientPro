@extends('layouts.admin')

@section('title', 'Review Questions - FindITFast Admin')

@section('content')
<script>
// Define function globally immediately - must be available before HTML renders
window.toggleReviews = function(userId) {
    console.log('Toggle reviews called for user:', userId);
    const reviewsContainer = document.getElementById('reviews-' + userId);
    const chevron = document.getElementById('chevron-' + userId);
    
    if (!reviewsContainer) {
        console.error('Could not find reviews container for user:', userId, 'Looking for: reviews-' + userId);
        return false;
    }
    
    if (!chevron) {
        console.error('Could not find chevron for user:', userId, 'Looking for: chevron-' + userId);
        return false;
    }
    
    const currentDisplay = window.getComputedStyle(reviewsContainer).display;
    const inlineDisplay = reviewsContainer.style.display;
    console.log('Current display (computed):', currentDisplay);
    console.log('Current display (inline):', inlineDisplay);
    
    if (currentDisplay === 'none' || inlineDisplay === 'none' || inlineDisplay === '') {
        // Show the reviews
        reviewsContainer.style.removeProperty('display');
        reviewsContainer.style.display = 'block';
        reviewsContainer.classList.remove('hidden');
        chevron.classList.remove('fa-chevron-down');
        chevron.classList.add('fa-chevron-up');
        console.log('✓ Expanded reviews for user:', userId);
        return true;
    } else {
        // Hide the reviews
        reviewsContainer.style.display = 'none';
        reviewsContainer.classList.add('hidden');
        chevron.classList.remove('fa-chevron-up');
        chevron.classList.add('fa-chevron-down');
        console.log('✓ Collapsed reviews for user:', userId);
        return true;
    }
};
</script>
<div class="admin-page">
    @include('admin.partials.page-header', [
        'title' => 'Review Questions & Feedback',
        'description' => 'Manage questions and view user reviews and ratings.',
    ])

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl">
        @include('admin.partials.stat-card', [
            'label' => 'Total Questions',
            'value' => $questions->count(),
            'icon' => 'fa-question-circle',
            'iconBg' => 'bg-purple-100',
            'iconColor' => 'text-purple-600',
        ])
        @include('admin.partials.stat-card', [
            'label' => 'Total Reviews',
            'value' => number_format($totalReviews),
            'icon' => 'fa-star',
            'iconBg' => 'bg-emerald-100',
            'iconColor' => 'text-emerald-600',
        ])
    </div>

    @include('admin.partials.alert', ['errors' => $errors])

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="flex space-x-8" aria-label="Tabs">
            <a href="{{ route('review-questions.index', ['tab' => 'questions']) }}"
               class="py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors {{ $tab === 'questions' ? 'border-purple-primary text-purple-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="fas fa-question-circle mr-2"></i>Questions
            </a>
            <a href="{{ route('review-questions.index', ['tab' => 'reviews']) }}"
               class="py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors {{ $tab === 'reviews' ? 'border-purple-primary text-purple-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="fas fa-star mr-2"></i>Reviews & Dashboard
            </a>
        </nav>
    </div>

    @if($tab === 'questions')
    <!-- Questions Tab Content -->
    <!-- Add New Question Form -->
    <div class="admin-card admin-card-body">
        <h2 class="admin-panel-title mb-4">Add New Question</h2>
        <form method="POST" action="{{ route('review-questions.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Question Text</label>
                    <input type="text" name="question" value="{{ old('question') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent {{ $errors->has('question') ? 'border-red-500' : '' }}"
                           placeholder="e.g., How would you rate your overall experience?">
                    @error('question')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Feedback Type</label>
                    <select name="question_type" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent {{ $errors->has('question_type') ? 'border-red-500' : '' }}">
                        <option value="rating" {{ old('question_type') === 'rating' ? 'selected' : '' }}>Rating (1-5 stars)</option>
                        <option value="text" {{ old('question_type') === 'text' ? 'selected' : '' }}>Text Response</option>
                    </select>
                    @error('question_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Display Order</label>
                    <input type="number" name="display_order" value="{{ old('display_order', 0) }}" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent {{ $errors->has('display_order') ? 'border-red-500' : '' }}">
                    @error('display_order')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_required" value="1" {{ old('is_required', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-purple-primary border-gray-300 rounded focus:ring-purple-primary">
                        <span class="ml-2 text-sm text-gray-700">Required</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="w-4 h-4 text-purple-primary border-gray-300 rounded focus:ring-purple-primary">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-primary text-white rounded-lg font-medium hover:bg-purple-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Add Question
            </button>
        </form>
    </div>

    <!-- Questions List -->
    <div class="admin-card">
        <div class="admin-toolbar">
            <h2 class="admin-panel-title">Existing Questions</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($questions as $question)
            <div class="p-6 border border-gray-200 rounded-lg">
                <form method="POST" action="{{ route('review-questions.update', $question->id) }}" class="space-y-4" id="update-form-{{ $question->id }}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Question Text</label>
                            <input type="text" name="question" value="{{ old('question', $question->question) }}" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Feedback Type</label>
                            <select name="question_type" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                                <option value="rating" {{ old('question_type', $question->question_type) === 'rating' ? 'selected' : '' }}>Rating (1-5 stars)</option>
                                <option value="text" {{ old('question_type', $question->question_type) === 'text' ? 'selected' : '' }}>Text Response</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Display Order</label>
                            <input type="number" name="display_order" value="{{ old('display_order', $question->display_order) }}" min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                        </div>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="hidden" name="is_required" value="0">
                                <input type="checkbox" name="is_required" value="1" {{ old('is_required', $question->is_required) ? 'checked' : '' }}
                                       class="w-4 h-4 text-purple-primary border-gray-300 rounded focus:ring-purple-primary">
                                <span class="ml-2 text-sm text-gray-700">Required</span>
                            </label>
                            <label class="flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $question->is_active) ? 'checked' : '' }}
                                       class="w-4 h-4 text-purple-primary border-gray-300 rounded focus:ring-purple-primary">
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <div class="text-sm text-gray-500">
                            <span class="inline-flex items-center px-2 py-1 rounded-full {{ $question->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $question->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="ml-2">Order: {{ $question->display_order }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-primary text-white rounded-lg font-medium hover:bg-purple-700 transition-colors text-sm">
                                <i class="fas fa-save mr-2"></i>Update
                            </button>
                            <button type="button" onclick="deleteQuestion({{ $question->id }})" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors text-sm">
                                <i class="fas fa-trash mr-2"></i>Delete
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Hidden delete form -->
                <form method="POST" action="{{ route('review-questions.destroy', $question->id) }}" id="delete-form-{{ $question->id }}" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            @empty
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-question-circle text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No review questions yet</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto">Add your first question above to start collecting user feedback.</p>
            </div>
            @endforelse
        </div>
    </div>
    @endif

    @if($tab === 'reviews')
    <!-- Reviews & Dashboard Tab Content -->
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @include('admin.partials.stat-card', ['label' => 'Total Reviews', 'value' => number_format($totalReviews), 'icon' => 'fa-comment-dots', 'iconBg' => 'bg-purple-100', 'iconColor' => 'text-purple-600'])
        @include('admin.partials.stat-card', ['label' => 'Users Reviewed', 'value' => number_format($totalUsers), 'icon' => 'fa-users', 'iconBg' => 'bg-emerald-100', 'iconColor' => 'text-emerald-600'])
        @include('admin.partials.stat-card', ['label' => 'Rating Questions', 'value' => count($ratingStats), 'icon' => 'fa-star', 'iconBg' => 'bg-amber-100', 'iconColor' => 'text-amber-600'])
    </div>

    <!-- Rating Statistics Dashboard -->
    @if(count($ratingStats) > 0)
    <div class="admin-card admin-card-body mb-6">
        <div class="mb-6">
            <h2 class="admin-panel-title">Rating Scale Dashboard</h2>
            <p class="admin-panel-subtitle">Distribution and average ratings for each question</p>
        </div>
        <div class="p-6 space-y-8">
            @foreach($ratingStats as $questionId => $stats)
            <div class="border-b border-gray-100 pb-6 last:border-b-0 last:pb-0">
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-gray-900 mb-2">{{ $stats['question'] }}</h3>
                    <div class="flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="text-gray-600">Total Responses:</span>
                            <span class="font-semibold text-gray-900">{{ $stats['total'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-600">Average Rating:</span>
                            <span class="font-semibold text-purple-primary">{{ $stats['average'] }}/5</span>
                            <div class="flex items-center">
                                @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star text-sm {{ $i <= round($stats['average']) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rating Distribution Bars -->
                <div class="space-y-3">
                    @for($rating = 5; $rating >= 1; $rating--)
                    <div class="flex items-center gap-3">
                        <div class="w-8 text-sm font-semibold text-gray-700 text-right">{{ $rating }}</div>
                        <div class="flex items-center gap-2 flex-1">
                            <div class="flex-1 bg-gray-100 rounded-full h-6 overflow-hidden">
                                <div class="bg-gradient-to-r from-purple-400 to-pink-400 h-full rounded-full transition-all" 
                                     style="width: {{ $stats['distribution'][$rating]['percentage'] }}%"></div>
                            </div>
                            <div class="w-16 text-sm text-gray-600 text-right">
                                {{ $stats['distribution'][$rating]['count'] }} ({{ $stats['distribution'][$rating]['percentage'] }}%)
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="admin-card admin-card-body text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-chart-bar text-gray-400 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Rating Data Available</h3>
        <p class="text-sm text-gray-500 max-w-md mx-auto">Add rating questions and wait for users to submit reviews to see statistics here.</p>
    </div>
    @endif

    <!-- Reviews List Grouped by User -->
    <div class="admin-card">
        <div class="admin-toolbar">
            <h2 class="admin-panel-title">All Reviews</h2>
            <p class="admin-panel-subtitle">View all user reviews grouped by user. Click "Reset Reviews" to allow a user to submit new reviews.</p>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($reviewsByUser as $userReviewGroup)
            @php
                $userId = $userReviewGroup['user']->id;
            @endphp
            <div class="p-6 border border-gray-200 rounded-lg mb-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-4 flex-1">
                        <button type="button" 
                                class="toggle-reviews-btn flex items-center justify-center w-8 h-8 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
                                data-user-id="{{ $userId }}"
                                onclick="toggleReviews({{ $userId }}); return false;">
                            <i class="fas fa-chevron-down text-gray-600 transition-transform toggle-chevron" id="chevron-{{ $userId }}" data-user-id="{{ $userId }}"></i>
                        </button>
                        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-primary font-semibold">
                            {{ strtoupper(substr($userReviewGroup['user']->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="flex-1">
                            <p class="text-base font-semibold text-gray-900">{{ $userReviewGroup['user']->name ?? 'Unknown' }}</p>
                            <p class="text-sm text-gray-500">{{ $userReviewGroup['user']->email ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div class="text-sm text-gray-500">
                            <span class="font-medium text-gray-700">{{ $userReviewGroup['reviews']->count() }}</span> review(s)
                            <span class="mx-2">•</span>
                            Submitted: {{ $userReviewGroup['submitted_at']->format('M d, Y') }}
                        </div>
                        <form method="POST" action="{{ route('review-questions.reset-user', $userReviewGroup['user']->id) }}" 
                              class="inline-block" 
                              onsubmit="return confirm('Are you sure you want to reset all reviews for {{ $userReviewGroup['user']->name }}? This will delete all their reviews and allow them to submit new ones.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition-colors text-sm whitespace-nowrap shadow-md hover:shadow-lg">
                                <i class="fas fa-redo mr-2"></i>Reset Reviews
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="space-y-4 reviews-container" id="reviews-{{ $userId }}" style="display: none;">
                    @foreach($userReviewGroup['reviews'] as $review)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <div class="flex items-start gap-3 mb-2">
                                    <p class="text-sm font-semibold text-gray-900 flex-1">{{ $review->reviewQuestion->question ?? 'N/A' }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $review->reviewQuestion->question_type === 'rating' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($review->reviewQuestion->question_type ?? 'N/A') }}
                                    </span>
                                </div>
                                @if($review->answer)
                                <p class="text-sm text-gray-700">{{ $review->answer }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col justify-between">
                                @if($review->rating)
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-sm {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ $review->rating }}/5</span>
                                </div>
                                @endif
                                <p class="text-xs text-gray-500">
                                    {{ $review->created_at->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-comment-slash text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No reviews yet</h3>
                <p class="text-sm text-gray-500 max-w-md mx-auto">Once users submit reviews, they will appear here.</p>
            </div>
            @endforelse
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
// Use event delegation for better reliability
document.addEventListener('DOMContentLoaded', function() {
    console.log('Review scripts loaded');
    
    // Add click handlers to all toggle buttons
    document.querySelectorAll('.toggle-reviews-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const userId = this.getAttribute('data-user-id');
            console.log('Button clicked for user:', userId);
            if (userId && window.toggleReviews) {
                window.toggleReviews(parseInt(userId));
            }
        });
    });
    
    // Also add click handlers directly to chevron icons
    document.querySelectorAll('.toggle-chevron').forEach(function(icon) {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const userId = this.getAttribute('data-user-id');
            console.log('Icon clicked for user:', userId);
            if (userId && window.toggleReviews) {
                window.toggleReviews(parseInt(userId));
            }
        });
    });
});

function deleteQuestion(questionId) {
    if (confirm('Are you sure you want to delete this question? This will also delete all associated reviews.')) {
        document.getElementById('delete-form-' + questionId).submit();
    }
}
</script>
@endpush
@endsection

