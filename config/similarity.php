<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Similarity Checking Configuration
    |--------------------------------------------------------------------------
    |
    | Controls how lost/found images are compared and when matches are stored
    | or shown on Claim & Verify.
    |
    */

    'enabled' => env('SIMILARITY_ENABLED', true),

    // Overall score for high-confidence notifications (0–1)
    'threshold' => env('SIMILARITY_THRESHOLD', 0.68),

    'notification' => [
        'enabled' => env('SIMILARITY_NOTIFICATION_ENABLED', true),
        'email' => [
            'enabled' => env('SIMILARITY_EMAIL_ENABLED', true),
            'template' => 'emails.similar-image-notification',
        ],
    ],

    'weights' => [
        'visual' => (float) env('SIMILARITY_WEIGHT_VISUAL', 0.65),
        'text' => (float) env('SIMILARITY_WEIGHT_TEXT', 0.35),
    ],

    /*
    | Perceptual hashing never returns 0 for unrelated photos: two random product
    | shots on a white background typically land around 0.50–0.55 because half the
    | hash bits agree by chance. Everything at or below this floor is rescaled to 0
    | so the visual score below actually means "how alike", not "hash noise".
    */
    'visual_floor' => (float) env('SIMILARITY_VISUAL_FLOOR', 0.55),

    // All visual thresholds below use the rescaled score, not the raw hash score.
    'thresholds' => [
        // Minimum visual score for a normal match (~0.66 raw)
        'visual' => (float) env('SIMILARITY_MIN_VISUAL', 0.25),
        // Soft text floor used inside overall scoring penalties
        'text' => (float) env('SIMILARITY_MIN_TEXT', 0.25),
        // Store a match only if overall score reaches this
        'match' => (float) env('SIMILARITY_MATCH_THRESHOLD', 0.45),
        // Claim & Verify display filter
        'display' => (float) env('SIMILARITY_DISPLAY_THRESHOLD', 0.42),
        // Alternate path: strong text + decent visual (same item, different photo)
        'semantic_visual' => (float) env('SIMILARITY_SEMANTIC_VISUAL', 0.20),
        'semantic_text' => (float) env('SIMILARITY_SEMANTIC_TEXT', 0.70),
        // Photo alone is convincing enough (~0.84 raw)
        'strong_visual' => (float) env('SIMILARITY_STRONG_VISUAL', 0.65),
        // Soft object-label guidance
        'objects' => (float) env('SIMILARITY_MIN_OBJECTS', 0.15),
        // Shown under "View unmatched similar" when matching fails but score isn't pure noise
        'near_miss' => (float) env('SIMILARITY_NEAR_MISS_THRESHOLD', 0.08),
        // Raw hash lookalikes (studio shots of different items) still appear in that list
        'near_miss_raw_visual' => (float) env('SIMILARITY_NEAR_MISS_RAW_VISUAL', 0.48),
    ],

    // How many unmatched-but-similar items to keep per scanned item
    'near_miss_limit' => (int) env('SIMILARITY_NEAR_MISS_LIMIT', 12),

    'algorithms' => [
        'jaro_winkler_weight' => 0.4,
        'levenshtein_weight' => 0.3,
        'word_overlap_weight' => 0.3,
    ],

    'max_items_to_check' => (int) env('SIMILARITY_MAX_ITEMS', 500),

    'comparison' => [
        'method' => env('SIMILARITY_METHOD', 'basic'),
        'weight' => [
            'file_size' => env('SIMILARITY_WEIGHT_SIZE', 0.3),
            'filename' => env('SIMILARITY_WEIGHT_FILENAME', 0.2),
            'random' => env('SIMILARITY_WEIGHT_RANDOM', 0.5),
        ],
    ],
];
