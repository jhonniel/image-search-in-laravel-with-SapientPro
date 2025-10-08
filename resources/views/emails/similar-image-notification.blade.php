<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Similar Images Found</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 0 0 8px 8px;
        }
        .similarity-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .similarity-score {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .image-info {
            margin: 10px 0;
        }
        .tags {
            margin: 5px 0;
        }
        .tag {
            display: inline-block;
            background: #e3f2fd;
            color: #1976d2;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            margin: 2px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 12px;
            color: #6c757d;
        }
        .highlight {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 10px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(isset($notification_type) && $notification_type === 'no_match')
            <h1>📝 Item Uploaded Successfully!</h1>
            <p>Your {{ ucfirst($newImageMetadata['status'] ?? 'item') }} has been added to our system</p>
        @else
            <h1>🔍 Similar Item Found!</h1>
            @if(isset($notification_type) && $notification_type === 'new_uploader')
                <p>Similar images were found for your upload</p>
            @else
                <p>Someone has uploaded images similar to yours</p>
            @endif
        @endif
    </div>

    <div class="content">
        <p>Hello,</p>

        @if(isset($notification_type) && $notification_type === 'no_match')
            <p>We found <strong>0 similar image(s)</strong> that match the {{ $newImageMetadata['status'] ?? 'item' }} you just uploaded to our system.</p>
            <div class="highlight">
                <h3>📢 We Will Notify You!</h3>
                <p>We will notify you once we find similar to your {{ $newImageMetadata['status'] ?? 'item' }}.</p>
            </div>
        @elseif(isset($notification_type) && $notification_type === 'new_uploader')
            <p>We found <strong>{{ $totalSimilar }} similar image(s)</strong> in our system that match the image you just uploaded.</p>
        @else
            <p>We found <strong>{{ $totalSimilar }} similar image(s)</strong> that match images you've previously uploaded to our system.</p>
        @endif

        <div class="highlight">
            <h3>📸 New Upload Details:</h3>
            @if(isset($newImageMetadata['description']) && $newImageMetadata['description'])
                <p><strong>Description:</strong> {{ $newImageMetadata['description'] }}</p>
            @endif
            @if(isset($newImageMetadata['tags']) && is_array($newImageMetadata['tags']) && count($newImageMetadata['tags']) > 0)
                <p><strong>Tags:</strong>
                    @foreach($newImageMetadata['tags'] as $tag)
                        <span class="tag">{{ $tag }}</span>
                    @endforeach
                </p>
            @endif
        </div>

        @if(isset($notification_type) && $notification_type === 'no_match')
            <h3>🎯 Your Uploaded {{ ucfirst($newImageMetadata['status'] ?? 'Item') }}:</h3>
            <div class="highlight">
                <p><strong>Status:</strong> {{ ucfirst($newImageMetadata['status'] ?? 'Unknown') }}</p>
                <p><strong>Description:</strong> {{ $newImageMetadata['description'] ?? 'No description provided' }}</p>
                @if(isset($newImageMetadata['tags']) && is_array($newImageMetadata['tags']) && count($newImageMetadata['tags']) > 0)
                    <p><strong>Tags:</strong>
                        @foreach($newImageMetadata['tags'] as $tag)
                            <span class="tag">{{ $tag }}</span>
                        @endforeach
                    </p>
                @endif
            </div>
        @elseif(isset($notification_type) && $notification_type === 'new_uploader')
            <h3>🎯 Similar Images Found in System:</h3>
        @else
            <h3>🎯 Your Similar Images:</h3>
        @endif

        @if(isset($notification_type) && $notification_type !== 'no_match')
            @foreach($similarImages as $similarImage)
            <div class="similarity-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <h4 style="margin: 0;">{{ $similarImage['image']->original_name }}</h4>
                    <span class="similarity-score">{{ round($similarImage['overall_similarity'] * 100, 1) }}% Match</span>
                </div>

                <div class="image-info">
                    <p><strong>Status:</strong> {{ ucfirst($similarImage['image']->status ?? 'Unknown') }}</p>
                    <p><strong>Visual Similarity:</strong> {{ round($similarImage['visual_similarity'] * 100, 1) }}%</p>
                    <p><strong>Text Similarity:</strong> {{ round($similarImage['text_similarity'] * 100, 1) }}%</p>
                </div>

                @if($similarImage['image']->description)
                    <div class="image-info">
                        @if(isset($notification_type) && $notification_type === 'new_uploader')
                            <p><strong>Existing Description:</strong> {{ $similarImage['image']->description }}</p>
                        @else
                            <p><strong>Your Description:</strong> {{ $similarImage['image']->description }}</p>
                        @endif
                    </div>
                @endif

                @if($similarImage['image']->tags && is_array($similarImage['image']->tags) && count($similarImage['image']->tags) > 0)
                    <div class="tags">
                        @if(isset($notification_type) && $notification_type === 'new_uploader')
                            <strong>Existing Tags:</strong><br>
                        @else
                            <strong>Your Tags:</strong><br>
                        @endif
                        @foreach($similarImage['image']->tags as $tag)
                            <span class="tag">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="image-info">
                    <p><strong>Uploaded:</strong> {{ $similarImage['image']->created_at->format('M d, Y \a\t g:i A') }}</p>
                </div>
            </div>
        @endforeach
        @endif

        <div class="highlight">
            <h3>💡 What This Means:</h3>
            <p>These images were found to be similar based on:</p>
            <ul>
                <li><strong>Visual similarity:</strong> How similar the images look</li>
                <li><strong>Text similarity:</strong> How similar the descriptions and tags are</li>
                <li><strong>Overall similarity:</strong> Combined score above our threshold</li>
            </ul>
        </div>

        @if(isset($notification_type) && $notification_type === 'no_match')
            <p>We will continue to monitor our system for similar {{ $newImageMetadata['status'] ?? 'items' }} and notify you when we find matches. This helps you stay connected with the community and discover related content.</p>
        @elseif(isset($notification_type) && $notification_type === 'new_uploader')
            <p>This notification helps you discover existing content in our system that's similar to what you just uploaded. You might find related images or connect with other users who have similar interests.</p>
        @else
            <p>This notification helps you discover related content and potential connections with other users in our community.</p>
        @endif

        <p>Best regards,<br>
        <strong>Image Search System Team</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated notification. If you no longer wish to receive these notifications, please contact our support team.</p>
        <p>&copy; {{ date('Y') }} Image Search System. All rights reserved.</p>
    </div>
</body>
</html>
