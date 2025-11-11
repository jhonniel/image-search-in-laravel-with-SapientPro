<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use SapientPro\ImageComparator\ImageComparator;
use SapientPro\ImageComparator\ImageResourceException;

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
}
