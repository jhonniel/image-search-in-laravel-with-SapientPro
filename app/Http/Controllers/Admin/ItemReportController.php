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
            'dismissed' => ItemReport::where('status', 'dismissed')->count(),
            'flagged_items' => ItemReport::query()->distinct()->count('upload_id'),
        ];

        $userStats = [
            'total_reports' => UserReport::count(),
            'pending' => UserReport::where('status', 'pending')->count(),
            'reviewed' => UserReport::where('status', 'reviewed')->count(),
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
            'status' => 'required|in:pending,reviewed,dismissed',
        ]);

        $itemReport->status = $request->input('status');
        $itemReport->save();

        return back()->with('success', 'Item report status updated.');
    }

    public function updateUserReportStatus(Request $request, UserReport $userReport)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,dismissed',
        ]);

        $userReport->status = $request->input('status');
        $userReport->save();

        return redirect()
            ->route('item-reports.index', ['tab' => 'users', 'status' => $request->get('status', 'all')])
            ->with('success', 'User report status updated.');
    }

    public function deleteItem(string $uploadId)
    {
        try {
            $items = ImageMetadata::where('upload_id', $uploadId)->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found or already deleted.',
                ], 404);
            }

            $deletedCount = ImageMetadata::where('upload_id', $uploadId)->delete();

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
                'created_at' => $first->created_at ?? null,
                'deleted_at' => $first->deleted_at ?? null,
                'images' => $images,
                'report_count' => (int) ($reportCounts[$uploadId] ?? $reports->count()),
                'pending_count' => $reports->where('status', 'pending')->count(),
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
                    'created_at' => $report->created_at?->format('M d, Y g:i A'),
                    'appeal_message' => $report->appeal_message,
                    'appealed_at' => $report->appealed_at?->format('M d, Y g:i A'),
                    'has_appeal' => $report->hasAppeal(),
                ];
            });
    }
}
