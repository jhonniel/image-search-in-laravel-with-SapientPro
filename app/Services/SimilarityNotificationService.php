<?php

namespace App\Services;

use App\Mail\SimilarImageNotification;
use App\Mail\UserItemNotification;
use App\Models\ImageMetadata;
use App\Models\ItemMatch;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use SapientPro\ImageComparator\ImageComparator;
use SapientPro\ImageComparator\ImageResourceException;

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
            if (! Schema::hasTable('settings')) {
                return;
            }

            // Check if email notifications are enabled
            $emailNotificationsEnabled = Setting::get('email_notifications', true);
            $similarityAlertsEnabled = Setting::get('similarity_alerts', true);

            // Only apply mail config if notifications are enabled
            if ($emailNotificationsEnabled && $similarityAlertsEnabled) {
                // Get mail settings from database
                $mailMailer = Setting::get('mail_mailer', env('MAIL_MAILER', 'log'));
                $mailHost = Setting::get('mail_host', env('MAIL_HOST'));
                $mailPort = Setting::get('mail_port', env('MAIL_PORT', 587));
                $mailUsername = Setting::get('mail_username', env('MAIL_USERNAME'));
                $mailPassword = Setting::get('mail_password', env('MAIL_PASSWORD'));
                $mailEncryption = Setting::get('mail_encryption', env('MAIL_ENCRYPTION', 'tls'));
                $mailFromAddress = Setting::get('mail_from_address', env('MAIL_FROM_ADDRESS'));
                $mailFromName = Setting::get('mail_from_name', env('MAIL_FROM_NAME'));

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
                        Config::set('mail.default', 'smtp');
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
            if (! Schema::hasTable('settings')) {
                return true; // Default to enabled if settings table doesn't exist
            }

            $emailNotificationsEnabled = Setting::get('email_notifications', true);
            $similarityAlertsEnabled = Setting::get('similarity_alerts', true);

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
        if (! $this->config['enabled'] ?? true) {
            return [
                'similar_images_found' => 0,
                'notifications_sent' => 0,
                'emails_notified' => [],
                'similar_images' => [],
                'message' => 'Similarity checking is disabled',
            ];
        }

        $similarImages = [];
        $notificationsSent = [];

        try {
            // Get all existing images with metadata
            $existingImages = ImageMetadata::whereNotNull('uploader_email')
                ->where('uploader_email', '!=', $newUploaderEmail) // Don't notify the same user
                ->whereNull('images_purged_at')
                ->availableForUsers()
                ->get();

            Log::info('Checking similarity for new image', [
                'new_image_path' => $newImagePath,
                'existing_images_count' => $existingImages->count(),
                'new_uploader_email' => $newUploaderEmail,
            ]);

            foreach ($existingImages as $existingImage) {
                $existingImagePath = storage_path('app/public/reference-images/'.$existingImage->filename);

                if (! file_exists($existingImagePath)) {
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
                        'overall_similarity' => $overallSimilarity,
                    ]);

                    // Check if similarity meets threshold
                    $visualThreshold = $this->config['thresholds']['visual'] ?? 0.8; // Use config value, default to 0.8 for strict similarity
                    $objectsSimilarity = $this->calculateObjectsOverlap($newImageMetadata, $existingImage);

                    Log::debug('Threshold check', [
                        'existing_image' => $existingImage->original_name,
                        'overall_similarity' => $overallSimilarity,
                        'visual_threshold' => $visualThreshold,
                        'objects_similarity' => $objectsSimilarity,
                        'meets_threshold' => $this->meetsMatchCriteria($visualSimilarity, $textSimilarity, $overallSimilarity, $objectsSimilarity),
                    ]);

                    if ($this->meetsMatchCriteria($visualSimilarity, $textSimilarity, $overallSimilarity, $objectsSimilarity)) {
                        $similarImages[] = [
                            'image' => $existingImage,
                            'visual_similarity' => $visualSimilarity,
                            'text_similarity' => $textSimilarity,
                            'overall_similarity' => $overallSimilarity,
                            'path' => $existingImagePath,
                        ];

                        Log::info('Similar image found', [
                            'existing_image' => $existingImage->original_name,
                            'similarity' => $overallSimilarity,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error calculating similarity for image: '.$existingImage->original_name, [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
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
                    'existing_owners_notified' => array_keys($emailGroups),
                ]);
            } else {
                // No similar images found - only notify the new uploader
                if ($newUploaderEmail) {
                    $this->sendNoMatchNotification($newUploaderEmail, $newImageMetadata);
                    $notificationsSent[] = $newUploaderEmail;
                }

                Log::info('No similar images found - only notifying new uploader', [
                    'similar_images_count' => count($similarImages),
                    'new_uploader_email' => $newUploaderEmail,
                ]);
            }

            return [
                'similar_images_found' => count($similarImages),
                'notifications_sent' => count($notificationsSent),
                'emails_notified' => $notificationsSent,
                'similar_images' => $similarImages,
            ];

        } catch (\Exception $e) {
            Log::error('Error checking similar images: '.$e->getMessage());

            return [
                'similar_images_found' => 0,
                'notifications_sent' => 0,
                'emails_notified' => [],
                'similar_images' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate visual similarity between two images
     */
    private function calculateVisualSimilarity(string $image1Path, string $image2Path): float
    {
        return $this->compareVisualScores($image1Path, $image2Path)['normalized'];
    }

    /**
     * @return array{raw:float,normalized:float}
     */
    private function compareVisualScores(string $image1Path, string $image2Path): array
    {
        try {
            $raw = $this->imageComparator->compare($image1Path, $image2Path);
            $raw = $raw > 1 ? $raw / 100 : $raw;

            return [
                'raw' => min(1.0, max(0.0, (float) $raw)),
                'normalized' => $this->normalizeVisualScore((float) $raw),
            ];
        } catch (ImageResourceException $e) {
            Log::warning('Could not compare images: '.$e->getMessage());

            return ['raw' => 0.0, 'normalized' => 0.0];
        }
    }

    /**
     * Rescale a raw perceptual-hash score so hash noise reads as 0.
     *
     * Unrelated photos (a bag vs a wallet, both centred on white) still agree on
     * roughly half the hash bits, so the raw score bottoms out near 0.5 instead of 0.
     * Mapping [floor, 1] onto [0, 1] keeps thresholds meaningful.
     */
    private function normalizeVisualScore(float $rawSimilarity): float
    {
        $floor = (float) ($this->config['visual_floor'] ?? 0.55);
        $floor = min(max($floor, 0.0), 0.95);

        if ($rawSimilarity <= $floor) {
            return 0.0;
        }

        return min(1.0, ($rawSimilarity - $floor) / (1.0 - $floor));
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
        $newObjectsText = is_array($newObjects) ? implode(' ', array_map(function ($obj) {
            return is_array($obj) ? ($obj['name'] ?? '') : $obj;
        }, $newObjects)) : '';

        $existingDescription = $existingImage->description ?? '';
        $existingTags = $existingImage->tags ?? [];
        $existingTagsText = is_array($existingTags) ? implode(' ', $existingTags) : $existingTags;
        $existingObjects = $existingImage->detected_objects ?? [];
        $existingObjectsText = is_array($existingObjects) ? implode(' ', array_map(function ($obj) {
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

        // Boost similarity if objects match strongly (objects are more reliable)
        if ($objectsSimilarity > 0.6) {
            $weightedSimilarity = max($weightedSimilarity, $objectsSimilarity * 1.05);
        }

        // Down-rank when Vision labels conflict and neither side is empty
        if ($objectsSimilarity > 0 && $objectsSimilarity < 0.2) {
            $weightedSimilarity *= 0.55;
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
        $textWeight = (float) ($this->config['weights']['text'] ?? 0.35);
        $visualWeight = (float) ($this->config['weights']['visual'] ?? 0.65);
        $weightSum = max(0.0001, $visualWeight + $textWeight);
        $visualWeight /= $weightSum;
        $textWeight /= $weightSum;

        $overallSimilarity = ($visualSimilarity * $visualWeight) + ($textSimilarity * $textWeight);

        // Near-duplicate photos should always score highly.
        if ($visualSimilarity >= 0.75) {
            $overallSimilarity = max($overallSimilarity, $visualSimilarity);
        }

        // Same item / different background: reward solid text with usable visual.
        if ($textSimilarity >= 0.70 && $visualSimilarity >= 0.25) {
            $overallSimilarity = max($overallSimilarity, ($visualSimilarity * 0.55) + ($textSimilarity * 0.45));
        }

        // The photo carries no real signal: text alone must not lift it into match range.
        if ($visualSimilarity <= 0.0) {
            $overallSimilarity *= 0.30;
        } elseif ($visualSimilarity < 0.15 && $textSimilarity < 0.40) {
            $overallSimilarity *= 0.35;
        } elseif ($visualSimilarity < 0.10) {
            $overallSimilarity *= 0.55;
        }

        return min(1.0, max(0.0, $overallSimilarity));
    }

    /**
     * Whether two items should be treated as a candidate match.
     */
    private function meetsMatchCriteria(float $visualSimilarity, float $textSimilarity, float $overallSimilarity, float $objectsSimilarity = -1.0): bool
    {
        $matchThreshold = (float) ($this->config['thresholds']['match'] ?? $this->config['threshold'] ?? 0.45);
        $minVisual = (float) ($this->config['thresholds']['visual'] ?? 0.25);
        $semanticVisual = (float) ($this->config['thresholds']['semantic_visual'] ?? 0.20);
        $semanticText = (float) ($this->config['thresholds']['semantic_text'] ?? 0.70);
        $strongVisualThreshold = (float) ($this->config['thresholds']['strong_visual'] ?? 0.65);

        // Photos with no similarity beyond hash noise are never a match, whatever the words say.
        if ($visualSimilarity <= 0.0) {
            return false;
        }

        // Soft guidance only: completely disjoint Vision labels + weak photo → skip.
        // Do not block otherwise — labels are often noisy for the same physical item.
        if ($objectsSimilarity === 0.0 && $visualSimilarity < 0.45 && $textSimilarity < 0.70) {
            return false;
        }

        $primaryMatch = $overallSimilarity >= $matchThreshold && $visualSimilarity >= $minVisual;
        $strongVisual = $visualSimilarity >= $strongVisualThreshold && $overallSimilarity >= ($matchThreshold - 0.08);
        $semanticFallback = $visualSimilarity >= $semanticVisual
            && $textSimilarity >= $semanticText
            && $overallSimilarity >= ($matchThreshold - 0.08);

        return $primaryMatch || $strongVisual || $semanticFallback;
    }

    /**
     * Persist a bidirectional ItemMatch row pair.
     */
    private function storeBidirectionalMatch(
        ImageMetadata $userItem,
        ImageMetadata $matchedItem,
        string $userEmail,
        float $overallSimilarity,
        float $visualSimilarity,
        float $textSimilarity
    ): void {
        $notifyThreshold = (float) ($this->config['thresholds']['match']
            ?? $this->config['threshold']
            ?? 0.52);
        $shouldNotify = $overallSimilarity >= $notifyThreshold
            || ($visualSimilarity >= 0.65 && $textSimilarity >= 0.70);

        ItemMatch::updateOrCreate(
            [
                'user_item_upload_id' => $userItem->upload_id,
                'matched_item_upload_id' => $matchedItem->upload_id,
            ],
            [
                'user_email' => $userEmail,
                'matched_item_owner_email' => $matchedItem->uploader_email,
                'user_item_status' => $userItem->status,
                'matched_item_status' => $matchedItem->status,
                'similarity_score' => $overallSimilarity,
                'visual_similarity' => $visualSimilarity,
                'text_similarity' => $textSimilarity,
                'is_notified' => $shouldNotify,
            ]
        );

        ItemMatch::updateOrCreate(
            [
                'user_item_upload_id' => $matchedItem->upload_id,
                'matched_item_upload_id' => $userItem->upload_id,
            ],
            [
                'user_email' => $matchedItem->uploader_email,
                'matched_item_owner_email' => $userEmail,
                'user_item_status' => $matchedItem->status,
                'matched_item_status' => $userItem->status,
                'similarity_score' => $overallSimilarity,
                'visual_similarity' => $visualSimilarity,
                'text_similarity' => $textSimilarity,
                'is_notified' => $shouldNotify,
            ]
        );
    }

    /**
     * Best visual similarity across every image file in two upload groups.
     *
     * @return array{raw:float,normalized:float}
     */
    private function maxVisualSimilarityBetweenGroups($userGroup, $otherGroup): array
    {
        $maxRaw = 0.0;
        $maxNormalized = 0.0;

        foreach ($userGroup as $userImg) {
            $userPath = $this->getItemFilePath($userImg);
            if (! $userPath) {
                continue;
            }
            foreach ($otherGroup as $otherImg) {
                $otherPath = $this->getItemFilePath($otherImg);
                if (! $otherPath) {
                    continue;
                }
                $scores = $this->compareVisualScores($userPath, $otherPath);
                if ($scores['raw'] > $maxRaw) {
                    $maxRaw = $scores['raw'];
                }
                if ($scores['normalized'] > $maxNormalized) {
                    $maxNormalized = $scores['normalized'];
                }
            }
        }

        return [
            'raw' => $maxRaw,
            'normalized' => $maxNormalized,
        ];
    }

    /**
     * Re-scan opposite-type listings for this user and store any missing matches.
     * Used by Claim & Verify so matches still appear when the upload-time check missed them.
     */
    public function refreshMatchesForUser(string $userEmail, int $maxOtherUploads = 80, int $maxSeconds = 10, ?string $onlyUploadId = null): int
    {
        return $this->rescanMatches($userEmail, $maxOtherUploads, $maxSeconds, $onlyUploadId)['stored'];
    }

    /**
     * Re-score stored matches and scan for new ones.
     *
     * @return array{stored:int,removed:int,near_misses:array<int,array{matched_item_upload_id:string,similarity:float,visual_similarity:float,text_similarity:float}>}
     */
    public function rescanMatches(string $userEmail, int $maxOtherUploads = 80, int $maxSeconds = 10, ?string $onlyUploadId = null): array
    {
        return $this->scanForNewMatches($userEmail, $maxOtherUploads, $maxSeconds, $onlyUploadId);
    }

    /**
     * @return array{stored:int,removed:int,near_misses:array}
     */
    private function scanForNewMatches(string $userEmail, int $maxOtherUploads, int $maxSeconds, ?string $onlyUploadId): array
    {
        if (! ($this->config['enabled'] ?? true)) {
            return ['stored' => 0, 'removed' => 0, 'near_misses' => []];
        }

        $userGroups = ImageMetadata::where('uploader_email', $userEmail)
            ->when($onlyUploadId, fn ($q) => $q->where('upload_id', $onlyUploadId))
            ->whereNull('images_purged_at')
            ->availableForUsers()
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('upload_id');

        if ($userGroups->isEmpty()) {
            return ['stored' => 0, 'removed' => 0, 'near_misses' => []];
        }

        $stored = 0;
        $removed = 0;
        $nearMisses = [];
        $started = microtime(true);

        foreach ($userGroups as $userUploadId => $userGroup) {
            if ((microtime(true) - $started) > $maxSeconds) {
                break;
            }

            $userFirst = $userGroup->first();
            if (! $userFirst) {
                continue;
            }

            $oppositeStatus = $userFirst->status === 'lost' ? 'found' : 'lost';

            // Re-score what is already stored first, so matches kept by older/looser
            // scoring are corrected or dropped instead of lingering forever.
            $removed += $this->rescoreStoredMatches($userEmail, $userUploadId, $userGroup, $userFirst, $nearMisses);

            $alreadyMatched = ItemMatch::where('user_email', $userEmail)
                ->where('user_item_upload_id', $userUploadId)
                ->pluck('matched_item_upload_id')
                ->all();

            $otherGroups = ImageMetadata::where('uploader_email', '!=', $userEmail)
                ->where('status', $oppositeStatus)
                ->whereNotNull('file_path')
                ->whereNull('images_purged_at')
                ->availableForUsers()
                ->when(! empty($alreadyMatched), fn ($q) => $q->whereNotIn('upload_id', $alreadyMatched))
                ->orderByDesc('created_at')
                ->limit($maxOtherUploads * 3) // more rows before grouping
                ->get()
                ->groupBy('upload_id')
                ->take($maxOtherUploads);

            foreach ($otherGroups as $otherUploadId => $otherGroup) {
                if ((microtime(true) - $started) > $maxSeconds) {
                    break 2;
                }

                $otherFirst = $otherGroup->first();
                if (! $otherFirst) {
                    continue;
                }

                try {
                    $visualScores = $this->maxVisualSimilarityBetweenGroups($userGroup, $otherGroup);
                    $visualSimilarity = $visualScores['normalized'];
                    $rawVisualSimilarity = $visualScores['raw'];
                    $newMetadata = [
                        'description' => $userFirst->description,
                        'tags' => $userFirst->tags,
                        'detected_objects' => $userFirst->detected_objects,
                    ];
                    $textSimilarity = $this->calculateTextSimilarity($newMetadata, $otherFirst);
                    $overallSimilarity = $this->calculateOverallSimilarity($visualSimilarity, $textSimilarity);
                    $objectsSimilarity = $this->calculateObjectsOverlap($newMetadata, $otherFirst);

                    if (! $this->meetsMatchCriteria($visualSimilarity, $textSimilarity, $overallSimilarity, $objectsSimilarity)) {
                        $this->rememberNearMiss(
                            $nearMisses,
                            (string) $otherUploadId,
                            $overallSimilarity,
                            $visualSimilarity,
                            $textSimilarity,
                            $rawVisualSimilarity
                        );

                        continue;
                    }

                    $this->storeBidirectionalMatch(
                        $userFirst,
                        $otherFirst,
                        $userEmail,
                        $overallSimilarity,
                        $visualSimilarity,
                        $textSimilarity
                    );
                    $stored++;

                    Log::info('Claim-verify refresh stored missing match', [
                        'user_email' => $userEmail,
                        'user_upload_id' => $userUploadId,
                        'matched_upload_id' => $otherUploadId,
                        'similarity' => $overallSimilarity,
                        'visual' => $visualSimilarity,
                        'text' => $textSimilarity,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Claim-verify refresh compare failed: '.$e->getMessage(), [
                        'user_upload_id' => $userUploadId,
                        'other_upload_id' => $otherUploadId,
                    ]);
                }
            }
        }

        return [
            'stored' => $stored,
            'removed' => $removed,
            'near_misses' => $this->rankNearMisses($nearMisses),
        ];
    }

    /**
     * Re-scan opposite-type listings for a single item and store any missing matches.
     * Scans wider than the whole-account refresh because only one item is compared.
     *
     * @return array{stored:int,removed:int,near_misses:array}
     */
    public function refreshMatchesForItem(string $userEmail, string $uploadId, int $maxOtherUploads = 200, int $maxSeconds = 20): array
    {
        return $this->rescanMatches($userEmail, $maxOtherUploads, $maxSeconds, $uploadId);
    }

    /**
     * Re-compare every stored match for one item, dropping the ones that no longer qualify.
     *
     * @param  array<string, array{matched_item_upload_id:string,similarity:float,visual_similarity:float,text_similarity:float}>  $nearMisses
     * @return int number of matches removed
     */
    private function rescoreStoredMatches(string $userEmail, string $userUploadId, $userGroup, ImageMetadata $userFirst, array &$nearMisses): int
    {
        $removed = 0;

        $existingMatches = ItemMatch::where('user_email', $userEmail)
            ->where('user_item_upload_id', $userUploadId)
            ->get();

        foreach ($existingMatches as $existing) {
            $otherGroup = ImageMetadata::where('upload_id', $existing->matched_item_upload_id)
                ->whereNull('images_purged_at')
                ->get();

            $otherFirst = $otherGroup->first();
            if (! $otherFirst) {
                continue;
            }

            try {
                $visualScores = $this->maxVisualSimilarityBetweenGroups($userGroup, $otherGroup);
                $visualSimilarity = $visualScores['normalized'];
                $rawVisualSimilarity = $visualScores['raw'];
                $newMetadata = [
                    'description' => $userFirst->description,
                    'tags' => $userFirst->tags,
                    'detected_objects' => $userFirst->detected_objects,
                ];
                $textSimilarity = $this->calculateTextSimilarity($newMetadata, $otherFirst);
                $overallSimilarity = $this->calculateOverallSimilarity($visualSimilarity, $textSimilarity);
                $objectsSimilarity = $this->calculateObjectsOverlap($newMetadata, $otherFirst);

                if ($this->meetsMatchCriteria($visualSimilarity, $textSimilarity, $overallSimilarity, $objectsSimilarity)) {
                    $this->storeBidirectionalMatch(
                        $userFirst,
                        $otherFirst,
                        $userEmail,
                        $overallSimilarity,
                        $visualSimilarity,
                        $textSimilarity
                    );

                    continue;
                }

                ItemMatch::where(function ($q) use ($userUploadId, $existing) {
                    $q->where('user_item_upload_id', $userUploadId)
                        ->where('matched_item_upload_id', $existing->matched_item_upload_id);
                })->orWhere(function ($q) use ($userUploadId, $existing) {
                    $q->where('user_item_upload_id', $existing->matched_item_upload_id)
                        ->where('matched_item_upload_id', $userUploadId);
                })->delete();

                $this->rememberNearMiss(
                    $nearMisses,
                    (string) $existing->matched_item_upload_id,
                    $overallSimilarity,
                    $visualSimilarity,
                    $textSimilarity,
                    $rawVisualSimilarity
                );

                $removed++;

                Log::info('Dropped match that no longer meets criteria', [
                    'user_upload_id' => $userUploadId,
                    'matched_upload_id' => $existing->matched_item_upload_id,
                    'old_score' => (float) $existing->similarity_score,
                    'visual' => $visualSimilarity,
                    'text' => $textSimilarity,
                    'overall' => $overallSimilarity,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Re-scoring stored match failed: '.$e->getMessage(), [
                    'user_upload_id' => $userUploadId,
                    'matched_upload_id' => $existing->matched_item_upload_id,
                ]);
            }
        }

        return $removed;
    }

    /**
     * Keep a rejected comparison so Claim & Verify can show "looked similar but failed".
     * Uses the raw hash score so studio-style lookalikes (bag vs wallet) still appear
     * even though normalized visual is 0 after the noise floor.
     *
     * @param  array<string, array{matched_item_upload_id:string,similarity:float,visual_similarity:float,text_similarity:float,raw_visual_similarity:float}>  $nearMisses
     */
    private function rememberNearMiss(
        array &$nearMisses,
        string $matchedUploadId,
        float $overallSimilarity,
        float $visualSimilarity,
        float $textSimilarity,
        float $rawVisualSimilarity = 0.0
    ): void {
        $floor = (float) ($this->config['thresholds']['near_miss'] ?? 0.08);
        $lookalikeRaw = (float) ($this->config['thresholds']['near_miss_raw_visual'] ?? 0.48);

        $isLookalike = $rawVisualSimilarity >= $lookalikeRaw;
        $hasSignal = $overallSimilarity >= $floor
            || $visualSimilarity > 0.0
            || $textSimilarity >= 0.40
            || $isLookalike;

        if (! $hasSignal) {
            return;
        }

        // Rank lookalikes by how close the raw hash was, not the flattened overall score.
        $rankScore = max($overallSimilarity, $isLookalike ? (($rawVisualSimilarity - 0.45) / 0.55) : 0.0);

        $existing = $nearMisses[$matchedUploadId] ?? null;
        if ($existing && ($existing['rank_score'] ?? $existing['similarity']) >= $rankScore) {
            return;
        }

        $nearMisses[$matchedUploadId] = [
            'matched_item_upload_id' => $matchedUploadId,
            'similarity' => $overallSimilarity,
            'visual_similarity' => $visualSimilarity,
            'text_similarity' => $textSimilarity,
            'raw_visual_similarity' => $rawVisualSimilarity,
            'rank_score' => $rankScore,
        ];
    }

    /**
     * @param  array<string, array{matched_item_upload_id:string,similarity:float,visual_similarity:float,text_similarity:float,raw_visual_similarity?:float,rank_score?:float}>  $nearMisses
     * @return array<int, array{matched_item_upload_id:string,similarity:float,visual_similarity:float,text_similarity:float,raw_visual_similarity:float}>
     */
    private function rankNearMisses(array $nearMisses): array
    {
        $limit = max(1, (int) ($this->config['near_miss_limit'] ?? 12));

        usort($nearMisses, function ($a, $b) {
            $scoreA = $a['rank_score'] ?? $a['similarity'];
            $scoreB = $b['rank_score'] ?? $b['similarity'];

            return $scoreB <=> $scoreA;
        });

        return array_map(function (array $near) {
            return [
                'matched_item_upload_id' => $near['matched_item_upload_id'],
                'similarity' => $near['similarity'],
                'visual_similarity' => $near['visual_similarity'],
                'text_similarity' => $near['text_similarity'],
                'raw_visual_similarity' => $near['raw_visual_similarity'] ?? 0.0,
            ];
        }, array_slice(array_values($nearMisses), 0, $limit));
    }

    /**
     * Object-label overlap (0–1), or -1 when either side has no labels.
     */
    private function calculateObjectsOverlap(array $newMetadata, ImageMetadata $existingImage): float
    {
        $normalize = function ($objects): array {
            if (! is_array($objects)) {
                return [];
            }

            $names = [];
            foreach ($objects as $obj) {
                $name = is_array($obj) ? strtolower(trim((string) ($obj['name'] ?? ''))) : strtolower(trim((string) $obj));
                if ($name !== '') {
                    $names[] = $name;
                }
            }

            return array_values(array_unique($names));
        };

        $newObjects = $normalize($newMetadata['detected_objects'] ?? []);
        $existingObjects = $normalize($existingImage->detected_objects ?? []);

        if ($newObjects === [] || $existingObjects === []) {
            return -1.0;
        }

        $intersection = array_intersect($newObjects, $existingObjects);
        $union = array_unique(array_merge($newObjects, $existingObjects));

        if ($union === []) {
            return -1.0;
        }

        return count($intersection) / count($union);
    }

    /**
     * Group similar images by uploader email
     */
    private function groupSimilarImagesByEmail(array $similarImages): array
    {
        $emailGroups = [];

        foreach ($similarImages as $similarImage) {
            $email = $similarImage['image']->uploader_email;
            if (! isset($emailGroups[$email])) {
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
        if (! $this->areEmailNotificationsEnabled()) {
            Log::info('Email notifications disabled - skipping bulk similarity notification', [
                'email' => $email,
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
                'notification_type' => 'existing_owner',
            ];

            // Send the actual email notification
            Mail::to($email)->send(new SimilarImageNotification($data));

            Log::info('Similarity notification sent to: '.$email, [
                'total_similar' => count($similarImages),
                'emails_sent' => 1,
                'mail_driver' => config('mail.default'),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send similarity notification to '.$email.': '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
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

        if ($len1 === 0) {
            return $len2 === 0 ? 1.0 : 0.0;
        }
        if ($len2 === 0) {
            return 0.0;
        }

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
        if (! $this->areEmailNotificationsEnabled()) {
            Log::info('Email notifications disabled - skipping new uploader notification', [
                'email' => $newUploaderEmail,
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
                'notification_type' => 'new_uploader',
            ];

            Mail::to($newUploaderEmail)->send(new SimilarImageNotification($data));

            Log::info('New uploader notification sent', [
                'email' => $newUploaderEmail,
                'similar_images_count' => count($similarImages),
                'mail_driver' => config('mail.default'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send new uploader notification', [
                'email' => $newUploaderEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Send no match notification - "we will notify you when similar item is found"
     */
    private function sendNoMatchNotification(string $newUploaderEmail, array $newImageMetadata): void
    {
        // Check if email notifications are enabled
        if (! $this->areEmailNotificationsEnabled()) {
            Log::info('Email notifications disabled - skipping no match notification', [
                'email' => $newUploaderEmail,
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
                'notification_type' => 'no_match',
            ];

            Mail::to($newUploaderEmail)->send(new SimilarImageNotification($data));

            Log::info('No match notification sent', [
                'email' => $newUploaderEmail,
                'status' => $newImageMetadata['status'] ?? 'unknown',
                'mail_driver' => config('mail.default'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send no match notification', [
                'email' => $newUploaderEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Defer similarity comparison until after the HTTP response is sent so uploads return quickly on slow networks.
     * Uses Laravel's terminating callback (no queue worker required).
     */
    public static function queueSimilarityCheckAfterResponse(int $imageMetadataId, string $userEmail): void
    {
        app()->terminating(function () use ($imageMetadataId, $userEmail): void {
            try {
                $metadata = ImageMetadata::find($imageMetadataId);
                if (! $metadata) {
                    Log::warning('Deferred similarity check skipped: ImageMetadata not found', [
                        'image_metadata_id' => $imageMetadataId,
                        'user_email' => $userEmail,
                    ]);

                    return;
                }

                $result = app(self::class)->checkAndNotifySimilarities($metadata, $userEmail);

                Log::info('Deferred similarity check completed', [
                    'image_metadata_id' => $imageMetadataId,
                    'upload_id' => $metadata->upload_id,
                    'user_email' => $userEmail,
                    'similar_items_found' => $result['similar_items_found'] ?? 0,
                    'notifications_sent' => $result['notifications_sent'] ?? 0,
                ]);
            } catch (\Throwable $e) {
                Log::error('Deferred similarity check failed: '.$e->getMessage(), [
                    'image_metadata_id' => $imageMetadataId,
                    'user_email' => $userEmail,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        });
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
                ->whereNull('images_purged_at')
                ->availableForUsers()
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
                'chunk_size' => $chunkSize,
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
                            'max_execution_time' => $maxExecutionTime,
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
                    if (! (($newItemStatus === 'lost' && $existingItemStatus === 'found') ||
                          ($newItemStatus === 'found' && $existingItemStatus === 'lost'))) {
                        continue;
                    }

                    // Get the file path for comparison
                    $newItemPath = $this->getItemFilePath($newItem);
                    $existingItemPath = $this->getItemFilePath($existingItem);

                    if (! $newItemPath || ! $existingItemPath) {
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
                        $newMetadata = [
                            'description' => $newItem->description,
                            'tags' => $newItem->tags,
                            'detected_objects' => $newItem->detected_objects,
                        ];
                        $textSimilarity = $this->calculateTextSimilarity($newMetadata, $existingItem);
                        $overallSimilarity = $this->calculateOverallSimilarity($visualSimilarity, $textSimilarity);
                        $objectsSimilarity = $this->calculateObjectsOverlap($newMetadata, $existingItem);

                        // Get threshold from config - check both old and new config structure
                        $visualThreshold = $this->config['thresholds']['match']
                            ?? $this->config['thresholds']['visual']
                            ?? $this->config['threshold']
                            ?? 0.52;

                        if ($this->meetsMatchCriteria($visualSimilarity, $textSimilarity, $overallSimilarity, $objectsSimilarity)) {
                            $similarItems[] = [
                                'description' => $existingItem->description,
                                'status' => $existingItem->status,
                                'uploader_email' => $existingItem->uploader_email,
                                'tags' => $existingItem->tags,
                                'similarity' => $overallSimilarity,
                                'item_id' => $existingItem->id,
                                'upload_id' => $existingItem->upload_id,
                                'visual_similarity' => $visualSimilarity,
                                'text_similarity' => $textSimilarity,
                            ];

                            // Store match in database for fast retrieval on claim-verify page
                            try {
                                ItemMatch::updateOrCreate(
                                    [
                                        'user_item_upload_id' => $newItem->upload_id,
                                        'matched_item_upload_id' => $existingItem->upload_id,
                                    ],
                                    [
                                        'user_email' => $userEmail,
                                        'matched_item_owner_email' => $existingItem->uploader_email,
                                        'user_item_status' => $newItem->status,
                                        'matched_item_status' => $existingItem->status,
                                        'similarity_score' => $overallSimilarity,
                                        'visual_similarity' => $visualSimilarity,
                                        'text_similarity' => $textSimilarity,
                                        'is_notified' => (
                                            $overallSimilarity >= $visualThreshold ||
                                            ($visualSimilarity >= 0.70 && $textSimilarity >= 0.80)
                                        ), // also notify high-confidence semantic+visual matches
                                    ]
                                );

                                // Also create reverse match (bidirectional) so both users see the match
                                ItemMatch::updateOrCreate(
                                    [
                                        'user_item_upload_id' => $existingItem->upload_id,
                                        'matched_item_upload_id' => $newItem->upload_id,
                                    ],
                                    [
                                        'user_email' => $existingItem->uploader_email,
                                        'matched_item_owner_email' => $userEmail,
                                        'user_item_status' => $existingItem->status,
                                        'matched_item_status' => $newItem->status,
                                        'similarity_score' => $overallSimilarity,
                                        'visual_similarity' => $visualSimilarity,
                                        'text_similarity' => $textSimilarity,
                                        'is_notified' => (
                                            $overallSimilarity >= $visualThreshold ||
                                            ($visualSimilarity >= 0.70 && $textSimilarity >= 0.80)
                                        ),
                                    ]
                                );

                                Log::info('Match stored in database', [
                                    'user_item_upload_id' => $newItem->upload_id,
                                    'matched_item_upload_id' => $existingItem->upload_id,
                                    'similarity_score' => $overallSimilarity,
                                ]);
                            } catch (\Exception $e) {
                                Log::error('Failed to store match in database: '.$e->getMessage());
                            }

                            Log::info('Similar item found for user upload (opposite type match)', [
                                'new_item_status' => $newItemStatus,
                                'existing_item_status' => $existingItemStatus,
                                'existing_item' => $existingItem->original_name,
                                'similarity' => $overallSimilarity,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error calculating similarity for item: '.$existingItem->original_name, [
                            'error' => $e->getMessage(),
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
                'total_existing_items' => $totalExistingItems,
            ]);

            // Send notification to the user
            if (count($similarItems) > 0) {
                Log::info('Similar items found, sending notifications', [
                    'user_email' => $userEmail,
                    'similar_items_count' => count($similarItems),
                    'new_item_id' => $newItem->id,
                    'new_item_status' => $newItem->status,
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
                                'similarity_score' => $similarItem['similarity'] ?? 0,
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
                                        'similarity' => $similarItem['similarity'] ?? 0,
                                    ]);
                                    $this->sendUserSimilarityNotification($matchedItemOwnerEmail, $matchedItem, [
                                        [
                                            'description' => $newItem->description,
                                            'status' => $newItem->status,
                                            'uploader_email' => $newItem->uploader_email,
                                            'tags' => $newItem->tags,
                                            'similarity' => $similarItem['similarity'] ?? 0,
                                            'item_id' => $newItem->id,
                                            'upload_id' => $newItem->upload_id,
                                        ],
                                    ]);
                                    $this->createSimilarItemsNotification($matchedItemOwnerEmail, $matchedItem, [
                                        [
                                            'description' => $newItem->description,
                                            'status' => $newItem->status,
                                            'uploader_email' => $newItem->uploader_email,
                                            'tags' => $newItem->tags,
                                            'similarity' => $similarItem['similarity'] ?? 0,
                                            'item_id' => $newItem->id,
                                            'upload_id' => $newItem->upload_id,
                                        ],
                                    ]);
                                }
                                $notificationsSent[] = $matchedItemOwnerEmail;
                            } else {
                                Log::info('Items are similar but not matching criteria', [
                                    'new_item_status' => $newItem->status,
                                    'matched_item_status' => $matchedItem->status,
                                    'similarity' => $similarItem['similarity'] ?? 0,
                                ]);
                            }
                        } else {
                            Log::warning('Matched item not found in database', [
                                'upload_id' => $similarItem['upload_id'] ?? null,
                            ]);
                        }
                    }
                }
            } else {
                Log::info('No similar items found, sending upload confirmation', [
                    'user_email' => $userEmail,
                    'new_item_id' => $newItem->id,
                ]);
                $this->sendUserUploadConfirmation($userEmail, $newItem);
                $notificationsSent[] = $userEmail;
            }

            return [
                'similar_items_found' => count($similarItems),
                'notifications_sent' => count($notificationsSent),
                'emails_notified' => $notificationsSent,
                'similar_items' => $similarItems,
            ];

        } catch (\Exception $e) {
            Log::error('Error checking user item similarities: '.$e->getMessage());

            return [
                'similar_items_found' => 0,
                'notifications_sent' => 0,
                'emails_notified' => [],
                'similar_items' => [],
                'error' => $e->getMessage(),
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
            $path = storage_path('app/public/user-items/'.$filename);
        } elseif ($item->filename) {
            // Try reference-images path
            $path = storage_path('app/public/reference-images/'.$item->filename);
        }

        // If path still not found, try alternative methods
        if (! $path || ! file_exists($path)) {
            // Try using filename directly from file_path
            if ($item->file_path) {
                $filename = basename($item->file_path);
                // Try user-items first
                $altPath = storage_path('app/public/user-items/'.$filename);
                if (file_exists($altPath)) {
                    return $altPath;
                }
                // Try reference-images
                $altPath = storage_path('app/public/reference-images/'.$filename);
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
        if (! $this->areEmailNotificationsEnabled()) {
            Log::info('Email notifications disabled - skipping similarity notification', [
                'email' => $userEmail,
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
                'item_id' => $newItem->id,
            ];

            Mail::to($userEmail)->send(new UserItemNotification($data));

            Log::info('User similarity notification sent', [
                'email' => $userEmail,
                'similar_items_count' => count($similarItems),
                'mail_driver' => config('mail.default'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send user similarity notification', [
                'email' => $userEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Send upload confirmation to user
     */
    private function sendUserUploadConfirmation(string $userEmail, ImageMetadata $newItem): void
    {
        // Check if email notifications are enabled
        if (! $this->areEmailNotificationsEnabled()) {
            Log::info('Email notifications disabled - skipping upload confirmation', [
                'email' => $userEmail,
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
                'item_id' => $newItem->id,
            ];

            Mail::to($userEmail)->send(new UserItemNotification($data));

            Log::info('User upload confirmation sent', [
                'email' => $userEmail,
                'item_type' => $newItem->status,
                'mail_driver' => config('mail.default'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send user upload confirmation', [
                'email' => $userEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Create in-app notification for similar items found
     */
    private function createSimilarItemsNotification(string $userEmail, ImageMetadata $newItem, array $similarItems): void
    {
        try {
            $user = User::where('email', $userEmail)->first();
            if (! $user) {
                return;
            }

            $topSimilarity = 0.0;
            foreach ($similarItems as $item) {
                $score = (float) ($item['similarity'] ?? 0);
                if ($score > $topSimilarity) {
                    $topSimilarity = $score;
                }
            }
            $confidenceLevel = $this->getConfidenceLevel($topSimilarity);

            // Default behavior: only surface high-confidence match notifications in bell.
            if ($confidenceLevel !== 'high') {
                Log::info('Skipping non-high confidence bell notification', [
                    'user_email' => $userEmail,
                    'confidence' => $confidenceLevel,
                    'top_similarity' => round($topSimilarity * 100, 2),
                    'similar_items_count' => count($similarItems),
                ]);

                return;
            }

            Notification::create([
                'user_id' => $user->id,
                'type' => 'item_match',
                'title' => 'High-confidence match found!',
                'message' => 'We found '.count($similarItems).' high-confidence similar item(s) that might match your '.($newItem->status === 'lost' ? 'lost' : 'found').' item.',
                'data' => [
                    'upload_id' => $newItem->upload_id,
                    'item_type' => $newItem->status,
                    'confidence' => $confidenceLevel,
                    'top_similarity_percent' => round($topSimilarity * 100, 2),
                    'similar_items_count' => count($similarItems),
                    'similar_items' => array_map(function ($item) {
                        return [
                            'upload_id' => $item['upload_id'] ?? null,
                            'description' => $item['description'] ?? '',
                            'similarity' => $item['similarity'] ?? 0,
                            'confidence' => $this->getConfidenceLevel((float) ($item['similarity'] ?? 0)),
                        ];
                    }, $similarItems),
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create similar items notification: '.$e->getMessage());
        }
    }

    /**
     * Notify the owner of a matched item about the match
     */
    private function notifyMatchedItemOwner(string $ownerEmail, ImageMetadata $matchedItem, ImageMetadata $newItem, array $similarityData): void
    {
        // Check if email notifications are enabled
        if (! $this->areEmailNotificationsEnabled()) {
            Log::info('Email notifications disabled - skipping matched item owner notification', [
                'email' => $ownerEmail,
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
                'mail_driver' => config('mail.default'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send matched item owner notification', [
                'email' => $ownerEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Create in-app notification for matched item owner
     */
    private function createMatchedItemNotification(string $ownerEmail, ImageMetadata $matchedItem, ImageMetadata $newItem, array $similarityData): void
    {
        try {
            $owner = User::where('email', $ownerEmail)->first();
            if (! $owner) {
                return;
            }

            $rawSimilarity = (float) ($similarityData['similarity'] ?? 0);
            $similarityPercent = round($rawSimilarity * 100, 2);
            $confidenceLevel = $this->getConfidenceLevel($rawSimilarity);

            // Default behavior: only surface high-confidence match notifications in bell.
            if ($confidenceLevel !== 'high') {
                Log::info('Skipping non-high confidence matched-item bell notification', [
                    'owner_email' => $ownerEmail,
                    'confidence' => $confidenceLevel,
                    'similarity_percent' => $similarityPercent,
                    'matched_item_id' => $matchedItem->id,
                    'new_item_id' => $newItem->id,
                ]);

                return;
            }

            $matchType = ($matchedItem->status === 'lost' && $newItem->status === 'found') ?
                        'Someone found an item that matches your lost item!' :
                        'Someone lost an item that matches your found item!';

            Notification::create([
                'user_id' => $owner->id,
                'type' => 'item_matched',
                'title' => 'Item Match Found!',
                'message' => $matchType.' (Confidence: '.strtoupper($confidenceLevel).', Similarity: '.$similarityPercent.'%)',
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
                    'similarity_score' => $rawSimilarity,
                    'similarity_percent' => $similarityPercent,
                    'confidence' => $confidenceLevel,
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to create matched item notification: '.$e->getMessage());
        }
    }

    /**
     * Classify a match confidence label from normalized similarity score (0..1).
     */
    private function getConfidenceLevel(float $similarity): string
    {
        if ($similarity >= 0.80) {
            return 'high';
        }
        if ($similarity >= 0.65) {
            return 'medium';
        }

        return 'low';
    }
}
