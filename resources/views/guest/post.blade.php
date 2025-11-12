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

                    @if($enableProvinceField ?? true)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Province 
                            @if($provinceFieldRequired ?? true)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   id="province-input" 
                                   name="province" 
                                   @if($provinceFieldRequired ?? true) required @endif
                                   autocomplete="off"
                                   value="{{ old('province') }}"
                                   class="w-full px-3 sm:px-4 py-3 sm:py-4 text-base sm:text-lg bg-white border border-gray-300 rounded-lg sm:rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400"
                                   placeholder="Enter your province name">
                            <!-- Autocomplete dropdown -->
                            <div id="province-autocomplete" class="hidden absolute z-50 w-full mt-1 bg-white border-2 border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                                <!-- Suggestions will be inserted here -->
                            </div>
                        </div>
                        <div id="province-error-message" class="hidden mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg" style="display: none;">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-info-circle mr-1"></i>
                                We're trying to expand our services to cover more locations. Please contact us if you'd like to see your province added.
                            </p>
                        </div>
                        @error('province')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    @endif
                    @if($enableCityField ?? true)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            City 
                            @if($cityFieldRequired ?? true)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   id="city-input" 
                                   name="city" 
                                   @if($cityFieldRequired ?? true) required @endif
                                   autocomplete="off"
                                   value="{{ old('city') }}"
                                   class="w-full px-3 sm:px-4 py-3 sm:py-4 text-base sm:text-lg bg-white border border-gray-300 rounded-lg sm:rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400"
                                   placeholder="Enter your city name">
                            <!-- Autocomplete dropdown -->
                            <div id="city-autocomplete" class="hidden absolute z-50 w-full mt-1 bg-white border-2 border-gray-300 rounded-lg shadow-xl max-h-60 overflow-y-auto">
                                <!-- Suggestions will be inserted here -->
                            </div>
                        </div>
                        <div id="city-error-message" class="hidden mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg" style="display: none;">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-info-circle mr-1"></i>
                                We're trying to expand our services to cover more locations. Please contact us if you'd like to see your city added.
                            </p>
                        </div>
                        @error('city')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <input type="text" name="location" required class="w-full px-3 sm:px-4 py-3 sm:py-4 text-base sm:text-lg bg-white border border-gray-300 rounded-lg sm:rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400" placeholder="Where was it lost/found? (e.g., Street name, Building, etc.)" value="{{ old('location') }}">
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

    // Province autocomplete functionality
    const enabledProvinces = @json($enabledProvinces ?? []);
    const provinceInput = document.getElementById('province-input');
    const provinceAutocomplete = document.getElementById('province-autocomplete');
    const provinceErrorMessage = document.getElementById('province-error-message');

    if (provinceInput && enabledProvinces.length > 0) {
        provinceInput.addEventListener('input', function(e) {
            const query = e.target.value.trim().toLowerCase();
            
            // Hide error message initially
            if (provinceErrorMessage) {
                provinceErrorMessage.classList.add('hidden');
                provinceErrorMessage.style.display = 'none';
            }

            if (query.length === 0) {
                if (provinceAutocomplete) {
                    provinceAutocomplete.classList.add('hidden');
                }
                return;
            }

            // Filter provinces that match the query
            const matches = enabledProvinces.filter(province => 
                province.toLowerCase().includes(query)
            ).slice(0, 10); // Limit to 10 suggestions

            if (matches.length > 0) {
                // Show dropdown with matches
                if (provinceAutocomplete) {
                    provinceAutocomplete.innerHTML = '';
                    matches.forEach((province, index) => {
                        const div = document.createElement('div');
                        div.className = 'px-4 py-3 hover:bg-pink-50 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors province-suggestion';
                        div.setAttribute('data-index', index);
                        
                        // Highlight matching text
                        const provinceLower = province.toLowerCase();
                        const queryIndex = provinceLower.indexOf(query);
                        if (queryIndex !== -1) {
                            const beforeMatch = province.substring(0, queryIndex);
                            const match = province.substring(queryIndex, queryIndex + query.length);
                            const afterMatch = province.substring(queryIndex + query.length);
                            div.innerHTML = `${beforeMatch}<strong class="text-pink-600">${match}</strong>${afterMatch}`;
                        } else {
                            div.textContent = province;
                        }
                        
                        div.addEventListener('click', function() {
                            provinceInput.value = province;
                            if (provinceAutocomplete) {
                                provinceAutocomplete.classList.add('hidden');
                            }
                            if (provinceErrorMessage) {
                                provinceErrorMessage.classList.add('hidden');
                                provinceErrorMessage.style.display = 'none';
                            }
                        });
                        
                        div.addEventListener('mouseenter', function() {
                            div.classList.add('bg-pink-50');
                        });
                        
                        div.addEventListener('mouseleave', function() {
                            div.classList.remove('bg-pink-50');
                        });
                        
                        provinceAutocomplete.appendChild(div);
                    });
                    provinceAutocomplete.classList.remove('hidden');
                }
                // Hide error message when matches are found
                if (provinceErrorMessage) {
                    provinceErrorMessage.classList.add('hidden');
                    provinceErrorMessage.style.display = 'none';
                }
            } else {
                // No matches found - hide dropdown and show error message
                if (provinceAutocomplete) {
                    provinceAutocomplete.classList.add('hidden');
                }
                if (query.length >= 2 && provinceErrorMessage) {
                    provinceErrorMessage.classList.remove('hidden');
                    provinceErrorMessage.style.display = 'block';
                } else if (provinceErrorMessage) {
                    provinceErrorMessage.classList.add('hidden');
                    provinceErrorMessage.style.display = 'none';
                }
            }
        });

        // Hide autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            if (provinceInput && provinceAutocomplete && 
                !provinceInput.contains(e.target) && !provinceAutocomplete.contains(e.target)) {
                provinceAutocomplete.classList.add('hidden');
            }
        });

        // Validate on form submit
        const form = provinceInput.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const inputValue = provinceInput.value.trim();
                const isValidProvince = enabledProvinces.some(province => 
                    province.toLowerCase() === inputValue.toLowerCase()
                );
                
                if (!isValidProvince) {
                    e.preventDefault();
                    if (provinceErrorMessage) {
                        provinceErrorMessage.classList.remove('hidden');
                        provinceErrorMessage.style.display = 'block';
                    }
                    provinceInput.focus();
                    return false;
                }
            });
        }
    }

    // City autocomplete functionality
    const enabledCities = @json($enabledCities ?? []);
    const cityInput = document.getElementById('city-input');
    const cityAutocomplete = document.getElementById('city-autocomplete');
    const cityErrorMessage = document.getElementById('city-error-message');

    if (cityInput && enabledCities.length > 0) {
        cityInput.addEventListener('input', function(e) {
            const query = e.target.value.trim().toLowerCase();
            
            // Hide error message initially
            if (cityErrorMessage) {
                cityErrorMessage.classList.add('hidden');
            }

            if (query.length === 0) {
                if (cityAutocomplete) {
                    cityAutocomplete.classList.add('hidden');
                }
                return;
            }

            // Filter cities that match the query
            const matches = enabledCities.filter(city => 
                city.toLowerCase().includes(query)
            ).slice(0, 10); // Limit to 10 suggestions

            if (matches.length > 0) {
                // Show dropdown with matches
                if (cityAutocomplete) {
                    cityAutocomplete.innerHTML = '';
                    matches.forEach((city, index) => {
                        const div = document.createElement('div');
                        div.className = 'px-4 py-3 hover:bg-pink-50 cursor-pointer border-b border-gray-200 last:border-b-0 transition-colors city-suggestion';
                        div.setAttribute('data-index', index);
                        
                        // Highlight matching text
                        const cityLower = city.toLowerCase();
                        const queryIndex = cityLower.indexOf(query);
                        if (queryIndex !== -1) {
                            const beforeMatch = city.substring(0, queryIndex);
                            const match = city.substring(queryIndex, queryIndex + query.length);
                            const afterMatch = city.substring(queryIndex + query.length);
                            div.innerHTML = `${beforeMatch}<strong class="text-pink-600">${match}</strong>${afterMatch}`;
                        } else {
                            div.textContent = city;
                        }
                        
                        div.addEventListener('click', function() {
                            cityInput.value = city;
                            if (cityAutocomplete) {
                                cityAutocomplete.classList.add('hidden');
                            }
                            if (cityErrorMessage) {
                                cityErrorMessage.classList.add('hidden');
                            }
                        });
                        
                        div.addEventListener('mouseenter', function() {
                            div.classList.add('bg-pink-50');
                        });
                        
                        div.addEventListener('mouseleave', function() {
                            div.classList.remove('bg-pink-50');
                        });
                        
                        cityAutocomplete.appendChild(div);
                    });
                    cityAutocomplete.classList.remove('hidden');
                }
                // Hide error message when matches are found
                if (cityErrorMessage) {
                    cityErrorMessage.classList.add('hidden');
                }
            } else {
                // No matches found - hide dropdown and show error message
                if (cityAutocomplete) {
                    cityAutocomplete.classList.add('hidden');
                }
                if (query.length >= 2 && cityErrorMessage) {
                    cityErrorMessage.classList.remove('hidden');
                    cityErrorMessage.style.display = 'block';
                } else if (cityErrorMessage) {
                    cityErrorMessage.classList.add('hidden');
                    cityErrorMessage.style.display = 'none';
                }
            }
        });

        // Hide autocomplete when clicking outside
        document.addEventListener('click', function(e) {
            if (cityInput && cityAutocomplete && 
                !cityInput.contains(e.target) && !cityAutocomplete.contains(e.target)) {
                cityAutocomplete.classList.add('hidden');
            }
        });

        // Validate on form submit
        const form = cityInput.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const inputValue = cityInput.value.trim();
                const isValidCity = enabledCities.some(city => 
                    city.toLowerCase() === inputValue.toLowerCase()
                );
                
                if (!isValidCity) {
                    e.preventDefault();
                    cityErrorMessage?.classList.remove('hidden');
                    cityInput.focus();
                    return false;
                }
            });
        }
    }
});
</script>
</body>
</html>


