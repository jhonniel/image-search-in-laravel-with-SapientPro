# Web Interface Debugging Guide

## Issue: "Validation failed" Error

The web interface was showing "validation failed" errors. This has been fixed by:

1. ✅ **Added CSRF Token**: Added `<meta name="csrf-token" content="{{ csrf_token() }}">` to the page
2. ✅ **Fixed Form Submission**: Updated JavaScript to handle both file uploads and URL submissions correctly
3. ✅ **Updated API Endpoints**: Changed from old endpoints to new v1 API endpoints

## How to Test the Web Interface

### Step 1: Access the Interface
1. Make sure your server is running: `php artisan serve --host=0.0.0.0 --port=8000`
2. Open: **http://localhost:8000/image-comparison**

### Step 2: Test File Upload
1. **Use Test Images**: The application includes test images in the vendor directory
2. **Copy Test Images**: Copy some test images to your desktop for easy access:
   ```bash
   cp vendor/sapientpro/image-comparator/tests/images/flower.jpg ~/Desktop/
   cp vendor/sapientpro/image-comparator/tests/images/flower2.jpg ~/Desktop/
   cp vendor/sapientpro/image-comparator/tests/images/rose.jpg ~/Desktop/
   ```

3. **Upload Images**:
   - Click on the first upload area
   - Select `flower.jpg` from your Desktop
   - Click on the second upload area
   - Select `flower2.jpg` from your Desktop
   - Click "Compare Images"

### Step 3: Test URL Comparison
1. Switch to the "Image URLs" tab
2. Enter these URLs:
   - First URL: `https://picsum.photos/400/300?random=1`
   - Second URL: `https://picsum.photos/400/300?random=2`
3. Click "Compare Images"

## Expected Results

### File Upload Test
- **flower.jpg vs flower2.jpg**: Should show ~68% similarity
- **flower.jpg vs rose.jpg**: Should show lower similarity (~20-40%)

### URL Test
- **Random images**: Should show varying similarity (usually 20-60%)

## Debugging Steps

### If You Still Get Errors:

#### 1. Check Browser Console
1. Open Developer Tools (F12)
2. Go to Console tab
3. Look for any JavaScript errors
4. Check Network tab for failed requests

#### 2. Test API Directly
```bash
# Test file upload
curl -X POST http://localhost:8000/api/v1/compare/upload \
  -F "image1=@vendor/sapientpro/image-comparator/tests/images/flower.jpg" \
  -F "image2=@vendor/sapientpro/image-comparator/tests/images/flower2.jpg"

# Test URL comparison
curl -X POST http://localhost:8000/api/v1/compare/urls \
  -H "Content-Type: application/json" \
  -d '{"url1": "https://picsum.photos/400/300?random=1", "url2": "https://picsum.photos/400/300?random=2"}'
```

#### 3. Check Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

#### 4. Verify CSRF Token
```bash
curl -s http://localhost:8000/image-comparison | grep -i "csrf-token"
```

## Common Issues and Solutions

### Issue: "CSRF token mismatch"
**Solution**: The CSRF token is now included in the page. Clear your browser cache and try again.

### Issue: "File must be an image"
**Solution**: Make sure you're uploading actual image files (JPG, PNG, GIF), not text files or other formats.

### Issue: "File too large"
**Solution**: Images must be under 10MB. Use smaller images for testing.

### Issue: "Network error"
**Solution**: 
1. Check if the server is running
2. Check your internet connection (for URL comparisons)
3. Try refreshing the page

## Test Images Available

The following test images are available in the project:
- `vendor/sapientpro/image-comparator/tests/images/flower.jpg`
- `vendor/sapientpro/image-comparator/tests/images/flower2.jpg`
- `vendor/sapientpro/image-comparator/tests/images/rose.jpg`
- `vendor/sapientpro/image-comparator/tests/images/bird-yellow.jpg`
- `vendor/sapientpro/image-comparator/tests/images/forest.jpg`
- `vendor/sapientpro/image-comparator/tests/images/forest-copy.jpg`

## Quick Test Commands

```bash
# Test API health
curl http://localhost:8000/api/v1/health

# Test file upload with known good images
curl -X POST http://localhost:8000/api/v1/compare/upload \
  -F "image1=@vendor/sapientpro/image-comparator/tests/images/flower.jpg" \
  -F "image2=@vendor/sapientpro/image-comparator/tests/images/flower2.jpg" | jq .

# Test URL comparison
curl -X POST http://localhost:8000/api/v1/compare/urls \
  -H "Content-Type: application/json" \
  -d '{"url1": "https://picsum.photos/400/300?random=1", "url2": "https://picsum.photos/400/300?random=2"}' | jq .
```

## Success Indicators

✅ **API Working**: Direct API calls return successful responses  
✅ **CSRF Token**: Token is present in the page source  
✅ **Form Submission**: No JavaScript errors in browser console  
✅ **Image Upload**: Files are accepted and previews show  
✅ **Comparison Results**: Similarity percentage is displayed  

If all these indicators are green, the web interface should work correctly!
