<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use SapientPro\ImageComparator\ImageComparator;
use SapientPro\ImageComparator\ImageComparatorException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        private ImageComparator $imageComparator
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
                    'similarity_percentage' => round($similarity * 100, 2),
                    'comparison_id' => 'comp_' . Str::random(10),
                    'timestamp' => now()->toISOString(),
                    'method' => 'upload'
                ],
                'message' => 'Images compared successfully'
            ]);

        } catch (ImageComparatorException $e) {
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
                    'similarity_percentage' => round($similarity * 100, 2),
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

        } catch (ImageComparatorException $e) {
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
                        'similarity_percentage' => round($similarity * 100, 2)
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

        } catch (ImageComparatorException $e) {
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
                    'batch_limit' => '5 images per batch'
                ]
            ],
            'message' => 'API documentation retrieved successfully'
        ]);
    }
}
