@extends('layouts.user')

@section('title', 'Submit a Review - FindITFast')

@section('content')
<script>
// Define function immediately - must be available before HTML renders
window.updateStarRating = function(questionId, rating) {
    // Update all stars in this group immediately
    for (let i = 1; i <= 5; i++) {
        const icon = document.querySelector('.star-icon-' + questionId + '-' + i);
        if (icon) {
            if (i <= rating) {
                // Yellow for selected stars
                icon.style.cssText = 'color: #fbbf24 !important; cursor: pointer; transition: color 0.2s;';
            } else {
                // Gray for unselected stars
                icon.style.cssText = 'color: #d1d5db !important; cursor: pointer; transition: color 0.2s;';
            }
        }
    }
    
    // Update rating text
    const ratingText = document.querySelector('.rating-text-' + questionId);
    if (ratingText) {
        ratingText.textContent = '(' + rating + '/5)';
        ratingText.style.display = 'inline';
    }
    
    // Check the radio button
    const radio = document.querySelector('.star-radio-' + questionId + '[value="' + rating + '"]');
    if (radio) {
        radio.checked = true;
    }
};
</script>

<div class="user-page">
    @include('user.partials.page-header', [
        'eyebrow' => 'Feedback',
        'title' => 'Submit a review',
        'description' => 'Share your feedback and help us improve FindITFast',
        'actions' => '<a href="'.route('dashboard').'" class="user-btn-secondary w-full sm:w-auto"><i class="fas fa-arrow-left"></i> Back to dashboard</a>',
    ])

    @if(session('success'))
        @include('user.partials.alert', ['type' => 'success', 'message' => session('success')])
    @endif

    @if(isset($hasCompletedAllReviews) && $hasCompletedAllReviews)
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500 text-2xl"></i>
            </div>
            <div class="ml-4 flex-1">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">Review Already Submitted</h3>
                <p class="text-blue-700 mb-3">You have already submitted your review for all active questions. Thank you for your feedback!</p>
                <p class="text-blue-600 text-sm">If you need to submit a new review, please contact an administrator to reset your previous submission.</p>
            </div>
        </div>
    </div>
    @endif

    @if($reviewQuestions->count() > 0)
    <div class="user-card user-card-body">
        <form action="{{ route('reviews.store') }}" method="POST" class="space-y-6" @if(isset($hasCompletedAllReviews) && $hasCompletedAllReviews) onsubmit="event.preventDefault(); alert('You have already submitted your review. Please contact an administrator to reset it if you need to submit a new one.'); return false;" @endif>
            @csrf
            @foreach($reviewQuestions as $question)
            <div class="border-b border-gray-100 pb-6 last:border-b-0 last:pb-0">
                <label class="block text-base font-semibold text-gray-900 mb-3">
                    {{ $question->question }}
                    @if($question->is_required)
                    <span class="text-red-500">*</span>
                    @endif
                </label>

                @if($question->question_type === 'rating')
                <div class="flex items-center space-x-2 star-rating-group" data-question="{{ $question->id }}" id="star-group-{{ $question->id }}">
                    @php
                        $existingReview = $userReviews->get($question->id);
                        $existingRating = $existingReview ? $existingReview->rating : null;
                    @endphp
                    @for($i = 1; $i <= 5; $i++)
                    <label class="star-label" style="display: inline-block; {{ (isset($hasCompletedAllReviews) && $hasCompletedAllReviews) ? 'cursor: not-allowed; opacity: 0.6;' : 'cursor: pointer;' }}" 
                           @if(!(isset($hasCompletedAllReviews) && $hasCompletedAllReviews)) onclick="updateStarRating({{ $question->id }}, {{ $i }})" @endif>
                        <input type="radio" name="question_{{ $question->id }}" value="{{ $i }}" 
                               class="sr-only star-radio-{{ $question->id }}" 
                               {{ $existingRating == $i ? 'checked' : '' }}
                               {{ $question->is_required ? 'required' : '' }}
                               {{ (isset($hasCompletedAllReviews) && $hasCompletedAllReviews) ? 'disabled' : '' }}
                               @if(!(isset($hasCompletedAllReviews) && $hasCompletedAllReviews)) onchange="updateStarRating({{ $question->id }}, {{ $i }})" @endif>
                        <i class="fas fa-star text-3xl star-rating-icon star-icon-{{ $question->id }}-{{ $i }}" 
                           data-rating="{{ $i }}"
                           @if(!(isset($hasCompletedAllReviews) && $hasCompletedAllReviews)) onclick="updateStarRating({{ $question->id }}, {{ $i }}); return false;" @endif
                           style="color: {{ $existingRating && $existingRating >= $i ? '#fbbf24' : '#d1d5db' }}; {{ (isset($hasCompletedAllReviews) && $hasCompletedAllReviews) ? 'cursor: not-allowed;' : 'cursor: pointer;' }} transition: color 0.2s;"></i>
                    </label>
                    @endfor
                    @if($existingRating)
                    <span class="ml-3 text-sm text-gray-500 rating-display rating-text-{{ $question->id }}">({{ $existingRating }}/5)</span>
                    @else
                    <span class="ml-3 text-sm text-gray-500 rating-display rating-text-{{ $question->id }}" style="display: none;"></span>
                    @endif
                </div>
                @else
                @php
                    $existingReview = $userReviews->get($question->id);
                    $existingAnswer = $existingReview ? $existingReview->answer : '';
                @endphp
                <textarea name="question_{{ $question->id }}" 
                          rows="4"
                          class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-200 transition {{ (isset($hasCompletedAllReviews) && $hasCompletedAllReviews) ? 'bg-gray-100 cursor-not-allowed opacity-60' : '' }}"
                          placeholder="Share your thoughts..."
                          {{ $question->is_required ? 'required' : '' }}
                          {{ (isset($hasCompletedAllReviews) && $hasCompletedAllReviews) ? 'disabled readonly' : '' }}>{{ $existingAnswer }}</textarea>
                @endif
            </div>
            @endforeach

            <div class="pt-4 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Cancel
                </a>
                @if(isset($hasCompletedAllReviews) && $hasCompletedAllReviews)
                <button type="button" disabled class="inline-flex items-center justify-center px-6 py-3 bg-gray-400 text-white font-semibold rounded-lg cursor-not-allowed opacity-60">
                    <i class="fas fa-check mr-2"></i>
                    Already Submitted
                </button>
                @else
                <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-purple-primary text-white font-semibold rounded-lg shadow-lg shadow-purple-200 hover:bg-purple-700 transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Submit Review
                </button>
                @endif
            </div>
        </form>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-star text-gray-400 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Review Questions Available</h3>
        <p class="text-sm text-gray-500 mb-6">Review questions will appear here once they are added by the administrator.</p>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-6 py-3 bg-purple-primary text-white font-semibold rounded-lg shadow-md hover:bg-purple-700 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Dashboard
        </a>
    </div>
    @endif
</div>

@push('styles')
<style>
    .star-icon {
        transition: color 0.2s ease, transform 0.2s ease;
        display: inline-block;
    }
    .star-icon.text-yellow-400,
    i.fa-star.text-yellow-400 {
        color: #fbbf24 !important;
        filter: drop-shadow(0 2px 4px rgba(251, 191, 36, 0.4));
    }
    .star-icon.text-yellow-300,
    i.fa-star.text-yellow-300 {
        color: #fcd34d !important;
    }
    .star-icon.text-gray-300,
    i.fa-star.text-gray-300 {
        color: #d1d5db !important;
    }
    label.cursor-pointer {
        display: inline-block;
        cursor: pointer;
    }
    label.cursor-pointer:hover .star-icon {
        transform: scale(1.1);
    }
</style>
@endpush

@push('scripts')
<script>
// Define function immediately in global scope - must be available before HTML renders
window.updateStarRating = function(questionId, rating) {
    // Update all stars in this group immediately
    for (let i = 1; i <= 5; i++) {
        const icon = document.querySelector('.star-icon-' + questionId + '-' + i);
        if (icon) {
            if (i <= rating) {
                // Yellow for selected stars
                icon.style.cssText = 'color: #fbbf24 !important; cursor: pointer; transition: color 0.2s;';
            } else {
                // Gray for unselected stars
                icon.style.cssText = 'color: #d1d5db !important; cursor: pointer; transition: color 0.2s;';
            }
        }
    }
    
    // Update rating text
    const ratingText = document.querySelector('.rating-text-' + questionId);
    if (ratingText) {
        ratingText.textContent = '(' + rating + '/5)';
        ratingText.style.display = 'inline';
    }
    
    // Check the radio button
    const radio = document.querySelector('.star-radio-' + questionId + '[value="' + rating + '"]');
    if (radio) {
        radio.checked = true;
    }
};

// Initialize on page load for existing ratings
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.star-rating-group').forEach(function(group) {
        const checked = group.querySelector('input[type="radio"]:checked');
        if (checked) {
            const questionId = group.getAttribute('data-question');
            const rating = parseInt(checked.value);
            window.updateStarRating(questionId, rating);
        }
    });
    
    // Add hover effects
    document.querySelectorAll('.star-label').forEach(function(label) {
        label.addEventListener('mouseenter', function() {
            const icon = this.querySelector('.star-rating-icon');
            if (icon) {
                const classMatch = icon.className.match(/star-icon-(\d+)-(\d+)/);
                if (classMatch) {
                    const qId = classMatch[1];
                    const hoverRating = parseInt(classMatch[2]);
                    for (let i = 1; i <= 5; i++) {
                        const starIcon = document.querySelector('.star-icon-' + qId + '-' + i);
                        if (starIcon) {
                            if (i <= hoverRating) {
                                starIcon.style.cssText = 'color: #fcd34d !important; cursor: pointer; transition: color 0.2s;';
                            } else {
                                starIcon.style.cssText = 'color: #d1d5db !important; cursor: pointer; transition: color 0.2s;';
                            }
                        }
                    }
                }
            }
        });
        
        label.addEventListener('mouseleave', function() {
            const icon = this.querySelector('.star-rating-icon');
            if (icon) {
                const classMatch = icon.className.match(/star-icon-(\d+)-(\d+)/);
                if (classMatch) {
                    const qId = classMatch[1];
                    const checked = document.querySelector('.star-radio-' + qId + ':checked');
                    if (checked) {
                        window.updateStarRating(qId, parseInt(checked.value));
                    } else {
                        for (let i = 1; i <= 5; i++) {
                            const starIcon = document.querySelector('.star-icon-' + qId + '-' + i);
                            if (starIcon) {
                                starIcon.style.cssText = 'color: #d1d5db !important; cursor: pointer; transition: color 0.2s;';
                            }
                        }
                    }
                }
            }
        });
    });
});
</script>
@endpush
@endsection

