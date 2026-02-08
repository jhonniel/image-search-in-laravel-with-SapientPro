<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Details - FindITFast</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Leaflet CSS for map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white shadow-sm">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <a href="{{ route('welcome') }}" class="text-2xl font-bold">
                    <span class="text-purple-primary">FindIT</span>
                    <span class="text-pink-primary">Fast</span>
                </a>
            </div>
            <nav class="flex items-center space-x-4">
                @auth
                    <a href="/dashboard" class="px-4 py-2 text-purple-primary hover:bg-purple-50 rounded-lg transition-colors">
                        Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 border-2 border-purple-primary text-purple-primary rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="/login" class="px-4 py-2 border-2 border-purple-primary text-purple-primary rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors">
                        Login
                    </a>
                    <a href="/register" class="px-4 py-2 bg-purple-primary text-white rounded-lg font-medium hover:bg-purple-600 transition-colors">
                        Sign Up
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Item Details -->
    <div class="container mx-auto px-6 py-12 max-w-6xl">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Item Header -->
            <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-8 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="px-4 py-2 bg-white/20 rounded-full text-sm font-semibold uppercase">
                            {{ ucfirst($item['item_type']) }}
                        </span>
                        @if($item['is_claimed'])
                        <span class="ml-2 px-4 py-2 bg-green-500/80 rounded-full text-sm font-semibold">
                            Claimed
                        </span>
                        @endif
                    </div>
                    <div class="text-sm opacity-90">
                        <i class="far fa-clock mr-2"></i>
                        {{ $item['created_at']->diffForHumans() }}
                    </div>
                </div>
                <h1 class="text-3xl font-bold mb-2">Item Details</h1>
                <div class="flex items-center gap-2">
                    <p class="text-lg opacity-90">Posted by {{ $item['uploader_name'] }}</p>
                    @if($item['uploader_verified'] ?? false)
                    <span class="inline-flex items-center justify-center w-5 h-5" title="Verified Profile">
                        <img src="{{ asset('images/icons/verify.png') }}" alt="Verified" class="w-5 h-5">
                    </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-8">
                <!-- Location Map Section (no item images) -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">Location</h2>
                    @if(!empty($item['location']) && $item['location'] !== 'Location not specified')
                        <p class="text-sm text-gray-600 mb-4">
                            <i class="fas fa-map-marker-alt text-pink-primary mr-1"></i>
                            {{ $item['location'] }}
                        </p>
                        <div id="item-location-map"
                             class="w-full rounded-lg border border-gray-200 overflow-hidden bg-gray-100"
                             style="height: 320px;"></div>
                        <p class="mt-2 text-xs text-gray-400">
                            Map location is approximate and based on the address provided.
                        </p>
                    @else
                        <p class="text-gray-500">Location not specified for this item.</p>
                    @endif
                </div>

                <!-- Details Section -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Item Information</h2>
                    
                    <div class="space-y-6">
                        <!-- Description -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Description</h3>
                            <p class="text-gray-900">{{ $item['description'] ?? 'No description provided' }}</p>
                        </div>

                        <!-- Tags -->
                        @if(!empty($item['tags']))
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Tags</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($item['tags'] as $tag)
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm">
                                    {{ $tag }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Uploader Info -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Posted By</h3>
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-gray-900">{{ $item['uploader_name'] }}</p>
                                @if($item['uploader_verified'] ?? false)
                                <span class="inline-flex items-center justify-center w-5 h-5" title="Verified Profile">
                                    <img src="{{ asset('images/icons/verify.png') }}" alt="Verified" class="w-5 h-5">
                                </span>
                                @endif
                            </div>
                            @if($item['uploader_verified'] ?? false)
                            <p class="text-xs text-blue-600 mt-1 flex items-center">
                                <i class="fas fa-shield-alt mr-1"></i>
                                This profile has been verified by administrators
                            </p>
                            @endif
                        </div>

                        <!-- Posted Date -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Posted Date</h3>
                            <p class="text-gray-900">{{ $item['created_at']->format('F d, Y \a\t h:i A') }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 pt-6 border-t border-gray-200 space-y-3">
                        @auth
                            @if($isOwner)
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <p class="text-blue-800 text-sm">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        This is your item. You can manage it from your dashboard.
                                    </p>
                                </div>
                                <a href="/reported-items" class="block w-full bg-purple-primary text-white text-center px-6 py-3 rounded-lg hover:bg-purple-600 transition-colors font-medium">
                                    <i class="fas fa-tasks mr-2"></i>Manage Item
                                </a>
                            @elseif($canClaim && !$item['is_claimed'])
                                <button onclick="claimItem('{{ $item['upload_id'] }}')" class="w-full bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors font-medium">
                                    <i class="fas fa-hand-holding mr-2"></i>Claim This Item
                                </button>
                                <button onclick="messageOwner('{{ $item['upload_id'] }}', '{{ $uploader->id ?? '' }}')" class="w-full bg-purple-primary text-white px-6 py-3 rounded-lg hover:bg-purple-600 transition-colors font-medium">
                                    <i class="fas fa-comments mr-2"></i>Message Owner
                                </button>
                            @elseif($item['is_claimed'])
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <p class="text-yellow-800 text-sm">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        This item has already been claimed.
                                    </p>
                                </div>
                            @endif
                        @else
                            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                                <p class="text-purple-800 text-sm mb-4">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    You need to create an account to claim this item or message the owner.
                                </p>
                            </div>
                            <a href="{{ route('register', ['item' => $item['upload_id']]) }}" class="block w-full bg-green-500 text-white text-center px-6 py-3 rounded-lg hover:bg-green-600 transition-colors font-medium mb-3">
                                <i class="fas fa-user-plus mr-2"></i>Sign Up to Claim
                            </a>
                            <a href="{{ route('login', ['item' => $item['upload_id']]) }}" class="block w-full bg-purple-primary text-white text-center px-6 py-3 rounded-lg hover:bg-purple-600 transition-colors font-medium">
                                <i class="fas fa-sign-in-alt mr-2"></i>Login to Claim
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS and map initialization -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const locationText = @json($item['location']);
            if (!locationText || locationText === 'Location not specified') {
                return;
            }

            const mapContainer = document.getElementById('item-location-map');
            if (!mapContainer) return;

            // Initialize basic Leaflet map
            const map = L.map('item-location-map').setView([0, 0], 2);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Geocode the location text to coordinates using Nominatim
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(locationText)}&limit=1`, {
                headers: {
                    'User-Agent': 'FindITFast Lost and Found App'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (!data || !data.length) {
                        return;
                    }

                    const lat = parseFloat(data[0].lat);
                    const lon = parseFloat(data[0].lon);

                    if (isNaN(lat) || isNaN(lon)) {
                        return;
                    }

                    map.setView([lat, lon], 15);

                    const marker = L.marker([lat, lon]).addTo(map);
                    marker.bindPopup(`<strong>${locationText}</strong>`).openPopup();
                })
                .catch(error => {
                    console.error('Error geocoding item location:', error);
                });
        });
    </script>

    @auth
    <script>
        function claimItem(uploadId) {
            if (!confirm('Are you sure you want to claim this item? This action cannot be undone.')) {
                return;
            }

            fetch(`/api/items/${uploadId}/claim`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Item claimed successfully! Redirecting to chat...');
                    // Redirect to chat with the item owner
                    if (data.owner_id && data.upload_id) {
                        window.location.href = `/chat?user=${data.owner_id}&item=${data.upload_id}`;
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert(data.error || 'Error claiming item. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error claiming item. Please try again.');
            });
        }

        function messageOwner(uploadId, userId) {
            if (!userId) {
                alert('Unable to find owner information.');
                return;
            }

            // Get item details for context
            const itemContext = {
                upload_id: '{{ $item['upload_id'] }}',
                uploadId: '{{ $item['upload_id'] }}',
                item_type: '{{ $item['item_type'] }}',
                itemType: '{{ $item['item_type'] }}',
                description: `{{ addslashes($item['description'] ?? '') }}`,
                location: `{{ addslashes($item['description'] ?? '') }}`,
                uploader_name: '{{ addslashes($item['uploader_name']) }}',
                uploader_email: '{{ $item['uploader_email'] }}',
                images: @json($item['images']),
                tags: @json($item['tags'] ?? [])
            };

            // Store in sessionStorage
            sessionStorage.setItem('chatItemContext', JSON.stringify(itemContext));

            // Redirect to chat
            window.location.href = `/chat?user=${userId}&item=${uploadId}`;
        }
    </script>
    @endauth
</body>
</html>

