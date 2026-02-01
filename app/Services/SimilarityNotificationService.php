<?php

namespace App\Services;

use App\Models\ImageMetadata;
use App\Mail\SimilarImageNotification;
use App\Mail\UserItemNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use SapientPro\ImageComparator\ImageComparator;
use SapientPro\ImageComparator\Exceptions\ImageResourceException;

class SimilarityNotificationService
{
    private ImageComparator $imageComparator;
    private array $config;

    public function __construct(ImageComparator $imageComparator)
    {
        $this->imageComparator = $imageComparator;
        $this->config = config('similarity', []);
        
        // Don't apply mail configuration in constructor to avoid timeout during service resolution
        // Mail configuration will be applied lazily when needed
    }
    
    /**
     * Apply mail configuration from database settings
     * Made lazy to avoid timeout during service container resolution
     */
    private function applyMailConfigurationFromSettings(): void
    {
        try {
            // Check if database connection is available and settings table exists
            if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return;
            }
            
            // Check if email notifications are enabled
            $emailNotificationsEnabled = \App\Models\Setting::get('email_notifications', true);
            $similarityAlertsEnabled = \App\Models\Setting::get('similarity_alerts', true);
            
            // Only apply mail config if notifications are enabled
            if ($emailNotificationsEnabled && $similarityAlertsEnabled) {
                // Get mail settings from database
                $mailMailer = \App\Models\Setting::get('mail_mailer', env('MAIL_MAILER', 'log'));
                $mailHost = \App\Models\Setting::get('mail_host', env('MAIL_HOST'));
                $mailPort = \App\Models\Setting::get('mail_port', env('MAIL_PORT', 587));
                $mailUsername = \App\Models\Setting::get('mail_username', env('MAIL_USERNAME'));
                $mailPassword = \App\Models\Setting::get('mail_password', env('MAIL_PASSWORD'));
                $mailEncryption = \App\Models\Setting::get('mail_encryption', env('MAIL_ENCRYPTION', 'tls'));
                $mailFromAddress = \App\Models\Setting::get('mail_from_address', env('MAIL_FROM_ADDRESS'));
                $mailFromName = \App\Models\Setting::get('mail_from_name', env('MAIL_FROM_NAME'));
                
                // Update config dynamically
                if ($mailMailer && $mailMailer !== 'log') {
                    config([
                        'mail.default' => $mailMailer,
                        'mail.mailers.smtp.host' => $mailHost ?? config('mail.mailers.smtp.host'),
                        'mail.mailers.smtp.port' => $mailPort ?? config('mail.mailers.smtp.port'),
                        'mail.mailers.smtp.username' => $mailUsername ?? config('mail.mailers.smtp.username'),
                        'mail.mailers.smtp.password' => $mailPassword ?? config('mail.mailers.smtp.password'),
                        'mail.mailers.smtp.encryption' => $mailEncryption ?? config('mail.mailers.smtp.encryption'),
                        'mail.from.address' => $mailFromAddress ?? config('mail.from.address'),
                        'mail.from.name' => $mailFromName ?? config('mail.from.name'),
                    ]);
                    
                    // Reconfigure mailer if SMTP settings are available
                    if ($mailMailer === 'smtp' && $mailHost && $mailUsername && $mailPassword) {
                        \Illuminate\Support\Facades\Config::set('mail.default', 'smtp');
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail - don't log to avoid spam during bootstrap
            // Log::warning('Failed to apply mail configuration from settings: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if email notifications are enabled
     */
    private function areEmailNotificationsEnabled(): bool
    {
        try {
            // Check if database connection is available
            if (!\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return true; // Default to enabled if settings table doesn't exist
            }
            
            $emailNotificationsEnabled = \App\Models\Setting::get('email_notifications', true);
            $similarityAlertsEnabled = \App\Models\Setting::get('similarity_alerts', true);
            
            return $emailNotificationsEnabled && $similarityAlertsEnabled;
        } catch (\Exception $e) {
            // Default to enabled if settings can't be read (e.g., during migrations)
            return true;
        }
    }

    /**
     * Check for similar images and send notifications
     */
    public function checkAndNotifySimilarImages(string $newImagePath, array $newImageMetadata, ?string $newUploaderEmail = null): array
    {
        // Check if similarity checking is enabled
        if (!$this->config['enabled'] ?? true) {
            return [
                'similar_images_found' => 0,
                'notifications_sent' => 0,
                'emails_notified' => [],
                'similar_images' => [],
                'message' => 'Similarity checking is disabled'
            ];
        }

        $similarImages = [];
        $notificationsSent = [];

        try {
            // Get all existing images with metadata
            $existingImages = ImageMetadata::whereNotNull('uploader_email')
                ->where('uploader_email', '!=', $newUploaderEmail) // Don't notify the same user
                ->get();

            Log::info('Checking similarity for new image', [
                'new_image_path' => $newImagePath,
                'existing_images_count' => $existingImages->count(),
                'new_uploader_email' => $newUploaderEmail
            ]);

            foreach ($existingImages as $existingImage) {
                $existingImagePath = storage_path('app/public/reference-images/' . $existingImage->filename);

                if (!file_exists($existingImagePath)) {
                    Log::warning('Existing image file not found', ['path' => $existingImagePath]);
                    continue;
                }

                try {
                    // Calculate visual similarity
                    $visualSimilarity = $this->calculateVisualSimilarity($newImagePath, $existingImagePath);

                    // Calculate text similarity
                    $textSimilarity = $this->calculateTextSimilarity($newImageMetadata, $existingImage);

                    // Calculate overall similarity
                    $overallSimilarity = $this->calculateOverallSimilarity($visualSimilarity, $textSimilarity);

                    Log::debug('Similarity calculation', [
                        'existing_image' => $existingImage->original_name,
                        'visual_similarity' => $visualSimilarity,
                        'text_similarity' => $textSimilarity,
                        'overall_similarity' => $overallSimilarity
                    ]);

                    // Check if similarity meets threshold
                    $visualThreshold = $this->config['thresholds']['visual'] ?? 0.8; // Use config value, default to 0.8 for strict similarity

                    Log::debug('Threshold check', [
                        'existing_image' => $existingImage->original_name,
                        'overall_similarity' => $overallSimilarity,
                        'visual_threshold' => $visualThreshold,
                        'meets_threshold' => $overallSimilarity >= $visualThreshold
                    ]);

                    if ($overallSimilarity >= $visualThreshold) {
                        $similarImages[] = [
                            'image' => $existingImage,
                            'visual_similarity' => $visualSimilarity,
                            'text_similarity' => $textSimilarity,
                            'overall_similarity' => $overallSimilarity,
                            'path' => $existingImagePath
                        ];

                        Log::info('Similar image found', [
                            'existing_image' => $existingImage->original_name,
                            'similarity' => $overallSimilarity
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error calculating similarity for image: ' . $existingImage->original_name, [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    continue;
                }
            }

        // If there is ANY similarity (≥1), notify BOTH uploader and existing image owners
        if (count($similarImages) >= 1) {
            // Group similar images by uploader email
            $emailGroups = $this->groupSimilarImagesByEmail($similarImages);

            // Send notifications to existing image owners
            foreach ($emailGroups as $email => $images) {
                $this->sendBulkSimilarityNotification($email, $images, $newImageMetadata);
                $notificationsSent[] = $email;
            }

            // Also notify the new uploader if they provided an email
            if ($newUploaderEmail) {
                $this->sendNewUploaderNotification($newUploaderEmail, $similarImages, $newImageMetadata);
                $notificationsSent[] = $newUploaderEmail;
            }

            Log::info('Similar images found - notifying all parties', [
                'similar_images_count' => count($similarImages),
                'new_uploader_email' => $newUploaderEmail,
                'existing_owners_notified' => array_keys($emailGroups)
            ]);
        } else {
            // No similar images found - only notify the new uploader
            if ($newUploaderEmail) {
                $this->sendNoMatchNotification($newUploaderEmail, $newImageMetadata);
                $notificationsSent[] = $newUploaderEmail;
            }

            Log::info('No similar images found - only notifying new uploader', [
                'similar_images_count' => count($similarImages),
                'new_uploader_email' => $newUploaderEmail
            ]);
        }

            return [
                'similar_images_found' => count($similarImages),
                'notifications_sent' => count($notificationsSent),
                'emails_notified' => $notificationsSent,
                'similar_images' => $similarImages
            ];

        } catch (\Exception $e) {
            Log::error('Error checking similar images: ' . $e->getMessage());
            return [
                'similar_images_found' => 0,
                'notifications_sent' => 0,
                'emails_notified' => [],
                'similar_images' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate visual similarity between two images
     */
    private function calculateVisualSimilarity(string $image1Path, string $image2Path): float
    {
        try {
            $similarity = $this->imageComparator->compare($image1Path, $image2Path);
            return $similarity > 1 ? $similarity / 100 : $similarity;
        } catch (ImageResourceException $e) {
            Log::warning('Could not compare images: ' . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Calculate text similarity between new image metadata and existing image
     */
    private function calculateTextSimilarity(array $newMetadata, ImageMetadata $existingImage): float
    {
        $newDescription = $newMetadata['description'] ?? '';
        $newTags = $newMetadata['tags'] ?? [];
        $newTagsText = is_array($newTags) ? implode(' ', $newTags) : $newTags;
        $newObjects = $newMetadata['detected_objects'] ?? [];
        $newObjectsText = is_array($newObjects) ? implode(' ', array_map(function($obj) {
            return is_array($obj) ? ($obj['name'] ?? '') : $obj;
        }, $newObjects)) : '';

        $existingDescription = $existingImage->description ?? '';
        $existingTags = $existingImage->tags ?? [];
        $existingTagsText = is_array($existingTags) ? implode(' ', $existingTags) : $existingTags;
        $existingObjects = $existingImage->detected_objects ?? [];
        $existingObjectsText = is_array($existingObjects) ? implode(' ', array_map(function($obj) {
            return is_array($obj) ? ($obj['name'] ?? '') : $obj;
        }, $existingObjects)) : '';

        // If all fields are empty, return 0
        if (empty($newDescription) && empty($newTagsText) && empty($newObjectsText) && 
            empty($existingDescription) && empty($existingTagsText) && empty($existingObjectsText)) {
            return 0.0;
        }

        // If one has content and the other doesn't, return 0
        if ((empty($newDescription) && empty($newTagsText) && empty($newObjectsText)) || 
            (empty($existingDescription) && empty($existingTagsText) && empty($existingObjectsText))) {
            return 0.0;
        }

        $descriptionSimilarity = $this->calculateTextSimilarityScore($newDescription, $existingDescription);
        $tagsSimilarity = $this->calculateTextSimilarityScore($newTagsText, $existingTagsText);
        $objectsSimilarity = $this->calculateTextSimilarityScore($newObjectsText, $existingObjectsText);

        // Calculate weighted average: description (40%), tags (30%), objects (30%)
        $weightedSimilarity = ($descriptionSimilarity * 0.4) + ($tagsSimilarity * 0.3) + ($objectsSimilarity * 0.3);

        // Boost similarity if objects match (objects are more reliable)
        if ($objectsSimilarity > 0.5) {
            $weightedSimilarity = max($weightedSimilarity, $objectsSimilarity * 1.2); // Boost by 20%
        }

        return min(1.0, $weightedSimilarity);
    }

    /**
     * Calculate text similarity score using multiple algorithms
     */
    private function calculateTextSimilarityScore(string $text1, string $text2): float
    {
        if (empty($text1) || empty($text2)) {
            return 0.0;
        }

        $text1 = strtolower(trim($text1));
        $text2 = strtolower(trim($text2));

        if ($text1 === $text2) {
            return 1.0;
        }

        $jaroWinkler = $this->jaroWinklerSimilarity($text1, $text2);
        $levenshtein = $this->levenshteinSimilarity($text1, $text2);
        $wordOverlap = $this->wordOverlapSimilarity($text1, $text2);

        $algorithms = $this->config['algorithms'] ?? [];
        $jaroWeight = $algorithms['jaro_winkler_weight'] ?? 0.4;
        $levenshteinWeight = $algorithms['levenshtein_weight'] ?? 0.3;
        $wordOverlapWeight = $algorithms['word_overlap_weight'] ?? 0.3;

        $similarity = ($jaroWinkler * $jaroWeight) + ($levenshtein * $levenshteinWeight) + ($wordOverlap * $wordOverlapWeight);
        return min(1.0, max(0.0, $similarity));
    }

    /**
     * Calculate overall similarity combining visual and text
     */
    private function calculateOverallSimilarity(float $visualSimilarity, float $textSimilarity): float
    {
        $textWeight = $this->config['weights']['text'] ?? 0.3;
        $visualWeight = $this->config['weights']['visual'] ?? 0.7;

        $overallSimilarity = ($visualSimilarity * $visualWeight) + ($textSimilarity * $textWeight);

        // Additional validation: both visual and text similarity must meet minimum thresholds
        $minVisualThreshold = 0.6; // Minimum 60% visual similarity
        $minTextThreshold = 0.3;   // Minimum 30% text similarity

        if ($visualSimilarity < $minVisualThreshold || $textSimilarity < $minTextThreshold) {
            // If either similarity is too low, reduce the overall score significantly
            return $overallSimilarity * 0.3;
        }

        return $overallSimilarity;
    }

    /**
     * Group similar images by uploader email
     */
    private function groupSimilarImagesByEmail(array $similarImages): array
    {
        $emailGroups = [];

        foreach ($similarImages as $similarImage) {
            $email = $similarImage['image']->uploader_email;
            if (!isset($emailGroups[$email])) {
                $emailGroups[$email] = [];
            }
            $emailGroups[$email][] = $similarImage;
        }

        return $emailGroups;
    }

    /**
     * Send bulk similarity notification email
     */
    private function sendBulkSimilarityNotification(string $email, array $similarImages, array $newImageMetadata): void
    {
        // Check if email notifications are enabled
        if (!$this->areEmailNotificationsEnabled()) {
            Log::info('Email notifications disabled - skipping bulk similarity notification', [
                'email' => $email
            ]);
            return;
        }
        
        try {
            // Apply mail configuration before sending
            $this->applyMailConfigurationFromSettings();
            
            $data = [
                'email' => $email,
                'similar_images' => $similarImages,
                'new_image_metadata' => $newImageMetadata,
                'total_similar' => count($similarImages),
                'notification_type' => 'existing_owner'
            ];

            // Send the actual email notification
            Mail::to($email)->send(new SimilarImageNotification($data));

            Log::info('Similarity notification sent to: ' . $email, [
                'total_similar' => count($similarImages),
                'emails_sent' => 1,
                'mail_driver' => config('mail.default')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send similarity notification to ' . $email . ': ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Jaro-Winkler similarity algorithm (simplified version)
     */
    private function jaroWinklerSimilarity(string $s1, string $s2): float
    {
        $len1 = strlen($s1);
        $len2 = strlen($s2);

        if ($len1 === 0 || $len2 === 0) {
            return 0.0;
        }

        if ($s1 === $s2) {
            return 1.0;
        }

        // Simplified Jaro-Winkler using character frequency
        $chars1 = str_split($s1);
        $chars2 = str_split($s2);

        $common1 = 0;
        $common2 = 0;

        // Count common characters
        foreach ($chars1 as $char) {
            if (in_array($char, $chars2)) {
                $common1++;
            }
        }

        foreach ($chars2 as $char) {
            if (in_array($char, $chars1)) {
                $common2++;
            }
        }

        if ($common1 === 0 || $common2 === 0) {
            return 0.0;
        }

        $jaro = ($common1 / $len1 + $common2 / $len2) / 2;

        // Calculate prefix similarity
        $prefix = 0;
        $maxPrefix = min($len1, $len2, 4);
        for ($i = 0; $i < $maxPrefix; $i++) {
            if ($s1[$i] === $s2[$i]) {
                $prefix++;
            } else {
                break;
            }
        }

        return $jaro + (0.1 * $prefix * (1 - $jaro));
    }

    /**
     * Levenshtein similarity algorithm
     */
    private function levenshteinSimilarity(string $s1, string $s2): float
    {
        $len1 = strlen($s1);
        $len2 = strlen($s2);

        if ($len1 === 0) return $len2 === 0 ? 1.0 : 0.0;
        if ($len2 === 0) return 0.0;

        $distance = levenshtein($s1, $s2);
        $maxLen = max($len1, $len2);

        return 1 - ($distance / $maxLen);
    }

    /**
     * Word overlap similarity algorithm
     */
    private function wordOverlapSimilarity(string $s1, string $s2): float
    {
        $words1 = array_filter(array_map('trim', explode(' ', $s1)));
        $words2 = array_filter(array_map('trim', explode(' ', $s2)));

        if (empty($words1) || empty($words2)) {
            return 0.0;
        }

        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));

        return count($intersection) / count($union);
    }

    /**
     * Set similarity thresholds
     */
    public function setThresholds(float $visualThreshold, float $textThreshold): void
    {
        // Update the config array instead of undefined properties
        $this->config['thresholds']['visual'] = $visualThreshold;
        $this->config['thresholds']['text'] = $textThreshold;
    }

    /**
     * Send notification to new uploader about similar images found
     */
    private function sendNewUploaderNotification(string $newUploaderEmail, array $similarImages, array $newImageMetadata): void
    {
        // Check if email notifications are enabled
        if (!$this->areEmailNotificationsEnabled()) {
            Log::info('Email notifications disabled - skipping new uploader notification', [
                'email' => $newUploaderEmail
            ]);
            return;
        }
        
        try {
            // Apply mail configuration before sending
            $this->applyMailConfigurationFromSettings();
            
            $data = [
                'email' => $newUploaderEmail,
                'similar_images' => $similarImages,
                'new_image_metadata' => $newImageMetadata,
                'total_similar' => count($similarImages),
                'notification_type' => 'new_uploader'
            ];

            Mail::to($newUploaderEmail)->send(new \App\Mail\SimilarImageNotification($data));

            Log::info('New uploader notification sent', [
                'email' => $newUploaderEmail,
                'similar_images_count' => count($similarImages),
                'mail_driver' => config('mail.default')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send new uploader notification', [
                'email' => $newUploaderEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send no match notification - "we will notify you when similar item is found"
     */
    private function sendNoMatchNotification(string $newUploaderEmail, array $newImageMetadata): void
    {
        // Check if email notifications are enabled
        if (!$this->areEmailNotificationsEnabled()) {
            Log::info('Email notifications disabled - skipping no match notification', [
                'email' => $newUploaderEmail
            ]);
            return;
        }
        
        try {
            // Apply mail configuration before sending
            $this->applyMailConfigurationFromSettings();
            
            $data = [
                'email' => $newUploaderEmail,
                'similar_images' => [], // No similar images
                'total_similar' => 0, // No similar images found
                'new_image_metadata' => $newImageMetadata,
                'notification_type' => 'no_match'
            ];

            Mail::to($newUploaderEmail)->send(new \App\Mail\SimilarImageNotification($data));

            Log::info('No match notification sent', [
                'email' => $newUploaderEmail,
                'status' => $newImageMetadata['status'] ?? 'unknown',
                'mail_driver' => config('mail.default')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send no match notification', [
                'email' => $newUploaderEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Check for similarities with user uploaded items and send notifications
     */
    public function checkAndNotifySimilarities(ImageMetadata $newItem, string $userEmail): array
    {
        try {
            // Performance optimization: Limit the number of items to check
            // In production with many items, checking all items is too slow
            $maxItemsToCheck = $this->config['max_items_to_check'] ?? 500; // Default: check max 500 items
            $chunkSize = 50; // Process in chunks to avoid memory issues
            
            // Get existing items with limit and ordering (most recent first for better matches)
            // Only get items that have file paths (to avoid file_exists checks on null paths)
            // Only match Lost with Found and Found with Lost (opposite types)
            $oppositeStatus = ($newItem->status === 'lost') ? 'found' : 'lost';
            
            $existingItemsQuery = ImageMetadata::where('uploader_email', '!=', $userEmail)
                ->whereNotNull('uploader_email')
                ->whereNotNull('file_path')
                ->whereNotNull('filename')
                ->where('status', $oppositeStatus) // Only check items with opposite status
                ->orderBy('created_at', 'desc') // Check recent items first (more likely to be relevant)
                ->limit($maxItemsToCheck);

            $totalExistingItems = ImageMetadata::where('uploader_email', '!=', $userEmail)
                ->whereNotNull('uploader_email')
                ->count();

            $similarItems = [];
            $notificationsSent = [];
            $itemsChecked = 0;
            $startTime = microtime(true);
            $maxExecutionTime = 25; // Maximum 25 seconds for similarity check

            Log::info('Checking similarities for user item', [
                'new_item' => $newItem->original_name,
                'user_email' => $userEmail,
                'total_existing_items' => $totalExistingItems,
                'max_items_to_check' => $maxItemsToCheck,
                'chunk_size' => $chunkSize
            ]);

            // Process in chunks to avoid memory issues and allow early exit
            $existingItemsQuery->chunk($chunkSize, function ($itemsChunk) use (&$similarItems, &$itemsChecked, &$notificationsSent, $newItem, $userEmail, $startTime, $maxExecutionTime) {
                foreach ($itemsChunk as $existingItem) {
                    // Check execution time - exit if taking too long
                    $elapsed = microtime(true) - $startTime;
                    if ($elapsed > $maxExecutionTime) {
                        Log::warning('Similarity check timeout - stopping early', [
                            'items_checked' => $itemsChecked,
                            'elapsed_seconds' => round($elapsed, 2),
                            'max_execution_time' => $maxExecutionTime
                        ]);
                        return false; // Stop chunking
                    }
                    
                    $itemsChecked++;
                    
                    // Only match Lost with Found and Found with Lost (opposite types)
                    $newItemStatus = $newItem->status;
                    $existingItemStatus = $existingItem->status;
                    
                    // Skip if both items have the same status (Lost-Lost or Found-Found)
                    if ($newItemStatus === $existingItemStatus) {
                        continue;
                    }
                    
                    // Ensure opposite types: Lost ↔ Found
                    if (!(($newItemStatus === 'lost' && $existingItemStatus === 'found') || 
                          ($newItemStatus === 'found' && $existingItemStatus === 'lost'))) {
                        continue;
                    }
                    
                    // Get the file path for comparison
                    $newItemPath = $this->getItemFilePath($newItem);
                    $existingItemPath = $this->getItemFilePath($existingItem);

                    if (!$newItemPath || !$existingItemPath) {
                        Log::warning('File path not found for similarity comparison', [
                            'new_item_id' => $newItem->id,
                            'new_item_file_path' => $newItem->file_path,
                            'new_item_filename' => $newItem->filename,
                            'new_item_path_resolved' => $newItemPath,
                            'existing_item_id' => $existingItem->id,
                            'existing_item_file_path' => $existingItem->file_path,
                            'existing_item_filename' => $existingItem->filename,
                            'existing_item_path_resolved' => $existingItemPath,
                        ]);
                        continue;
                    }

                    try {
                        // Calculate similarities
                        $visualSimilarity = $this->calculateVisualSimilarity($newItemPath, $existingItemPath);
                        $textSimilarity = $this->calculateTextSimilarity([
                            'description' => $newItem->description,
                            'tags' => $newItem->tags,
                            'detected_objects' => $newItem->detected_objects
                        ], $existingItem);
                        $overallSimilarity = $this->calculateOverallSimilarity($visualSimilarity, $textSimilarity);

                        // Get threshold from config - check both old and new config structure
                        $visualThreshold = $this->config['thresholds']['visual'] ?? 
                                           $this->config['threshold'] ?? 
                                           0.7;

                        if ($overallSimilarity >= $visualThreshold) {
                            $similarItems[] = [
                                'description' => $existingItem->description,
                                'status' => $existingItem->status,
                                'uploader_email' => $existingItem->uploader_email,
                                'tags' => $existingItem->tags,
                                'similarity' => $overallSimilarity,
                                'item_id' => $existingItem->id,
                                'upload_id' => $existingItem->upload_id
                            ];

                            Log::info('Similar item found for user upload (opposite type match)', [
                                'new_item_status' => $newItemStatus,
                                'existing_item_status' => $existingItemStatus,
                                'existing_item' => $existingItem->original_name,
                                'similarity' => $overallSimilarity
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error calculating similarity for item: ' . $existingItem->original_name, [
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }
            });

            $elapsed = microtime(true) - $startTime;
            Log::info('Similarity check completed', [
                'items_checked' => $itemsChecked,
                'similar_items_found' => count($similarItems),
                'elapsed_seconds' => round($elapsed, 2),
                'total_existing_items' => $totalExistingItems
            ]);

            // Send notification to the user
            if (count($similarItems) > 0) {
                Log::info('Similar items found, sending notifications', [
                    'user_email' => $userEmail,
                    'similar_items_count' => count($similarItems),
                    'new_item_id' => $newItem->id,
                    'new_item_status' => $newItem->status
                ]);
                
                $this->sendUserSimilarityNotification($userEmail, $newItem, $similarItems);
                // Create in-app notification for similar items found
                $this->createSimilarItemsNotification($userEmail, $newItem, $similarItems);
                $notificationsSent[] = $userEmail;
                
                // Notify the owners of matched items (both users get notified)
                foreach ($similarItems as $similarItem) {
                    $matchedItemOwnerEmail = $similarItem['uploader_email'] ?? null;
                    if ($matchedItemOwnerEmail && $matchedItemOwnerEmail !== $userEmail) {
                        // Get the matched item details
                        $matchedItem = ImageMetadata::where('upload_id', $similarItem['upload_id'] ?? null)->first();
                        if ($matchedItem) {
                            // Check if it's a match (one lost, one found)
                            $isMatch = ($newItem->status === 'lost' && $matchedItem->status === 'found') ||
                                      ($newItem->status === 'found' && $matchedItem->status === 'lost');
                            
                            Log::info('Checking match status', [
                                'new_item_status' => $newItem->status,
                                'matched_item_status' => $matchedItem->status,
                                'is_match' => $isMatch,
                                'matched_item_owner_email' => $matchedItemOwnerEmail,
                                'similarity_score' => $similarItem['similarity'] ?? 0
                            ]);
                            
                            // Notify the matched item owner if it's a match (lost ↔ found)
                            // OR if similarity is high enough (>= 0.75) regardless of type
                            $highSimilarity = ($similarItem['similarity'] ?? 0) >= 0.75;
                            
                            if ($isMatch || $highSimilarity) {
                                if ($isMatch) {
                                    // Perfect match (lost ↔ found) - send match notification
                                    $this->notifyMatchedItemOwner($matchedItemOwnerEmail, $matchedItem, $newItem, $similarItem);
                                    $this->createMatchedItemNotification($matchedItemOwnerEmail, $matchedItem, $newItem, $similarItem);
                                } else {
                                    // High similarity but same type - send similarity notification
                                    Log::info('High similarity found (same type), sending similarity notification', [
                                        'similarity' => $similarItem['similarity'] ?? 0
                                    ]);
                                    $this->sendUserSimilarityNotification($matchedItemOwnerEmail, $matchedItem, [
                                        [
                                            'description' => $newItem->description,
                                            'status' => $newItem->status,
                                            'uploader_email' => $newItem->uploader_email,
                                            'tags' => $newItem->tags,
                                            'similarity' => $similarItem['similarity'] ?? 0,
                                            'item_id' => $newItem->id,
                                            'upload_id' => $newItem->upload_id
                                        ]
                                    ]);
                                    $this->createSimilarItemsNotification($matchedItemOwnerEmail, $matchedItem, [
                                        [
                                            'description' => $newItem->description,
                                            'status' => $newItem->status,
                                            'uploader_email' => $newItem->uploader_email,
                                            'tags' => $newItem->tags,
                                            'similarity' => $similarItem['similarity'] ?? 0,
                                            'item_id' => $newItem->id,
                                            'upload_id' => $newItem->upload_id
                                        ]
                                    ]);
                                }
                                $notificationsSent[] = $matchedItemOwnerEmail;
                            } else {
                                Log::info('Items are similar but not matching criteria', [
                                    'new_item_status' => $newItem->status,
                                    'matched_item_status' => $matchedItem->status,
                                    'similarity' => $similarItem['similarity'] ?? 0
                                ]);
                            }
                        } else {
                            Log::warning('Matched item not found in database', [
                                'upload_id' => $similarItem['upload_id'] ?? null
                            ]);
                        }
                    }
                }
            } else {
                Log::info('No similar items found, sending upload confirmation', [
                    'user_email' => $userEmail,
                    'new_item_id' => $newItem->id
                ]);
                $this->sendUserUploadConfirmation($userEmail, $newItem);
                $notificationsSent[] = $userEmail;
            }

            return [
                'similar_items_found' => count($similarItems),
                'notifications_sent' => count($notificationsSent),
                'emails_notified' => $notificationsSent,
                'similar_items' => $similarItems
            ];

        } catch (\Exception $e) {
            Log::error('Error checking user item similarities: ' . $e->getMessage());
            return [
                'similar_items_found' => 0,
                'notifications_sent' => 0,
                'emails_notified' => [],
                'similar_items' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get the file path for an item
     */
    private function getItemFilePath(ImageMetadata $item): ?string
    {
        $path = null;
        
        // Check if it's a user item or reference image
        if (str_contains($item->file_path ?? '', 'user-items')) {
            // Extract filename from file_path (e.g., /storage/user-items/filename.jpg -> filename.jpg)
            $filename = basename($item->file_path);
            $path = storage_path('app/public/user-items/' . $filename);
        } elseif ($item->filename) {
            // Try reference-images path
            $path = storage_path('app/public/reference-images/' . $item->filename);
        }
        
        // If path still not found, try alternative methods
        if (!$path || !file_exists($path)) {
            // Try using filename directly from file_path
            if ($item->file_path) {
                $filename = basename($item->file_path);
                // Try user-items first
                $altPath = storage_path('app/public/user-items/' . $filename);
                if (file_exists($altPath)) {
                    return $altPath;
                }
                // Try reference-images
                $altPath = storage_path('app/public/reference-images/' . $filename);
                if (file_exists($altPath)) {
                    return $altPath;
                }
            }
            
            // Log the failure for debugging
            Log::warning('File not found for item', [
                'item_id' => $item->id,
                'file_path' => $item->file_path,
                'filename' => $item->filename,
                'attempted_path' => $path,
            ]);
            
            return null;
        }

        return $path;
    }

    /**
     * Send similarity notification to user
     */
    private function sendUserSimilarityNotification(string $userEmail, ImageMetadata $newItem, array $similarItems): void
    {
        // Check if email notifications are enabled
        if (!$this->areEmailNotificationsEnabled()) {
            Log::info('Email notifications disabled - skipping similarity notification', [
                'email' => $userEmail
            ]);
            return;
        }
        
        try {
            // Apply mail configuration before sending
            $this->applyMailConfigurationFromSettings();
            
            $data = [
                'notification_type' => 'similar_item_found',
                'item_type' => $newItem->status,
                'item_description' => $newItem->description,
                'item_location' => $newItem->description, // You might want to add a location field
                'item_tags' => $newItem->tags,
                'contact_email' => $userEmail,
                'user_email' => $userEmail, // The authenticated user's email
                'similar_items' => $similarItems,
                'upload_date' => $newItem->created_at->format('M d, Y'),
                'upload_id' => $newItem->upload_id,
                'item_id' => $newItem->id
            ];

            Mail::to($userEmail)->send(new UserItemNotification($data));

            Log::info('User similarity notification sent', [
                'email' => $userEmail,
                'similar_items_count' => count($similarItems),
                'mail_driver' => config('mail.default')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send user similarity notification', [
                'email' => $userEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send upload confirmation to user
     */
    private function sendUserUploadConfirmation(string $userEmail, ImageMetadata $newItem): void
    {
        // Check if email notifications are enabled
        if (!$this->areEmailNotificationsEnabled()) {
            Log::info('Email notifications disabled - skipping upload confirmation', [
                'email' => $userEmail
            ]);
            return;
        }
        
        try {
            // Apply mail configuration before sending
            $this->applyMailConfigurationFromSettings();
            
            $data = [
                'notification_type' => 'new_item_uploaded',
                'item_type' => $newItem->status,
                'item_description' => $newItem->description,
                'item_location' => $newItem->description, // You might want to add a location field
                'item_tags' => $newItem->tags,
                'contact_email' => $userEmail,
                'user_email' => $userEmail, // The authenticated user's email
                'upload_date' => $newItem->created_at->format('M d, Y'),
                'upload_id' => $newItem->upload_id,
                'item_id' => $newItem->id
            ];

            Mail::to($userEmail)->send(new UserItemNotification($data));

            Log::info('User upload confirmation sent', [
                'email' => $userEmail,
                'item_type' => $newItem->status,
                'mail_driver' => config('mail.default')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send user upload confirmation', [
                'email' => $userEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create in-app notification for similar items found
     */
    private function createSimilarItemsNotification(string $userEmail, ImageMetadata $newItem, array $similarItems): void
    {
        try {
            $user = \App\Models\User::where('email', $userEmail)->first();
            if (!$user) {
                return;
            }

            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'item_match',
                'title' => 'Similar items found!',
                'message' => 'We found ' . count($similarItems) . ' similar item(s) that might match your ' . ($newItem->status === 'lost' ? 'lost' : 'found') . ' item.',
                'data' => [
                    'upload_id' => $newItem->upload_id,
                    'item_type' => $newItem->status,
                    'similar_items_count' => count($similarItems),
                    'similar_items' => array_map(function($item) {
                        return [
                            'upload_id' => $item['upload_id'] ?? null,
                            'description' => $item['description'] ?? '',
                        ];
                    }, $similarItems),
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create similar items notification: ' . $e->getMessage());
        }
    }

    /**
     * Notify the owner of a matched item about the match
     */
    private function notifyMatchedItemOwner(string $ownerEmail, ImageMetadata $matchedItem, ImageMetadata $newItem, array $similarityData): void
    {
        // Check if email notifications are enabled
        if (!$this->areEmailNotificationsEnabled()) {
            Log::info('Email notifications disabled - skipping matched item owner notification', [
                'email' => $ownerEmail
            ]);
            return;
        }
        
        try {
            // Apply mail configuration before sending
            $this->applyMailConfigurationFromSettings();
            
            $data = [
                'notification_type' => 'item_matched',
                'matched_item_type' => $matchedItem->status,
                'matched_item_description' => $matchedItem->description,
                'matched_item_location' => $matchedItem->location ?? 'Location not specified',
                'matched_item_tags' => $matchedItem->tags,
                'new_item_type' => $newItem->status,
                'new_item_description' => $newItem->description,
                'new_item_location' => $newItem->location ?? 'Location not specified',
                'new_item_tags' => $newItem->tags,
                'similarity_score' => round(($similarityData['similarity'] ?? 0) * 100, 2),
                'contact_email' => $ownerEmail,
                'user_email' => $ownerEmail,
                'matched_item_upload_id' => $matchedItem->upload_id,
                'new_item_upload_id' => $newItem->upload_id,
                'matched_item_id' => $matchedItem->id,
                'new_item_id' => $newItem->id,
                'match_date' => now()->format('M d, Y'),
            ];

            Mail::to($ownerEmail)->send(new UserItemNotification($data));

            Log::info('Matched item owner notification sent', [
                'email' => $ownerEmail,
                'matched_item_id' => $matchedItem->id,
                'new_item_id' => $newItem->id,
                'mail_driver' => config('mail.default')
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send matched item owner notification', [
                'email' => $ownerEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create in-app notification for matched item owner
     */
    private function createMatchedItemNotification(string $ownerEmail, ImageMetadata $matchedItem, ImageMetadata $newItem, array $similarityData): void
    {
        try {
            $owner = \App\Models\User::where('email', $ownerEmail)->first();
            if (!$owner) {
                return;
            }

            $similarityPercent = round(($similarityData['similarity'] ?? 0) * 100, 2);
            $matchType = ($matchedItem->status === 'lost' && $newItem->status === 'found') ? 
                        'Someone found an item that matches your lost item!' : 
                        'Someone lost an item that matches your found item!';

            \App\Models\Notification::create([
                'user_id' => $owner->id,
                'type' => 'item_matched',
                'title' => 'Item Match Found!',
                'message' => $matchType . ' (Similarity: ' . $similarityPercent . '%)',
                'data' => [
                    'matched_item_upload_id' => $matchedItem->upload_id,
                    'matched_item_id' => $matchedItem->id,
                    'matched_item_type' => $matchedItem->status,
                    'matched_item_description' => $matchedItem->description,
                    'matched_item_location' => $matchedItem->location,
                    'matched_item_tags' => $matchedItem->tags,
                    'new_item_upload_id' => $newItem->upload_id,
                    'new_item_id' => $newItem->id,
                    'new_item_type' => $newItem->status,
                    'new_item_description' => $newItem->description,
                    'new_item_location' => $newItem->location,
                    'new_item_tags' => $newItem->tags,
                    'similarity_score' => $similarityData['similarity'] ?? 0,
                    'similarity_percent' => $similarityPercent,
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create matched item notification: ' . $e->getMessage());
        }
    }
}
