@extends('layouts.user')

@section('title', 'Pending Claims - FindITFast')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl shadow-md p-6 border border-purple-100">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Claims Management</h2>
            <p class="text-gray-600 text-lg">Review pending claims and view verified claims on your items</p>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="notification-container"></div>

    <!-- Pending Claims Section -->
    <div class="mb-8">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Pending Claims</h3>
        @if(count($pendingClaims) === 0)
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-gray-400 text-4xl"></i>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">No Pending Claims</h4>
                <p class="text-gray-600">You don't have any pending claims to review at the moment.</p>
            </div>
        @else
            <!-- Pending Claims Tiles -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($pendingClaims as $claim)
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-200 flex flex-col">
                <!-- Image Carousel -->
                <div class="relative">
                    @if(count($claim['images']) > 0)
                    <div class="aspect-video bg-gray-100 overflow-hidden">
                        <img src="{{ $claim['images'][0]['path'] }}" alt="{{ $claim['images'][0]['original_name'] }}" class="w-full h-full object-cover">
                    </div>
                    @if(count($claim['images']) > 1)
                    <div class="absolute top-2 right-2 bg-black bg-opacity-50 text-white px-2 py-1 rounded-full text-xs font-medium">
                        <i class="fas fa-images mr-1"></i>{{ count($claim['images']) }}
                    </div>
                    @endif
                    @else
                    <div class="aspect-video bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-image text-gray-400 text-4xl"></i>
                    </div>
                    @endif
                    <!-- Status Badge -->
                    <div class="absolute top-2 left-2">
                        <span class="px-2 py-1 bg-yellow-500 text-white rounded-full text-xs font-medium">
                            <i class="fas fa-clock mr-1"></i>Pending
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-5 flex-1 flex flex-col">
                    <!-- Item Info -->
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">{{ $claim['description'] }}</h3>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-map-marker-alt mr-2 text-purple-500 w-4"></i>
                                <span class="truncate">{{ $claim['location'] ?? 'Location not specified' }}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-tag mr-2 text-purple-500 w-4"></i>
                                <span>{{ ucfirst($claim['status']) }} Item</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-clock mr-2 text-purple-500 w-4"></i>
                                <span>Claimed {{ \Carbon\Carbon::parse($claim['claimed_at'])->diffForHumans() }}</span>
                            </div>
                        </div>

                        <!-- Claimer Info -->
                        @if($claim['claimer'])
                        <div class="bg-gray-50 rounded-lg p-3 mb-4 border border-gray-200">
                            <h4 class="text-xs font-medium text-gray-500 mb-2">Claimed By:</h4>
                            <div class="flex items-center gap-2">
                                @if(!empty($claim['claimer']['profile_picture']))
                                    <img src="{{ $claim['claimer']['profile_picture'] }}" alt="{{ $claim['claimer']['name'] }}" class="w-8 h-8 rounded-full object-cover border border-purple-100 shrink-0">
                                @else
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center shrink-0">
                                        <span class="text-purple-600 font-semibold text-xs">{{ strtoupper(substr($claim['claimer']['name'], 0, 2)) }}</span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1">
                                        <p class="font-medium text-gray-900 text-sm truncate">{{ $claim['claimer']['name'] }}</p>
                                        @if($claim['claimer']['is_verified'])
                                        <img src="{{ asset('images/icons/verify.png') }}" alt="Verified" class="w-3 h-3 shrink-0">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Tags -->
                        @if(!empty($claim['tags']) && is_array($claim['tags']) && count($claim['tags']) > 0)
                        <div class="mb-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($claim['tags'], 0, 3) as $tag)
                                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">{{ $tag }}</span>
                                @endforeach
                                @if(count($claim['tags']) > 3)
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">+{{ count($claim['tags']) - 3 }}</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="space-y-2 pt-4 border-t border-gray-200">
                        <a href="{{ route('chat', ['userId' => $claim['claimer']['id']]) }}" 
                           class="block w-full px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-colors text-sm font-medium text-center">
                            <i class="fas fa-comments mr-1"></i>
                            Message Claimer
                        </a>
                        <div class="grid grid-cols-2 gap-2">
                            <button onclick="verifyClaim('{{ $claim['upload_id'] }}')" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium text-sm flex items-center justify-center">
                                <i class="fas fa-check-circle mr-1"></i>
                                Verify
                            </button>
                            <button onclick="rejectClaim('{{ $claim['upload_id'] }}')" 
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm flex items-center justify-center">
                                <i class="fas fa-times-circle mr-1"></i>
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Verified/Claimed Items Section -->
    <div>
        <h3 class="text-xl font-semibold text-gray-900 mb-4">Verified Claims</h3>
        @if(count($verifiedClaims) === 0)
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-gray-400 text-4xl"></i>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">No Verified Claims</h4>
                <p class="text-gray-600">You haven't verified any claims yet. Verified claims will appear here.</p>
            </div>
        @else
            <!-- Verified Claims Tiles (View Only) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($verifiedClaims as $claim)
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-200 flex flex-col opacity-90">
                    <!-- Image Carousel -->
                    <div class="relative">
                        @if(count($claim['images']) > 0)
                        <div class="aspect-video bg-gray-100 overflow-hidden">
                            <img src="{{ $claim['images'][0]['path'] }}" alt="{{ $claim['images'][0]['original_name'] }}" class="w-full h-full object-cover">
                        </div>
                        @if(count($claim['images']) > 1)
                        <div class="absolute top-2 right-2 bg-black bg-opacity-50 text-white px-2 py-1 rounded-full text-xs font-medium">
                            <i class="fas fa-images mr-1"></i>{{ count($claim['images']) }}
                        </div>
                        @endif
                        @else
                        <div class="aspect-video bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-4xl"></i>
                        </div>
                        @endif
                        <!-- Status Badge -->
                        <div class="absolute top-2 left-2">
                            <span class="px-2 py-1 bg-green-500 text-white rounded-full text-xs font-medium">
                                <i class="fas fa-check-circle mr-1"></i>Verified
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-5 flex-1 flex flex-col">
                        <!-- Item Info -->
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">{{ $claim['description'] }}</h3>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-map-marker-alt mr-2 text-purple-500 w-4"></i>
                                    <span class="truncate">{{ $claim['location'] ?? 'Location not specified' }}</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-tag mr-2 text-purple-500 w-4"></i>
                                    <span>{{ ucfirst($claim['status']) }} Item</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-check-circle mr-2 text-green-500 w-4"></i>
                                    <span>Verified {{ \Carbon\Carbon::parse($claim['claim_verified_at'] ?? $claim['claimed_at'])->diffForHumans() }}</span>
                                </div>
                            </div>

                            <!-- Claimer Info -->
                            @if($claim['claimer'])
                            <div class="bg-gray-50 rounded-lg p-3 mb-4 border border-gray-200">
                                <h4 class="text-xs font-medium text-gray-500 mb-2">Claimed By:</h4>
                                <div class="flex items-center gap-2">
                                    @if(!empty($claim['claimer']['profile_picture']))
                                        <img src="{{ $claim['claimer']['profile_picture'] }}" alt="{{ $claim['claimer']['name'] }}" class="w-8 h-8 rounded-full object-cover border border-purple-100 shrink-0">
                                    @else
                                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center shrink-0">
                                            <span class="text-purple-600 font-semibold text-xs">{{ strtoupper(substr($claim['claimer']['name'], 0, 2)) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1">
                                            <p class="font-medium text-gray-900 text-sm truncate">{{ $claim['claimer']['name'] }}</p>
                                            @if($claim['claimer']['is_verified'])
                                            <img src="{{ asset('images/icons/verify.png') }}" alt="Verified" class="w-3 h-3 shrink-0">
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Tags -->
                            @if(!empty($claim['tags']) && is_array($claim['tags']) && count($claim['tags']) > 0)
                            <div class="mb-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($claim['tags'], 0, 3) as $tag)
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">{{ $tag }}</span>
                                    @endforeach
                                    @if(count($claim['tags']) > 3)
                                    <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">+{{ count($claim['tags']) - 3 }}</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- View Only Notice -->
                        <div class="pt-4 border-t border-gray-200">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                                <p class="text-blue-700 text-xs font-medium">
                                    <i class="fas fa-lock mr-1"></i>
                                    This item has been verified and cannot be modified
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
function showNotification(message, type = 'success') {
    const container = document.getElementById('notification-container');
    const bgColor = type === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
    const textColor = type === 'success' ? 'text-green-700' : 'text-red-700';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    const iconColor = type === 'success' ? 'text-green-500' : 'text-red-500';

    container.innerHTML = `
        <div class="${bgColor} border rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas ${icon} ${iconColor} mr-3"></i>
                <p class="${textColor} font-medium">${message}</p>
            </div>
        </div>
    `;

    setTimeout(() => {
        container.innerHTML = '';
    }, 5000);
}

function verifyClaim(uploadId) {
    if (!confirm('Are you sure this item belongs to the claimer? This action will verify the claim.')) {
        return;
    }

    fetch(`/claims/${uploadId}/verify`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.error || 'Failed to verify claim', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while verifying the claim', 'error');
    });
}

function rejectClaim(uploadId) {
    if (!confirm('Are you sure this item does NOT belong to the claimer? This will reject the claim and make the item available for others.')) {
        return;
    }

    fetch(`/claims/${uploadId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.error || 'Failed to reject claim', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while rejecting the claim', 'error');
    });
}
</script>
@endsection

