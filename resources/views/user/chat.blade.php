@extends('layouts.user')

@section('content')
<div class="p-6 h-full">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Messages</h1>
        <p class="text-gray-600 mt-2">Chat with other users in the system</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border h-[calc(100vh-200px)] flex">
        <!-- Users List Sidebar -->
        <div class="w-1/3 border-r border-gray-200 flex flex-col">
            <!-- Search -->
            <div class="p-4 border-b border-gray-200">
                <div class="relative">
                    <input type="text" id="user-search" placeholder="Search users..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Users List -->
            <div class="flex-1 overflow-y-auto" id="users-list">
                <!-- Recent Conversations -->
                @if($conversations->count() > 0)
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-sm font-medium text-gray-500 mb-3">Recent Conversations</h3>
                    <div class="space-y-2">
                        @foreach($conversations as $conversation)
                        <div class="user-item flex items-center p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
                             data-user-id="{{ $conversation['user']->id }}">
                            <div class="flex-shrink-0">
                                @if($conversation['user']->profile_picture)
                                    <img src="{{ $conversation['user']->profile_picture }}" alt="{{ $conversation['user']->name }}"
                                         class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-purple-600">
                                            {{ strtoupper(substr($conversation['user']->name, 0, 2)) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $conversation['user']->name }}</p>
                                        @if($conversation['user']->is_verified ?? false)
                                        <span class="inline-flex items-center justify-center w-4 h-4 flex-shrink-0" title="Verified Profile">
                                            <img src="{{ asset('images/icons/verify.png') }}" alt="Verified" class="w-4 h-4">
                                        </span>
                                        @endif
                                    </div>
                                    @if($conversation['unread_count'] > 0)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $conversation['unread_count'] }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 truncate">
                                    {{ $conversation['last_message']->message }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $conversation['last_message']->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- All Users -->
                <div class="p-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-3">All Users</h3>
                    <div class="space-y-2">
                        @foreach($users as $user)
                        <div class="user-item flex items-center p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
                             data-user-id="{{ $user->id }}">
                            <div class="flex-shrink-0">
                                @if($user->profile_picture)
                                    <img src="{{ $user->profile_picture }}" alt="{{ $user->name }}"
                                         class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-purple-600">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                    @if($user->is_verified ?? false)
                                    <span class="inline-flex items-center justify-center w-4 h-4 flex-shrink-0" title="Verified Profile">
                                        <img src="{{ asset('images/icons/verify.png') }}" alt="Verified" class="w-4 h-4">
                                    </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 flex flex-col">
            <!-- Chat Header -->
            <div id="chat-header" class="p-4 border-b border-gray-200 bg-white hidden">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div id="chat-user-avatar" class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                <span id="chat-user-initials" class="text-sm font-medium text-purple-600"></span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="flex items-center gap-2">
                                <h3 id="chat-user-name" class="text-lg font-medium text-gray-900"></h3>
                                <span id="chat-user-verified-badge" class="hidden inline-flex items-center justify-center w-5 h-5" title="Verified Profile">
                                    <img src="{{ asset('images/icons/verify.png') }}" alt="Verified" class="w-5 h-5">
                                </span>
                            </div>
                            <p id="chat-user-status" class="text-sm text-gray-500">Online</p>
                        </div>
                    </div>
                </div>
                <!-- Item Context Info (shown when chatting about an item) -->
                <div id="item-context-info" class="hidden mt-3 pt-3 border-t border-gray-200 bg-purple-50 rounded-lg p-3">
                    <div class="flex items-start space-x-2">
                        <i class="fas fa-info-circle text-purple-500 mt-0.5"></i>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-purple-900" id="item-context-title">About Item</p>
                            <p class="text-xs text-purple-700 mt-1" id="item-context-details">Item details will appear here</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4 hidden">
                <div id="messages-list" class="space-y-4">
                    <!-- Messages will be loaded here -->
                </div>
            </div>

            <!-- Message Input -->
            <div id="message-input-container" class="p-4 border-t border-gray-200 hidden">
                <!-- Item Context Message -->
                <div id="item-context-message" class="mb-4 p-3 bg-purple-50 border border-purple-200 rounded-lg hidden">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-medium text-purple-900">Chatting about this item:</h4>
                        <button onclick="clearItemContext()" class="text-purple-600 hover:text-purple-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="item-context-content" class="text-sm text-purple-700">
                        <!-- Item details will be loaded here -->
                    </div>
                </div>

                <form id="message-form" class="flex items-center space-x-4">
                    <div class="flex-1">
                        <input type="text" id="message-input" placeholder="Type a message..."
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                               maxlength="1000">
                    </div>
                    <button type="submit"
                            class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send
                    </button>
                </form>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="flex-1 flex items-center justify-center">
                <div class="text-center">
                    <i class="fas fa-comments text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Start a Conversation</h3>
                    <p class="text-gray-500">Select a user from the list to start chatting</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentUserId = null;
let messageInterval = null;

// User search functionality
document.getElementById('user-search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const userItems = document.querySelectorAll('.user-item');

    userItems.forEach(item => {
        const userName = item.querySelector('p').textContent.toLowerCase();
        const userEmail = item.querySelector('p:last-child').textContent.toLowerCase();

        if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// User selection
document.addEventListener('click', function(e) {
    const userItem = e.target.closest('.user-item');
    if (userItem) {
        const userId = userItem.dataset.userId;
        selectUser(userId);
    }
});

// Select user and load messages
function selectUser(userId) {
    currentUserId = userId;

    // Update UI
    document.getElementById('empty-state').classList.add('hidden');
    document.getElementById('chat-header').classList.remove('hidden');
    document.getElementById('messages-container').classList.remove('hidden');
    document.getElementById('message-input-container').classList.remove('hidden');

    // Update active user in sidebar
    document.querySelectorAll('.user-item').forEach(item => {
        item.classList.remove('bg-purple-50', 'border-purple-200');
    });
    document.querySelector(`[data-user-id="${userId}"]`).classList.add('bg-purple-50', 'border-purple-200');

    // Load messages
    loadMessages(userId);

    // Start polling for new messages
    if (messageInterval) {
        clearInterval(messageInterval);
    }
    messageInterval = setInterval(() => loadMessages(userId), 3000);
}

// Load messages for selected user
async function loadMessages(userId) {
    try {
        // Build URL with item_id parameter if available
        let url = `/user/chat/messages/${userId}`;
        if (itemContext && itemContext.uploadId) {
            url += `?item_id=${itemContext.uploadId}`;
        }

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            updateChatHeader(data.other_user);
            displayMessages(data.messages);

            // Update item context if provided by server (this ensures both users see it)
            if (data.item_context && !itemContext) {
                itemContext = data.item_context;
                // Also store in sessionStorage for persistence
                sessionStorage.setItem('chatItemContext', JSON.stringify(itemContext));
                showItemContext();
            } else if (data.item_context) {
                // Update existing context
                itemContext = data.item_context;
                sessionStorage.setItem('chatItemContext', JSON.stringify(itemContext));
                showItemContext();
            }
        }
    } catch (error) {
        console.error('Error loading messages:', error);
    }
}

// Update chat header with user info
function updateChatHeader(user) {
    document.getElementById('chat-user-name').textContent = user.name;
    document.getElementById('chat-user-initials').textContent = user.name.substring(0, 2).toUpperCase();

    // Show/hide verification badge
    const verifiedBadge = document.getElementById('chat-user-verified-badge');
    if (user.is_verified) {
        verifiedBadge.classList.remove('hidden');
        verifiedBadge.innerHTML = '<img src="/images/icons/verify.png" alt="Verified" class="w-5 h-5">';
    } else {
        verifiedBadge.classList.add('hidden');
    }

    if (user.profile_picture) {
        document.getElementById('chat-user-avatar').innerHTML =
            `<img src="${user.profile_picture}" alt="${user.name}" class="w-full h-full rounded-full object-cover">`;
    }
}

// Display messages
function displayMessages(messages) {
    const messagesList = document.getElementById('messages-list');
    messagesList.innerHTML = '';

    messages.forEach(message => {
        const messageElement = createMessageElement(message);
        messagesList.appendChild(messageElement);
    });

    // Scroll to bottom
    const messagesContainer = document.getElementById('messages-container');
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Create message element
function createMessageElement(message) {
    const isOwnMessage = message.sender_id == {{ Auth::id() }};
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex ${isOwnMessage ? 'justify-end' : 'justify-start'}`;

    messageDiv.innerHTML = `
        <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${isOwnMessage ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-900'}">
            <p class="text-sm">${message.message}</p>
            <p class="text-xs mt-1 ${isOwnMessage ? 'text-purple-100' : 'text-gray-500'}">
                ${new Date(message.created_at).toLocaleTimeString()}
            </p>
        </div>
    `;

    return messageDiv;
}

// Send message
document.getElementById('message-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();

    if (!message || !currentUserId) return;

    try {
        const response = await fetch('/user/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                receiver_id: currentUserId,
                message: message,
                item_upload_id: itemContext ? itemContext.uploadId : null,
                item_context: itemContext ? JSON.stringify(itemContext) : null
            })
        });

        const data = await response.json();

        if (data.success) {
            messageInput.value = '';
            // Reload messages to show the new message
            loadMessages(currentUserId);
        } else {
            showNotification(data.message || 'Failed to send message', 'error');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        showNotification('An error occurred while sending the message', 'error');
    }
});

// Toast notification function
function showNotification(message, type = 'info') {
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(toast => toast.remove());

    const toast = document.createElement('div');
    toast.className = 'toast-notification fixed top-4 right-4 z-50 transform transition-all duration-300 ease-in-out';

    let bgColor, icon, iconColor;
    switch (type) {
        case 'success':
            bgColor = 'bg-green-500';
            icon = 'fas fa-check-circle';
            iconColor = 'text-green-100';
            break;
        case 'error':
            bgColor = 'bg-red-500';
            icon = 'fas fa-exclamation-circle';
            iconColor = 'text-red-100';
            break;
        default:
            bgColor = 'bg-blue-500';
            icon = 'fas fa-info-circle';
            iconColor = 'text-blue-100';
    }

    toast.innerHTML = `
        <div class="${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 max-w-md">
            <i class="${icon} ${iconColor} text-xl"></i>
            <div class="flex-1">
                <div class="font-medium">${message}</div>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    }, 100);

    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.transform = 'translateX(100%)';
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Clean up interval when page is unloaded
window.addEventListener('beforeunload', function() {
    if (messageInterval) {
        clearInterval(messageInterval);
    }
});

// Item Context Functions
let itemContext = null;

// Check for item context on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('user');
    const itemId = urlParams.get('item');

    // Try to get item context from server-side data first
    @if(isset($itemContextData) && $itemContextData)
        itemContext = @json($itemContextData);
    @endif

    // Fallback to sessionStorage if not available from server
    if (!itemContext) {
        const storedContext = sessionStorage.getItem('chatItemContext');
        if (storedContext) {
            try {
                itemContext = JSON.parse(storedContext);
            } catch (e) {
                console.error('Error parsing item context from sessionStorage:', e);
            }
        }
    }

    if (userId) {
        selectUser(parseInt(userId));
        // Show item context if available
        if (itemContext) {
            setTimeout(() => showItemContext(), 100);
        }
    }
});

// Show item context in chat
function showItemContext() {
    if (!itemContext) return;

    const contextElement = document.getElementById('item-context-message');
    const contextContent = document.getElementById('item-context-content');
    const contextInfo = document.getElementById('item-context-info');
    const contextTitle = document.getElementById('item-context-title');
    const contextDetails = document.getElementById('item-context-details');

    // Show item context in message input area (enhanced display)
    contextElement.classList.remove('hidden');
    contextElement.classList.add('bg-purple-50', 'border-purple-300');
    
    const firstImage = itemContext.images && itemContext.images.length > 0 ? itemContext.images[0] : null;
    const itemType = itemContext.item_type || itemContext.itemType || 'item';
    const uploadId = itemContext.upload_id || itemContext.uploadId;
    
    contextContent.innerHTML = `
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                ${firstImage ? `
                    <img src="${firstImage.path}" alt="${firstImage.original_name || 'Item image'}"
                         class="w-20 h-20 object-cover rounded-lg border-2 border-purple-300 shadow-sm cursor-pointer"
                         onclick="window.open('/item/${uploadId}', '_blank')">
                ` : `
                    <div class="w-20 h-20 bg-purple-100 rounded-lg flex items-center justify-center border-2 border-purple-300">
                        <i class="fas fa-image text-purple-400 text-2xl"></i>
                    </div>
                `}
            </div>
            <div class="flex-1 min-w-0">
                <div class="space-y-2">
                    <div class="flex items-center space-x-2 flex-wrap">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold ${itemType === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                            ${itemType === 'lost' ? '📌 Lost Item' : '📌 Found Item'}
                        </span>
                        <span class="text-xs text-gray-600">
                            Posted by ${itemContext.uploader_name || 'Unknown'}
                        </span>
                    </div>
                    <div class="text-sm font-semibold text-gray-900">${itemContext.description || 'No description'}</div>
                    ${itemContext.location ? `<div class="text-sm text-gray-600">📍 ${itemContext.location}</div>` : ''}
                    ${itemContext.tags && itemContext.tags.length > 0 ? `
                        <div class="flex flex-wrap gap-1 mt-2">
                            ${itemContext.tags.map(tag => `<span class="px-2 py-1 bg-white text-purple-700 rounded-full text-xs border border-purple-200">${tag}</span>`).join('')}
                        </div>
                    ` : ''}
                    ${uploadId ? `
                        <a href="/item/${uploadId}" target="_blank" class="inline-block text-xs text-purple-600 hover:text-purple-800 font-medium mt-2">
                            <i class="fas fa-external-link-alt mr-1"></i>View Full Item Details
                        </a>
                    ` : ''}
                </div>
            </div>
        </div>
    `;

    // Show item context in header
    if (contextInfo) {
        contextInfo.classList.remove('hidden');
        if (contextTitle) contextTitle.textContent = `💬 Chatting about: ${itemType === 'lost' ? 'Lost' : 'Found'} Item`;
        if (contextDetails) contextDetails.textContent = `${itemContext.description || 'Item'}${itemContext.location ? ' • ' + itemContext.location : ''}`;
    }

    // Update message placeholder
    const messageInput = document.getElementById('message-input');
    if (messageInput) {
        messageInput.placeholder = `Type your message about this ${itemType} item...`;
    }
}

// Clear item context
function clearItemContext() {
    itemContext = null;
    sessionStorage.removeItem('chatItemContext');

    // Hide context elements
    document.getElementById('item-context-message').classList.add('hidden');
    document.getElementById('item-context-info').classList.add('hidden');

    // Reset message placeholder
    const messageInput = document.getElementById('message-input');
    messageInput.placeholder = 'Type a message...';

    // Clean URL
    const url = new URL(window.location);
    url.searchParams.delete('user');
    url.searchParams.delete('item');
    window.history.replaceState({}, '', url);
}
</script>
@endsection
