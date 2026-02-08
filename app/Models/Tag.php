<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'usage_count',
    ];

    protected $casts = [
        'usage_count' => 'integer',
    ];

    /**
     * Get tags ordered by usage count (most used first)
     */
    public static function getPopularTags($limit = 50)
    {
        return self::orderBy('usage_count', 'desc')
            ->orderBy('name', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Increment usage count
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }
}
