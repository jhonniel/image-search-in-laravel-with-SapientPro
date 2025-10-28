<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindITFast - Lost and Found Platform</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <style>
        :root {
            --purple-primary: #8B5CF6;
            --pink-primary: #EC4899;
        }

        .bg-purple-primary {
            background-color: var(--purple-primary);
        }

        .text-purple-primary {
            color: var(--purple-primary);
        }

        .bg-pink-primary {
            background-color: var(--pink-primary);
        }

        .text-pink-primary {
            color: var(--pink-primary);
        }

        .border-purple-primary {
            border-color: var(--purple-primary);
        }

        .border-pink-primary {
            border-color: var(--pink-primary);
        }
            </style>
    </head>
<body class="bg-white">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white shadow-sm">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <!-- Logo -->
            <div class="flex items-center">
                <h1 class="text-2xl font-bold">
                    <span class="text-purple-primary">FindIT</span>
                    <span class="text-pink-primary">Fast</span>
                </h1>
            </div>

            <!-- Navigation -->
            <nav class="flex items-center space-x-4">
                <a href="/login" class="px-4 py-2 border-2 border-purple-primary text-purple-primary rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors">
                    Login
                </a>
                <a href="/register" class="px-4 py-2 bg-purple-primary text-white rounded-lg font-medium hover:bg-purple-600 transition-colors">
                    Sign Up
                </a>
                </nav>
        </div>
        </header>

    <!-- Hero Section -->
    <section class="container mx-auto px-6 py-20">
        <div class="text-center max-w-4xl mx-auto">
            <!-- Main Heading -->
            <h2 class="text-5xl font-bold text-gray-900 mb-4">
                From Lost to Found. In Just a Few Clicks.
            </h2>

            <!-- Subheading -->
            <p class="text-xl text-gray-600 mb-8">
                One place for lost and found. Open to everyone.
            </p>

            <!-- Search Bar -->
            <div class="flex items-center justify-center space-x-2 mb-6">
                <div class="flex-1 max-w-2xl relative">
                    <input
                        type="text"
                        placeholder="Search for lost or found items..."
                        class="w-full px-6 py-4 text-gray-700 bg-white border-2 border-gray-200 rounded-lg focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-500"
                    >
                    <i class="fas fa-search absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                <button class="px-8 py-4 bg-pink-primary text-white rounded-lg font-medium hover:bg-pink-600 transition-colors">
                    Search
                </button>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-center justify-center space-x-4">
                <a href="{{ route('guest.post.form', ['type' => 'lost']) }}" class="px-6 py-3 bg-gray-100 border-2 border-purple-primary text-purple-primary rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors">
                    I Lost Something
                </a>
                <a href="{{ route('guest.post.form', ['type' => 'found']) }}" class="px-6 py-3 bg-gray-100 border-2 border-purple-primary text-purple-primary rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors">
                    I Found Something
                </a>
            </div>
        </div>
    </section>



    <!-- City Illustration Section -->
    <section class="container mx-auto px-6 py-12">
        <div class="text-center mb-12">
            <h3 class="text-3xl font-bold text-gray-900 mb-4">Available in Multiple Cities</h3>
            <p class="text-lg text-gray-600">Connect with people from all around</p>
        </div>

        <!-- City skyline illustration -->
        <div class="w-full">
            <img src="{{ asset('images/city-skyline.png') }}" alt="City Skyline" class="w-full h-auto">
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="container mx-auto px-6 py-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <!-- Lost Reports -->
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-purple-100 mb-4">
                    <i class="fas fa-shopping-bag text-purple-primary text-4xl"></i>
                </div>
                <div class="text-4xl font-bold text-purple-primary mb-2">1,000 +</div>
                <div class="text-gray-600 text-lg">Lost Reports</div>
            </div>

            <!-- Items Reunited -->
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-pink-100 mb-4">
                    <i class="fas fa-hand-holding-heart text-pink-primary text-4xl"></i>
                </div>
                <div class="text-4xl font-bold text-pink-primary mb-2">1,000 +</div>
                <div class="text-gray-600 text-lg">Items Reunited</div>
            </div>

            <!-- Locations Covered -->
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-purple-100 mb-4">
                    <i class="fas fa-map-marker-alt text-purple-primary text-4xl"></i>
                </div>
                <div class="text-4xl font-bold text-purple-primary mb-2">100 +</div>
                <div class="text-gray-600 text-lg">Locations Covered</div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="container mx-auto px-6 py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-4xl font-bold text-gray-900 mb-12 text-center">How FindITFast Works</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <!-- Left Side: Steps -->
                <div class="space-y-8">
                    <!-- Step 1: Post IT -->
                    <div class="flex items-start space-x-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-purple-primary text-white text-2xl font-bold flex-shrink-0">
                            1
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Post IT</h3>
                            <p class="text-gray-600">Let the community know what you lost or found.</p>
                        </div>
                    </div>

                    <!-- Step 2: Track IT -->
                    <div class="flex items-start space-x-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-purple-primary text-white text-2xl font-bold flex-shrink-0">
                            2
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Track IT</h3>
                            <p class="text-gray-600">Our system uses image matching and keywords to automatically compare your post with other reports.</p>
                        </div>
                    </div>

                    <!-- Step 3: Return IT -->
                    <div class="flex items-start space-x-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-purple-primary text-white text-2xl font-bold flex-shrink-0">
                            3
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Return IT</h3>
                            <p class="text-gray-600">When there's a match, we'll notify you immediately. You can then coordinate safely to return or retrieve the item.</p>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Illustration -->
                <div class="flex items-center justify-center">
                    <div class="w-full max-w-md">
                        <img src="{{ asset('images/how-it-works.png') }}" alt="How It Works Illustration" class="w-full h-auto">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fresh Reports Section -->
    <section class="container mx-auto px-6 py-20">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-4xl font-bold text-gray-900 mb-12">Fresh Reports from Your Area</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Item Card 1: Black Wallet -->
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <i class="fas fa-wallet text-pink-primary text-3xl"></i>
                        <span class="px-3 py-1 bg-pink-100 text-pink-800 rounded-full text-xs font-semibold uppercase">Lost</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Black Wallet</h3>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-purple-primary mr-2"></i>
                            <span>SM Davao</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock text-purple-primary mr-2"></i>
                            <span>2 hours ago</span>
                        </div>
                    </div>
                    <a href="/login" class="text-purple-primary underline font-medium text-sm hover:text-purple-600">
                        View Details
                    </a>
                </div>

                <!-- Item Card 2: Backpack -->
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <i class="fas fa-backpack text-pink-primary text-3xl"></i>
                        <span class="px-3 py-1 bg-pink-100 text-pink-800 rounded-full text-xs font-semibold uppercase">Lost</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Backpack</h3>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-purple-primary mr-2"></i>
                            <span>University of Mindanao</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock text-purple-primary mr-2"></i>
                            <span>2 hours ago</span>
                        </div>
                    </div>
                    <a href="/login" class="text-purple-primary underline font-medium text-sm hover:text-purple-600">
                        View Details
                    </a>
                </div>

                <!-- Item Card 3: iPhone 13 -->
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <i class="fas fa-mobile-alt text-pink-primary text-3xl"></i>
                        <span class="px-3 py-1 bg-pink-100 text-pink-800 rounded-full text-xs font-semibold uppercase">Lost</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">iPhone 13</h3>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-purple-primary mr-2"></i>
                            <span>Roxas Night Market</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock text-purple-primary mr-2"></i>
                            <span>7 hours ago</span>
                        </div>
                    </div>
                    <a href="/login" class="text-purple-primary underline font-medium text-sm hover:text-purple-600">
                        View Details
                    </a>
                </div>

                <!-- Item Card 4: PRC ID -->
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <i class="fas fa-id-card text-pink-primary text-3xl"></i>
                        <span class="px-3 py-1 bg-pink-100 text-pink-800 rounded-full text-xs font-semibold uppercase">Lost</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">PRC ID</h3>
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-purple-primary mr-2"></i>
                            <span>Gaisano Mall</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-clock text-purple-primary mr-2"></i>
                            <span>5 hours ago</span>
                        </div>
                    </div>
                    <a href="/login" class="text-purple-primary underline font-medium text-sm hover:text-purple-600">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Top Helpers Section (moved below Fresh Reports) -->
    <section class="container mx-auto px-6 py-16">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-end justify-between mb-8">
                <h2 class="text-4xl font-bold text-gray-900">Top Helpers</h2>
                <span class="text-sm text-gray-500">Most items successfully returned</span>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="divide-y divide-gray-100">
                    <!-- Row 1 -->
                    <div class="grid grid-cols-12 items-center px-6 py-4">
                        <div class="col-span-1 text-2xl font-extrabold text-yellow-500">#1</div>
                        <div class="col-span-7 flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-primary flex items-center justify-center font-bold">AM</div>
                            <div>
                                <div class="font-semibold text-gray-900">Alyssa M.</div>
                                <div class="text-xs text-gray-500">Davao City</div>
                            </div>
                        </div>
                        <div class="col-span-4 text-right">
                            <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm font-medium">16 returned</span>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="grid grid-cols-12 items-center px-6 py-4">
                        <div class="col-span-1 text-2xl font-extrabold text-gray-500">#2</div>
                        <div class="col-span-7 flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-pink-100 text-pink-primary flex items-center justify-center font-bold">JR</div>
                            <div>
                                <div class="font-semibold text-gray-900">Jared R.</div>
                                <div class="text-xs text-gray-500">Quezon City</div>
                            </div>
                        </div>
                        <div class="col-span-4 text-right">
                            <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm font-medium">12 returned</span>
                        </div>
                    </div>

                    <!-- Row 3 -->
                    <div class="grid grid-cols-12 items-center px-6 py-4">
                        <div class="col-span-1 text-2xl font-extrabold text-gray-500">#3</div>
                        <div class="col-span-7 flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">KC</div>
                            <div>
                                <div class="font-semibold text-gray-900">Kaye C.</div>
                                <div class="text-xs text-gray-500">Cebu City</div>
                            </div>
                        </div>
                        <div class="col-span-4 text-right">
                            <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm font-medium">10 returned</span>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-500 mt-4">Note: Demo data for illustration. Can be powered from real stats later.</p>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="bg-purple-50 py-20">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-purple-primary mb-12 text-center">What Our Users Say</h2>

            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <p class="text-gray-700 text-lg leading-relaxed mb-6">
                        "I was honestly skeptical at first, but FindITFast proved me wrong. I lost my backpack at a coffee shop and within a few hours, someone posted it on the platform. The system matched my report, and we coordinated easily. I got everything back — even my notebook! Super thankful for this tool."
                    </p>
                    <div class="flex items-center justify-between">
                        <span class="text-purple-primary font-semibold">Alyssa M., Davao City</span>
                        <div class="flex items-center space-x-2">
                            <button class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center hover:bg-purple-primary hover:text-white transition-colors">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center hover:bg-purple-primary hover:text-white transition-colors">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Divider -->
    <div class="border-t border-gray-300"></div>

    <!-- Footer -->
    <footer class="bg-purple-50 py-16">
        <div class="container mx-auto px-6">
            <div class="max-w-none grid grid-cols-1 md:grid-cols-2 gap-24 mb-10">
                <!-- Left Side: Brand Info -->
                <div>
                    <h3 class="text-5xl font-extrabold mb-6">
                        <span class="text-purple-primary">FindIT</span>
                        <span class="text-pink-primary">Fast</span>
                    </h3>
                    <p class="text-gray-700 text-xl mb-8">Reuniting people with their lost items—fast, easy, and smart.</p>

                    <!-- Social Media Icons -->
                    <div class="flex space-x-5 mb-8">
                        <a href="#" class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-purple-primary hover:bg-purple-primary hover:text-white transition-colors shadow-md">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-purple-primary hover:bg-purple-primary hover:text-white transition-colors shadow-md">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>

                    <!-- Contact Information -->
                    <div class="space-y-3">
                        <div class="flex items-center text-gray-700 text-xl">
                            <i class="fas fa-envelope mr-3 text-purple-primary"></i>
                            <span>support@finditfast.com</span>
                        </div>
                        <div class="flex items-center text-gray-700 text-xl">
                            <i class="fas fa-globe mr-3 text-purple-primary"></i>
                            <span>finditfast.com</span>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Support Links -->
                <div class="md:justify-self-end md:text-right">
                    <h4 class="text-4xl font-extrabold text-pink-primary mb-6">Support</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-2xl text-gray-700 hover:text-purple-primary transition-colors">FAQs</a></li>
                        <li><a href="#" class="text-2xl text-gray-700 hover:text-purple-primary transition-colors">Contact Us</a></li>
                        <li><a href="#" class="text-2xl text-gray-700 hover:text-purple-primary transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-2xl text-gray-700 hover:text-purple-primary transition-colors">Terms & Condition</a></li>
                    </ul>
                </div>
            </div>

            <!-- Copyright Notice -->
            <div class="max-w-6xl mx-auto border-t border-gray-300 pt-8 text-center">
                <p class="text-gray-700 text-2xl">© 2025 FindITFast — Built with care for the city we love.</p>
                </div>
        </div>
    </footer>
    </body>
</html>
