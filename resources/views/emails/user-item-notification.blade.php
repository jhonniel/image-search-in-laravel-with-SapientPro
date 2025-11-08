<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindITFast - Item Notification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(to right, #8B5CF6, #EC4899);
            color: #ffffff;
            padding: 30px;
            text-align: center;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 30px;
            color: #555;
        }
        .content h2 {
            color: #8B5CF6;
            font-size: 22px;
            margin-top: 0;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .content p {
            margin-bottom: 15px;
        }
        .button-container {
            text-align: center;
            margin-top: 30px;
        }
        .button {
            display: inline-block;
            background-color: #8B5CF6;
            color: #ffffff !important;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            background-color: #f0f0f0;
            color: #777;
            text-align: center;
            padding: 20px;
            font-size: 12px;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        .item-details {
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .item-details h3 {
            color: #8B5CF6;
            margin-top: 0;
        }
        .item-details p {
            margin: 5px 0;
        }
        .similar-items {
            margin-top: 20px;
        }
        .similar-item {
            background-color: #f0f0f0;
            border: 1px solid #d0d0d0;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
        }
        .similar-item h4 {
            color: #8B5CF6;
            margin-top: 0;
        }
        .tag {
            display: inline-block;
            background-color: #e0ffe0;
            color: #28a745;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .status-lost {
            background-color: #ffe0e0;
            color: #dc3545;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
        }
        .status-found {
            background-color: #e0ffe0;
            color: #28a745;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
        }
        .highlight {
            background-color: #e6f2ff;
            padding: 15px;
            border-left: 4px solid #8B5CF6;
            margin: 15px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>FindITFast</h1>
            <p>Item Notification</p>
        </div>
        <div class="content">
            @if ($data['notification_type'] === 'similar_item_found')
                <h2>🎉 Great News! We Found Similar Items!</h2>
                <p>Hello {{ explode('@', $data['user_email'] ?? 'User')[0] }},</p>
                <p>We're excited to let you know that we found some items in our database that might match your {{ $data['item_type'] }} item!</p>

                <div class="item-details">
                    <h3>Your {{ ucfirst($data['item_type']) }} Item Details:</h3>
                    <p><strong>Description:</strong> {{ $data['item_description'] ?? 'N/A' }}</p>
                    <p><strong>Location:</strong> {{ $data['item_location'] ?? 'N/A' }}</p>
                    <p><strong>Tags:</strong>
                        @if(isset($data['item_tags']) && is_array($data['item_tags']))
                            @foreach($data['item_tags'] as $tag)
                                <span class="tag">{{ $tag }}</span>
                            @endforeach
                        @else
                            N/A
                        @endif
                    </p>
                    <p><strong>Contact:</strong> {{ $data['user_email'] ?? $data['contact_email'] ?? 'N/A' }}</p>
                </div>

                <div class="similar-items">
                    <h3>Similar Items Found:</h3>
                    @if(isset($data['similar_items']) && count($data['similar_items']) > 0)
                        @foreach($data['similar_items'] as $index => $similar)
                            <div class="similar-item">
                                <h4>{{ $similar['description'] ?? 'Item' }}</h4>
                                <p><strong>Type:</strong> <span class="{{ $similar['status'] === 'found' ? 'status-found' : 'status-lost' }}">{{ ucfirst($similar['status']) }} Item</span></p>
                                <p><strong>Owner:</strong> {{ $similar['uploader_email'] ?? 'N/A' }}</p>
                                <p><strong>Similarity:</strong> {{ round($similar['similarity'] * 100, 1) }}%</p>
                                @if(isset($similar['tags']) && is_array($similar['tags']))
                                    <p><strong>Tags:</strong>
                                        @foreach($similar['tags'] as $tag)
                                            <span class="tag">{{ $tag }}</span>
                                        @endforeach
                                    </p>
                                @endif
                                <p class="mt-3">
                                    <a href="{{ url('/user/reported-items') }}?highlight={{ $index + 1 }}&item={{ $similar['item_id'] ?? '' }}"
                                       class="button"
                                       style="display: inline-block; background-color: #8B5CF6; color: #ffffff; padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 14px;">
                                        View This Item
                                    </a>
                                </p>
                            </div>
                        @endforeach
                    @else
                        <p>No similar items found at this time.</p>
                    @endif
                </div>

                <div class="highlight">
                    <p><strong>Next Steps:</strong></p>
                    <p>1. Review the similar items above</p>
                    <p>2. If you find a match, contact the item owner directly</p>
                    <p>3. Visit our platform to see more details and images</p>
                </div>

            @elseif ($data['notification_type'] === 'new_item_uploaded')
                <h2>✅ Your Item Has Been Successfully Uploaded!</h2>
                <p>Hello {{ explode('@', $data['user_email'] ?? 'User')[0] }},</p>
                <p>Thank you for using FindITFast! Your {{ $data['item_type'] }} item has been successfully added to our database.</p>

                <div class="item-details">
                    <h3>Your {{ ucfirst($data['item_type']) }} Item Details:</h3>
                    <p><strong>Description:</strong> {{ $data['item_description'] ?? 'N/A' }}</p>
                    <p><strong>Location:</strong> {{ $data['item_location'] ?? 'N/A' }}</p>
                    <p><strong>Tags:</strong>
                        @if(isset($data['item_tags']) && is_array($data['item_tags']))
                            @foreach($data['item_tags'] as $tag)
                                <span class="tag">{{ $tag }}</span>
                            @endforeach
                        @else
                            N/A
                        @endif
                    </p>
                    <p><strong>Upload Date:</strong> {{ $data['upload_date'] ?? now()->format('M d, Y') }}</p>
                </div>

                <div class="highlight">
                    <p><strong>What happens next?</strong></p>
                    <p>• Our system will automatically check for similar items</p>
                    <p>• You'll receive notifications if matches are found</p>
                    <p>• Other users can search for your item</p>
                    <p>• You can track your item's status on our platform</p>
                </div>

                <div class="item-details">
                    <h3>View Your Uploaded Item:</h3>
                    <p>You can view and manage your uploaded item by clicking the button below:</p>
                    <p class="mt-3">
                        <a href="{{ url('/user/reported-items') }}?view=my-item&upload_id={{ $data['upload_id'] ?? '' }}&item_id={{ $data['item_id'] ?? '' }}"
                           class="button"
                           style="display: inline-block; background-color: #8B5CF6; color: #ffffff; padding: 12px 24px; border-radius: 4px; text-decoration: none; font-size: 16px; font-weight: bold;">
                            View My Item
                        </a>
                    </p>
                </div>

            @else
                <h2>FindITFast Notification</h2>
                <p>Hello {{ explode('@', $data['user_email'] ?? 'User')[0] }},</p>
                <p>This is a notification from FindITFast regarding your item.</p>
            @endif

            <div class="button-container">
                <a href="{{ url('/user/reported-items') }}" class="button">View My Items</a>
                <a href="{{ url('/user/dashboard') }}" class="button" style="margin-left: 10px; background-color: #EC4899;">Go to Dashboard</a>
            </div>

            <div class="highlight" style="margin-top: 20px;">
                <h3 style="color: #8B5CF6; margin-top: 0;">Quick Access Links:</h3>
                <p style="margin: 5px 0;"><a href="{{ url('/user/reported-items') }}" style="color: #8B5CF6; text-decoration: none;">📋 My Reported Items</a></p>
                <p style="margin: 5px 0;"><a href="{{ url('/user/dashboard') }}" style="color: #8B5CF6; text-decoration: none;">🏠 Dashboard</a></p>
                <p style="margin: 5px 0;"><a href="{{ url('/user/claim-verify') }}" style="color: #8B5CF6; text-decoration: none;">✅ Claim & Verify</a></p>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} FindITFast. All rights reserved.</p>
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>
