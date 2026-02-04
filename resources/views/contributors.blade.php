<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contributors - FindITFast</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white shadow-sm">
        <div class="container mx-auto px-4 sm:px-6 py-3 sm:py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('welcome') }}" class="text-xl sm:text-2xl font-bold">
                        <span class="text-purple-primary">FindIT</span>
                        <span class="text-pink-primary">Fast</span>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex items-center space-x-2 sm:space-x-4">
                    <a href="{{ route('welcome') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base text-gray-700 hover:text-purple-primary transition-colors">
                        <i class="fas fa-home mr-1"></i>Home
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base border-2 border-purple-primary text-purple-primary rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base border-2 border-purple-primary text-purple-primary rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="px-4 sm:px-6 py-1.5 sm:py-2 text-sm sm:text-base bg-purple-primary text-white rounded-lg font-medium hover:bg-purple-600 transition-colors">
                            Sign Up
                        </a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <!-- Header Section -->
        <div class="text-center mb-12">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold mb-4">
                <span class="text-purple-primary">Our</span>
                <span class="text-pink-primary">Contributors</span>
            </h1>
            <p class="text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto">
                Meet the talented team of developers and designers who helped build FindITFast. 
                Their dedication and expertise made this platform possible.
            </p>
        </div>

        <!-- Contributors Grid -->
        @if($contributors->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            @foreach($contributors as $contributor)
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow">
                <!-- Avatar Section -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-8 flex items-center justify-center">
                    @if($contributor->avatar_path)
                        <img src="{{ asset($contributor->avatar_path) }}" 
                             alt="{{ $contributor->name }}" 
                             class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
                    @else
                        <div class="w-32 h-32 rounded-full bg-gradient-to-br from-purple-primary to-pink-primary flex items-center justify-center text-white text-4xl font-bold border-4 border-white shadow-lg">
                            {{ strtoupper(substr($contributor->name, 0, 2)) }}
                        </div>
                    @endif
                </div>

                <!-- Info Section -->
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $contributor->name }}</h3>
                    @if($contributor->role)
                        <p class="text-purple-primary font-semibold mb-4">{{ $contributor->role }}</p>
                    @endif
                    
                    @if($contributor->bio)
                        <p class="text-gray-600 mb-6 leading-relaxed">{{ $contributor->bio }}</p>
                    @endif

                    <!-- Social Links -->
                    <div class="flex flex-wrap gap-3">
                        @if($contributor->email)
                            <a href="mailto:{{ $contributor->email }}" 
                               class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-purple-primary hover:text-white transition-colors"
                               title="Email">
                                <i class="fas fa-envelope"></i>
                            </a>
                        @endif
                        @if($contributor->github)
                            <a href="{{ $contributor->github }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-900 hover:text-white transition-colors"
                               title="GitHub">
                                <i class="fab fa-github"></i>
                            </a>
                        @endif
                        @if($contributor->linkedin)
                            <a href="{{ $contributor->linkedin }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-blue-600 hover:text-white transition-colors"
                               title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                        @endif
                        @if($contributor->twitter)
                            <a href="{{ $contributor->twitter }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-blue-400 hover:text-white transition-colors"
                               title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                        @endif
                        @if($contributor->website)
                            <a href="{{ $contributor->website }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-purple-primary hover:text-white transition-colors"
                               title="Website">
                                <i class="fas fa-globe"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-users text-gray-400 text-4xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">No Contributors Yet</h3>
            <p class="text-gray-600 mb-6">Contributors will appear here once they are added by the admin.</p>
            <a href="{{ route('welcome') }}" class="inline-block bg-purple-primary text-white px-6 py-3 rounded-lg hover:bg-purple-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Home
            </a>
        </div>
        @endif

        <!-- Back to Home Button -->
        <div class="text-center mt-12">
            <a href="{{ route('welcome') }}" class="inline-flex items-center px-6 py-3 bg-white border-2 border-purple-primary text-purple-primary rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Home
            </a>
        </div>
    </main>

    @include('components.footer', [
        'socialLinks' => $socialLinks ?? [],
        'contactEmail' => $contactEmail ?? null,
        'contactWebsite' => $contactWebsite ?? null,
    ])
</body>
</html>

