<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class GoogleVisionService
{
    /** Max labels stored and shown in the UI. */
    public const MAX_LABELS = 6;

    /**
     * Analyze an image and return top item labels from Google Vision.
     *
     * Prefer localized object names (pen, wallet, phone) over generic scene labels.
     * Each entry: ['name' => string, 'score' => float]
     * Returns null when Vision is disabled or the call fails (upload should continue).
     */
    public function detectObjects(string $imagePath): ?array
    {
        if (! $this->isEnabled()) {
            Log::info('Google Vision skipped: disabled in settings/env');

            return null;
        }

        if (! is_readable($imagePath)) {
            Log::warning('Google Vision skipped: image not readable', ['path' => $imagePath]);

            return null;
        }

        try {
            $labels = $this->fetchTopLabels($imagePath);

            Log::info('Google Vision label detection completed', [
                'path' => $imagePath,
                'labels_count' => count($labels),
                'labels' => array_column($labels, 'name'),
            ]);

            return $labels === [] ? null : $labels;
        } catch (\Throwable $e) {
            Log::warning('Google Vision API analysis failed: '.$e->getMessage(), [
                'path' => $imagePath,
            ]);

            return null;
        }
    }

    public function isEnabled(): bool
    {
        $dbEnabled = Setting::get('google_vision_enabled', null);
        if ($dbEnabled !== null) {
            // Settings may be stored as string "1"/"0" or real bool — normalize safely.
            return filter_var($dbEnabled, FILTER_VALIDATE_BOOLEAN);
        }

        return filter_var(env('GOOGLE_VISION_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
    }

    public function getApiKey(): string
    {
        $dbKey = Setting::get('google_vision_api_key', '');
        if (! empty($dbKey)) {
            return trim((string) $dbKey);
        }

        return trim((string) env('GOOGLE_VISION_API_KEY', ''));
    }

    /**
     * Label an image and return labels, throwing on hard failures when \$strict is true.
     *
     * @return array<int, array{name: string, score: float}>|null
     */
    public function labelImage(string $imagePath, bool $strict = false): ?array
    {
        if (! $this->isEnabled()) {
            if ($strict) {
                throw new \RuntimeException('Google Vision is disabled. Enable it in Admin → Settings.');
            }
            Log::info('Google Vision skipped: disabled in settings/env');

            return null;
        }

        if ($this->getApiKey() === '') {
            if ($strict) {
                throw new \RuntimeException('Google Vision API key is missing.');
            }
            Log::warning('Google Vision skipped: API key missing');

            return null;
        }

        return $this->detectObjects($imagePath);
    }

    /**
     * @return array<int, array{name: string, score: float}>
     */
    private function fetchTopLabels(string $imagePath): array
    {
        $apiKey = $this->getApiKey();
        if ($apiKey === '') {
            throw new \RuntimeException('Google Vision API key not configured. Save it in Admin → Settings or set GOOGLE_VISION_API_KEY in .env.');
        }

        $contents = @file_get_contents($imagePath);
        if ($contents === false || $contents === '') {
            throw new \RuntimeException('Could not read image for Vision analysis.');
        }

        $url = 'https://vision.googleapis.com/v1/images:annotate?key='.urlencode($apiKey);

        $data = [
            'requests' => [
                [
                    'image' => [
                        'content' => base64_encode($contents),
                    ],
                    'features' => [
                        // Object names are more useful for matching (Pen, Wallet, Phone).
                        ['type' => 'OBJECT_LOCALIZATION', 'maxResults' => 10],
                        ['type' => 'LABEL_DETECTION', 'maxResults' => 10],
                    ],
                ],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        if (PHP_VERSION_ID < 80500) {
            curl_close($ch);
        }

        if ($httpCode !== 200) {
            $errorData = json_decode((string) $response, true);
            $errorMessage = $errorData['error']['message'] ?? ($curlError ?: 'Unknown error');
            throw new \RuntimeException('Google Vision API error: '.$errorMessage);
        }

        $responseData = json_decode((string) $response, true);
        $response0 = $responseData['responses'][0] ?? [];

        if (! empty($response0['error']['message'])) {
            throw new \RuntimeException('Google Vision API error: '.$response0['error']['message']);
        }

        $byName = [];

        // Prefer localized objects first (more specific item names).
        foreach ($response0['localizedObjectAnnotations'] ?? [] as $object) {
            $name = trim((string) ($object['name'] ?? ''));
            $score = (float) ($object['score'] ?? 0);
            if ($name === '' || $score < 0.40) {
                continue;
            }
            $key = strtolower($name);
            if (! isset($byName[$key]) || $score > $byName[$key]['score']) {
                $byName[$key] = ['name' => $name, 'score' => round($score, 4)];
            }
        }

        foreach ($response0['labelAnnotations'] ?? [] as $label) {
            $name = trim((string) ($label['description'] ?? ''));
            $score = (float) ($label['score'] ?? 0);
            if ($name === '' || $score < 0.50) {
                continue;
            }
            $key = strtolower($name);
            if (! isset($byName[$key]) || $score > $byName[$key]['score']) {
                $byName[$key] = ['name' => $name, 'score' => round($score, 4)];
            }
        }

        $out = array_values($byName);
        usort($out, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($out, 0, self::MAX_LABELS);
    }
}
