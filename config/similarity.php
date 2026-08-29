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
    'visual_floor' => (float) env('SIMILARITY_VISUAL_FLOOR', 0.58),

    // All visual thresholds below use the rescaled score, not the raw hash score.
    'thresholds' => [
        // Minimum normalized visual for a normal match (~0.74 raw)
        'visual' => (float) env('SIMILARITY_MIN_VISUAL', 0.35),
        // Soft text floor used inside overall scoring penalties
        'text' => (float) env('SIMILARITY_MIN_TEXT', 0.25),
        // Store a match only if overall score reaches this
        'match' => (float) env('SIMILARITY_MATCH_THRESHOLD', 0.55),
        // Claim & Verify display filter
        'display' => (float) env('SIMILARITY_DISPLAY_THRESHOLD', 0.50),
        // Alternate path: strong text + decent visual (same item, different photo)
        'semantic_visual' => (float) env('SIMILARITY_SEMANTIC_VISUAL', 0.30),
        'semantic_text' => (float) env('SIMILARITY_SEMANTIC_TEXT', 0.75),
        // Photo alone is convincing enough (~0.88 raw)
        'strong_visual' => (float) env('SIMILARITY_STRONG_VISUAL', 0.70),
        // When both items have Vision labels, require at least this Jaccard overlap.
        'objects_min_overlap' => (float) env('SIMILARITY_OBJECTS_MIN_OVERLAP', 0.15),
        // Allow label mismatch only when normalized visual is this strong (near-duplicate photo).
        'objects_veto_override_visual' => (float) env('SIMILARITY_OBJECTS_VETO_OVERRIDE', 0.75),
        // Raw hash scores in this band (just above floor) are treated as weak / noisy.
        'raw_visual_borderline_max' => (float) env('SIMILARITY_RAW_BORDERLINE_MAX', 0.68),
        // Absolute minimum to show anywhere on Claim & Verify (unrelated pairs hidden).
        'minimum_display' => (float) env('SIMILARITY_MINIMUM_DISPLAY', 0.20),
        // Shown under "View below threshold": real overall % above this but under match threshold.
        'near_miss' => (float) env('SIMILARITY_NEAR_MISS_THRESHOLD', 0.28),
    ],

    // Stripped before text comparison so "lost item at mall" does not match unrelated listings.
    'generic_text_words' => [
        'a', 'an', 'the', 'and', 'or', 'at', 'in', 'on', 'to', 'for', 'of', 'with', 'my', 'your',
        'lost', 'found', 'missing', 'item', 'items', 'thing', 'things', 'object', 'objects',
        'personal', 'belonging', 'belongings', 'property', 'report', 'reported', 'please',
        'help', 'contact', 'return', 'reward', 'near', 'around', 'area', 'place', 'location',
        'mall', 'station', 'school', 'office', 'building', 'room', 'floor', 'left', 'leave',
        'someone', 'anyone', 'who', 'this', 'that', 'these', 'those', 'very', 'really',
    ],

    // How many below-threshold items to keep per scanned item
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
