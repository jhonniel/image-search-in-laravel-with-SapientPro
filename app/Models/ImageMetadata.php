<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class ImageMetadata extends Model
{
    use SoftDeletes;
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
        'location',
        'province',
        'city',
        'tags',
        'detected_objects',
        'upload_id',
        'file_size',
        'mime_type',
        'uploader_email',
        'user_id',
        'status',
        'is_claimed',
        'claimed_by_email',
        'claimed_at',
        'claim_verification_status',
        'claim_verified_at',
        'images_purged_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tags' => 'array',
        'detected_objects' => 'array',
        'is_claimed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'claimed_at' => 'datetime',
        'claim_verified_at' => 'datetime',
        'images_purged_at' => 'datetime',
        'deleted_at' => 'datetime',
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
     * Rows that still need Google Vision labels (null, empty array, or JSON null).
     * Works with SQLite, PostgreSQL, and MySQL.
     */
    public function scopeMissingDetectedObjects($query)
    {
        $driver = $query->getConnection()->getDriverName();

        return $query->where(function ($q) use ($driver) {
            $q->whereNull('detected_objects');

            if ($driver === 'pgsql') {
                $q->orWhereRaw("detected_objects::text = '[]'")
                    ->orWhereRaw("detected_objects::text = 'null'");
            } elseif ($driver === 'sqlite') {
                $q->orWhere('detected_objects', '[]')
                    ->orWhere('detected_objects', 'null');
            } else {
                $q->orWhereJsonLength('detected_objects', 0);
            }
        });
    }

    /**
     * Scope to search by tags.
     * Works with SQLite, PostgreSQL, and MySQL
     */
    public function scopeByTags($query, array $tags)
    {
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        
        // For PostgreSQL and MySQL, use whereJsonContains
        if (in_array($driver, ['pgsql', 'mysql'])) {
            // For array of tags, check if any tag is contained
            if (count($tags) === 1) {
                return $query->whereJsonContains('tags', $tags[0]);
            } else {
                // For multiple tags, use OR condition
                return $query->where(function($q) use ($tags) {
                    foreach ($tags as $tag) {
                        $q->orWhereJsonContains('tags', $tag);
                    }
                });
            }
        } else {
            // For SQLite, use LIKE search as fallback
            return $query->where(function($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhere('tags', 'like', '%' . $tag . '%');
                }
            });
        }
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

    /**
     * Scope: items owned by the given user.
     *
     * Matches by user_id when present (preferred, stable across email changes),
     * and falls back to uploader_email so legacy rows without a user_id still appear.
     */
    public function scopeOwnedBy($query, ?User $user)
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($user): void {
            $q->where('user_id', $user->id)
                ->orWhere('uploader_email', $user->email);
        });
    }

    /**
     * Scope: items NOT owned by the given user.
     */
    public function scopeNotOwnedBy($query, ?User $user)
    {
        if (! $user) {
            return $query;
        }

        return $query->where(function ($q) use ($user): void {
            $q->whereNull('user_id')
                ->orWhere('user_id', '!=', $user->id);
        })->where(function ($q) use ($user): void {
            $q->whereNull('uploader_email')
                ->orWhere('uploader_email', '!=', $user->email);
        });
    }

    /**
     * Scope: items still available for matching / public user listings
     * (exclude verified claims whose images were archived for admin-only audit).
     */
    public function scopeAvailableForUsers($query)
    {
        return $query->where(function ($q) {
            $q->where('is_claimed', false)
                ->orWhereNull('is_claimed')
                ->orWhere(function ($inner) {
                    $inner->where('is_claimed', true)
                        ->where('claim_verification_status', '!=', 'verified');
                });
        });
    }

    /**
     * Whether this row is a verified claim archived for admin audit.
     */
    public function isClaimArchived(): bool
    {
        return (bool) $this->is_claimed
            && $this->claim_verification_status === 'verified';
    }

    /**
     * Delete stored image files for a verified claim, keep metadata for admin audit/counts.
     * Clears file_path so users no longer receive image URLs.
     */
    public static function purgeImagesForUpload(string $uploadId): int
    {
        $items = static::where('upload_id', $uploadId)->get();
        if ($items->isEmpty()) {
            return 0;
        }

        $purged = 0;
        $now = now();

        foreach ($items as $item) {
            $relativePath = static::relativeStoragePath($item->file_path, $item->filename);

            if ($relativePath && Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
                $purged++;
            }

            // Keep filename / original_name for audit; clear path so images are not served
            $item->file_path = null;
            $item->images_purged_at = $now;
            $item->save();
        }

        \Illuminate\Support\Facades\Log::info('Purged claim images for audit archive', [
            'upload_id' => $uploadId,
            'rows' => $items->count(),
            'files_deleted' => $purged,
        ]);

        return $purged;
    }

    /**
     * Resolve a public-disk relative path from stored file_path / filename.
     */
    public static function relativeStoragePath(?string $filePath, ?string $filename = null): ?string
    {
        if (! empty($filePath)) {
            $path = $filePath;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                $parsed = parse_url($path, PHP_URL_PATH);
                $path = is_string($parsed) ? $parsed : $path;
            }
            $path = ltrim(str_replace('/storage/', '', $path), '/');
            if (str_starts_with($path, 'storage/')) {
                $path = substr($path, 8);
            }

            return $path !== '' ? $path : null;
        }

        if (! empty($filename)) {
            if (Storage::disk('public')->exists('user-items/'.$filename)) {
                return 'user-items/'.$filename;
            }
            if (Storage::disk('public')->exists('reference-images/'.$filename)) {
                return 'reference-images/'.$filename;
            }
        }

        return null;
    }

    /**
     * Owning user (nullable for guest-uploaded items).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
