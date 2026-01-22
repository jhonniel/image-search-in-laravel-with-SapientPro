<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Cache duration for settings (5 minutes)
     */
    private static int $cacheDuration = 300;

    /**
     * Get a setting value by key with caching to prevent timeout
     */
    public static function get($key, $default = null)
    {
        // Use cache to avoid multiple database queries
        $cacheKey = "setting_{$key}";
        
        return Cache::remember($cacheKey, self::$cacheDuration, function () use ($key, $default) {
            try {
                // Check if settings table exists first
                if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                    return $default;
                }

                $setting = self::where('key', $key)->first();
                
                if (!$setting) {
                    return $default;
                }

                $value = match($setting->type) {
                    'boolean' => self::parseBoolean($setting->value),
                    'integer' => (int) $setting->value,
                    'json' => json_decode($setting->value, true),
                    default => $setting->value,
                };

                // For boolean type, if value is null after parsing, return default
                // Otherwise return the parsed value (even if it's false)
                if ($setting->type === 'boolean') {
                    if ($value === null) {
                        return $default;
                    }
                    // Return the actual boolean value (true or false), not the default
                    return $value;
                }

                return $value ?? $default;
            } catch (\Exception $e) {
                // Return default on any error to prevent timeout
                return $default;
            }
        });
    }

    /**
     * Set a setting value by key
     * Ensures boolean values are stored consistently across SQLite and PostgreSQL
     */
    public static function set($key, $value, $type = 'string', $description = null)
    {
        // Normalize boolean values to '1' or '0' strings for database compatibility
        if ($type === 'boolean' || is_bool($value)) {
            $value = $value ? '1' : '0';
            $type = 'boolean';
        } else {
            $value = (string) $value;
        }

        $result = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]
        );

        // Clear cache when setting is updated
        Cache::forget("setting_{$key}");

        return $result;
    }

    /**
     * Parse a boolean value from database, handling various formats
     * Works with both SQLite and PostgreSQL
     */
    private static function parseBoolean($value): ?bool
    {
        // Handle null or empty values
        if ($value === null || $value === '') {
            return null;
        }

        // Handle boolean types directly (PostgreSQL might return actual booleans)
        if (is_bool($value)) {
            return $value;
        }

        // Handle integer types (SQLite might return 0/1 as integers)
        if (is_int($value)) {
            return $value === 1;
        }

        // Convert to string and normalize
        $value = strtolower(trim((string) $value));
        
        // Explicit true values (handles '1', 'true', 'on', 'yes', 't', etc.)
        if (in_array($value, ['1', 'true', 'on', 'yes', 't', 'y'])) {
            return true;
        }
        
        // Explicit false values (handles '0', 'false', 'off', 'no', 'f', 'n', etc.)
        if (in_array($value, ['0', 'false', 'off', 'no', 'f', 'n'])) {
            return false;
        }

        // Fallback to filter_var for edge cases
        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $result;
    }
}
