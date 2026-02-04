# SapientPro Image Comparison API Documentation

## Overview

The SapientPro Image Comparison API provides RESTful endpoints for comparing images using the SapientPro ImageComparator library. This API supports multiple comparison methods including file uploads, URL-based comparisons, and batch processing.

## Base URL

```
http://localhost:8000/api/v1
```

## Authentication

Currently, the API does not require authentication. All endpoints are publicly accessible.

## Rate Limits

- **60 requests per minute** per IP address
- **10MB maximum file size** per image
- **5 images maximum** per batch comparison

## Response Format

All API responses follow a consistent JSON format:

```json
{
  "success": true|false,
  "data": {
    // Response data
  },
  "message": "Human readable message"
}
```

## Endpoints

### 1. Health Check

Check the API health status.

**Endpoint:** `GET /health`

**Response:**
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "version": "1.0.0",
    "timestamp": "2024-01-01T12:00:00Z",
    "services": {
      "image_comparator": "available"
    }
  },
  "message": "API is healthy"
}
```

### 2. API Documentation

Get comprehensive API documentation.

**Endpoint:** `GET /docs`

**Response:**
```json
{
  "success": true,
  "data": {
    "api_name": "SapientPro Image Comparison API",
    "version": "1.0.0",
    "description": "RESTful API for comparing images using SapientPro ImageComparator",
    "endpoints": [...],
    "rate_limits": {...}
  },
  "message": "API documentation retrieved successfully"
}
```

### 3. Compare Uploaded Images

Compare two images uploaded as files.

**Endpoint:** `POST /compare/upload`

**Content-Type:** `multipart/form-data`

**Parameters:**
- `image1` (file, required): First image file (max 10MB)
- `image2` (file, required): Second image file (max 10MB)

**Supported Formats:** JPG, PNG, GIF, BMP, WebP

**Example Request (cURL):**
```bash
curl -X POST http://localhost:8000/api/v1/compare/upload \
  -F "image1=@/path/to/image1.jpg" \
  -F "image2=@/path/to/image2.jpg"
```

**Example Request (JavaScript):**
```javascript
const formData = new FormData();
formData.append('image1', file1);
formData.append('image2', file2);

fetch('http://localhost:8000/api/v1/compare/upload', {
  method: 'POST',
  body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "similarity": 0.85,
    "similarity_percentage": 85.0,
    "comparison_id": "comp_abc123def4",
    "timestamp": "2024-01-01T12:00:00Z",
    "method": "upload"
  },
  "message": "Images compared successfully"
}
```

**Error Response (400):**
```json
{
  "success": false,
  "error": "Validation failed",
  "errors": {
    "image1": ["The image1 field is required."]
  }
}
```

### 4. Compare Images from URLs

Compare two images from their URLs.

**Endpoint:** `POST /compare/urls`

**Content-Type:** `application/json`

**Parameters:**
- `url1` (string, required): First image URL
- `url2` (string, required): Second image URL

**Example Request (cURL):**
```bash
curl -X POST http://localhost:8000/api/v1/compare/urls \
  -H "Content-Type: application/json" \
  -d '{
    "url1": "https://example.com/image1.jpg",
    "url2": "https://example.com/image2.jpg"
  }'
```

**Example Request (JavaScript):**
```javascript
fetch('http://localhost:8000/api/v1/compare/urls', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    url1: 'https://example.com/image1.jpg',
    url2: 'https://example.com/image2.jpg'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "similarity": 0.75,
    "similarity_percentage": 75.0,
    "comparison_id": "comp_xyz789abc1",
    "timestamp": "2024-01-01T12:00:00Z",
    "method": "url",
    "urls": {
      "url1": "https://example.com/image1.jpg",
      "url2": "https://example.com/image2.jpg"
    }
  },
  "message": "Images compared successfully"
}
```

### 5. Batch Comparison

Compare multiple images in batch (up to 5 images).

**Endpoint:** `POST /compare/batch`

**Content-Type:** `multipart/form-data`

**Parameters:**
- `images[]` (array of files, required): Array of image files (2-5 images, 10MB each)

**Example Request (cURL):**
```bash
curl -X POST http://localhost:8000/api/v1/compare/batch \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg" \
  -F "images[]=@/path/to/image3.jpg"
```

**Example Request (JavaScript):**
```javascript
const formData = new FormData();
files.forEach(file => {
  formData.append('images[]', file);
});

fetch('http://localhost:8000/api/v1/compare/batch', {
  method: 'POST',
  body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "comparisons": [
      {
        "image1": "image1.jpg",
        "image2": "image2.jpg",
        "similarity": 0.85,
        "similarity_percentage": 85.0
      },
      {
        "image1": "image1.jpg",
        "image2": "image3.jpg",
        "similarity": 0.72,
        "similarity_percentage": 72.0
      },
      {
        "image1": "image2.jpg",
        "image2": "image3.jpg",
        "similarity": 0.91,
        "similarity_percentage": 91.0
      }
    ],
    "batch_id": "batch_def456ghi7",
    "total_comparisons": 3,
    "timestamp": "2024-01-01T12:00:00Z",
    "method": "batch"
  },
  "message": "Batch comparison completed"
}
```

## Error Handling

The API uses standard HTTP status codes:

- **200 OK**: Request successful
- **400 Bad Request**: Validation error or invalid input
- **500 Internal Server Error**: Server error

### Error Response Format

```json
{
  "success": false,
  "error": "Error type",
  "message": "Detailed error message"
}
```

### Common Error Types

1. **Validation Errors (400)**
   - Missing required fields
   - Invalid file formats
   - File size exceeded
   - Invalid URLs

2. **Image Comparison Errors (400)**
   - Unsupported image format
   - Corrupted image files
   - Images too different in size

3. **Server Errors (500)**
   - Internal processing errors
   - Service unavailable

## Usage Examples

### PHP Example

```php
<?php

// Compare uploaded images
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/v1/compare/upload');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'image1' => new CURLFile('/path/to/image1.jpg'),
    'image2' => new CURLFile('/path/to/image2.jpg')
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$data = json_decode($response, true);

if ($data['success']) {
    echo "Similarity: " . $data['data']['similarity_percentage'] . "%\n";
} else {
    echo "Error: " . $data['message'] . "\n";
}

curl_close($ch);
```

### Python Example

```python
import requests

# Compare images from URLs
url = 'http://localhost:8000/api/v1/compare/urls'
data = {
    'url1': 'https://example.com/image1.jpg',
    'url2': 'https://example.com/image2.jpg'
}

response = requests.post(url, json=data)
result = response.json()

if result['success']:
    print(f"Similarity: {result['data']['similarity_percentage']}%")
else:
    print(f"Error: {result['message']}")
```

### Node.js Example

```javascript
const FormData = require('form-data');
const fs = require('fs');

// Compare uploaded images
const form = new FormData();
form.append('image1', fs.createReadStream('/path/to/image1.jpg'));
form.append('image2', fs.createReadStream('/path/to/image2.jpg'));

fetch('http://localhost:8000/api/v1/compare/upload', {
  method: 'POST',
  body: form
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    console.log(`Similarity: ${data.data.similarity_percentage}%`);
  } else {
    console.log(`Error: ${data.message}`);
  }
});
```

## Best Practices

1. **File Formats**: Use JPG or PNG for best compatibility
2. **File Sizes**: Keep images under 5MB for faster processing
3. **Image Quality**: Use similar quality and resolution images for better comparison
4. **Error Handling**: Always check the `success` field in responses
5. **Rate Limiting**: Implement exponential backoff for rate limit errors

## Support

For API support and questions:
- Check the health endpoint for service status
- Review error messages for troubleshooting
- Ensure images are in supported formats
- Verify file sizes are within limits

## Changelog

### Version 1.0.0
- Initial API release
- Support for file uploads and URL comparisons
- Batch comparison functionality
- Comprehensive error handling
- Health check and documentation endpoints
