<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class GoogleVisionService
{
    /** Max labels stored and shown in the UI. */
    public const MAX_LABELS = 6;

    /**
     * Scene / material noise that rarely identifies the lost/found item itself.
     *
     * @var list<string>
     */
    private const BACKGROUND_NOISE = [
        'plastic',
        'metal',
        'wood',
        'hardwood',
        'softwood',
        'lumber',
        'plywood',
        'woodworking',
        'wood flooring',
        'reclaimed lumber',
        'floor',
        'flooring',
        'material',
        'product',
        'used',
        'font',
        'photography',
        'close-up',
        'macro photography',
        'still life photography',
        'room',
        'furniture',
        'table',
        'kitchen',
        'kitchen cabinet',
        'cabinet',
        'building',
        'us building products',
        'pattern',
        'circle',
        'line',
        'white',
        'black',
        'color',
        'melee weapon',
        'weapon',
        'bottled and jarred packaged goods',
    ];

    /**
     * Words that suggest a concrete personal item / product (not background scenery).
     *
     * @var list<string>
     */
    private const PRODUCT_HINTS = [
        'key',
        'keys',
        'pencil',
        'pen',
        'stylus',
        'phone',
        'iphone',
        'android',
        'laptop',
        'macbook',
        'airpods',
        'earbuds',
        'headphones',
        'watch',
        'wallet',
        'bag',
        'backpack',
        'umbrella',
        'glasses',
        'camera',
        'charger',
        'cable',
        'tablet',
        'ipad',
        'remote',
        'card',
        'passport',
        'toy',
        'bottle',
        'cup',
        'ring',
        'necklace',
        'bracelet',
        'shoe',
        'shoes',
        'hat',
        'jacket',
        'toyota',
        'honda',
        'samsung',
        'apple',
        'sony',
        'xiaomi',
        'huawei',
    ];

    /**
     * Analyze an image and return top item labels from Google Vision.
     *
     * Prefers brand / product signals (web entities, logos, OCR) when they agree
     * with what is in the photo; falls back to object/label detection otherwise.
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
                        ['type' => 'WEB_DETECTION', 'maxResults' => 15],
                        ['type' => 'LOGO_DETECTION', 'maxResults' => 5],
                        ['type' => 'TEXT_DETECTION', 'maxResults' => 10],
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

        $objects = [];
        foreach ($response0['localizedObjectAnnotations'] ?? [] as $object) {
            $name = $this->normalizeLabelName((string) ($object['name'] ?? ''));
            $score = (float) ($object['score'] ?? 0);
            if ($name === '' || $score < 0.40 || $this->isBackgroundNoise($name)) {
                continue;
            }
            $objects[] = ['name' => $name, 'score' => round($score, 4), 'source' => 'object'];
        }

        $labels = [];
        foreach ($response0['labelAnnotations'] ?? [] as $label) {
            $name = $this->normalizeLabelName((string) ($label['description'] ?? ''));
            $score = (float) ($label['score'] ?? 0);
            if ($name === '' || $score < 0.50 || $this->isBackgroundNoise($name)) {
                continue;
            }
            $labels[] = ['name' => $name, 'score' => round($score, 4), 'source' => 'label'];
        }

        $evidenceTokens = $this->tokenSet(array_merge(
            array_column($objects, 'name'),
            array_column($labels, 'name')
        ));

        $productSignals = [];

        foreach ($response0['logoAnnotations'] ?? [] as $logo) {
            $name = $this->normalizeLabelName((string) ($logo['description'] ?? ''));
            $score = (float) ($logo['score'] ?? 0);
            if ($name === '' || $score < 0.40) {
                continue;
            }
            $productSignals[] = [
                'name' => $name,
                'score' => round(max($score, 0.92), 4),
                'source' => 'logo',
            ];
        }

        $web = $response0['webDetection'] ?? [];

        foreach ($web['bestGuessLabels'] ?? [] as $guess) {
            $name = $this->normalizeLabelName((string) ($guess['label'] ?? ''));
            if ($name === '' || $this->isBackgroundNoise($name)) {
                continue;
            }
            // Accept best-guess only when it agrees with objects/labels, or no objects exist.
            if ($evidenceTokens !== [] && ! $this->sharesToken($name, $evidenceTokens) && ! $this->looksLikeProductName($name)) {
                continue;
            }
            if ($this->looksLikeProductName($name) || $this->sharesToken($name, $evidenceTokens) || $evidenceTokens === []) {
                $productSignals[] = [
                    'name' => $name,
                    'score' => 0.96,
                    'source' => 'web_guess',
                ];
            }
        }

        $webEntities = $web['webEntities'] ?? [];
        $maxWeb = 0.0;
        foreach ($webEntities as $entity) {
            $maxWeb = max($maxWeb, (float) ($entity['score'] ?? 0));
        }
        if ($maxWeb <= 0) {
            $maxWeb = 1.0;
        }

        foreach ($webEntities as $entity) {
            $name = $this->normalizeLabelName((string) ($entity['description'] ?? ''));
            $raw = (float) ($entity['score'] ?? 0);
            if ($name === '' || $raw <= 0 || $this->isBackgroundNoise($name)) {
                continue;
            }

            $normalized = $raw / $maxWeb;
            $isProduct = $this->looksLikeProductName($name);
            $agrees = $this->sharesToken($name, $evidenceTokens);

            // Keep web entities that look like products/brands or agree with photo evidence.
            if (! $isProduct && ! $agrees) {
                continue;
            }
            if ($normalized < 0.30 && ! $isProduct) {
                continue;
            }

            $boost = $isProduct ? 0.12 : 0.0;
            $productSignals[] = [
                'name' => $name,
                'score' => round(min(0.97, max(0.60, $normalized) + $boost), 4),
                'source' => 'web_entity',
            ];
        }

        foreach ($this->extractProductTextLabels($response0) as $textLabel) {
            if ($evidenceTokens !== [] && ! $this->sharesToken($textLabel['name'], $evidenceTokens) && ! $this->looksLikeProductName($textLabel['name'])) {
                // Still keep short brand-like OCR even without overlap.
                if (! preg_match('/^[A-Za-z0-9][A-Za-z0-9 &\-]{1,20}$/', $textLabel['name'])) {
                    continue;
                }
            }
            $productSignals[] = $textLabel + ['source' => 'text'];
        }

        // Build final list: product/brand first, then objects, then labels.
        $ranked = array_merge($productSignals, $objects, $labels);
        usort($ranked, function ($a, $b) {
            $rank = [
                'logo' => 1,
                'web_guess' => 2,
                'web_entity' => 3,
                'text' => 4,
                'object' => 5,
                'label' => 6,
            ];
            $ra = $rank[$a['source'] ?? 'label'] ?? 9;
            $rb = $rank[$b['source'] ?? 'label'] ?? 9;
            if ($ra !== $rb) {
                return $ra <=> $rb;
            }

            return $b['score'] <=> $a['score'];
        });

        $byName = [];
        foreach ($ranked as $row) {
            $key = strtolower($row['name']);
            if (isset($byName[$key])) {
                continue;
            }
            if ($this->isBackgroundNoise($row['name']) && ($row['source'] ?? '') !== 'object') {
                continue;
            }
            $byName[$key] = [
                'name' => $row['name'],
                'score' => $row['score'],
            ];
            if (count($byName) >= self::MAX_LABELS) {
                break;
            }
        }

        // If product signals wiped useful objects (e.g. Key), ensure top objects are present.
        foreach ($objects as $object) {
            if (count($byName) >= self::MAX_LABELS) {
                break;
            }
            $key = strtolower($object['name']);
            if (! isset($byName[$key])) {
                $byName[$key] = [
                    'name' => $object['name'],
                    'score' => $object['score'],
                ];
            }
        }

        return array_values($byName);
    }

    /**
     * @param  array<string, mixed>  $response0
     * @return list<array{name: string, score: float}>
     */
    private function extractProductTextLabels(array $response0): array
    {
        $annotations = $response0['textAnnotations'] ?? [];
        if ($annotations === []) {
            return [];
        }

        $out = [];
        foreach (array_slice($annotations, 1, 12) as $ann) {
            $text = trim((string) ($ann['description'] ?? ''));
            if ($text === '') {
                continue;
            }

            $wordCount = str_word_count($text);
            $len = mb_strlen($text);
            if ($wordCount < 1 || $wordCount > 4 || $len < 2 || $len > 32) {
                continue;
            }
            if (! preg_match('/[A-Za-z]/', $text)) {
                continue;
            }
            if ($this->isBackgroundNoise($text)) {
                continue;
            }

            $out[] = [
                'name' => $this->normalizeLabelName($text),
                'score' => 0.74,
            ];
        }

        return $out;
    }

    private function normalizeLabelName(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name) ?? $name);
        if ($name === '') {
            return '';
        }

        if (mb_strlen($name) <= 40 && ! preg_match('/[a-z].*[A-Z]/', $name)) {
            return mb_convert_case(mb_strtolower($name), MB_CASE_TITLE, 'UTF-8');
        }

        return $name;
    }

    private function isBackgroundNoise(string $name): bool
    {
        return in_array(mb_strtolower(trim($name)), self::BACKGROUND_NOISE, true);
    }

    /**
     * Prefer brand+product style names (Apple Pencil, Car Key) over scenery phrases.
     */
    private function looksLikeProductName(string $name): bool
    {
        $trimmed = trim($name);
        if ($trimmed === '' || $this->isBackgroundNoise($trimmed)) {
            return false;
        }

        $tokens = array_values(array_filter(
            preg_split('/[^a-z0-9]+/i', mb_strtolower($trimmed)) ?: [],
            static fn ($t) => mb_strlen($t) >= 2
        ));

        if ($tokens === []) {
            return false;
        }

        foreach ($tokens as $token) {
            if (in_array($token, self::PRODUCT_HINTS, true)) {
                return true;
            }
        }

        // Logo-like single brand token (short proper noun).
        if (count($tokens) === 1 && mb_strlen($trimmed) >= 3 && mb_strlen($trimmed) <= 14) {
            return (bool) preg_match('/^[A-Z][a-zA-Z0-9\-]+$/', $trimmed);
        }

        return false;
    }

    /**
     * @param  list<string>  $names
     * @return array<string, true>
     */
    private function tokenSet(array $names): array
    {
        $tokens = [];
        foreach ($names as $name) {
            foreach (preg_split('/[^a-z0-9]+/i', mb_strtolower($name)) ?: [] as $token) {
                if (mb_strlen($token) < 3) {
                    continue;
                }
                $tokens[$token] = true;
            }
        }

        return $tokens;
    }

    /**
     * @param  array<string, true>  $evidenceTokens
     */
    private function sharesToken(string $name, array $evidenceTokens): bool
    {
        if ($evidenceTokens === []) {
            return false;
        }

        foreach (preg_split('/[^a-z0-9]+/i', mb_strtolower($name)) ?: [] as $token) {
            if (mb_strlen($token) >= 3 && isset($evidenceTokens[$token])) {
                return true;
            }
        }

        return false;
    }
}
