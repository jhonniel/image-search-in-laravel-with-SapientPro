<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageComparisonController;

Route::get('/', function () {
    return redirect('/image-comparison');
});

Route::get('/image-comparison', function () {
    return view('image-comparison');
});

Route::get('/debug-test', function () {
    return view('debug-test');
});

Route::post('/debug-upload', function (Illuminate\Http\Request $request) {
    $result = [
        'files_received' => $request->allFiles(),
        'has_image1' => $request->hasFile('image1'),
        'has_image2' => $request->hasFile('image2'),
        'image1_valid' => $request->file('image1') ? $request->file('image1')->isValid() : false,
        'image2_valid' => $request->file('image2') ? $request->file('image2')->isValid() : false,
        'image1_error' => $request->file('image1') ? $request->file('image1')->getError() : 'no file',
        'image2_error' => $request->file('image2') ? $request->file('image2')->getError() : 'no file',
        'image1_size' => $request->file('image1') ? $request->file('image1')->getSize() : 0,
        'image2_size' => $request->file('image2') ? $request->file('image2')->getSize() : 0,
        'php_upload_max_filesize' => ini_get('upload_max_filesize'),
        'php_post_max_size' => ini_get('post_max_size'),
    ];
    
    return response()->json($result);
});


// Image comparison routes
Route::post('/api/compare-images', [ImageComparisonController::class, 'compare']);
Route::post('/api/compare-urls', [ImageComparisonController::class, 'compareUrls']);
