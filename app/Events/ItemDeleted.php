<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $uploadId;
    public $ownerId;

    /**
     * Create a new event instance.
     */
    public function __construct($uploadId, $ownerId)
    {
        $this->uploadId = $uploadId;
        $this->ownerId = $ownerId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        // Broadcast to item owner and all users who might have this item in their chat
        // We'll broadcast to a public channel that all authenticated users can listen to
        return [
            new PrivateChannel('user.' . $this->ownerId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'item.deleted';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'upload_id' => $this->uploadId,
            'owner_id' => $this->ownerId,
        ];
    }
}
