@extends('layouts.admin')

@section('title', 'Claimed')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Claimed</h2>
                <p class="text-gray-600">View all items that have been claimed by users</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <div class="text-2xl font-bold text-green-600">{{ count($formattedItems) }}</div>
                    <div class="text-sm text-gray-500">Claimed Items</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ count($formattedItems) }}</h3>
                    <p class="text-sm text-gray-500">Total Claimed</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-search text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ collect($formattedItems)->where('item_type', 'lost')->count() }}</h3>
                    <p class="text-sm text-gray-500">Lost Items Claimed</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-hand-holding text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ collect($formattedItems)->where('item_type', 'found')->count() }}</h3>
                    <p class="text-sm text-gray-500">Found Items Claimed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Claimed Items List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Claimed Items</h3>
            <p class="text-sm text-gray-500 mt-1">Items that have been successfully claimed by users</p>
        </div>

        <div class="p-6">
            @if(count($formattedItems) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($formattedItems as $item)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <!-- Item Header -->
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $item['item_type'] === 'lost' ? 'bg-red-100' : 'bg-green-100' }}">
                                            <i class="fas {{ $item['item_type'] === 'lost' ? 'fa-search text-red-600' : 'fa-hand-holding text-green-600' }}"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $item['item_type'] === 'lost' ? 'Lost Item' : 'Found Item' }}</h3>
                                            <p class="text-sm text-gray-500">Claimed {{ $item['claimed_at'] ? \Carbon\Carbon::parse($item['claimed_at'])->format('M d, Y') : 'Unknown' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end space-y-2">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $item['item_type'] === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $item['item_type'] === 'lost' ? 'Lost' : 'Found' }}
                                        </span>
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Claimed
                                        </span>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <p class="text-gray-700 mb-2"><strong>Description:</strong> {{ $item['description'] ?: 'No description provided' }}</p>
                                    <p class="text-gray-700 mb-2"><strong>Location:</strong> {{ $item['location'] ?: 'No location specified' }}</p>
                                    @if($item['tags'] && count($item['tags']) > 0)
                                        <div class="flex flex-wrap gap-2 mb-2">
                                            <strong class="text-gray-700">Tags:</strong>
                                            @foreach($item['tags'] as $tag)
                                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <!-- Claim Information -->
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <i class="fas fa-user-check text-green-600"></i>
                                        <span class="text-sm font-medium text-green-800">Claimed by:</span>
                                    </div>
                                    <p class="text-sm text-green-700">{{ $item['claimed_by_name'] }}</p>
                                    <p class="text-xs text-green-600">{{ $item['claimed_by_email'] }}</p>
                                </div>

                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    Originally posted {{ \Carbon\Carbon::parse($item['created_at'])->format('M d, Y') }}
                                </div>
                            </div>

                            <!-- Images Carousel -->
                            <div class="p-6">
                                <div class="relative">
                                    <div class="carousel-container overflow-hidden rounded-lg">
                                        <div class="carousel-track flex transition-transform duration-300 ease-in-out" id="carousel-{{ $item['upload_id'] }}">
                                            @foreach($item['images'] as $index => $image)
                                                <div class="carousel-slide flex-shrink-0 w-full">
                                                    <div class="relative group">
                                                        <img src="{{ $image['url'] ?? $image['path'] }}" alt="{{ $image['original_name'] }}" class="w-full h-48 object-cover rounded-lg border border-gray-200 bg-gray-100" onerror="this.onerror=null; this.src='{{ asset('images/report-found-item-placeholder.svg') }}';">
                                                        <div class="absolute inset-0 bg-transparent group-hover:bg-black/30 transition-all duration-200 rounded-lg flex items-center justify-center">
                                                            <button onclick="viewImage('{{ $image['url'] ?? $image['path'] }}')" class="opacity-0 group-hover:opacity-100 bg-white text-gray-800 px-3 py-1 rounded-lg text-sm font-medium transition-all duration-200">
                                                                <i class="fas fa-eye mr-1"></i>
                                                                View
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    @if(count($item['images']) > 1)
                                        <!-- Carousel Navigation -->
                                        <div class="flex items-center justify-between mt-4">
                                            <button onclick="previousSlide('{{ $item['upload_id'] }}')" class="flex items-center justify-center w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
                                                <i class="fas fa-chevron-left text-gray-600"></i>
                                            </button>

                                            <div class="flex items-center space-x-2">
                                                <div class="flex space-x-1">
                                                    @foreach($item['images'] as $index => $image)
                                                        <button onclick="goToSlide('{{ $item['upload_id'] }}', {{ $index }})"
                                                                class="carousel-dot w-2 h-2 rounded-full bg-gray-300 transition-colors"
                                                                id="dot-{{ $item['upload_id'] }}-{{ $index }}"></button>
                                                    @endforeach
                                                </div>
                                                <span class="carousel-counter text-sm text-gray-500 ml-2" id="counter-{{ $item['upload_id'] }}">1 / {{ count($item['images']) }}</span>
                                            </div>

                                            <button onclick="nextSlide('{{ $item['upload_id'] }}')" class="flex items-center justify-center w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
                                                <i class="fas fa-chevron-right text-gray-600"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="p-6 border-t border-gray-200 bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <button onclick="viewItemDetails('{{ $item['upload_id'] }}')" class="px-4 py-2 bg-blue-100 text-blue-800 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        View Details
                                    </button>
                                    <div class="text-sm text-gray-500">
                                        <i class="fas fa-images mr-1"></i>
                                        {{ count($item['images']) }} image(s)
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Claimed Items</h3>
                    <p class="text-gray-500">No items have been claimed yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="image-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg max-w-4xl max-h-full overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Image Preview</h3>
            <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-4">
            <img id="modal-image" src="" alt="Preview" class="max-w-full max-h-96 object-contain mx-auto">
        </div>
    </div>
</div>

<script>
// Carousel functions
const carouselStates = {};

function initializeCarousel(carouselId, totalSlides) {
    carouselStates[carouselId] = {
        currentSlide: 0,
        totalSlides: totalSlides
    };
    updateCarouselPosition(carouselId);
    updateCarouselDots(carouselId);
    updateCarouselCounter(carouselId);
}

function nextSlide(carouselId) {
    const state = carouselStates[carouselId];
    if (state.currentSlide < state.totalSlides - 1) {
        state.currentSlide++;
        updateCarouselPosition(carouselId);
        updateCarouselDots(carouselId);
        updateCarouselCounter(carouselId);
    }
}

function previousSlide(carouselId) {
    const state = carouselStates[carouselId];
    if (state.currentSlide > 0) {
        state.currentSlide--;
        updateCarouselPosition(carouselId);
        updateCarouselDots(carouselId);
        updateCarouselCounter(carouselId);
    }
}

function goToSlide(carouselId, slideIndex) {
    const state = carouselStates[carouselId];
    state.currentSlide = slideIndex;
    updateCarouselPosition(carouselId);
    updateCarouselDots(carouselId);
    updateCarouselCounter(carouselId);
}

function updateCarouselPosition(carouselId) {
    const state = carouselStates[carouselId];
    const track = document.getElementById(`carousel-${carouselId}`);
    if (track) {
        track.style.transform = `translateX(-${state.currentSlide * 100}%)`;
    }
}

function updateCarouselDots(carouselId) {
    const state = carouselStates[carouselId];
    for (let i = 0; i < state.totalSlides; i++) {
        const dot = document.getElementById(`dot-${carouselId}-${i}`);
        if (dot) {
            dot.className = i === state.currentSlide
                ? 'carousel-dot w-2 h-2 rounded-full bg-purple-600 transition-colors'
                : 'carousel-dot w-2 h-2 rounded-full bg-gray-300 transition-colors';
        }
    }
}

function updateCarouselCounter(carouselId) {
    const state = carouselStates[carouselId];
    const counter = document.getElementById(`counter-${carouselId}`);
    if (counter) {
        counter.textContent = `${state.currentSlide + 1} / ${state.totalSlides}`;
    }
}

// Image modal functions
function viewImage(imagePath) {
    document.getElementById('modal-image').src = imagePath;
    document.getElementById('image-modal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('image-modal').classList.add('hidden');
}

// Item details function
function viewItemDetails(uploadId) {
    // This would show detailed information about the claimed item
    alert('Item details for: ' + uploadId);
}

// Initialize carousels when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all carousels
    @foreach($formattedItems as $item)
        @if(count($item['images']) > 1)
            initializeCarousel('{{ $item['upload_id'] }}', {{ count($item['images']) }});
        @endif
    @endforeach
});
</script>
@endsection
