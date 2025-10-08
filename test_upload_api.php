<?php

// Simple test script to verify the upload API with descriptions and tags
$testData = [
    'descriptions' => [
        'Beautiful sunset over the mountains',
        'Ocean waves crashing on the shore'
    ],
    'tags' => [
        'sunset,mountains,landscape',
        'ocean,waves,nature'
    ]
];

echo "Test data for API upload:\n";
echo "Descriptions: " . json_encode($testData['descriptions']) . "\n";
echo "Tags: " . json_encode($testData['tags']) . "\n";

echo "\nTo test the API manually:\n";
echo "1. Go to http://localhost:8000/image-comparison\n";
echo "2. Click on 'Manage References' tab\n";
echo "3. Upload some images\n";
echo "4. Add descriptions and tags for each image\n";
echo "5. Click 'Upload Reference Images'\n";
echo "6. Check the results to see descriptions and tags displayed\n";

echo "\nAPI endpoint: POST /api/v1/reference-images/upload\n";
echo "Expected form data:\n";
echo "- images[]: file uploads\n";
echo "- descriptions[]: array of descriptions (optional)\n";
echo "- tags[]: array of tag strings (optional)\n";
