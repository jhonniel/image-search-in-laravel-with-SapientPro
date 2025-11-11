<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a {{ $itemType === 'found' ? 'Found' : 'Lost' }} Item - FindITFast</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --purple-primary:#8B5CF6; --pink-primary:#EC4899; }
        .text-purple-primary{ color: var(--purple-primary); }
        .text-pink-primary{ color: var(--pink-primary); }
        .bg-pink-primary{ background-color: var(--pink-primary); }
    </style>
    @php
        $illustration = asset('images/register.png');
    @endphp
</head>
<body class="min-h-screen bg-white">
    <!-- Top Logo -->
    <div class="px-4 sm:px-6 pt-6 sm:pt-8">
        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight">
            <span class="text-purple-primary">FindIT</span><span class="text-pink-primary">Fast</span>
        </h1>
    </div>

    <div class="container mx-auto px-4 sm:px-6 py-6 sm:py-8 md:py-12 min-h-[80vh] flex items-center">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 sm:gap-14 md:gap-20 items-start mx-auto w-full max-w-7xl">
            <!-- Illustration -->
            <div class="hidden md:block">
                <img src="{{ $illustration }}" alt="Illustration" class="w-full h-auto object-contain scale-105">
            </div>

            <!-- Card -->
            <div class="bg-[#F5F4FE] rounded-2xl sm:rounded-3xl shadow-xl p-6 sm:p-8 md:p-10 lg:p-14">
                <div class="mb-6 sm:mb-8">
                    <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-[#213A8F] mb-2">{{ $itemType === 'found' ? 'Found' : 'Lost' }} Item</h2>
                    @auth
                        <p class="text-gray-600 text-base sm:text-lg">Fill in the details. Your item will be saved to your account.</p>
                    @else
                        <p class="text-gray-600 text-base sm:text-lg">Fill in the details. You'll create an account next to publish it.</p>
                    @endauth
                </div>

                <form class="space-y-6" method="POST" action="{{ route('guest.post.submit') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="item_type" value="{{ $itemType }}">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <input type="text" name="location" required class="w-full px-3 sm:px-4 py-3 sm:py-4 text-base sm:text-lg bg-white border border-gray-300 rounded-lg sm:rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400" placeholder="Where was it lost/found?" value="{{ old('location') }}">
                        @error('location')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4" required class="w-full px-3 sm:px-4 py-3 sm:py-4 text-base sm:text-lg bg-white border border-gray-300 rounded-lg sm:rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400" placeholder="Describe the item and key details">{{ old('description', !empty($searchQuery) ? 'Looking for: ' . $searchQuery : '') }}</textarea>
                        @error('description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        @if(!empty($searchQuery))
                            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Pre-filled based on your search. Feel free to edit and add more details.
                            </p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tags (comma separated)</label>
                        <input type="text" name="tags" class="w-full px-3 sm:px-4 py-3 sm:py-4 text-base sm:text-lg bg-white border border-gray-300 rounded-lg sm:rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400" placeholder="wallet, black, leather" value="{{ old('tags', !empty($searchQuery) ? $searchQuery : '') }}">
                        @error('tags')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Images <span class="text-red-500">*</span></label>
                        
                        <!-- Drag and Drop Zone -->
                        <div id="drop-zone" class="relative border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-all duration-200 hover:border-pink-400 hover:bg-pink-50 cursor-pointer">
                            <input type="file" id="item-images" name="images[]" multiple accept="image/*" class="hidden" required>
                            
                            <div id="drop-zone-content" class="space-y-4">
                                <div class="flex justify-center">
                                    <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-cloud-upload-alt text-pink-600 text-2xl"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-lg font-medium text-gray-700 mb-1">
                                        <span class="text-pink-600">Click to upload</span> or drag and drop
                                    </p>
                                    <p class="text-sm text-gray-500">PNG, JPG, GIF up to 10MB each (Max 5 images)</p>
                                </div>
                                <button type="button" onclick="document.getElementById('item-images').click()" 
                                        class="inline-flex items-center px-4 py-2 bg-pink-primary text-white rounded-lg hover:bg-pink-600 transition-colors text-sm font-medium">
                                    <i class="fas fa-folder-open mr-2"></i>
                                    Browse Files
                                </button>
                            </div>
                        </div>

                        <!-- Image Previews -->
                        <div id="image-preview-container" class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 hidden">
                            <!-- Image previews will be inserted here -->
                        </div>

                        <!-- Upload Progress -->
                        <div id="upload-progress-container" class="mt-4 hidden">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Uploading images...</span>
                                <span id="upload-progress-text" class="text-sm text-gray-500">0%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div id="upload-progress-bar" class="bg-pink-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>

                        @error('images')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        @error('images.*')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="w-full bg-pink-primary text-white font-semibold py-3 sm:py-4 rounded-lg sm:rounded-xl hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-pink-300 text-base sm:text-lg">Continue</button>
                </form>
            </div>
        </div>
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
// Image upload state
let selectedFiles = [];

// Initialize drag and drop
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('item-images');
    const previewContainer = document.getElementById('image-preview-container');

    if (!dropZone || !fileInput) return;

    // Click to upload
    dropZone.addEventListener('click', function(e) {
        if (e.target.closest('button')) return;
        fileInput.click();
    });

    // File input change
    fileInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

    // Drag and drop events
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-pink-500', 'bg-pink-100');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-pink-500', 'bg-pink-100');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-pink-500', 'bg-pink-100');
        
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    // Handle selected files
    function handleFiles(files) {
        const maxFiles = 5;
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        // Filter valid image files
        const validFiles = Array.from(files).filter(file => {
            if (!file.type.startsWith('image/')) {
                alert('Only image files are allowed');
                return false;
            }
            if (file.size > maxSize) {
                alert(`File ${file.name} is too large. Maximum size is 10MB`);
                return false;
            }
            return true;
        });

        // Check total file count
        const totalFiles = selectedFiles.length + validFiles.length;
        if (totalFiles > maxFiles) {
            alert(`Maximum ${maxFiles} images allowed. You selected ${totalFiles} files.`);
            validFiles.splice(maxFiles - selectedFiles.length);
        }

        // Add to selected files
        validFiles.forEach(file => {
            if (!selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
                selectedFiles.push(file);
            }
        });

        // Update file input
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;

        // Show previews
        displayImagePreviews();
    }

    // Display image previews
    function displayImagePreviews() {
        if (selectedFiles.length === 0) {
            previewContainer.classList.add('hidden');
            return;
        }

        previewContainer.classList.remove('hidden');
        previewContainer.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'relative group';
                previewDiv.innerHTML = `
                    <div class="relative aspect-square rounded-lg overflow-hidden border-2 border-gray-200 hover:border-pink-400 transition-colors">
                        <img src="${e.target.result}" alt="${file.name}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all flex items-center justify-center">
                            <button onclick="removeImage(${index})" class="opacity-0 group-hover:opacity-100 bg-red-500 text-white px-3 py-1 rounded-lg text-sm font-medium transition-opacity hover:bg-red-600">
                                <i class="fas fa-trash mr-1"></i>Remove
                            </button>
                        </div>
                        <div class="absolute top-2 right-2 bg-white rounded-full p-1 shadow-md">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-600 truncate" title="${file.name}">${file.name}</p>
                    <p class="text-xs text-gray-500">${formatFileSize(file.size)}</p>
                `;
                previewContainer.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        });
    }

    // Make removeImage function global
    window.removeImage = function(index) {
        selectedFiles.splice(index, 1);
        
        // Update file input
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;

        displayImagePreviews();
    };

    // Format file size
    window.formatFileSize = function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    };
});
</script>
</body>
</html>


