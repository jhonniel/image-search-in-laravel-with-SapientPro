<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReport extends Model
{
    public const LABELS = [
        'false_claim' => 'False Claim',
        'scam_claimer' => 'Scam Claimer',
        'impersonation' => 'Impersonation',
        'harassment' => 'Harassment',
        'other' => 'Other',
    ];

    protected $fillable = [
        'reporter_user_id',
        'reported_user_id',
        'upload_id',
        'label',
        'explanation',
        'status',
        'appeal_message',
        'appealed_at',
    ];

    protected $casts = [
        'appealed_at' => 'datetime',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function labelName(): string
    {
        return self::LABELS[$this->label] ?? ucfirst(str_replace('_', ' ', $this->label));
    }

    public function hasAppeal(): bool
    {
        return $this->appealed_at !== null && filled($this->appeal_message);
    }
}
