@extends('layouts.admin')

@section('title', 'Claimed - FindITFast Admin')

@section('content')
@php
    $totalClaimed = count($formattedItems);
    $lostClaimed = collect($formattedItems)->where('item_type', 'lost')->count();
    $foundClaimed = collect($formattedItems)->where('item_type', 'found')->count();
@endphp
<div class="admin-page">
    @include('admin.partials.page-header', [
        'title' => 'Claimed Items',
        'description' => 'View all items that have been successfully claimed by users.',
    ])

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @include('admin.partials.stat-card', [
            'label' => 'Total Claimed',
            'value' => number_format($totalClaimed),
            'icon' => 'fa-check-circle',
            'iconBg' => 'bg-emerald-100',
            'iconColor' => 'text-emerald-600',
        ])
        @include('admin.partials.stat-card', [
            'label' => 'Lost Items Claimed',
            'value' => number_format($lostClaimed),
            'icon' => 'fa-search',
            'iconBg' => 'bg-red-100',
            'iconColor' => 'text-red-600',
        ])
        @include('admin.partials.stat-card', [
            'label' => 'Found Items Claimed',
            'value' => number_format($foundClaimed),
            'icon' => 'fa-hand-holding',
            'iconBg' => 'bg-blue-100',
            'iconColor' => 'text-blue-600',
        ])
    </div>

    <div class="admin-card">
        <div class="admin-toolbar">
            <h3 class="admin-panel-title">Claimed Items</h3>
            <p class="admin-panel-subtitle">Items that have been successfully claimed by users</p>
        </div>

        <div class="admin-card-body">
            @if($totalClaimed > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($formattedItems as $item)
                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                            <div class="p-5 border-b border-gray-100">
                                <div class="flex items-start justify-between gap-3 mb-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $item['item_type'] === 'lost' ? 'bg-red-100' : 'bg-emerald-100' }}">
                                            <i class="fas {{ $item['item_type'] === 'lost' ? 'fa-search text-red-600' : 'fa-hand-holding text-emerald-600' }}"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <h3 class="text-sm font-semibold text-gray-900">{{ $item['item_type'] === 'lost' ? 'Lost Item' : 'Found Item' }}</h3>
                                            <p class="text-xs text-gray-500 mt-0.5">Claimed {{ $item['claimed_at'] ? \Carbon\Carbon::parse($item['claimed_at'])->format('M d, Y') : 'Unknown' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-1.5 shrink-0">
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium {{ $item['item_type'] === 'lost' ? 'bg-red-50 text-red-700 ring-1 ring-red-600/10' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10' }}">
                                            {{ ucfirst($item['item_type']) }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10">
                                            <i class="fas fa-check-circle text-[10px] mr-1"></i>Claimed
                                        </span>
                                    </div>
                                </div>

                                <p class="text-sm text-gray-700 mb-1"><span class="font-medium text-gray-900">Description:</span> {{ $item['description'] ?: 'No description provided' }}</p>
                                <p class="text-sm text-gray-700 mb-3"><span class="font-medium text-gray-900">Location:</span> {{ $item['location'] ?: 'No location specified' }}</p>
                                @if($item['tags'] && count($item['tags']) > 0)
                                    <div class="flex flex-wrap gap-1.5 mb-3">
                                        @foreach($item['tags'] as $tag)
                                            <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded-md text-xs">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 mb-3">
                                    <p class="text-xs font-semibold text-emerald-800 mb-1"><i class="fas fa-user-check mr-1"></i>Claimed by</p>
                                    <p class="text-sm text-emerald-900 font-medium">{{ $item['claimed_by_name'] }}</p>
                                    <p class="text-xs text-emerald-700">{{ $item['claimed_by_email'] }}</p>
                                </div>

                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    Originally posted {{ \Carbon\Carbon::parse($item['created_at'])->format('M d, Y') }}
                                </p>
                            </div>

                            <div class="p-5">
                                <div class="relative">
                                    <div class="carousel-container overflow-hidden rounded-lg">
                                        <div class="carousel-track flex transition-transform duration-300 ease-in-out" id="carousel-{{ $item['upload_id'] }}">
                                            @foreach($item['images'] as $index => $image)
                                                <div class="carousel-slide flex-shrink-0 w-full">
                                                    <div class="relative group">
                                                        <img src="{{ $image['url'] ?? $image['path'] }}" alt="{{ $image['original_name'] }}" class="w-full h-48 object-cover rounded-lg border border-gray-200 bg-gray-100" onerror="this.onerror=null; this.src='{{ asset('images/report-found-item-placeholder.svg') }}';">
                                                        <div class="absolute inset-0 bg-transparent group-hover:bg-black/30 transition-all duration-200 rounded-lg flex items-center justify-center">
                                                            <button type="button" onclick="viewImage('{{ $image['url'] ?? $image['path'] }}')" class="opacity-0 group-hover:opacity-100 bg-white text-gray-800 px-3 py-1 rounded-lg text-sm font-medium transition-all duration-200">
                                                                <i class="fas fa-eye mr-1"></i>View
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    @if(count($item['images']) > 1)
                                        <div class="flex items-center justify-between mt-4">
                                            <button type="button" onclick="previousSlide('{{ $item['upload_id'] }}')" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors">
                                                <i class="fas fa-chevron-left text-sm"></i>
                                            </button>
                                            <div class="flex items-center gap-2">
                                                <div class="flex gap-1">
                                                    @foreach($item['images'] as $index => $image)
                                                        <button type="button" onclick="goToSlide('{{ $item['upload_id'] }}', {{ $index }})"
                                                                class="carousel-dot w-2 h-2 rounded-full bg-gray-300 transition-colors"
                                                                id="dot-{{ $item['upload_id'] }}-{{ $index }}"></button>
                                                    @endforeach
                                                </div>
                                                <span class="carousel-counter text-sm text-gray-500" id="counter-{{ $item['upload_id'] }}">1 / {{ count($item['images']) }}</span>
                                            </div>
                                            <button type="button" onclick="nextSlide('{{ $item['upload_id'] }}')" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors">
                                                <i class="fas fa-chevron-right text-sm"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/80 flex items-center justify-between">
                                <button type="button" onclick="viewItemDetails('{{ $item['upload_id'] }}')" class="admin-btn-secondary text-xs py-2">
                                    <i class="fas fa-info-circle"></i>
                                    View Details
                                </button>
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-images mr-1"></i>{{ count($item['images']) }} image(s)
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                        <i class="fas fa-check-circle text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-base font-semibold text-gray-900">No claimed items yet</p>
                    <p class="text-sm text-gray-500 mt-1">Successfully claimed items will appear here.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="image-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeImageModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-xl max-w-4xl w-full overflow-hidden border border-gray-200">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Image Preview</h3>
                <button type="button" onclick="closeImageModal()" class="admin-icon-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <img id="modal-image" src="" alt="Preview" class="max-w-full max-h-[70vh] object-contain mx-auto rounded-lg">
            </div>
        </div>
    </div>
</div>

<script>
const carouselStates = {};

function initializeCarousel(carouselId, totalSlides) {
    carouselStates[carouselId] = { currentSlide: 0, totalSlides: totalSlides };
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
    if (track) track.style.transform = `translateX(-${state.currentSlide * 100}%)`;
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
    if (counter) counter.textContent = `${state.currentSlide + 1} / ${state.totalSlides}`;
}

function viewImage(imagePath) {
    document.getElementById('modal-image').src = imagePath;
    document.getElementById('image-modal').classList.remove('hidden');
}

function closeImageModal() {
    document.getElementById('image-modal').classList.add('hidden');
}

function viewItemDetails(uploadId) {
    alert('Item details for: ' + uploadId);
}

document.addEventListener('DOMContentLoaded', function() {
    @foreach($formattedItems as $item)
        @if(count($item['images']) > 1)
            initializeCarousel('{{ $item['upload_id'] }}', {{ count($item['images']) }});
        @endif
    @endforeach
});
</script>
@endsection
