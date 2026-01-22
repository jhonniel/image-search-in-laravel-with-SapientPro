<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Message;
use App\Models\MessageImageView;
use App\Models\User;
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
        
        // Get item context if itemId is provided
        // BUT only if the item is not verified/claimed
        $itemContextData = null;
        if ($itemId) {
            $items = \App\Models\ImageMetadata::where('upload_id', $itemId)->get();
            if ($items->count() > 0) {
                $firstItem = $items->first();
                
                // Don't return context if item is verified/claimed
                if (!($firstItem->is_claimed && $firstItem->claim_verification_status === 'verified')) {
                    $uploader = User::where('email', $firstItem->uploader_email)->first();
                    
                    $images = $items->map(function ($item) {
                        $filePath = $item->file_path;
                        if (str_starts_with($filePath, '/storage/')) {
                            $filePath = substr($filePath, 9);
                        }
                        return [
                            'path' => \Illuminate\Support\Facades\Storage::url($filePath),
                            'original_name' => $item->original_name ?? basename($filePath),
                            'filename' => $item->filename
                        ];
                    })->toArray();
                    
                    $claimer = $firstItem->claimed_by_email ? User::where('email', $firstItem->claimed_by_email)->first() : null;
                    
                    $itemContextData = [
                        'upload_id' => $firstItem->upload_id,
                        'uploadId' => $firstItem->upload_id,
                        'item_type' => $firstItem->status,
                        'itemType' => $firstItem->status,
                        'description' => $firstItem->description,
                        'location' => $firstItem->location ?? 'Location not specified',
                        'tags' => $firstItem->tags ? (is_string($firstItem->tags) ? json_decode($firstItem->tags, true) : $firstItem->tags) : [],
                        'uploader_name' => $uploader ? $uploader->name : 'Unknown User',
                        'uploader_email' => $firstItem->uploader_email,
                        'images' => $images,
                        'claim_status' => $firstItem->claim_verification_status,
                        'claimed_by_id' => $claimer ? $claimer->id : null,
                    ];
                }
            }
        }

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

        // Get item context from the first message that has item context
        // Show item context based on claim - always show if there's a claim, even if verified
        $itemContext = null;
        $firstMessageWithContext = $messages->where('item_upload_id', '!=', null)->whereNotNull('item_context')->first();
        if ($firstMessageWithContext && isset($firstMessageWithContext['item_context']) && $firstMessageWithContext['item_context']) {
            $decodedContext = json_decode($firstMessageWithContext['item_context'], true);
            
            // If decoding failed, try to get from database
            if (!$decodedContext && isset($firstMessageWithContext['item_upload_id']) && $firstMessageWithContext['item_upload_id']) {
                $itemId = $firstMessageWithContext['item_upload_id'];
                $items = \App\Models\ImageMetadata::where('upload_id', $itemId)->get();
                if ($items->count() > 0) {
                    $firstItem = $items->first();
                    $uploader = User::where('email', $firstItem->uploader_email)->first();
                    $claimer = $firstItem->claimed_by_email ? User::where('email', $firstItem->claimed_by_email)->first() : null;
                    
                    $images = $items->map(function ($item) {
                        $filePath = $item->file_path;
                        if (str_starts_with($filePath, '/storage/')) {
                            $filePath = substr($filePath, 9);
                        }
                        return [
                            'path' => \Illuminate\Support\Facades\Storage::url($filePath),
                            'original_name' => $item->original_name ?? basename($filePath),
                            'filename' => $item->filename
                        ];
                    })->toArray();
                    
                    $decodedContext = [
                        'upload_id' => $firstItem->upload_id,
                        'uploadId' => $firstItem->upload_id,
                        'description' => $firstItem->description,
                        'location' => $firstItem->location ?? 'Location not specified',
                        'item_type' => $firstItem->status,
                        'itemType' => $firstItem->status,
                        'status' => $firstItem->status,
                        'tags' => $firstItem->tags ? (is_string($firstItem->tags) ? json_decode($firstItem->tags, true) : $firstItem->tags) : [],
                        'uploader_name' => $uploader ? $uploader->name : 'Unknown User',
                        'uploader_email' => $firstItem->uploader_email,
                        'images' => $images,
                        'claim_status' => $firstItem->claim_verification_status ?? null,
                        'claimed_by_id' => $claimer ? $claimer->id : null,
                        'created_at' => $firstItem->created_at->toIso8601String(),
                    ];
                }
            }
            
            // Always return context if it exists (show based on claim, even if verified)
            if ($decodedContext && (isset($decodedContext['upload_id']) || isset($decodedContext['uploadId']))) {
                $itemId = $decodedContext['upload_id'] ?? $decodedContext['uploadId'];
                $item = \App\Models\ImageMetadata::where('upload_id', $itemId)->first();
                
                if ($item) {
                    $itemContext = $decodedContext;
                    // Always update claim status from database (get latest status)
                    $itemContext['claim_status'] = $item->claim_verification_status ?? null;
                    $itemContext['is_claimed'] = $item->is_claimed ?? false;
                    $itemContext['claimed_by_id'] = $item->claimed_by_email ? (User::where('email', $item->claimed_by_email)->first()?->id ?? null) : null;
                    
                    // Ensure all required fields are present for display
                    if (!isset($itemContext['images']) || empty($itemContext['images'])) {
                        $items = \App\Models\ImageMetadata::where('upload_id', $itemId)->get();
                        $images = $items->map(function ($item) {
                            $filePath = $item->file_path;
                            if (str_starts_with($filePath, '/storage/')) {
                                $filePath = substr($filePath, 9);
                            }
                            return [
                                'path' => \Illuminate\Support\Facades\Storage::url($filePath),
                                'original_name' => $item->original_name ?? basename($filePath),
                                'filename' => $item->filename
                            ];
                        })->toArray();
                        $itemContext['images'] = $images;
                    }
                    if (!isset($itemContext['item_type']) && isset($itemContext['status'])) {
                        $itemContext['item_type'] = $itemContext['status'];
                        $itemContext['itemType'] = $itemContext['status'];
                    }
                    if (!isset($itemContext['uploader_name']) && isset($itemContext['uploader_email'])) {
                        $uploader = User::where('email', $itemContext['uploader_email'])->first();
                        $itemContext['uploader_name'] = $uploader ? $uploader->name : 'Unknown User';
                    }
                }
            } elseif ($decodedContext) {
                // If no upload_id but we have context, return it (might be old format)
                $itemContext = $decodedContext;
            }
        } elseif ($request->has('item_id')) {
            // Fallback: get item context from request parameter
            $itemId = $request->get('item_id');

            // Find all items with this upload_id
            $items = \App\Models\ImageMetadata::where('upload_id', $itemId)->get();
            if ($items->count() > 0) {
                $firstItem = $items->first();
                
                // Don't return context if item is verified/claimed
                if ($firstItem->is_claimed && $firstItem->claim_verification_status === 'verified') {
                    $itemContext = null; // Item is verified, don't show context
                } else {
                    // Get the uploader's user information
                    $uploader = User::where('email', $firstItem->uploader_email)->first();

                    // Get all images for this item
                    $images = $items->map(function ($item) {
                        $filePath = $item->file_path;
                        if (str_starts_with($filePath, '/storage/')) {
                            $filePath = substr($filePath, 9);
                        }
                        return [
                            'path' => \Illuminate\Support\Facades\Storage::url($filePath),
                            'original_name' => $item->original_name ?? basename($filePath),
                            'filename' => $item->filename
                        ];
                    })->toArray();

                    $itemContext = [
                        'upload_id' => $firstItem->upload_id,
                        'item_type' => $firstItem->status,
                        'description' => $firstItem->description,
                        'location' => $firstItem->location ?? 'Location not specified',
                        'tags' => $firstItem->tags ? (is_string($firstItem->tags) ? json_decode($firstItem->tags, true) : $firstItem->tags) : [],
                        'uploader_name' => $uploader ? $uploader->name : 'Unknown User',
                        'uploader_email' => $firstItem->uploader_email,
                        'uploader_verified' => $uploader ? ($uploader->is_verified ?? false) : false,
                        'created_at' => $firstItem->created_at->toIso8601String(),
                        'images' => $images,
                        'claim_status' => $firstItem->claim_verification_status,
                        'claimed_by_id' => $firstItem->claimed_by_email ? (User::where('email', $firstItem->claimed_by_email)->first()?->id ?? null) : null,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'other_user' => $otherUser,
            'item_context' => $itemContext
        ]);
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
