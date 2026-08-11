<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemReport extends Model
{
    public const LABELS = [
        'scam' => 'Scam',
        'fake' => 'Fake / Misleading',
        'inappropriate' => 'Inappropriate Content',
        'spam' => 'Spam',
        'stolen' => 'Stolen Goods',
        'other' => 'Other',
    ];

    protected $fillable = [
        'reporter_user_id',
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

    public function labelName(): string
    {
        return self::LABELS[$this->label] ?? ucfirst($this->label);
    }

    public function hasAppeal(): bool
    {
        return $this->appealed_at !== null && filled($this->appeal_message);
    }
}
