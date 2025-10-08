<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Similarity Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the similarity notification system including
    | thresholds, weights, and email settings.
    |
    */

    'enabled' => env('SIMILARITY_ENABLED', true),

    'thresholds' => [
        'visual' => env('SIMILARITY_VISUAL_THRESHOLD', 0.6), // Lowered for testing - notify both parties when similarity found
        'text' => env('SIMILARITY_TEXT_THRESHOLD', 0.5), // Lowered for testing
    ],

    'notification' => [
        'min_similar_images' => env('SIMILARITY_MIN_SIMILAR_IMAGES', 2), // Minimum similar images required for notification
    ],

    'weights' => [
        'text' => env('SIMILARITY_TEXT_WEIGHT', 0.3),
        'visual' => env('SIMILARITY_VISUAL_WEIGHT', 0.7),
    ],

    'email' => [
        'enabled' => env('SIMILARITY_EMAIL_ENABLED', true),
        'from_name' => env('SIMILARITY_FROM_NAME', env('MAIL_FROM_NAME', 'Image Search System')),
        'from_address' => env('SIMILARITY_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'noreply@example.com')),
        'subject_prefix' => env('SIMILARITY_SUBJECT_PREFIX', '🔍 Similar Images Found'),
    ],

    'algorithms' => [
        'jaro_winkler_weight' => 0.4,
        'levenshtein_weight' => 0.3,
        'word_overlap_weight' => 0.3,
    ],

    'logging' => [
        'enabled' => env('SIMILARITY_LOGGING_ENABLED', true),
        'level' => env('SIMILARITY_LOG_LEVEL', 'info'),
    ],
];
