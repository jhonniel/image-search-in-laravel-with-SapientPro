<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FindITFast Admin')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="antialiased bg-gray-100" x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('adminSidebarCollapsed') === 'true' }" x-init="$watch('sidebarCollapsed', value => localStorage.setItem('adminSidebarCollapsed', value))">
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
                                && !str_starts_with($currentPath, 'reported-items-admin')
                                && !str_starts_with($currentPath, 'claimed')
                                && !str_starts_with($currentPath, 'users')
                                && !str_starts_with($currentPath, 'insights')
                                && !str_starts_with($currentPath, 'settings')
                                && !str_starts_with($currentPath, 'sponsors')
                                && !str_starts_with($currentPath, 'rewards')
                                && !str_starts_with($currentPath, 'image-comparison');
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
                            $isReportedItems = (in_array($currentRoute, ['reported-items-admin', 'delete-item']) || str_starts_with($currentPath, 'reported-items-admin')) 
                                && !str_starts_with($currentPath, 'dashboard')
                                && !str_starts_with($currentPath, 'claimed')
                                && !str_starts_with($currentPath, 'users')
                                && !str_starts_with($currentPath, 'insights')
                                && !str_starts_with($currentPath, 'settings')
                                && !str_starts_with($currentPath, 'sponsors')
                                && !str_starts_with($currentPath, 'rewards')
                                && !str_starts_with($currentPath, 'image-comparison');
                        @endphp
                        <a href="{{ route('reported-items-admin') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isReportedItems ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Reported Items' : ''">
                            <i class="fas fa-briefcase w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Reported Items</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isClaimVerify = ($currentRoute === 'claimed' || str_starts_with($currentPath, 'claimed')) 
                                && !str_starts_with($currentPath, 'dashboard')
                                && !str_starts_with($currentPath, 'reported-items-admin')
                                && !str_starts_with($currentPath, 'users')
                                && !str_starts_with($currentPath, 'insights')
                                && !str_starts_with($currentPath, 'settings')
                                && !str_starts_with($currentPath, 'sponsors')
                                && !str_starts_with($currentPath, 'rewards')
                                && !str_starts_with($currentPath, 'image-comparison');
                        @endphp
                        <a href="{{ route('claimed') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isClaimVerify ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Claimed' : ''">
                            <i class="fas fa-check-circle w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Claimed</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isUsers = (str_starts_with($currentRoute, 'users') || str_starts_with($currentPath, 'users')) 
                                && !str_starts_with($currentPath, 'dashboard')
                                && !str_starts_with($currentPath, 'reported-items-admin')
                                && !str_starts_with($currentPath, 'claimed')
                                && !str_starts_with($currentPath, 'insights')
                                && !str_starts_with($currentPath, 'settings')
                                && !str_starts_with($currentPath, 'sponsors')
                                && !str_starts_with($currentPath, 'rewards')
                                && !str_starts_with($currentPath, 'image-comparison');
                        @endphp
                        <a href="{{ route('users') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isUsers ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Users' : ''">
                            <i class="fas fa-users w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Users</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isInsights = ($currentRoute === 'insights' || str_starts_with($currentPath, 'insights')) 
                                && !str_starts_with($currentPath, 'dashboard')
                                && !str_starts_with($currentPath, 'reported-items-admin')
                                && !str_starts_with($currentPath, 'claimed')
                                && !str_starts_with($currentPath, 'users')
                                && !str_starts_with($currentPath, 'settings')
                                && !str_starts_with($currentPath, 'sponsors')
                                && !str_starts_with($currentPath, 'rewards')
                                && !str_starts_with($currentPath, 'image-comparison');
                        @endphp
                        <a href="{{ route('insights') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isInsights ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Insights' : ''">
                            <i class="fas fa-chart-line w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Insights</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isRewards = (str_starts_with($currentRoute, 'rewards') || str_starts_with($currentPath, 'rewards')) 
                                && !str_starts_with($currentPath, 'dashboard')
                                && !str_starts_with($currentPath, 'reported-items-admin')
                                && !str_starts_with($currentPath, 'claimed')
                                && !str_starts_with($currentPath, 'users')
                                && !str_starts_with($currentPath, 'insights')
                                && !str_starts_with($currentPath, 'settings')
                                && !str_starts_with($currentPath, 'sponsors')
                                && !str_starts_with($currentPath, 'image-comparison');
                        @endphp
                        <a href="{{ route('rewards.index') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isRewards ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Rewards' : ''">
                            <i class="fas fa-gift w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Rewards</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isSettings = (str_starts_with($currentRoute, 'settings') || str_starts_with($currentPath, 'settings')) 
                                && !str_starts_with($currentPath, 'dashboard')
                                && !str_starts_with($currentPath, 'reported-items-admin')
                                && !str_starts_with($currentPath, 'claimed')
                                && !str_starts_with($currentPath, 'users')
                                && !str_starts_with($currentPath, 'insights')
                                && !str_starts_with($currentPath, 'sponsors')
                                && !str_starts_with($currentPath, 'rewards')
                                && !str_starts_with($currentPath, 'image-comparison');
                        @endphp
                        <a href="{{ route('settings') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isSettings ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Settings' : ''">
                            <i class="fas fa-cog w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Settings</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isSponsors = (str_starts_with($currentRoute, 'sponsors') || str_starts_with($currentPath, 'sponsors')) 
                                && !str_starts_with($currentPath, 'dashboard')
                                && !str_starts_with($currentPath, 'reported-items-admin')
                                && !str_starts_with($currentPath, 'claimed')
                                && !str_starts_with($currentPath, 'users')
                                && !str_starts_with($currentPath, 'insights')
                                && !str_starts_with($currentPath, 'settings')
                                && !str_starts_with($currentPath, 'rewards')
                                && !str_starts_with($currentPath, 'image-comparison');
                        @endphp
                        <a href="{{ route('sponsors.index') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isSponsors ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Sponsors' : ''">
                            <i class="fas fa-handshake w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Sponsors</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isContributors = (str_starts_with($currentRoute, 'contributors') || str_starts_with($currentPath, 'admin/contributors')) 
                                && !str_starts_with($currentPath, 'dashboard')
                                && !str_starts_with($currentPath, 'reported-items-admin')
                                && !str_starts_with($currentPath, 'claimed')
                                && !str_starts_with($currentPath, 'users')
                                && !str_starts_with($currentPath, 'insights')
                                && !str_starts_with($currentPath, 'settings')
                                && !str_starts_with($currentPath, 'sponsors')
                                && !str_starts_with($currentPath, 'rewards')
                                && !str_starts_with($currentPath, 'image-comparison');
                        @endphp
                        <a href="{{ route('contributors.index') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isContributors ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Contributors' : ''">
                            <i class="fas fa-users-cog w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Contributors</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isContactRequests = (str_starts_with($currentRoute, 'contact-requests') || str_starts_with($currentPath, 'contact-requests'))
                                && !str_starts_with($currentPath, 'dashboard')
                                && !str_starts_with($currentPath, 'reported-items-admin')
                                && !str_starts_with($currentPath, 'claimed')
                                && !str_starts_with($currentPath, 'users')
                                && !str_starts_with($currentPath, 'insights')
                                && !str_starts_with($currentPath, 'settings')
                                && !str_starts_with($currentPath, 'sponsors')
                                && !str_starts_with($currentPath, 'rewards')
                                && !str_starts_with($currentPath, 'image-comparison')
                                && !str_starts_with($currentPath, 'review-questions');
                        @endphp
                        <a href="{{ route('contact-requests.index') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isContactRequests ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Contact Requests' : ''">
                            <i class="fas fa-inbox w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Contact Requests</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isReviewQuestions = (str_starts_with($currentRoute, 'review-questions') || str_starts_with($currentPath, 'review-questions'))
                                && !str_starts_with($currentPath, 'dashboard')
                                && !str_starts_with($currentPath, 'reported-items-admin')
                                && !str_starts_with($currentPath, 'claimed')
                                && !str_starts_with($currentPath, 'users')
                                && !str_starts_with($currentPath, 'insights')
                                && !str_starts_with($currentPath, 'settings')
                                && !str_starts_with($currentPath, 'sponsors')
                                && !str_starts_with($currentPath, 'rewards')
                                && !str_starts_with($currentPath, 'image-comparison');
                        @endphp
                        <a href="{{ route('review-questions.index') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isReviewQuestions ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Review Questions' : ''">
                            <i class="fas fa-star w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Review Questions</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentPath = request()->path();
                            $isImageComparison = str_starts_with($currentPath, 'image-comparison') 
                                && !str_starts_with($currentPath, 'admin/dashboard')
                                && !str_starts_with($currentPath, 'admin/reported-items')
                                && !str_starts_with($currentPath, 'admin/claimed')
                                && !str_starts_with($currentPath, 'admin/users')
                                && !str_starts_with($currentPath, 'admin/insights')
                                && !str_starts_with($currentPath, 'admin/settings')
                                && !str_starts_with($currentPath, 'admin/sponsors');
                        @endphp
                        <a href="/image-comparison" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isImageComparison ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Image Comparison' : ''">
                            <i class="fas fa-search w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Image Comparison</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isAdminNotifications = ($currentRoute === 'notifications.create' || str_starts_with($currentPath, 'notifications'))
                                && !str_starts_with($currentPath, 'dashboard')
                                && !str_starts_with($currentPath, 'reported-items-admin')
                                && !str_starts_with($currentPath, 'claimed')
                                && !str_starts_with($currentPath, 'users')
                                && !str_starts_with($currentPath, 'insights')
                                && !str_starts_with($currentPath, 'settings')
                                && !str_starts_with($currentPath, 'sponsors')
                                && !str_starts_with($currentPath, 'rewards')
                                && !str_starts_with($currentPath, 'image-comparison');
                        @endphp
                        <a href="{{ route('notifications.create') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isAdminNotifications ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}" :title="sidebarCollapsed ? 'Send Notifications' : ''">
                            <i class="fas fa-bullhorn w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" :class="sidebarCollapsed ? 'lg:mx-auto' : 'mr-2 sm:mr-3'"></i>
                            <span class="transition-opacity duration-300" :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : 'lg:opacity-100'">Send Notifications</span>
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
                            <button class="relative p-2 text-gray-600 hover:text-purple-primary" @click="open = !open; if(open){ fetch('/api/notifications').then(r=>r.json()).then(d=>{ unread = d.unread; const list = document.getElementById('admin-notif-list'); list.innerHTML=''; (d.notifications||[]).forEach(n=>{ const li = document.createElement('div'); li.className='px-4 py-2 hover:bg-gray-50'; li.innerHTML = `<div class=\'text-sm font-medium text-gray-900\'>${n.title}</div><div class=\'text-xs text-gray-500\'>${n.message ?? ''}</div>`; list.appendChild(li); }); }); }">
                                <i class="fas fa-bell w-4 h-4 sm:w-5 sm:h-5"></i>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 sm:w-5 sm:h-5 flex items-center justify-center" x-text="unread" x-show="unread > 0"></span>
                            </button>
                            <div x-show="open" class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50" style="display:none;">
                                <div class="px-4 py-2 border-b flex items-center justify-between">
                                    <span class="text-sm font-semibold text-gray-700">Notifications</span>
                                    <button class="text-xs text-purple-primary hover:underline" onclick="fetch('/api/notifications/mark-read',{method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}})">Mark all read</button>
                                </div>
                                <div id="admin-notif-list" class="max-h-80 overflow-y-auto"></div>
                            </div>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 sm:space-x-3 bg-purple-50 px-2 sm:px-4 py-1.5 sm:py-2 rounded-full hover:bg-purple-100 transition-colors">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full overflow-hidden bg-purple-primary flex items-center justify-center">
                                    @if(auth()->check() && auth()->user()->profile_picture)
                                        <img src="{{ auth()->user()->profile_picture }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-user text-white text-xs sm:text-sm"></i>
                                    @endif
                                </div>
                                <div class="hidden sm:block">
                                    <p class="text-xs sm:text-sm font-medium text-gray-900">{{ auth()->check() ? auth()->user()->name : 'Admin' }}</p>
                                    <p class="text-xs text-gray-500">{{ auth()->check() ? 'Admin' : '' }}</p>
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
                                <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-3 text-gray-400"></i>
                                    Profile
                                </a>
                                <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
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
            <main class="flex-1 overflow-y-auto p-3 sm:p-4 md:p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
