<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ImageMetadata extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'image_metadata';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'filename',
        'file_path',
        'original_name',
        'description',
        'tags',
        'upload_id',
        'file_size',
        'mime_type',
        'uploader_email',
        'status',
        'is_claimed',
        'claimed_by_email',
        'claimed_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tags' => 'array',
        'is_claimed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    /**
     * Get the formatted file size.
     */
    protected function formattedFileSize(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->file_size) {
                    return null;
                }

                $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                $bytes = $this->file_size;

                for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                    $bytes /= 1024;
                }

                return round($bytes, 2) . ' ' . $units[$i];
            }
        );
    }

    /**
     * Get the full path to the image.
     */
    protected function fullPath(): Attribute
    {
        return Attribute::make(
            get: fn () => 'storage/reference-images/' . $this->filename
        );
    }

    /**
     * Scope to search by tags.
     */
    public function scopeByTags($query, array $tags)
    {
        return $query->whereJsonContains('tags', $tags);
    }

    /**
     * Scope to search by description.
     */
    public function scopeByDescription($query, string $search)
    {
        return $query->where('description', 'like', '%' . $search . '%');
    }

    /**
     * Scope to search by original name.
     */
    public function scopeByOriginalName($query, string $search)
    {
        return $query->where('original_name', 'like', '%' . $search . '%');
    }

    /**
     * Scope to filter by status (lost or found).
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by claimed status.
     */
    public function scopeClaimed($query)
    {
        return $query->where('is_claimed', true);
    }

    /**
     * Scope to filter by unclaimed status.
     */
    public function scopeUnclaimed($query)
    {
        return $query->where('is_claimed', false);
    }
}
