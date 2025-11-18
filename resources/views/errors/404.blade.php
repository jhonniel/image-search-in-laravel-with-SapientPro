<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | FindITFast</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full text-center">
            <!-- 404 Illustration -->
            <div class="mb-8">
                <div class="relative inline-block">
                    <!-- Animated 404 Text -->
                    <h1 class="text-9xl sm:text-[12rem] font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-primary via-pink-primary to-purple-primary animate-pulse">
                        404
                    </h1>
                    <!-- Decorative Elements -->
                    <div class="absolute -top-4 -right-4 w-16 h-16 bg-purple-primary rounded-full opacity-20 animate-bounce"></div>
                    <div class="absolute -bottom-4 -left-4 w-12 h-12 bg-pink-primary rounded-full opacity-20 animate-bounce" style="animation-delay: 0.5s;"></div>
                </div>
            </div>

            <!-- Error Message -->
            <div class="mb-8">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Oops! Page Not Found
                </h2>
                <p class="text-lg sm:text-xl text-gray-600 mb-2">
                    The page you're looking for seems to have wandered off.
                </p>
                <p class="text-base text-gray-500">
                    Don't worry, we'll help you find your way back!
                </p>
            </div>

            <!-- Search Icon Animation -->
            <div class="mb-8 flex justify-center">
                <div class="relative">
                    <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full bg-gradient-to-br from-purple-primary to-pink-primary flex items-center justify-center shadow-lg transform rotate-12 hover:rotate-0 transition-transform duration-300">
                        <i class="fas fa-search text-white text-4xl sm:text-5xl"></i>
                    </div>
                    <div class="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center animate-ping">
                        <i class="fas fa-exclamation text-white text-sm"></i>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
                <a href="{{ route('welcome') }}" 
                   class="inline-flex items-center px-6 py-3 bg-purple-primary text-white rounded-lg font-medium hover:bg-purple-600 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <i class="fas fa-home mr-2"></i>
                    Go to Home
                </a>
                <button onclick="window.history.back()" 
                        class="inline-flex items-center px-6 py-3 bg-white text-purple-primary border-2 border-purple-primary rounded-lg font-medium hover:bg-purple-50 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Go Back
                </button>
            </div>

            <!-- Helpful Links -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-4">You might be looking for:</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('welcome') }}" class="text-purple-primary hover:text-purple-600 text-sm font-medium transition-colors">
                        <i class="fas fa-compass mr-1"></i> Home
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-purple-primary hover:text-purple-600 text-sm font-medium transition-colors">
                            <i class="fas fa-th-large mr-1"></i> Dashboard
                        </a>
                        <a href="{{ route('reported-items') }}" class="text-purple-primary hover:text-purple-600 text-sm font-medium transition-colors">
                            <i class="fas fa-briefcase mr-1"></i> My Items
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-purple-primary hover:text-purple-600 text-sm font-medium transition-colors">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                        <a href="{{ route('register') }}" class="text-purple-primary hover:text-purple-600 text-sm font-medium transition-colors">
                            <i class="fas fa-user-plus mr-1"></i> Sign Up
                        </a>
                    @endauth
                    <a href="/post" class="text-purple-primary hover:text-purple-600 text-sm font-medium transition-colors">
                        <i class="fas fa-plus-circle mr-1"></i> Report Item
                    </a>
                </div>
            </div>

            <!-- Footer Note -->
            <div class="mt-8 text-center">
                <p class="text-xs text-gray-400">
                    If you believe this is an error, please <a href="mailto:support@finditfast.com" class="text-purple-primary hover:underline">contact support</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Floating Elements Animation -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute top-20 left-10 w-20 h-20 bg-purple-primary rounded-full opacity-10 animate-float"></div>
        <div class="absolute top-40 right-20 w-16 h-16 bg-pink-primary rounded-full opacity-10 animate-float" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-20 left-1/4 w-12 h-12 bg-blue-primary rounded-full opacity-10 animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-40 right-1/3 w-14 h-14 bg-purple-primary rounded-full opacity-10 animate-float" style="animation-delay: 1.5s;"></div>
    </div>

    <style>
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</body>
</html>

