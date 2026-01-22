<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FindITFast')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="antialiased bg-gray-100" x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }" x-init="$watch('sidebarCollapsed', value => localStorage.setItem('sidebarCollapsed', value))">
    <div class="flex h-screen overflow-hidden bg-gray-100">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden"
             style="display: none;"></div>

        <!-- Sidebar -->
        <div :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                sidebarCollapsed ? 'lg:w-20' : 'lg:w-64'
             ]"
             class="fixed lg:static inset-y-0 left-0 z-50 bg-white shadow-lg transform transition-all duration-300 ease-in-out overflow-y-auto">
            <!-- Logo -->
            <div class="p-4 sm:p-6 border-b border-gray-200 flex items-center justify-between">
                <h1 class="text-xl sm:text-2xl font-bold transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">
                    <span class="text-purple-primary">FindIT</span>
                    <span class="text-pink-primary">Fast</span>
                </h1>
                <div class="flex items-center gap-2">
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:block text-gray-600 hover:text-gray-900 p-1 rounded hover:bg-gray-100">
                        <i class="fas fa-chevron-left" :class="sidebarCollapsed ? 'rotate-180' : ''"></i>
                    </button>
                    <button @click="sidebarOpen = false" class="lg:hidden text-gray-600 hover:text-gray-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="mt-4 sm:mt-6">
                <ul class="space-y-2 px-2 sm:px-4">
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isDashboard = ($currentRoute === 'dashboard' || $currentPath === 'dashboard') 
                                && $currentPath !== 'reported-items' 
                                && !str_starts_with($currentPath, 'reported-items')
                                && !str_starts_with($currentPath, 'claim-verify')
                                && !str_starts_with($currentPath, 'profile')
                                && !str_starts_with($currentPath, 'chat');
                        @endphp
                        <a href="{{ route('dashboard') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ ($isDashboard ?? false) ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Dashboard' : ''">
                            <i class="fas fa-th-large w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isReportedItems = ($currentRoute === 'reported-items' || str_starts_with($currentPath, 'reported-items')) 
                                && !str_starts_with($currentPath, 'dashboard')
                                && !str_starts_with($currentPath, 'claim-verify')
                                && !str_starts_with($currentPath, 'profile')
                                && !str_starts_with($currentPath, 'chat');
                        @endphp
                        <a href="{{ route('reported-items') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isReportedItems ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Reported Items' : ''">
                            <i class="fas fa-briefcase w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Reported Items</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentPath = request()->path();
                            $isClaimVerify = str_starts_with($currentPath, 'claim-verify');
                        @endphp
                        <a href="{{ route('claim-verify') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isClaimVerify ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Claim and Verify' : ''">
                            <i class="fas fa-check-circle w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Claim and Verify</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentPath = request()->path();
                            $isPendingClaims = str_starts_with($currentPath, 'pending-claims');
                        @endphp
                        <a href="{{ route('pending-claims') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isPendingClaims ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Pending Claims' : ''">
                            <i class="fas fa-clock w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Pending Claims</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isChat = (str_starts_with($currentRoute, 'chat') || str_starts_with($currentPath, 'chat')) 
                                && !str_starts_with($currentPath, 'dashboard')
                                && !str_starts_with($currentPath, 'reported-items')
                                && !str_starts_with($currentPath, 'claim-verify')
                                && !str_starts_with($currentPath, 'profile');
                        @endphp
                        <a href="{{ route('chat') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isChat ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Messages' : ''">
                            <i class="fas fa-comments w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Messages</span>
                            <span id="messages-unread-badge" class="ml-2 px-2 py-0.5 bg-red-500 text-white text-xs font-semibold rounded-full hidden" :class="sidebarCollapsed ? 'lg:hidden' : ''">0</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isProfile = (str_starts_with($currentRoute, 'profile') || str_starts_with($currentPath, 'profile')) 
                                && !str_starts_with($currentPath, 'dashboard')
                                && !str_starts_with($currentPath, 'reported-items')
                                && !str_starts_with($currentPath, 'claim-verify')
                                && !str_starts_with($currentPath, 'chat');
                        @endphp
                        <a href="{{ route('profile') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isProfile ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Profile' : ''">
                            <i class="fas fa-user w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Profile</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Logout Button -->
            <div class="mt-auto p-3 sm:p-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base text-red-600 hover:bg-red-50 transition-colors" :title="sidebarCollapsed ? 'Logout' : ''">
                        <i class="fas fa-sign-out-alt w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                        <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-3 sm:px-4 md:px-6 py-3 sm:py-4">
                <div class="flex items-center justify-between gap-3">
                    <!-- Menu Toggle Button -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:block p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Search Bar -->
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <input type="text"
                                   placeholder="Search here"
                                   class="w-full pl-3 sm:pl-4 pr-8 sm:pr-10 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent">
                            <i class="fas fa-search absolute right-2 sm:right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                        </div>
                    </div>

                    <!-- Right Side -->
                    <div class="flex items-center space-x-2 sm:space-x-4">
                        <!-- Notifications -->
                        <div class="relative" x-data="{ open: false, unread: 0 }" @click.away="open = false">
                            <button class="relative p-2 text-gray-600 hover:text-purple-primary" @click="open = !open; if(open){ loadNotifications(); }">
                                <i class="fas fa-bell w-4 h-4 sm:w-5 sm:h-5"></i>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 sm:w-5 sm:h-5 flex items-center justify-center" x-text="unread" x-show="unread > 0"></span>
                            </button>
                            <div x-show="open" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50" style="display:none;">
                                <div class="px-4 py-2 border-b flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-700">Notifications</span>
                                    <button onclick="markAllNotificationsRead()" class="text-xs text-purple-primary hover:underline">Mark all read</button>
                                </div>
                                <div id="notif-list" class="max-h-80 overflow-y-auto">
                                    <div class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                        <p class="text-sm">Loading notifications...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 sm:space-x-3 hover:bg-gray-50 px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg transition-colors">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full overflow-hidden bg-purple-primary flex items-center justify-center">
                                    @if(auth()->check() && auth()->user()->profile_picture)
                                        <img src="{{ auth()->user()->profile_picture }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-user text-white text-xs sm:text-sm"></i>
                                    @endif
                                </div>
                                <div class="hidden sm:block">
                                    <p class="text-xs sm:text-sm font-medium text-gray-900">{{ auth()->check() ? auth()->user()->name : 'Guest' }}</p>
                                    <p class="text-xs text-gray-500">{{ auth()->check() ? Str::limit(auth()->user()->email, 20) : '' }}</p>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 text-xs hidden sm:block"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open"
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                                 style="display: none;">
                                <a href="{{ route('profile') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-3 text-gray-400"></i>
                                    Profile
                                </a>
                                <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-cog mr-3 text-gray-400"></i>
                                    Settings
                                </a>
                                <hr class="my-1">
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <i class="fas fa-sign-out-alt mr-3"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6 overflow-x-hidden">
                <div class="max-w-full">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        // Fetch and update unread message count
        function updateUnreadMessageCount() {
            fetch('{{ route("chat.unread-count") }}')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('messages-unread-badge');
                    if (data.success && data.unread_count > 0) {
                        badge.textContent = data.unread_count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error fetching unread count:', error);
                });
        }

        // Load and display notifications
        function loadNotifications() {
            const list = document.getElementById('notif-list');
            list.innerHTML = '<div class="px-4 py-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><p class="text-sm">Loading notifications...</p></div>';
            
            fetch('/api/notifications?limit=20')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update unread count in Alpine.js
                        const alpineData = Alpine.$data(document.querySelector('[x-data*="unread"]'));
                        if (alpineData) {
                            alpineData.unread = data.unread || 0;
                        }
                        
                        list.innerHTML = '';
                        if (data.notifications && data.notifications.length > 0) {
                            data.notifications.forEach(n => {
                                const div = document.createElement('div');
                                div.className = 'px-4 py-3 hover:bg-gray-50 border-b border-gray-100' + (n.is_read ? '' : ' bg-blue-50');
                                
                                const icon = n.type === 'item_uploaded' ? 'fa-check-circle text-green-500' : 
                                            n.type === 'item_match' ? 'fa-search text-blue-500' : 
                                            n.type === 'item_matched' ? 'fa-link text-purple-500' :
                                            n.type === 'item_claimed' ? 'fa-hand-holding text-purple-500' : 
                                            'fa-bell text-gray-500';
                                
                                // Get notification data
                                const notifData = n.data || {};
                                let itemDetails = '';
                                let viewButton = '';
                                let itemUrl = null;
                                
                                // Determine item URL based on notification type
                                if (n.type === 'item_matched' && notifData.new_item_upload_id) {
                                    // Redirect to Claim and Verify page to see the matched item
                                    itemUrl = `/claim-verify`;
                                    const similarityPercent = notifData.similarity_percent || notifData.similarity_score ? 
                                        (notifData.similarity_percent || (notifData.similarity_score * 100).toFixed(2)) : 'N/A';
                                    itemDetails = `
                                        <div class="mt-2 p-2 bg-purple-50 rounded border border-purple-200">
                                            <div class="text-xs font-semibold text-purple-700 mb-1">Similar Item Found!</div>
                                            <div class="text-xs text-gray-700">
                                                <div><strong>Type:</strong> ${notifData.new_item_type || 'N/A'}</div>
                                                <div><strong>Description:</strong> ${(notifData.new_item_description || '').substring(0, 50)}${(notifData.new_item_description || '').length > 50 ? '...' : ''}</div>
                                                <div><strong>Location:</strong> ${notifData.new_item_location || 'N/A'}</div>
                                                <div><strong>Similarity:</strong> ${similarityPercent}%</div>
                                            </div>
                                        </div>
                                    `;
                                    viewButton = `
                                        <button onclick="event.stopPropagation(); window.location.href='${itemUrl}'; markNotificationRead(${n.id});" 
                                                class="mt-2 px-3 py-1 bg-purple-500 text-white text-xs rounded hover:bg-purple-600 transition-colors">
                                            View in Claim & Verify
                                        </button>
                                    `;
                                } else if (n.type === 'item_match' && notifData.similar_items && notifData.similar_items.length > 0) {
                                    // For item_match notifications, redirect to Claim and Verify page
                                    itemUrl = `/claim-verify`;
                                    const similarCount = notifData.similar_items_count || notifData.similar_items.length;
                                    itemDetails = `
                                        <div class="mt-2 p-2 bg-blue-50 rounded border border-blue-200">
                                            <div class="text-xs font-semibold text-blue-700 mb-1">Similar Items Found!</div>
                                            <div class="text-xs text-gray-700">
                                                We found ${similarCount} similar item(s) that match your reported item. Check the Claim & Verify page to view them.
                                            </div>
                                        </div>
                                    `;
                                    viewButton = `
                                        <button onclick="event.stopPropagation(); window.location.href='${itemUrl}'; markNotificationRead(${n.id});" 
                                                class="mt-2 px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition-colors">
                                            View in Claim & Verify
                                        </button>
                                    `;
                                } else if (n.type === 'item_match' && notifData.upload_id) {
                                    // Fallback for item_match - redirect to Claim and Verify
                                    itemUrl = `/claim-verify`;
                                } else if (n.type === 'item_uploaded' && notifData.upload_id) {
                                    itemUrl = `/item/${notifData.upload_id}`;
                                } else if (n.type === 'item_claimed' && notifData.upload_id) {
                                    itemUrl = `/item/${notifData.upload_id}`;
                                }
                                
                                // For item_matched, redirect to Claim and Verify to see matched items
                                if (n.type === 'item_matched' && notifData.matched_item_upload_id && !itemUrl) {
                                    itemUrl = `/claim-verify`;
                                }
                                
                                div.innerHTML = `
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <i class="fas ${icon}"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-900">${n.title || 'Notification'}</div>
                                            <div class="text-xs text-gray-500 mt-1">${n.message || ''}</div>
                                            ${itemDetails}
                                            ${viewButton}
                                            <div class="text-xs text-gray-400 mt-2">${new Date(n.created_at).toLocaleString()}</div>
                                        </div>
                                        ${!n.is_read ? '<div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></div>' : ''}
                                    </div>
                                `;
                                
                                // Make the notification clickable - navigate to item if URL exists, otherwise just mark as read
                                div.onclick = (e) => {
                                    // Don't navigate if clicking on the view button (it handles its own navigation)
                                    if (e.target.closest('button')) {
                                        return;
                                    }
                                    
                                    if (itemUrl) {
                                        markNotificationRead(n.id);
                                        window.location.href = itemUrl;
                                    } else {
                                        markNotificationRead(n.id);
                                    }
                                };
                                
                                // Add cursor pointer if there's a link
                                if (itemUrl) {
                                    div.style.cursor = 'pointer';
                                    div.classList.add('hover:bg-gray-100');
                                }
                                
                                list.appendChild(div);
                            });
                        } else {
                            list.innerHTML = '<div class="px-4 py-8 text-center text-gray-500"><i class="fas fa-bell-slash text-2xl mb-2"></i><p class="text-sm">No notifications</p></div>';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    list.innerHTML = '<div class="px-4 py-8 text-center text-red-500"><p class="text-sm">Error loading notifications</p></div>';
                });
        }

        // Mark single notification as read
        function markNotificationRead(id) {
            fetch('/api/notifications/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ id: id })
            })
            .then(() => {
                loadNotifications();
                updateUnreadNotificationCount();
            });
        }

        // Mark all notifications as read
        function markAllNotificationsRead() {
            fetch('/api/notifications/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(() => {
                loadNotifications();
                updateUnreadNotificationCount();
            });
        }

        // Update unread notification count badge
        function updateUnreadNotificationCount() {
            fetch('/api/notifications')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const alpineData = Alpine.$data(document.querySelector('[x-data*="unread"]'));
                        if (alpineData) {
                            alpineData.unread = data.unread || 0;
                        }
                    }
                })
                .catch(error => console.error('Error updating notification count:', error));
        }

        // Update on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateUnreadMessageCount();
            updateUnreadNotificationCount();
            // Update every 30 seconds
            setInterval(() => {
                updateUnreadMessageCount();
                updateUnreadNotificationCount();
            }, 30000);
        });
    </script>

</body>
</html>
