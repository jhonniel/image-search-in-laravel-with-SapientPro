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
                    <button id="match-tab" class="tab-btn px-6 py-2 rounded-md text-sm font-medium transition-colors">
                        Find Match
                    </button>
                    <button id="manage-tab" class="tab-btn px-6 py-2 rounded-md text-sm font-medium transition-colors">
                        Manage References
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

            <!-- Find Match Tab -->
            <div id="match-content" class="tab-content hidden">
                <form id="match-form" class="space-y-6">
                    <div class="max-w-md mx-auto">
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Upload Images to Find Matches</label>
                            <div id="match-drop-zone" class="drop-zone rounded-lg p-6 text-center cursor-pointer">
                                <div class="space-y-2">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="text-sm text-gray-600">
                                        <label for="match-images" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                            <span>Upload files</span>
                                            <input id="match-images" name="images[]" type="file" class="sr-only" accept="image/*" multiple>
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB each (max 5 images)</p>
                                </div>
                                <div id="match-preview" class="hidden grid grid-cols-2 gap-2 mt-4"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                            <div class="space-y-2">
                                <label for="threshold" class="block text-sm font-medium text-gray-700">Similarity Threshold</label>
                                <input type="number" id="threshold" name="threshold" min="0" max="1" step="0.1" value="0.7"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500">0.0 = Any match, 1.0 = Perfect match</p>
                            </div>
                            <div class="space-y-2">
                                <label for="limit" class="block text-sm font-medium text-gray-700">Max Results</label>
                                <input type="number" id="limit" name="limit" min="1" max="50" value="10"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500">Maximum number of matches to return</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                            Find Matching Images
                        </button>
                    </div>
                </form>
            </div>

            <!-- Manage References Tab -->
            <div id="manage-content" class="tab-content hidden">
                <div class="space-y-6">
                    <!-- Upload Reference Images -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Upload Reference Images</h3>
                        <form id="reference-upload-form" class="space-y-4">
                            <div class="space-y-4">
                                <label class="block text-sm font-medium text-gray-700">Select Reference Images</label>
                                <div id="reference-drop-zone" class="drop-zone rounded-lg p-6 text-center cursor-pointer">
                                    <div class="space-y-2">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="text-sm text-gray-600">
                                            <label for="reference-images" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                                                <span>Upload files</span>
                                                <input id="reference-images" name="images[]" type="file" class="sr-only" accept="image/*" multiple>
                                            </label>
                                            <p class="pl-1">or drag and drop multiple images</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB each (max 5 images)</p>
                                    </div>
                                    <div id="reference-preview" class="hidden mt-4 grid grid-cols-2 md:grid-cols-4 gap-2"></div>
                                </div>
                            </div>
                            <div class="flex justify-center space-x-4">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                                    Upload Reference Images
                                </button>
                                <button type="button" onclick="clearReferenceUploadForm()" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                                    Clear
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- List Reference Images -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Reference Images</h3>
                            <button id="refresh-references" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                Refresh
                            </button>
                        </div>
                        <div id="reference-images-list" class="space-y-2">
                            <div class="text-center text-gray-500 py-4">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
                                <p class="mt-2">Loading reference images...</p>
                            </div>
                        </div>
                    </div>
                </div>
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
        const tabs = {
            upload: { tab: document.getElementById('upload-tab'), content: document.getElementById('upload-content') },
            url: { tab: document.getElementById('url-tab'), content: document.getElementById('url-content') },
            match: { tab: document.getElementById('match-tab'), content: document.getElementById('match-content') },
            manage: { tab: document.getElementById('manage-tab'), content: document.getElementById('manage-content') }
        };

        function switchTab(activeTabName) {
            // Hide all content and remove active states
            Object.values(tabs).forEach(tab => {
                tab.content.classList.add('hidden');
                tab.tab.classList.remove('active', 'bg-blue-600', 'text-white');
                tab.tab.classList.add('text-gray-500');
            });

            // Show active tab content and set active state
            const activeTab = tabs[activeTabName];
            activeTab.content.classList.remove('hidden');
            activeTab.tab.classList.remove('text-gray-500');
            activeTab.tab.classList.add('active', 'bg-blue-600', 'text-white');

            // Load reference images if manage tab is selected
            if (activeTabName === 'manage') {
                loadReferenceImages();
            }
        }

        Object.keys(tabs).forEach(tabName => {
            tabs[tabName].tab.addEventListener('click', () => switchTab(tabName));
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

        // Multiple file drop zone functionality
        function setupMultipleDropZone(dropZoneId, inputId, previewId) {
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
                const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
                if (files.length > 0) {
                    // Check if too many files are dropped
                    if (files.length > 5) {
                        showError('Maximum 5 images allowed per upload. Please drop fewer images.');
                        return;
                    }

                    // Create a new DataTransfer object to properly set the files
                    const dt = new DataTransfer();
                    files.forEach(file => dt.items.add(file));
                    input.files = dt.files;
                    handleMultipleFileSelect(files, preview, dropZone);
                }
            });

            input.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    const files = Array.from(e.target.files).filter(file => file.type.startsWith('image/'));

                    // Check if too many files are selected
                    if (files.length > 5) {
                        showError('Maximum 5 images allowed per upload. Please select fewer images.');
                        // Clear the input
                        e.target.value = '';
                        return;
                    }

                    handleMultipleFileSelect(files, preview, dropZone);
                }
            });
        }

        function handleMultipleFileSelect(files, preview, dropZone) {
            preview.innerHTML = '';
            preview.classList.remove('hidden');
            dropZone.classList.add('has-image');

            // Add file count indicator
            const fileCount = document.createElement('div');
            fileCount.className = 'col-span-full p-2 bg-blue-50 rounded border text-sm text-blue-800 font-medium';
            fileCount.textContent = `${files.length}/5 file(s) selected`;
            preview.appendChild(fileCount);

            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'w-full h-20 object-cover rounded border';
                    img.alt = file.name;
                    img.title = `${file.name} (${formatBytes(file.size)})`;
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }

        setupDropZone('drop-zone-1', 'image1', 'preview-1');
        setupDropZone('drop-zone-2', 'image2', 'preview-2');
        setupMultipleDropZone('match-drop-zone', 'match-images', 'match-preview');
        setupMultipleDropZone('reference-drop-zone', 'reference-images', 'reference-preview');

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

        // Match form submission
        document.getElementById('match-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const files = document.getElementById('match-images').files;

            // Check if any files are selected
            if (files.length === 0) {
                showError('Please select at least one image to find matches.');
                return;
            }

            // Check if too many files are selected
            if (files.length > 5) {
                showError('Maximum 5 images allowed per search. Please select fewer images.');
                return;
            }

            // Ensure form data includes all files
            for (let i = 0; i < files.length; i++) {
                formData.append('images[]', files[i]);
            }

            await submitForm('/api/v1/compare/find-match', formData);
        });

        // Reference upload form submission
        document.getElementById('reference-upload-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const files = document.getElementById('reference-images').files;

            // Check if any files are selected
            if (files.length === 0) {
                showError('Please select at least one image to upload.');
                return;
            }

            // Check if too many files are selected
            if (files.length > 5) {
                showError('Maximum 5 images allowed per upload. Please select fewer images.');
                return;
            }

            // Debug: Log form data
            console.log('Uploading files:', files.length);
            for (let i = 0; i < files.length; i++) {
                console.log('File', i, ':', files[i].name, files[i].size, files[i].type);
            }

            // Ensure form data includes all files
            console.log('FormData entries:');
            for (let [key, value] of formData.entries()) {
                if (value instanceof File) {
                    console.log(key, ':', value.name, value.size, value.type);
                } else {
                    console.log(key, ':', value);
                }
            }

            await submitForm('/api/v1/reference-images/upload', formData);
        });

        // Refresh reference images button
        document.getElementById('refresh-references').addEventListener('click', () => {
            loadReferenceImages();
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
                    if (url.includes('find-match')) {
                        showMatchResults(responseData.data);
                    } else if (url.includes('reference-images/upload')) {
                        showUploadSuccess(responseData.data);
                        loadReferenceImages(); // Refresh the reference images list
                    } else {
                        showResults(responseData.data.similarity_percentage, responseData.message);
                    }
                } else {
                    // Handle validation errors
                    if (responseData.errors) {
                        const errorMessages = Object.values(responseData.errors).flat().join(', ');
                        showError('Validation failed: ' + errorMessages);
                    } else {
                        showError(responseData.error || responseData.message);
                    }
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

        function showMatchResults(data) {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('results').classList.remove('hidden');

            const resultsContainer = document.getElementById('results');
            resultsContainer.innerHTML = `
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Match Results</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Total Matches Found:</span>
                            <span class="text-2xl font-bold text-green-600">${data.total_matches}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Threshold Used:</span>
                            <span class="text-lg font-semibold text-blue-600">${(data.threshold_used * 100).toFixed(1)}%</span>
                        </div>
                        ${data.uploaded_images_count ? `
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Images Searched:</span>
                            <span class="text-lg font-semibold text-purple-600">${data.uploaded_images_count}</span>
                        </div>
                        ` : ''}
                        <div id="match-message" class="text-sm text-gray-600">${data.total_matches > 0 ?
                            `Found ${data.total_matches} matching image(s) with ${data.uploaded_images_count || 1} uploaded image(s)` :
                            'No matching images found with the given threshold'}</div>
                    </div>
                    ${data.matches && data.matches.length > 0 ? `
                        <div class="mt-6">
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Matching Images:</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                ${data.matches.map(match => `
                                    <div class="border rounded-lg p-4 bg-gray-50">
                                        <div class="aspect-w-16 aspect-h-9 mb-3">
                                            <img src="/${match.path}" alt="${match.reference_filename}" class="w-full h-32 object-cover rounded">
                                        </div>
                                        <div class="space-y-2">
                                            <p class="text-sm font-medium text-gray-800 truncate" title="${match.reference_filename}">${match.reference_filename}</p>
                                            ${match.uploaded_image ? `
                                            <p class="text-xs text-gray-500 truncate" title="Matched by: ${match.uploaded_image}">Matched by: ${match.uploaded_image}</p>
                                            ` : ''}
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm text-gray-600">Similarity:</span>
                                                <span class="text-sm font-bold text-green-600">${match.similarity_percentage}%</span>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
        }

        function showUploadSuccess(data) {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('results').classList.remove('hidden');

            const resultsContainer = document.getElementById('results');
            resultsContainer.innerHTML = `
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Upload Successful</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Images Uploaded:</span>
                            <span class="text-2xl font-bold text-green-600">${data.total_uploaded}</span>
                        </div>
                        <div class="text-sm text-gray-600">Successfully uploaded ${data.total_uploaded} reference image(s)</div>
                        ${data.uploaded_images && data.uploaded_images.length > 0 ? `
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Uploaded Files:</h4>
                                <ul class="text-sm text-gray-600 space-y-1">
                                    ${data.uploaded_images.map(img => `
                                        <li class="flex justify-between">
                                            <span>${img.original_name}</span>
                                            <span class="text-gray-500">${formatBytes(img.size)}</span>
                                        </li>
                                    `).join('')}
                                </ul>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;

            // Clear the form after successful upload
            clearReferenceUploadForm();
        }

        async function loadReferenceImages() {
            const listContainer = document.getElementById('reference-images-list');

            try {
                listContainer.innerHTML = `
                    <div class="text-center text-gray-500 py-4">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="mt-2">Loading reference images...</p>
                    </div>
                `;

                const response = await fetch('/api/v1/reference-images', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                const data = await response.json();

                if (data.success) {
                    if (data.data.images.length === 0) {
                        listContainer.innerHTML = `
                            <div class="text-center text-gray-500 py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-lg font-medium text-gray-900">No reference images found</p>
                                <p class="text-sm text-gray-500">Upload some images using the form above to get started.</p>
                            </div>
                        `;
                    } else {
                        listContainer.innerHTML = `
                            <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-blue-800">
                                        <strong>${data.data.total_images}</strong> reference images
                                        (${data.data.total_size})
                                    </p>
                                    <div class="flex items-center space-x-2">
                                        <label class="flex items-center space-x-2 text-sm text-blue-800">
                                            <input type="checkbox" id="select-all-images" onchange="toggleSelectAll()" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                            <span>Select All</span>
                                        </label>
                                        <button id="bulk-delete-btn" onclick="bulkDeleteImages()" disabled class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                            Delete Selected
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                ${data.data.images.map(image => `
                                    <div class="border rounded-lg p-4 bg-white relative">
                                        <div class="absolute top-2 left-2">
                                            <input type="checkbox" class="image-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" value="${image.filename}" onchange="updateBulkDeleteButton()">
                                        </div>
                                        <div class="aspect-w-16 aspect-h-9 mb-3">
                                            <img src="/${image.path}" alt="${image.original_name}" class="w-full h-32 object-cover rounded">
                                        </div>
                                        <div class="space-y-2">
                                            <p class="text-sm font-medium text-gray-800 truncate" title="${image.original_name}">${image.original_name}</p>
                                            <p class="text-xs text-gray-500">${image.filename}</p>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-gray-500">Size:</span>
                                                <span class="text-xs text-gray-600">${formatBytes(image.size)}</span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-gray-500">Uploaded:</span>
                                                <span class="text-xs text-gray-600">${new Date(image.uploaded_at).toLocaleDateString()}</span>
                                            </div>
                                            <button onclick="deleteReferenceImage('${image.filename}')"
                                                    class="w-full mt-2 bg-red-600 hover:bg-red-700 text-white text-xs font-medium py-1 px-2 rounded transition-colors">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        `;
                    }
                } else {
                    listContainer.innerHTML = `
                        <div class="text-center text-red-500 py-4">
                            <p>Error loading reference images: ${data.error || data.message}</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading reference images:', error);
                listContainer.innerHTML = `
                    <div class="text-center text-red-500 py-4">
                        <p>Error loading reference images. Please try again.</p>
                    </div>
                `;
            }
        }

        async function deleteReferenceImage(filename) {
            if (!confirm('Are you sure you want to delete this reference image?')) {
                return;
            }

            try {
                const response = await fetch(`/api/v1/reference-images/${filename}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                const data = await response.json();

                if (data.success) {
                    loadReferenceImages(); // Refresh the list
                } else {
                    alert('Error deleting image: ' + (data.error || data.message));
                }
            } catch (error) {
                console.error('Error deleting reference image:', error);
                alert('Error deleting image. Please try again.');
            }
        }

        // Make clearReferenceUploadForm globally accessible
        window.clearReferenceUploadForm = function() {
            // Clear the file input
            const fileInput = document.getElementById('reference-images');
            fileInput.value = '';

            // Clear the preview
            const preview = document.getElementById('reference-preview');
            preview.innerHTML = '';
            preview.classList.add('hidden');

            // Reset the drop zone
            const dropZone = document.getElementById('reference-drop-zone');
            dropZone.classList.remove('has-image');
        }

        // Bulk selection functions
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('select-all-images');
            const imageCheckboxes = document.querySelectorAll('.image-checkbox');

            imageCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });

            updateBulkDeleteButton();
        }

        function updateBulkDeleteButton() {
            const selectedCheckboxes = document.querySelectorAll('.image-checkbox:checked');
            const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
            const selectAllCheckbox = document.getElementById('select-all-images');
            const allCheckboxes = document.querySelectorAll('.image-checkbox');

            if (bulkDeleteBtn) {
                bulkDeleteBtn.disabled = selectedCheckboxes.length === 0;
                bulkDeleteBtn.textContent = selectedCheckboxes.length > 0 ?
                    `Delete Selected (${selectedCheckboxes.length})` : 'Delete Selected';
            }

            // Update select all checkbox state
            if (selectAllCheckbox && allCheckboxes.length > 0) {
                if (selectedCheckboxes.length === 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                } else if (selectedCheckboxes.length === allCheckboxes.length) {
                    selectAllCheckbox.checked = true;
                    selectAllCheckbox.indeterminate = false;
                } else {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = true;
                }
            }
        }

        async function bulkDeleteImages() {
            const selectedCheckboxes = document.querySelectorAll('.image-checkbox:checked');

            if (selectedCheckboxes.length === 0) {
                showError('No images selected for deletion.');
                return;
            }

            const selectedFilenames = Array.from(selectedCheckboxes).map(cb => cb.value);

            // Show confirmation with image names
            const imageNames = selectedFilenames.slice(0, 5).join(', ');
            const moreText = selectedFilenames.length > 5 ? ` and ${selectedFilenames.length - 5} more` : '';

            if (!confirm(`Are you sure you want to delete ${selectedFilenames.length} selected image(s)?\n\nImages to delete:\n${imageNames}${moreText}\n\nThis action cannot be undone.`)) {
                return;
            }

            try {
                showLoading('Deleting selected images...');

                // Use bulk delete API
                const response = await fetch('/api/v1/reference-images/bulk', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        filenames: selectedFilenames
                    })
                });

                const result = await response.json();
                hideLoading();

                if (result.success) {
                    const { deleted_count, failed_count } = result.data;

                    if (deleted_count > 0) {
                        showSuccess(`Successfully deleted ${deleted_count} image(s).`);
                        if (failed_count > 0) {
                            showError(`Failed to delete ${failed_count} image(s).`);
                        }
                        // Reload the reference images list
                        loadReferenceImages();
                    } else {
                        showError('Failed to delete any images. Please try again.');
                    }
                } else {
                    showError(`Failed to delete images: ${result.error || 'Unknown error'}`);
                }

            } catch (error) {
                hideLoading();
                showError('An error occurred while deleting images. Please try again.');
                console.error('Bulk delete error:', error);
            }
        }

        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
    </script>
</body>
</html>
