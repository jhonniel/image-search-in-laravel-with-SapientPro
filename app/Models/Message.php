<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'item_upload_id',
        'item_context',
        'is_read',
        'read_at',
        'image_path',
        'view_option',
        'view_count',
        'is_expired',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
        'view_count' => 'integer',
        'is_expired' => 'boolean',
    ];

    /**
     * Get the sender of the message
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the message
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get image views for this message
     */
    public function imageViews(): HasMany
    {
        return $this->hasMany(MessageImageView::class);
    }

    /**
     * Check if image can be viewed by user
     */
    public function canViewImage($userId): bool
    {
        if (!$this->image_path || $this->is_expired) {
            return false;
        }

        // Sender can always view their own images
        if ($this->sender_id == $userId) {
            return true;
        }

        if ($this->view_option === 'keep') {
            return true;
        }

        if ($this->view_option === 'once') {
            return $this->view_count < 1;
        }

        if ($this->view_option === 'twice') {
            return $this->view_count < 2;
        }

        return false;
    }

    /**
     * Record image view
     */
    public function recordImageView($userId): void
    {
        // Don't record view if sender is viewing their own image
        if ($this->sender_id == $userId) {
            return;
        }

        // Check if user already viewed this image
        $existingView = MessageImageView::where('message_id', $this->id)
            ->where('viewer_id', $userId)
            ->first();

        if (!$existingView) {
            MessageImageView::create([
                'message_id' => $this->id,
                'viewer_id' => $userId,
                'viewed_at' => now(),
            ]);

            $this->increment('view_count');

            // Check if image should expire
            if ($this->view_option === 'once' && $this->view_count >= 1) {
                $this->update(['is_expired' => true]);
            } elseif ($this->view_option === 'twice' && $this->view_count >= 2) {
                $this->update(['is_expired' => true]);
            }
        }
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
