<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FindITFast')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'purple-primary': '#8B5CF6',
                        'purple-light': '#A78BFA',
                        'pink-primary': '#EC4899',
                        'pink-light': '#F472B6',
                        'blue-primary': '#3B82F6',
                        'blue-light': '#60A5FA',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
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
        <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
             class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:transition-none overflow-y-auto">
            <!-- Logo -->
            <div class="p-4 sm:p-6 border-b border-gray-200 flex items-center justify-between">
                <h1 class="text-xl sm:text-2xl font-bold">
                    <span class="text-purple-primary">FindIT</span>
                    <span class="text-pink-primary">Fast</span>
                </h1>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-600 hover:text-gray-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="mt-4 sm:mt-6">
                <ul class="space-y-2 px-2 sm:px-4">
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isDashboard = ($currentRoute === 'user.dashboard' || $currentPath === 'user/dashboard') 
                                && $currentPath !== 'user/reported-items' 
                                && !str_starts_with($currentPath, 'user/reported-items')
                                && !str_starts_with($currentPath, 'user/claim-verify')
                                && !str_starts_with($currentPath, 'user/profile')
                                && !str_starts_with($currentPath, 'user/chat');
                        @endphp
                        <a href="{{ route('user.dashboard') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isDashboard ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="fas fa-th-large w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isReportedItems = ($currentRoute === 'user.reported-items' || str_starts_with($currentPath, 'user/reported-items')) 
                                && !str_starts_with($currentPath, 'user/dashboard')
                                && !str_starts_with($currentPath, 'user/claim-verify')
                                && !str_starts_with($currentPath, 'user/profile')
                                && !str_starts_with($currentPath, 'user/chat');
                        @endphp
                        <a href="{{ route('user.reported-items') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isReportedItems ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="fas fa-briefcase w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            <span>Reported Items</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentPath = request()->path();
                            $isClaimVerify = str_starts_with($currentPath, 'user/claim-verify');
                        @endphp
                        <a href="/user/claim-verify" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isClaimVerify ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="fas fa-check-circle w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            <span>Claim and Verify</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isProfile = (str_starts_with($currentRoute, 'user.profile') || str_starts_with($currentPath, 'user/profile')) 
                                && !str_starts_with($currentPath, 'user/dashboard')
                                && !str_starts_with($currentPath, 'user/reported-items')
                                && !str_starts_with($currentPath, 'user/claim-verify')
                                && !str_starts_with($currentPath, 'user/chat');
                        @endphp
                        <a href="{{ route('user.profile') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isProfile ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="fas fa-user w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        @php
                            $currentRoute = request()->route()->getName() ?? '';
                            $currentPath = request()->path();
                            $isChat = (str_starts_with($currentRoute, 'user.chat') || str_starts_with($currentPath, 'user/chat')) 
                                && !str_starts_with($currentPath, 'user/dashboard')
                                && !str_starts_with($currentPath, 'user/reported-items')
                                && !str_starts_with($currentPath, 'user/claim-verify')
                                && !str_starts_with($currentPath, 'user/profile');
                        @endphp
                        <a href="{{ route('user.chat') }}" class="flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base {{ $isChat ? 'bg-pink-50 text-purple-primary border-l-4 border-purple-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                            <i class="fas fa-comments w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                            <span>Messages</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Logout Button -->
            <div class="mt-auto p-3 sm:p-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg text-sm sm:text-base text-red-600 hover:bg-red-50 transition-colors">
                        <i class="fas fa-sign-out-alt w-4 h-4 sm:w-5 sm:h-5 mr-2 sm:mr-3"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-3 sm:px-4 md:px-6 py-3 sm:py-4">
                <div class="flex items-center justify-between gap-3">
                    <!-- Mobile Menu Button -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
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
                        <button class="relative p-2 text-gray-600 hover:text-purple-primary">
                            <i class="fas fa-bell w-4 h-4 sm:w-5 sm:h-5"></i>
                        </button>

                        <!-- User Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 sm:space-x-3 hover:bg-gray-50 px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg transition-colors">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-purple-primary rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-white text-xs sm:text-sm"></i>
                                </div>
                                <div class="hidden sm:block">
                                    <p class="text-xs sm:text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-gray-500">{{ Str::limit(Auth::user()->email, 20) }}</p>
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
                                <a href="{{ route('user.profile') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-3 text-gray-400"></i>
                                    Profile
                                </a>
                                <a href="{{ route('user.profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
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
