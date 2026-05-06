<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use SapientPro\ImageComparator\ImageComparator;
use SapientPro\ImageComparator\ImageResourceException;
// Google Vision API now uses REST API with API key (no gRPC client needed)

class ImageComparisonController extends Controller
{
    public function __construct(
        private ImageComparator $imageComparator
    ) {}

    /**
     * Compare two images and return the similarity percentage
     */
    public function compare(Request $request): JsonResponse
    {
        $request->validate([
            'image1' => 'required|image|max:10240', // 10MB max
            'image2' => 'required|image|max:10240', // 10MB max
        ]);

        try {
            $image1Path = $request->file('image1')->getPathname();
            $image2Path = $request->file('image2')->getPathname();

            $similarity = $this->imageComparator->compare($image1Path, $image2Path);

            // The ImageComparator returns a percentage (0-100), so we just need to cap it
            $percentage = round(max(0, min(100, $similarity)), 2);

            return response()->json([
                'success' => true,
                'similarity' => $similarity,
                'similarity_percentage' => $percentage,
                'message' => 'Images compared successfully'
            ]);

        } catch (ImageResourceException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Image comparison failed: ' . $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare images from URLs
     */
    public function compareUrls(Request $request): JsonResponse
    {
        $request->validate([
            'url1' => 'required|url',
            'url2' => 'required|url',
        ]);

        try {
            $url1 = $request->input('url1');
            $url2 = $request->input('url2');

            $similarity = $this->imageComparator->compare($url1, $url2); 

            // The ImageComparator returns a percentage (0-100), so we just need to cap it
            $percentage = round(max(0, min(100, $similarity)), 2);

            return response()->json([
                'success' => true,
                'similarity' => $similarity,
                'similarity_percentage' => $percentage,
                'message' => 'Images compared successfully'
            ]);

        } catch (ImageResourceException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Image comparison failed: ' . $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare two images using Google Vision API
     */
    public function compareWithGoogleVision(Request $request): JsonResponse
    {
        $request->validate([
            'image1' => 'required|image|max:10240', // 10MB max
            'image2' => 'required|image|max:10240', // 10MB max
        ]);

        try {
            // Check if Google Vision is enabled
            $isEnabled = $this->isGoogleVisionEnabled();
            if (!$isEnabled) {
                return response()->json([
                    'success' => false,
                    'error' => 'Google Vision API is not enabled. Please enable it in admin settings.'
                ], 400);
            }

            $image1Path = $request->file('image1')->getPathname();
            $image2Path = $request->file('image2')->getPathname();

            // Get Google Vision results for both images
            $vision1 = $this->analyzeImageWithGoogleVision($image1Path);
            $vision2 = $this->analyzeImageWithGoogleVision($image2Path);

            // Calculate similarity based on Vision API features
            $similarity = $this->calculateVisionSimilarity($vision1, $vision2);

            return response()->json([
                'success' => true,
                'similarity' => $similarity,
                'similarity_percentage' => round($similarity * 100, 2),
                'vision_data' => [
                    'image1' => $vision1,
                    'image2' => $vision2,
                ],
                'message' => 'Images compared successfully using Google Vision API'
            ]);

        } catch (\Exception $e) {
            Log::error('Google Vision API error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Google Vision API error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze a single image using Google Vision API
     */
    public function analyzeImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:10240', // 10MB max
        ]);

        try {
            // Check if Google Vision is enabled
            $isEnabled = $this->isGoogleVisionEnabled();
            if (!$isEnabled) {
                return response()->json([
                    'success' => false,
                    'error' => 'Google Vision API is not enabled. Please enable it in admin settings.'
                ], 400);
            }

            $imagePath = $request->file('image')->getPathname();
            $visionData = $this->analyzeImageWithGoogleVision($imagePath);

            return response()->json([
                'success' => true,
                'message' => 'Image analyzed successfully using Google Vision API',
                'vision_data' => $visionData,
            ]);

        } catch (\Exception $e) {
            Log::error('Google Vision API error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Google Vision API error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare images from URLs using Google Vision API
     */
    public function compareUrlsWithGoogleVision(Request $request): JsonResponse
    {
        $request->validate([
            'url1' => 'required|url',
            'url2' => 'required|url',
        ]);

        try {
            $url1 = $request->input('url1');
            $url2 = $request->input('url2');

            // Download images from URLs
            $image1Content = file_get_contents($url1);
            $image2Content = file_get_contents($url2);

            if ($image1Content === false || $image2Content === false) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to download images from URLs'
                ], 400);
            }

            // Get Google Vision results
            $vision1 = $this->analyzeImageContentWithGoogleVision($image1Content);
            $vision2 = $this->analyzeImageContentWithGoogleVision($image2Content);

            // Calculate similarity
            $similarity = $this->calculateVisionSimilarity($vision1, $vision2);

            return response()->json([
                'success' => true,
                'similarity' => $similarity,
                'similarity_percentage' => round($similarity * 100, 2),
                'vision_data' => [
                    'image1' => $vision1,
                    'image2' => $vision2,
                ],
                'message' => 'Images compared successfully using Google Vision API'
            ]);

        } catch (\Exception $e) {
            Log::error('Google Vision API error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Google Vision API error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analyze image using Google Vision API
     */
    private function analyzeImageWithGoogleVision(string $imagePath): array
    {
        $tempFile = null;
        try {
            // Check if Google Vision is enabled
            $isEnabled = $this->isGoogleVisionEnabled();
            if (!$isEnabled) {
                throw new \Exception('Google Vision API is not enabled. Enable it in admin settings or set GOOGLE_VISION_ENABLED=true.');
            }

            $imageContent = file_get_contents($imagePath);
            return $this->analyzeImageContentWithGoogleVision($imageContent);
        } catch (\Exception $e) {
            Log::error('Error analyzing image with Google Vision: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Analyze image content using Google Vision API (REST API with API key)
     */
    private function analyzeImageContentWithGoogleVision(string $imageContent): array
    {
        try {
            // Check if Google Vision is enabled
            $isEnabled = $this->isGoogleVisionEnabled();
            if (!$isEnabled) {
                throw new \Exception('Google Vision API is not enabled. Enable it in admin settings or set GOOGLE_VISION_ENABLED=true.');
            }

            // Get API key from settings with env fallback
            $apiKey = $this->getGoogleVisionApiKey();
            
            if (empty($apiKey)) {
                throw new \Exception('Google Vision API key not configured. Save it in admin settings or set GOOGLE_VISION_API_KEY in .env.');
            }

            // Use REST API with API key
            $url = 'https://vision.googleapis.com/v1/images:annotate?key=' . urlencode($apiKey);
            
            $data = [
                'requests' => [
                    [
                        'image' => [
                            'content' => base64_encode($imageContent)
                        ],
                        'features' => [
                            ['type' => 'LABEL_DETECTION', 'maxResults' => 10],
                            ['type' => 'OBJECT_LOCALIZATION', 'maxResults' => 10],
                            ['type' => 'TEXT_DETECTION', 'maxResults' => 10],
                            ['type' => 'IMAGE_PROPERTIES'],
                            ['type' => 'SAFE_SEARCH_DETECTION'],
                        ]
                    ]
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode !== 200) {
                $errorData = json_decode($response, true);
                $errorMessage = $errorData['error']['message'] ?? ($curlError ?: 'Unknown error');
                throw new \Exception('Google Vision API error: ' . $errorMessage);
            }

            $responseData = json_decode($response, true);
            $annotations = $responseData['responses'][0] ?? [];

            // Extract labels
            $labels = [];
            if (isset($annotations['labelAnnotations'])) {
                foreach ($annotations['labelAnnotations'] as $label) {
                    $labels[] = [
                        'description' => $label['description'] ?? '',
                        'score' => $label['score'] ?? 0.0,
                    ];
                }
            }

            // Extract objects
            $objects = [];
            if (isset($annotations['localizedObjectAnnotations'])) {
                foreach ($annotations['localizedObjectAnnotations'] as $object) {
                    $objects[] = [
                        'name' => $object['name'] ?? '',
                        'score' => $object['score'] ?? 0.0,
                    ];
                }
            }

            // Extract text
            $texts = [];
            if (isset($annotations['textAnnotations']) && !empty($annotations['textAnnotations'])) {
                foreach ($annotations['textAnnotations'] as $text) {
                    $texts[] = [
                        'description' => $text['description'] ?? '',
                        'locale' => $text['locale'] ?? '',
                    ];
                }
            }

            // Extract image properties (dominant colors)
            $imageProperties = null;
            if (isset($annotations['imagePropertiesAnnotation']['dominantColors']['colors'])) {
                $dominantColors = [];
                foreach ($annotations['imagePropertiesAnnotation']['dominantColors']['colors'] as $color) {
                    $rgb = $color['color'] ?? [];
                    $dominantColors[] = [
                        'red' => $rgb['red'] ?? 0,
                        'green' => $rgb['green'] ?? 0,
                        'blue' => $rgb['blue'] ?? 0,
                        'score' => $color['score'] ?? 0.0,
                        'pixel_fraction' => $color['pixelFraction'] ?? 0.0,
                    ];
                }
                $imageProperties = [
                    'dominant_colors' => $dominantColors,
                ];
            }

            // Extract safe search
            $safeSearch = null;
            if (isset($annotations['safeSearchAnnotation'])) {
                $safeSearch = [
                    'adult' => $annotations['safeSearchAnnotation']['adult'] ?? 'UNKNOWN',
                    'spoof' => $annotations['safeSearchAnnotation']['spoof'] ?? 'UNKNOWN',
                    'medical' => $annotations['safeSearchAnnotation']['medical'] ?? 'UNKNOWN',
                    'violence' => $annotations['safeSearchAnnotation']['violence'] ?? 'UNKNOWN',
                    'racy' => $annotations['safeSearchAnnotation']['racy'] ?? 'UNKNOWN',
                ];
            }

            return [
                'labels' => $labels,
                'objects' => $objects,
                'texts' => $texts,
                'image_properties' => $imageProperties,
                'safe_search' => $safeSearch,
            ];
        } catch (\Exception $e) {
            Log::error('Google Vision API analysis error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate similarity between two Google Vision API results
     */
    private function calculateVisionSimilarity(array $vision1, array $vision2): float
    {
        $similarityScores = [];

        // Compare labels (weight: 40%)
        if (!empty($vision1['labels']) && !empty($vision2['labels'])) {
            $labelSimilarity = $this->compareLabels($vision1['labels'], $vision2['labels']);
            $similarityScores['labels'] = $labelSimilarity * 0.4;
        }

        // Compare objects (weight: 30%)
        if (!empty($vision1['objects']) && !empty($vision2['objects'])) {
            $objectSimilarity = $this->compareObjects($vision1['objects'], $vision2['objects']);
            $similarityScores['objects'] = $objectSimilarity * 0.3;
        }

        // Compare text (weight: 20%)
        if (!empty($vision1['texts']) && !empty($vision2['texts'])) {
            $textSimilarity = $this->compareTexts($vision1['texts'], $vision2['texts']);
            $similarityScores['texts'] = $textSimilarity * 0.2;
        }

        // Compare dominant colors (weight: 10%)
        if (!empty($vision1['image_properties']['dominant_colors']) && 
            !empty($vision2['image_properties']['dominant_colors'])) {
            $colorSimilarity = $this->compareColors(
                $vision1['image_properties']['dominant_colors'],
                $vision2['image_properties']['dominant_colors']
            );
            $similarityScores['colors'] = $colorSimilarity * 0.1;
        }

        // Sum all similarity scores
        $totalSimilarity = array_sum($similarityScores);

        // Normalize to 0-1 range
        return min(1.0, max(0.0, $totalSimilarity));
    }

    /**
     * Compare labels from two images
     */
    private function compareLabels(array $labels1, array $labels2): float
    {
        $descriptions1 = array_map(fn($label) => strtolower($label['description']), $labels1);
        $descriptions2 = array_map(fn($label) => strtolower($label['description']), $labels2);

        $common = array_intersect($descriptions1, $descriptions2);
        $total = array_unique(array_merge($descriptions1, $descriptions2));

        if (empty($total)) {
            return 0.0;
        }

        return count($common) / count($total);
    }

    /**
     * Compare objects from two images
     */
    private function compareObjects(array $objects1, array $objects2): float
    {
        $names1 = array_map(fn($obj) => strtolower($obj['name']), $objects1);
        $names2 = array_map(fn($obj) => strtolower($obj['name']), $objects2);

        $common = array_intersect($names1, $names2);
        $total = array_unique(array_merge($names1, $names2));

        if (empty($total)) {
            return 0.0;
        }

        return count($common) / count($total);
    }

    /**
     * Compare text from two images
     */
    private function compareTexts(array $texts1, array $texts2): float
    {
        if (empty($texts1) || empty($texts2)) {
            return 0.0;
        }

        // Get first text annotation (usually contains all detected text)
        $text1 = strtolower($texts1[0]['description'] ?? '');
        $text2 = strtolower($texts2[0]['description'] ?? '');

        if (empty($text1) || empty($text2)) {
            return 0.0;
        }

        // Use Jaccard similarity (word overlap)
        $words1 = array_filter(explode(' ', $text1));
        $words2 = array_filter(explode(' ', $text2));

        $common = array_intersect($words1, $words2);
        $total = array_unique(array_merge($words1, $words2));

        if (empty($total)) {
            return 0.0;
        }

        return count($common) / count($total);
    }

    /**
     * Compare dominant colors from two images
     */
    private function compareColors(array $colors1, array $colors2): float
    {
        if (empty($colors1) || empty($colors2)) {
            return 0.0;
        }

        // Get top 3 dominant colors from each image
        $topColors1 = array_slice($colors1, 0, 3);
        $topColors2 = array_slice($colors2, 0, 3);

        $totalSimilarity = 0.0;
        $comparisons = 0;

        foreach ($topColors1 as $color1) {
            $bestMatch = 0.0;
            foreach ($topColors2 as $color2) {
                // Calculate color distance (Euclidean distance in RGB space)
                $distance = sqrt(
                    pow($color1['red'] - $color2['red'], 2) +
                    pow($color1['green'] - $color2['green'], 2) +
                    pow($color1['blue'] - $color2['blue'], 2)
                );

                // Normalize to 0-1 similarity (max distance is sqrt(3 * 255^2))
                $maxDistance = sqrt(3 * pow(255, 2));
                $similarity = 1 - ($distance / $maxDistance);
                $bestMatch = max($bestMatch, $similarity);
            }
            $totalSimilarity += $bestMatch;
            $comparisons++;
        }

        return $comparisons > 0 ? $totalSimilarity / $comparisons : 0.0;
    }

    /**
     * Determine whether Google Vision is enabled via settings or env.
     */
    private function isGoogleVisionEnabled(): bool
    {
        $dbEnabled = \App\Models\Setting::get('google_vision_enabled', null);
        if ($dbEnabled !== null) {
            return (bool) $dbEnabled;
        }

        return filter_var(env('GOOGLE_VISION_ENABLED', false), FILTER_VALIDATE_BOOL);
    }

    /**
     * Read Google Vision API key from settings, fallback to env.
     */
    private function getGoogleVisionApiKey(): string
    {
        $dbKey = \App\Models\Setting::get('google_vision_api_key', '');
        if (!empty($dbKey)) {
            return trim((string) $dbKey);
        }

        return trim((string) env('GOOGLE_VISION_API_KEY', ''));
    }
}
