<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemClaimVerified implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $uploadId;
    public $claimerId;
    public $ownerId;

    /**
     * Create a new event instance.
     */
    public function __construct($uploadId, $claimerId, $ownerId)
    {
        $this->uploadId = $uploadId;
        $this->claimerId = $claimerId;
        $this->ownerId = $ownerId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        // Broadcast to both claimer and owner
        return [
            new PrivateChannel('user.' . $this->claimerId),
            new PrivateChannel('user.' . $this->ownerId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'item.claim.verified';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'upload_id' => $this->uploadId,
            'claimer_id' => $this->claimerId,
            'owner_id' => $this->ownerId,
        ];
    }
}
