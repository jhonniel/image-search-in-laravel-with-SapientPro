<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ImageComparisonApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| SapientPro Image Comparison API v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Health check
    Route::get('/health', [ImageComparisonApiController::class, 'health']);

    // API documentation
    Route::get('/docs', [ImageComparisonApiController::class, 'documentation']);

    // Image comparison endpoints
    Route::prefix('compare')->group(function () {

        // Compare uploaded images
        Route::post('/upload', [ImageComparisonApiController::class, 'compareUploadedImages'])
            ->name('api.compare.upload');

        // Compare images from URLs
        Route::post('/urls', [ImageComparisonApiController::class, 'compareImagesFromUrls'])
            ->name('api.compare.urls');

        // Batch comparison
        Route::post('/batch', [ImageComparisonApiController::class, 'batchCompare'])
            ->name('api.compare.batch');

        // Find matching images from stored reference images
        Route::post('/find-match', [ImageComparisonApiController::class, 'findMatchingImages'])
            ->name('api.compare.find-match');
    });

    // Reference image management endpoints
    Route::prefix('reference-images')->group(function () {

        // Upload reference images
        Route::post('/upload', [ImageComparisonApiController::class, 'uploadReferenceImages'])
            ->name('api.reference-images.upload');

        // List all reference images
        Route::get('/', [ImageComparisonApiController::class, 'listReferenceImages'])
            ->name('api.reference-images.list');

        // Bulk delete reference images
        Route::delete('/bulk', [ImageComparisonApiController::class, 'bulkDeleteReferenceImages'])
            ->name('api.reference-images.bulk-delete');

        // Delete a specific reference image
        Route::delete('/{filename}', [ImageComparisonApiController::class, 'deleteReferenceImage'])
            ->name('api.reference-images.delete');
    });
});

/*
|--------------------------------------------------------------------------
| Legacy API Routes (for backward compatibility)
|--------------------------------------------------------------------------
*/

Route::prefix('compare')->group(function () {
    Route::post('/images', [ImageComparisonApiController::class, 'compareUploadedImages']);
    Route::post('/urls', [ImageComparisonApiController::class, 'compareImagesFromUrls']);
});
