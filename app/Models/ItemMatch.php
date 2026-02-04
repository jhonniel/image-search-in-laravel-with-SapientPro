<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemMatch extends Model
{
    protected $fillable = [
        'user_item_upload_id',
        'matched_item_upload_id',
        'user_email',
        'matched_item_owner_email',
        'user_item_status',
        'matched_item_status',
        'similarity_score',
        'visual_similarity',
        'text_similarity',
        'is_notified',
    ];

    protected $casts = [
        'similarity_score' => 'decimal:4',
        'visual_similarity' => 'decimal:4',
        'text_similarity' => 'decimal:4',
        'is_notified' => 'boolean',
    ];
}
