@extends('layouts.user')

@section('content')
<div class="p-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Messages</h1>
    <p class="text-gray-600 mb-6">Chat with other users in the system</p>

    <div class="bg-white rounded-lg shadow-sm border flex" style="height: calc(100vh - 200px);">
        <!-- Users List Sidebar -->
        <div class="w-1/3 border-r border-gray-200 flex flex-col">
            <!-- Search -->
            <div class="p-4 border-b border-gray-200">
                    <input type="text" id="user-search" placeholder="Search users..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <!-- Users List -->
            <div class="flex-1 overflow-y-auto" id="users-list">
                @if($conversations && $conversations->count() > 0)
                <div class="p-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-3">Conversations</h3>
                    <div class="space-y-2">
                        @foreach($conversations as $conversation)
                        @if(isset($conversation['user']) && $conversation['user'] && isset($conversation['last_message']) && $conversation['last_message'])
                        <div class="user-item flex items-center p-3 rounded-lg hover:bg-gray-50 cursor-pointer"
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
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $conversation['user']->name }}</p>
                                    @if(isset($conversation['unread_count']) && $conversation['unread_count'] > 0)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $conversation['unread_count'] }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 truncate">
                                    @if($conversation['last_message']->image_path)
                                        <i class="fas fa-image mr-1"></i> Image
                                    @elseif($conversation['last_message']->message)
                                        {{ \Illuminate\Support\Str::limit($conversation['last_message']->message, 50) }}
                                    @else
                                        <span class="text-gray-400 italic">No message</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $conversation['last_message']->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @else
                <div class="p-4 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-comments text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-2">No Conversations</h3>
                    <p class="text-xs text-gray-500">You don't have any conversations yet.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Chat Area -->
        <div id="chat-area" class="flex-1 flex flex-col">
            <!-- Chat Header -->
            <div id="chat-header" class="p-4 border-b border-gray-200 bg-white hidden">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div id="chat-user-avatar" class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                <span id="chat-user-initials" class="text-sm font-medium text-purple-600"></span>
                            </div>
                        </div>
                        <div class="ml-3">
                        <p id="chat-user-name" class="text-sm font-medium text-gray-900"></p>
                            </div>
                        </div>
                    </div>

            <!-- Messages Container -->
            <div id="messages-container" class="flex-1 overflow-y-auto p-4 bg-gray-50 hidden">
                <!-- Item Context Display in Chat Area -->
                <div id="chat-item-context" class="mb-4 p-4 bg-white border border-purple-200 rounded-lg shadow-sm hidden">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-box text-purple-600"></i>
                            <h3 class="text-sm font-semibold text-purple-900">Item Being Discussed</h3>
                </div>
                        <button type="button" onclick="hideChatItemContext()" class="text-gray-400 hover:text-gray-600" title="Hide item details">
                            <i class="fas fa-times"></i>
                        </button>
                        </div>
                    <div id="chat-item-context-content">
                        <!-- Item details will be displayed here -->
                    </div>
                    <div id="chat-item-context-images" class="mt-3 flex gap-2 overflow-x-auto">
                        <!-- Item images will be displayed here -->
                </div>
            </div>

                <!-- Show Item Context Button (hidden by default) -->
                <div id="show-chat-item-context-btn" class="mb-4 hidden">
                    <button type="button" onclick="showChatItemContext()" class="text-sm text-purple-600 hover:text-purple-800 flex items-center gap-2 px-3 py-2 bg-purple-50 rounded-lg border border-purple-200">
                        <i class="fas fa-box"></i>
                        <span>Show Item Details</span>
                    </button>
                </div>
                
                <div id="messages-list" class="space-y-4">
                    <!-- Messages will be loaded here -->
                </div>
            </div>

            <!-- Message Input -->
            <div id="message-input-container" class="p-4 border-t border-gray-200 bg-white hidden" style="overflow: visible !important;">
                <!-- Privacy Warning -->
                <div id="privacy-warning" class="mb-4 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start flex-1">
                        <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Privacy Notice:</strong> Please be respectful and only share necessary information. 
                                    Do not share personal details unless you trust the other party.
                            </p>
                        </div>
                        </div>
                        <button type="button" onclick="hidePrivacyNotice()" class="ml-3 text-yellow-600 hover:text-yellow-800" title="Hide notice">
                            <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                
                <!-- Show Privacy Notice Button (hidden by default) -->
                <div id="show-privacy-notice-btn" class="mb-4 hidden">
                    <button type="button" onclick="showPrivacyNotice()" class="text-xs text-yellow-600 hover:text-yellow-800 flex items-center gap-1">
                        <i class="fas fa-info-circle"></i>
                        <span>Show Privacy Notice</span>
                    </button>
                </div>

                <!-- Item Context Display -->
                <div id="item-context-message" class="mb-4 p-3 bg-purple-50 border border-purple-200 rounded-lg hidden">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-purple-900 mb-1">📦 About Item</p>
                            <p id="item-context-title" class="text-sm font-medium text-gray-900"></p>
                            <p id="item-context-location" class="text-xs text-gray-600 mt-1"></p>
                        </div>
                        <button type="button" onclick="hideItemContext()" class="ml-3 text-gray-400 hover:text-gray-600" title="Hide item context">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="item-context-images" class="mt-2 flex gap-2 overflow-x-auto">
                        <!-- Item images will be displayed here -->
                    </div>
                </div>

                <!-- Show Item Context Button (hidden by default) -->
                <div id="show-item-context-btn" class="mb-4 hidden">
                    <button type="button" onclick="showItemContext()" class="text-xs text-purple-600 hover:text-purple-800 flex items-center gap-1">
                        <i class="fas fa-box"></i>
                        <span>Show Item Details</span>
                    </button>
                </div>

                <!-- Image Preview -->
                <div id="image-preview-container" class="mb-4 hidden">
                    <div class="relative inline-block">
                        <img id="image-preview" src="" alt="Preview" class="max-w-xs max-h-48 rounded-lg border-2 border-purple-300">
                        <button type="button" onclick="removeImagePreview()" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1.5 hover:bg-red-600">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <!-- View Option Selection -->
                    <div class="mt-2">
                        <label class="block text-xs font-medium text-gray-700 mb-2">Image View Option:</label>
                        <div class="flex gap-3">
                            <label class="flex items-center">
                                <input type="radio" name="view_option" value="once" class="mr-2" checked>
                                <span class="text-xs">View Once</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="view_option" value="twice" class="mr-2">
                                <span class="text-xs">View Twice</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="view_option" value="keep" class="mr-2">
                                <span class="text-xs">Keep in Chat</span>
                            </label>
                        </div>
                    </div>
                </div>

                <form id="message-form" class="flex items-end gap-3" enctype="multipart/form-data" style="display: flex !important; width: 100%; overflow: visible !important;">
                    <input type="file" id="image-input" accept="image/*" class="hidden" onchange="handleImageSelect(event)">
                    <div class="flex-1 relative" style="min-width: 0;">
                        <textarea id="message-input" 
                                  placeholder="Type a message..."
                                  rows="1"
                                  class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 resize-none"
                                  maxlength="1000"></textarea>
                        <button type="button" onclick="document.getElementById('image-input').click()" 
                                class="absolute bottom-2 right-2 text-gray-400 hover:text-purple-500 p-2 transition-colors" style="opacity: 1 !important; visibility: visible !important; display: block !important;">
                            <i class="fas fa-image"></i>
                            </button>
                        </div>
                    <button type="submit" id="send-button" class="flex-shrink-0 shadow-md rounded-lg transition-all hover:shadow-lg" style="opacity: 1 !important; visibility: visible !important; display: flex !important; align-items: center !important; justify-content: center !important; min-width: 48px !important; min-height: 48px !important; width: 48px !important; height: 48px !important; position: relative !important; z-index: 10 !important; background-color: #8B5CF6 !important; border: none !important; padding: 0 !important; cursor: pointer !important;" onmouseover="this.style.backgroundColor='#7C3AED'" onmouseout="this.style.backgroundColor='#8B5CF6'">
                        <i class="fas fa-paper-plane" style="color: white !important; font-size: 18px !important;"></i>
                    </button>
                </form>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="flex-1 flex items-center justify-center">
                <div class="text-center">
                    <i class="fas fa-comments text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Start a Conversation</h3>
                    <p class="text-sm text-gray-500">Select a user from the list to start chatting</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentUserId = null;
let selectedImage = null;

// Select user and load messages
function selectUser(userId) {
    if (!userId) {
        console.error('selectUser called without userId');
        return;
    }
    
    console.log('selectUser called with userId:', userId);
    currentUserId = userId;

        // Update UI
        const emptyState = document.getElementById('empty-state');
        const chatHeader = document.getElementById('chat-header');
        const messagesContainer = document.getElementById('messages-container');
        const messageInputContainer = document.getElementById('message-input-container');
        
    if (emptyState) emptyState.classList.add('hidden');
    if (chatHeader) chatHeader.classList.remove('hidden');
    if (messagesContainer) messagesContainer.classList.remove('hidden');
    if (messageInputContainer) messageInputContainer.classList.remove('hidden');
    
    // Ensure send button is visible when input container is shown
    setTimeout(() => {
        const sendButton = document.getElementById('send-button');
        if (sendButton) {
            sendButton.style.display = 'block';
            sendButton.style.visibility = 'visible';
            sendButton.style.opacity = '1';
            sendButton.classList.remove('hidden');
        }
    }, 100);

        // Update active user in sidebar
        document.querySelectorAll('.user-item').forEach(item => {
        item.classList.remove('bg-purple-50');
        });
        const selectedUserItem = document.querySelector(`[data-user-id="${userId}"]`);
        if (selectedUserItem) {
        selectedUserItem.classList.add('bg-purple-50');
    }
    
    // Always show privacy notice when opening conversation
    showPrivacyNotice();

    // Load messages (this will also restore item context if available)
    loadMessages(userId);
    
    // If item context is available (from claim redirect), show it immediately
    if (itemContext) {
        setTimeout(() => {
            showItemContext();
            showChatItemContext();
        }, 300);
    }
}

// Load messages
async function loadMessages(userId) {
    try {
        const response = await fetch(`/chat/messages/${userId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            console.error('Failed to load messages');
            document.getElementById('messages-list').innerHTML = '<div class="text-center text-red-500 py-8">Failed to load messages</div>';
            return;
        }

        const data = await response.json();

        if (data.success) {
            // Update chat header
            if (data.other_user) {
                document.getElementById('chat-user-name').textContent = data.other_user.name;
                const initials = data.other_user.name.substring(0, 2).toUpperCase();
                document.getElementById('chat-user-initials').textContent = initials;
            }
            
            // Update item context if provided - show based on claim, even if verified
            if (data.item_context) {
                itemContext = data.item_context;
                
                // Always show item context when opening conversation (shows claim status)
                showItemContext();
                showChatItemContext();
            } else {
                    hideItemContext();
                hideChatItemContext();
                itemContext = null;
                // Hide the show button if there's no item context
                const showBtn = document.getElementById('show-chat-item-context-btn');
                if (showBtn) showBtn.classList.add('hidden');
            }
            
            // Display messages
            displayMessages(data.messages || []);
        } else {
            document.getElementById('messages-list').innerHTML = '<div class="text-center text-red-500 py-8">Failed to load messages</div>';
        }
    } catch (error) {
        console.error('Error loading messages:', error);
        document.getElementById('messages-list').innerHTML = '<div class="text-center text-red-500 py-8">Error loading messages</div>';
    }
}

// Display messages
function displayMessages(messages) {
    const messagesList = document.getElementById('messages-list');
    if (!messagesList) return;
    
    messagesList.innerHTML = '';
    
    if (!messages || messages.length === 0) {
        messagesList.innerHTML = '<div class="text-center text-gray-500 py-8">No messages yet. Start the conversation!</div>';
        return;
    }
    
    const currentUser = @json(Auth::user());
    
    messages.forEach(message => {
        const isOwnMessage = parseInt(message.sender_id) === parseInt(currentUser.id);
        console.log('Message sender_id:', message.sender_id, 'Current user id:', currentUser.id, 'isOwnMessage:', isOwnMessage);
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex items-end gap-2 mb-4 ${isOwnMessage ? 'flex-row-reverse' : 'flex-row'}`;
        
        // Avatar
        const avatar = document.createElement('div');
        avatar.className = 'flex-shrink-0 w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center';
        const senderName = message.sender && message.sender.name ? message.sender.name : 'User';
        const initials = senderName.substring(0, 2).toUpperCase();
        avatar.innerHTML = `<span class="text-xs font-medium text-purple-600">${initials}</span>`;
        
        // Message bubble - ensure blue for sender
        const bubble = document.createElement('div');
        bubble.className = 'max-w-md px-4 py-2 rounded-lg';
        if (isOwnMessage) {
            bubble.className += ' bg-blue-300 text-gray-900';
            bubble.style.backgroundColor = '#93c5fd'; // blue-300 color as fallback
            bubble.style.color = '#111827'; // gray-900
            } else {
            bubble.className += ' bg-gray-200 text-gray-900';
            bubble.style.backgroundColor = '#e5e7eb'; // gray-200 color as fallback
            bubble.style.color = '#111827'; // gray-900
        }
        
        let messageContent = '';
        
        // Item context in message
    if (message.item_context) {
            let itemCtx = message.item_context;
            if (typeof itemCtx === 'string') {
                try {
                    itemCtx = JSON.parse(itemCtx);
        } catch (e) {
                    itemCtx = null;
                }
            }
            
            if (itemCtx) {
                const description = itemCtx.description || 'Item';
                const location = itemCtx.location || 'Location not specified';
                const images = itemCtx.images || [];
                
                // Use appropriate background for item context based on message sender
                const itemBgClass = isOwnMessage ? 'bg-white/70' : 'bg-white/60';
                messageContent += `<div class="mb-2 p-2 ${itemBgClass} rounded border border-gray-300">`;
                messageContent += '<p class="text-xs font-semibold mb-1 text-gray-700">📦 About Item</p>';
                messageContent += `<p class="text-sm font-medium text-gray-900">${escapeHtml(description)}</p>`;
                messageContent += `<p class="text-xs mt-1 text-gray-700">📍 ${escapeHtml(location)}</p>`;
                if (images.length > 0) {
                    messageContent += '<div class="mt-2 flex gap-1">';
                    images.slice(0, 3).forEach(img => {
                        messageContent += `<img src="${img.path}" alt="Item" class="w-12 h-12 object-cover rounded">`;
                    });
                    messageContent += '</div>';
                }
                messageContent += '</div>';
            }
        }
        
        // Image in message
    if (message.image_path) {
            messageContent += `<img src="${message.image_path}" alt="Image" class="max-w-full rounded mb-2">`;
        }
        
        // Text message
        if (message.message) {
            messageContent += `<p>${escapeHtml(message.message)}</p>`;
        }
        
        bubble.innerHTML = messageContent || '<p class="text-sm opacity-70">Message</p>';
        
        // Time
        const time = document.createElement('span');
        time.className = `text-xs ${isOwnMessage ? 'text-gray-700' : 'text-gray-400'}`;
        if (message.created_at) {
            const date = new Date(message.created_at);
            time.textContent = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        
        const messageWrapper = document.createElement('div');
        messageWrapper.className = `flex flex-col ${isOwnMessage ? 'items-end' : 'items-start'}`;
        messageWrapper.appendChild(bubble);
        messageWrapper.appendChild(time);
        
        messageDiv.appendChild(avatar);
        messageDiv.appendChild(messageWrapper);
        messagesList.appendChild(messageDiv);
    });
    
    // Scroll to bottom
    const container = document.getElementById('messages-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

// Escape HTML helper
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Send message
document.getElementById('message-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();
    
    if ((!message && !selectedImage) || !currentUserId) {
        alert('Please select a user and enter a message');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('receiver_id', currentUserId);
        formData.append('message', message || '');
        if (selectedImage) {
            formData.append('image', selectedImage);
            const viewOption = document.querySelector('input[name="view_option"]:checked')?.value || 'keep';
            formData.append('view_option', viewOption);
        }
        if (itemContext) {
            formData.append('item_upload_id', itemContext.uploadId || itemContext.upload_id || '');
            formData.append('item_context', JSON.stringify(itemContext));
        }

        const response = await fetch('/chat/send', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            messageInput.value = '';
            selectedImage = null;
            document.getElementById('image-input').value = '';
            removeImagePreview();
            
            // Update item context if response includes it
            if (data.message && data.message.item_context) {
                let responseContext = data.message.item_context;
                if (typeof responseContext === 'string') {
                    try {
                        responseContext = JSON.parse(responseContext);
                    } catch (e) {
                        responseContext = null;
                    }
                }
                if (responseContext) {
                    itemContext = responseContext;
                    showItemContext(); // This will also call showChatItemContext()
                }
            }

            // Reload messages
            loadMessages(currentUserId);
            } else {
            alert(data.message || 'Failed to send message');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        alert('An error occurred while sending the message');
    }
});

// Item context
let itemContext = null;

// Handle image selection
function handleImageSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
        alert('Please select an image file');
        return;
    }
    
    if (file.size > 10 * 1024 * 1024) {
        alert('Image size must be less than 10MB');
        return;
    }
    
    selectedImage = file;
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('image-preview').src = e.target.result;
        document.getElementById('image-preview-container').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

// Remove image preview
function removeImagePreview() {
    selectedImage = null;
    document.getElementById('image-input').value = '';
    document.getElementById('image-preview-container').classList.add('hidden');
}

// Show item context
function showItemContext() {
    if (!itemContext) return;
    
    const contextElement = document.getElementById('item-context-message');
    const showBtn = document.getElementById('show-item-context-btn');
    const titleElement = document.getElementById('item-context-title');
    const locationElement = document.getElementById('item-context-location');
    const imagesElement = document.getElementById('item-context-images');
    
    if (contextElement && titleElement && locationElement) {
        titleElement.textContent = itemContext.description || 'Item';
        locationElement.textContent = '📍 ' + (itemContext.location || 'Location not specified');
        
        // Display images
        if (imagesElement && itemContext.images && itemContext.images.length > 0) {
            imagesElement.innerHTML = '';
            itemContext.images.forEach(image => {
                const img = document.createElement('img');
                img.src = image.path;
                img.className = 'w-20 h-20 object-cover rounded border border-gray-200';
                img.alt = 'Item image';
                imagesElement.appendChild(img);
            });
        }
        
        contextElement.classList.remove('hidden');
        if (showBtn) showBtn.classList.add('hidden');
        
        // Store preference - item context is visible
        if (currentUserId) {
            localStorage.setItem(`item-context-hidden-${currentUserId}`, 'false');
        }
    }
    
    // Also show in chat area
    showChatItemContext();
}

// Hide item context
function hideItemContext() {
    const contextElement = document.getElementById('item-context-message');
    const showBtn = document.getElementById('show-item-context-btn');
    
    if (contextElement) {
        contextElement.classList.add('hidden');
    }
    if (showBtn) {
        showBtn.classList.remove('hidden');
    }
    
    // Store preference - item context is hidden
    if (currentUserId) {
        localStorage.setItem(`item-context-hidden-${currentUserId}`, 'true');
    }
}

// Show item context in chat area (visible to both users)
function showChatItemContext() {
    // If itemContext is not set, try to get it from the current conversation
    if (!itemContext && currentUserId) {
        // Try to fetch item context from the current conversation
        fetch(`/chat/messages/${currentUserId}`, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch item context');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.item_context) {
                    itemContext = data.item_context;
                    displayChatItemContext();
                } else {
                    console.log('No item context available for this conversation');
                    alert('No item details available for this conversation');
                }
            })
            .catch(error => {
                console.error('Error fetching item context:', error);
                alert('Failed to load item details. Please try again.');
            });
        return;
    }
    
    if (!itemContext) {
        console.log('No item context available');
        alert('No item details available');
        return;
    }
    
    displayChatItemContext();
}

function displayChatItemContext() {
    if (!itemContext) return;
    
    const chatContextElement = document.getElementById('chat-item-context');
    const chatShowBtn = document.getElementById('show-chat-item-context-btn');
    const chatContentElement = document.getElementById('chat-item-context-content');
    const chatImagesElement = document.getElementById('chat-item-context-images');
    
    if (chatContextElement && chatContentElement) {
        // Build content
        const description = itemContext.description || 'Item';
        const location = itemContext.location || 'Location not specified';
        const itemType = itemContext.item_type || itemContext.itemType || 'item';
        const tags = itemContext.tags || [];
        
        // Get claim status info
        const claimStatus = itemContext.claim_status || null;
        const isClaimed = itemContext.is_claimed || false;
        let claimBadge = '';
        
        if (claimStatus === 'verified' || (isClaimed && claimStatus === 'verified')) {
            claimBadge = '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">✓ Claim Verified</span>';
        } else if (claimStatus === 'pending') {
            claimBadge = '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⏳ Claim Pending</span>';
        } else if (claimStatus === 'rejected') {
            claimBadge = '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">✗ Claim Rejected</span>';
        }
        
        let contentHtml = `
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">${escapeHtml(description)}</p>
                    ${claimBadge}
                </div>
                <p class="text-xs text-gray-600 flex items-center gap-1">
                    <i class="fas fa-map-marker-alt"></i>
                    ${escapeHtml(location)}
                </p>
                <p class="text-xs text-gray-600 flex items-center gap-1">
                    <i class="fas fa-tag"></i>
                    ${escapeHtml(itemType)}
                </p>
        `;
        
        if (tags.length > 0) {
            contentHtml += '<div class="flex flex-wrap gap-1 mt-2">';
            tags.forEach(tag => {
                contentHtml += `<span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full text-xs">${escapeHtml(tag)}</span>`;
            });
            contentHtml += '</div>';
        }
        
        contentHtml += '</div>';
        
        chatContentElement.innerHTML = contentHtml;
        
        // Display images
        if (chatImagesElement && itemContext.images && itemContext.images.length > 0) {
            chatImagesElement.innerHTML = '';
            itemContext.images.forEach(image => {
                const img = document.createElement('img');
                img.src = image.path;
                img.className = 'w-24 h-24 object-cover rounded border border-gray-200 cursor-pointer hover:opacity-80';
                img.alt = 'Item image';
                img.onclick = () => {
                    const modal = document.createElement('div');
                    modal.className = 'fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4';
                    modal.innerHTML = `
                        <div class="relative max-w-4xl max-h-full">
                            <img src="${image.path}" alt="Item" class="max-w-full max-h-[90vh] rounded-lg">
                            <button onclick="this.closest('.fixed').remove()" class="absolute top-4 right-4 bg-white/20 hover:bg-white/30 text-white rounded-full p-2">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    modal.onclick = (e) => {
                        if (e.target === modal) modal.remove();
                    };
                    document.body.appendChild(modal);
                };
                chatImagesElement.appendChild(img);
            });
        } else {
            chatImagesElement.innerHTML = '';
        }
        
        chatContextElement.classList.remove('hidden');
        if (chatShowBtn) chatShowBtn.classList.add('hidden');
        
        // Store preference
        if (currentUserId) {
            localStorage.setItem(`chat-item-context-hidden-${currentUserId}`, 'false');
        }
    }
}

// Hide item context in chat area
function hideChatItemContext() {
    const chatContextElement = document.getElementById('chat-item-context');
    const chatShowBtn = document.getElementById('show-chat-item-context-btn');
    
    if (chatContextElement) {
        chatContextElement.classList.add('hidden');
    }
    // Only show the button if item context exists
    if (chatShowBtn && itemContext) {
        chatShowBtn.classList.remove('hidden');
    } else if (chatShowBtn) {
        chatShowBtn.classList.add('hidden');
    }
    
    // Store preference
    if (currentUserId) {
        localStorage.setItem(`chat-item-context-hidden-${currentUserId}`, 'true');
    }
}

// Show privacy notice
function showPrivacyNotice() {
    const notice = document.getElementById('privacy-warning');
    const showBtn = document.getElementById('show-privacy-notice-btn');
    
    if (notice) {
        notice.classList.remove('hidden');
    }
    if (showBtn) {
        showBtn.classList.add('hidden');
    }
    
    // Store preference - notice is visible
    localStorage.setItem('privacy-notice-hidden', 'false');
}

// Hide privacy notice
function hidePrivacyNotice() {
    const notice = document.getElementById('privacy-warning');
    const showBtn = document.getElementById('show-privacy-notice-btn');
    
    if (notice) {
        notice.classList.add('hidden');
    }
    if (showBtn) {
        showBtn.classList.remove('hidden');
    }
    
    // Store preference - notice is hidden
    localStorage.setItem('privacy-notice-hidden', 'true');
}

// User item click handlers
// Ensure send button is always visible
function ensureSendButtonVisible() {
    const sendButton = document.getElementById('send-button');
    if (sendButton) {
        sendButton.style.display = 'flex';
        sendButton.style.visibility = 'visible';
        sendButton.style.opacity = '1';
        sendButton.style.alignItems = 'center';
        sendButton.style.justifyContent = 'center';
        sendButton.style.position = 'relative';
        sendButton.style.zIndex = '10';
        sendButton.classList.remove('hidden');
        
        // Also ensure parent form is visible
        const form = document.getElementById('message-form');
        if (form) {
            form.style.display = 'flex';
            form.style.overflow = 'visible';
        }
        
        // Ensure container is visible
        const container = document.getElementById('message-input-container');
        if (container) {
            container.style.overflow = 'visible';
        }
    }
}

function initializeChat() {
    console.log('Initializing chat...');
    
    // Attach click handlers to user items
    const userItems = document.querySelectorAll('.user-item');
    console.log('Found', userItems.length, 'user items');
    
    userItems.forEach(item => {
        // Remove any existing listeners by cloning
        const newItem = item.cloneNode(true);
        item.parentNode.replaceChild(newItem, item);
        
        newItem.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const userId = this.getAttribute('data-user-id');
            console.log('User item clicked, userId:', userId);
            if (userId) {
                selectUser(parseInt(userId));
        } else {
                console.error('No userId found on clicked item');
            }
        });
    });
    
    // Auto-select user from URL
    const urlParams = new URLSearchParams(window.location.search);
    const userId = urlParams.get('user');
    const itemId = urlParams.get('item');
    const selectedUserId = urlParams.get('user');

    // Load item context if itemId is provided (from claim-verify redirect)
    @if(isset($itemContextData) && $itemContextData)
        itemContext = @json($itemContextData);
        console.log('Item context loaded from claim:', itemContext);
    @endif
    
    // Auto-select user if provided in URL (from claim-verify redirect)
    if (selectedUserId) {
        // Wait for DOM to be ready, then select the user
        setTimeout(() => {
            selectUser(parseInt(selectedUserId));
            // Show item context if available
            if (itemContext) {
                showItemContext();
                showChatItemContext();
            }
        }, 100);
    }
    
    // Restore privacy notice preference on page load
    const privacyNoticeHidden = localStorage.getItem('privacy-notice-hidden') === 'true';
    if (privacyNoticeHidden) {
        hidePrivacyNotice();
    } else {
        showPrivacyNotice();
    }
    
    if (userId) {
        console.log('Auto-selecting user from URL:', userId);
        setTimeout(() => selectUser(parseInt(userId)), 100);
    }
    
    // User search
    const userSearch = document.getElementById('user-search');
    if (userSearch) {
        userSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.user-item').forEach(item => {
                const userNameEl = item.querySelector('p.font-medium');
                if (userNameEl) {
                    const userName = userNameEl.textContent.toLowerCase();
                    if (userName.includes(searchTerm)) {
                        item.style.display = 'flex';
                            } else {
                        item.style.display = 'none';
                }
            }
        });
        });
    }
    
    // Send message on Enter key (but not Shift+Enter for new line)
    const messageInput = document.getElementById('message-input');
    if (messageInput) {
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                const sendButton = document.getElementById('send-button');
                if (sendButton && !sendButton.disabled) {
                    sendButton.click();
                }
            }
        });
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        initializeChat();
        ensureSendButtonVisible();
        // Check periodically to ensure button stays visible
        setInterval(ensureSendButtonVisible, 1000);
    });
        } else {
    // DOM is already ready
    initializeChat();
    ensureSendButtonVisible();
    // Check periodically to ensure button stays visible
    setInterval(ensureSendButtonVisible, 1000);
}
</script>
@endsection

