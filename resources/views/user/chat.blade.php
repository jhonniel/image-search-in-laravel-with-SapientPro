@extends('layouts.user')

@section('content')
<div class="p-3 sm:p-6 h-full">
    <!-- Header -->
    <div class="mb-4 sm:mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Messages</h1>
        <p class="text-sm sm:text-base text-gray-600 mt-1 sm:mt-2">Chat with other users in the system</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border h-[calc(100vh-180px)] sm:h-[calc(100vh-200px)] flex flex-col lg:flex-row relative overflow-hidden">
        <!-- Users List Sidebar -->
        <div id="users-sidebar" class="w-full lg:w-1/3 border-r border-gray-200 flex flex-col absolute lg:relative inset-0 z-10 lg:z-auto bg-white transition-transform duration-300 ease-in-out lg:translate-x-0">
            <!-- Search -->
            <div class="p-3 sm:p-4 border-b border-gray-200">
                <div class="relative">
                    <input type="text" id="user-search" placeholder="Search users..."
                           class="w-full pl-10 pr-4 py-2 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Users List -->
            <div class="flex-1 overflow-y-auto" id="users-list">
                <!-- Conversations -->
                @if($conversations->count() > 0)
                <div class="p-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-3">Conversations</h3>
                    <div class="space-y-1 sm:space-y-2">
                        @foreach($conversations as $conversation)
                        <div class="user-item flex items-center p-2 sm:p-3 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors active:bg-gray-100"
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
                @else
                <!-- Empty State -->
                <div class="p-4 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-comments text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-2">No Conversations</h3>
                    <p class="text-xs text-gray-500">You don't have any conversations yet. Start chatting by claiming an item or messaging someone about an item.</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Chat Area -->
        <div id="chat-area" class="flex-1 flex flex-col hidden lg:flex">
            <!-- Chat Header -->
            <div id="chat-header" class="p-3 sm:p-4 border-b border-gray-200 bg-white">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center flex-1 min-w-0">
                        <!-- Back Button (Mobile Only) -->
                        <button id="back-to-users" onclick="showUsersList()" class="lg:hidden mr-3 p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <div class="flex-shrink-0">
                            <div id="chat-user-avatar" class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                <span id="chat-user-initials" class="text-sm font-medium text-purple-600"></span>
                            </div>
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 id="chat-user-name" class="text-base sm:text-lg font-medium text-gray-900 truncate"></h3>
                                <span id="chat-user-verified-badge" class="hidden inline-flex items-center justify-center w-5 h-5 flex-shrink-0" title="Verified Profile">
                                    <img src="{{ asset('images/icons/verify.png') }}" alt="Verified" class="w-5 h-5">
                                </span>
                            </div>
                            <p id="chat-user-status" class="text-xs sm:text-sm text-gray-500">Online</p>
                        </div>
                    </div>
                </div>
                <!-- Item Context Info (shown when chatting about an item) -->
                <div id="item-context-info" class="hidden mt-2 sm:mt-3 pt-2 sm:pt-3 border-t border-gray-200 bg-purple-50 rounded-lg p-2 sm:p-3">
                    <div class="flex items-start space-x-2">
                        <i class="fas fa-info-circle text-purple-500 mt-0.5 text-sm sm:text-base"></i>
                        <div class="flex-1">
                            <p class="text-xs sm:text-sm font-semibold text-purple-900" id="item-context-title">About Item</p>
                            <p class="text-xs text-purple-700 mt-1" id="item-context-details">Item details will appear here</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages Area -->
            <div id="messages-container" class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 bg-gradient-to-b from-gray-50 to-white">
                <div id="messages-list" class="space-y-1 sm:space-y-2">
                    <!-- Messages will be loaded here -->
                </div>
            </div>

            <!-- Message Input -->
            <div id="message-input-container" class="p-3 sm:p-4 border-t border-gray-200 bg-white">
                <!-- Privacy Warning -->
                <div id="privacy-warning" class="mb-3 sm:mb-4 p-2 sm:p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-base sm:text-lg"></i>
                        </div>
                        <div class="ml-2 sm:ml-3 flex-1">
                            <p class="text-xs sm:text-sm font-medium text-yellow-800">
                                <strong>Privacy Notice:</strong> Please do not share personal information such as phone numbers, addresses, email addresses, or financial details in your messages. Keep your conversations focused on the items you're discussing.
                            </p>
                        </div>
                        <div class="ml-2 sm:ml-3 flex-shrink-0">
                            <button type="button" 
                                    onclick="hidePrivacyWarning()" 
                                    class="text-yellow-600 hover:text-yellow-800 transition-colors p-1 rounded-full hover:bg-yellow-100"
                                    title="Hide this notice">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Item Context Message -->
                <div id="item-context-message" class="mb-3 sm:mb-4 p-2 sm:p-3 bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg sm:rounded-xl shadow-sm hidden">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs sm:text-sm font-semibold text-purple-900 flex items-center">
                            <i class="fas fa-info-circle mr-1 sm:mr-2 text-xs sm:text-sm"></i>Chatting about this item:
                        </h4>
                        <button onclick="clearItemContext()" class="text-purple-600 hover:text-purple-800 transition-colors p-1 rounded-full hover:bg-purple-100 min-w-[32px] min-h-[32px] flex items-center justify-center">
                            <i class="fas fa-times text-xs sm:text-sm"></i>
                        </button>
                    </div>
                    <div id="item-context-content" class="text-xs sm:text-sm text-purple-700">
                        <!-- Item details will be loaded here -->
                    </div>
                </div>

                <!-- Image Upload Preview (Hidden by default) -->
                <div id="image-preview-container" class="mb-3 sm:mb-4 hidden">
                    <div class="relative inline-block">
                        <img id="image-preview" src="" alt="Preview" class="max-w-full sm:max-w-xs max-h-40 sm:max-h-48 rounded-lg border-2 border-purple-300">
                        <button type="button" onclick="removeImagePreview()" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1.5 sm:p-2 hover:bg-red-600 min-w-[32px] min-h-[32px] flex items-center justify-center">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <!-- View Option Selection -->
                    <div class="mt-2">
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Image View Option:</label>
                        <div class="flex flex-wrap gap-2 sm:gap-3">
                            <label class="flex items-center">
                                <input type="radio" name="view_option" value="once" class="mr-1 sm:mr-2" checked>
                                <span class="text-xs sm:text-sm">View Once</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="view_option" value="twice" class="mr-1 sm:mr-2">
                                <span class="text-xs sm:text-sm">View Twice</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="view_option" value="keep" class="mr-1 sm:mr-2">
                                <span class="text-xs sm:text-sm">Keep in Chat</span>
                            </label>
                        </div>
                    </div>
                </div>

                <form id="message-form" class="flex items-end gap-2 sm:gap-3" enctype="multipart/form-data">
                    <input type="file" id="image-input" accept="image/*" class="hidden" onchange="handleImageSelect(event)">
                    <div class="flex-1 relative">
                        <textarea id="message-input" 
                                  placeholder="Type a message..."
                                  rows="1"
                                  class="w-full px-3 sm:px-4 py-2.5 sm:py-3 pr-10 sm:pr-12 text-base border-2 border-gray-200 rounded-xl sm:rounded-2xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none overflow-hidden transition-all"
                                  maxlength="1000"
                                  style="min-height: 44px; max-height: 120px;"></textarea>
                        <div class="absolute bottom-1.5 sm:bottom-2 right-1.5 sm:right-2 flex items-center gap-1 sm:gap-2">
                            <span id="char-count" class="text-xs text-gray-400 hidden sm:inline">0/1000</span>
                            <button type="button" onclick="document.getElementById('image-input').click()" class="text-gray-400 hover:text-purple-500 transition-colors p-1.5 sm:p-2 rounded-full hover:bg-purple-50 min-w-[44px] min-h-[44px] flex items-center justify-center" title="Upload image">
                                <i class="fas fa-image text-base sm:text-lg"></i>
                            </button>
                            <button type="button" class="text-gray-400 hover:text-purple-500 transition-colors p-1.5 sm:p-2 rounded-full hover:bg-purple-50 min-w-[44px] min-h-[44px] flex items-center justify-center hidden sm:flex" title="Add emoji">
                                <i class="fas fa-smile text-base sm:text-lg"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit"
                            class="px-4 sm:px-5 py-2.5 sm:py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl sm:rounded-2xl hover:from-purple-600 hover:to-purple-700 transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none min-w-[44px] min-h-[44px]"
                            title="Send message">
                        <i class="fas fa-paper-plane text-sm sm:text-base"></i>
                    </button>
                </form>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="flex-1 flex items-center justify-center hidden lg:flex">
                <div class="text-center px-4">
                    <i class="fas fa-comments text-4xl sm:text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">Start a Conversation</h3>
                    <p class="text-sm sm:text-base text-gray-500">Select a user from the list to start chatting</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentUserId = null;
let messageInterval = null;

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // User search functionality
    const userSearchInput = document.getElementById('user-search');
    if (userSearchInput) {
        userSearchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const userItems = document.querySelectorAll('.user-item');

            userItems.forEach(item => {
                const userNameEl = item.querySelector('p.font-medium');
                const lastMessage = item.querySelector('p.text-gray-500:not(.text-xs)');
                const userName = userNameEl ? userNameEl.textContent.toLowerCase() : '';
                const lastMessageText = lastMessage ? lastMessage.textContent.toLowerCase() : '';

                if (userName.includes(searchTerm) || lastMessageText.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});

// User selection - attach after DOM is ready
function initializeUserSelection() {
    // Attach click handlers to all user items
    document.querySelectorAll('.user-item').forEach(userItem => {
        userItem.addEventListener('click', function(e) {
            e.stopPropagation();
            const userId = this.dataset.userId || this.getAttribute('data-user-id');
            if (userId) {
                console.log('User item clicked, opening conversation with user:', userId);
                selectUser(parseInt(userId));
            } else {
                console.error('No user ID found on clicked item:', this);
            }
        });
    });
    console.log('User selection handlers attached to', document.querySelectorAll('.user-item').length, 'items');
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeUserSelection);
} else {
    // DOM is already ready
    initializeUserSelection();
}

// Select user and load messages
function selectUser(userId) {
    if (!userId) {
        console.error('selectUser called without userId');
        return;
    }
    
    console.log('selectUser called with userId:', userId);
    currentUserId = userId;

    try {
        // Update UI
        const emptyState = document.getElementById('empty-state');
        const chatHeader = document.getElementById('chat-header');
        const messagesContainer = document.getElementById('messages-container');
        const messageInputContainer = document.getElementById('message-input-container');
        
        console.log('UI Elements found:', {
            emptyState: !!emptyState,
            chatHeader: !!chatHeader,
            messagesContainer: !!messagesContainer,
            messageInputContainer: !!messageInputContainer
        });
        
        if (emptyState) {
            emptyState.classList.add('hidden');
            emptyState.style.display = 'none';
            console.log('Hidden empty state');
        }
        if (chatHeader) {
            chatHeader.classList.remove('hidden');
            chatHeader.style.display = '';
            console.log('Showed chat header');
        }
        if (messagesContainer) {
            messagesContainer.classList.remove('hidden');
            messagesContainer.style.display = '';
            console.log('Showed messages container');
        }
        if (messageInputContainer) {
            messageInputContainer.classList.remove('hidden');
            messageInputContainer.style.display = '';
            console.log('Showed message input container');
        }
        
        // Show chat area on mobile
        const chatArea = document.getElementById('chat-area');
        if (chatArea) {
            chatArea.classList.remove('hidden');
        }
        
        // Show privacy warning when opening conversation
        if (typeof showPrivacyWarning === 'function') {
            showPrivacyWarning();
        } else {
            console.warn('showPrivacyWarning function not found');
        }

        // Update active user in sidebar
        document.querySelectorAll('.user-item').forEach(item => {
            item.classList.remove('bg-purple-50', 'border-purple-200');
        });
        
        const selectedUserItem = document.querySelector(`[data-user-id="${userId}"]`);
        if (selectedUserItem) {
            selectedUserItem.classList.add('bg-purple-50', 'border-purple-200');
        } else {
            console.warn('Could not find user item with data-user-id:', userId, '- User may not be in conversations list yet, but chat will still work');
        }

        // Load messages (this will also update the chat header with user info)
        loadMessages(userId);

        // Stop polling since we're using WebSocket now
        if (messageInterval) {
            clearInterval(messageInterval);
            messageInterval = null;
        }
        
        // On mobile, hide sidebar and show chat area
        if (window.innerWidth < 1024) {
            showChatArea();
        }
    } catch (error) {
        console.error('Error in selectUser:', error);
    }
}

// Mobile navigation functions
function showUsersList() {
    const sidebar = document.getElementById('users-sidebar');
    const chatArea = document.getElementById('chat-area');
    
    if (sidebar) {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
    }
    
    if (chatArea) {
        chatArea.classList.add('hidden');
    }
}

function showChatArea() {
    const sidebar = document.getElementById('users-sidebar');
    const chatArea = document.getElementById('chat-area');
    
    if (sidebar) {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
    }
    
    if (chatArea) {
        chatArea.classList.remove('hidden');
    }
}

// Handle window resize
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        // On desktop, always show both
        if (window.innerWidth >= 1024) {
            const sidebar = document.getElementById('users-sidebar');
            const chatArea = document.getElementById('chat-area');
            
            if (sidebar) {
                sidebar.classList.remove('-translate-x-full', 'translate-x-0');
            }
            
            if (chatArea) {
                chatArea.classList.remove('hidden');
            }
        }
    }, 250);
});

// Load messages for selected user
async function loadMessages(userId) {
    try {
        // Build URL - don't include item_id parameter, let server extract from messages
        let url = `/chat/messages/${userId}`;

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (!response.ok) {
            console.error('Failed to load messages:', response.status, response.statusText);
            const messagesList = document.getElementById('messages-list');
            if (messagesList) {
                messagesList.innerHTML = '<div class="text-center text-red-500 py-8">Failed to load messages. Please try again.</div>';
            }
            return;
        }

        const data = await response.json();
        console.log('API Response:', data);

        if (data.success) {
            updateChatHeader(data.other_user);
            console.log('Messages received:', data.messages);
            if (data.messages && Array.isArray(data.messages) && data.messages.length > 0) {
                displayMessages(data.messages);
            } else {
                console.log('No messages to display');
                const messagesList = document.getElementById('messages-list');
                if (messagesList) {
                    messagesList.innerHTML = '<div class="text-center text-gray-500 py-8">No messages yet. Start the conversation!</div>';
                }
            }

            // Update item context if provided by server (this ensures both users see it)
            // BUT only if item is not verified
            if (data.item_context) {
                console.log('Item context received from server:', data.item_context);
                // Check if item is verified - if so, hide context
                if (data.item_context.claim_status === 'verified') {
                    // Item is verified, hide context
                    console.log('Item is verified, hiding context');
                    hideItemContext();
                } else {
                    // Item is not verified, show/update context (for both users in conversation)
                    console.log('Showing item context for both users');
                itemContext = data.item_context;
                sessionStorage.setItem('chatItemContext', JSON.stringify(itemContext));
                    showItemContext(); // Always refresh to show latest claim status
                }
            } else {
                // No item context from server - check if current context is for a verified item
                if (itemContext && (itemContext.claim_status === 'verified' || itemContext.claimStatus === 'verified')) {
                    console.log('Current context is verified, hiding');
                    hideItemContext();
                } else if (itemContext) {
                    // If we have context but server didn't return it, it might be verified - hide it
                    console.log('Server returned no context, hiding existing context');
                    hideItemContext();
                }
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
    const messagesContainer = document.getElementById('messages-container');
    
    if (!messagesList) {
        console.error('Messages list element not found');
        return;
    }
    
    // Ensure messages container is visible
    if (messagesContainer) {
        messagesContainer.classList.remove('hidden');
    }
    
    if (!messages || !Array.isArray(messages) || messages.length === 0) {
        messagesList.innerHTML = '<div class="text-center text-gray-500 py-8">No messages yet. Start the conversation!</div>';
        return;
    }
    
    console.log('Displaying', messages.length, 'messages');
    messagesList.innerHTML = '';

    // Group messages by date
    let currentDate = null;
    messages.forEach((message, index) => {
        if (!message) {
            console.warn('Invalid message at index', index);
            return;
        }
        
        try {
        // Check if we need to show a date separator
        const messageDate = new Date(message.created_at);
        const messageDateStr = messageDate.toDateString();
        
        if (currentDate !== messageDateStr) {
            currentDate = messageDateStr;
            const dateSeparator = document.createElement('div');
            dateSeparator.className = 'flex items-center justify-center my-4';
            const today = new Date().toDateString();
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            
            let dateLabel = '';
            if (messageDateStr === today) {
                dateLabel = 'Today';
            } else if (messageDateStr === yesterday.toDateString()) {
                dateLabel = 'Yesterday';
            } else {
                dateLabel = messageDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: messageDate.getFullYear() !== new Date().getFullYear() ? 'numeric' : undefined });
            }
            
            dateSeparator.innerHTML = `
                <div class="px-4 py-1.5 bg-white/80 backdrop-blur-sm border border-gray-200 rounded-full shadow-sm">
                    <span class="text-xs font-medium text-gray-600">${dateLabel}</span>
                </div>
            `;
            messagesList.appendChild(dateSeparator);
        }

        const messageElement = createMessageElement(message);
        if (messageElement) {
            messagesList.appendChild(messageElement);
        } else {
            console.error('Failed to create message element for message:', message);
        }
        } catch (error) {
            console.error('Error processing message at index', index, ':', error, message);
        }
    });

    // Scroll to bottom smoothly
    if (messagesContainer) {
        setTimeout(() => {
            messagesContainer.scrollTo({
                top: messagesContainer.scrollHeight,
                behavior: 'smooth'
            });
        }, 100);
    }
}

// Create message element with messenger-style speech bubbles
function createMessageElement(message) {
    const currentUserId = {{ Auth::id() }};
    const isOwnMessage = parseInt(message.sender_id) === parseInt(currentUserId);
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex items-end gap-2 mb-4 ${isOwnMessage ? 'flex-row-reverse' : 'flex-row'}`;
    messageDiv.setAttribute('data-message-id', message.id);

    // Format created_at date
    let timeString = 'Just now';
    if (message.created_at) {
        try {
            const date = new Date(message.created_at);
            if (!isNaN(date.getTime())) {
                timeString = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
        } catch (e) {
            console.error('Error parsing date:', e);
        }
    }

    // Get sender info for avatar
    const sender = message.sender || {};
    const senderName = sender.name || 'User';
    const senderAvatar = sender.profile_picture || null;
    const senderInitials = senderName.substring(0, 2).toUpperCase();

    // Build item context preview if available
    let itemContextHtml = '';
    if (message.item_context) {
        let previewContext = message.item_context;
        if (typeof previewContext === 'string') {
            try {
                previewContext = JSON.parse(previewContext);
            } catch (parseError) {
                console.error('Failed to parse message item_context in createMessageElement:', parseError);
                previewContext = null;
            }
        }

        if (previewContext && typeof previewContext === 'object') {
            const claimStatus = previewContext.claim_status || previewContext.claimStatus;
            // Show preview only if item is not yet verified
            if (claimStatus !== 'verified') {
                const description = previewContext.description || 'No description provided';
                const location = previewContext.location || 'Location not specified';
                const statusLabel = (previewContext.item_type || previewContext.itemType || 'item').toString();
                const uploadId = previewContext.upload_id || previewContext.uploadId;
                const tags = Array.isArray(previewContext.tags) ? previewContext.tags : [];
                const claimBadge = claimStatus === 'pending'
                    ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⏳ Claim Pending</span>'
                    : '';
                const images = Array.isArray(previewContext.images) ? previewContext.images : [];
                const firstImage = images.length ? images[0] : null;
                const imagePreviewHtml = firstImage && firstImage.path
                    ? `<div class="mb-2 -mx-2 -mt-2 first:mt-0"><img src="${firstImage.path}" alt="Item image" class="w-full h-40 object-cover rounded-t-lg"></div>`
                    : '';

                itemContextHtml = `
                    <div class="mt-2 bg-white text-gray-900 rounded-2xl border border-gray-200 p-3 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-purple-600">Item Details</span>
                            ${claimBadge}
                        </div>
                        ${imagePreviewHtml}
                        <p class="text-sm font-medium mb-1">${escapeHtml(description)}</p>
                        <p class="text-xs text-gray-600 mb-2"><i class="fas fa-map-marker-alt mr-1"></i>${escapeHtml(location)}</p>
                        <p class="text-xs text-gray-600 mb-2"><i class="fas fa-tag mr-1"></i>${escapeHtml(statusLabel)}</p>
                        ${tags.length ? `<div class="flex flex-wrap gap-1 mb-2">${tags.map(tag => `<span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full text-[10px] font-medium">${escapeHtml(tag)}</span>`).join('')}</div>` : ''}
                        ${uploadId ? `<a href="/item/${uploadId}" target="_blank" class="text-xs text-purple-600 hover:text-purple-800 font-medium inline-flex items-center">
                            View item <i class="fas fa-external-link-alt ml-1"></i>
                        </a>` : ''}
                    </div>
                `;
            }
        }
    }

    // Avatar HTML (only show for received messages)
    const avatarHtml = !isOwnMessage ? `
        <div class="flex-shrink-0 w-8 h-8">
            ${senderAvatar ? `
                <img src="${senderAvatar}" alt="${senderName}" class="w-8 h-8 rounded-full object-cover border-2 border-gray-200">
            ` : `
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center border-2 border-gray-200">
                    <span class="text-xs font-semibold text-white">${senderInitials}</span>
                </div>
            `}
        </div>
    ` : '';

    // Message bubble with speech bubble tail
    const bubbleClass = isOwnMessage 
        ? 'bg-gradient-to-br from-[#0a7bff] to-[#1d8dff] text-white rounded-2xl rounded-tr-sm shadow-md' 
        : 'bg-[#e9e9eb] text-gray-900 rounded-2xl rounded-tl-sm shadow-sm border border-gray-200';
    
    // Image HTML
    let imageHtml = '';
    if (message.image_path) {
        const canView = message.can_view_image !== false;
        const isExpired = message.is_expired === true;
        const viewOption = message.view_option || 'keep';
        const viewCount = message.view_count || 0;
        
        if (isExpired || !canView) {
            imageHtml = `
                <div class="mb-2 p-4 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 text-center">
                    <i class="fas fa-image text-gray-400 text-2xl mb-2"></i>
                    <p class="text-xs text-gray-500">This image has expired</p>
                </div>
            `;
        } else {
            imageHtml = `
                <div class="mb-2 relative">
                    <img src="${message.image_path}" 
                         alt="Shared image" 
                         class="max-w-full max-h-64 rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                         onclick="viewImage(${message.id}, '${message.image_path}')"
                         data-message-id="${message.id}">
                    ${viewOption !== 'keep' ? `
                        <div class="absolute top-2 right-2 bg-black/50 text-white text-xs px-2 py-1 rounded">
                            ${viewOption === 'once' ? 'View Once' : 'View Twice'} (${viewCount}/${viewOption === 'once' ? '1' : '2'})
                        </div>
                    ` : ''}
                </div>
            `;
        }
    }
    
    messageDiv.innerHTML = `
        ${avatarHtml}
        <div class="flex flex-col ${isOwnMessage ? 'items-end' : 'items-start'} max-w-[85%] sm:max-w-[70%] md:max-w-[65%] lg:max-w-[55%]">
            ${!isOwnMessage ? `<span class="text-xs text-gray-500 mb-1 px-2">${escapeHtml(senderName)}</span>` : ''}
            <div class="${bubbleClass} px-3 sm:px-4 py-2 sm:py-2.5 relative group">
                ${imageHtml}
                ${message.message && message.message.trim() ? `<p class="text-sm leading-relaxed whitespace-pre-wrap break-words">${escapeHtml(message.message)}</p>` : ''}
                ${itemContextHtml}
                ${!imageHtml && !message.message && !itemContextHtml ? '<p class="text-sm opacity-70">Message</p>' : ''}
                <div class="flex items-center justify-end gap-1.5 mt-1.5">
                    <span class="text-[10px] ${isOwnMessage ? 'text-white/70' : 'text-gray-400'}">
                        ${timeString}
                    </span>
                    ${isOwnMessage ? `
                        <i class="fas fa-check text-[10px] ${message.is_read ? 'text-blue-300' : 'text-white/50'}"></i>
                    ` : ''}
                </div>
            </div>
        </div>
    `;

    return messageDiv;
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Auto-resize textarea
const messageInput = document.getElementById('message-input');
if (messageInput) {
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        
        // Show character count if typing
        const charCount = document.getElementById('char-count');
        if (charCount) {
            if (this.value.length > 0) {
                charCount.textContent = `${this.value.length}/1000`;
                charCount.classList.remove('hidden');
            } else {
                charCount.classList.add('hidden');
            }
        }
    });
}

// Handle image selection
let selectedImage = null;
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

// View image and record view
async function viewImage(messageId, imagePath) {
    // Record the view
    try {
        const response = await fetch(`/chat/image-view/${messageId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        if (data.success) {
            // Update the message element if view limit reached
            if (data.is_expired || !data.can_view_image) {
                const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
                if (messageElement) {
                    const img = messageElement.querySelector('img[data-message-id]');
                    if (img) {
                        img.parentElement.innerHTML = `
                            <div class="mb-2 p-4 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 text-center">
                                <i class="fas fa-image text-gray-400 text-2xl mb-2"></i>
                                <p class="text-xs text-gray-500">This image has expired</p>
                            </div>
                        `;
                    }
                }
            }
        }
    } catch (error) {
        console.error('Error recording image view:', error);
    }
    
    // Show image in modal/lightbox
    showImageModal(imagePath);
}

// Show image in modal
function showImageModal(imagePath) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="relative max-w-4xl max-h-full">
            <img src="${imagePath}" alt="Image" class="max-w-full max-h-[90vh] rounded-lg">
            <button onclick="this.closest('.fixed').remove()" class="absolute top-4 right-4 bg-white/20 hover:bg-white/30 text-white rounded-full p-2">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    modal.onclick = function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    };
    document.body.appendChild(modal);
}

// Send message
document.getElementById('message-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();
    const imageInput = document.getElementById('image-input');
    const viewOption = document.querySelector('input[name="view_option"]:checked')?.value || null;

    if ((!message && !selectedImage) || !currentUserId) return;
    
    // Reset textarea height
    messageInput.style.height = 'auto';

    try {
        const formData = new FormData();
        formData.append('receiver_id', currentUserId);
        formData.append('message', message || '');
        if (selectedImage) {
            formData.append('image', selectedImage);
            formData.append('view_option', viewOption || 'keep');
        }
        if (itemContext) {
            formData.append('item_upload_id', itemContext.uploadId || '');
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
            // Clear input immediately for better UX
            const messageText = messageInput.value.trim();
            messageInput.value = '';
            messageInput.style.height = 'auto';
            
            // Clear image preview if any
            removeImagePreview();
            
            // Hide character count
            const charCount = document.getElementById('char-count');
            if (charCount) {
                charCount.classList.add('hidden');
            }

            // Update item context if the response includes it (e.g., claim message)
            if (data.message && data.message.item_context) {
                let responseContext = data.message.item_context;
                if (typeof responseContext === 'string') {
                    try {
                        responseContext = JSON.parse(responseContext);
                    } catch (parseError) {
                        console.error('Failed to parse item_context from response:', parseError);
                        responseContext = null;
                    }
                }

                if (responseContext && (responseContext.claim_status !== 'verified')) {
                    itemContext = responseContext;
                    sessionStorage.setItem('chatItemContext', JSON.stringify(itemContext));
                    showItemContext();
                }
            }

            // Add message to UI immediately for sender (optimistic update)
            if (data.message) {
                // Format the message to match expected structure
                let created_at = data.message.created_at;
                if (!created_at) {
                    created_at = new Date().toISOString();
                } else if (typeof created_at === 'object' && created_at.date) {
                    created_at = created_at.date;
                }

                const formattedMessage = {
                    id: data.message.id,
                    sender_id: data.message.sender_id,
                    receiver_id: data.message.receiver_id,
                    message: data.message.message || messageText,
                    item_upload_id: data.message.item_upload_id,
                    item_context: data.message.item_context,
                    is_read: data.message.is_read || false,
                    read_at: data.message.read_at,
                    created_at: created_at,
                    sender: data.message.sender || { id: data.message.sender_id },
                    receiver: data.message.receiver || { id: data.message.receiver_id }
                };

                console.log('Adding sent message to UI:', formattedMessage);
                addMessageToUI(formattedMessage);
            } else {
                console.error('No message data in response:', data);
            }
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

// Add message to UI without reloading
function addMessageToUI(message) {
    if (!message || !message.id) {
        console.error('Invalid message data:', message);
        return;
    }
    
    const messagesList = document.getElementById('messages-list');
    if (!messagesList) {
        console.error('Messages list element not found');
        return;
    }
    
    // Check if message already exists to prevent duplicates
    const existingMessage = document.querySelector(`[data-message-id="${message.id}"]`);
    if (existingMessage) {
        console.log('Message already exists, skipping:', message.id);
        return;
    }
    
    const messageElement = createMessageElement(message);
    messagesList.appendChild(messageElement);
    
    // Scroll to bottom smoothly
    const messagesContainer = document.getElementById('messages-container');
    if (messagesContainer) {
        messagesContainer.scrollTo({
            top: messagesContainer.scrollHeight,
            behavior: 'smooth'
        });
    }
}

// Mark message as read
async function markMessageAsRead(messageId) {
    try {
        await fetch(`/chat/messages/${currentUserId}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
    } catch (error) {
        console.error('Error marking message as read:', error);
    }
}

// Update unread count in sidebar
function updateUnreadCount() {
    // This will be called when a new message arrives
    // You can implement a fetch to get the latest unread count
    fetch('/chat/unread-count')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update unread count display if needed
                console.log('Unread count:', data.unread_count);
            }
        })
        .catch(error => console.error('Error fetching unread count:', error));
}

// Update conversation list when new message arrives
function updateConversationList(message) {
    // Find the conversation in the sidebar and update it
    const otherUserId = message.sender_id == {{ Auth::id() }} ? message.receiver_id : message.sender_id;
    const userItem = document.querySelector(`[data-user-id="${otherUserId}"]`);
    if (userItem) {
        // Update last message preview
        const lastMessageElement = userItem.querySelector('p.text-gray-500:not(.text-xs)');
        if (lastMessageElement) {
            lastMessageElement.textContent = message.message;
        }
        
        // Update timestamp
        const timeElement = userItem.querySelector('p.text-xs.text-gray-400');
        if (timeElement) {
            const messageTime = new Date(message.created_at);
            timeElement.textContent = messageTime.toLocaleTimeString();
        }
        
        // Update unread count if message is from other user
        if (message.sender_id != {{ Auth::id() }}) {
            const unreadBadge = userItem.querySelector('.inline-flex.items-center.px-2');
            if (unreadBadge) {
                const currentCount = parseInt(unreadBadge.textContent) || 0;
                unreadBadge.textContent = currentCount + 1;
                unreadBadge.classList.remove('hidden');
            } else {
                // Create unread badge if it doesn't exist
                const badge = document.createElement('span');
                badge.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800';
                badge.textContent = '1';
                userItem.querySelector('.flex.items-center.justify-between').appendChild(badge);
            }
        }
    }
}

// Clean up interval when page is unloaded
window.addEventListener('beforeunload', function() {
    if (messageInterval) {
        clearInterval(messageInterval);
    }
    
    // Disconnect Echo
    if (typeof window.Echo !== 'undefined') {
        window.Echo.disconnect();
    }
});

// Item Context Functions
let itemContext = null;

// Check for item context on page load and initialize WebSocket
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
        // Wait a bit for DOM to be fully ready, then select user
        setTimeout(() => {
            selectUser(parseInt(userId));
            // Show item context if available and not verified
            if (itemContext) {
                // Check if item is verified - if so, don't show context
                if (itemContext.claim_status === 'verified' || itemContext.claimStatus === 'verified') {
                    hideItemContext();
                } else {
                    setTimeout(() => showItemContext(), 100);
                }
            }
        }, 100);
    }
    
    // Show privacy warning when conversation opens
    showPrivacyWarning();

    // Initialize Laravel Echo for real-time messaging
    if (typeof window.Echo !== 'undefined' && window.Echo) {
        try {
            const currentUser = @json(Auth::user());
            
            if (!currentUser || !currentUser.id) {
                console.error('Current user not available for Echo initialization');
                return;
            }
            
            console.log('Initializing Laravel Echo for user:', currentUser.id);
            
            // Listen for new messages on the current user's private channel
            const channel = window.Echo.private(`user.${currentUser.id}`);
        
        channel.listen('.message.sent', (e) => {
            console.log('Received message event:', e);
            const message = e;
            
            // Only add message if it's for the currently open conversation
            if (currentUserId && (message.sender_id == currentUserId || message.receiver_id == currentUserId)) {
                // Check if message already exists in UI (prevent duplicates)
                const existingMessage = document.querySelector(`[data-message-id="${message.id}"]`);
                if (!existingMessage) {
                    console.log('Adding message to UI:', message);
                    addMessageToUI(message);
                    
                    // Check if message has item_context and update item context display
                    if (message.item_context) {
                        let messageItemContext = message.item_context;
                        if (typeof messageItemContext === 'string') {
                            try {
                                messageItemContext = JSON.parse(messageItemContext);
                            } catch (parseError) {
                                console.error('Failed to parse item_context from message:', parseError);
                                messageItemContext = null;
                            }
                        }
                        
                        if (messageItemContext && typeof messageItemContext === 'object') {
                            // Check if item is verified - if so, don't show context
                            if (messageItemContext.claim_status !== 'verified') {
                                console.log('Updating item context from received message:', messageItemContext);
                                itemContext = messageItemContext;
                                sessionStorage.setItem('chatItemContext', JSON.stringify(itemContext));
                                showItemContext();
                            } else {
                                console.log('Item is verified, hiding context');
                                hideItemContext();
                            }
                        }
                    }
                    
                    // Mark as read if it's the current conversation and user is the receiver
                    if (message.receiver_id == currentUser.id && message.sender_id == currentUserId) {
                        markMessageAsRead(message.id);
                    }
                } else {
                    console.log('Message already exists in UI, skipping:', message.id);
                }
            } else {
                // Update unread count in sidebar if message is not for current conversation
                console.log('Message not for current conversation, updating unread count');
                updateUnreadCount();
            }
            
            // Update conversation list
            updateConversationList(message);
        });
        
        // Log connection status
        channel.subscribed(() => {
            console.log('Successfully subscribed to private channel: user.' + currentUser.id);
        });
        
        channel.error((error) => {
            console.error('Echo subscription error:', error);
        });

        // Listen for item claim events
        channel.listen('.item.claimed', (e) => {
            console.log('Item claimed event received:', e);
            // Check if this is for the current conversation
            const isCurrentConversation = currentUserId && (
                (e.claimer_id == currentUser.id && e.owner_id == currentUserId) ||
                (e.owner_id == currentUser.id && e.claimer_id == currentUserId)
            );
            
            if (isCurrentConversation) {
                // Always reload messages to get the item context from the claim message
                // This ensures both users see the item context
                if (currentUserId) {
                    console.log('Reloading messages to show item context after claim');
                    loadMessages(currentUserId);
                }
            }
        });

        // Listen for item claim verified events
        channel.listen('.item.claim.verified', (e) => {
            console.log('Item claim verified event received:', e);
            // Check if this is for the current conversation
            const isCurrentConversation = currentUserId && (
                (e.claimer_id == currentUser.id && e.owner_id == currentUserId) ||
                (e.owner_id == currentUser.id && e.claimer_id == currentUserId)
            );
            
            if (isCurrentConversation) {
                if (itemContext && (itemContext.upload_id === e.upload_id || itemContext.uploadId === e.upload_id)) {
                    // Hide item context since item is now verified/claimed
                    hideItemContext();
                    showNotification('This item has been claimed and verified. Item context removed from chat.', 'info');
                } else if (currentUserId) {
                    // Reload messages to ensure context is removed
                    loadMessages(currentUserId);
                }
            }
        });

        // Listen for item deleted events
        channel.listen('.item.deleted', (e) => {
            console.log('Item deleted event received:', e);
            if (itemContext && (itemContext.upload_id === e.upload_id || itemContext.uploadId === e.upload_id)) {
                // Hide item context since item is deleted
                hideItemContext();
                showNotification('This item has been deleted. Item context removed from chat.', 'info');
            }
        });
        } catch (error) {
            console.error('Error initializing Laravel Echo:', error);
            console.warn('Real-time messaging will not work. Make sure Pusher credentials are configured in your .env file (VITE_PUSHER_APP_KEY, VITE_PUSHER_APP_CLUSTER, etc.)');
        }
    } else {
        console.warn('Laravel Echo is not available. Real-time messaging will not work. Make sure to include the app.js script and that Pusher credentials are configured in your .env file.');
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
    const claimStatus = itemContext.claim_status || itemContext.claimStatus;
    const currentUserId = {{ Auth::id() }};
    const claimedById = itemContext.claimed_by_id || itemContext.claimedById;
    
    // Determine if item is claimed and by whom
    let claimBadge = '';
    if (claimStatus === 'pending') {
        if (claimedById == currentUserId) {
            claimBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">⏳ Claim Pending (You claimed this)</span>';
        } else {
            claimBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">⏳ Claim Pending</span>';
        }
    } else if (claimStatus === 'verified') {
        claimBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">✅ Claim Verified</span>';
    }
    
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
                        ${claimBadge}
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
        messageInput.focus();
    }
}

// Hide privacy warning (temporary, will show again on next conversation)
function hidePrivacyWarning() {
    const privacyWarning = document.getElementById('privacy-warning');
    if (privacyWarning) {
        privacyWarning.classList.add('hidden');
    }
}

// Show privacy warning when conversation opens
function showPrivacyWarning() {
    const privacyWarning = document.getElementById('privacy-warning');
    if (privacyWarning) {
        privacyWarning.classList.remove('hidden');
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
    if (messageInput) {
        messageInput.placeholder = 'Type a message...';
        messageInput.focus();
    }

    // Clean URL
    const url = new URL(window.location);
    url.searchParams.delete('user');
    url.searchParams.delete('item');
    window.history.replaceState({}, '', url);
}
</script>
@endsection
