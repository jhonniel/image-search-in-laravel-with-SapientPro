<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactHelpSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'heading',
        'body',
        'cta_label',
        'cta_url',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

