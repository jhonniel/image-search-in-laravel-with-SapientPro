<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sponsor extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'image_path',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get active sponsors ordered by order field
     */
    public static function getActive()
    {
        return static::where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'asc')
            ->get(); // Soft deletes automatically excludes deleted records
    }
}
