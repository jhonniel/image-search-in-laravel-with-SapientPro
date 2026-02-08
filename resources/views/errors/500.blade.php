<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error | FindITFast</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gradient-to-br from-red-50 via-pink-50 to-purple-50 min-h-screen" x-data="{ showModal: true }">
    <div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full text-center">
            <!-- 500 Illustration -->
            <div class="mb-8">
                <div class="relative inline-block">
                    <!-- Animated 500 Text -->
                    <h1 class="text-9xl sm:text-[12rem] font-bold text-transparent bg-clip-text bg-gradient-to-r from-red-500 via-pink-primary to-purple-primary animate-pulse">
                        500
                    </h1>
                    <!-- Decorative Elements -->
                    <div class="absolute -top-4 -right-4 w-16 h-16 bg-red-500 rounded-full opacity-20 animate-bounce"></div>
                    <div class="absolute -bottom-4 -left-4 w-12 h-12 bg-pink-primary rounded-full opacity-20 animate-bounce" style="animation-delay: 0.5s;"></div>
                </div>
            </div>

            <!-- Error Message -->
            <div class="mb-8">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Internal Server Error
                </h2>
                <p class="text-lg sm:text-xl text-gray-600 mb-2">
                    Something went wrong on our end.
                </p>
                <p class="text-base text-gray-500">
                    We're working to fix this issue. Please try again later.
                </p>
            </div>

            <!-- Warning Icon Animation -->
            <div class="mb-8 flex justify-center">
                <div class="relative">
                    <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full bg-gradient-to-br from-red-500 to-pink-primary flex items-center justify-center shadow-lg transform rotate-12 hover:rotate-0 transition-transform duration-300">
                        <i class="fas fa-exclamation-triangle text-white text-4xl sm:text-5xl"></i>
                    </div>
                    <div class="absolute -top-2 -right-2 w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center animate-ping">
                        <i class="fas fa-bell text-white text-sm"></i>
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
                <button @click="showModal = true" 
                        class="inline-flex items-center px-6 py-3 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <i class="fas fa-envelope mr-2"></i>
                    Contact Admin
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
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Admin Modal -->
    <div x-show="showModal" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
         @click.away="showModal = false"
         x-cloak>
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 sm:p-8 transform transition-all"
             @click.stop>
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Contact Administrator</h3>
                </div>
                <button @click="showModal = false" 
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="mb-6">
                <p class="text-gray-600 mb-4">
                    We apologize for the inconvenience. An internal server error has occurred. 
                    Please contact our administrator to report this issue.
                </p>
                
                <div class="space-y-3">
                    <!-- Email Contact -->
                    <div class="flex items-center p-3 bg-purple-50 rounded-lg border border-purple-200">
                        <div class="w-10 h-10 bg-purple-primary rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                            <i class="fas fa-envelope text-white"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-1">Email Support</p>
                            <a href="mailto:admin@finditfast.com?subject=Internal Server Error Report" 
                               class="text-purple-primary hover:text-purple-600 font-medium text-sm break-all">
                                admin@finditfast.com
                            </a>
                        </div>
                    </div>

                    <!-- Copy Error Details Button -->
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-xs text-gray-500 mb-2">Error Details (for support):</p>
                        <div class="flex items-center justify-between">
                            <code class="text-xs text-gray-600 font-mono break-all flex-1 mr-2">
                                500 Internal Server Error
                            </code>
                            <button onclick="copyErrorDetails()" 
                                    class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs font-medium transition-colors flex-shrink-0">
                                <i class="fas fa-copy mr-1"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button @click="showModal = false" 
                        class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Close
                </button>
                <a href="mailto:admin@finditfast.com?subject=Internal Server Error Report&body=Hello,%0D%0A%0D%0AI encountered a 500 Internal Server Error on FindITFast.%0D%0A%0D%0AError Details:%0D%0A- Error Code: 500%0D%0A- Time: {{ now()->format('Y-m-d H:i:s') }}%0D%0A- URL: {{ request()->fullUrl() }}%0D%0A%0D%0APlease investigate and resolve this issue.%0D%0A%0D%0AThank you." 
                   class="flex-1 px-4 py-2.5 bg-purple-primary text-white rounded-lg hover:bg-purple-600 transition-colors font-medium text-center">
                    <i class="fas fa-paper-plane mr-2"></i> Send Email
                </a>
            </div>
        </div>
    </div>

    <!-- Floating Elements Animation -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute top-20 left-10 w-20 h-20 bg-red-500 rounded-full opacity-10 animate-float"></div>
        <div class="absolute top-40 right-20 w-16 h-16 bg-pink-primary rounded-full opacity-10 animate-float" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-20 left-1/4 w-12 h-12 bg-purple-primary rounded-full opacity-10 animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-40 right-1/3 w-14 h-14 bg-red-500 rounded-full opacity-10 animate-float" style="animation-delay: 1.5s;"></div>
    </div>

    <script>
        function copyErrorDetails() {
            const errorDetails = `500 Internal Server Error\nTime: {{ now()->format('Y-m-d H:i:s') }}\nURL: {{ request()->fullUrl() }}`;
            
            navigator.clipboard.writeText(errorDetails).then(() => {
                // Show success feedback
                const button = event.target.closest('button');
                const originalHTML = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check mr-1"></i> Copied!';
                button.classList.add('bg-green-200', 'text-green-700');
                button.classList.remove('bg-gray-200', 'text-gray-700');
                
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('bg-green-200', 'text-green-700');
                    button.classList.add('bg-gray-200', 'text-gray-700');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                alert('Failed to copy error details. Please copy manually.');
            });
        }

        // Auto-show modal on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Modal is already set to show by default via Alpine.js x-show="showModal"
        });
    </script>

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
        [x-cloak] {
            display: none !important;
        }
    </style>
</body>
</html>

