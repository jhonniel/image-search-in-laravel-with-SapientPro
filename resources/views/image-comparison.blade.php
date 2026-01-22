<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Image Comparison Tool</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                            <div id="drop-zone-1" class="relative border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-all duration-200 hover:border-blue-400 hover:bg-blue-50 cursor-pointer">
                                <input id="image1" name="image1" type="file" class="hidden" accept="image/*">
                                
                                <div id="drop-zone-1-content" class="space-y-4">
                                    <div class="flex justify-center">
                                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-cloud-upload-alt text-blue-600 text-2xl"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-lg font-medium text-gray-700 mb-1">
                                            <span class="text-blue-600">Click to upload</span> or drag and drop
                                        </p>
                                        <p class="text-sm text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                    </div>
                                    <button type="button" onclick="document.getElementById('image1').click()" 
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                        <i class="fas fa-folder-open mr-2"></i>
                                        Browse Files
                                    </button>
                                </div>
                                
                                <img id="preview-1" class="hidden max-w-full h-auto rounded mt-4 mx-auto" alt="Preview">
                            </div>
                        </div>

                        <!-- Image 2 -->
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Second Image</label>
                            <div id="drop-zone-2" class="relative border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-all duration-200 hover:border-blue-400 hover:bg-blue-50 cursor-pointer">
                                <input id="image2" name="image2" type="file" class="hidden" accept="image/*">
                                
                                <div id="drop-zone-2-content" class="space-y-4">
                                    <div class="flex justify-center">
                                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-cloud-upload-alt text-blue-600 text-2xl"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-lg font-medium text-gray-700 mb-1">
                                            <span class="text-blue-600">Click to upload</span> or drag and drop
                                        </p>
                                        <p class="text-sm text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                    </div>
                                    <button type="button" onclick="document.getElementById('image2').click()" 
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                        <i class="fas fa-folder-open mr-2"></i>
                                        Browse Files
                                    </button>
                                </div>
                                
                                <img id="preview-2" class="hidden max-w-full h-auto rounded mt-4 mx-auto" alt="Preview">
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
                            <label class="block text-sm font-medium text-gray-700 mb-3">Upload Images to Find Matches</label>
                            <div id="match-drop-zone" class="relative border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-all duration-200 hover:border-blue-400 hover:bg-blue-50 cursor-pointer">
                                <input id="match-images" name="images[]" type="file" class="hidden" accept="image/*" multiple>
                                
                                <div id="match-drop-zone-content" class="space-y-4">
                                    <div class="flex justify-center">
                                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-cloud-upload-alt text-blue-600 text-2xl"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-lg font-medium text-gray-700 mb-1">
                                            <span class="text-blue-600">Click to upload</span> or drag and drop
                                        </p>
                                        <p class="text-sm text-gray-500">PNG, JPG, GIF up to 10MB each (max 5 images)</p>
                                    </div>
                                    <button type="button" onclick="document.getElementById('match-images').click()" 
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                        <i class="fas fa-folder-open mr-2"></i>
                                        Browse Files
                                    </button>
                                </div>
                                
                                <div id="match-preview" class="hidden grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mt-4"></div>
                            </div>
                        </div>

                        <!-- Email Field -->
                        <div class="space-y-4 mt-6">
                            <div>
                                <label for="match-uploader-email" class="block text-sm font-medium text-gray-700 mb-2">Your Email Address</label>
                                <input type="email" id="match-uploader-email" name="uploader_email"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                       placeholder="Enter your email address (optional)">
                                <p class="text-xs text-gray-500 mt-1">Optional: Provide your email for uploaded images</p>
                            </div>

                            <!-- Status Field -->
                            <div>
                                <label for="match-status" class="block text-sm font-medium text-gray-700 mb-2">Item Status</label>
                                <select id="match-status" name="status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="lost">Lost Item</option>
                                    <option value="found">Found Item</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Select whether this is a lost or found item</p>
                            </div>
                        </div>

                        <!-- Uploaded Images Descriptions Container -->
                        <div id="uploaded-descriptions-container" class="hidden space-y-4 mt-6">
                            <h4 class="text-md font-semibold text-gray-700">Add Descriptions for Uploaded Images</h4>
                            <div id="uploaded-descriptions-fields" class="space-y-4"></div>
                        </div>

                        <!-- Uploaded Images Tags Container -->
                        <div id="uploaded-tags-container" class="hidden space-y-4 mt-6">
                            <h4 class="text-md font-semibold text-gray-700">Add Tags for Uploaded Images</h4>
                            <div id="uploaded-tags-fields" class="space-y-4"></div>
                        </div>

                        <div class="space-y-4 mt-6">
                            <div>
                                <label for="search-text" class="block text-sm font-medium text-gray-700 mb-2">Search Text (Optional)</label>
                                <input type="text" id="search-text" name="search_text" maxlength="1000"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Enter text to search in reference image descriptions and tags (e.g., 'sunset landscape')">
                                <p class="text-xs text-gray-500 mt-1">Search for reference images with similar descriptions or tags</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="space-y-2">
                                    <label for="threshold" class="block text-sm font-medium text-gray-700">Visual Threshold</label>
                                    <input type="number" id="threshold" name="threshold" min="0" max="1" step="0.1" value="0.7"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <p class="text-xs text-gray-500">Visual similarity threshold</p>
                                </div>
                                <div class="space-y-2">
                                    <label for="text-threshold" class="block text-sm font-medium text-gray-700">Text Threshold</label>
                                    <input type="number" id="text-threshold" name="text_threshold" min="0" max="1" step="0.1" value="0.5"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <p class="text-xs text-gray-500">Text similarity threshold</p>
                                </div>
                                <div class="space-y-2">
                                    <label for="text-weight" class="block text-sm font-medium text-gray-700">Text Weight</label>
                                    <input type="number" id="text-weight" name="text_weight" min="0" max="1" step="0.1" value="0.3"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <p class="text-xs text-gray-500">Weight of text in overall score</p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="limit" class="block text-sm font-medium text-gray-700">Max Results</label>
                                <input type="number" id="limit" name="limit" min="1" max="50" value="10"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500">Maximum number of matches to return</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center space-x-4">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                            Find Matching Images
                        </button>
                        <button type="button" onclick="clearMatchForm()" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                            Clear
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
                                <label class="block text-sm font-medium text-gray-700 mb-3">Select Reference Images</label>
                                <div id="reference-drop-zone" class="relative border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-all duration-200 hover:border-blue-400 hover:bg-blue-50 cursor-pointer">
                                    <input id="reference-images" name="images[]" type="file" class="hidden" accept="image/*" multiple>
                                    
                                    <div id="reference-drop-zone-content" class="space-y-4">
                                        <div class="flex justify-center">
                                            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-cloud-upload-alt text-blue-600 text-2xl"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-lg font-medium text-gray-700 mb-1">
                                                <span class="text-blue-600">Click to upload</span> or drag and drop
                                            </p>
                                            <p class="text-sm text-gray-500">PNG, JPG, GIF up to 10MB each (max 5 images)</p>
                                        </div>
                                        <button type="button" onclick="document.getElementById('reference-images').click()" 
                                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                            <i class="fas fa-folder-open mr-2"></i>
                                            Browse Files
                                        </button>
                                    </div>
                                    
                                    <div id="reference-preview" class="hidden grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mt-4"></div>
                                </div>

                                <!-- General Description and Tags -->
                                <div class="space-y-4 mt-6">
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <h4 class="text-md font-semibold text-blue-800 mb-3">📝 Add Description and Tags</h4>
                                        <p class="text-sm text-blue-700 mb-4">Add a general description and tags that will apply to all uploaded images, or leave blank to add individual descriptions for each image.</p>

                                        <div class="space-y-4">
                                            <div>
                                                <label for="general-description" class="block text-sm font-medium text-gray-700 mb-2">General Description (Optional)</label>
                                                <textarea id="general-description" name="general_description" rows="3"
                                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                          placeholder="Describe the items you're uploading (e.g., 'Lost items from Central Park on Monday')"></textarea>
                                                <p class="text-xs text-gray-500 mt-1">This description will be applied to all uploaded images</p>
                                            </div>

                                            <div>
                                                <label for="general-tags" class="block text-sm font-medium text-gray-700 mb-2">General Tags (Optional)</label>
                                                <input type="text" id="general-tags" name="general_tags"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                       placeholder="Enter tags separated by commas (e.g., lost, Central Park, Monday, personal items)">
                                                <p class="text-xs text-gray-500 mt-1">These tags will be applied to all uploaded images</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Email Field -->
                                    <div>
                                        <label for="reference-uploader-email" class="block text-sm font-medium text-gray-700 mb-2">Your Email Address</label>
                                        <input type="email" id="reference-uploader-email" name="uploader_email"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                               placeholder="Enter your email address (optional)">
                                        <p class="text-xs text-gray-500 mt-1">Optional: Provide your email for reference images</p>
                                    </div>

                                    <!-- Status Field -->
                                    <div>
                                        <label for="reference-status" class="block text-sm font-medium text-gray-700 mb-2">Item Status</label>
                                        <select id="reference-status" name="status"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="lost">Lost Item</option>
                                            <option value="found">Found Item</option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Select whether this is a lost or found item</p>
                                    </div>
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
                            <div class="flex items-center space-x-3">
                                <select id="status-filter" onchange="filterImagesByStatus()" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="all">All Items</option>
                                    <option value="lost">Lost Items</option>
                                    <option value="found">Found Items</option>
                                </select>
                                <button id="refresh-references" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                    Refresh
                                </button>
                            </div>
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

            files.forEach((file, index) => {
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

            // Show metadata fields if this is for reference images or match images
            if (preview.id === 'match-preview') {
                showUploadedDescriptionsFields(files);
                showUploadedTagsFields(files);
            }
        }


        function showUploadedDescriptionsFields(files) {
            const container = document.getElementById('uploaded-descriptions-container');
            const fieldsContainer = document.getElementById('uploaded-descriptions-fields');

            container.classList.remove('hidden');
            fieldsContainer.innerHTML = '';

            files.forEach((file, index) => {
                const fieldGroup = document.createElement('div');
                fieldGroup.className = 'border rounded-lg p-4 bg-blue-50';
                fieldGroup.innerHTML = `
                    <div class="flex items-center space-x-2 mb-3">
                        <img src="${URL.createObjectURL(file)}" alt="${file.name}" class="w-12 h-12 object-cover rounded">
                        <div>
                            <p class="text-sm font-medium text-gray-800 truncate" title="${file.name}">${file.name}</p>
                            <p class="text-xs text-gray-500">${formatBytes(file.size)}</p>
                        </div>
                    </div>
                    <div>
                        <label for="uploaded-description-${index}" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="uploaded-description-${index}" name="uploaded_descriptions[${index}]" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                  placeholder="Describe what you're looking for (e.g., 'sunset over mountains')..."></textarea>
                    </div>
                `;
                fieldsContainer.appendChild(fieldGroup);
            });
        }

        function showUploadedTagsFields(files) {
            const container = document.getElementById('uploaded-tags-container');
            const fieldsContainer = document.getElementById('uploaded-tags-fields');

            container.classList.remove('hidden');
            fieldsContainer.innerHTML = '';

            files.forEach((file, index) => {
                const fieldGroup = document.createElement('div');
                fieldGroup.className = 'border rounded-lg p-4 bg-green-50';
                fieldGroup.innerHTML = `
                    <div class="flex items-center space-x-2 mb-3">
                        <img src="${URL.createObjectURL(file)}" alt="${file.name}" class="w-12 h-12 object-cover rounded">
                        <div>
                            <p class="text-sm font-medium text-gray-800 truncate" title="${file.name}">${file.name}</p>
                            <p class="text-xs text-gray-500">${formatBytes(file.size)}</p>
                        </div>
                    </div>
                    <div>
                        <label for="uploaded-tags-${index}" class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                        <input type="text" id="uploaded-tags-${index}" name="uploaded_tags[${index}]"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                               placeholder="Enter tags for what you're looking for (e.g., sunset, mountains, nature)">
                        <p class="text-xs text-gray-500 mt-1">Separate multiple tags with commas</p>
                    </div>
                `;
                fieldsContainer.appendChild(fieldGroup);
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

            // Add text search parameters
            const searchText = document.getElementById('search-text').value;
            const textThreshold = document.getElementById('text-threshold').value;
            const textWeight = document.getElementById('text-weight').value;

            if (searchText.trim()) {
                formData.append('search_text', searchText.trim());
                formData.append('text_threshold', textThreshold);
                formData.append('text_weight', textWeight);
            }

            // Add email address
            const uploaderEmail = document.getElementById('match-uploader-email').value;
            if (uploaderEmail.trim()) {
                formData.append('uploader_email', uploaderEmail.trim());
            }

            // Add uploaded image metadata
            const uploadedDescriptions = document.querySelectorAll('[name^="uploaded_descriptions"]');
            const uploadedTags = document.querySelectorAll('[name^="uploaded_tags"]');

            uploadedDescriptions.forEach((field, index) => {
                if (field.value.trim()) {
                    formData.append(`uploaded_descriptions[${index}]`, field.value.trim());
                }
            });

            uploadedTags.forEach((field, index) => {
                if (field.value.trim()) {
                    formData.append(`uploaded_tags[${index}]`, field.value.trim());
                }
            });

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

            // Add general description and tags if provided
            const generalDescription = document.getElementById('general-description').value;
            const generalTags = document.getElementById('general-tags').value;

            if (generalDescription.trim()) {
                formData.append('general_description', generalDescription.trim());
            }
            if (generalTags.trim()) {
                formData.append('general_tags', generalTags.trim());
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
                            <span class="text-gray-600">Visual Threshold:</span>
                            <span class="text-lg font-semibold text-blue-600">${(data.visual_threshold_used * 100).toFixed(1)}%</span>
                        </div>
                        ${data.search_text ? `
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Search Text:</span>
                            <span class="text-lg font-semibold text-purple-600">"${data.search_text}"</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Text Threshold:</span>
                            <span class="text-lg font-semibold text-orange-600">${(data.text_threshold_used * 100).toFixed(1)}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Text Weight:</span>
                            <span class="text-lg font-semibold text-indigo-600">${(data.text_weight_used * 100).toFixed(1)}%</span>
                        </div>
                        ` : ''}
                        ${data.uploaded_images_count ? `
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Images Searched:</span>
                            <span class="text-lg font-semibold text-purple-600">${data.uploaded_images_count}</span>
                        </div>
                        ` : ''}
                        <div id="match-message" class="text-sm text-gray-600">${data.total_matches > 0 ?
                            `Found ${data.total_matches} matching image(s) with ${data.uploaded_images_count || 1} uploaded image(s)` :
                            'No matching images found with the given thresholds'}</div>
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

                                            <!-- Similarity Scores -->
                                            <div class="space-y-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs text-gray-600">Visual:</span>
                                                    <span class="text-xs font-bold text-blue-600">${match.visual_similarity_percentage}%</span>
                                                </div>
                                                ${match.overall_similarity_percentage !== undefined ? `
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs text-gray-600">Overall:</span>
                                                    <span class="text-xs font-bold text-green-600">${match.overall_similarity_percentage}%</span>
                                                </div>
                                                ` : ''}
                                            </div>

                                            <!-- Uploaded Image Metadata -->
                                            ${match.uploaded_metadata && (match.uploaded_metadata.description || match.uploaded_metadata.tags.length > 0) ? `
                                                <div class="mt-3 pt-2 border-t border-gray-200">
                                                    <p class="text-xs font-medium text-gray-700 mb-2">Your Uploaded Image:</p>
                                                    ${match.uploaded_metadata.description ? `
                                                        <div class="mb-2">
                                                            <p class="text-xs text-gray-600 bg-purple-50 p-2 rounded border-l-2 border-purple-200">
                                                                <span class="font-medium">Description:</span> ${match.uploaded_metadata.description}
                                                                ${match.uploaded_metadata.uploaded_description_similarity ? `<br><span class="text-xs text-purple-600">Match: ${match.uploaded_metadata.uploaded_description_similarity}%</span>` : ''}
                                                            </p>
                                                        </div>
                                                    ` : ''}
                                                    ${match.uploaded_metadata.tags && match.uploaded_metadata.tags.length > 0 ? `
                                                        <div class="mb-2">
                                                            <p class="text-xs text-gray-600 mb-1"><span class="font-medium">Tags:</span></p>
                                                            <div class="flex flex-wrap gap-1">
                                                                ${match.uploaded_metadata.tags.map(tag => `
                                                                    <span class="inline-block bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">${tag}</span>
                                                                `).join('')}
                                                            </div>
                                                            ${match.uploaded_metadata.uploaded_tags_similarity ? `<p class="text-xs text-purple-600 mt-1">Match: ${match.uploaded_metadata.uploaded_tags_similarity}%</p>` : ''}
                                                        </div>
                                                    ` : ''}
                                                </div>
                                            ` : ''}

                                            <!-- Reference Image Metadata -->
                                            ${match.reference_metadata ? `
                                                <div class="mt-3 pt-2 border-t border-gray-200">
                                                    <p class="text-xs font-medium text-gray-700 mb-2">Reference Image:</p>
                                                    ${match.reference_metadata.description ? `
                                                        <div class="mb-2">
                                                            <p class="text-xs text-gray-600 bg-blue-50 p-2 rounded border-l-2 border-blue-200">
                                                                <span class="font-medium">Description:</span> ${match.reference_metadata.description}
                                                                ${match.reference_metadata.description_similarity ? `<br><span class="text-xs text-blue-600">Text match: ${match.reference_metadata.description_similarity}%</span>` : ''}
                                                            </p>
                                                        </div>
                                                    ` : ''}
                                                    ${match.reference_metadata.tags && match.reference_metadata.tags.length > 0 ? `
                                                        <div class="mb-2">
                                                            <p class="text-xs text-gray-600 mb-1"><span class="font-medium">Tags:</span></p>
                                                            <div class="flex flex-wrap gap-1">
                                                                ${match.reference_metadata.tags.map(tag => `
                                                                    <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">${tag}</span>
                                                                `).join('')}
                                                            </div>
                                                            ${match.reference_metadata.tags_similarity ? `<p class="text-xs text-green-600 mt-1">Tag match: ${match.reference_metadata.tags_similarity}%</p>` : ''}
                                                        </div>
                                                    ` : ''}
                                                </div>
                                            ` : ''}
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

                        ${data.similarity_summary && data.similarity_summary.total_similar_images_found > 0 ? `
                            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <h4 class="text-sm font-medium text-blue-800 mb-2">🔍 Similarity Check Results</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-blue-700">Similar Images Found:</span>
                                        <span class="text-lg font-bold text-blue-600">${data.similarity_summary.total_similar_images_found}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-blue-700">Notifications Sent:</span>
                                        <span class="text-lg font-bold text-green-600">${data.similarity_summary.total_notifications_sent}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-blue-700">Users Notified:</span>
                                        <span class="text-lg font-bold text-purple-600">${data.similarity_summary.unique_emails_notified}</span>
                                    </div>
                                    ${data.similarity_summary.emails_notified && data.similarity_summary.emails_notified.length > 0 ? `
                                        <div class="mt-2">
                                            <p class="text-xs text-blue-600 mb-1">Emails notified:</p>
                                            <div class="flex flex-wrap gap-1">
                                                ${data.similarity_summary.emails_notified.map(email => `
                                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">${email}</span>
                                                `).join('')}
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        ` : ''}

                        ${data.uploaded_images && data.uploaded_images.length > 0 ? `
                            <div class="mt-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Uploaded Files:</h4>
                                <div class="space-y-3">
                                    ${data.uploaded_images.map(img => `
                                        <div class="bg-gray-50 p-3 rounded-lg border">
                                            <div class="flex justify-between items-start mb-2">
                                                <span class="text-sm font-medium text-gray-800">${img.original_name}</span>
                                                <span class="text-xs text-gray-500">${formatBytes(img.size)}</span>
                                            </div>
                                            ${img.description ? `
                                                <div class="mb-2">
                                                    <p class="text-xs text-gray-600 bg-blue-50 p-2 rounded border-l-2 border-blue-200">
                                                        <span class="font-medium">Description:</span> ${img.description}
                                                    </p>
                                                </div>
                                            ` : ''}
                                            ${img.tags && img.tags.length > 0 ? `
                                                <div>
                                                    <p class="text-xs text-gray-600 mb-1"><span class="font-medium">Tags:</span></p>
                                                    <div class="flex flex-wrap gap-1">
                                                        ${img.tags.map(tag => `
                                                            <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">${tag}</span>
                                                        `).join('')}
                                                    </div>
                                                </div>
                                            ` : ''}
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;

            // Clear the form after successful upload
            clearReferenceUploadForm();
        }

        // Global variable to store all images for filtering
        let allReferenceImages = [];

        function filterImagesByStatus() {
            const statusFilter = document.getElementById('status-filter').value;
            const listContainer = document.getElementById('reference-images-list');

            if (statusFilter === 'all') {
                displayImages(allReferenceImages);
            } else {
                const filteredImages = allReferenceImages.filter(image => image.status === statusFilter);
                displayImages(filteredImages);
            }
        }

        function displayImages(images) {
            const listContainer = document.getElementById('reference-images-list');

            if (images.length === 0) {
                listContainer.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-lg font-medium text-gray-900">No images found</p>
                        <p class="text-sm text-gray-500">No images match the current filter.</p>
                    </div>
                `;
            } else {
                // Group images by upload_id
                const groupedImages = {};
                images.forEach(image => {
                    const uploadId = image.upload_id || 'single';
                    if (!groupedImages[uploadId]) {
                        groupedImages[uploadId] = [];
                    }
                    groupedImages[uploadId].push(image);
                });

                const totalImages = images.length;
                const totalUploads = Object.keys(groupedImages).length;

                listContainer.innerHTML = `
                    <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-blue-800">
                                <strong>${totalImages}</strong> reference images in <strong>${totalUploads}</strong> upload${totalUploads > 1 ? 's' : ''}
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
                    <div class="space-y-6">
                        ${Object.entries(groupedImages).map(([uploadId, uploadImages]) => `
                            <div class="border-2 border-blue-200 rounded-lg p-6 bg-white shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" class="batch-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" value="${uploadId}" onchange="updateBulkDeleteButton()">
                                        <div>
                                            <h4 class="text-lg font-semibold text-blue-900">📦 Batch Upload</h4>
                                            <p class="text-sm text-blue-700">${uploadImages.length} image${uploadImages.length > 1 ? 's' : ''} • Uploaded: ${new Date(uploadImages[0].created_at).toLocaleString()}</p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button onclick="editBatchUpload('${uploadId}')" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded transition-colors">
                                            Edit Batch
                                        </button>
                                        <button onclick="deleteBatchUpload('${uploadId}')" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded transition-colors">
                                            Delete Batch
                                        </button>
                                    </div>
                                </div>

                                ${uploadImages[0].description ? `
                                    <div class="mb-4 p-3 bg-blue-50 rounded-lg border-l-4 border-blue-400">
                                        <p class="text-sm text-blue-800"><span class="font-medium">Description:</span> ${uploadImages[0].description}</p>
                                    </div>
                                ` : ''}

                                ${uploadImages[0].tags && uploadImages[0].tags.length > 0 ? `
                                    <div class="mb-4 p-3 bg-green-50 rounded-lg border-l-4 border-green-400">
                                        <p class="text-sm text-green-800 mb-2"><span class="font-medium">Tags:</span></p>
                                        <div class="flex flex-wrap gap-2">
                                            ${Array.isArray(uploadImages[0].tags) ? uploadImages[0].tags.map(tag => `
                                                <span class="inline-block bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full">${tag}</span>
                                            `).join('') : ''}
                                        </div>
                                    </div>
                                ` : ''}

                                ${uploadImages[0].uploader_email ? `
                                    <div class="mb-4 p-3 bg-purple-50 rounded-lg border-l-4 border-purple-400">
                                        <p class="text-sm text-purple-800"><span class="font-medium">Uploaded by:</span> ${uploadImages[0].uploader_email}</p>
                                    </div>
                                ` : ''}

                                <div class="mb-4 p-3 bg-${uploadImages[0].status === 'found' ? 'green' : 'red'}-50 rounded-lg border-l-4 border-${uploadImages[0].status === 'found' ? 'green' : 'red'}-400">
                                    <p class="text-sm text-${uploadImages[0].status === 'found' ? 'green' : 'red'}-800">
                                        <span class="font-medium">Status:</span>
                                        <span class="inline-block px-3 py-1 text-sm font-medium rounded-full ${uploadImages[0].status === 'found' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'} ml-2">
                                            ${uploadImages[0].status === 'found' ? 'Found Item' : 'Lost Item'}
                                        </span>
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    ${uploadImages.map(image => `
                                        <div class="relative group">
                                            <div class="aspect-w-16 aspect-h-9">
                                                <img src="/${image.path}" alt="${image.original_name}" class="w-full h-32 object-cover rounded-lg shadow-sm">
                                            </div>
                                            <div class="mt-2">
                                                <p class="text-xs font-medium text-gray-800 truncate" title="${image.original_name}">${image.original_name}</p>
                                                <p class="text-xs text-gray-500">${formatBytes(image.size)}</p>
                                            </div>
                                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button onclick="deleteReferenceImage('${image.filename}')" class="bg-red-600 hover:bg-red-700 text-white text-xs font-medium py-1 px-2 rounded transition-colors">
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            }
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
                    // Store all images globally for filtering
                    allReferenceImages = data.data.images;

                    // Display images based on current filter
                    const statusFilter = document.getElementById('status-filter').value;
                    if (statusFilter === 'all') {
                        displayImages(allReferenceImages);
                    } else {
                        const filteredImages = allReferenceImages.filter(image => image.status === statusFilter);
                        displayImages(filteredImages);
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

        // Batch upload functions
        window.editBatchUpload = function(uploadId) {
            // TODO: Implement batch edit functionality
            alert('Batch edit functionality will be implemented soon!');
        };

        window.deleteBatchUpload = function(uploadId) {
            if (!confirm('Are you sure you want to delete this entire batch upload?')) {
                return;
            }

            // Get all images with this upload_id
            const batchImages = allReferenceImages.filter(img => img.upload_id === uploadId);
            const filenames = batchImages.map(img => img.filename);

            // Delete all images in the batch
            deleteMultipleImages(filenames);
        };

        async function deleteMultipleImages(filenames) {
            try {
                const response = await fetch('/api/v1/reference-images/bulk', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ filenames: filenames })
                });

                const data = await response.json();

                if (data.success) {
                    loadReferenceImages(); // Refresh the list
                } else {
                    alert('Error deleting images: ' + (data.error || data.message));
                }
            } catch (error) {
                console.error('Error deleting images:', error);
                alert('Error deleting images. Please try again.');
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

            // Clear email field
            document.getElementById('reference-uploader-email').value = '';


            // Reset the drop zone
            const dropZone = document.getElementById('reference-drop-zone');
            dropZone.classList.remove('has-image');
        }

        // Make clearMatchForm globally accessible
        window.clearMatchForm = function() {
            // Clear the file input
            const fileInput = document.getElementById('match-images');
            fileInput.value = '';

            // Clear the preview
            const preview = document.getElementById('match-preview');
            preview.innerHTML = '';
            preview.classList.add('hidden');

            // Clear uploaded descriptions fields
            const uploadedDescriptionsContainer = document.getElementById('uploaded-descriptions-container');
            uploadedDescriptionsContainer.classList.add('hidden');
            document.getElementById('uploaded-descriptions-fields').innerHTML = '';

            // Clear uploaded tags fields
            const uploadedTagsContainer = document.getElementById('uploaded-tags-container');
            uploadedTagsContainer.classList.add('hidden');
            document.getElementById('uploaded-tags-fields').innerHTML = '';

            // Clear email field
            document.getElementById('match-uploader-email').value = '';

            // Clear search text
            document.getElementById('search-text').value = '';

            // Reset thresholds to defaults
            document.getElementById('threshold').value = '0.7';
            document.getElementById('text-threshold').value = '0.5';
            document.getElementById('text-weight').value = '0.3';
            document.getElementById('limit').value = '10';

            // Reset the drop zone
            const dropZone = document.getElementById('match-drop-zone');
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

        // Edit reference image functionality
        function editReferenceImageFromButton(button, metadataId, originalName, description, tags, uploaderEmail, status) {
            console.log('Edit button clicked for image:');
            console.log('Metadata ID:', metadataId);
            console.log('Original Name:', originalName);
            console.log('Description:', description);
            console.log('Tags:', tags);
            console.log('Uploader Email:', uploaderEmail);
            console.log('Status:', status);

            // Convert tags string back to array if needed
            const tagsArray = tags ? tags.split(', ').filter(tag => tag.trim()) : [];

            editReferenceImage(metadataId, originalName, description, tagsArray, uploaderEmail, status);
        }

        function editReferenceImage(metadataId, originalName, description, tags, uploaderEmail, status) {
            console.log('Opening edit modal for metadata ID:', metadataId);
            console.log('Original name:', originalName);
            console.log('Description:', description);
            console.log('Tags:', tags);
            console.log('Uploader email:', uploaderEmail);
            console.log('Status:', status);

            // Populate the edit form
            document.getElementById('edit-metadata-id').value = metadataId;
            document.getElementById('edit-original-name').textContent = originalName;
            document.getElementById('edit-description').value = description;
            document.getElementById('edit-tags').value = Array.isArray(tags) ? tags.join(', ') : tags;
            document.getElementById('edit-uploader-email').value = uploaderEmail;
            document.getElementById('edit-status').value = status || 'lost';

            // Clear the image file input
            document.getElementById('edit-image-file').value = '';
            document.getElementById('edit-image-preview').innerHTML = '';

            // Show the modal
            document.getElementById('edit-image-modal').classList.remove('hidden');

            console.log('Edit modal opened successfully');
        }

        function closeEditModal() {
            document.getElementById('edit-image-modal').classList.add('hidden');
        }

        function previewEditImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('edit-image-preview').innerHTML = `
                        <img src="${e.target.result}" alt="Preview" class="w-full h-32 object-cover rounded">
                        <p class="text-xs text-gray-500 mt-1">New image preview</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        }

        async function updateReferenceImage() {
            const metadataId = document.getElementById('edit-metadata-id').value;

            if (!metadataId || metadataId === 'null' || metadataId === '') {
                console.error('No metadata ID found! Cannot update image.');
                showError('Error: No metadata ID found. Cannot update this image.');
                return;
            }

            const formData = new FormData();

            console.log('Starting update for metadata ID:', metadataId);

            // Add image file if selected
            const imageFile = document.getElementById('edit-image-file').files[0];
            if (imageFile) {
                formData.append('image', imageFile);
                console.log('Image file added:', imageFile.name);
            }

            // Add metadata fields
            const description = document.getElementById('edit-description').value.trim();
            if (description) {
                formData.append('description', description);
                console.log('Description added:', description);
            }

            const tags = document.getElementById('edit-tags').value.trim();
            if (tags) {
                formData.append('tags', tags);
                console.log('Tags added:', tags);
            }

            const uploaderEmail = document.getElementById('edit-uploader-email').value.trim();
            formData.append('uploader_email', uploaderEmail);
            console.log('Uploader email added:', uploaderEmail);

            const status = document.getElementById('edit-status').value;
            formData.append('status', status);
            console.log('Status added:', status);

            // Add method spoofing for PUT request
            formData.append('_method', 'PUT');

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const url = `/api/v1/reference-images/${metadataId}`;

            console.log('Request URL:', url);
            console.log('CSRF Token:', csrfToken);
            console.log('FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(key, ':', value);
            }

            try {
                const response = await fetch(url, {
                    method: 'POST', // Use POST with method spoofing
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                const data = await response.json();
                console.log('Response data:', data);

                if (data.success) {
                    showSuccess('Image updated successfully!');
                    closeEditModal();
                    loadReferenceImages(); // Refresh the list
                } else {
                    showError(data.error || data.message || 'Failed to update image');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred while updating the image: ' + error.message);
            }
        }

        function showSuccess(message) {
            const resultsContainer = document.getElementById('results');
            resultsContainer.innerHTML = `
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">${message}</p>
                        </div>
                    </div>
                </div>
            `;
            resultsContainer.classList.remove('hidden');
        }
    </script>

    <!-- Edit Image Modal -->
    <div id="edit-image-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Edit Reference Image</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <input type="hidden" id="edit-metadata-id" value="">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Original Name</label>
                        <p id="edit-original-name" class="text-sm text-gray-600 bg-gray-50 p-2 rounded"></p>
                    </div>

                    <div>
                        <label for="edit-image-file" class="block text-sm font-medium text-gray-700 mb-1">Replace Image (Optional)</label>
                        <input type="file" id="edit-image-file" accept="image/*" onchange="previewEditImage(event)"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div id="edit-image-preview" class="mt-2"></div>
                    </div>

                    <div>
                        <label for="edit-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="edit-description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Enter image description"></textarea>
                    </div>

                    <div>
                        <label for="edit-tags" class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                        <input type="text" id="edit-tags"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter tags separated by commas">
                    </div>

                    <div>
                        <label for="edit-uploader-email" class="block text-sm font-medium text-gray-700 mb-1">Uploader Email</label>
                        <input type="email" id="edit-uploader-email"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter uploader email">
                    </div>

                    <div>
                        <label for="edit-status" class="block text-sm font-medium text-gray-700 mb-1">Item Status</label>
                        <select id="edit-status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="lost">Lost Item</option>
                            <option value="found">Found Item</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button onclick="closeEditModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button onclick="updateReferenceImage()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Update Image
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
