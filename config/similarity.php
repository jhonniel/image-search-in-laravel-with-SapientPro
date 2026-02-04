<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Similarity Checking Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the image similarity checking
    | and notification system.
    |
    */

    'enabled' => env('SIMILARITY_ENABLED', true),

    'threshold' => env('SIMILARITY_THRESHOLD', 0.7),

    'notification' => [
        'enabled' => env('SIMILARITY_NOTIFICATION_ENABLED', true),
        'email' => [
            'enabled' => env('SIMILARITY_EMAIL_ENABLED', true),
            'template' => 'emails.similar-image-notification',
        ],
    ],

    'comparison' => [
        'method' => env('SIMILARITY_METHOD', 'basic'),
        'weight' => [
            'file_size' => env('SIMILARITY_WEIGHT_SIZE', 0.3),
            'filename' => env('SIMILARITY_WEIGHT_FILENAME', 0.2),
            'random' => env('SIMILARITY_WEIGHT_RANDOM', 0.5),
        ],
    ],
];
