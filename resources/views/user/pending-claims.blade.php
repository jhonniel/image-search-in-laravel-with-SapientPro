@extends('layouts.user')

@section('title', 'Pending Claims - FindITFast')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl shadow-md p-6 border border-purple-100">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Pending Claims</h2>
            <p class="text-gray-600 text-lg">Review and verify claims on your items</p>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="notification-container"></div>

    @if(count($pendingClaims) === 0)
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-12 text-center">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-inbox text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Pending Claims</h3>
            <p class="text-gray-600">You don't have any pending claims to review at the moment.</p>
        </div>
    @else
        <!-- Pending Claims List -->
        <div class="grid grid-cols-1 gap-6">
            @foreach($pendingClaims as $claim)
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex flex-col lg:flex-row gap-6">
                        <!-- Images -->
                        <div class="lg:w-1/3">
                            <h3 class="text-sm font-medium text-gray-500 mb-3">Item Images</h3>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach($claim['images'] as $image)
                                <div class="aspect-square rounded-lg overflow-hidden bg-gray-100">
                                    <img src="{{ $image['path'] }}" alt="{{ $image['original_name'] }}" class="w-full h-full object-cover">
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Item Details -->
                        <div class="lg:w-2/3 space-y-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $claim['description'] }}</h3>
                                <div class="flex flex-wrap gap-3 text-sm text-gray-600">
                                    <span class="flex items-center">
                                        <i class="fas fa-map-marker-alt mr-1.5 text-purple-500"></i>
                                        {{ $claim['location'] ?? 'Location not specified' }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-tag mr-1.5 text-purple-500"></i>
                                        {{ ucfirst($claim['status']) }} Item
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-clock mr-1.5 text-purple-500"></i>
                                        Claimed {{ \Carbon\Carbon::parse($claim['claimed_at'])->diffForHumans() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Claimer Info -->
                            @if($claim['claimer'])
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Claimed By:</h4>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                        <span class="text-purple-600 font-semibold">{{ substr($claim['claimer']['name'], 0, 2) }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium text-gray-900">{{ $claim['claimer']['name'] }}</p>
                                            @if($claim['claimer']['is_verified'])
                                            <img src="{{ asset('images/icons/verify.png') }}" alt="Verified" class="w-4 h-4">
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $claim['claimer']['email'] }}</p>
                                    </div>
                                    <a href="{{ route('user.chat', ['userId' => $claim['claimer']['id']]) }}" 
                                       class="px-4 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-colors text-sm font-medium">
                                        <i class="fas fa-comments mr-1"></i>
                                        Message
                                    </a>
                                </div>
                            </div>
                            @endif

                            <!-- Tags -->
                            @if(!empty($claim['tags']) && is_array($claim['tags']))
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Tags:</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($claim['tags'] as $tag)
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Actions -->
                            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                                <button onclick="verifyClaim('{{ $claim['upload_id'] }}')" 
                                        class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center justify-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Verify Claim
                                </button>
                                <button onclick="rejectClaim('{{ $claim['upload_id'] }}')" 
                                        class="flex-1 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium flex items-center justify-center">
                                    <i class="fas fa-times-circle mr-2"></i>
                                    Reject Claim
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
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

    fetch(`/user/claims/${uploadId}/verify`, {
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

    fetch(`/user/claims/${uploadId}/reject`, {
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

