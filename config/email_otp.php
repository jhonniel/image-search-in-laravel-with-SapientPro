<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Email OTP (signup verification)
    |--------------------------------------------------------------------------
    */

    'length' => (int) env('EMAIL_OTP_LENGTH', 6),

    // Minutes until the code expires
    'expires_minutes' => (int) env('EMAIL_OTP_EXPIRES_MINUTES', 15),

    // Max failed attempts before the code is invalidated
    'max_attempts' => (int) env('EMAIL_OTP_MAX_ATTEMPTS', 5),

    // Minimum seconds between resend requests
    'resend_cooldown_seconds' => (int) env('EMAIL_OTP_RESEND_COOLDOWN', 60),
];
