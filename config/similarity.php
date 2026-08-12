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

    'thresholds' => [
        // Minimum visual score for a normal match
        'visual' => (float) env('SIMILARITY_MIN_VISUAL', 0.40),
        // Soft text floor used inside overall scoring penalties
        'text' => (float) env('SIMILARITY_MIN_TEXT', 0.25),
        // Store a match only if overall score reaches this
        'match' => (float) env('SIMILARITY_MATCH_THRESHOLD', 0.52),
        // Claim & Verify display filter
        'display' => (float) env('SIMILARITY_DISPLAY_THRESHOLD', 0.50),
        // Alternate path: strong text + decent visual (same item, different photo)
        'semantic_visual' => (float) env('SIMILARITY_SEMANTIC_VISUAL', 0.35),
        'semantic_text' => (float) env('SIMILARITY_SEMANTIC_TEXT', 0.65),
        // Soft object-label guidance
        'objects' => (float) env('SIMILARITY_MIN_OBJECTS', 0.15),
    ],

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
