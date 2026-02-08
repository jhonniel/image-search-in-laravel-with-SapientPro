# Swagger/OpenAPI Documentation Setup

## Overview

This project includes comprehensive Swagger/OpenAPI documentation for the SapientPro Image Comparison API. The documentation is automatically generated from code annotations and provides an interactive interface for testing API endpoints.

## Features

✅ **Interactive Documentation**: Test API endpoints directly from the browser  
✅ **Auto-generated**: Documentation is generated from code annotations  
✅ **Comprehensive**: Includes all endpoints, parameters, and responses  
✅ **Custom Styling**: Beautiful, branded interface  
✅ **Real-time Testing**: Execute API calls and see responses instantly  

## Accessing the Documentation

### Web Interface
Visit the Swagger UI at: **http://localhost:8000/api/documentation**

### JSON Specification
Access the raw OpenAPI specification at: **http://localhost:8000/docs/api-docs.json**

## API Documentation Structure

### 1. System Endpoints
- **GET /health** - Check API health status
- **GET /docs** - Get API documentation information

### 2. Image Comparison Endpoints
- **POST /compare/upload** - Compare two uploaded images
- **POST /compare/urls** - Compare images from URLs
- **POST /compare/batch** - Compare multiple images in batch

## Using the Swagger UI

### 1. Navigation
- **Tags**: Use the tag groups to filter endpoints
- **Expand/Collapse**: Click on endpoint names to expand details
- **Try it out**: Click "Try it out" to test endpoints

### 2. Testing Endpoints

#### File Upload Endpoint
1. Navigate to **POST /compare/upload**
2. Click "Try it out"
3. Upload two image files using the file inputs
4. Click "Execute"
5. View the response with similarity percentage

#### URL Comparison Endpoint
1. Navigate to **POST /compare/urls**
2. Click "Try it out"
3. Enter two image URLs in JSON format:
   ```json
   {
     "url1": "https://example.com/image1.jpg",
     "url2": "https://example.com/image2.jpg"
   }
   ```
4. Click "Execute"
5. View the comparison results

#### Batch Comparison Endpoint
1. Navigate to **POST /compare/batch**
2. Click "Try it out"
3. Upload 2-5 image files
4. Click "Execute"
5. View all pairwise comparisons

### 3. Response Examples

#### Success Response
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

#### Error Response
```json
{
  "success": false,
  "error": "Validation failed",
  "errors": {
    "image1": ["The image1 field is required."]
  }
}
```

## Code Annotations

The Swagger documentation is generated from OpenAPI annotations in the controller. Here's an example:

```php
/**
 * @OA\Post(
 *     path="/compare/upload",
 *     operationId="compareUploadedImages",
 *     tags={"Image Comparison"},
 *     summary="Compare two uploaded images",
 *     description="Upload two image files and get their similarity percentage",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(
 *                     property="image1",
 *                     type="string",
 *                     format="binary",
 *                     description="First image file (max 10MB)"
 *                 ),
 *                 @OA\Property(
 *                     property="image2",
 *                     type="string",
 *                     format="binary",
 *                     description="Second image file (max 10MB)"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Images compared successfully",
 *         @OA\JsonContent(...)
 *     )
 * )
 */
```

## Configuration

### Swagger Configuration File
Located at: `config/l5-swagger.php`

Key settings:
- **API Title**: "SapientPro Image Comparison API"
- **Version**: "1.0.0"
- **Documentation Route**: `/api/documentation`
- **JSON Output**: `storage/api-docs/api-docs.json`

### Custom Styling
The Swagger UI includes custom styling for:
- Modern gradient header
- Custom operation block colors
- Improved typography with Inter font
- Enhanced button styling
- Better visual hierarchy

## Regenerating Documentation

After making changes to API annotations, regenerate the documentation:

```bash
php artisan l5-swagger:generate
```

## Integration with Development

### Development Workflow
1. Add/modify API endpoints in controllers
2. Add OpenAPI annotations to document the endpoints
3. Run `php artisan l5-swagger:generate` to update documentation
4. Test endpoints using the Swagger UI
5. Commit changes with updated documentation

### Testing with Swagger
- Use the "Try it out" feature to test endpoints
- Verify request/response formats
- Test error scenarios
- Validate parameter constraints

## Best Practices

### Documentation Standards
1. **Clear Descriptions**: Provide meaningful descriptions for all endpoints
2. **Complete Examples**: Include realistic example values
3. **Error Documentation**: Document all possible error responses
4. **Parameter Validation**: Specify validation rules in annotations
5. **Response Schemas**: Define complete response structures

### Code Organization
1. **Group by Tags**: Use tags to organize related endpoints
2. **Consistent Naming**: Use consistent operation IDs and parameter names
3. **Version Control**: Keep documentation in sync with code changes
4. **Regular Updates**: Update documentation when API changes

## Troubleshooting

### Common Issues

#### Documentation Not Updating
```bash
# Clear cache and regenerate
php artisan cache:clear
php artisan l5-swagger:generate
```

#### Routes Not Working
```bash
# Check route list
php artisan route:list | grep api
```

#### Styling Issues
- Clear browser cache
- Check if custom CSS is loading
- Verify asset paths in configuration

### Debugging
1. Check the generated JSON file: `storage/api-docs/api-docs.json`
2. Verify annotations are properly formatted
3. Check Laravel logs for errors
4. Test individual endpoints directly

## Advanced Features

### Custom Response Examples
```php
@OA\JsonContent(
    @OA\Property(property="success", type="boolean", example=true),
    @OA\Property(
        property="data",
        type="object",
        @OA\Property(property="similarity", type="number", format="float", example=0.85)
    )
)
```

### Parameter Validation
```php
@OA\Property(
    property="url1",
    type="string",
    format="uri",
    description="First image URL",
    example="https://example.com/image1.jpg"
)
```

### Security Schemes
```php
@OA\SecurityScheme(
    securityScheme="bearerAuth",
    type="http",
    scheme="bearer"
)
```

## Conclusion

The Swagger documentation provides a comprehensive, interactive way to explore and test the SapientPro Image Comparison API. It serves as both documentation and a testing tool, making API development and integration much more efficient.

For more information about OpenAPI/Swagger specifications, visit:
- [OpenAPI Specification](https://swagger.io/specification/)
- [Swagger UI](https://swagger.io/tools/swagger-ui/)
- [L5-Swagger Documentation](https://github.com/DarkaOnLine/L5-Swagger)
