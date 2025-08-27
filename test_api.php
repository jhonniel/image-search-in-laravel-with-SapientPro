<?php

/**
 * SapientPro Image Comparison API Test Script
 * 
 * This script demonstrates how to use the API endpoints
 * Run this script to test the API functionality
 */

class SapientProApiTester
{
    private string $baseUrl = 'http://localhost:8000/api/v1';
    
    public function runTests(): void
    {
        echo "🧪 SapientPro Image Comparison API Tests\n";
        echo "==========================================\n\n";
        
        // Test 1: Health Check
        $this->testHealthCheck();
        
        // Test 2: API Documentation
        $this->testApiDocumentation();
        
        // Test 3: URL Comparison (using sample images)
        $this->testUrlComparison();
        
        echo "\n✅ All tests completed!\n";
    }
    
    private function testHealthCheck(): void
    {
        echo "1. Testing Health Check...\n";
        
        $response = $this->makeRequest('GET', '/health');
        
        if ($response && $response['success']) {
            echo "   ✅ Health check passed\n";
            echo "   📊 Status: {$response['data']['status']}\n";
            echo "   🔧 Version: {$response['data']['version']}\n";
        } else {
            echo "   ❌ Health check failed\n";
        }
        
        echo "\n";
    }
    
    private function testApiDocumentation(): void
    {
        echo "2. Testing API Documentation...\n";
        
        $response = $this->makeRequest('GET', '/docs');
        
        if ($response && $response['success']) {
            echo "   ✅ API documentation retrieved\n";
            echo "   📚 API Name: {$response['data']['api_name']}\n";
            echo "   🔗 Endpoints: " . count($response['data']['endpoints']) . " available\n";
            
            foreach ($response['data']['endpoints'] as $endpoint) {
                echo "      - {$endpoint['method']} {$endpoint['path']}: {$endpoint['description']}\n";
            }
        } else {
            echo "   ❌ Failed to retrieve API documentation\n";
        }
        
        echo "\n";
    }
    
    private function testUrlComparison(): void
    {
        echo "3. Testing URL Comparison...\n";
        
        // Using sample images from a public CDN
        $data = [
            'url1' => 'https://picsum.photos/400/300?random=1',
            'url2' => 'https://picsum.photos/400/300?random=2'
        ];
        
        $response = $this->makeRequest('POST', '/compare/urls', $data);
        
        if ($response && $response['success']) {
            echo "   ✅ URL comparison successful\n";
            echo "   📊 Similarity: {$response['data']['similarity_percentage']}%\n";
            echo "   🆔 Comparison ID: {$response['data']['comparison_id']}\n";
            echo "   ⏰ Timestamp: {$response['data']['timestamp']}\n";
        } else {
            echo "   ❌ URL comparison failed\n";
            if (isset($response['message'])) {
                echo "   💬 Error: {$response['message']}\n";
            }
        }
        
        echo "\n";
    }
    
    private function makeRequest(string $method, string $endpoint, array $data = null): ?array
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            
            if ($data) {
                if (isset($data['url1'])) {
                    // JSON request for URL comparison
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Accept: application/json'
                    ]);
                } else {
                    // Form data for file uploads
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                }
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "   ⚠️  cURL Error: $error\n";
            return null;
        }
        
        if ($httpCode !== 200) {
            echo "   ⚠️  HTTP Error: $httpCode\n";
            return null;
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "   ⚠️  JSON Decode Error: " . json_last_error_msg() . "\n";
            return null;
        }
        
        return $decoded;
    }
}

// Example usage functions
function demonstrateFileUpload(): void
{
    echo "📁 File Upload Example:\n";
    echo "curl -X POST http://localhost:8000/api/v1/compare/upload \\\n";
    echo "  -F \"image1=@/path/to/image1.jpg\" \\\n";
    echo "  -F \"image2=@/path/to/image2.jpg\"\n\n";
}

function demonstrateBatchComparison(): void
{
    echo "📦 Batch Comparison Example:\n";
    echo "curl -X POST http://localhost:8000/api/v1/compare/batch \\\n";
    echo "  -F \"images[]=@/path/to/image1.jpg\" \\\n";
    echo "  -F \"images[]=@/path/to/image2.jpg\" \\\n";
    echo "  -F \"images[]=@/path/to/image3.jpg\"\n\n";
}

function demonstrateJavaScriptUsage(): void
{
    echo "🟨 JavaScript Example:\n";
    echo "```javascript\n";
    echo "const formData = new FormData();\n";
    echo "formData.append('image1', file1);\n";
    echo "formData.append('image2', file2);\n\n";
    echo "fetch('http://localhost:8000/api/v1/compare/upload', {\n";
    echo "  method: 'POST',\n";
    echo "  body: formData\n";
    echo "})\n";
    echo ".then(response => response.json())\n";
    echo ".then(data => {\n";
    echo "  if (data.success) {\n";
    echo "    console.log(`Similarity: \${data.data.similarity_percentage}%`);\n";
    echo "  }\n";
    echo "});\n";
    echo "```\n\n";
}

// Run the tests
if (php_sapi_name() === 'cli') {
    $tester = new SapientProApiTester();
    $tester->runTests();
    
    echo "\n📖 Additional Examples:\n";
    echo "=====================\n\n";
    
    demonstrateFileUpload();
    demonstrateBatchComparison();
    demonstrateJavaScriptUsage();
    
    echo "🌐 API Base URL: http://localhost:8000/api/v1\n";
    echo "📚 Full Documentation: API_DOCUMENTATION.md\n";
} else {
    echo "This script should be run from the command line: php test_api.php";
}
