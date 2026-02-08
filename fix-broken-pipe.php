#!/usr/bin/env php
<?php

/**
 * Fix broken pipe error in Laravel's server.php
 * Run this script after composer install/update to suppress broken pipe errors
 */

$serverFile = __DIR__ . '/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php';

if (!file_exists($serverFile)) {
    echo "❌ server.php not found at: $serverFile\n";
    exit(1);
}

$content = file_get_contents($serverFile);

// Check if already fixed
if (strpos($content, '@file_put_contents') !== false) {
    echo "✅ server.php already has the fix applied\n";
    exit(0);
}

// Apply the fix
$oldLine = "file_put_contents('php://stdout', \"[{\$formattedDateTime}] {\$remoteAddress} [{\$requestMethod}] URI: {\$uri}\\n\");";
$newLine = "// Suppress broken pipe errors when writing to stdout\n@file_put_contents('php://stdout', \"[{\$formattedDateTime}] {\$remoteAddress} [{\$requestMethod}] URI: {\$uri}\\n\");";

if (strpos($content, $oldLine) !== false) {
    $content = str_replace($oldLine, $newLine, $content);
    file_put_contents($serverFile, $content);
    echo "✅ Fixed broken pipe error in server.php\n";
} else {
    // Try alternative pattern
    $pattern = "/file_put_contents\('php:\/\/stdout',.*?\);/s";
    if (preg_match($pattern, $content)) {
        $content = preg_replace(
            "/file_put_contents\('php:\/\/stdout',/",
            "// Suppress broken pipe errors\n@file_put_contents('php://stdout',",
            $content
        );
        file_put_contents($serverFile, $content);
        echo "✅ Fixed broken pipe error in server.php (alternative pattern)\n";
    } else {
        echo "⚠️  Could not find the file_put_contents line to fix\n";
        exit(1);
    }
}

echo "✅ Broken pipe error fix applied successfully!\n";
