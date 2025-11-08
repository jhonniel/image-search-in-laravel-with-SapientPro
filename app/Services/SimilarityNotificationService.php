<?php

namespace App\Services;

use App\Models\ImageMetadata;
use App\Mail\SimilarImageNotification;
use App\Mail\UserItemNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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

        $existingDescription = $existingImage->description ?? '';
        $existingTags = $existingImage->tags ?? [];
        $existingTagsText = is_array($existingTags) ? implode(' ', $existingTags) : $existingTags;

        // If both descriptions and tags are empty, return 0
        if (empty($newDescription) && empty($newTagsText) && empty($existingDescription) && empty($existingTagsText)) {
            return 0.0;
        }

        // If one has content and the other doesn't, return 0
        if ((empty($newDescription) && empty($newTagsText)) || (empty($existingDescription) && empty($existingTagsText))) {
            return 0.0;
        }

        $descriptionSimilarity = $this->calculateTextSimilarityScore($newDescription, $existingDescription);
        $tagsSimilarity = $this->calculateTextSimilarityScore($newTagsText, $existingTagsText);

        // Only return high similarity if both description and tags have meaningful similarity
        if ($descriptionSimilarity > 0.5 && $tagsSimilarity > 0.5) {
            return max($descriptionSimilarity, $tagsSimilarity);
        }

        // If only one aspect is similar, return a lower score
        return max($descriptionSimilarity, $tagsSimilarity) * 0.5;
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
        try {
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
                'emails_sent' => 1
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send similarity notification to ' . $email . ': ' . $e->getMessage());
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
        try {
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
                'similar_images_count' => count($similarImages)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send new uploader notification', [
                'email' => $newUploaderEmail,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send no match notification - "we will notify you when similar item is found"
     */
    private function sendNoMatchNotification(string $newUploaderEmail, array $newImageMetadata): void
    {
        try {
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
                'status' => $newImageMetadata['status'] ?? 'unknown'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send no match notification', [
                'email' => $newUploaderEmail,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check for similarities with user uploaded items and send notifications
     */
    public function checkAndNotifySimilarities(ImageMetadata $newItem, string $userEmail): array
    {
        try {
            // Get all existing items (both reference images and user items)
            $existingItems = ImageMetadata::where('uploader_email', '!=', $userEmail)
                ->whereNotNull('uploader_email')
                ->get();

            $similarItems = [];
            $notificationsSent = [];

            Log::info('Checking similarities for user item', [
                'new_item' => $newItem->original_name,
                'user_email' => $userEmail,
                'existing_items_count' => $existingItems->count()
            ]);

            foreach ($existingItems as $existingItem) {
                // Get the file path for comparison
                $newItemPath = $this->getItemFilePath($newItem);
                $existingItemPath = $this->getItemFilePath($existingItem);

                if (!$newItemPath || !$existingItemPath) {
                    continue;
                }

                try {
                    // Calculate similarities
                    $visualSimilarity = $this->calculateVisualSimilarity($newItemPath, $existingItemPath);
                    $textSimilarity = $this->calculateTextSimilarity([
                        'description' => $newItem->description,
                        'tags' => $newItem->tags
                    ], $existingItem);
                    $overallSimilarity = $this->calculateOverallSimilarity($visualSimilarity, $textSimilarity);

                    $visualThreshold = $this->config['thresholds']['visual'] ?? 0.7;

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

                        Log::info('Similar item found for user upload', [
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

            // Send notification to the user
            if (count($similarItems) > 0) {
                $this->sendUserSimilarityNotification($userEmail, $newItem, $similarItems);
                $notificationsSent[] = $userEmail;
            } else {
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
        // Check if it's a user item or reference image
        if (str_contains($item->file_path, 'user-items')) {
            $filename = basename($item->file_path);
            $path = storage_path('app/public/user-items/' . $filename);
        } else {
            $path = storage_path('app/public/reference-images/' . $item->filename);
        }

        return file_exists($path) ? $path : null;
    }

    /**
     * Send similarity notification to user
     */
    private function sendUserSimilarityNotification(string $userEmail, ImageMetadata $newItem, array $similarItems): void
    {
        try {
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
                'similar_items_count' => count($similarItems)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send user similarity notification', [
                'email' => $userEmail,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send upload confirmation to user
     */
    private function sendUserUploadConfirmation(string $userEmail, ImageMetadata $newItem): void
    {
        try {
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
                'item_type' => $newItem->status
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send user upload confirmation', [
                'email' => $userEmail,
                'error' => $e->getMessage()
            ]);
        }
    }
}
