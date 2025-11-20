<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindITFast - Lost and Found Platform</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
<body class="bg-white">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white shadow-sm">
        <div class="container mx-auto px-4 sm:px-6 py-3 sm:py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center">
                    <h1 class="text-xl sm:text-2xl font-bold">
                        <span class="text-purple-primary">FindIT</span>
                        <span class="text-pink-primary">Fast</span>
                    </h1>
                </div>

                <!-- Navigation -->
                <nav class="flex items-center space-x-2 sm:space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base border-2 border-purple-primary text-purple-primary rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="/login" class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base border-2 border-purple-primary text-purple-primary rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors">
                            Login
                        </a>
                        <a href="/register" class="px-4 sm:px-6 py-1.5 sm:py-2 text-sm sm:text-base bg-purple-primary text-white rounded-lg font-medium hover:bg-purple-600 transition-colors">
                            Sign Up
                        </a>
                    @endauth
                </nav>
            </div>
        </div>
        </header>

    <!-- Hero Section -->
    <section class="container mx-auto px-4 sm:px-6 py-12 sm:py-20">
        <div class="text-center max-w-4xl mx-auto">
            <!-- Main Heading -->
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mb-3 sm:mb-4 px-4 whitespace-normal leading-tight">
                From Lost to Found. In Just a Few Clicks.
            </h2>

            <!-- Subheading -->
            <p class="text-base sm:text-lg md:text-xl text-gray-600 mb-6 sm:mb-8 px-4">
                One place for lost and found. Open to everyone.
            </p>

            <!-- Search Bar -->
            <form action="{{ route('search') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2 sm:gap-2 mb-4 sm:mb-6 px-4" id="searchForm">
                <div class="flex-1 w-full sm:max-w-2xl relative">
                    <input
                        type="text"
                        name="q"
                        id="searchInput"
                        value="{{ $searchQuery ?? '' }}"
                        placeholder="Search for lost or found items..."
                        class="w-full px-4 sm:px-6 py-3 sm:py-4 pr-10 sm:pr-12 text-sm sm:text-base text-gray-700 bg-white border-2 border-gray-200 rounded-lg focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-500"
                        autocomplete="off"
                    >
                    <i class="fas fa-search absolute right-3 sm:right-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm sm:text-base" id="searchIcon"></i>
                    <div id="searchLoading" class="hidden absolute right-3 sm:right-4 top-1/2 transform -translate-y-1/2">
                        <i class="fas fa-spinner fa-spin text-purple-primary"></i>
                    </div>
                </div>
                <button type="submit" class="w-full sm:w-auto px-6 sm:px-8 py-3 sm:py-4 text-sm sm:text-base bg-pink-primary text-white rounded-lg font-medium hover:bg-pink-600 transition-colors">
                    Search
                </button>
            </form>

            <!-- Search Filters (shown when searching) -->
            @if(isset($isSearch) && $isSearch)
            <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-4 mb-4 sm:mb-6 px-4">
                <a href="{{ route('search', ['q' => $searchQuery, 'status' => '']) }}"
                   class="px-3 sm:px-4 py-2 text-sm sm:text-base {{ empty($statusFilter) ? 'bg-purple-primary text-white' : 'bg-gray-100 text-gray-700' }} rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors">
                    All
                </a>
                <a href="{{ route('search', ['q' => $searchQuery, 'status' => 'lost']) }}"
                   class="px-3 sm:px-4 py-2 text-sm sm:text-base {{ $statusFilter === 'lost' ? 'bg-purple-primary text-white' : 'bg-gray-100 text-gray-700' }} rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors">
                    Lost
                </a>
                <a href="{{ route('search', ['q' => $searchQuery, 'status' => 'found']) }}"
                   class="px-3 sm:px-4 py-2 text-sm sm:text-base {{ $statusFilter === 'found' ? 'bg-purple-primary text-white' : 'bg-gray-100 text-gray-700' }} rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors">
                    Found
                </a>
            </div>
            @endif

            <!-- Filter Buttons -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3 sm:gap-4 px-4">
                <a href="{{ route('guest.post.form', ['type' => 'lost']) }}" class="w-full sm:w-auto px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base bg-gray-100 border-2 border-purple-primary text-purple-primary rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors text-center">
                    I Lost Something
                </a>
                <a href="{{ route('guest.post.form', ['type' => 'found']) }}" class="w-full sm:w-auto px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base bg-gray-100 border-2 border-purple-primary text-purple-primary rounded-lg font-medium hover:bg-purple-primary hover:text-white transition-colors text-center">
                    I Found Something
                </a>
            </div>
        </div>
    </section>



    <!-- City Illustration Section -->
    <section class="container mx-auto px-4 sm:px-6 py-8 sm:py-12">
        <div class="text-center mb-8 sm:mb-12">
            <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2 sm:mb-4">Available in Multiple Cities</h3>
            <p class="text-base sm:text-lg text-gray-600">Connect with people from all around</p>
        </div>

        <!-- City skyline illustration -->
        <div class="w-full">
            <img src="{{ asset('images/city-skyline.png') }}" alt="City Skyline" class="w-full h-auto">
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="container mx-auto px-4 sm:px-6 py-12 sm:py-20">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-8 max-w-5xl mx-auto">
            <!-- Lost Reports -->
            <div class="text-center" data-counter>
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-purple-100 mb-4">
                    <i class="fas fa-shopping-bag text-purple-primary text-4xl"></i>
                </div>
                <div class="text-4xl font-bold text-purple-primary mb-2 counter-value" data-target="{{ $totalLostReports }}" data-plus-threshold="1000">0</div>
                <div class="text-gray-600 text-lg">Lost&Found Reports</div>
            </div>

            <!-- Items Reunited -->
            <div class="text-center" data-counter>
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-pink-100 mb-4">
                    <i class="fas fa-hand-holding-heart text-pink-primary text-4xl"></i>
                </div>
                <div class="text-4xl font-bold text-pink-primary mb-2 counter-value" data-target="{{ $totalItemsReunited }}" data-plus-threshold="1000">0</div>
                <div class="text-gray-600 text-lg">Items Reunited</div>
            </div>

            <!-- Locations Covered -->
            <div class="text-center" data-counter>
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-purple-100 mb-4">
                    <i class="fas fa-map-marker-alt text-purple-primary text-4xl"></i>
                </div>
                <div class="text-4xl font-bold text-purple-primary mb-2 counter-value" data-target="{{ $totalLocations }}" data-plus-threshold="100">0</div>
                <div class="text-gray-600 text-lg">Locations Covered</div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="container mx-auto px-4 sm:px-6 py-12 sm:py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-8 sm:mb-12 text-center px-4">How FindITFast Works</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 sm:gap-12 items-center">
                <!-- Left Side: Steps -->
                <div class="space-y-8">
                    <!-- Step 1: Post IT -->
                    <div class="flex items-start space-x-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-purple-primary text-white text-2xl font-bold shrink-0">
                            1
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Post IT</h3>
                            <p class="text-gray-600">Let the community know what you lost or found.</p>
                        </div>
                    </div>

                    <!-- Step 2: Track IT -->
                    <div class="flex items-start space-x-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-purple-primary text-white text-2xl font-bold shrink-0">
                            2
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Track IT</h3>
                            <p class="text-gray-600">Our system uses image matching and keywords to automatically compare your post with other reports.</p>
                        </div>
                    </div>

                    <!-- Step 3: Return IT -->
                    <div class="flex items-start space-x-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-purple-primary text-white text-2xl font-bold shrink-0">
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

    <!-- Fresh Reports Section / Search Results -->
    <section class="container mx-auto px-4 sm:px-6 py-12 sm:py-20" id="resultsSection">
        <div class="max-w-6xl mx-auto" id="resultsContainer">
            @if(isset($isSearch) && $isSearch)
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 sm:mb-8 gap-3 sm:gap-4">
                    <div class="flex-1">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900">
                            Search Results
                            @if(!empty($searchQuery))
                                <span class="block sm:inline text-base sm:text-lg font-normal text-gray-600 mt-1 sm:mt-0">for "{{ $searchQuery }}"</span>
                            @endif
                        </h2>
                        @if(!$freshReports->isEmpty())
                            <p class="text-sm sm:text-base text-gray-500 mt-2">
                                <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                Found {{ $freshReports->count() }} {{ $freshReports->count() === 1 ? 'item' : 'items' }} matching your search
                            </p>
                        @endif
                    </div>
                    <a href="{{ route('welcome') }}" class="text-sm sm:text-base text-purple-primary hover:text-purple-600 underline whitespace-nowrap self-start sm:self-center">
                        <i class="fas fa-times mr-2"></i>Clear Search
                    </a>
                </div>
                @if($freshReports->isEmpty())
                    <div class="max-w-2xl mx-auto text-center py-16">
                        <div class="mb-6">
                            <i class="fas fa-search text-gray-300 text-7xl mb-4"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">No Match Found</h3>
                        <p class="text-gray-600 text-lg mb-2">
                            We couldn't find any items matching "{{ $searchQuery }}".
                        </p>
                        <p class="text-gray-500 mb-8">
                            Don't worry! Upload your item and our smart system will automatically search for matches.
                            When someone posts a matching item, we'll notify you immediately so you can get reunited with your item.
                        </p>

                        <!-- Call to Action Buttons -->
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-6">
                            <a href="{{ route('guest.post.form', ['type' => 'lost', 'search' => $searchQuery ?? '']) }}"
                               class="px-8 py-4 bg-pink-primary text-white rounded-lg font-medium hover:bg-pink-600 transition-colors shadow-md hover:shadow-lg flex items-center justify-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                I Lost Something
                            </a>
                            <a href="{{ route('guest.post.form', ['type' => 'found', 'search' => $searchQuery ?? '']) }}"
                               class="px-8 py-4 bg-purple-primary text-white rounded-lg font-medium hover:bg-purple-600 transition-colors shadow-md hover:shadow-lg flex items-center justify-center">
                                <i class="fas fa-hand-holding-heart mr-2"></i>
                                I Found Something
                            </a>
                        </div>

                        <div class="mt-8 pt-8 border-t border-gray-200">
                            <p class="text-gray-500 text-sm mb-4">
                                <i class="fas fa-info-circle text-purple-primary mr-2"></i>
                                <strong>How it works:</strong> Our system uses advanced image matching and keyword analysis to automatically compare your post with existing reports. You'll be notified instantly when a match is found!
                            </p>
                            <a href="{{ route('welcome') }}" class="text-purple-primary hover:text-purple-600 underline text-sm">
                                <i class="fas fa-arrow-left mr-1"></i>Browse all items instead
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Show search results grid when there are results -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @endif
            @else
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-8 sm:mb-12 px-4 sm:px-0">New Reported Items</h2>
                <!-- Show fresh reports grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            @endif

                @forelse($freshReports as $report)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <!-- Image Section -->
                    <div class="relative h-48 bg-gray-100">
                        @if($report['image_path'])
                            <img src="{{ $report['image_path'] }}"
                                 alt="{{ $report['title'] }}"
                                 class="w-full h-full object-cover"
                                 onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23e5e7eb\' width=\'400\' height=\'300\'/%3E%3Ctext fill=\'%239ca3af\' font-family=\'sans-serif\' font-size=\'20\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3ENo Image%3C/text%3E%3C/svg%3E';">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                <i class="fas fa-image text-gray-400 text-4xl"></i>
                            </div>
                        @endif
                        <!-- Status Badge Overlay -->
                        <span class="absolute top-3 right-3 px-3 py-1 {{ $report['type'] === 'lost' ? 'bg-pink-100 text-pink-800' : 'bg-green-100 text-green-800' }} rounded-full text-xs font-semibold uppercase shadow-md">
                            {{ ucfirst($report['type']) }}
                        </span>
                    </div>

                    <!-- Content Section -->
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 line-clamp-2">{{ \Illuminate\Support\Str::limit($report['title'], 50) }}</h3>
                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-map-marker-alt text-purple-primary mr-2"></i>
                                <span class="line-clamp-1">{{ \Illuminate\Support\Str::limit($report['location'], 25) }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-clock text-purple-primary mr-2"></i>
                                <span>{{ $report['time_ago'] }}</span>
                            </div>
                        </div>
                    @if(isset($report['upload_id']) && !empty($report['upload_id']))
                        <a href="{{ route('public.item.show', $report['upload_id']) }}" class="text-purple-primary underline font-medium text-sm hover:text-purple-600">
                            View Details
                        </a>
                    @else
                        <span class="text-gray-400 text-sm">Details unavailable</span>
                    @endif
                    </div>
                </div>
                @empty
                @if(!isset($isSearch) || !$isSearch)
                    <div class="col-span-4 text-center py-12">
                        <p class="text-gray-500 text-lg">No reports available at the moment. Be the first to post!</p>
                    </div>
                @endif
                @endforelse
            </div>
        </div>
    </section>

    <!-- Top Helpers Section (moved below Fresh Reports) -->
    <section class="container mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-6 sm:mb-8 gap-2">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900">Top Helpers</h2>
                <span class="text-xs sm:text-sm text-gray-500">Most items successfully returned</span>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="divide-y divide-gray-100">
                    @forelse($topHelpers as $index => $helper)
                    <div class="grid grid-cols-12 items-center px-4 sm:px-6 py-3 sm:py-4 gap-3">
                        <div class="col-span-2 sm:col-span-1 text-xl sm:text-2xl font-extrabold {{ $index === 0 ? 'text-yellow-500' : 'text-gray-500' }}">#{{ $index + 1 }}</div>
                        <div class="col-span-6 sm:col-span-7 flex items-center space-x-2 sm:space-x-3">
                            @if(!empty($helper['profile_picture']))
                                <img src="{{ $helper['profile_picture'] }}" alt="{{ $helper['name'] }} profile photo" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover border-2 border-purple-100">
                            @else
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full {{ $index === 0 ? 'bg-purple-100 text-purple-primary' : ($index === 1 ? 'bg-pink-100 text-pink-primary' : 'bg-blue-100 text-blue-600') }} flex items-center justify-center font-bold text-sm sm:text-base">
                                    {{ $helper['initial'] }}
                                </div>
                            @endif
                            <div>
                                <div class="font-semibold text-gray-900 text-sm sm:text-base">{{ $helper['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $helper['city'] }}</div>
                            </div>
                        </div>
                        <div class="col-span-4 text-right sm:text-right">
                            <span class="px-2 sm:px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs sm:text-sm font-medium">{{ $helper['returned_count'] }} returned</span>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 sm:px-6 py-6 sm:py-8 text-center text-gray-500 text-sm sm:text-base">
                        <p>No top helpers yet. Be the first to return an item!</p>
                    </div>
                    @endforelse
                </div>
            </div>

            @if($topHelpers->isEmpty())
            <p class="text-xs text-gray-500 mt-4 text-center">Help others find their lost items to become a top helper!</p>
            @endif
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="bg-purple-50 py-12 sm:py-20">
        <div class="container mx-auto px-4 sm:px-6">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-purple-primary mb-8 sm:mb-12 text-center">What Our Users Say</h2>

            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-6 sm:p-8">
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

    @if($faqs->count())
    <!-- FAQ Section -->
    <section id="faq" class="bg-gray-50 py-12 sm:py-16">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="max-w-4xl mx-auto text-center mb-10">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-purple-primary uppercase tracking-widest mb-2">Need Answers?</h2>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
                <p class="text-base sm:text-lg text-gray-600">A quick guide to how FindITFast keeps lost items moving back to their owners.</p>
            </div>

            <div class="max-w-4xl mx-auto space-y-4">
                @foreach($faqs as $index => $faq)
                <details class="group bg-white border border-gray-200 rounded-2xl shadow-sm transition-all">
                    <summary class="flex items-center justify-between w-full px-5 py-4 cursor-pointer">
                        <div class="flex items-start text-left">
                            <span class="text-sm font-semibold text-purple-primary mr-4">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="text-lg font-semibold text-gray-900">{{ $faq->question }}</span>
                        </div>
                        <span class="ml-4 flex-shrink-0 text-purple-primary group-open:hidden">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="ml-4 flex-shrink-0 text-purple-primary hidden group-open:block">
                            <i class="fas fa-minus"></i>
                        </span>
                    </summary>
                    <div class="px-5 pb-5 text-left">
                        <p class="text-gray-600 leading-relaxed">{{ $faq->answer }}</p>
                    </div>
                </details>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Contact Section -->
    <section id="contact-us" class="py-12 sm:py-16 bg-white">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-3xl p-8 shadow-sm border border-purple-100">
                    <p class="text-sm uppercase tracking-widest text-purple-primary font-semibold mb-2">Contact Us</p>
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Need help or have feedback?</h2>
                    <p class="text-base sm:text-lg text-gray-600 mb-6">Share your questions, partnership ideas, or product feedback. Our team reviews every request within one business day.</p>

                    <div class="space-y-5">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow">
                                <i class="fas fa-headset text-purple-primary text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-500">Support Hours</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $contactSupportHours }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow">
                                <i class="fas fa-envelope-open-text text-purple-primary text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-500">Email</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $contactEmail }}</p>
                                <p class="text-sm text-gray-500">{{ $contactEmailHelpText }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow">
                                <i class="fas fa-globe-asia text-purple-primary text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-500">Website</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $contactWebsite }}</p>
                                <p class="text-sm text-gray-500">Visit our resources and guides.</p>
                            </div>
                        </div>
                    </div>

                    @if($contactHelpSections->count())
                    <div class="mt-8 space-y-4">
                        @foreach($contactHelpSections as $section)
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-purple-50">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $section->heading }}</h3>
                            <p class="text-gray-600 mb-3">{{ $section->body }}</p>
                            @if($section->cta_label && $section->cta_url)
                            <a href="{{ $section->cta_url }}" target="_blank" class="inline-flex items-center text-purple-primary font-semibold text-sm hover:text-purple-700">
                                {{ $section->cta_label }}
                                <i class="fas fa-arrow-up-right-from-square text-xs ml-1"></i>
                            </a>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 sm:p-8">
                    @if(session('contact_success'))
                    <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>{{ session('contact_success') }}</span>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                        <p class="font-semibold mb-1">Please fix the following:</p>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="contactName" class="block text-sm font-semibold text-gray-700 mb-1">Full Name</label>
                            <input type="text" id="contactName" name="name" value="{{ old('name') }}" required
                                   class="w-full px-4 py-3 border-2 border-gray-100 rounded-2xl focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-200 transition"
                                   placeholder="Maria Santos">
                        </div>

                        <div>
                            <label for="contactEmail" class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                            <input type="email" id="contactEmail" name="email" value="{{ old('email') }}" required
                                   class="w-full px-4 py-3 border-2 border-gray-100 rounded-2xl focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-200 transition"
                                   placeholder="you@email.com">
                        </div>

                        <div>
                            <label for="contactSubject" class="block text-sm font-semibold text-gray-700 mb-1">Subject</label>
                            <input type="text" id="contactSubject" name="subject" value="{{ old('subject') }}"
                                   class="w-full px-4 py-3 border-2 border-gray-100 rounded-2xl focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-200 transition"
                                   placeholder="Feature request, partnership...">
                        </div>

                        <div>
                            <label for="contactMessage" class="block text-sm font-semibold text-gray-700 mb-1">Message</label>
                            <textarea id="contactMessage" name="message" rows="5" required
                                      class="w-full px-4 py-3 border-2 border-gray-100 rounded-2xl focus:outline-none focus:border-purple-primary focus:ring-2 focus:ring-purple-200 transition"
                                      placeholder="Tell us how we can help...">{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 bg-purple-primary text-white font-semibold rounded-2xl shadow-lg shadow-purple-200 hover:bg-purple-700 transition">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Send Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Sponsors Carousel Section -->
    @if(isset($showSponsors) && $showSponsors && $sponsors->count() > 0)
    <section class="bg-gray-50 py-12 sm:py-16">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 sm:mb-8 text-center">Our Sponsors</h2>

                <div class="relative">
                    <!-- Carousel Container -->
                    <div id="sponsorsCarousel" class="overflow-hidden px-12">
                        <div class="flex transition-transform duration-500 ease-in-out" id="sponsorsTrack" style="transform: translateX(0%);">
                            @foreach($sponsors as $sponsor)
                            <div class="shrink-0" style="width: 25%; padding: 0 1rem;">
                                <div class="bg-white rounded-lg shadow-md p-6 h-32 flex items-center justify-center hover:shadow-lg transition-shadow">
                                    <img src="{{ $sponsor->image_path }}"
                                         alt="{{ $sponsor->name }}"
                                         class="max-h-20 max-w-full object-contain"
                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <span class="hidden text-gray-600 font-medium">{{ $sponsor->name }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Navigation Arrows -->
                    @if($sponsors->count() > 4)
                    <button id="prevSponsor" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-3 shadow-md hover:bg-gray-100 transition-colors z-10">
                        <i class="fas fa-chevron-left text-purple-primary"></i>
                    </button>
                    <button id="nextSponsor" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-3 shadow-md hover:bg-gray-100 transition-colors z-10">
                        <i class="fas fa-chevron-right text-purple-primary"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </section>
    @endif

    @include('components.footer', [
        'socialLinks' => $socialLinks,
        'contactEmail' => $contactEmail,
        'contactWebsite' => $contactWebsite,
    ])

    @if(isset($showSponsors) && $showSponsors && $sponsors->count() > 0)
    <script>
        // Sponsors Carousel
        (function() {
            let currentIndex = 0;
            const track = document.getElementById('sponsorsTrack');
            const totalItems = {{ $sponsors->count() }};
            const itemsPerView = 4;
            let autoScrollInterval;

            function updateCarousel() {
                const maxIndex = Math.max(0, totalItems - itemsPerView);
                if (currentIndex > maxIndex) {
                    currentIndex = 0;
                }
                if (currentIndex < 0) {
                    currentIndex = maxIndex;
                }
                const translateX = -(currentIndex * (100 / itemsPerView));
                track.style.transform = `translateX(${translateX}%)`;
            }

            function moveNext() {
                currentIndex++;
                updateCarousel();
            }

            function movePrev() {
                currentIndex--;
                updateCarousel();
            }

            function startAutoScroll() {
                autoScrollInterval = setInterval(() => {
                    moveNext();
                }, 3000);
            }

            function stopAutoScroll() {
                if (autoScrollInterval) {
                    clearInterval(autoScrollInterval);
                }
            }

            const nextBtn = document.getElementById('nextSponsor');
            const prevBtn = document.getElementById('prevSponsor');
            const carousel = document.getElementById('sponsorsCarousel');

            if (nextBtn && prevBtn && carousel) {
                nextBtn.addEventListener('click', () => {
                    stopAutoScroll();
                    moveNext();
                    startAutoScroll();
                });

                prevBtn.addEventListener('click', () => {
                    stopAutoScroll();
                    movePrev();
                    startAutoScroll();
                });

                carousel.addEventListener('mouseenter', stopAutoScroll);
                carousel.addEventListener('mouseleave', startAutoScroll);

                // Start auto-scroll
                startAutoScroll();
            }
        })();
    </script>
    @endif

    <script>
        // Live Search Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchForm = document.getElementById('searchForm');
            const searchIcon = document.getElementById('searchIcon');
            const searchLoading = document.getElementById('searchLoading');
            const resultsContainer = document.getElementById('resultsContainer');

            let searchTimeout;
            let isSearching = false;

            // Original fresh reports HTML (to restore when search is cleared)
            const originalContent = resultsContainer ? resultsContainer.innerHTML : '';

            // Debounced live search
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const query = e.target.value.trim();

                    // Clear previous timeout
                    clearTimeout(searchTimeout);

                    // Hide loading if query is empty
                    if (query.length === 0) {
                        hideLoading();
                        restoreOriginalContent();
                        return;
                    }

                    // Show loading after a short delay
                    searchTimeout = setTimeout(() => {
                        showLoading();
                        performLiveSearch(query);
                    }, 500); // Wait 500ms after user stops typing
                });

                // Allow Enter key to submit form (traditional search)
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        clearTimeout(searchTimeout);
                        if (searchInput.value.trim().length > 0) {
                            searchForm.submit();
                        }
                    }
                });
            }

            function showLoading() {
                if (searchIcon) searchIcon.classList.add('hidden');
                if (searchLoading) searchLoading.classList.remove('hidden');
            }

            function hideLoading() {
                if (searchIcon) searchIcon.classList.remove('hidden');
                if (searchLoading) searchLoading.classList.add('hidden');
            }

            function performLiveSearch(query) {
                if (isSearching) return;

                isSearching = true;

                fetch(`{{ route('api.search') }}?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        isSearching = false;
                        hideLoading();

                        if (data.success) {
                            updateResults(data);
                        } else {
                            showNoResults(query);
                        }
                    })
                    .catch(error => {
                        isSearching = false;
                        hideLoading();
                        console.error('Search error:', error);
                        // On error, allow form submission
                    });
            }

            function updateResults(data) {
                if (data.count === 0) {
                    showNoResults(data.query);
                    return;
                }

                // Build results HTML
                let html = `
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 sm:mb-8 gap-3 sm:gap-4">
                        <div class="flex-1">
                            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900">
                                Search Results
                                <span class="block sm:inline text-base sm:text-lg font-normal text-gray-600 mt-1 sm:mt-0">for "${escapeHtml(data.query)}"</span>
                            </h2>
                            <p class="text-sm sm:text-base text-gray-500 mt-2">
                                <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                Found ${data.count} ${data.count === 1 ? 'item' : 'items'} matching your search
                            </p>
                        </div>
                        <a href="{{ route('welcome') }}" class="text-sm sm:text-base text-purple-primary hover:text-purple-600 underline whitespace-nowrap self-start sm:self-center" onclick="restoreOriginalContent(); return true;">
                            <i class="fas fa-times mr-2"></i>Clear Search
                        </a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                `;

                data.results.forEach(report => {
                    const typeClass = report.type === 'lost' ? 'bg-pink-100 text-pink-800' : 'bg-green-100 text-green-800';
                    const imagePath = report.image_path || '';
                    const imageError = "this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'400\\' height=\\'300\\'%3E%3Crect fill=\\'%23e5e7eb\\' width=\\'400\\' height=\\'300\\'/%3E%3Ctext fill=\\'%239ca3af\\' font-family=\\'sans-serif\\' font-size=\\'20\\' x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\' dy=\\'.3em\\'%3ENo Image%3C/text%3E%3C/svg%3E';";

                    html += `
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="relative h-48 bg-gray-100">
                                ${imagePath ?
                                    `<img src="${imagePath}" alt="${escapeHtml(report.title)}" class="w-full h-full object-cover" onerror="${imageError}">` :
                                    `<div class="w-full h-full flex items-center justify-center bg-gray-100"><i class="fas fa-image text-gray-400 text-4xl"></i></div>`
                                }
                                <span class="absolute top-3 right-3 px-3 py-1 ${typeClass} rounded-full text-xs font-semibold uppercase shadow-md">
                                    ${report.type.charAt(0).toUpperCase() + report.type.slice(1)}
                                </span>
                            </div>
                            <div class="p-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-4 line-clamp-2">${escapeHtml(report.title)}</h3>
                                <div class="space-y-2 text-sm text-gray-600 mb-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-map-marker-alt text-purple-primary mr-2"></i>
                                        <span class="line-clamp-1">${escapeHtml(report.location)}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-clock text-purple-primary mr-2"></i>
                                        <span>${escapeHtml(report.time_ago)}</span>
                                    </div>
                                </div>
                                <a href="/item/${report.upload_id}" class="text-purple-primary underline font-medium text-sm hover:text-purple-600">
                                    View Details
                                </a>
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
                if (resultsContainer) {
                    resultsContainer.innerHTML = html;
                }
            }

            function showNoResults(query) {
                const html = `
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 sm:mb-8 gap-3 sm:gap-4">
                        <div class="flex-1">
                            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900">
                                Search Results
                                <span class="block sm:inline text-base sm:text-lg font-normal text-gray-600 mt-1 sm:mt-0">for "${escapeHtml(query)}"</span>
                            </h2>
                        </div>
                        <a href="{{ route('welcome') }}" class="text-sm sm:text-base text-purple-primary hover:text-purple-600 underline whitespace-nowrap self-start sm:self-center" onclick="restoreOriginalContent(); return true;">
                            <i class="fas fa-times mr-2"></i>Clear Search
                        </a>
                    </div>
                    <div class="max-w-2xl mx-auto text-center py-12 sm:py-16 px-4">
                        <div class="mb-6">
                            <i class="fas fa-search text-gray-300 text-5xl sm:text-7xl mb-4"></i>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3 sm:mb-4">No Match Found</h3>
                        <p class="text-base sm:text-lg text-gray-600 mb-2">
                            We couldn't find any items matching "${escapeHtml(query)}".
                        </p>
                        <p class="text-sm sm:text-base text-gray-500 mb-6 sm:mb-8">
                            Don't worry! Upload your item and our smart system will automatically search for matches.
                            When someone posts a matching item, we'll notify you immediately so you can get reunited with your item.
                        </p>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3 sm:gap-4 mb-6">
                            <a href="/post?type=lost&search=${encodeURIComponent(query)}"
                               class="w-full sm:w-auto px-6 sm:px-8 py-3 sm:py-4 text-sm sm:text-base bg-pink-primary text-white rounded-lg font-medium hover:bg-pink-600 transition-colors shadow-md hover:shadow-lg flex items-center justify-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                I Lost Something
                            </a>
                            <a href="/post?type=found&search=${encodeURIComponent(query)}"
                               class="w-full sm:w-auto px-6 sm:px-8 py-3 sm:py-4 text-sm sm:text-base bg-purple-primary text-white rounded-lg font-medium hover:bg-purple-600 transition-colors shadow-md hover:shadow-lg flex items-center justify-center">
                                <i class="fas fa-hand-holding-heart mr-2"></i>
                                I Found Something
                            </a>
                        </div>
                        <div class="mt-6 sm:mt-8 pt-6 sm:pt-8 border-t border-gray-200">
                            <p class="text-xs sm:text-sm text-gray-500 mb-3 sm:mb-4">
                                <i class="fas fa-info-circle text-purple-primary mr-2"></i>
                                <strong>How it works:</strong> Our system uses advanced image matching and keyword analysis to automatically compare your post with existing reports. You'll be notified instantly when a match is found!
                            </p>
                            <a href="{{ route('welcome') }}" class="text-purple-primary hover:text-purple-600 underline text-xs sm:text-sm" onclick="restoreOriginalContent(); return true;">
                                <i class="fas fa-arrow-left mr-1"></i>Browse all items instead
                            </a>
                        </div>
                    </div>
                `;
                if (resultsContainer) {
                    resultsContainer.innerHTML = html;
                }
            }

            function restoreOriginalContent() {
                if (resultsContainer && originalContent) {
                    resultsContainer.innerHTML = originalContent;
                    if (searchInput) {
                        searchInput.value = '';
                    }
                }
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Animated counters (Lost & Found stats)
            const counterCards = document.querySelectorAll('[data-counter]');
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            function animateCounter(card) {
                const valueEl = card.querySelector('.counter-value');
                if (!valueEl) return;

                const target = parseInt(valueEl.dataset.target, 10) || 0;
                const threshold = parseInt(valueEl.dataset.plusThreshold, 10) || null;

                if (target === 0 || prefersReducedMotion) {
                    valueEl.textContent = target.toLocaleString();
                    if (threshold && target >= threshold) {
                        valueEl.textContent = `${valueEl.textContent}+`;
                    }
                    return;
                }

                const duration = 1600;
                const startTime = performance.now();

                function update(now) {
                    const elapsed = now - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const easedProgress = 1 - Math.pow(1 - progress, 3); // easeOutCubic
                    const currentValue = Math.floor(easedProgress * target);

                    valueEl.textContent = currentValue.toLocaleString();

                    if (progress < 1) {
                        requestAnimationFrame(update);
                    } else if (threshold && target >= threshold) {
                        valueEl.textContent = `${target.toLocaleString()}+`;
                    } else {
                        valueEl.textContent = target.toLocaleString();
                    }
                }

                requestAnimationFrame(update);
            }

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            if (!entry.target.dataset.animated) {
                                animateCounter(entry.target);
                                entry.target.dataset.animated = 'true';
                            }
                            obs.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.4 });

                counterCards.forEach(card => observer.observe(card));
            } else {
                counterCards.forEach(card => animateCounter(card));
            }

            // Make restoreOriginalContent available globally
            window.restoreOriginalContent = restoreOriginalContent;
        });
    </script>
    </body>
</html>



