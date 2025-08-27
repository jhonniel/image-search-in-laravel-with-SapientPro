<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Debug Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Debug Test - Image Comparison</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Test File Upload</h2>
            <form id="test-form">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image 1</label>
                        <input type="file" name="image1" accept="image/*" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Image 2</label>
                        <input type="file" name="image2" accept="image/*" class="w-full p-2 border rounded">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Test Upload</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Test URL Comparison</h2>
            <form id="url-test-form">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">URL 1</label>
                        <input type="url" name="url1" value="https://picsum.photos/400/300?random=1" class="w-full p-2 border rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">URL 2</label>
                        <input type="url" name="url2" value="https://picsum.photos/400/300?random=2" class="w-full p-2 border rounded">
                    </div>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Test URLs</button>
                </div>
            </form>
        </div>

        <div id="results" class="bg-white rounded-lg shadow p-6 hidden">
            <h2 class="text-lg font-semibold mb-4">Results</h2>
            <pre id="result-content" class="bg-gray-100 p-4 rounded text-sm overflow-auto"></pre>
        </div>

        <div id="error" class="bg-red-50 border border-red-200 rounded-lg p-4 hidden">
            <h3 class="text-sm font-medium text-red-800">Error</h3>
            <div id="error-content" class="mt-2 text-sm text-red-700"></div>
        </div>
    </div>

    <script>
        // Test file upload
        document.getElementById('test-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('File upload test submitted');
            
            const formData = new FormData(e.target);
            console.log('FormData entries:');
            for (let [key, value] of formData.entries()) {
                console.log(key, ':', value instanceof File ? `File: ${value.name} (${value.size} bytes)` : value);
            }

            try {
                // First test with debug endpoint
                const debugResponse = await fetch('/debug-upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                console.log('Debug response status:', debugResponse.status);
                const debugData = await debugResponse.json();
                console.log('Debug data:', debugData);

                // Then test with actual API
                const response = await fetch('/api/v1/compare/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                console.log('API response status:', response.status);
                const data = await response.json();
                console.log('API response data:', data);

                showResults({
                    debug_info: debugData,
                    api_response: data
                });
            } catch (error) {
                console.error('Error:', error);
                showError(error.message);
            }
        });

        // Test URL comparison
        document.getElementById('url-test-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('URL test submitted');
            
            const formData = new FormData(e.target);
            const urlData = {
                url1: formData.get('url1'),
                url2: formData.get('url2')
            };
            console.log('URL data:', urlData);

            try {
                const response = await fetch('/api/v1/compare/urls', {
                    method: 'POST',
                    body: JSON.stringify(urlData),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Response data:', data);

                showResults(data);
            } catch (error) {
                console.error('Error:', error);
                showError(error.message);
            }
        });

        function showResults(data) {
            document.getElementById('error').classList.add('hidden');
            document.getElementById('results').classList.remove('hidden');
            document.getElementById('result-content').textContent = JSON.stringify(data, null, 2);
        }

        function showError(message) {
            document.getElementById('results').classList.add('hidden');
            document.getElementById('error').classList.remove('hidden');
            document.getElementById('error-content').textContent = message;
        }
    </script>
</body>
</html>
