<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use SapientPro\ImageComparator\ImageComparator;
use SapientPro\ImageComparator\ImageResourceException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\ImageMetadata;
use App\Services\SimilarityNotificationService;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="SapientPro Image Comparison API",
 *     description="RESTful API for comparing images using SapientPro ImageComparator",
 *     @OA\Contact(
 *         email="support@example.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Local Development Server"
 * )
 *
 * @OA\Tag(
 *     name="Image Comparison",
 *     description="API Endpoints for image comparison operations"
 * )
 *
 * @OA\Tag(
 *     name="System",
 *     description="API system endpoints"
 * )
 */

class ImageComparisonApiController extends Controller
{
    public function __construct(
        private ImageComparator $imageComparator,
        private SimilarityNotificationService $similarityNotificationService
    ) {}

    /**
     * Compare two uploaded images
     *
     * @OA\Post(
     *     path="/compare/upload",
     *     operationId="compareUploadedImages",
     *     tags={"Image Comparison"},
     *     summary="Compare two uploaded images",
     *     description="Upload two image files and get their similarity percentage",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="image1",
     *                     type="string",
     *                     format="binary",
     *                     description="First image file (max 10MB)"
     *                 ),
     *                 @OA\Property(
     *                     property="image2",
     *                     type="string",
     *                     format="binary",
     *                     description="Second image file (max 10MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Images compared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="similarity", type="number", format="float", example=0.85),
     *                 @OA\Property(property="similarity_percentage", type="number", format="float", example=85.0),
     *                 @OA\Property(property="comparison_id", type="string", example="comp_abc123def4"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
     *                 @OA\Property(property="method", type="string", example="upload")
     *             ),
     *             @OA\Property(property="message", type="string", example="Images compared successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="image1",
     *                     type="array",
     *                     @OA\Items(type="string", example="The image1 field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Internal server error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function compareUploadedImages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image1' => 'required|image|max:10240', // 10MB max
            'image2' => 'required|image|max:10240', // 10MB max
        ], [
            'image1.required' => 'First image is required',
            'image1.image' => 'First file must be an image',
            'image1.max' => 'First image must not exceed 10MB',
            'image2.required' => 'Second image is required',
            'image2.image' => 'Second file must be an image',
            'image2.max' => 'Second image must not exceed 10MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $image1Path = $request->file('image1')->getPathname();
            $image2Path = $request->file('image2')->getPathname();

            $similarity = $this->imageComparator->compare($image1Path, $image2Path);

            return response()->json([
                'success' => true,
                'data' => [
                    'similarity' => $similarity,
                    'similarity_percentage' => round($similarity, 2),
                    'comparison_id' => 'comp_' . Str::random(10),
                    'timestamp' => now()->toISOString(),
                    'method' => 'upload'
                ],
                'message' => 'Images compared successfully'
            ]);

        } catch (ImageResourceException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Image comparison failed',
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Compare images from URLs
     *
     * @OA\Post(
     *     path="/compare/urls",
     *     operationId="compareImagesFromUrls",
     *     tags={"Image Comparison"},
     *     summary="Compare images from URLs",
     *     description="Compare two images by providing their URLs",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"url1","url2"},
     *             @OA\Property(
     *                 property="url1",
     *                 type="string",
     *                 format="uri",
     *                 description="First image URL",
     *                 example="https://example.com/image1.jpg"
     *             ),
     *             @OA\Property(
     *                 property="url2",
     *                 type="string",
     *                 format="uri",
     *                 description="Second image URL",
     *                 example="https://example.com/image2.jpg"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Images compared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="similarity", type="number", format="float", example=0.75),
     *                 @OA\Property(property="similarity_percentage", type="number", format="float", example=75.0),
     *                 @OA\Property(property="comparison_id", type="string", example="comp_xyz789abc1"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
     *                 @OA\Property(property="method", type="string", example="url"),
     *                 @OA\Property(
     *                     property="urls",
     *                     type="object",
     *                     @OA\Property(property="url1", type="string", example="https://example.com/image1.jpg"),
     *                     @OA\Property(property="url2", type="string", example="https://example.com/image2.jpg")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Images compared successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="url1",
     *                     type="array",
     *                     @OA\Items(type="string", example="The url1 field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Internal server error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function compareImagesFromUrls(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url1' => 'required|url',
            'url2' => 'required|url',
        ], [
            'url1.required' => 'First image URL is required',
            'url1.url' => 'First URL must be a valid URL',
            'url2.required' => 'Second image URL is required',
            'url2.url' => 'Second URL must be a valid URL',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $url1 = $request->input('url1');
            $url2 = $request->input('url2');

            $similarity = $this->imageComparator->compare($url1, $url2);

            return response()->json([
                'success' => true,
                'data' => [
                    'similarity' => $similarity,
                    'similarity_percentage' => round($similarity, 2),
                    'comparison_id' => 'comp_' . Str::random(10),
                    'timestamp' => now()->toISOString(),
                    'method' => 'url',
                    'urls' => [
                        'url1' => $url1,
                        'url2' => $url2
                    ]
                ],
                'message' => 'Images compared successfully'
            ]);

        } catch (ImageResourceException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Image comparison failed',
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Compare multiple images in batch
     *
     * @OA\Post(
     *     path="/compare/batch",
     *     operationId="batchCompare",
     *     tags={"Image Comparison"},
     *     summary="Compare multiple images in batch",
     *     description="Upload multiple images (2-5) and compare all pairs",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     ),
     *                     description="Array of image files (2-5 images, 10MB each)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Batch comparison completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="comparisons",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="image1", type="string", example="image1.jpg"),
     *                         @OA\Property(property="image2", type="string", example="image2.jpg"),
     *                         @OA\Property(property="similarity", type="number", format="float", example=0.85),
     *                         @OA\Property(property="similarity_percentage", type="number", format="float", example=85.0)
     *                     )
     *                 ),
     *                 @OA\Property(property="batch_id", type="string", example="batch_def456ghi7"),
     *                 @OA\Property(property="total_comparisons", type="integer", example=3),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
     *                 @OA\Property(property="method", type="string", example="batch")
     *             ),
     *             @OA\Property(property="message", type="string", example="Batch comparison completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(type="string", example="At least 2 images are required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Internal server error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function batchCompare(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:2|max:5',
            'images.*' => 'required|image|max:10240',
        ], [
            'images.required' => 'Images array is required',
            'images.array' => 'Images must be an array',
            'images.min' => 'At least 2 images are required',
            'images.max' => 'Maximum 5 images allowed',
            'images.*.required' => 'Each image is required',
            'images.*.image' => 'Each file must be an image',
            'images.*.max' => 'Each image must not exceed 10MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $images = $request->file('images');
            $comparisons = [];
            $batchId = 'batch_' . Str::random(10);

            // Compare each pair of images
            for ($i = 0; $i < count($images) - 1; $i++) {
                for ($j = $i + 1; $j < count($images); $j++) {
                    $image1Path = $images[$i]->getPathname();
                    $image2Path = $images[$j]->getPathname();

                    $similarity = $this->imageComparator->compare($image1Path, $image2Path);

                    $comparisons[] = [
                        'image1' => $images[$i]->getClientOriginalName(),
                        'image2' => $images[$j]->getClientOriginalName(),
                        'similarity' => $similarity,
                        'similarity_percentage' => round($similarity, 2)
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'comparisons' => $comparisons,
                    'batch_id' => $batchId,
                    'total_comparisons' => count($comparisons),
                    'timestamp' => now()->toISOString(),
                    'method' => 'batch'
                ],
                'message' => 'Batch comparison completed'
            ]);

        } catch (ImageResourceException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Batch comparison failed',
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Get API health status
     *
     * @OA\Get(
     *     path="/health",
     *     operationId="getHealth",
     *     tags={"System"},
     *     summary="Get API health status",
     *     description="Check if the API and its services are running properly",
     *     @OA\Response(
     *         response=200,
     *         description="API is healthy",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="status", type="string", example="healthy"),
     *                 @OA\Property(property="version", type="string", example="1.0.0"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
     *                 @OA\Property(
     *                     property="services",
     *                     type="object",
     *                     @OA\Property(property="image_comparator", type="string", example="available")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="API is healthy")
     *         )
     *     )
     * )
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'status' => 'healthy',
                'version' => '1.0.0',
                'timestamp' => now()->toISOString(),
                'services' => [
                    'image_comparator' => 'available'
                ]
            ],
            'message' => 'API is healthy'
        ]);
    }

    /**
     * Find matching images from stored reference images
     *
     * @OA\Post(
     *     path="/compare/find-match",
     *     operationId="findMatchingImages",
     *     tags={"Image Comparison"},
     *     summary="Find matching images from stored reference images",
     *     description="Upload one or more images and find similar matches from the stored reference images collection",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     ),
     *                     description="Array of image files to match against stored images (max 10MB each, max 5 images)"
     *                 ),
     *                 @OA\Property(
     *                     property="threshold",
     *                     type="number",
     *                     format="float",
     *                     description="Similarity threshold (0.0-1.0, default: 0.7)",
     *                     example=0.7
     *                 ),
     *                 @OA\Property(
     *                     property="limit",
     *                     type="integer",
     *                     description="Maximum number of matches to return (default: 10)",
     *                     example=10
     *                 ),
     *                 @OA\Property(
     *                     property="text_threshold",
     *                     type="number",
     *                     format="float",
     *                     description="Text similarity threshold (0.0-1.0, default: 0.5)",
     *                     example=0.5
     *                 ),
     *                 @OA\Property(
     *                     property="text_weight",
     *                     type="number",
     *                     format="float",
     *                     description="Weight of text similarity in overall score (0.0-1.0, default: 0.3)",
     *                     example=0.3
     *                 ),
     *                 @OA\Property(
     *                     property="search_text",
     *                     type="string",
     *                     description="Optional text to search for in descriptions and tags",
     *                     example="sunset landscape"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Matching images found successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="matches",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="uploaded_image", type="string", example="uploaded_image_1.jpg"),
     *                         @OA\Property(property="reference_filename", type="string", example="reference_image_1.jpg"),
     *                         @OA\Property(property="visual_similarity", type="number", format="float", example=0.85),
     *                         @OA\Property(property="visual_similarity_percentage", type="number", format="float", example=85.0),
     *                         @OA\Property(property="overall_similarity", type="number", format="float", example=0.82),
     *                         @OA\Property(property="overall_similarity_percentage", type="number", format="float", example=82.0),
     *                         @OA\Property(property="path", type="string", example="storage/reference-images/reference_image_1.jpg"),
     *                         @OA\Property(
     *                             property="metadata",
     *                             type="object",
     *                             @OA\Property(property="description", type="string", example="Beautiful sunset landscape"),
     *                             @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"sunset", "landscape", "nature"}),
     *                             @OA\Property(property="description_similarity", type="number", format="float", example=75.5),
     *                             @OA\Property(property="tags_similarity", type="number", format="float", example=80.0),
     *                             @OA\Property(property="text_similarity", type="number", format="float", example=80.0)
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="total_matches", type="integer", example=3),
     *                 @OA\Property(property="visual_threshold_used", type="number", format="float", example=0.7),
     *                 @OA\Property(property="text_threshold_used", type="number", format="float", example=0.5),
     *                 @OA\Property(property="text_weight_used", type="number", format="float", example=0.3),
     *                 @OA\Property(property="search_text", type="string", example="sunset landscape"),
     *                 @OA\Property(property="uploaded_images_count", type="integer", example=2),
     *                 @OA\Property(property="search_id", type="string", example="search_abc123def4"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-01T12:00:00Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Found 3 matching images")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(type="string", example="The images field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Internal server error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function findMatchingImages(Request $request): JsonResponse
    {
        try {
            Log::info('Find matching images request received', [
                'request_data' => $request->except(['images']), // Log everything except file data
                'images_count' => $request->hasFile('images') ? count($request->file('images')) : 0
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging find matching images request', ['error' => $e->getMessage()]);
        }

        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'required|image|max:10240', // 10MB max per image
            'threshold' => 'sometimes|numeric|min:0|max:1',
            'limit' => 'sometimes|integer|min:1|max:50',
            'text_threshold' => 'sometimes|numeric|min:0|max:1',
            'text_weight' => 'sometimes|numeric|min:0|max:1',
            'search_text' => 'sometimes|string|max:1000',
            'uploaded_descriptions' => 'sometimes|array|max:5',
            'uploaded_descriptions.*' => 'nullable|string|max:1000',
            'uploaded_tags' => 'sometimes|array|max:5',
            'uploaded_tags.*' => 'nullable|string|max:255',
            'uploader_email' => 'nullable|email|max:255',
        ], [
            'images.required' => 'Images array is required',
            'images.array' => 'Images must be an array',
            'images.min' => 'At least one image is required',
            'images.max' => 'Maximum 5 images allowed',
            'images.*.required' => 'Each image is required',
            'images.*.image' => 'Each file must be an image',
            'images.*.max' => 'Each image must not exceed 10MB',
            'threshold.numeric' => 'Threshold must be a number',
            'threshold.min' => 'Threshold must be between 0 and 1',
            'threshold.max' => 'Threshold must be between 0 and 1',
            'limit.integer' => 'Limit must be an integer',
            'limit.min' => 'Limit must be at least 1',
            'limit.max' => 'Limit cannot exceed 50',
            'text_threshold.numeric' => 'Text threshold must be a number',
            'text_threshold.min' => 'Text threshold must be between 0 and 1',
            'text_threshold.max' => 'Text threshold must be between 0 and 1',
            'text_weight.numeric' => 'Text weight must be a number',
            'text_weight.min' => 'Text weight must be between 0 and 1',
            'text_weight.max' => 'Text weight must be between 0 and 1',
            'search_text.string' => 'Search text must be a string',
            'search_text.max' => 'Search text must not exceed 1000 characters',
            'uploaded_descriptions.array' => 'Uploaded descriptions must be an array',
            'uploaded_descriptions.max' => 'Maximum 5 uploaded descriptions allowed',
            'uploaded_descriptions.*.string' => 'Each uploaded description must be a string',
            'uploaded_descriptions.*.max' => 'Each uploaded description must not exceed 1000 characters',
            'uploaded_tags.array' => 'Uploaded tags must be an array',
            'uploaded_tags.max' => 'Maximum 5 uploaded tag arrays allowed',
            'uploaded_tags.*.string' => 'Each uploaded tag must be a string',
            'uploaded_tags.*.max' => 'Each uploaded tag must not exceed 255 characters',
            'uploader_email.email' => 'Uploader email must be a valid email address',
            'uploader_email.max' => 'Uploader email must not exceed 255 characters',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $uploadedImages = $request->file('images');
            $threshold = (float) $request->input('threshold', 0.7); // Visual similarity threshold
            $textThreshold = (float) $request->input('text_threshold', 0.5); // Text similarity threshold
            $textWeight = (float) $request->input('text_weight', 0.3); // Weight of text similarity in overall score
            $searchText = $request->input('search_text', ''); // Optional search text
            $limit = (int) $request->input('limit', 10);

            // Get uploaded image metadata
            $uploadedDescriptions = $request->input('uploaded_descriptions', []);
            $uploadedTags = $request->input('uploaded_tags', []);

            // Get all reference images from storage
            $referenceImagesPath = storage_path('app/public/reference-images');
            $allMatches = [];

            if (!is_dir($referenceImagesPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No reference images directory found',
                    'message' => 'Reference images directory does not exist'
                ], 404);
            }

            $referenceImages = glob($referenceImagesPath . '/*.{jpg,jpeg,png,gif,bmp}', GLOB_BRACE);

            if (empty($referenceImages)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No reference images found',
                    'message' => 'No images found in the reference images directory'
                ], 404);
            }

            // Get all metadata for reference images
            $metadataMap = [];
            $allMetadata = ImageMetadata::whereIn('filename', array_map('basename', $referenceImages))->get();
            foreach ($allMetadata as $metadata) {
                $metadataMap[$metadata->filename] = $metadata;
            }

            // Compare each uploaded image with each reference image
            foreach ($uploadedImages as $index => $uploadedImage) {
                $uploadedImagePath = $uploadedImage->getPathname();
                $uploadedImageName = $uploadedImage->getClientOriginalName();

                // Get uploaded image metadata for this image
                $uploadedDescription = $uploadedDescriptions[$index] ?? '';
                $uploadedTagsString = $uploadedTags[$index] ?? '';
                $uploadedTagsArray = !empty($uploadedTagsString) ?
                    array_map('trim', explode(',', $uploadedTagsString)) : [];

                foreach ($referenceImages as $referenceImagePath) {
                    try {
                        $referenceFilename = basename($referenceImagePath);
                        $metadata = $metadataMap[$referenceFilename] ?? null;

                        // Calculate visual similarity
                        $visualSimilarity = $this->imageComparator->compare($uploadedImagePath, $referenceImagePath);
                        $visualSimilarityDecimal = $visualSimilarity > 1 ? $visualSimilarity / 100 : $visualSimilarity;

                        // Calculate text similarity
                        $textSimilarity = 0.0;
                        $descriptionSimilarity = 0.0;
                        $tagsSimilarity = 0.0;
                        $uploadedDescriptionSimilarity = 0.0;
                        $uploadedTagsSimilarity = 0.0;

                        if ($metadata) {
                            // Compare with search text if provided
                            if (!empty($searchText)) {
                                if (!empty($metadata->description)) {
                                    $descriptionSimilarity = $this->calculateTextSimilarity($searchText, $metadata->description);
                                }
                                if (!empty($metadata->tags)) {
                                    $tagsText = implode(' ', $metadata->tags);
                                    $tagsSimilarity = $this->calculateTextSimilarity($searchText, $tagsText);
                                }
                            }

                            // Compare uploaded image metadata with reference image metadata
                            if (!empty($uploadedDescription)) {
                                if (!empty($metadata->description)) {
                                    $uploadedDescriptionSimilarity = $this->calculateTextSimilarity($uploadedDescription, $metadata->description);
                                }
                            }

                            if (!empty($uploadedTagsArray)) {
                                if (!empty($metadata->tags)) {
                                    $uploadedTagsText = implode(' ', $uploadedTagsArray);
                                    $referenceTagsText = implode(' ', $metadata->tags);
                                    $uploadedTagsSimilarity = $this->calculateTextSimilarity($uploadedTagsText, $referenceTagsText);
                                }
                            }

                            // Use the maximum similarity from all comparisons
                            $textSimilarity = max($descriptionSimilarity, $tagsSimilarity, $uploadedDescriptionSimilarity, $uploadedTagsSimilarity);
                        }

                        // Calculate overall similarity
                        $overallSimilarity = $this->calculateOverallSimilarity($visualSimilarityDecimal, $textSimilarity, $textWeight);

                        // Check if it meets the threshold (either visual or overall)
                        $meetsThreshold = $visualSimilarityDecimal >= $threshold;

                        // If text search is provided or uploaded metadata exists, also check text threshold
                        if ((!empty($searchText) || !empty($uploadedDescription) || !empty($uploadedTagsArray)) && $textSimilarity > 0) {
                            $meetsThreshold = $meetsThreshold && $textSimilarity >= $textThreshold;
                        }

                        if ($meetsThreshold) {
                            $match = [
                                'uploaded_image' => $uploadedImageName,
                                'reference_filename' => $referenceFilename,
                                'visual_similarity' => $visualSimilarityDecimal,
                                'visual_similarity_percentage' => round($visualSimilarityDecimal * 100, 2),
                                'overall_similarity' => $overallSimilarity,
                                'overall_similarity_percentage' => round($overallSimilarity * 100, 2),
                                'path' => 'storage/reference-images/' . $referenceFilename,
                                'uploaded_metadata' => [
                                    'description' => $uploadedDescription,
                                    'tags' => $uploadedTagsArray,
                                    'uploaded_description_similarity' => round($uploadedDescriptionSimilarity * 100, 2),
                                    'uploaded_tags_similarity' => round($uploadedTagsSimilarity * 100, 2)
                                ],
                                'reference_metadata' => null
                            ];

                            // Add reference metadata information
                            if ($metadata) {
                                $match['reference_metadata'] = [
                                    'description' => $metadata->description,
                                    'tags' => $metadata->tags,
                                    'description_similarity' => round($descriptionSimilarity * 100, 2),
                                    'tags_similarity' => round($tagsSimilarity * 100, 2),
                                    'text_similarity' => round($textSimilarity * 100, 2)
                                ];
                            }

                            $allMatches[] = $match;
                        }
                    } catch (ImageResourceException $e) {
                        // Skip images that can't be compared
                        continue;
                    }
                }
            }

            // Sort matches by overall similarity (highest first)
            usort($allMatches, function($a, $b) {
                return $b['overall_similarity'] <=> $a['overall_similarity'];
            });

            // Remove duplicates (same reference image matched by multiple uploaded images)
            $uniqueMatches = [];
            $seenReferences = [];

            foreach ($allMatches as $match) {
                if (!in_array($match['reference_filename'], $seenReferences)) {
                    $uniqueMatches[] = $match;
                    $seenReferences[] = $match['reference_filename'];
                }
            }

            // Limit results
            $matches = array_slice($uniqueMatches, 0, $limit);

            return response()->json([
                'success' => true,
                'data' => [
                    'matches' => $matches,
                    'total_matches' => count($matches),
                    'visual_threshold_used' => $threshold,
                    'text_threshold_used' => $textThreshold,
                    'text_weight_used' => $textWeight,
                    'search_text' => $searchText,
                    'uploaded_images_count' => count($uploadedImages),
                    'search_id' => 'search_' . Str::random(10),
                    'timestamp' => now()->toISOString()
                ],
                'message' => count($matches) > 0 ?
                    'Found ' . count($matches) . ' matching image(s) with ' . (count($uploadedImages) > 1 ? count($uploadedImages) . ' uploaded images' : '1 uploaded image') :
                    'No matching images found with the given thresholds'
            ]);

        } catch (ImageResourceException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Image comparison failed',
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Upload reference images for matching
     *
     * @OA\Post(
     *     path="/reference-images/upload",
     *     operationId="uploadReferenceImages",
     *     tags={"Image Management"},
     *     summary="Upload reference images",
     *     description="Upload one or more images to be used as reference for matching",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     ),
     *                     description="Array of image files to upload as references (max 10MB each, max 5 images)"
     *                 ),
     *                 @OA\Property(
     *                     property="descriptions[]",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         maxLength=1000
     *                     ),
     *                     description="Optional array of descriptions for each image (max 1000 chars each)"
     *                 ),
     *                 @OA\Property(
     *                     property="tags[]",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         maxLength=255
     *                     ),
     *                     description="Optional array of tags for each image (comma-separated string or array of strings)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reference images uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="uploaded_images",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="original_name", type="string", example="image1.jpg"),
     *                         @OA\Property(property="stored_name", type="string", example="image1_1234567890.jpg"),
     *                         @OA\Property(property="path", type="string", example="storage/reference-images/image1_1234567890.jpg"),
     *                         @OA\Property(property="size", type="integer", example=1024000),
     *                         @OA\Property(property="description", type="string", example="A beautiful sunset landscape"),
     *                         @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"sunset", "landscape", "nature"}),
     *                         @OA\Property(property="metadata_id", type="integer", example=123)
     *                     )
     *                 ),
     *                 @OA\Property(property="total_uploaded", type="integer", example=3),
     *                 @OA\Property(property="upload_id", type="string", example="upload_xyz789abc1"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-01T12:00:00Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Successfully uploaded 3 reference images")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(type="string", example="At least one image is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Internal server error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function uploadReferenceImages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'required|image|max:10240',
            'general_description' => 'nullable|string|max:1000',
            'general_tags' => 'nullable|string|max:255',
            'uploader_email' => 'nullable|email|max:255',
            'status' => 'sometimes|in:lost,found',
        ], [
            'images.required' => 'Images array is required',
            'images.array' => 'Images must be an array',
            'images.min' => 'At least one image is required',
            'images.max' => 'Maximum 5 images allowed per upload',
            'images.*.required' => 'Each image is required',
            'images.*.image' => 'Each file must be an image',
            'images.*.max' => 'Each image must not exceed 10MB',
            'general_description.string' => 'General description must be a string',
            'general_description.max' => 'General description must not exceed 1000 characters',
            'general_tags.string' => 'General tags must be a string',
            'general_tags.max' => 'General tags must not exceed 255 characters',
            'uploader_email.email' => 'Uploader email must be a valid email address',
            'uploader_email.max' => 'Uploader email must not exceed 255 characters',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $images = $request->file('images');
            $generalDescription = $request->input('general_description');
            $generalTags = $request->input('general_tags');
            $uploadedImages = [];
            $uploadId = 'upload_' . Str::random(10);
            $similarityResults = [];

            // Ensure directory exists
            $referenceImagesPath = storage_path('app/public/reference-images');
            if (!is_dir($referenceImagesPath)) {
                mkdir($referenceImagesPath, 0755, true);
            }

            foreach ($images as $index => $image) {
                $originalName = $image->getClientOriginalName();
                $extension = $image->getClientOriginalExtension();
                $timestamp = time();
                $randomString = Str::random(10);
                $storedName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . $timestamp . '_' . $randomString . '.' . $extension;

                // Get file size and MIME type before moving
                $fileSize = $image->getSize();
                $mimeType = $image->getMimeType();

                // Store the image
                $image->move($referenceImagesPath, $storedName);

                // Use general description and tags for all images
                $description = $generalDescription;
                $imageTags = $generalTags;

                // Parse tags if they're provided as a comma-separated string
                if (is_string($imageTags)) {
                    $imageTags = array_map('trim', explode(',', $imageTags));
                    $imageTags = array_filter($imageTags); // Remove empty tags
                }

                // Store metadata in database
                $metadata = ImageMetadata::create([
                    'filename' => $storedName,
                    'original_name' => $originalName,
                    'description' => $description,
                    'tags' => $imageTags,
                    'upload_id' => $uploadId,
                    'file_size' => $fileSize,
                    'mime_type' => $mimeType,
                    'uploader_email' => $request->input('uploader_email'),
                    'status' => $request->input('status', 'lost'), // Default to 'lost' if not provided
                ]);

                $uploadedImages[] = [
                    'original_name' => $originalName,
                    'stored_name' => $storedName,
                    'path' => 'storage/reference-images/' . $storedName,
                    'size' => $fileSize,
                    'description' => $description,
                    'tags' => $imageTags,
                    'metadata_id' => $metadata->id,
                ];

                // Check for similar images and send notifications
                try {
                    $newImageMetadata = [
                        'description' => $description,
                        'tags' => $imageTags,
                        'original_name' => $originalName
                    ];

                    $storedPath = storage_path('app/public/reference-images/' . $storedName);
                    $similarityResult = $this->similarityNotificationService->checkAndNotifySimilarImages(
                        $storedPath,
                        $newImageMetadata,
                        $request->input('uploader_email')
                    );

                    $similarityResults[] = [
                        'image_name' => $originalName,
                        'similar_images_found' => $similarityResult['similar_images_found'],
                        'notifications_sent' => $similarityResult['notifications_sent'],
                        'emails_notified' => $similarityResult['emails_notified']
                    ];

                    Log::info('Similarity check completed for image: ' . $originalName, $similarityResult);
                } catch (\Exception $e) {
                    Log::error('Error checking similarity for image: ' . $originalName . ' - ' . $e->getMessage());
                }
            }

            // Calculate total similarity statistics
            $totalSimilarFound = array_sum(array_column($similarityResults, 'similar_images_found'));
            $totalNotificationsSent = array_sum(array_column($similarityResults, 'notifications_sent'));
            $allEmailsNotified = array_unique(array_merge(...array_column($similarityResults, 'emails_notified')));

            return response()->json([
                'success' => true,
                'data' => [
                    'uploaded_images' => $uploadedImages,
                    'total_uploaded' => count($uploadedImages),
                    'upload_id' => $uploadId,
                    'similarity_check_results' => $similarityResults,
                    'similarity_summary' => [
                        'total_similar_images_found' => $totalSimilarFound,
                        'total_notifications_sent' => $totalNotificationsSent,
                        'unique_emails_notified' => count($allEmailsNotified),
                        'emails_notified' => $allEmailsNotified
                    ],
                    'timestamp' => now()->toISOString()
                ],
                'message' => 'Successfully uploaded ' . count($uploadedImages) . ' reference image(s)' .
                           ($totalSimilarFound > 0 ? ' and found ' . $totalSimilarFound . ' similar images' : '')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Upload failed',
                'message' => 'An unexpected error occurred while uploading images'
            ], 500);
        }
    }

    /**
     * List all reference images
     *
     * @OA\Get(
     *     path="/reference-images",
     *     operationId="listReferenceImages",
     *     tags={"Image Management"},
     *     summary="List all reference images",
     *     description="Get a list of all stored reference images",
     *     @OA\Response(
     *         response=200,
     *         description="Reference images retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="filename", type="string", example="image1_1234567890_abc123.jpg"),
     *                         @OA\Property(property="original_name", type="string", example="image1.jpg"),
     *                         @OA\Property(property="path", type="string", example="storage/reference-images/image1_1234567890_abc123.jpg"),
     *                         @OA\Property(property="size", type="integer", example=1024000),
     *                         @OA\Property(property="uploaded_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
     *                         @OA\Property(property="description", type="string", example="A beautiful sunset landscape"),
     *                         @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"sunset", "landscape", "nature"}),
     *                         @OA\Property(property="metadata_id", type="integer", example=123)
     *                     )
     *                 ),
     *                 @OA\Property(property="total_images", type="integer", example=15),
     *                 @OA\Property(property="total_size", type="string", example="15.2 MB"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-01T12:00:00Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Retrieved 15 reference images")
     *         )
     *     )
     * )
     */
    public function listReferenceImages(): JsonResponse
    {
        try {
            $referenceImagesPath = storage_path('app/public/reference-images');
            $images = [];
            $totalSize = 0;

            if (is_dir($referenceImagesPath)) {
                $files = glob($referenceImagesPath . '/*.{jpg,jpeg,png,gif,bmp}', GLOB_BRACE);

                foreach ($files as $file) {
                    $fileInfo = pathinfo($file);
                    $filename = $fileInfo['basename'];
                    $size = filesize($file);
                    $totalSize += $size;

                    // Get metadata from database
                    $metadata = ImageMetadata::where('filename', $filename)->first();

                    $imageData = [
                        'filename' => $filename,
                        'original_name' => $metadata ? $metadata->original_name : $filename,
                        'path' => 'storage/reference-images/' . $filename,
                        'size' => $size,
                        'uploaded_at' => $metadata ? $metadata->created_at->toISOString() : date('c', filemtime($file)),
                        'created_at' => $metadata ? $metadata->created_at->toISOString() : date('c', filemtime($file)),
                        'upload_id' => $metadata ? $metadata->upload_id : null,
                        'description' => $metadata ? $metadata->description : null,
                        'tags' => $metadata ? $metadata->tags : [],
                        'uploader_email' => $metadata ? $metadata->uploader_email : null,
                        'status' => $metadata ? $metadata->status : 'lost', // Default to 'lost' if no metadata
                        'metadata_id' => $metadata ? $metadata->id : null,
                    ];

                    // If no metadata exists, try to extract original name from filename
                    if (!$metadata) {
                        if (preg_match('/^(.+)_\d+_[a-zA-Z0-9]+\.(.+)$/', $filename, $matches)) {
                            $imageData['original_name'] = $matches[1] . '.' . $matches[2];
                        }
                    }

                    $images[] = $imageData;
                }

                // Sort by upload time (newest first)
                usort($images, function($a, $b) {
                    return strtotime($b['uploaded_at']) <=> strtotime($a['uploaded_at']);
                });
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'images' => $images,
                    'total_images' => count($images),
                    'total_size' => $this->formatBytes($totalSize),
                    'timestamp' => now()->toISOString()
                ],
                'message' => 'Retrieved ' . count($images) . ' reference image(s)'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve reference images',
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Delete reference images
     *
     * @OA\Delete(
     *     path="/reference-images/{filename}",
     *     operationId="deleteReferenceImage",
     *     tags={"Image Management"},
     *     summary="Delete a reference image",
     *     description="Delete a specific reference image by filename",
     *     @OA\Parameter(
     *         name="filename",
     *         in="path",
     *         required=true,
     *         description="Filename of the reference image to delete",
     *         @OA\Schema(type="string", example="image1_1234567890_abc123.jpg")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reference image deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="deleted_filename", type="string", example="image1_1234567890_abc123.jpg"),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-01T12:00:00Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Reference image deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reference image not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Reference image not found"),
     *             @OA\Property(property="message", type="string", example="The specified reference image does not exist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Internal server error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function deleteReferenceImage(string $filename): JsonResponse
    {
        try {
            $referenceImagesPath = storage_path('app/public/reference-images');
            $filePath = $referenceImagesPath . '/' . $filename;

            // Find the metadata record first
            $metadata = ImageMetadata::where('filename', $filename)->first();

            if (!$metadata) {
                return response()->json([
                    'success' => false,
                    'error' => 'Reference image metadata not found',
                    'message' => 'The specified reference image metadata does not exist in the database'
                ], 404);
            }

            // Delete the physical file if it exists
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Failed to delete reference image file',
                        'message' => 'Could not delete the reference image file from storage'
                    ], 500);
                }
            }

            // Delete the metadata record from database
            $metadata->delete();

            return response()->json([
                'success' => true,
                'data' => [
                    'deleted_filename' => $filename,
                    'deleted_metadata_id' => $metadata->id,
                    'timestamp' => now()->toISOString()
                ],
                'message' => 'Reference image and metadata deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Bulk delete reference images
     *
     * @OA\Delete(
     *     path="/reference-images/bulk",
     *     operationId="bulkDeleteReferenceImages",
     *     tags={"Image Management"},
     *     summary="Delete multiple reference images",
     *     description="Delete multiple reference images by their filenames",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="filenames",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     description="Array of filenames to delete"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bulk delete operation completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="deleted_count", type="integer", example=3),
     *                 @OA\Property(property="failed_count", type="integer", example=0),
     *                 @OA\Property(property="deleted_filenames", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="failed_filenames", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="timestamp", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string", example="Bulk delete operation completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function bulkDeleteReferenceImages(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filenames' => 'required|array|min:1|max:50',
            'filenames.*' => 'required|string|max:255',
        ], [
            'filenames.required' => 'Filenames array is required',
            'filenames.array' => 'Filenames must be an array',
            'filenames.min' => 'At least one filename is required',
            'filenames.max' => 'Maximum 50 filenames allowed per bulk delete',
            'filenames.*.required' => 'Each filename is required',
            'filenames.*.string' => 'Each filename must be a string',
            'filenames.*.max' => 'Each filename must not exceed 255 characters',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $filenames = $request->input('filenames');
        $referenceImagesPath = storage_path('app/public/reference-images');

        $deletedFilenames = [];
        $failedFilenames = [];

        foreach ($filenames as $filename) {
            $imagePath = $referenceImagesPath . '/' . $filename;

            try {
                // Find and delete the metadata record first
                $metadata = ImageMetadata::where('filename', $filename)->first();

                if ($metadata) {
                    $metadata->delete();
                }

                // Delete the physical file if it exists
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }

                $deletedFilenames[] = $filename;
            } catch (\Exception $e) {
                $failedFilenames[] = $filename;
                Log::error("Failed to delete reference image {$filename}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'deleted_count' => count($deletedFilenames),
                'failed_count' => count($failedFilenames),
                'deleted_filenames' => $deletedFilenames,
                'failed_filenames' => $failedFilenames,
                'timestamp' => now()->toISOString()
            ],
            'message' => 'Bulk delete operation completed'
        ]);
    }

    /**
     * Get API documentation
     *
     * @OA\Get(
     *     path="/docs",
     *     operationId="getDocumentation",
     *     tags={"System"},
     *     summary="Get API documentation",
     *     description="Retrieve comprehensive API documentation and endpoint information",
     *     @OA\Response(
     *         response=200,
     *         description="API documentation retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="api_name", type="string", example="SapientPro Image Comparison API"),
     *                 @OA\Property(property="version", type="string", example="1.0.0"),
     *                 @OA\Property(property="description", type="string", example="RESTful API for comparing images using SapientPro ImageComparator"),
     *                 @OA\Property(
     *                     property="endpoints",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="method", type="string", example="POST"),
     *                         @OA\Property(property="path", type="string", example="/api/v1/compare/upload"),
     *                         @OA\Property(property="description", type="string", example="Compare two uploaded images")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="API documentation retrieved successfully")
     *         )
     *     )
     * )
     */
    public function documentation(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'api_name' => 'SapientPro Image Comparison API',
                'version' => '1.0.0',
                'description' => 'RESTful API for comparing images using SapientPro ImageComparator',
                'endpoints' => [
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/compare/upload',
                        'description' => 'Compare two uploaded images',
                        'parameters' => ['image1 (file)', 'image2 (file)']
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/compare/urls',
                        'description' => 'Compare images from URLs',
                        'parameters' => ['url1 (string)', 'url2 (string)']
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/compare/batch',
                        'description' => 'Compare multiple images in batch',
                        'parameters' => ['images (array of files)']
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/compare/find-match',
                        'description' => 'Find matching images from stored reference images',
                        'parameters' => ['image (file)', 'threshold (optional)', 'limit (optional)']
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/v1/reference-images/upload',
                        'description' => 'Upload reference images for matching',
                        'parameters' => ['images (array of files)']
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/reference-images',
                        'description' => 'List all reference images'
                    ],
                    [
                        'method' => 'DELETE',
                        'path' => '/api/v1/reference-images/{filename}',
                        'description' => 'Delete a reference image'
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/health',
                        'description' => 'Get API health status'
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/v1/docs',
                        'description' => 'Get API documentation'
                    ]
                ],
                'rate_limits' => [
                    'requests_per_minute' => 60,
                    'file_size_limit' => '10MB per image',
                    'batch_limit' => '5 images per batch',
                    'reference_upload_limit' => '5 images per upload'
                ]
            ],
            'message' => 'API documentation retrieved successfully'
        ]);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Calculate text similarity between two strings using multiple algorithms
     */
    public function calculateTextSimilarity(string $text1, string $text2): float
    {
        if (empty($text1) || empty($text2)) {
            return 0.0;
        }

        // Normalize text (lowercase, trim)
        $text1 = strtolower(trim($text1));
        $text2 = strtolower(trim($text2));

        if ($text1 === $text2) {
            return 1.0; // Perfect match
        }

        // Calculate Jaro-Winkler similarity
        $jaroWinkler = $this->jaroWinklerSimilarity($text1, $text2);

        // Calculate Levenshtein similarity
        $levenshtein = $this->levenshteinSimilarity($text1, $text2);

        // Calculate word overlap similarity
        $wordOverlap = $this->wordOverlapSimilarity($text1, $text2);

        // Weighted average of different similarity measures
        $similarity = ($jaroWinkler * 0.4) + ($levenshtein * 0.3) + ($wordOverlap * 0.3);

        return min(1.0, max(0.0, $similarity));
    }

    /**
     * Calculate Jaro-Winkler similarity
     */
    public function jaroWinklerSimilarity(string $s1, string $s2): float
    {
        $len1 = strlen($s1);
        $len2 = strlen($s2);

        if ($len1 === 0 || $len2 === 0) {
            return 0.0;
        }

        $matchWindow = max($len1, $len2) / 2 - 1;
        $matchWindow = max(0, floor($matchWindow));

        $s1Matches = array_fill(0, $len1, false);
        $s2Matches = array_fill(0, $len2, false);

        $matches = 0;
        $transpositions = 0;

        // Find matches
        for ($i = 0; $i < $len1; $i++) {
            $start = max(0, $i - $matchWindow);
            $end = min($i + $matchWindow + 1, $len2);

            for ($j = $start; $j < $end; $j++) {
                if ($s2Matches[$j] || $s1[$i] !== $s2[$j]) {
                    continue;
                }
                $s1Matches[$i] = true;
                $s2Matches[$j] = true;
                $matches++;
                break;
            }
        }

        if ($matches === 0) {
            return 0.0;
        }

        // Count transpositions
        $k = 0;
        for ($i = 0; $i < $len1; $i++) {
            if (!$s1Matches[$i]) {
                continue;
            }
            while (!$s2Matches[$k]) {
                $k++;
            }
            if ($s1[$i] !== $s2[$k]) {
                $transpositions++;
            }
            $k++;
        }

        $jaro = ($matches / $len1 + $matches / $len2 + ($matches - $transpositions / 2) / $matches) / 3;

        // Winkler modification
        if ($jaro < 0.7) {
            return $jaro;
        }

        $prefix = 0;
        $prefixLimit = min(4, min($len1, $len2));
        for ($i = 0; $i < $prefixLimit; $i++) {
            if ($s1[$i] === $s2[$i]) {
                $prefix++;
            } else {
                break;
            }
        }

        return $jaro + (0.1 * $prefix * (1 - $jaro));
    }

    /**
     * Calculate Levenshtein similarity
     */
    public function levenshteinSimilarity(string $s1, string $s2): float
    {
        $len1 = strlen($s1);
        $len2 = strlen($s2);

        if ($len1 === 0) return $len2 === 0 ? 1.0 : 0.0;
        if ($len2 === 0) return 0.0;

        $distance = levenshtein($s1, $s2);
        $maxLen = max($len1, $len2);

        return 1 - ($distance / $maxLen);
    }

    /**
     * Calculate word overlap similarity
     */
    public function wordOverlapSimilarity(string $s1, string $s2): float
    {
        $words1 = array_filter(array_map('trim', explode(' ', $s1)));
        $words2 = array_filter(array_map('trim', explode(' ', $s2)));

        if (empty($words1) || empty($words2)) {
            return 0.0;
        }

        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));

        return count($intersection) / count($union);
    }

    /**
     * Calculate overall similarity including text similarity
     */
    public function calculateOverallSimilarity(float $visualSimilarity, float $textSimilarity, float $textWeight = 0.3): float
    {
        $visualWeight = 1 - $textWeight;
        return ($visualSimilarity * $visualWeight) + ($textSimilarity * $textWeight);
    }

    /**
     * Update reference image details and/or image file
     *
     * @OA\Put(
     *     path="/reference-images/{metadataId}",
     *     operationId="updateReferenceImage",
     *     tags={"Image Management"},
     *     summary="Update reference image details and/or image file",
     *     description="Update the metadata (description, tags, email) and optionally replace the image file",
     *     @OA\Parameter(
     *         name="metadataId",
     *         in="path",
     *         required=true,
     *         description="ID of the image metadata to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Optional new image file to replace the existing one (max 10MB)"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     maxLength=1000,
     *                     description="Updated description for the image (max 1000 chars)"
     *                 ),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="string",
     *                     maxLength=255,
     *                     description="Updated tags for the image (comma-separated string or JSON array)"
     *                 ),
     *                 @OA\Property(
     *                     property="uploader_email",
     *                     type="string",
     *                     format="email",
     *                     maxLength=255,
     *                     description="Updated uploader email address"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reference image updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="updated_image", type="object"),
     *                 @OA\Property(property="image_replaced", type="boolean", example=true),
     *                 @OA\Property(property="metadata_updated", type="boolean", example=true),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2024-01-01T12:00:00Z")
     *             ),
     *             @OA\Property(property="message", type="string", example="Reference image updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reference image not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Reference image not found"),
     *             @OA\Property(property="message", type="string", example="The specified reference image does not exist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function updateReferenceImage(Request $request, int $metadataId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'sometimes|image|max:10240',
            'description' => 'sometimes|string|max:1000',
            'tags' => 'sometimes|string|max:255',
            'uploader_email' => 'sometimes|email|max:255',
            'status' => 'sometimes|in:lost,found',
        ], [
            'image.image' => 'The uploaded file must be an image',
            'image.max' => 'The image must not exceed 10MB',
            'description.string' => 'Description must be a string',
            'description.max' => 'Description must not exceed 1000 characters',
            'tags.string' => 'Tags must be a string',
            'tags.max' => 'Tags must not exceed 255 characters',
            'uploader_email.email' => 'Uploader email must be a valid email address',
            'uploader_email.max' => 'Uploader email must not exceed 255 characters',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Debug logging
            Log::info('Update reference image request', [
                'metadata_id' => $metadataId,
                'request_data' => $request->except(['image']),
                'has_uploader_email' => $request->has('uploader_email'),
                'uploader_email_value' => $request->input('uploader_email'),
                'has_status' => $request->has('status'),
                'status_value' => $request->input('status'),
                'all_input' => $request->all(),
                'method' => $request->method(),
                'content_type' => $request->header('Content-Type')
            ]);

            // Find the metadata record
            $metadata = ImageMetadata::find($metadataId);

            if (!$metadata) {
                return response()->json([
                    'success' => false,
                    'error' => 'Reference image not found',
                    'message' => 'The specified reference image does not exist'
                ], 404);
            }

            $imageReplaced = false;
            $metadataUpdated = false;
            $oldFilename = $metadata->filename;
            $oldPath = storage_path('app/public/reference-images/' . $oldFilename);

            // Handle image file replacement
            if ($request->hasFile('image')) {
                $newImage = $request->file('image');
                $originalName = $newImage->getClientOriginalName();
                $extension = $newImage->getClientOriginalExtension();
                $fileSize = $newImage->getSize();
                $mimeType = $newImage->getMimeType();

                // Generate new filename
                $timestamp = time();
                $randomString = Str::random(10);
                $newFilename = pathinfo($originalName, PATHINFO_FILENAME) . '_' . $timestamp . '_' . $randomString . '.' . $extension;

                // Store the new image
                $newImage->storeAs('public/reference-images', $newFilename);

                // Delete the old image file
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }

                // Update metadata with new file information
                $metadata->filename = $newFilename;
                $metadata->original_name = $originalName;
                $metadata->file_size = $fileSize;
                $metadata->mime_type = $mimeType;

                $imageReplaced = true;
            }

            // Update metadata fields
            if ($request->has('description')) {
                $metadata->description = $request->input('description');
                $metadataUpdated = true;
            }

            if ($request->has('tags')) {
                $tagsInput = $request->input('tags');
                if (is_string($tagsInput)) {
                    // Parse comma-separated tags
                    $tags = array_map('trim', explode(',', $tagsInput));
                    $tags = array_filter($tags); // Remove empty tags
                } else {
                    $tags = $tagsInput;
                }
                $metadata->tags = $tags;
                $metadataUpdated = true;
            }

            if ($request->has('uploader_email')) {
                $oldEmail = $metadata->uploader_email;
                $newEmail = $request->input('uploader_email');
                // Handle empty email - set to null if empty string
                $newEmail = empty($newEmail) ? null : $newEmail;
                $metadata->uploader_email = $newEmail;
                $metadataUpdated = true;

                Log::info('Updating uploader email', [
                    'metadata_id' => $metadataId,
                    'old_email' => $oldEmail,
                    'new_email' => $newEmail,
                    'email_changed' => $oldEmail !== $newEmail
                ]);
            }

            if ($request->has('status')) {
                $oldStatus = $metadata->status;
                $newStatus = $request->input('status');
                $metadata->status = $newStatus;
                $metadataUpdated = true;

                Log::info('Updating status', [
                    'metadata_id' => $metadataId,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'status_changed' => $oldStatus !== $newStatus
                ]);
            }

            // Save the updated metadata
            if ($imageReplaced || $metadataUpdated) {
                $metadata->save();

                Log::info('Metadata saved successfully', [
                    'metadata_id' => $metadataId,
                    'image_replaced' => $imageReplaced,
                    'metadata_updated' => $metadataUpdated,
                    'final_uploader_email' => $metadata->uploader_email
                ]);
            }

            // Prepare response data
            $updatedImage = [
                'metadata_id' => $metadata->id,
                'filename' => $metadata->filename,
                'original_name' => $metadata->original_name,
                'path' => 'storage/reference-images/' . $metadata->filename,
                'size' => $metadata->file_size,
                'description' => $metadata->description,
                'tags' => $metadata->tags,
                'uploader_email' => $metadata->uploader_email,
                'status' => $metadata->status,
                'updated_at' => $metadata->updated_at->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'updated_image' => $updatedImage,
                    'image_replaced' => $imageReplaced,
                    'metadata_updated' => $metadataUpdated,
                    'old_filename' => $imageReplaced ? $oldFilename : null,
                    'new_filename' => $imageReplaced ? $metadata->filename : null,
                    'timestamp' => now()->toISOString()
                ],
                'message' => 'Reference image updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating reference image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Update failed',
                'message' => 'An unexpected error occurred while updating the image'
            ], 500);
        }
    }
}
