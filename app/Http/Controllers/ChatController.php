<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\User;

class ChatController extends Controller
{
    /**
     * Show chat interface
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get all users except current user
        $users = User::where('id', '!=', $user->id)
            ->orderBy('name')
            ->get();

        // Get recent conversations
        $conversations = $this->getRecentConversations($user);

        // Check if we have URL parameters for pre-selecting a user
        $selectedUserId = $request->get('user');
        $itemId = $request->get('item');

        return view('user.chat', compact('user', 'users', 'conversations', 'selectedUserId', 'itemId'));
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

        // Mark messages as read
        Message::where('sender_id', $otherUser->id)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        // Get item context from the first message that has item context
        $itemContext = null;
        $firstMessageWithContext = $messages->where('item_upload_id', '!=', null)->first();
        if ($firstMessageWithContext && $firstMessageWithContext->item_context) {
            $itemContext = json_decode($firstMessageWithContext->item_context, true);
        } elseif ($request->has('item_id')) {
            // Fallback: get item context from request parameter
            $itemId = $request->get('item_id');

            // Find the item in ImageMetadata
            $item = \App\Models\ImageMetadata::where('upload_id', $itemId)->first();
            if ($item) {
                // Get the uploader's user information
                $uploader = User::where('email', $item->uploader_email)->first();

                // Fix file path for images
                $filePath = $item->file_path;
                if (str_starts_with($filePath, '/storage/')) {
                    $filePath = substr($filePath, 9);
                }

                $itemContext = [
                    'upload_id' => $item->upload_id,
                    'item_type' => $item->status,
                    'description' => $item->description,
                    'location' => $item->location,
                    'tags' => $item->tags ? (is_string($item->tags) ? json_decode($item->tags, true) : $item->tags) : [],
                    'uploader_name' => $uploader ? $uploader->name : 'Unknown User',
                    'uploader_email' => $item->uploader_email,
                    'created_at' => $item->created_at,
                    'images' => [
                        [
                            'path' => \Illuminate\Support\Facades\Storage::url($filePath),
                            'original_name' => basename($filePath)
                        ]
                    ]
                ];
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
            'message' => 'required|string|max:1000',
            'item_upload_id' => 'nullable|string',
            'item_context' => 'nullable|string',
        ]);

        $currentUser = Auth::user();

        // Prevent sending message to self
        if ($request->receiver_id == $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot send message to yourself'
            ], 422);
        }

        try {
            $message = Message::create([
                'sender_id' => $currentUser->id,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
                'item_upload_id' => $request->item_upload_id,
                'item_context' => $request->item_context,
            ]);

            $message->load(['sender', 'receiver']);

            return response()->json([
                'success' => true,
                'message' => $message,
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
        // Get users with whom the current user has had conversations
        $messages = Message::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender', 'receiver'])
            ->get();

        $conversationUsers = $messages->groupBy(function($message) use ($user) {
            if ($message->sender_id == $user->id) {
                return $message->receiver_id;
            } else {
                return $message->sender_id;
            }
        });

        $conversations = [];

        foreach ($conversationUsers as $otherUserId => $messages) {
            $otherUser = $messages->first()->sender_id == $user->id
                ? $messages->first()->receiver
                : $messages->first()->sender;

            $lastMessage = $messages->sortByDesc('created_at')->first();
            $unreadCount = $messages->where('receiver_id', $user->id)
                ->where('is_read', false)
                ->count();

            $conversations[] = [
                'user' => $otherUser,
                'last_message' => $lastMessage,
                'unread_count' => $unreadCount,
                'last_message_time' => $lastMessage->created_at
            ];
        }

        // Sort by last message time
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
}
