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

### Core Functionality
- **Image Comparison**: Compare two images and get similarity percentage
- **Multiple Input Methods**: Upload images or provide image URLs
- **Batch Comparison**: Compare up to 5 images simultaneously
- **Image Matching**: Find similar images from a reference database
- **Multiple Image Search**: Upload 1-5 images to find matches

### Advanced Features
- **Smart Threshold Filtering**: Default 70% similarity threshold (configurable)
- **Reference Image Management**: Upload, view, and manage reference images
- **Duplicate Detection**: Automatic removal of duplicate matches
- **High-Performance Matching**: Optimized comparison algorithms

### User Interface
- **Modern UI**: Beautiful, responsive interface with Tailwind CSS
- **Drag-and-Drop**: Intuitive file upload with drag-and-drop support
- **Real-time Preview**: Instant preview of uploaded images
- **Tabbed Interface**: Organized sections for different functionalities
- **Visual Feedback**: Loading states, progress indicators, and error handling

### API & Integration
- **RESTful API**: Comprehensive API with OpenAPI/Swagger documentation
- **Multiple Endpoints**: Upload, URL, batch, and matching endpoints
- **Health Monitoring**: Built-in health checks and status monitoring
- **Error Handling**: Robust error handling with detailed messages

## Installation & Setup

### Prerequisites

Before installing this project, make sure you have the following software installed on your system:

#### Required Software:
- **PHP 8.2 or higher** - [Download PHP](https://www.php.net/downloads.php)
- **Composer** - [Download Composer](https://getcomposer.org/download/)
- **Node.js 18+ and npm** - [Download Node.js](https://nodejs.org/)
- **Git** - [Download Git](https://git-scm.com/downloads)

#### Optional (for better development experience):
- **Laravel Herd** (macOS) or **Laravel Valet** - For local development
- **Docker & Docker Compose** - For containerized development

### Step-by-Step Installation

#### 1. Clone the Repository
```bash
git clone <repository-url>
cd image-search
```

#### 2. Install PHP Dependencies
```bash
composer install
```

#### 3. Environment Configuration
```bash
# Copy the environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

#### 4. Database Setup
The project uses SQLite by default (no additional database setup required):
```bash
# Create SQLite database (if it doesn't exist)
touch database/database.sqlite

# Run migrations
php artisan migrate
```

#### 5. Install Node.js Dependencies
```bash
npm install
```

#### 6. Storage Setup
```bash
# Create storage directories
php artisan storage:link

# Ensure storage directories have proper permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

#### 7. Start the Development Server

**Option A: Using Laravel's built-in server**
```bash
php artisan serve
```
The application will be available at: http://localhost:8000

**Option B: Using the development script (recommended)**
```bash
composer dev
```
This command runs multiple services concurrently:
- Laravel server (port 8000)
- Queue worker
- Log monitoring
- Vite development server (if available)

**Option C: Using Laravel Herd (macOS)**
```bash
# If you have Laravel Herd installed
herd link
```
The application will be available at: http://image-search.test

### Verification

#### 1. Check Application Health
```bash
# Test the API health endpoint
curl http://localhost:8000/api/v1/health
```

#### 2. Access the Web Interface
Open your browser and navigate to:
- **Main Application**: http://localhost:8000
- **Image Comparison Tool**: http://localhost:8000/image-comparison
- **API Documentation**: http://localhost:8000/api/documentation

#### 3. Run Tests
```bash
# Run the included API test script
php test_api.php

# Run Laravel tests (if available)
php artisan test
```

### Troubleshooting

#### Common Issues:

**1. Permission Errors**
```bash
# Fix storage permissions
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

**2. Composer Memory Issues**
```bash
# Increase memory limit
php -d memory_limit=2G /usr/local/bin/composer install
```

**3. Node.js/npm Issues**
```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

**4. Vite Command Not Found**
```bash
# Install Vite globally
npm install -g vite

# Or use npx
npx vite
```

**5. Port Already in Use**
```bash
# Use a different port
php artisan serve --port=8080
```

### Production Deployment

#### 1. Environment Configuration
```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Generate optimized autoloader
composer install --optimize-autoloader --no-dev

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 2. Web Server Configuration
Configure your web server (Apache/Nginx) to point to the `public` directory.

#### 3. Queue Workers
```bash
# Start queue workers for background processing
php artisan queue:work
```

### Development Commands

#### Useful Commands:
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Regenerate API documentation
php artisan l5-swagger:generate

# Run database migrations
php artisan migrate

# Create new migration
php artisan make:migration create_table_name

# Run tests
php artisan test

# Start queue worker
php artisan queue:work

# Monitor logs
php artisan pail
```

## Usage

### Web Interface

1. **Navigate to the application**: http://localhost:8000/image-comparison
2. **Choose your functionality**:
   - **Upload Tab**: Compare two uploaded images
   - **URL Tab**: Compare images from URLs
   - **Find Match Tab**: Find similar images from reference database
   - **Manage Tab**: Upload and manage reference images

3. **For Image Comparison**:
   - Select or drag-and-drop two images
   - Click "Compare Images" to get similarity results

4. **For Image Matching**:
   - Upload 1-5 images to search with
   - Set similarity threshold (default: 70%)
   - Click "Find Matching Images" to see results

5. **For Reference Management**:
   - Upload reference images (up to 5 at a time)
   - View all stored reference images
   - Delete individual or bulk delete images

### Key Features

- **70% Similarity Threshold**: Only shows high-confidence matches by default
- **Multiple Image Support**: Upload up to 5 images for matching
- **Drag & Drop**: Intuitive file upload interface
- **Real-time Preview**: See uploaded images before processing
- **Visual Results**: Clear display of similarity percentages and matched images

## API Endpoints

### RESTful API v1

The application provides a comprehensive RESTful API for image comparison:

#### Core Endpoints
- `POST /api/v1/compare/upload` - Compare two uploaded images
- `POST /api/v1/compare/urls` - Compare images from URLs
- `POST /api/v1/compare/batch` - Compare multiple images in batch (up to 5)
- `POST /api/v1/compare/find-match` - Find matching images from reference database (1-5 images)
- `GET /api/v1/health` - Check API health status
- `GET /api/v1/docs` - Get API documentation

#### Reference Image Management
- `POST /api/v1/reference-images/upload` - Upload reference images (up to 5)
- `GET /api/v1/reference-images` - List all reference images
- `DELETE /api/v1/reference-images/{filename}` - Delete specific reference image
- `DELETE /api/v1/reference-images/bulk` - Bulk delete reference images

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

# Find matching images (with 70% threshold)
curl -X POST http://localhost:8000/api/v1/compare/find-match \
  -F "images[]=@path/to/search_image1.jpg" \
  -F "images[]=@path/to/search_image2.jpg" \
  -F "threshold=0.7" \
  -F "limit=10"

# Upload reference images
curl -X POST http://localhost:8000/api/v1/reference-images/upload \
  -F "images[]=@path/to/reference1.jpg" \
  -F "images[]=@path/to/reference2.jpg"

# List reference images
curl -X GET http://localhost:8000/api/v1/reference-images
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
