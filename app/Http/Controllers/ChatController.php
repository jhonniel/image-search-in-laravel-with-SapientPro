<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\ImageMetadata;
use App\Models\Message;
use App\Models\MessageImageView;
use App\Models\User;
use App\Models\UserReport;
use App\Events\MessageSent as MessageSentEvent;

class ChatController extends Controller
{
    /**
     * Show chat interface
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get recent conversations (only users with initiated conversations)
        $conversations = $this->getRecentConversations($user);
        
        // Debug: Log conversation count
        \Log::info('Chat conversations count: ' . $conversations->count());

        // Check if we have URL parameters for pre-selecting a user
        $selectedUserId = $request->get('user');
        $itemId = $request->get('item');
        
        // Always show item context when item parameter is provided (claim / message-about-item redirect)
        $itemContextData = $itemId ? $this->buildItemContextFromUploadId((string) $itemId) : null;

        return view('user.chat', compact('user', 'conversations', 'selectedUserId', 'itemId', 'itemContextData'));
    }

    /**
     * Get messages between two users
     */
    public function getMessages(Request $request, $userId)
    {
        $currentUser = Auth::user();

        // Validate that the user exists
        $otherUser = User::findOrFail($userId);

        // Get messages between current user and selected user
        $messages = Message::where(function($query) use ($currentUser, $otherUser) {
            $query->where('sender_id', $currentUser->id)
                  ->where('receiver_id', $otherUser->id);
        })->orWhere(function($query) use ($currentUser, $otherUser) {
            $query->where('sender_id', $otherUser->id)
                  ->where('receiver_id', $currentUser->id);
        })
        ->with(['sender', 'receiver'])
        ->orderBy('created_at', 'asc')
        ->get();
        
        // Map messages to include image data with proper date formatting
        $messages = $messages->map(function ($message) use ($currentUser) {
            $messageArray = $message->toArray();
            $messageArray['image_path'] = $message->image_path ? Storage::url($message->image_path) : null;
            $messageArray['can_view_image'] = $message->canViewImage($currentUser->id);
            
            // Ensure proper date formatting
            $messageArray['created_at'] = $message->created_at ? $message->created_at->toIso8601String() : now()->toIso8601String();
            $messageArray['read_at'] = $message->read_at ? $message->read_at->toIso8601String() : null;

            // Decode item_context so the frontend always gets an object
            if (! empty($messageArray['item_context']) && is_string($messageArray['item_context'])) {
                $decoded = json_decode($messageArray['item_context'], true);
                if (is_array($decoded)) {
                    $messageArray['item_context'] = $decoded;
                }
            }
            
            // Ensure sender and receiver are properly formatted
            if (isset($messageArray['sender']) && is_array($messageArray['sender'])) {
                // Already formatted
            } elseif ($message->sender) {
                $messageArray['sender'] = [
                    'id' => $message->sender->id,
                    'name' => $message->sender->name,
                    'email' => $message->sender->email,
                    'profile_picture' => $message->sender->profile_picture,
                ];
            }
            
            if (isset($messageArray['receiver']) && is_array($messageArray['receiver'])) {
                // Already formatted
            } elseif ($message->receiver) {
                $messageArray['receiver'] = [
                    'id' => $message->receiver->id,
                    'name' => $message->receiver->name,
                    'email' => $message->receiver->email,
                    'profile_picture' => $message->receiver->profile_picture,
                ];
            }
            
            return $messageArray;
        })->values();

        // Mark messages as read
        Message::where('sender_id', $otherUser->id)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $itemContext = $this->resolveConversationItemContext(
            $request,
            $currentUser,
            $otherUser,
            $messages
        );

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'other_user' => $otherUser,
            'item_context' => $itemContext,
        ]);
    }

    /**
     * Resolve item details for a chat conversation (claim redirect, claimed items, or message context).
     */
    private function resolveConversationItemContext(Request $request, User $currentUser, User $otherUser, $mappedMessages): ?array
    {
        // Priority 1: explicit item from URL / query (claim or message-about-item redirect)
        $itemId = $request->query('item') ?? $request->query('item_id') ?? $request->input('item') ?? $request->input('item_id');
        if ($itemId) {
            $context = $this->buildItemContextFromUploadId((string) $itemId);
            if ($context) {
                return $context;
            }
        }

        // Priority 2: most recent claim between these two users (case-insensitive emails for SQLite)
        $currentEmail = strtolower((string) $currentUser->email);
        $otherEmail = strtolower((string) $otherUser->email);

        $recentClaimedItem = ImageMetadata::query()
            ->whereNotNull('claimed_by_email')
            ->whereNotNull('claim_verification_status')
            ->where(function ($query) use ($currentEmail, $otherEmail) {
                $query->where(function ($q) use ($currentEmail, $otherEmail) {
                    $q->whereRaw('LOWER(uploader_email) = ?', [$otherEmail])
                        ->whereRaw('LOWER(claimed_by_email) = ?', [$currentEmail]);
                })->orWhere(function ($q) use ($currentEmail, $otherEmail) {
                    $q->whereRaw('LOWER(uploader_email) = ?', [$currentEmail])
                        ->whereRaw('LOWER(claimed_by_email) = ?', [$otherEmail]);
                });
            })
            ->orderByDesc('claimed_at')
            ->first();

        if ($recentClaimedItem) {
            $context = $this->buildItemContextFromUploadId((string) $recentClaimedItem->upload_id);
            if ($context) {
                return $context;
            }
        }

        // Priority 3: latest message in this thread that references an item
        $messageWithItem = collect($mappedMessages)
            ->reverse()
            ->first(function ($message) {
                $uploadId = $message['item_upload_id'] ?? null;
                $ctx = $message['item_context'] ?? null;

                return ! empty($uploadId) || ! empty($ctx);
            });

        if ($messageWithItem) {
            $uploadId = $messageWithItem['item_upload_id']
                ?? (is_array($messageWithItem['item_context'] ?? null) ? ($messageWithItem['item_context']['upload_id'] ?? $messageWithItem['item_context']['uploadId'] ?? null) : null);

            if ($uploadId) {
                $context = $this->buildItemContextFromUploadId((string) $uploadId);
                if ($context) {
                    return $context;
                }
            }

            if (is_array($messageWithItem['item_context'] ?? null)) {
                return $messageWithItem['item_context'];
            }

            if (is_string($messageWithItem['item_context'] ?? null)) {
                $decoded = json_decode($messageWithItem['item_context'], true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return null;
    }

    /**
     * Build a full item-context payload from an upload_id.
     */
    private function buildItemContextFromUploadId(string $uploadId): ?array
    {
        $items = ImageMetadata::where('upload_id', $uploadId)->get();
        if ($items->isEmpty()) {
            return null;
        }

        $firstItem = $items->first();
        $uploader = User::whereRaw('LOWER(email) = ?', [strtolower((string) $firstItem->uploader_email)])->first();
        $claimer = $firstItem->claimed_by_email
            ? User::whereRaw('LOWER(email) = ?', [strtolower((string) $firstItem->claimed_by_email)])->first()
            : null;

        $images = $items->map(function ($item) {
            return $this->normalizeItemImage($item);
        })->filter()->values()->all();

        $tags = $firstItem->tags
            ? (is_string($firstItem->tags) ? (json_decode($firstItem->tags, true) ?: []) : (array) $firstItem->tags)
            : [];

        return [
            'upload_id' => $firstItem->upload_id,
            'uploadId' => $firstItem->upload_id,
            'description' => $firstItem->description,
            'location' => $firstItem->location ?? 'Location not specified',
            'item_type' => $firstItem->status,
            'itemType' => $firstItem->status,
            'status' => $firstItem->status,
            'tags' => $tags,
            'uploader_name' => $uploader->name ?? 'Unknown User',
            'uploader_email' => $firstItem->uploader_email,
            'images' => $images,
            'claim_status' => $firstItem->claim_verification_status,
            'is_claimed' => (bool) ($firstItem->is_claimed ?? false),
            'claimed_by_email' => $firstItem->claimed_by_email,
            'claimed_by_id' => $claimer->id ?? null,
            'claimed_by_name' => $claimer->name ?? null,
            'claimed_at' => $firstItem->claimed_at ? $firstItem->claimed_at->toIso8601String() : null,
        ];
    }

    private function normalizeItemImage(ImageMetadata $item): ?array
    {
        $filePath = (string) ($item->file_path ?? '');
        if ($filePath === '') {
            return null;
        }

        if (str_starts_with($filePath, '/storage/')) {
            $path = $filePath;
        } elseif (str_starts_with($filePath, 'storage/')) {
            $path = '/'.$filePath;
        } elseif (str_starts_with($filePath, 'http')) {
            $path = $filePath;
        } else {
            $path = Storage::url(ltrim(str_replace('/storage/', '', $filePath), '/'));
        }

        return [
            'path' => $path,
            'original_name' => $item->original_name ?? basename($filePath),
            'filename' => $item->filename,
        ];
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:1000',
            'item_upload_id' => 'nullable|string',
            'item_context' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:10240',
            'view_option' => 'nullable|in:once,twice,keep',
        ]);

        $currentUser = Auth::user();

        // Prevent sending message to self
        if ($request->receiver_id == $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot send message to yourself'
            ], 422);
        }

        // Require either message or image
        $hasMessage = $request->filled('message') && trim($request->message) !== '';
        $hasImage = $request->hasFile('image');
        
        if (!$hasMessage && !$hasImage) {
            return response()->json([
                'success' => false,
                'message' => 'Either message text or image is required'
            ], 422);
        }

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '_' . uniqid() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('chat-images', $filename, 'public');
            }

            $message = Message::create([
                'sender_id' => $currentUser->id,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message ?? '',
                'item_upload_id' => $request->item_upload_id,
                'item_context' => $request->item_context,
                'image_path' => $imagePath,
                'view_option' => $request->view_option ?? null,
                'view_count' => 0,
                'is_expired' => false,
            ]);

            $message->load(['sender', 'receiver']);

            // Broadcast the message event for real-time updates (to others, not to sender)
            broadcast(new MessageSentEvent($message))->toOthers();

            // Return message with proper serialization
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'receiver_id' => $message->receiver_id,
                    'message' => $message->message,
                    'item_upload_id' => $message->item_upload_id,
                    'item_context' => $message->item_context,
                    'image_path' => $message->image_path ? Storage::url($message->image_path) : null,
                    'view_option' => $message->view_option,
                    'view_count' => $message->view_count,
                    'is_expired' => $message->is_expired,
                    'can_view_image' => $message->canViewImage($currentUser->id),
                    'is_read' => $message->is_read,
                    'read_at' => $message->read_at ? $message->read_at->toIso8601String() : null,
                    'created_at' => $message->created_at->toIso8601String(),
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                        'email' => $message->sender->email,
                        'profile_picture' => $message->sender->profile_picture,
                    ],
                    'receiver' => [
                        'id' => $message->receiver->id,
                        'name' => $message->receiver->name,
                        'email' => $message->receiver->email,
                        'profile_picture' => $message->receiver->profile_picture,
                    ],
                ],
                'message_text' => 'Message sent successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent conversations
     */
    public function getRecentConversations($user)
    {
        // Get all messages where user is sender or receiver
        $messages = Message::where(function($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('receiver_id', $user->id);
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get();

        // If no messages, return empty collection
        if ($messages->isEmpty()) {
            return collect([]);
        }

        // Group messages by the other user (not the current user)
        $conversationUsers = $messages->groupBy(function($message) use ($user) {
            if ($message->sender_id == $user->id) {
                return $message->receiver_id;
            } else {
                return $message->sender_id;
            }
        });

        $conversations = [];

        foreach ($conversationUsers as $otherUserId => $messageGroup) {
            // Get the other user from the first message
            $firstMessage = $messageGroup->first();
            
            if (!$firstMessage) {
                continue;
            }
            
            // Ensure relationships are loaded
            if (!$firstMessage->relationLoaded('sender') || !$firstMessage->relationLoaded('receiver')) {
                $firstMessage->load(['sender', 'receiver']);
            }
            
            $otherUser = $firstMessage->sender_id == $user->id
                ? $firstMessage->receiver
                : $firstMessage->sender;
            
            // Skip if user doesn't exist (might be deleted)
            if (!$otherUser) {
                continue;
            }

            // Get the most recent message
            $lastMessage = $messageGroup->sortByDesc('created_at')->first();
            
            // Ensure last message has relationships loaded
            if ($lastMessage && (!$lastMessage->relationLoaded('sender') || !$lastMessage->relationLoaded('receiver'))) {
                $lastMessage->load(['sender', 'receiver']);
            }
            
            if (!$lastMessage) {
                continue;
            }
            
            // Count unread messages (messages sent TO the current user that are unread)
            $unreadCount = $messageGroup->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();

            $conversations[] = [
                'user' => $otherUser,
                'last_message' => $lastMessage,
                'unread_count' => $unreadCount,
                'last_message_time' => $lastMessage->created_at
            ];
        }

        // Sort by last message time (most recent first)
        usort($conversations, function($a, $b) {
            return $b['last_message_time'] <=> $a['last_message_time'];
        });

        return collect($conversations);
    }

    /**
     * Get unread messages count
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        $unreadCount = $user->unreadMessagesCount();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request, $userId)
    {
        $currentUser = Auth::user();

        Message::where('sender_id', $userId)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read'
        ]);
    }

    /**
     * Get user by email
     */
    public function getUserByEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_picture' => $user->profile_picture
            ]
        ]);
    }

    /**
     * Report another user (e.g. false claimer) from chat.
     */
    public function reportUser(Request $request)
    {
        $reporter = Auth::user();
        if (! $reporter) {
            return response()->json([
                'success' => false,
                'error' => 'User not authenticated',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'reported_user_id' => 'required|integer|exists:users,id',
            'upload_id' => 'nullable|string|max:100',
            'label' => 'required|string|in:'.implode(',', array_keys(UserReport::LABELS)),
            'explanation' => 'required|string|min:10|max:2000',
        ], [
            'reported_user_id.required' => 'Please select the user to report.',
            'label.required' => 'Please select a report reason.',
            'label.in' => 'Invalid report reason selected.',
            'explanation.required' => 'Please explain why you are reporting this user.',
            'explanation.min' => 'Explanation must be at least 10 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $reportedUserId = (int) $request->input('reported_user_id');
        if ($reportedUserId === (int) $reporter->id) {
            return response()->json([
                'success' => false,
                'error' => 'You cannot report yourself.',
            ], 403);
        }

        $reportedUser = User::find($reportedUserId);
        if (! $reportedUser) {
            return response()->json([
                'success' => false,
                'error' => 'User not found.',
            ], 404);
        }

        $uploadId = $request->filled('upload_id') ? trim((string) $request->input('upload_id')) : null;
        if ($uploadId === '') {
            $uploadId = null;
        }

        if ($uploadId !== null) {
            $itemExists = ImageMetadata::withTrashed()->where('upload_id', $uploadId)->exists();
            if (! $itemExists) {
                return response()->json([
                    'success' => false,
                    'error' => 'Related item not found.',
                ], 404);
            }
        }

        $existingQuery = UserReport::where('reporter_user_id', $reporter->id)
            ->where('reported_user_id', $reportedUserId);

        if ($uploadId === null) {
            $existingQuery->whereNull('upload_id');
        } else {
            $existingQuery->where('upload_id', $uploadId);
        }

        if ($existingQuery->exists()) {
            return response()->json([
                'success' => false,
                'error' => 'You have already reported this user for this item.',
            ], 409);
        }

        try {
            $report = UserReport::create([
                'reporter_user_id' => $reporter->id,
                'reported_user_id' => $reportedUserId,
                'upload_id' => $uploadId,
                'label' => $request->input('label'),
                'explanation' => $request->input('explanation'),
                'status' => 'pending',
            ]);

            Log::info('User reported from chat', [
                'report_id' => $report->id,
                'reporter_user_id' => $reporter->id,
                'reported_user_id' => $reportedUserId,
                'upload_id' => $uploadId,
                'label' => $report->label,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you. Your report has been submitted for admin review.',
                'report' => [
                    'id' => $report->id,
                    'label' => $report->label,
                    'label_name' => $report->labelName(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to report user: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to submit report. Please try again.',
            ], 500);
        }
    }

    /**
     * Record image view
     */
    public function recordImageView(Request $request, $messageId)
    {
        $currentUser = Auth::user();
        $message = Message::findOrFail($messageId);

        // Check if user can view the image
        if (!$message->canViewImage($currentUser->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Image view limit reached or image expired'
            ], 403);
        }

        // Record the view
        $message->recordImageView($currentUser->id);

        return response()->json([
            'success' => true,
            'view_count' => $message->fresh()->view_count,
            'is_expired' => $message->fresh()->is_expired,
            'can_view_image' => $message->fresh()->canViewImage($currentUser->id)
        ]);
    }
}
