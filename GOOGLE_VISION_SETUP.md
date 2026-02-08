# Google Vision API Setup Guide

This guide explains how to set up Google Vision API for the admin image comparison feature.

## Prerequisites

1. **Google Cloud Account** - You need a Google Cloud account
2. **Google Cloud Project** - Create or select a project in Google Cloud Console
3. **Billing Enabled** - Google Vision API requires billing to be enabled (free tier available)

## Step 1: Enable Google Vision API

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project
3. Navigate to **APIs & Services** > **Library**
4. Search for "Cloud Vision API"
5. Click on **Cloud Vision API**
6. Click **Enable**

## Step 2: Create API Key

1. Go to **APIs & Services** > **Credentials**
2. Click **Create Credentials** > **API Key**
3. Copy the generated API key (starts with `AIzaSy...`)
4. **Optional but Recommended**: Click "Restrict Key" to:
   - Restrict to "Cloud Vision API" only
   - Set application restrictions (HTTP referrers, IP addresses, etc.)
   - This improves security

## Step 3: Configure in Admin Settings

1. **Go to Admin Settings**:
   - Navigate to `/settings?tab=google-vision` (admin only)
   - Or: Admin Panel → Settings → Google Vision API tab

2. **Enter API Key**:
   - Check "Google Vision API Enabled"
   - Paste your API key in the "Google Vision API Key" field
   - Click "Save Settings"

3. **Test Connection**:
   - Click "Test Google Vision API Connection"
   - Verify the connection is successful

## Step 4: Test the Integration

1. Go to admin image comparison page: `/image-comparison`
2. Upload two images
3. Check the "Use Google Vision API" checkbox
4. Click "Compare Images"
5. You should see detailed analysis including:
   - Labels detected in both images
   - Objects detected
   - Text detected
   - Similarity score based on Vision API analysis

## Features

### What Google Vision API Analyzes

1. **Labels** - Detects objects, scenes, and concepts (e.g., "backpack", "outdoor", "red")
2. **Objects** - Detects and locates objects in images
3. **Text Detection** - Extracts text from images (OCR)
4. **Image Properties** - Dominant colors in the image
5. **Safe Search** - Detects adult, violent, or racy content

### Similarity Calculation

The system calculates similarity using:
- **Labels** (40% weight) - Compares detected labels between images
- **Objects** (30% weight) - Compares detected objects
- **Text** (20% weight) - Compares extracted text
- **Colors** (10% weight) - Compares dominant colors

## Pricing

Google Vision API offers a **free tier**:
- First 1,000 units per month: **FREE**
- After that: $1.50 per 1,000 units

**Note**: Each image analysis counts as 1 unit per feature used.

## Troubleshooting

### Error: "Google Vision API key not configured"

**Solution**: 
1. Go to Admin Settings → Google Vision API tab
2. Enter your API key in the "Google Vision API Key" field
3. Make sure "Google Vision API Enabled" is checked
4. Click "Save Settings"

### Error: "API key validation failed" or "Invalid API key"

**Solution**:
1. Verify the API key is correct (starts with `AIzaSy...`)
2. Check that Cloud Vision API is enabled in your Google Cloud project
3. Ensure the API key is not restricted incorrectly
4. Try creating a new API key if the current one doesn't work

### Error: "Billing not enabled"

**Solution**:
1. Go to Google Cloud Console
2. Navigate to **Billing**
3. Link a billing account to your project
4. Note: Free tier still requires billing to be enabled

## Security Best Practices

1. **Never commit API keys** - API keys are stored in the database, ensure database is secured
2. **Restrict API key** - In Google Cloud Console, restrict the API key to:
   - Only Cloud Vision API
   - Specific IP addresses or HTTP referrers (if applicable)
3. **Rotate keys regularly** - Create new API keys and update in admin settings periodically
4. **Monitor usage** - Set up billing alerts in Google Cloud Console
5. **Use HTTPS** - Always use HTTPS when making API calls (automatically handled)

## API Endpoints

### Compare Images with Google Vision (Upload)
```
POST /api/compare-images-vision
Content-Type: multipart/form-data

Parameters:
- image1: File (required)
- image2: File (required)
```

### Compare Images with Google Vision (URLs)
```
POST /api/compare-urls-vision
Content-Type: application/json

Body:
{
  "url1": "https://example.com/image1.jpg",
  "url2": "https://example.com/image2.jpg"
}
```

## Response Format

```json
{
  "success": true,
  "similarity": 0.85,
  "similarity_percentage": 85.0,
  "vision_data": {
    "image1": {
      "labels": [
        {"description": "backpack", "score": 0.95},
        {"description": "outdoor", "score": 0.87}
      ],
      "objects": [
        {"name": "Backpack", "score": 0.92}
      ],
      "texts": [
        {"description": "Found item", "locale": "en"}
      ],
      "image_properties": {
        "dominant_colors": [
          {"red": 255, "green": 0, "blue": 0, "score": 0.5}
        ]
      }
    },
    "image2": {
      // Same structure as image1
    }
  },
  "message": "Images compared successfully using Google Vision API"
}
```

## Comparison: Standard vs Google Vision

| Feature | Standard Comparison | Google Vision API |
|---------|-------------------|-------------------|
| **Method** | Perceptual hashing | Machine learning |
| **Speed** | Fast | Slower (API call) |
| **Accuracy** | Good for exact matches | Better for semantic similarity |
| **Details** | Similarity score only | Labels, objects, text, colors |
| **Cost** | Free | Free tier available |
| **Use Case** | Quick comparisons | Detailed analysis |

## Support

For issues with:
- **Google Vision API**: Check [Google Cloud Vision API Documentation](https://cloud.google.com/vision/docs)
- **Laravel Integration**: Check application logs in `storage/logs/laravel.log`
