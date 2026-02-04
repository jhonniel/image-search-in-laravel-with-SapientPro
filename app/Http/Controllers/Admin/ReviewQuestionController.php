<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewQuestion;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewQuestionController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'questions');
        
        $questions = ReviewQuestion::orderBy('display_order', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Get reviews data for dashboard
        $totalReviews = Review::count();
        $totalUsers = Review::distinct('user_id')->count('user_id');
        
        // Get rating statistics for each rating question
        $ratingStats = [];
        $ratingQuestions = ReviewQuestion::where('question_type', 'rating')
            ->where('is_active', true)
            ->get();
        
        foreach ($ratingQuestions as $question) {
            $ratings = Review::where('review_question_id', $question->id)
                ->whereNotNull('rating')
                ->select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->orderBy('rating', 'asc')
                ->get()
                ->keyBy('rating');
            
            $totalRatings = Review::where('review_question_id', $question->id)
                ->whereNotNull('rating')
                ->count();
            
            $averageRating = Review::where('review_question_id', $question->id)
                ->whereNotNull('rating')
                ->avg('rating');
            
            // Build rating distribution (1-5)
            $distribution = [];
            for ($i = 1; $i <= 5; $i++) {
                $count = $ratings->get($i) ? $ratings->get($i)->count : 0;
                $percentage = $totalRatings > 0 ? round(($count / $totalRatings) * 100, 1) : 0;
                $distribution[$i] = [
                    'count' => $count,
                    'percentage' => $percentage,
                ];
            }
            
            $ratingStats[$question->id] = [
                'question' => $question->question,
                'total' => $totalRatings,
                'average' => round($averageRating, 2),
                'distribution' => $distribution,
            ];
        }
        
        // Get all reviews grouped by user
        $reviewsByUser = Review::with(['user', 'reviewQuestion'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id')
            ->map(function ($userReviews) {
                return [
                    'user' => $userReviews->first()->user,
                    'reviews' => $userReviews,
                    'submitted_at' => $userReviews->max('created_at'),
                ];
            })
            ->sortByDesc('submitted_at')
            ->values();
        
        return view('admin.review-questions.index', compact(
            'questions',
            'tab',
            'totalReviews',
            'totalUsers',
            'ratingStats',
            'reviewsByUser'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'question_type' => 'required|in:rating,text',
            'display_order' => 'nullable|integer|min:0',
        ]);

        ReviewQuestion::create([
            'question' => $validated['question'],
            'question_type' => $validated['question_type'],
            'is_required' => $request->has('is_required') && $request->input('is_required') == '1',
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => $request->has('is_active') && $request->input('is_active') == '1',
        ]);

        return redirect()->route('review-questions.index')
            ->with('success', 'Review question added successfully.');
    }

    public function update(Request $request, $id)
    {
        $reviewQuestion = ReviewQuestion::findOrFail($id);
        
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'question_type' => 'required|in:rating,text',
            'display_order' => 'nullable|integer|min:0',
            'is_required' => 'nullable|in:0,1',
            'is_active' => 'nullable|in:0,1',
        ]);

        $reviewQuestion->update([
            'question' => $validated['question'],
            'question_type' => $validated['question_type'],
            'is_required' => $request->input('is_required', '0') == '1',
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => $request->input('is_active', '0') == '1',
        ]);

        return redirect()->route('review-questions.index', ['tab' => 'questions'])
            ->with('success', 'Review question updated successfully.');
    }

    public function destroy($id)
    {
        $reviewQuestion = ReviewQuestion::findOrFail($id);
        $reviewQuestion->delete();

        return redirect()->route('review-questions.index', ['tab' => 'questions'])
            ->with('success', 'Review question deleted successfully.');
    }

    public function resetUserReviews($userId)
    {
        $user = \App\Models\User::findOrFail($userId);
        
        // Delete all reviews for this user
        Review::where('user_id', $userId)->delete();

        return redirect()->route('review-questions.index', ['tab' => 'reviews'])
            ->with('success', "All reviews for {$user->name} have been reset. They can now submit new reviews.");
    }
}
