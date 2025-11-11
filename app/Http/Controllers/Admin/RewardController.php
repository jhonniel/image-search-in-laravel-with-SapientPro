<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reward;
use App\Models\User;
use App\Models\ImageMetadata;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    /**
     * Display all rewards
     */
    public function index()
    {
        $rewards = Reward::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => Reward::count(),
            'active' => Reward::where('status', 'active')->where('is_used', false)->count(),
            'used' => Reward::where('is_used', true)->count(),
            'auto_assign' => Reward::where('is_auto_assign', true)->count(),
        ];

        return view('admin.rewards.index', compact('rewards', 'stats'));
    }

    /**
     * Show form to create a new reward
     */
    public function create()
    {
        return view('admin.rewards.create');
    }

    /**
     * Store a new reward
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:discount,free_item,cashback',
            'value' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
            'code' => 'nullable|string|max:255|unique:rewards,code',
            'is_auto_assign' => 'boolean',
            'min_reports' => 'nullable|integer|min:0',
            'min_claims' => 'nullable|integer|min:0',
            'min_found_items' => 'nullable|integer|min:0',
            'min_lost_items' => 'nullable|integer|min:0',
            'rule_description' => 'nullable|string',
        ]);

        // Generate unique code if not provided
        if (!$request->code) {
            do {
                $code = strtoupper(Str::random(8));
            } while (Reward::where('code', $code)->exists());
        } else {
            $code = $request->code;
        }

        $reward = Reward::create([
            'user_id' => null, // Will be assigned when sent to user
            'title' => $request->title,
            'description' => $request->description,
            'code' => $code,
            'type' => $request->type,
            'value' => $request->value,
            'expires_at' => $request->expires_at,
            'is_auto_assign' => $request->has('is_auto_assign'),
            'min_reports' => $request->min_reports,
            'min_claims' => $request->min_claims,
            'min_found_items' => $request->min_found_items,
            'min_lost_items' => $request->min_lost_items,
            'rule_description' => $request->rule_description,
            'status' => 'active',
        ]);

        return redirect()->route('admin.rewards.index')
            ->with('success', 'Reward created successfully!');
    }

    /**
     * Show form to send reward to users
     */
    public function showSendForm()
    {
        $users = User::orderBy('name')->get();
        $rewards = Reward::whereNull('user_id')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.rewards.send', compact('users', 'rewards'));
    }

    /**
     * Send reward to user(s)
     */
    public function send(Request $request)
    {
        $request->validate([
            'reward_id' => 'required|exists:rewards,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $templateReward = Reward::findOrFail($request->reward_id);
        $sentCount = 0;

        DB::transaction(function () use ($templateReward, $request, &$sentCount) {
            foreach ($request->user_ids as $userId) {
                // Generate unique code for each user
                do {
                    $code = strtoupper(Str::random(8));
                } while (Reward::where('code', $code)->exists());
                
                // Create a copy of the reward for each user
                Reward::create([
                    'user_id' => $userId,
                    'title' => $templateReward->title,
                    'description' => $templateReward->description,
                    'code' => $code,
                    'type' => $templateReward->type,
                    'value' => $templateReward->value,
                    'expires_at' => $templateReward->expires_at,
                    'is_auto_assign' => false, // Manual assignment
                    'status' => 'active',
                ]);
                $sentCount++;
            }
        });

        return redirect()->route('admin.rewards.index')
            ->with('success', "Reward sent to {$sentCount} user(s) successfully!");
    }

    /**
     * Check and auto-assign rewards based on rules
     */
    public function checkAutoAssign()
    {
        $autoAssignRewards = Reward::where('is_auto_assign', true)
            ->whereNull('user_id')
            ->where('status', 'active')
            ->get();

        $assignedCount = 0;

        DB::transaction(function () use ($autoAssignRewards, &$assignedCount) {
            foreach ($autoAssignRewards as $templateReward) {
                $users = User::all();

                foreach ($users as $user) {
                    // Check if user already has this reward
                    $existingReward = Reward::where('user_id', $user->id)
                        ->where('title', $templateReward->title)
                        ->where('is_auto_assign', true)
                        ->first();

                    if ($existingReward) {
                        continue; // User already has this reward
                    }

                    // Check if user meets the criteria
                    if ($this->userMeetsCriteria($user, $templateReward)) {
                        // Generate unique code
                        do {
                            $code = strtoupper(Str::random(8));
                        } while (Reward::where('code', $code)->exists());
                        
                        Reward::create([
                            'user_id' => $user->id,
                            'title' => $templateReward->title,
                            'description' => $templateReward->description,
                            'code' => $code,
                            'type' => $templateReward->type,
                            'value' => $templateReward->value,
                            'expires_at' => $templateReward->expires_at,
                            'is_auto_assign' => true,
                            'status' => 'active',
                        ]);
                        $assignedCount++;
                    }
                }
            }
        });

        return redirect()->route('admin.rewards.index')
            ->with('success', "Auto-assigned {$assignedCount} reward(s) to eligible users!");
    }

    /**
     * Check if user meets reward criteria
     */
    private function userMeetsCriteria(User $user, Reward $reward): bool
    {
        $userReports = ImageMetadata::where('uploader_email', $user->email)
            ->select('upload_id')
            ->distinct()
            ->count('upload_id');

        $userClaims = ImageMetadata::where('claimed_by_email', $user->email)
            ->where('is_claimed', true)
            ->select('upload_id')
            ->distinct()
            ->count('upload_id');

        $userFoundItems = ImageMetadata::where('uploader_email', $user->email)
            ->where('status', 'found')
            ->select('upload_id')
            ->distinct()
            ->count('upload_id');

        $userLostItems = ImageMetadata::where('uploader_email', $user->email)
            ->where('status', 'lost')
            ->select('upload_id')
            ->distinct()
            ->count('upload_id');

        if ($reward->min_reports && $userReports < $reward->min_reports) {
            return false;
        }

        if ($reward->min_claims && $userClaims < $reward->min_claims) {
            return false;
        }

        if ($reward->min_found_items && $userFoundItems < $reward->min_found_items) {
            return false;
        }

        if ($reward->min_lost_items && $userLostItems < $reward->min_lost_items) {
            return false;
        }

        return true;
    }

    /**
     * Delete a reward
     */
    public function destroy($id)
    {
        $reward = Reward::findOrFail($id);
        
        // Only allow deleting template rewards (not assigned to users)
        if ($reward->user_id === null) {
            $reward->delete();
            return redirect()->route('admin.rewards.index')
                ->with('success', 'Reward deleted successfully!');
        }

        return redirect()->route('admin.rewards.index')
            ->with('error', 'Cannot delete rewards that have been assigned to users.');
    }
}
