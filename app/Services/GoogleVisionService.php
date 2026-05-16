<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class GoogleVisionService
{
    /** Max labels stored and shown in the UI. */
    public const MAX_LABELS = 3;

    /**
     * Analyze an image and return the top item labels from Google Vision.
     *
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
            return (bool) $dbEnabled;
        }

        return filter_var(env('GOOGLE_VISION_ENABLED', false), FILTER_VALIDATE_BOOL);
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
     * @return array<int, array{name: string, score: float}>
     */
    private function fetchTopLabels(string $imagePath): array
    {
        $apiKey = $this->getApiKey();
        if ($apiKey === '') {
            throw new \RuntimeException('Google Vision API key not configured. Save it in Admin → Settings or set GOOGLE_VISION_API_KEY in .env.');
        }

        $url = 'https://vision.googleapis.com/v1/images:annotate?key='.urlencode($apiKey);

        $data = [
            'requests' => [
                [
                    'image' => [
                        'content' => base64_encode((string) file_get_contents($imagePath)),
                    ],
                    'features' => [
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

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
        $annotations = $responseData['responses'][0]['labelAnnotations'] ?? [];

        $minScore = 0.5;
        $out = [];

        foreach ($annotations as $label) {
            $name = trim((string) ($label['description'] ?? ''));
            $score = (float) ($label['score'] ?? 0);
            if ($name === '' || $score < $minScore) {
                continue;
            }
            $out[] = ['name' => $name, 'score' => round($score, 4)];
        }

        usort($out, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($out, 0, self::MAX_LABELS);
    }
}
