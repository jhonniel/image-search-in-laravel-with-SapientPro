<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        
        // Get active review questions
        $reviewQuestions = ReviewQuestion::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();
        
        // Get user's existing reviews
        $userReviews = Review::where('user_id', $user->id)
            ->get()
            ->keyBy('review_question_id');
        
        // Check if user has completed all active reviews
        $activeQuestionIds = $reviewQuestions->pluck('id')->toArray();
        $userReviewQuestionIds = $userReviews->pluck('review_question_id')->toArray();
        
        $hasCompletedAllReviews = count($activeQuestionIds) > 0 && 
                                  count(array_intersect($userReviewQuestionIds, $activeQuestionIds)) === count($activeQuestionIds);
        
        return view('user.review-form', compact('reviewQuestions', 'userReviews', 'hasCompletedAllReviews'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Get all active review questions
        $questions = ReviewQuestion::where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();
        
        $rules = [];
        foreach ($questions as $question) {
            if ($question->is_required) {
                if ($question->question_type === 'rating') {
                    $rules["question_{$question->id}"] = 'required|integer|min:1|max:5';
                } else {
                    $rules["question_{$question->id}"] = 'required|string|max:2000';
                }
            } else {
                if ($question->question_type === 'rating') {
                    $rules["question_{$question->id}"] = 'nullable|integer|min:1|max:5';
                } else {
                    $rules["question_{$question->id}"] = 'nullable|string|max:2000';
                }
            }
        }
        
        $validated = $request->validate($rules);
        
        // Save each answer
        foreach ($questions as $question) {
            $key = "question_{$question->id}";
            $value = $validated[$key] ?? null;
            
            if ($value !== null) {
                Review::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'review_question_id' => $question->id,
                    ],
                    [
                        'rating' => $question->question_type === 'rating' ? (int) $value : null,
                        'answer' => $question->question_type === 'text' ? $value : null,
                    ]
                );
            }
        }
        
        return redirect()->route('reviews.create')
            ->with('success', 'Thank you for your feedback! Your review has been submitted.');
    }
}
