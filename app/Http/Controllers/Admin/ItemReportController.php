<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImageMetadata;
use App\Models\ItemReport;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ItemReportController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'items');
        $status = $request->get('status', 'all');

        $itemStats = [
            'total_reports' => ItemReport::count(),
            'pending' => ItemReport::where('status', 'pending')->count(),
            'reviewed' => ItemReport::where('status', 'reviewed')->count(),
            'violated' => ItemReport::where('status', 'violated')->count(),
            'dismissed' => ItemReport::where('status', 'dismissed')->count(),
            'flagged_items' => ItemReport::query()->distinct()->count('upload_id'),
        ];

        $userStats = [
            'total_reports' => UserReport::count(),
            'pending' => UserReport::where('status', 'pending')->count(),
            'reviewed' => UserReport::where('status', 'reviewed')->count(),
            'violated' => UserReport::where('status', 'violated')->count(),
            'dismissed' => UserReport::where('status', 'dismissed')->count(),
            'flagged_users' => UserReport::query()->distinct()->count('reported_user_id'),
        ];

        $reportedItems = collect();
        $userReports = collect();

        if ($tab !== 'users') {
            $reportedItems = $this->buildReportedItems($status);
        }

        if ($tab === 'users') {
            $userReports = $this->buildUserReports($status);
        }

        return view('admin.item-reports.index', [
            'tab' => $tab,
            'status' => $status,
            'reportedItems' => $reportedItems,
            'userReports' => $userReports,
            'itemStats' => $itemStats,
            'userStats' => $userStats,
            'stats' => $tab === 'users' ? $userStats : $itemStats,
            'itemLabels' => ItemReport::LABELS,
            'userLabels' => UserReport::LABELS,
        ]);
    }

    public function updateStatus(Request $request, ItemReport $itemReport)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,violated,dismissed',
            'offense_banned' => 'nullable|boolean',
            'offense_cannot_post' => 'nullable|boolean',
            'offense_cannot_claim' => 'nullable|boolean',
            'offense_cannot_login' => 'nullable|boolean',
            'login_block_days' => 'nullable|integer|min:1|max:365',
            'remove_item' => 'nullable|boolean',
        ]);

        $status = $request->input('status');
        $itemReport->status = $status;
        $itemReport->save();

        $removed = false;
        if ($status === 'violated') {
            $uploader = $this->resolveUploaderForUploadId($itemReport->upload_id);
            $this->applyOffensesToUser(
                $uploader,
                $request,
                'Offense applied from item report #'.$itemReport->id
            );

            if ($request->boolean('remove_item')) {
                $removed = $this->softDeleteUpload($itemReport->upload_id);
            }

            Log::info('Admin marked item report as violated', [
                'report_id' => $itemReport->id,
                'upload_id' => $itemReport->upload_id,
                'uploader_user_id' => $uploader?->id,
                'removed_item' => $removed,
            ]);
        }

        $redirectStatus = $request->input('redirect_status', $status);
        $message = 'Item report status updated.';
        if ($status === 'violated') {
            $message = $removed
                ? 'Item report marked as Violated, offenses applied to uploader, and listing removed.'
                : 'Item report marked as Violated and offenses applied to uploader.';
        }

        return redirect()
            ->route('item-reports.index', ['tab' => 'items', 'status' => $redirectStatus])
            ->with('success', $message);
    }

    public function updateUserReportStatus(Request $request, UserReport $userReport)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,violated,dismissed',
            'offense_banned' => 'nullable|boolean',
            'offense_cannot_post' => 'nullable|boolean',
            'offense_cannot_claim' => 'nullable|boolean',
            'offense_cannot_login' => 'nullable|boolean',
            'login_block_days' => 'nullable|integer|min:1|max:365',
        ]);

        $status = $request->input('status');
        $userReport->status = $status;
        $userReport->save();

        if ($status === 'violated') {
            $reportedUser = User::find($userReport->reported_user_id);
            $this->applyOffensesToUser(
                $reportedUser,
                $request,
                'Offense applied from user report #'.$userReport->id
            );

            Log::info('Admin applied user offense after violated report', [
                'report_id' => $userReport->id,
                'reported_user_id' => $reportedUser?->id,
                'is_banned' => $reportedUser?->is_banned,
                'cannot_post' => $reportedUser?->cannot_post,
                'cannot_claim' => $reportedUser?->cannot_claim,
                'login_blocked_until' => optional($reportedUser?->login_blocked_until)?->toDateTimeString(),
            ]);
        }

        $redirectStatus = $request->input('redirect_status', $status);

        return redirect()
            ->route('item-reports.index', ['tab' => 'users', 'status' => $redirectStatus])
            ->with('success', $status === 'violated'
                ? 'User report marked as Violated and offenses applied.'
                : 'User report status updated.');
    }

    public function deleteItem(string $uploadId)
    {
        try {
            $deletedCount = $this->softDeleteUpload($uploadId);

            if ($deletedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found or already deleted.',
                ], 404);
            }

            ItemReport::where('upload_id', $uploadId)
                ->where('status', 'pending')
                ->update(['status' => 'reviewed']);

            Log::info('Admin deleted reported item', [
                'upload_id' => $uploadId,
                'deleted_count' => $deletedCount,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Item deleted ({$deletedCount} image(s)). It can be restored from All Items trash if needed.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete item: '.$e->getMessage(),
            ], 500);
        }
    }

    private function applyOffensesToUser(?User $user, Request $request, string $note): void
    {
        if (! $user || $user->role === 'admin') {
            return;
        }

        if ($request->boolean('offense_banned')) {
            $user->forceFill([
                'is_banned' => true,
                'cannot_post' => false,
                'cannot_claim' => false,
                'login_blocked_until' => null,
                'restriction_note' => str_replace('Offense applied from', 'Banned after', $note),
            ])->save();

            return;
        }

        $cannotLogin = $request->boolean('offense_cannot_login');
        $days = (int) $request->input('login_block_days', 7);
        if ($cannotLogin && $days < 1) {
            $days = 7;
        }

        $user->forceFill([
            'is_banned' => false,
            'cannot_post' => $request->boolean('offense_cannot_post'),
            'cannot_claim' => $request->boolean('offense_cannot_claim'),
            'login_blocked_until' => $cannotLogin ? now()->addDays($days) : null,
            'restriction_note' => $note,
        ])->save();
    }

    private function resolveUploaderForUploadId(string $uploadId): ?User
    {
        $email = ImageMetadata::withTrashed()
            ->where('upload_id', $uploadId)
            ->value('uploader_email');

        if (! $email) {
            return null;
        }

        return User::where('email', $email)->first();
    }

    private function softDeleteUpload(string $uploadId): int
    {
        return ImageMetadata::where('upload_id', $uploadId)->delete();
    }

    private function buildReportedItems(string $status)
    {
        $reportCounts = ItemReport::query()
            ->select('upload_id', DB::raw('COUNT(*) as report_count'))
            ->groupBy('upload_id')
            ->pluck('report_count', 'upload_id');

        $groupedUploadIds = ItemReport::query()
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->select('upload_id')
            ->distinct()
            ->pluck('upload_id');

        $itemGroups = ImageMetadata::withTrashed()
            ->whereIn('upload_id', $groupedUploadIds)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('upload_id');

        $reportsByUpload = ItemReport::with('reporter')
            ->whereIn('upload_id', $groupedUploadIds)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('upload_id');

        return $groupedUploadIds->map(function ($uploadId) use ($itemGroups, $reportsByUpload, $reportCounts) {
            $group = $itemGroups->get($uploadId) ?? collect();
            $first = $group->first();
            $reports = $reportsByUpload->get($uploadId) ?? collect();

            $tags = [];
            if ($first && $first->tags) {
                $tags = is_string($first->tags) ? (json_decode($first->tags, true) ?: []) : (array) $first->tags;
            }

            $images = $group->map(function ($item) {
                $filePath = (string) ($item->file_path ?? '');
                if ($filePath === '') {
                    return null;
                }
                if (str_starts_with($filePath, '/storage/') || str_starts_with($filePath, 'http')) {
                    $path = $filePath;
                } elseif (str_starts_with($filePath, 'storage/')) {
                    $path = '/'.$filePath;
                } else {
                    $path = Storage::url($filePath);
                }

                return [
                    'path' => $path,
                    'original_name' => $item->original_name,
                ];
            })->filter()->values()->all();

            $uploader = $first
                ? User::where('email', $first->uploader_email)->first()
                : null;

            return [
                'upload_id' => $uploadId,
                'item_type' => $first->status ?? 'unknown',
                'description' => $first->description ?? 'Item removed or unavailable',
                'location' => $first->location ?? null,
                'tags' => $tags,
                'uploader_email' => $first->uploader_email ?? null,
                'uploader_name' => $uploader->name ?? ($first->uploader_email ?? 'Unknown'),
                'uploader_is_banned' => (bool) ($uploader->is_banned ?? false),
                'uploader_cannot_post' => (bool) ($uploader->cannot_post ?? false),
                'uploader_cannot_claim' => (bool) ($uploader->cannot_claim ?? false),
                'uploader_login_blocked_until' => $uploader?->login_blocked_until?->format('M d, Y'),
                'created_at' => $first->created_at ?? null,
                'deleted_at' => $first->deleted_at ?? null,
                'images' => $images,
                'report_count' => (int) ($reportCounts[$uploadId] ?? $reports->count()),
                'pending_count' => $reports->where('status', 'pending')->count(),
                'violated_count' => $reports->where('status', 'violated')->count(),
                'reports' => $reports->map(fn (ItemReport $r) => [
                    'id' => $r->id,
                    'label' => $r->label,
                    'label_name' => $r->labelName(),
                    'explanation' => $r->explanation,
                    'status' => $r->status,
                    'reporter_name' => $r->reporter->name ?? 'Unknown',
                    'reporter_email' => $r->reporter->email ?? null,
                    'created_at' => $r->created_at?->format('M d, Y g:i A'),
                    'appeal_message' => $r->appeal_message,
                    'appealed_at' => $r->appealed_at?->format('M d, Y g:i A'),
                    'has_appeal' => $r->hasAppeal(),
                ])->values()->all(),
            ];
        })->sortByDesc('report_count')->values();
    }

    private function buildUserReports(string $status)
    {
        return UserReport::with(['reporter', 'reportedUser'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->get()
            ->map(function (UserReport $report) {
                $item = null;
                if ($report->upload_id) {
                    $meta = ImageMetadata::withTrashed()
                        ->where('upload_id', $report->upload_id)
                        ->first();
                    if ($meta) {
                        $item = [
                            'upload_id' => $meta->upload_id,
                            'description' => $meta->description,
                            'item_type' => $meta->status,
                            'deleted' => $meta->trashed(),
                        ];
                    }
                }

                return [
                    'id' => $report->id,
                    'label' => $report->label,
                    'label_name' => $report->labelName(),
                    'explanation' => $report->explanation,
                    'status' => $report->status,
                    'upload_id' => $report->upload_id,
                    'item' => $item,
                    'reporter_name' => $report->reporter->name ?? 'Unknown',
                    'reporter_email' => $report->reporter->email ?? null,
                    'reported_name' => $report->reportedUser->name ?? 'Unknown',
                    'reported_email' => $report->reportedUser->email ?? null,
                    'reported_user_id' => $report->reported_user_id,
                    'reported_is_banned' => (bool) ($report->reportedUser->is_banned ?? false),
                    'reported_cannot_post' => (bool) ($report->reportedUser->cannot_post ?? false),
                    'reported_cannot_claim' => (bool) ($report->reportedUser->cannot_claim ?? false),
                    'reported_login_blocked_until' => $report->reportedUser?->login_blocked_until?->format('M d, Y'),
                    'created_at' => $report->created_at?->format('M d, Y g:i A'),
                    'appeal_message' => $report->appeal_message,
                    'appealed_at' => $report->appealed_at?->format('M d, Y g:i A'),
                    'has_appeal' => $report->hasAppeal(),
                ];
            });
    }
}
