<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contributor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'role',
        'bio',
        'avatar_path',
        'email',
        'github',
        'linkedin',
        'twitter',
        'website',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];
}
