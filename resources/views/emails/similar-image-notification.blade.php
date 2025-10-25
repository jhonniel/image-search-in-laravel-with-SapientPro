<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $data['notification_type'] === 'existing_owner' ? 'Similar Image Found' : 'Image Upload Status' }}</title>
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
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .notification-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .similar-item {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .new-item {
            background: #f3e5f5;
            border-left: 4px solid #9c27b0;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            background: #2196f3;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #1976d2;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔍 FindITFast</h1>
        <h2>{{ $data['notification_type'] === 'existing_owner' ? 'Similar Image Found!' : 'Image Upload Status' }}</h2>
    </div>

    <div class="content">
        @if($data['notification_type'] === 'existing_owner')
            <div class="notification-box">
                <h3>🎉 Great News!</h3>
                <p>Someone has uploaded an image that might match your lost/found item!</p>
                <p><strong>Similar items found:</strong> {{ $data['total_similar'] }}</p>
            </div>

            <h3>📸 Your Similar Items:</h3>
            @foreach($data['similar_images'] as $similar)
                <div class="similar-item">
                    <h4>{{ $similar['image']->original_name }}</h4>
                    <p><strong>Similarity Score:</strong> {{ number_format($similar['overall_similarity'] * 100, 1) }}%</p>
                    @if($similar['image']->description)
                        <p><strong>Description:</strong> {{ $similar['image']->description }}</p>
                    @endif
                    @if($similar['image']->tags && count($similar['image']->tags) > 0)
                        <p><strong>Tags:</strong> {{ is_array($similar['image']->tags) ? implode(', ', $similar['image']->tags) : $similar['image']->tags }}</p>
                    @endif
                    <p><strong>Status:</strong> {{ ucfirst($similar['image']->status) }} Item</p>
                </div>
            @endforeach

            <h3>🆕 New Uploaded Item:</h3>
            <div class="new-item">
                <h4>{{ $data['new_image_metadata']['original_name'] ?? 'New Item' }}</h4>
                @if(isset($data['new_image_metadata']['description']))
                    <p><strong>Description:</strong> {{ $data['new_image_metadata']['description'] }}</p>
                @endif
                @if(isset($data['new_image_metadata']['tags']) && count($data['new_image_metadata']['tags']) > 0)
                    <p><strong>Tags:</strong> {{ is_array($data['new_image_metadata']['tags']) ? implode(', ', $data['new_image_metadata']['tags']) : $data['new_image_metadata']['tags'] }}</p>
                @endif
            </div>

        @else
            <div class="notification-box">
                <h3>📤 Upload Confirmation</h3>
                <p>Your image has been successfully uploaded to FindITFast!</p>
                @if($data['total_similar'] > 0)
                    <p><strong>Similar items found:</strong> {{ $data['total_similar'] }}</p>
                    <p>We've notified the owners of similar items about your upload.</p>
                @else
                    <p>No similar items were found in our database.</p>
                    <p>We'll notify you if someone uploads a similar item!</p>
                @endif
            </div>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.url') }}/image-comparison" class="btn">View on FindITFast</a>
        </div>

        <div class="footer">
            <p><strong>FindITFast</strong> - Connecting lost and found items</p>
            <p>This is an automated notification. Please do not reply to this email.</p>
            <p>If you have any questions, please contact us through our website.</p>
        </div>
    </div>
</body>
</html>
