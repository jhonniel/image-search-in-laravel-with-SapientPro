<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Image Search & Comparison Tool

A Laravel 12 application that provides image comparison functionality using the `sapientpro/image-comparator` package.

## Problem Solved

The original issue was that `sapientpro/image-comparator-laravel` package doesn't support Laravel 12. This solution uses the base `sapientpro/image-comparator` package and integrates it with Laravel 12 through a custom service provider.

## Features

- **Image Comparison**: Compare two images and get similarity percentage
- **Multiple Input Methods**: Upload images or provide image URLs
- **Modern UI**: Beautiful, responsive interface with drag-and-drop functionality
- **Real-time Results**: Instant comparison results with visual feedback

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy environment file:
   ```bash
   cp .env.example .env
   ```
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Start the development server:
   ```bash
   php artisan serve
   ```

## Usage

1. Navigate to `/image-comparison` in your browser
2. Choose between uploading images or providing URLs
3. Select or drag-and-drop two images
4. Click "Compare Images" to get similarity results

## API Endpoints

### RESTful API v1

The application provides a comprehensive RESTful API for image comparison:

#### Core Endpoints
- `POST /api/v1/compare/upload` - Compare two uploaded images
- `POST /api/v1/compare/urls` - Compare images from URLs
- `POST /api/v1/compare/batch` - Compare multiple images in batch (up to 5)
- `GET /api/v1/health` - Check API health status
- `GET /api/v1/docs` - Get API documentation

#### Legacy Endpoints (for backward compatibility)
- `POST /api/compare/images` - Compare uploaded images
- `POST /api/compare/urls` - Compare images from URLs

### Example API Usage

```bash
# Health check
curl -X GET http://localhost:8000/api/v1/health

# Compare uploaded images
curl -X POST http://localhost:8000/api/v1/compare/upload \
  -F "image1=@path/to/image1.jpg" \
  -F "image2=@path/to/image2.jpg"

# Compare images from URLs
curl -X POST http://localhost:8000/api/v1/compare/urls \
  -H "Content-Type: application/json" \
  -d '{
    "url1": "https://example.com/image1.jpg",
    "url2": "https://example.com/image2.jpg"
  }'

# Batch comparison
curl -X POST http://localhost:8000/api/v1/compare/batch \
  -F "images[]=@path/to/image1.jpg" \
  -F "images[]=@path/to/image2.jpg" \
  -F "images[]=@path/to/image3.jpg"
```

### API Testing

Run the included test script to verify API functionality:

```bash
php test_api.php
```

## Interactive API Documentation

The project includes comprehensive Swagger/OpenAPI documentation:

### Access Swagger UI
Visit: **http://localhost:8000/api/documentation**

### Features
- **Interactive Testing**: Test all API endpoints directly from the browser
- **Auto-generated**: Documentation is generated from code annotations
- **Real-time Validation**: See request/response formats instantly
- **Beautiful Interface**: Custom styled with modern design

### Documentation Files
- **Swagger Setup Guide**: `SWAGGER_SETUP.md`
- **API Documentation**: `API_DOCUMENTATION.md`
- **OpenAPI JSON**: `storage/api-docs/api-docs.json`

### Regenerating Documentation
After making changes to API annotations:

```bash
php artisan l5-swagger:generate
```

## Technical Implementation

### Service Provider
The `ImageComparatorServiceProvider` integrates the base image comparator package with Laravel's dependency injection container.

### Controllers
- `ImageComparisonController`: Handles web interface requests
- `ImageComparisonApiController`: Handles API requests with comprehensive validation and error handling

### Frontend
A modern, responsive interface built with Tailwind CSS and vanilla JavaScript, featuring:
- Drag-and-drop file uploads
- Tabbed interface for different input methods
- Real-time preview of selected images
- Loading states and error handling
- Visual similarity score display

## Dependencies

- Laravel 12
- `sapientpro/image-comparator` (v1.0.1)
- Tailwind CSS (via CDN)

## File Structure

```
app/
├── Http/Controllers/
│   ├── ImageComparisonController.php
│   └── Api/
│       └── ImageComparisonApiController.php
├── Providers/
│   └── ImageComparatorServiceProvider.php
resources/views/
├── image-comparison.blade.php
└── vendor/l5-swagger/
│   └── index.blade.php (custom styled)
routes/
├── web.php
└── api.php
config/
└── l5-swagger.php (Swagger configuration)
storage/
└── api-docs/
│   └── api-docs.json (generated OpenAPI spec)
bootstrap/
└── app.php (updated with service provider)
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
