<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a {{ $itemType === 'found' ? 'Found' : 'Lost' }} Item - FindITFast</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }
        
        @keyframes fillAnimation {
            0% {
                transform: scaleX(0);
                transform-origin: left;
            }
            100% {
                transform: scaleX(1);
                transform-origin: left;
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.95;
                transform: scale(1.02);
            }
        }
        
        #progress-bar {
            background: linear-gradient(90deg, 
                #ec4899 0%, 
                #f472b6 25%, 
                #ec4899 50%, 
                #f472b6 75%, 
                #ec4899 100%);
            background-size: 200% 100%;
            animation: shimmer 2s linear infinite;
            position: relative;
            overflow: visible;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(236, 72, 153, 0.3);
        }
        
        #progress-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 100%;
            background: linear-gradient(90deg, 
                rgba(255, 255, 255, 0) 0%, 
                rgba(255, 255, 255, 0.5) 50%, 
                rgba(255, 255, 255, 0) 100%);
            animation: shimmer 1.5s linear infinite;
            pointer-events: none;
        }
        
        #progress-bar::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            bottom: -2px;
            width: 4px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 0 8px 8px 0;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.6);
        }
        
        #progress-percentage {
            animation: pulse 2s ease-in-out infinite;
            transition: all 0.3s ease-out;
            text-shadow: 0 2px 4px rgba(236, 72, 153, 0.2);
        }
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

    <div class="container mx-auto px-3 sm:px-4 md:px-6 py-4 sm:py-6 md:py-8 lg:py-12 min-h-[80vh] flex items-center">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 md:gap-8 lg:gap-14 xl:gap-20 items-start mx-auto w-full max-w-7xl">
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

                <form id="post-item-form" class="space-y-4 sm:space-y-5 md:space-y-6" method="POST" action="{{ route('guest.post.submit') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="item_type" value="{{ $itemType }}">

                    @if($enableProvinceField ?? true)
                    <div>
                        <label class="block text-sm sm:text-base font-semibold text-gray-800 mb-2.5">
                            <i class="fas fa-map mr-1.5" style="color: #EC4899;"></i>
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
                                   class="w-full px-4 sm:px-5 py-3 sm:py-3.5 md:py-4 text-sm sm:text-base md:text-lg bg-white border-2 border-gray-200 rounded-xl sm:rounded-2xl focus:outline-none transition-all duration-200 shadow-sm hover:shadow-md hover:border-gray-300"
                                   style="--focus-border: #EC4899; --focus-ring: rgba(236, 72, 153, 0.2);"
                                   onfocus="this.style.borderColor='#EC4899'; this.style.boxShadow='0 0 0 2px rgba(236, 72, 153, 0.2)';"
                                   onblur="this.style.borderColor='rgb(229, 231, 235)'; this.style.boxShadow='';"
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
                        <label class="block text-sm sm:text-base font-semibold text-gray-800 mb-2.5">
                            <i class="fas fa-city mr-1.5" style="color: #EC4899;"></i>
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
                                   class="w-full px-4 sm:px-5 py-3 sm:py-3.5 md:py-4 text-sm sm:text-base md:text-lg bg-white border-2 border-gray-200 rounded-xl sm:rounded-2xl focus:outline-none transition-all duration-200 shadow-sm hover:shadow-md hover:border-gray-300"
                                   style="--focus-border: #EC4899; --focus-ring: rgba(236, 72, 153, 0.2);"
                                   onfocus="this.style.borderColor='#EC4899'; this.style.boxShadow='0 0 0 2px rgba(236, 72, 153, 0.2)';"
                                   onblur="this.style.borderColor='rgb(229, 231, 235)'; this.style.boxShadow='';"
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
                        <label class="block text-sm sm:text-base font-semibold text-gray-800 mb-2.5">
                            <i class="fas fa-map-marker-alt mr-1.5" style="color: #EC4899;"></i>
                            Location <span class="text-red-500">*</span>
                        </label>
                        <div class="flex flex-col sm:flex-row gap-2.5 sm:gap-3 mb-2 relative">
                            <div class="flex-1 relative">
                                <input type="text" id="location" name="location" required autocomplete="off"
                                       class="w-full px-4 py-3 sm:py-3.5 md:py-4 text-sm sm:text-base md:text-lg bg-white border-2 border-gray-200 rounded-xl sm:rounded-2xl focus:outline-none transition-all duration-200 shadow-sm hover:shadow-md hover:border-gray-300"
                                       style="padding-right: 2.75rem; --focus-border: #EC4899; --focus-ring: rgba(236, 72, 153, 0.2);"
                                       onfocus="this.style.borderColor='#EC4899'; this.style.boxShadow='0 0 0 2px rgba(236, 72, 153, 0.2)';"
                                       onblur="this.style.borderColor='rgb(229, 231, 235)'; this.style.boxShadow='';"
                                       placeholder="Where was it lost/found? (e.g., Street name, Building, etc.)" 
                                       value="{{ old('location') }}">
                                <i class="fas fa-search absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm pointer-events-none z-10"></i>
                                <!-- Location autocomplete dropdown -->
                                <div id="location-autocomplete" class="hidden absolute z-50 w-full mt-2 bg-white rounded-xl shadow-2xl max-h-48 sm:max-h-56 md:max-h-60 overflow-y-auto"
                                     style="border: 2px solid #EC4899;">
                                    <!-- Suggestions will be inserted here -->
                                </div>
                            </div>
                            <button type="button" onclick="useCurrentLocation('location')" 
                                    class="w-auto px-3 sm:px-4 md:px-5 py-3 sm:py-3.5 md:py-4 bg-blue-500 text-white rounded-xl sm:rounded-2xl hover:bg-blue-600 active:bg-blue-700 transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg transform hover:-translate-y-0.5 active:translate-y-0 border border-blue-400"
                                    title="Use Current Location">
                                <i class="fas fa-crosshairs text-base sm:text-lg md:text-xl"></i>
                            </button>
                        </div>
                        <!-- Hidden fields for coordinates -->
                        <input type="hidden" id="location-lat" name="location_lat">
                        <input type="hidden" id="location-lon" name="location_lon">
                        <!-- Map container -->
                        <div class="mt-3">
                            <div id="location-map" class="w-full h-48 sm:h-56 md:h-64 lg:h-72 rounded-lg sm:rounded-xl border border-gray-300 relative" style="display: none;">
                                <!-- Location autocomplete overlay on map -->
                                <div id="location-autocomplete-map" class="hidden absolute top-2 left-2 right-2 z-[1000] bg-white border-2 border-pink-300 rounded-lg shadow-2xl max-h-48 sm:max-h-56 md:max-h-60 overflow-y-auto">
                                    <!-- Suggestions will be inserted here -->
                                </div>
                            </div>
                            <div class="mt-2 flex flex-col sm:flex-row gap-2.5 sm:gap-3">
                                <button type="button" onclick="toggleLocationMap('location')" 
                                        class="w-full sm:w-auto px-4 sm:px-5 md:px-6 py-2.5 sm:py-3 md:py-3.5 text-white font-semibold rounded-xl sm:rounded-2xl transition-all duration-200 text-xs sm:text-sm md:text-base shadow-md hover:shadow-lg border flex items-center justify-center sm:justify-start gap-2 transform hover:-translate-y-0.5 active:translate-y-0"
                                        style="background-color: #EC4899; border-color: #EC4899;"
                                        onmouseover="this.style.backgroundColor='#db2777'; this.style.borderColor='#db2777';"
                                        onmouseout="this.style.backgroundColor='#EC4899'; this.style.borderColor='#EC4899';">
                                    <i class="fas fa-map-marker-alt text-sm sm:text-base"></i> 
                                    <span>Pin on Map</span>
                                </button>
                                <button type="button" onclick="clearLocationMap('location')" 
                                        class="w-full sm:w-auto px-4 sm:px-5 md:px-6 py-2.5 sm:py-3 md:py-3.5 text-white font-semibold rounded-xl sm:rounded-2xl transition-all duration-200 text-xs sm:text-sm md:text-base shadow-md hover:shadow-lg border flex items-center justify-center sm:justify-start gap-2 transform hover:-translate-y-0.5 active:translate-y-0" 
                                        style="display: none; background-color: #6B7280; border-color: #6B7280;"
                                        onmouseover="this.style.backgroundColor='#4B5563'; this.style.borderColor='#4B5563';"
                                        onmouseout="this.style.backgroundColor='#6B7280'; this.style.borderColor='#6B7280';"
                                        id="clear-location-btn">
                                    <i class="fas fa-times text-sm sm:text-base"></i> 
                                    <span>Clear Map</span>
                                </button>
                            </div>
                        </div>
                        @error('location')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm sm:text-base font-semibold text-gray-800 mb-2.5">
                            <i class="fas fa-align-left mr-1.5" style="color: #EC4899;"></i>
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea name="description" rows="4" required class="w-full px-4 sm:px-5 py-3 sm:py-3.5 md:py-4 text-sm sm:text-base md:text-lg bg-white border-2 border-gray-200 rounded-xl sm:rounded-2xl focus:outline-none transition-all duration-200 shadow-sm hover:shadow-md hover:border-gray-300 resize-y" style="--focus-border: #EC4899; --focus-ring: rgba(236, 72, 153, 0.2);" onfocus="this.style.borderColor='#EC4899'; this.style.boxShadow='0 0 0 2px rgba(236, 72, 153, 0.2)';" onblur="this.style.borderColor='rgb(229, 231, 235)'; this.style.boxShadow='';" placeholder="Describe the item and key details...">{{ old('description', !empty($searchQuery) ? 'Looking for: ' . $searchQuery : '') }}</textarea>
                        @error('description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        @if(!empty($searchQuery))
                            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Pre-filled based on your search. Feel free to edit and add more details.
                            </p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm sm:text-base font-semibold text-gray-800 mb-2.5">
                            <i class="fas fa-tags mr-1.5" style="color: #EC4899;"></i>
                            Tags <span class="text-red-500">*</span>
                        </label>
                        
                        <!-- Tag Dropdown -->
                        <div class="relative mb-2">
                            <select id="tags-dropdown" 
                                    class="w-full px-4 sm:px-5 py-3 sm:py-3.5 md:py-4 pr-10 sm:pr-12 text-sm sm:text-base md:text-lg bg-white border-2 border-gray-200 rounded-xl sm:rounded-2xl focus:outline-none transition-all duration-200 shadow-sm hover:shadow-md hover:border-gray-300 cursor-pointer"
                                    style="appearance: none; -webkit-appearance: none; -moz-appearance: none; --focus-border: #EC4899; --focus-ring: rgba(236, 72, 153, 0.2);"
                                    onfocus="this.style.borderColor='#EC4899'; this.style.boxShadow='0 0 0 2px rgba(236, 72, 153, 0.2)';"
                                    onblur="this.style.borderColor='rgb(229, 231, 235)'; this.style.boxShadow='';">
                                <option value="">Select a tag...</option>
                                <!-- Options will be loaded dynamically -->
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none z-10"></i>
                        </div>
                        
                        <!-- Add New Tag Button -->
                        <div class="mb-2">
                            <button type="button" 
                                    onclick="toggleNewTagInput()" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 sm:px-5 sm:py-2.5 text-sm sm:text-base text-white font-semibold rounded-xl sm:rounded-2xl transition-all duration-200 border shadow-md hover:shadow-lg"
                                    style="background-color: #EC4899; border-color: #EC4899;"
                                    onmouseover="this.style.backgroundColor='#db2777'; this.style.borderColor='#db2777';"
                                    onmouseout="this.style.backgroundColor='#EC4899'; this.style.borderColor='#EC4899';">
                                <i class="fas fa-plus text-xs sm:text-sm"></i>
                                <span>Add another tag</span>
                            </button>
                        </div>
                        
                        <!-- Add New Tag Input (Hidden by default) -->
                        <div id="new-tag-input-container" class="flex gap-2 mb-2 hidden">
                            <input type="text" 
                                   id="new-tag-input" 
                                   class="flex-1 px-3 sm:px-4 py-3 sm:py-4 text-base sm:text-lg bg-white border border-gray-300 rounded-lg sm:rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400"
                                   placeholder="Type a new tag">
                            <button type="button" 
                                    onclick="addTagFromInput()" 
                                    class="px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button type="button" 
                                    onclick="toggleNewTagInput()" 
                                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- Selected Tags Display -->
                        <div id="selected-tags-container" class="flex flex-wrap gap-2 mt-2 min-h-[20px]">
                            <!-- Selected tags will appear here -->
                        </div>
                        
                        <!-- Hidden input to store selected tags as JSON -->
                        <input type="hidden" id="tags" name="tags" required value="{{ old('tags', !empty($searchQuery) ? $searchQuery : '') }}">
                        
                        <p class="text-xs text-gray-500 mt-1">Select tags from the dropdown or add new ones. Tags help others find your item more easily.</p>
                        @error('tags')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm sm:text-base font-semibold text-gray-800 mb-2.5 sm:mb-3">
                            <i class="fas fa-images mr-1.5" style="color: #EC4899;"></i>
                            Images <span class="text-red-500">*</span>
                        </label>
                        
                        <!-- Drag and Drop Zone -->
                        <div id="drop-zone" class="relative border-2 border-dashed border-gray-300 rounded-xl sm:rounded-2xl p-6 sm:p-8 md:p-10 text-center transition-all duration-300 cursor-pointer shadow-sm hover:shadow-lg"
                             onmouseenter="this.style.borderColor='#EC4899'; this.style.backgroundColor='rgba(236, 72, 153, 0.05)';"
                             onmouseleave="this.style.borderColor='rgb(209, 213, 219)'; this.style.backgroundColor='';">
                            <input type="file" id="item-images" name="images[]" multiple accept="image/*" class="hidden" required>
                            
                            <div id="drop-zone-content" class="space-y-4 sm:space-y-5">
                                <div class="flex justify-center">
                                    <div class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 rounded-full flex items-center justify-center shadow-md"
                                         style="background: linear-gradient(135deg, rgba(236, 72, 153, 0.1), rgba(236, 72, 153, 0.2));">
                                        <i class="fas fa-cloud-upload-alt text-2xl sm:text-3xl md:text-4xl" style="color: #EC4899;"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-base sm:text-lg md:text-xl font-semibold text-gray-800 mb-2">
                                        <span style="color: #EC4899;">Click to upload</span> <span class="hidden sm:inline text-gray-600">or drag and drop</span>
                                    </p>
                                    <p class="text-xs sm:text-sm text-gray-500">PNG, JPG, GIF up to 10MB each (Max 5 images)</p>
                                </div>
                                <button type="button" onclick="document.getElementById('item-images').click()" 
                                        class="inline-flex items-center px-5 sm:px-6 md:px-8 py-2.5 sm:py-3 text-white rounded-xl sm:rounded-2xl transition-all duration-200 text-sm sm:text-base font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 active:translate-y-0 border"
                                        style="background-color: #EC4899; border-color: #EC4899;"
                                        onmouseover="this.style.backgroundColor='#db2777'; this.style.borderColor='#db2777';"
                                        onmouseout="this.style.backgroundColor='#EC4899'; this.style.borderColor='#EC4899';">
                                    <i class="fas fa-folder-open mr-2 text-base sm:text-lg"></i>
                                    <span>Browse Files</span>
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
                    <button type="submit" id="submit-btn" class="w-full text-white font-semibold py-3.5 sm:py-4 md:py-5 rounded-xl sm:rounded-2xl focus:outline-none focus:ring-2 focus:ring-pink-300 text-base sm:text-lg md:text-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0 border"
                            style="background-color: #EC4899; border-color: #EC4899;"
                            onmouseover="this.style.backgroundColor='#db2777'; this.style.borderColor='#db2777';"
                            onmouseout="this.style.backgroundColor='#EC4899'; this.style.borderColor='#EC4899';">
                        <span id="submit-btn-text" class="flex items-center justify-center">
                            <i class="fas fa-arrow-right mr-2"></i>
                            Continue
                        </span>
                        <span id="submit-btn-spinner" class="hidden flex items-center justify-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Processing...
                        </span>
                    </button>
                    
                    <!-- Upload Progress (staged) -->
                    <div id="processing-indicator" class="mt-4 hidden">
                        <div class="rounded-xl border border-pink-200 bg-pink-50/60 p-3 sm:p-4 shadow-sm">
                            <div class="flex items-start gap-3">
                                <span id="upload-stage-icon" class="inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white shadow text-pink-600 shrink-0">
                                    <i class="fas fa-circle-notch fa-spin text-base sm:text-lg"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <span id="upload-stage-label" class="text-sm sm:text-base font-semibold text-gray-900 truncate">Preparing your upload…</span>
                                        <span id="upload-progress-percent" class="text-xs sm:text-sm font-medium text-pink-700 tabular-nums">0%</span>
                                    </div>
                                    <p id="upload-stage-detail" class="text-xs sm:text-sm text-gray-600 mt-0.5">Getting your images ready</p>
                                    <div class="w-full bg-pink-100 rounded-full h-2 sm:h-2.5 mt-2 overflow-hidden">
                                        <div id="progress-bar" class="h-full rounded-full bg-gradient-to-r from-pink-500 to-rose-500 transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Match-checking note -->
                            <div id="matching-check-message" class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg hidden">
                                <div class="flex items-start space-x-2">
                                    <i class="fas fa-search text-blue-600 animate-pulse mt-0.5"></i>
                                    <span class="text-xs sm:text-sm text-blue-800">
                                        <strong>Match checking continues in the background.</strong> Once you sign in or register, you'll see a bell notification if similar items are found.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Warning Note -->
                        <div id="processing-warning-note" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start space-x-2">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>
                                <p class="text-xs sm:text-sm text-yellow-800">
                                    <strong>Please don't refresh or close this page while uploading.</strong> Your images are being sent and saved.
                                </p>
                            </div>
                        </div>
                    </div>
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
                
                // Only validate if field is required and has a value
                const isRequired = provinceInput.hasAttribute('required');
                if (!isRequired && !inputValue) {
                    // Field is optional and empty, skip validation
                    return true;
                }
                
                // If field has a value, validate it's in the enabled list
                if (inputValue && enabledProvinces.length > 0) {
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
                        // Scroll to error
                        provinceInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return false;
                    }
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
                
                // Only validate if field is required and has a value
                const isRequired = cityInput.hasAttribute('required');
                if (!isRequired && !inputValue) {
                    // Field is optional and empty, skip validation
                    return true;
                }
                
                // If field has a value, validate it's in the enabled list
                if (inputValue && enabledCities.length > 0) {
                    const isValidCity = enabledCities.some(city => 
                        city.toLowerCase() === inputValue.toLowerCase()
                    );
                    
                    if (!isValidCity) {
                        e.preventDefault();
                        if (cityErrorMessage) {
                            cityErrorMessage.classList.remove('hidden');
                            cityErrorMessage.style.display = 'block';
                        }
                        cityInput.focus();
                        // Scroll to error
                        cityInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return false;
                    }
                }
            });
        }
    }
    
    // Load tags from API
    loadTags();
});

// Tag management functionality for guest post page
let availableTags = [];
let selectedTags = [];

// Load tags from API
async function loadTags() {
    try {
        const response = await fetch('/api/tags');
        const data = await response.json();
        availableTags = data;
        
        // Populate dropdown
        populateTagDropdown('tags-dropdown', availableTags);
        
        // Load existing tags from hidden input if present
        const hiddenInput = document.getElementById('tags');
        if (hiddenInput && hiddenInput.value) {
            try {
                const existingTags = JSON.parse(hiddenInput.value);
                if (Array.isArray(existingTags)) {
                    selectedTags = existingTags;
                    updateSelectedTagsDisplay('selected-tags-container', selectedTags, 'tags');
                } else if (typeof existingTags === 'string') {
                    // Handle comma-separated string
                    selectedTags = existingTags.split(',').map(t => t.trim()).filter(t => t);
                    updateSelectedTagsDisplay('selected-tags-container', selectedTags, 'tags');
                }
            } catch (e) {
                // If it's a comma-separated string, parse it
                const tagsString = hiddenInput.value;
                if (tagsString) {
                    selectedTags = tagsString.split(',').map(t => t.trim()).filter(t => t);
                    updateSelectedTagsDisplay('selected-tags-container', selectedTags, 'tags');
                }
            }
        }
    } catch (error) {
        console.error('Error loading tags:', error);
    }
}

// Populate tag dropdown
function populateTagDropdown(dropdownId, tags) {
    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) return;
    
    // Clear existing options except the first one
    dropdown.innerHTML = '<option value="">Select a tag...</option>';
    
    tags.forEach(tag => {
        const option = document.createElement('option');
        option.value = tag.name;
        option.textContent = `${tag.name} (${tag.usage_count} uses)`;
        option.dataset.tagId = tag.id;
        dropdown.appendChild(option);
    });
}

// Add tag from dropdown
function addTagFromDropdown() {
    const dropdown = document.getElementById('tags-dropdown');
    if (!dropdown || !dropdown.value) return;
    
    const tagName = dropdown.value.trim();
    if (tagName && !selectedTags.includes(tagName)) {
        selectedTags.push(tagName);
        updateSelectedTagsDisplay('selected-tags-container', selectedTags, 'tags');
        dropdown.value = ''; // Reset dropdown
    }
}

// Toggle new tag input field
function toggleNewTagInput() {
    const container = document.getElementById('new-tag-input-container');
    if (container) {
        container.classList.toggle('hidden');
        const input = document.getElementById('new-tag-input');
        if (input && !container.classList.contains('hidden')) {
            input.focus();
        }
    }
}

// Add tag from input field
function addTagFromInput() {
    const input = document.getElementById('new-tag-input');
    if (!input) return;
    
    const tagName = input.value.trim();
    if (tagName && !selectedTags.includes(tagName)) {
        selectedTags.push(tagName);
        updateSelectedTagsDisplay('selected-tags-container', selectedTags, 'tags');
        input.value = ''; // Clear input
        // Hide the input field after adding
        toggleNewTagInput();
    }
}

// Update selected tags display
function updateSelectedTagsDisplay(containerId, tagsArray, hiddenInputId) {
    const container = document.getElementById(containerId);
    const hiddenInput = document.getElementById(hiddenInputId);
    
    if (!container || !hiddenInput) return;
    
    // Update hidden input with JSON array
    hiddenInput.value = JSON.stringify(tagsArray);
    
    // Update display - only show tags if there are any
    if (tagsArray.length === 0) {
        container.innerHTML = '';
        container.style.display = 'none';
    } else {
        container.style.display = 'flex';
        container.innerHTML = tagsArray.map((tag, index) => `
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-pink-100 text-pink-800 rounded-full text-sm font-medium">
                ${tag}
                <button type="button" 
                        onclick="removeTag('${tag}', '${containerId}', '${hiddenInputId}')" 
                        class="ml-1 text-pink-600 hover:text-pink-800">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </span>
        `).join('');
    }
}

// Remove tag
function removeTag(tagName, containerId, hiddenInputId) {
    selectedTags = selectedTags.filter(t => t !== tagName);
    updateSelectedTagsDisplay(containerId, selectedTags, hiddenInputId);
}

// Add event listeners for dropdown and input
document.addEventListener('DOMContentLoaded', function() {
    const tagsDropdown = document.getElementById('tags-dropdown');
    if (tagsDropdown) {
        tagsDropdown.addEventListener('change', function() {
            if (this.value) {
                addTagFromDropdown();
            }
        });
    }
    
    // Handle Enter key in new tag input
    const newTagInput = document.getElementById('new-tag-input');
    if (newTagInput) {
        newTagInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addTagFromInput();
            }
        });
    }
});

// Form submission with processing indicator
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('post-item-form');
    const submitBtn = document.getElementById('submit-btn');
    const submitBtnText = document.getElementById('submit-btn-text');
    const submitBtnSpinner = document.getElementById('submit-btn-spinner');
    const processingIndicator = document.getElementById('processing-indicator');
    const progressBar = document.getElementById('progress-bar');
    const progressPercentage = document.getElementById('upload-progress-percent');
    const stageIcon = document.getElementById('upload-stage-icon');
    const stageLabel = document.getElementById('upload-stage-label');
    const stageDetail = document.getElementById('upload-stage-detail');
    const matchingMessage = document.getElementById('matching-check-message');

    /** Format raw bytes into a short human-readable string. */
    function formatBytes(bytes) {
        if (bytes === 0 || !Number.isFinite(bytes)) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        const i = Math.min(units.length - 1, Math.floor(Math.log(bytes) / Math.log(1024)));
        return (bytes / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 1) + ' ' + units[i];
    }

    /** Update only the progress bar + percent label (no stage change). */
    function updateProgress(percent) {
        if (!progressBar || !progressPercentage) return;
        const clamped = Math.max(0, Math.min(100, percent));
        progressBar.style.width = clamped + '%';
        progressPercentage.textContent = Math.round(clamped) + '%';
    }

    /**
     * Staged upload status. Matches the implementation on `/reported-items`.
     * Stages: preparing | uploading | processing | done | error
     *
     * For the "uploading" and "processing" stages this also schedules a short
     * timeline of reassurance messages so long uploads keep communicating
     * progress and don't look stuck.
     *
     * opts:
     *   fileCount  – number of files being uploaded (used in "uploading X images…")
     *   bytesText  – e.g. "1.2 MB / 4.5 MB sent"
     *   label      – override label (error stage)
     *   detail     – override detail (error stage)
     */
    const STAGE_TIMELINE = {
        uploading: [
            { afterMs: 12000, suffix: ' · Taking a bit longer than expected — please don\'t close this page.' },
            { afterMs: 30000, suffix: ' · Still uploading — your photos are safe, hold tight.' },
            { afterMs: 60000, suffix: ' · We\'re working through a slow connection. Hang in there.' },
        ],
        processing: [
            { afterMs: 0,     label: 'Saving your item…',                 detail: 'Finalising your post' },
            { afterMs: 3500,  label: 'Analysing image content…',          detail: 'Running object detection on your photos' },
            { afterMs: 8000,  label: 'Matching similar items…',           detail: 'Comparing against the database for similar reports' },
            { afterMs: 14000, label: 'Almost done…',                      detail: 'Wrapping up notifications' },
            { afterMs: 25000, label: 'Taking a bit longer than expected', detail: 'Your upload is safe — we\'re still finalising' },
        ],
    };

    let __stageTimers = [];
    let __stageStart = 0;
    let __activeStage = null;
    let __lastBytesText = '';

    function __clearStageTimers() {
        __stageTimers.forEach((t) => clearTimeout(t));
        __stageTimers = [];
    }

    function setUploadStage(stage, opts = {}) {
        if (!stageIcon || !stageLabel || !stageDetail) return;

        const stages = {
            preparing: {
                icon: '<i class="fas fa-circle-notch fa-spin text-base sm:text-lg"></i>',
                label: 'Preparing your upload…',
                detail: opts.fileCount
                    ? `Getting ${opts.fileCount} image${opts.fileCount === 1 ? '' : 's'} ready`
                    : 'Getting your images ready',
                color: 'text-pink-600',
            },
            uploading: {
                icon: '<i class="fas fa-cloud-upload-alt text-base sm:text-lg"></i>',
                label: opts.fileCount
                    ? `Uploading ${opts.fileCount} image${opts.fileCount === 1 ? '' : 's'}…`
                    : 'Uploading images…',
                detail: opts.bytesText || 'Sending to FindITFast',
                color: 'text-pink-600',
            },
            processing: {
                icon: '<i class="fas fa-cog fa-spin text-base sm:text-lg"></i>',
                label: 'Saving your item…',
                detail: 'Almost done, finalizing your post',
                color: 'text-pink-600',
            },
            done: {
                icon: '<i class="fas fa-check text-base sm:text-lg"></i>',
                label: 'Done! Item posted',
                detail: 'Match checking continues in the background',
                color: 'text-green-600',
            },
            error: {
                icon: '<i class="fas fa-exclamation-triangle text-base sm:text-lg"></i>',
                label: opts.label || 'Upload failed',
                detail: opts.detail || 'Please try again',
                color: 'text-red-600',
            },
        };

        const s = stages[stage] || stages.preparing;
        stageIcon.className = `inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white shadow shrink-0 ${s.color}`;
        stageIcon.innerHTML = s.icon;
        stageLabel.textContent = s.label;

        if (stage === 'uploading' && opts.bytesText) {
            __lastBytesText = opts.bytesText;
        }
        stageDetail.textContent = s.detail;

        if (stage !== __activeStage) {
            __clearStageTimers();
            __activeStage = stage;
            __stageStart = Date.now();

            const timeline = STAGE_TIMELINE[stage];
            if (Array.isArray(timeline)) {
                timeline.forEach((step) => {
                    const tid = setTimeout(() => {
                        if (__activeStage !== stage) return;
                        if (step.label) stageLabel.textContent = step.label;
                        if (step.detail) stageDetail.textContent = step.detail;
                        if (step.suffix) {
                            const base = __lastBytesText || stageDetail.textContent || '';
                            stageDetail.textContent = base + step.suffix;
                        }
                    }, step.afterMs);
                    __stageTimers.push(tid);
                });
            }
        } else if (stage === 'uploading' && opts.bytesText) {
            // Same stage, progress tick — refresh bytes string and re-apply
            // any active "taking longer" suffix so it doesn't flicker away.
            const elapsed = Date.now() - __stageStart;
            const activeSuffix = (STAGE_TIMELINE.uploading || [])
                .filter((step) => step.suffix && elapsed >= step.afterMs)
                .pop();
            stageDetail.textContent = activeSuffix
                ? opts.bytesText + activeSuffix.suffix
                : opts.bytesText;
        }

        if (stage === 'done' || stage === 'error') {
            __clearStageTimers();
        }
    }

    function showMatchingCheckMessage() {
        if (matchingMessage) matchingMessage.classList.remove('hidden');
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Native validation first.
            if (!form.checkValidity()) {
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
                return;
            }
            if (submitBtn.disabled) return;

            // Lock the submit button and reveal the staged progress card.
            submitBtn.disabled = true;
            submitBtnText.classList.add('hidden');
            submitBtnSpinner.classList.remove('hidden');

            const fileInput = document.getElementById('item-images');
            const fileCount = fileInput && fileInput.files ? fileInput.files.length : 0;

            processingIndicator.classList.remove('hidden');
            updateProgress(0);
            setUploadStage('preparing', { fileCount });
            // Hide the matching note until we have a successful upload.
            if (matchingMessage) matchingMessage.classList.add('hidden');

            setTimeout(() => {
                processingIndicator.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);

            const formData = new FormData(form);
            const xhr = new XMLHttpRequest();

            // -- Real upload progress (XHR) maps to the "uploading" stage ----
            xhr.upload.addEventListener('loadstart', function(e) {
                setUploadStage('uploading', {
                    fileCount,
                    bytesText: `0 / ${formatBytes(e.total || 0)} sent`,
                });
            });
            xhr.upload.addEventListener('progress', function(e) {
                if (!e.lengthComputable) return;
                const pct = (e.loaded / e.total) * 95; // leave 5% for server work
                updateProgress(pct);
                setUploadStage('uploading', {
                    fileCount,
                    bytesText: `${formatBytes(e.loaded)} / ${formatBytes(e.total)} sent`,
                });
            });
            xhr.upload.addEventListener('load', function() {
                // Bytes are now on the server — move to "processing".
                updateProgress(95);
                setUploadStage('processing');
            });

            // -- Response handling -----------------------------------------
            xhr.addEventListener('load', function() {
                if (xhr.status === 200 || xhr.status === 201) {
                    updateProgress(100);
                    setUploadStage('done');
                    showMatchingCheckMessage();

                    // Resolve a redirect target from a variety of response shapes.
                    let redirectTo = '/reported-items';
                    if (xhr.responseURL && xhr.responseURL !== window.location.href
                        && xhr.responseURL.includes('/reported-items')) {
                        redirectTo = xhr.responseURL;
                    } else {
                        const location = xhr.getResponseHeader('Location');
                        if (location && location.includes('/reported-items')) {
                            redirectTo = location;
                        } else {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.redirect && response.redirect.includes('/reported-items')) {
                                    redirectTo = response.redirect;
                                } else if (response.url && response.url.includes('/reported-items')) {
                                    redirectTo = response.url;
                                }
                            } catch (_) {
                                const match = xhr.responseText.match(/window\.location\.href\s*=\s*['"]([^'"]+)['"]/);
                                if (match && match[1].includes('/reported-items')) {
                                    redirectTo = match[1];
                                }
                            }
                        }
                    }

                    setTimeout(() => { window.location.href = redirectTo; }, 800);
                    return;
                }

                // -- Error path ----------------------------------------------
                let errorMessage = 'Upload failed. Please check your fields and try again.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) errorMessage = response.message;
                    else if (response.error) errorMessage = response.error;
                    else if (response.errors) {
                        const firstField = Object.keys(response.errors)[0];
                        const list = firstField ? response.errors[firstField] : null;
                        if (Array.isArray(list) && list.length > 0) errorMessage = list[0];
                    }
                } catch (_) {
                    if (xhr.status === 302 || xhr.status === 422) {
                        errorMessage = 'Some fields are invalid. Please check your inputs and selected image format/size.';
                    } else if (xhr.status === 413) {
                        errorMessage = 'Your images are too large. Please reduce the size and try again.';
                    } else if (xhr.status === 0) {
                        errorMessage = 'Network error. Please check your connection and try again.';
                    }
                }

                setUploadStage('error', { detail: errorMessage });
                submitBtn.disabled = false;
                submitBtnText.classList.remove('hidden');
                submitBtnSpinner.classList.add('hidden');
            });

            xhr.addEventListener('error', function() {
                setUploadStage('error', { detail: 'Network error. Please check your connection and try again.' });
                submitBtn.disabled = false;
                submitBtnText.classList.remove('hidden');
                submitBtnSpinner.classList.add('hidden');
            });

            xhr.addEventListener('timeout', function() {
                setUploadStage('error', { detail: 'Upload timed out. Please try again with smaller images or a stronger connection.' });
                submitBtn.disabled = false;
                submitBtnText.classList.remove('hidden');
                submitBtnSpinner.classList.add('hidden');
            });

            xhr.addEventListener('abort', function() {
                setUploadStage('error', { label: 'Upload cancelled', detail: 'You stopped the upload before it finished.' });
                submitBtn.disabled = false;
                submitBtnText.classList.remove('hidden');
                submitBtnSpinner.classList.add('hidden');
            });

            xhr.open('POST', form.action || window.location.href);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.send(formData);
        });
    }
});
</script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script>
// Map functionality for location
let locationMaps = {};
let locationMarkers = {};
let isSettingLocationProgrammatically = false;
let locationAutocompleteTimeout = {};
let selectedSuggestionIndex = -1;

// Initialize location map
function initializeLocationMap(mapId, inputId) {
    const mapElement = document.getElementById(mapId);
    if (!mapElement || locationMaps[mapId]) return;
    
    // Initialize map centered on a default location (you can change this)
    const map = L.map(mapId).setView([14.5995, 120.9842], 13); // Default to Manila, Philippines
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    locationMaps[mapId] = map;
    
    // Add click handler to place marker
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lon = e.latlng.lng;
        
        // Place marker
        if (locationMarkers[mapId]) {
            locationMarkers[mapId].setLatLng([lat, lon]);
        } else {
            locationMarkers[mapId] = L.marker([lat, lon], {
                draggable: true
            }).addTo(map);
            
            // Update location when marker is dragged
            locationMarkers[mapId].on('dragend', function() {
                const position = locationMarkers[mapId].getLatLng();
                updateLocationFromMap(inputId, position.lat, position.lng);
                reverseGeocode(position.lat, position.lng).then(geo => fillLocationFields(inputId, geo));
            });
        }
        
        // Reverse geocode then auto-fill Province + City + Location (street).
        reverseGeocode(lat, lon).then(geo => fillLocationFields(inputId, geo));

        updateLocationFromMap(inputId, lat, lon);
    });
}

// Update location from map coordinates
function updateLocationFromMap(inputId, lat, lon) {
    const latInput = document.getElementById(`${inputId}-lat`);
    const lonInput = document.getElementById(`${inputId}-lon`);
    
    if (latInput) latInput.value = lat;
    if (lonInput) lonInput.value = lon;
}

// Use current location
function useCurrentLocation(inputId) {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser');
        return;
    }
    
    navigator.geolocation.getCurrentPosition(
        async function(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;

            updateLocationFromMap(inputId, lat, lon);

            // Reverse geocode then auto-fill Province + City + Location (street).
            const geo = await reverseGeocode(lat, lon);
            fillLocationFields(inputId, geo);

            const mapId = inputId === 'location' ? 'location-map' : 'edit-location-map';
            const mapElement = document.getElementById(mapId);
            if (mapElement && (mapElement.style.display === 'none' || !mapElement.style.display)) {
                toggleLocationMap(inputId);
            }

            setTimeout(() => {
                pinLocationOnMap(mapId, inputId, lat, lon);
            }, 300);
        },
        function(error) {
            console.error('Error getting location:', error);
            alert('Unable to get your current location. Please enter it manually.');
        }
    );
}

// Pin location on map
function pinLocationOnMap(mapId, inputId, lat, lon) {
    if (!locationMaps[mapId]) {
        initializeLocationMap(mapId, inputId);
    }
    
    setTimeout(() => {
        const map = locationMaps[mapId];
        if (map) {
            map.setView([lat, lon], 15);
            
            if (locationMarkers[mapId]) {
                locationMarkers[mapId].setLatLng([lat, lon]);
            } else {
                locationMarkers[mapId] = L.marker([lat, lon], {
                    draggable: true
                }).addTo(map);
                
                locationMarkers[mapId].on('dragend', function() {
                    const position = locationMarkers[mapId].getLatLng();
                    updateLocationFromMap(inputId, position.lat, position.lng);
                    reverseGeocode(position.lat, position.lng).then(geo => fillLocationFields(inputId, geo));
                });
            }
        }
    }, 100);
}

// Toggle location map
function toggleLocationMap(inputId) {
    const mapId = inputId === 'location' ? 'location-map' : 'edit-location-map';
    const mapElement = document.getElementById(mapId);
    const clearBtn = document.getElementById(`clear-${inputId}-btn`);
    
    if (!mapElement) return;
    
    if (mapElement.style.display === 'none' || !mapElement.style.display) {
        // Show map
        mapElement.style.display = 'block';
        if (clearBtn) clearBtn.style.display = 'block';
        
        // Initialize map if not already initialized
        if (!locationMaps[mapId]) {
            initializeLocationMap(mapId, inputId);
        } else {
            // Invalidate size to fix rendering issues
            setTimeout(() => {
                locationMaps[mapId].invalidateSize();
            }, 100);
        }
        
        // If there's already a location, center on it
        const latInput = document.getElementById(`${inputId}-lat`);
        const lonInput = document.getElementById(`${inputId}-lon`);
        if (latInput && lonInput && latInput.value && lonInput.value) {
            const lat = parseFloat(latInput.value);
            const lon = parseFloat(lonInput.value);
            if (!isNaN(lat) && !isNaN(lon)) {
                setTimeout(() => {
                    pinLocationOnMap(mapId, inputId, lat, lon);
                }, 200);
            }
        }
    } else {
        // Hide map
        mapElement.style.display = 'none';
        if (clearBtn) clearBtn.style.display = 'none';
        hideLocationAutocomplete(inputId);
    }
}

// Clear location map
function clearLocationMap(inputId) {
    const mapId = inputId === 'location' ? 'location-map' : 'edit-location-map';
    const locationInput = document.getElementById(inputId);
    const latInput = document.getElementById(`${inputId}-lat`);
    const lonInput = document.getElementById(`${inputId}-lon`);
    
    // Clear inputs
    if (locationInput) locationInput.value = '';
    if (latInput) latInput.value = '';
    if (lonInput) lonInput.value = '';
    
    // Remove marker
    if (locationMarkers[mapId]) {
        locationMaps[mapId].removeLayer(locationMarkers[mapId]);
        delete locationMarkers[mapId];
    }
    
    // Hide map
    const mapElement = document.getElementById(mapId);
    if (mapElement) {
        mapElement.style.display = 'none';
    }
    
    const clearBtn = document.getElementById(`clear-${inputId}-btn`);
    if (clearBtn) {
        clearBtn.style.display = 'none';
    }
    
    hideLocationAutocomplete(inputId);
}

/**
 * Reverse-geocode coordinates via Nominatim and return a structured address.
 *
 * Result shape:
 *   {
 *     display:  full human-readable string,
 *     street:   street/road only (house number + road, falls back to pedestrian/etc.),
 *     city:     city / town / municipality / village,
 *     province: state / region,
 *     country:  country name,
 *     raw:      original Nominatim address object,
 *   }
 *
 * A toString() is attached so existing callers that treat the result as a
 * string (template literals, direct assignment) continue to work and resolve
 * to `display`.
 */
async function reverseGeocode(lat, lon) {
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`,
            {
                headers: { 'Accept': 'application/json' }
            }
        );

        const data = await response.json();
        const addr = (data && data.address) ? data.address : {};

        const streetParts = [];
        if (addr.house_number) streetParts.push(addr.house_number);
        if (addr.road) streetParts.push(addr.road);
        else if (addr.pedestrian) streetParts.push(addr.pedestrian);
        else if (addr.footway) streetParts.push(addr.footway);
        else if (addr.path) streetParts.push(addr.path);
        else if (addr.neighbourhood) streetParts.push(addr.neighbourhood);
        else if (addr.suburb) streetParts.push(addr.suburb);

        const street = streetParts.join(' ').trim();
        const city = addr.city || addr.town || addr.municipality || addr.village || addr.hamlet || '';
        const province = addr.state || addr.region || addr.province || addr.state_district || '';
        const country = addr.country || '';

        const displayParts = [];
        if (street) displayParts.push(street);
        if (addr.suburb && addr.suburb !== street) displayParts.push(addr.suburb);
        if (city) displayParts.push(city);
        if (province) displayParts.push(province);
        if (country) displayParts.push(country);
        const display = displayParts.length > 0
            ? displayParts.join(', ')
            : (data.display_name || `${lat}, ${lon}`);

        const result = { display, street, city, province, country, raw: addr };
        result.toString = () => display;
        return result;
    } catch (error) {
        console.error('Error reverse geocoding:', error);
        const empty = { display: '', street: '', city: '', province: '', country: '', raw: {} };
        empty.toString = () => '';
        return empty;
    }
}

/**
 * Apply a reverse-geocode result to the guest form fields.
 *
 * - Location → full address (street + suburb + city + province + country).
 * - Province → `province-input`
 * - City     → `city-input`
 *
 * Dispatches input/change events so any existing autocomplete-validation
 * (the "we don't cover this province yet" banner) still runs.
 */
function fillLocationFields(inputId, geo) {
    if (!geo) return;
    const locationInput = document.getElementById(inputId);
    const provinceInput = document.getElementById('province-input');
    const cityInput = document.getElementById('city-input');

    isSettingLocationProgrammatically = true;
    try {
        if (locationInput) {
            // Full composed address; falls back to street-only if Nominatim
            // didn't return any administrative components.
            locationInput.value = geo.display || geo.street || '';
        }
        if (provinceInput && geo.province) {
            provinceInput.value = geo.province;
            provinceInput.dispatchEvent(new Event('input', { bubbles: true }));
            provinceInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        if (cityInput && geo.city) {
            cityInput.value = geo.city;
            cityInput.dispatchEvent(new Event('input', { bubbles: true }));
            cityInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    } finally {
        setTimeout(() => { isSettingLocationProgrammatically = false; }, 100);
    }
}

// Fetch location suggestions
async function fetchLocationSuggestions(query, inputId) {
    if (!query || query.length < 2) {
        hideLocationAutocomplete(inputId);
        return;
    }
    
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1`,
            {
                headers: {
                    'User-Agent': 'FindITFast Lost and Found App'
                }
            }
        );
        
        const data = await response.json();
        displayLocationSuggestions(data, inputId);
    } catch (error) {
        console.error('Error fetching location suggestions:', error);
        hideLocationAutocomplete(inputId);
    }
}

// Display location suggestions
function displayLocationSuggestions(suggestions, inputId) {
    if (!suggestions || suggestions.length === 0) {
        hideLocationAutocomplete(inputId);
        return;
    }
    
    // Check if map is visible
    const mapId = inputId === 'location' ? 'location-map' : 'edit-location-map';
    const mapElement = document.getElementById(mapId);
    const isMapVisible = mapElement && mapElement.style.display !== 'none';
    
    // Choose which autocomplete div to use
    let autocompleteDiv;
    if (isMapVisible) {
        // Show on top of map
        autocompleteDiv = document.getElementById(inputId === 'location' ? 'location-autocomplete-map' : 'edit-location-autocomplete-map');
        // Hide the regular autocomplete
        const regularAutocomplete = document.getElementById(inputId === 'location' ? 'location-autocomplete' : 'edit-location-autocomplete');
        if (regularAutocomplete) regularAutocomplete.classList.add('hidden');
    } else {
        // Show below input field
        autocompleteDiv = document.getElementById(inputId === 'location' ? 'location-autocomplete' : 'edit-location-autocomplete');
        // Hide the map autocomplete
        const mapAutocomplete = document.getElementById(inputId === 'location' ? 'location-autocomplete-map' : 'edit-location-autocomplete-map');
        if (mapAutocomplete) mapAutocomplete.classList.add('hidden');
    }
    
    if (!autocompleteDiv) return;
    
    // Add header
    const header = `
        <div class="px-4 py-2 bg-pink-50 border-b border-pink-200 sticky top-0">
            <div class="flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-pink-600"></i>
                <span class="text-xs font-semibold text-pink-900">Suggested Locations</span>
                <span class="text-xs text-pink-600 ml-auto">${suggestions.length} result${suggestions.length !== 1 ? 's' : ''}</span>
            </div>
        </div>
    `;
    
    autocompleteDiv.innerHTML = header + suggestions.map((suggestion, index) => {
        const displayName = suggestion.display_name;
        const lat = suggestion.lat;
        const lon = suggestion.lon;
        
        // Format address nicely
        let formattedAddress = '';
        if (suggestion.address) {
            const addr = suggestion.address;
            const parts = [];
            
            if (addr.house_number) parts.push(addr.house_number);
            if (addr.road) parts.push(addr.road);
            if (addr.neighbourhood || addr.suburb) parts.push(addr.neighbourhood || addr.suburb);
            if (addr.city || addr.town || addr.village) parts.push(addr.city || addr.town || addr.village);
            if (addr.state) parts.push(addr.state);
            if (addr.country) parts.push(addr.country);
            
            formattedAddress = parts.join(', ');
        }
        
        const primaryText = formattedAddress || displayName;
        const secondaryText = formattedAddress ? displayName : '';
        
        return `
            <div class="location-suggestion px-4 py-3 hover:bg-pink-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors" 
                 data-index="${index}"
                 data-lat="${lat}"
                 data-lon="${lon}"
                 data-name="${displayName.replace(/"/g, '&quot;')}"
                 onclick="selectLocationSuggestion('${inputId}', '${lat}', '${lon}', '${displayName.replace(/'/g, "\\'")}')"
                 onmouseenter="highlightLocationSuggestion('${inputId}', ${index})">
                <div class="flex items-start gap-3">
                    <div class="shrink-0 mt-0.5">
                        <i class="fas fa-map-marker-alt text-pink-500 text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-900 leading-tight">${primaryText}</div>
                        ${secondaryText ? `
                            <div class="text-xs text-gray-500 mt-1 leading-tight truncate">${secondaryText}</div>
                        ` : ''}
                    </div>
                    <div class="shrink-0 mt-0.5">
                        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    autocompleteDiv.classList.remove('hidden');
    selectedSuggestionIndex = -1;
}

// Hide location autocomplete
function hideLocationAutocomplete(inputId) {
    const regularAutocompleteId = inputId === 'location' ? 'location-autocomplete' : 'edit-location-autocomplete';
    const mapAutocompleteId = inputId === 'location' ? 'location-autocomplete-map' : 'edit-location-autocomplete-map';
    
    const regularAutocomplete = document.getElementById(regularAutocompleteId);
    const mapAutocomplete = document.getElementById(mapAutocompleteId);
    
    if (regularAutocomplete) {
        regularAutocomplete.classList.add('hidden');
    }
    if (mapAutocomplete) {
        mapAutocomplete.classList.add('hidden');
    }
    selectedSuggestionIndex = -1;
}

// Select location suggestion
function selectLocationSuggestion(inputId, lat, lon, name) {
    const locationInput = document.getElementById(inputId);
    const latInput = document.getElementById(`${inputId}-lat`);
    const lonInput = document.getElementById(`${inputId}-lon`);
    
    isSettingLocationProgrammatically = true;
    
    if (locationInput) {
        locationInput.value = name;
        if (locationAutocompleteTimeout[inputId]) {
            clearTimeout(locationAutocompleteTimeout[inputId]);
            delete locationAutocompleteTimeout[inputId];
        }
    }
    
    setTimeout(() => {
        isSettingLocationProgrammatically = false;
    }, 100);
    
    if (latInput) latInput.value = lat;
    if (lonInput) lonInput.value = lon;
    
    hideLocationAutocomplete(inputId);
    
    const mapId = inputId === 'location' ? 'location-map' : 'edit-location-map';
    const mapElement = document.getElementById(mapId);
    const latNum = parseFloat(lat);
    const lonNum = parseFloat(lon);
    
    if (mapElement && (mapElement.style.display === 'none' || !mapElement.style.display)) {
        toggleLocationMap(inputId);
    }
    
    setTimeout(() => {
        pinLocationOnMap(mapId, inputId, latNum, lonNum);
    }, 300);
}

// Highlight location suggestion
function highlightLocationSuggestion(inputId, index) {
    selectedSuggestionIndex = index;
    const mapId = inputId === 'location' ? 'location-map' : 'edit-location-map';
    const mapElement = document.getElementById(mapId);
    const isMapVisible = mapElement && mapElement.style.display !== 'none';
    
    const autocompleteDiv = isMapVisible 
        ? document.getElementById(inputId === 'location' ? 'location-autocomplete-map' : 'edit-location-autocomplete-map')
        : document.getElementById(inputId === 'location' ? 'location-autocomplete' : 'edit-location-autocomplete');
    
    if (autocompleteDiv) {
        const suggestions = autocompleteDiv.querySelectorAll('.location-suggestion');
        suggestions.forEach((suggestion, i) => {
            if (i === index) {
                suggestion.classList.add('bg-pink-100');
            } else {
                suggestion.classList.remove('bg-pink-100');
            }
        });
    }
}

// Handle keyboard navigation for location autocomplete
function handleLocationAutocompleteKeydown(event, inputId) {
    const mapId = inputId === 'location' ? 'location-map' : 'edit-location-map';
    const mapElement = document.getElementById(mapId);
    const isMapVisible = mapElement && mapElement.style.display !== 'none';
    
    const autocompleteDiv = isMapVisible 
        ? document.getElementById(inputId === 'location' ? 'location-autocomplete-map' : 'edit-location-autocomplete-map')
        : document.getElementById(inputId === 'location' ? 'location-autocomplete' : 'edit-location-autocomplete');
    
    if (!autocompleteDiv || autocompleteDiv.classList.contains('hidden')) {
        return;
    }
    
    const suggestions = autocompleteDiv.querySelectorAll('.location-suggestion');
    if (suggestions.length === 0) return;
    
    if (event.key === 'ArrowDown') {
        event.preventDefault();
        selectedSuggestionIndex = (selectedSuggestionIndex + 1) % suggestions.length;
        highlightLocationSuggestion(inputId, selectedSuggestionIndex);
        suggestions[selectedSuggestionIndex].scrollIntoView({ block: 'nearest' });
    } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        selectedSuggestionIndex = selectedSuggestionIndex <= 0 ? suggestions.length - 1 : selectedSuggestionIndex - 1;
        highlightLocationSuggestion(inputId, selectedSuggestionIndex);
        suggestions[selectedSuggestionIndex].scrollIntoView({ block: 'nearest' });
    } else if (event.key === 'Enter' && selectedSuggestionIndex >= 0) {
        event.preventDefault();
        const selected = suggestions[selectedSuggestionIndex];
        const lat = selected.dataset.lat;
        const lon = selected.dataset.lon;
        const name = selected.dataset.name;
        selectLocationSuggestion(inputId, lat, lon, name);
    } else if (event.key === 'Escape') {
        hideLocationAutocomplete(inputId);
    }
}

// Initialize location input with autocomplete
const locationInput = document.getElementById('location');
if (locationInput) {
    let geocodeTimeout;
    
    locationInput.addEventListener('input', function() {
        if (isSettingLocationProgrammatically) {
            return;
        }
        
        const address = this.value.trim();
        
        clearTimeout(geocodeTimeout);
        
        if (address.length >= 2) {
            geocodeTimeout = setTimeout(() => {
                fetchLocationSuggestions(address, 'location');
            }, 300);
        } else {
            hideLocationAutocomplete('location');
        }
    });
    
    locationInput.addEventListener('keydown', function(e) {
        handleLocationAutocompleteKeydown(e, 'location');
    });
    
    // Hide autocomplete when clicking outside
    document.addEventListener('click', function(e) {
        const autocompleteDiv = document.getElementById('location-autocomplete');
        const autocompleteMapDiv = document.getElementById('location-autocomplete-map');
        if (!locationInput.contains(e.target) && 
            (!autocompleteDiv || !autocompleteDiv.contains(e.target)) &&
            (!autocompleteMapDiv || !autocompleteMapDiv.contains(e.target))) {
            hideLocationAutocomplete('location');
        }
    });
}

</script>
</body>
</html>
