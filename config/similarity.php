<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Similarity Checking Configuration
    |--------------------------------------------------------------------------
    |
    | Controls how lost/found images are compared and when matches are stored
    | or shown on Claim & Verify. Raise thresholds to reduce false positives.
    |
    */

    'enabled' => env('SIMILARITY_ENABLED', true),

    // Overall score required for high-confidence notifications (0–1)
    'threshold' => env('SIMILARITY_THRESHOLD', 0.75),

    'notification' => [
        'enabled' => env('SIMILARITY_NOTIFICATION_ENABLED', true),
        'email' => [
            'enabled' => env('SIMILARITY_EMAIL_ENABLED', true),
            'template' => 'emails.similar-image-notification',
        ],
    ],

    'weights' => [
        'visual' => (float) env('SIMILARITY_WEIGHT_VISUAL', 0.70),
        'text' => (float) env('SIMILARITY_WEIGHT_TEXT', 0.30),
    ],

    'thresholds' => [
        // Minimum visual score to accept a stored/display match
        'visual' => (float) env('SIMILARITY_MIN_VISUAL', 0.62),
        // Soft text floor used inside overall scoring penalties
        'text' => (float) env('SIMILARITY_MIN_TEXT', 0.35),
        // Store a match only if overall score reaches this
        'match' => (float) env('SIMILARITY_MATCH_THRESHOLD', 0.72),
        // Claim & Verify display filter (normally same as match)
        'display' => (float) env('SIMILARITY_DISPLAY_THRESHOLD', 0.72),
        // Alternate path: strong text + solid visual (same item, different photo)
        'semantic_visual' => (float) env('SIMILARITY_SEMANTIC_VISUAL', 0.55),
        'semantic_text' => (float) env('SIMILARITY_SEMANTIC_TEXT', 0.80),
        // When both items have Vision labels, require some overlap
        'objects' => (float) env('SIMILARITY_MIN_OBJECTS', 0.20),
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
