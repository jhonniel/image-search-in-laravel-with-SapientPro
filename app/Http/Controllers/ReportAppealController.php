<?php

namespace App\Http\Controllers;

use App\Models\ImageMetadata;
use App\Models\ItemReport;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReportAppealController extends Controller
{
    /**
     * Appeal an item (listing) report — poster defends that the post is legitimate.
     */
    public function appealItemReport(Request $request, ItemReport $itemReport)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'appeal_message' => 'required|string|min:10|max:2000',
        ], [
            'appeal_message.required' => 'Please explain why this listing is legitimate.',
            'appeal_message.min' => 'Appeal must be at least 10 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $item = ImageMetadata::withTrashed()->where('upload_id', $itemReport->upload_id)->first();
        if (! $item || strcasecmp((string) $item->uploader_email, (string) $user->email) !== 0) {
            return response()->json([
                'success' => false,
                'error' => 'You can only appeal reports against your own listings.',
            ], 403);
        }

        if ($itemReport->appealed_at) {
            return response()->json([
                'success' => false,
                'error' => 'You have already submitted an appeal for this report.',
            ], 409);
        }

        $itemReport->appeal_message = $request->input('appeal_message');
        $itemReport->appealed_at = now();
        $itemReport->save();

        $this->markReportNotificationsAppealed($user->id, 'item', $itemReport->id);
        $this->notifyUserAppealReceived($user->id, 'item', $itemReport->id, $itemReport->upload_id);

        Log::info('Item report appealed', [
            'report_id' => $itemReport->id,
            'user_id' => $user->id,
            'upload_id' => $itemReport->upload_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your appeal was submitted. An admin will review it.',
        ]);
    }

    /**
     * Appeal a user report — reported claimer defends that the claim is legitimate.
     */
    public function appealUserReport(Request $request, UserReport $userReport)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'appeal_message' => 'required|string|min:10|max:2000',
        ], [
            'appeal_message.required' => 'Please explain why this claim / report is incorrect.',
            'appeal_message.min' => 'Appeal must be at least 10 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        if ((int) $userReport->reported_user_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'You can only appeal reports filed against you.',
            ], 403);
        }

        if ($userReport->appealed_at) {
            return response()->json([
                'success' => false,
                'error' => 'You have already submitted an appeal for this report.',
            ], 409);
        }

        $userReport->appeal_message = $request->input('appeal_message');
        $userReport->appealed_at = now();
        $userReport->save();

        $this->markReportNotificationsAppealed($user->id, 'user', $userReport->id);
        $this->notifyUserAppealReceived($user->id, 'user', $userReport->id, $userReport->upload_id);

        Log::info('User report appealed', [
            'report_id' => $userReport->id,
            'user_id' => $user->id,
            'upload_id' => $userReport->upload_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your appeal was submitted. An admin will review it.',
        ]);
    }

    private function notifyUserAppealReceived(int $userId, string $reportType, int $reportId, ?string $uploadId): void
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'type' => 'report_appeal_submitted',
                'title' => 'Appeal submitted',
                'message' => 'Your appeal was sent to admins. They will review the report and your response.',
                'data' => [
                    'report_type' => $reportType,
                    'report_id' => $reportId,
                    'upload_id' => $uploadId,
                    'can_appeal' => false,
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create appeal confirmation notification: '.$e->getMessage());
        }
    }

    private function markReportNotificationsAppealed(int $userId, string $reportType, int $reportId): void
    {
        $types = $reportType === 'item' ? ['item_reported'] : ['user_reported'];

        Notification::where('user_id', $userId)
            ->whereIn('type', $types)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->each(function (Notification $notification) use ($reportId) {
                $data = $notification->data ?? [];
                if ((int) ($data['report_id'] ?? 0) !== (int) $reportId) {
                    return;
                }
                $data['can_appeal'] = false;
                $data['appealed'] = true;
                $notification->data = $data;
                $notification->save();
            });
    }

    /**
     * Notify the listing owner that their post was reported.
     */
    public static function notifyItemReported(ItemReport $report, User $owner): void
    {
        try {
            $notification = Notification::create([
                'user_id' => $owner->id,
                'type' => 'item_reported',
                'title' => 'Your listing was reported',
                'message' => 'Someone reported your post ('.$report->labelName().'). You can appeal if the listing is legitimate.',
                'data' => [
                    'report_type' => 'item',
                    'report_id' => $report->id,
                    'upload_id' => $report->upload_id,
                    'label' => $report->label,
                    'label_name' => $report->labelName(),
                    'can_appeal' => true,
                ],
                'is_read' => false,
            ]);

            Log::info('System notification sent for item report', [
                'notification_id' => $notification->id,
                'owner_user_id' => $owner->id,
                'report_id' => $report->id,
                'upload_id' => $report->upload_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify item owner of report: '.$e->getMessage(), [
                'report_id' => $report->id,
                'owner_user_id' => $owner->id,
            ]);
        }
    }

    /**
     * Notify the reported user that they were reported (e.g. false claim).
     */
    public static function notifyUserReported(UserReport $report, User $reportedUser): void
    {
        try {
            $notification = Notification::create([
                'user_id' => $reportedUser->id,
                'type' => 'user_reported',
                'title' => 'You were reported',
                'message' => 'Someone reported you ('.$report->labelName().'). You can appeal if your claim or activity is legitimate.',
                'data' => [
                    'report_type' => 'user',
                    'report_id' => $report->id,
                    'upload_id' => $report->upload_id,
                    'label' => $report->label,
                    'label_name' => $report->labelName(),
                    'can_appeal' => true,
                ],
                'is_read' => false,
            ]);

            Log::info('System notification sent for user report', [
                'notification_id' => $notification->id,
                'reported_user_id' => $reportedUser->id,
                'report_id' => $report->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify reported user: '.$e->getMessage(), [
                'report_id' => $report->id,
                'reported_user_id' => $reportedUser->id,
            ]);
        }
    }

    /**
     * Notify all admins that a user was reported (bell + Reported Items page).
     */
    public static function notifyAdminsOfUserReport(UserReport $report, User $reportedUser): void
    {
        $admins = User::where('role', 'admin')->get();
        if ($admins->isEmpty()) {
            return;
        }

        $reporterName = optional($report->reporter)->name ?? 'A user';
        $title = 'User reported';
        $message = $reporterName.' reported '.$reportedUser->name.' ('.$report->labelName().'). Review in Reported Items → Reported users.';

        foreach ($admins as $admin) {
            try {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'admin_user_reported',
                    'title' => $title,
                    'message' => $message,
                    'data' => [
                        'report_type' => 'user',
                        'report_id' => $report->id,
                        'reported_user_id' => $reportedUser->id,
                        'label' => $report->label,
                        'url' => route('item-reports.index', ['tab' => 'users', 'status' => 'pending'], false),
                    ],
                    'is_read' => false,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to notify admin of user report: '.$e->getMessage(), [
                    'admin_id' => $admin->id,
                    'report_id' => $report->id,
                ]);
            }
        }

        Log::info('Admins notified of user report', [
            'report_id' => $report->id,
            'admin_count' => $admins->count(),
        ]);
    }

    /**
     * Notify all admins that a listing was reported.
     */
    public static function notifyAdminsOfItemReport(ItemReport $report): void
    {
        $admins = User::where('role', 'admin')->get();
        if ($admins->isEmpty()) {
            return;
        }

        $reporterName = optional($report->reporter)->name ?? 'A user';
        $title = 'Listing reported';
        $message = $reporterName.' reported a listing ('.$report->labelName().'). Review in Reported Items.';

        foreach ($admins as $admin) {
            try {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'admin_item_reported',
                    'title' => $title,
                    'message' => $message,
                    'data' => [
                        'report_type' => 'item',
                        'report_id' => $report->id,
                        'upload_id' => $report->upload_id,
                        'label' => $report->label,
                        'url' => route('item-reports.index', ['tab' => 'items', 'status' => 'pending'], false),
                    ],
                    'is_read' => false,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to notify admin of item report: '.$e->getMessage(), [
                    'admin_id' => $admin->id,
                    'report_id' => $report->id,
                ]);
            }
        }

        Log::info('Admins notified of item report', [
            'report_id' => $report->id,
            'admin_count' => $admins->count(),
        ]);
    }
}
