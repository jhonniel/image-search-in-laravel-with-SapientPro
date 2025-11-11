<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reward extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'code',
        'type',
        'value',
        'expires_at',
        'is_used',
        'used_at',
        'status',
        'is_auto_assign',
        'min_reports',
        'min_claims',
        'min_found_items',
        'min_lost_items',
        'rule_description',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
        'is_auto_assign' => 'boolean',
        'value' => 'decimal:2',
    ];

    /**
     * Get the user that owns the reward
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if reward is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    /**
     * Check if reward is available (not used and not expired)
     */
    public function isAvailable(): bool
    {
        return !$this->is_used && !$this->isExpired() && $this->status === 'active';
    }
}
