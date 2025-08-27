<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Image Comparison Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .drop-zone {
            border: 2px dashed #cbd5e1;
            transition: all 0.3s ease;
        }
        .drop-zone.dragover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        .drop-zone.has-image {
            border-color: #10b981;
            background-color: #f0fdf4;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">
                Image Comparison Tool
            </h1>
            
            <!-- Tab Navigation -->
            <div class="flex justify-center mb-8">
                <div class="bg-white rounded-lg shadow-sm p-1">
                    <button id="upload-tab" class="tab-btn active px-6 py-2 rounded-md text-sm font-medium transition-colors">
                        Upload Images
                    </button>
                    <button id="url-tab" class="tab-btn px-6 py-2 rounded-md text-sm font-medium transition-colors">
                        Image URLs
                    </button>
                </div>
            </div>

            <!-- Upload Images Tab -->
            <div id="upload-content" class="tab-content">
                <form id="upload-form" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Image 1 -->
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">First Image</label>
                            <div id="drop-zone-1" class="drop-zone rounded-lg p-6 text-center cursor-pointer">
                                <div class="space-y-2">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="text-sm text-gray-600">
                                        <label for="image1" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                            <span>Upload a file</span>
                                            <input id="image1" name="image1" type="file" class="sr-only" accept="image/*">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                </div>
                                <img id="preview-1" class="hidden max-w-full h-auto rounded mt-4" alt="Preview">
                            </div>
                        </div>

                        <!-- Image 2 -->
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Second Image</label>
                            <div id="drop-zone-2" class="drop-zone rounded-lg p-6 text-center cursor-pointer">
                                <div class="space-y-2">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="text-sm text-gray-600">
                                        <label for="image2" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                            <span>Upload a file</span>
                                            <input id="image2" name="image2" type="file" class="sr-only" accept="image/*">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                </div>
                                <img id="preview-2" class="hidden max-w-full h-auto rounded mt-4" alt="Preview">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                            Compare Images
                        </button>
                    </div>
                </form>
            </div>

            <!-- URL Tab -->
            <div id="url-content" class="tab-content hidden">
                <form id="url-form" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <label for="url1" class="block text-sm font-medium text-gray-700">First Image URL</label>
                            <input type="url" id="url1" name="url1" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="https://example.com/image1.jpg">
                        </div>
                        <div class="space-y-4">
                            <label for="url2" class="block text-sm font-medium text-gray-700">Second Image URL</label>
                            <input type="url" id="url2" name="url2" required 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="https://example.com/image2.jpg">
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                            Compare Images
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results Section -->
            <div id="results" class="hidden mt-8">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Comparison Results</h3
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Similarity Score:</span>
                            <span id="similarity-score" class="text-2xl font-bold text-blue-600">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div id="similarity-bar" class="bg-blue-600 h-4 rounded-full transition-all duration-500" style="width: 0%"></div>
                        </div>
                        <div id="result-message" class="text-sm text-gray-600"></div>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loading" class="hidden mt-8">
                <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                    <div class="inline-flex items-center space-x-2">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                        <span class="text-gray-600">Comparing images...</span>
                    </div>
                </div>
            </div>

            <!-- Error State -->
            <div id="error" class="hidden mt-8">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Error</h3>
                            <div id="error-message" class="mt-2 text-sm text-red-700"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        const uploadTab = document.getElementById('upload-tab');
        const urlTab = document.getElementById('url-tab');
        const uploadContent = document.getElementById('upload-content');
        const urlContent = document.getElementById('url-content');

        uploadTab.addEventListener('click', () => {
            uploadTab.classList.add('active', 'bg-blue-600', 'text-white');
            urlTab.classList.remove('active', 'bg-blue-600', 'text-white');
            urlTab.classList.add('text-gray-500');
            uploadContent.classList.remove('hidden');
            urlContent.classList.add('hidden');
        });

        urlTab.addEventListener('click', () => {
            urlTab.classList.add('active', 'bg-blue-600', 'text-white');
            uploadTab.classList.remove('active', 'bg-blue-600', 'text-white');
            uploadTab.classList.add('text-gray-500');
            urlContent.classList.remove('hidden');
            uploadContent.classList.add('hidden');
        });

        // File upload functionality
        function setupDropZone(dropZoneId, inputId, previewId) {
            const dropZone = document.getElementById(dropZoneId);
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);

            dropZone.addEventListener('click', () => input.click());

            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.classList.add('dragover');
            });

            dropZone.addEventListener('dragleave', () => {
                dropZone.classList.remove('dragover');
            });

            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    input.files = files;
                    handleFileSelect(files[0], preview, dropZone);
                }
            });

            input.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFileSelect(e.target.files[0], preview, dropZone);
                }
            });
        }

        function handleFileSelect(file, preview, dropZone) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    dropZone.classList.add('has-image');
                };
                reader.readAsDataURL(file);
            }
        }

        setupDropZone('drop-zone-1', 'image1', 'preview-1');
        setupDropZone('drop-zone-2', 'image2', 'preview-2');

        // Form submission
        document.getElementById('upload-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log('Form submitted!');
            console.log('Form data:', e.target);
            const formData = new FormData(e.target);
            console.log('FormData entries:');
            for (let [key, value] of formData.entries()) {
                console.log(key, ':', value instanceof File ? `File: ${value.name} (${value.size} bytes)` : value);
            }
            await submitForm('/api/v1/compare/upload', formData);
        });

        document.getElementById('url-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const urlData = {
                url1: formData.get('url1'),
                url2: formData.get('url2')
            };
            await submitForm('/api/v1/compare/urls', urlData, true);
        });

        async function submitForm(url, data, isJson = false) {
            hideAllStates();
            showLoading();

            try {
                const headers = {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                };

                let body = data;
                if (isJson) {
                    headers['Content-Type'] = 'application/json';
                    body = JSON.stringify(data);
                }

                // Debug logging
                console.log('Submitting to:', url);
                console.log('Headers:', headers);
                console.log('Body type:', isJson ? 'JSON' : 'FormData');
                if (!isJson) {
                    console.log('FormData entries:');
                    for (let [key, value] of data.entries()) {
                        console.log(key, ':', value instanceof File ? `File: ${value.name} (${value.size} bytes)` : value);
                    }
                }

                const response = await fetch(url, {
                    method: 'POST',
                    body: body,
                    headers: headers
                });

                console.log('Response status:', response.status);
                const responseData = await response.json();
                console.log('Response data:', responseData);

                if (responseData.success) {
                    showResults(responseData.data.similarity_percentage, responseData.message);
                } else {
                    showError(responseData.error || responseData.message);
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred while comparing images. Please try again.');
            }
        }

        function hideAllStates() {
            document.getElementById('results').classList.add('hidden');
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('error').classList.add('hidden');
        }

        function showLoading() {
            document.getElementById('loading').classList.remove('hidden');
        }

        function showResults(percentage, message) {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('results').classList.remove('hidden');
            document.getElementById('similarity-score').textContent = percentage + '%';
            document.getElementById('similarity-bar').style.width = percentage + '%';
            document.getElementById('result-message').textContent = message;
        }

        function showError(message) {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('error').classList.remove('hidden');
            document.getElementById('error-message').textContent = message;
        }
    </script>
</body>
</html>
