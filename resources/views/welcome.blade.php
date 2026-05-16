<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindITFast — Lost and Found Platform</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="landing-page font-sans overflow-x-hidden">
    @include('landing.partials.header')

    <section class="landing-hero landing-section !pt-10 sm:!pt-14">
        <div class="landing-hero-glow" aria-hidden="true"></div>
        <div class="container relative mx-auto px-4 sm:px-6">
            <div class="mx-auto max-w-3xl text-center">
                <p class="landing-eyebrow mb-5">
                    <i class="fas fa-bolt text-amber-500"></i>
                    Community-powered lost &amp; found
                </p>
                <h1 class="landing-heading-xl mb-4">
                    From <span class="bg-gradient-to-r from-purple-600 to-pink-500 bg-clip-text text-transparent">lost</span> to found — in just a few clicks
                </h1>
                <p class="landing-subheading mx-auto mb-8">
                    Search reports, post what you lost or found, and let smart matching connect you with the right people.
                </p>

                <div class="landing-search-shell mx-auto mb-6 max-w-2xl">
                    <form action="{{ route('search') }}" method="GET" id="searchForm" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <div class="relative flex-1">
                            <input type="text" name="q" id="searchInput" value="{{ $searchQuery ?? '' }}"
                                   placeholder="Search wallets, phones, IDs, locations…"
                                   class="landing-input !pr-11"
                                   autocomplete="off">
                            <i class="fas fa-search absolute right-4 top-1/2 -translate-y-1/2 text-gray-400" id="searchIcon"></i>
                            <div id="searchLoading" class="absolute right-4 top-1/2 hidden -translate-y-1/2">
                                <i class="fas fa-spinner fa-spin text-purple-600"></i>
                            </div>
                        </div>
                        <button type="submit" class="landing-btn-primary w-full shrink-0 sm:w-auto">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>

                @if(isset($isSearch) && $isSearch)
                <div class="mb-6 flex flex-wrap items-center justify-center gap-2">
                    <a href="{{ route('search', ['q' => $searchQuery, 'status' => '']) }}"
                       class="{{ empty($statusFilter) ? 'landing-pill-active' : 'landing-pill-inactive' }}">All</a>
                    <a href="{{ route('search', ['q' => $searchQuery, 'status' => 'lost']) }}"
                       class="{{ $statusFilter === 'lost' ? 'landing-pill-active' : 'landing-pill-inactive' }}">Lost</a>
                    <a href="{{ route('search', ['q' => $searchQuery, 'status' => 'found']) }}"
                       class="{{ $statusFilter === 'found' ? 'landing-pill-active' : 'landing-pill-inactive' }}">Found</a>
                </div>
                @endif

                <div class="flex w-full max-w-md flex-col items-stretch justify-center gap-3 sm:mx-auto sm:max-w-none sm:flex-row sm:items-center">
                    <a href="{{ route('guest.post.form', ['type' => 'lost']) }}" class="landing-btn-outline w-full sm:w-auto">
                        <i class="fas fa-exclamation-circle text-rose-500"></i> I lost something
                    </a>
                    <a href="{{ route('guest.post.form', ['type' => 'found']) }}" class="landing-btn-purple w-full sm:w-auto">
                        <i class="fas fa-hand-holding-heart"></i> I found something
                    </a>
                </div>
            </div>
        </div>
    </section>


    <!-- City Illustration Section -->
    <section class="container mx-auto px-4 sm:px-6 py-8 sm:py-12">
        <div class="text-center mb-8 sm:mb-12">
            <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2 sm:mb-4">Available in Multiple Cities</h3>
            <p class="text-base sm:text-lg text-gray-600">Connect with people from all around</p>
        </div>

        <div class="w-full">
            <img src="{{ asset('images/city-skyline.png') }}" alt="City Skyline" class="w-full h-auto">
        </div>
    </section>

    <section class="landing-section">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="mx-auto mb-10 max-w-2xl text-center">
                <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 mb-2">Impact</p>
                <h2 class="landing-heading">Trusted by the community</h2>
            </div>
            <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 sm:grid-cols-3 sm:gap-8">
                <div class="landing-stat-card" data-counter>
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-100 text-purple-600">
                        <i class="fas fa-box-open text-2xl"></i>
                    </div>
                    <div class="text-4xl font-bold text-purple-600 counter-value mb-1" data-target="{{ $totalLostReports }}" data-plus-threshold="1000">0</div>
                    <p class="text-sm font-medium text-gray-600">Lost &amp; found reports</p>
                </div>
                <div class="landing-stat-card" data-counter>
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-pink-100 text-pink-600">
                        <i class="fas fa-hand-holding-heart text-2xl"></i>
                    </div>
                    <div class="text-4xl font-bold text-pink-600 counter-value mb-1" data-target="{{ $totalItemsReunited }}" data-plus-threshold="1000">0</div>
                    <p class="text-sm font-medium text-gray-600">Items reunited</p>
                </div>
                <div class="landing-stat-card" data-counter>
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-violet-600">
                        <i class="fas fa-map-marker-alt text-2xl"></i>
                    </div>
                    <div class="text-4xl font-bold text-violet-600 counter-value mb-1" data-target="{{ $totalLocations }}" data-plus-threshold="100">0</div>
                    <p class="text-sm font-medium text-gray-600">Locations covered</p>
                </div>
            </div>
        </div>
    </section>

    <section class="landing-section-alt">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="mx-auto mb-12 max-w-2xl text-center">
                <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 mb-2">Simple process</p>
                <h2 class="landing-heading">How FindITFast works</h2>
            </div>
            <div class="mx-auto grid max-w-6xl grid-cols-1 items-center gap-10 lg:grid-cols-2 lg:gap-14">
                <div class="space-y-4">
                    <div class="landing-step-card flex gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-purple-600 to-purple-500 text-lg font-bold text-white shadow-md">1</span>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Post it</h3>
                            <p class="mt-1 text-sm text-gray-600 leading-relaxed">Tell the community what you lost or found — photos and details help matching.</p>
                        </div>
                    </div>
                    <div class="landing-step-card flex gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-purple-600 to-purple-500 text-lg font-bold text-white shadow-md">2</span>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Track it</h3>
                            <p class="mt-1 text-sm text-gray-600 leading-relaxed">Image matching and keywords automatically compare your post with other reports.</p>
                        </div>
                    </div>
                    <div class="landing-step-card flex gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-pink-500 to-purple-600 text-lg font-bold text-white shadow-md">3</span>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Return it</h3>
                            <p class="mt-1 text-sm text-gray-600 leading-relaxed">Get notified on a match and coordinate safely to reunite items with owners.</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-gray-200/80 bg-white p-4 shadow-lg ring-1 ring-gray-900/5">
                    <img src="{{ asset('images/how-it-works.png') }}" alt="How it works" class="w-full h-auto rounded-xl">
                </div>
            </div>
        </div>
    </section>

    <!-- Search results only (hidden until user searches) -->
    <section class="landing-section {{ empty($isSearch) ? 'hidden' : '' }}" id="resultsSection">
        <div class="container mx-auto px-4 sm:px-6">
        <div class="mx-auto max-w-6xl" id="resultsContainer">
            @if(isset($isSearch) && $isSearch)
                <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 mb-1">Search</p>
                        <h2 class="landing-heading">
                            Results
                            @if(!empty($searchQuery))
                                <span class="mt-1 block text-base font-normal text-gray-500 sm:inline sm:ml-2">for “{{ $searchQuery }}”</span>
                            @endif
                        </h2>
                        @if(!$freshReports->isEmpty())
                            <p class="mt-2 text-sm text-gray-500">
                                <i class="fas fa-check-circle mr-1 text-emerald-500"></i>
                                {{ $freshReports->count() }} {{ $freshReports->count() === 1 ? 'match' : 'matches' }} found
                            </p>
                        @endif
                    </div>
                    <a href="{{ route('welcome') }}" class="landing-btn-ghost self-start">
                        <i class="fas fa-times"></i> Clear search
                    </a>
                </div>
                @if($freshReports->isEmpty())
                    <div class="landing-empty mx-auto max-w-xl">
                        <i class="fas fa-search mb-4 text-5xl text-gray-300"></i>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No match found</h3>
                        <p class="text-gray-600 text-lg mb-2">
                            We couldn't find any items matching "{{ $searchQuery }}".
                        </p>
                        <p class="text-gray-500 mb-8">
                            Don't worry! Upload your item and our smart system will automatically search for matches.
                            When someone posts a matching item, we'll notify you immediately so you can get reunited with your item.
                        </p>

                        <!-- Call to Action Buttons -->
                        <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
                            <a href="{{ route('guest.post.form', ['type' => 'lost', 'search' => $searchQuery ?? '']) }}" class="landing-btn-primary">
                                <i class="fas fa-exclamation-circle"></i> I lost something
                            </a>
                            <a href="{{ route('guest.post.form', ['type' => 'found', 'search' => $searchQuery ?? '']) }}" class="landing-btn-purple">
                                <i class="fas fa-hand-holding-heart"></i> I found something
                            </a>
                        </div>

                        <div class="mt-8 pt-8 border-t border-gray-200">
                            <p class="text-gray-500 text-sm mb-4">
                                <i class="fas fa-info-circle text-purple-primary mr-2"></i>
                                <strong>How it works:</strong> Our system uses advanced image matching and keyword analysis to automatically compare your post with existing reports. You'll be notified instantly when a match is found!
                            </p>
                            <a href="{{ route('welcome') }}" class="text-purple-primary hover:text-purple-600 underline text-sm">
                                <i class="fas fa-arrow-left mr-1"></i>Clear search
                            </a>
                        </div>
                    </div>
                @else
                    <div class="landing-results-grid">
                        @foreach($freshReports as $report)
                            @include('landing.partials.report-card', ['report' => $report])
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
        </div>
    </section>

    <!-- Top Helpers -->
    <section class="landing-section-alt">
        <div class="container mx-auto px-4 sm:px-6">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-6 sm:mb-8 gap-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 mb-1">Community</p>
                    <h2 class="landing-heading">Top helpers</h2>
                </div>
                <span class="text-sm text-gray-500">Most items successfully returned</span>
            </div>

            <div class="landing-card overflow-hidden">
                <div class="divide-y divide-gray-100">
                    @forelse($topHelpers as $index => $helper)
                    <div class="landing-helper-row">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <span class="landing-helper-rank {{ $index === 0 ? 'text-yellow-500' : 'text-gray-500' }}">#{{ $index + 1 }}</span>
                            <div class="landing-helper-user">
                                @if(!empty($helper['profile_picture']))
                                    <img src="{{ $helper['profile_picture'] }}" alt="{{ $helper['name'] }}" class="h-9 w-9 shrink-0 rounded-full border-2 border-purple-100 object-cover sm:h-10 sm:w-10">
                                @else
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-bold sm:h-10 sm:w-10 {{ $index === 0 ? 'bg-purple-100 text-purple-primary' : ($index === 1 ? 'bg-pink-100 text-pink-primary' : 'bg-blue-100 text-blue-600') }}">
                                        {{ $helper['initial'] }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-gray-900 text-sm sm:text-base">{{ $helper['name'] }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ $helper['city'] }}</p>
                                </div>
                            </div>
                        </div>
                        <span class="shrink-0 self-start rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-800 sm:self-center sm:px-3 sm:text-sm">{{ $helper['returned_count'] }} returned</span>
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

    <section class="landing-section">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="mx-auto mb-10 max-w-2xl text-center">
                <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 mb-2">Stories</p>
                <h2 class="landing-heading">What our users say</h2>
            </div>

            <div class="mx-auto max-w-4xl">
                <div class="landing-testimonial">
                    <i class="fas fa-quote-left mb-4 text-3xl text-purple-200"></i>
                    <p class="text-lg leading-relaxed text-gray-700 mb-6">
                        "I was honestly skeptical at first, but FindITFast proved me wrong. I lost my backpack at a coffee shop and within a few hours, someone posted it on the platform. The system matched my report, and we coordinated easily. I got everything back — even my notebook! Super thankful for this tool."
                    </p>
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 text-sm font-bold text-purple-700">AM</span>
                        <div>
                            <p class="font-semibold text-gray-900">Alyssa M.</p>
                            <p class="text-sm text-gray-500">Davao City</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($faqs->count())
    <!-- FAQ Section -->
    <section id="faq" class="landing-section-alt">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="mx-auto mb-10 max-w-2xl text-center">
                <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 mb-2">FAQ</p>
                <h2 class="landing-heading">Frequently asked questions</h2>
                <p class="mt-2 text-gray-600">A quick guide to how FindITFast reunites people with their belongings.</p>
            </div>

            <div class="mx-auto max-w-4xl space-y-3">
                @foreach($faqs as $index => $faq)
                <details class="landing-faq group">
                    <summary class="flex cursor-pointer items-start justify-between gap-3">
                        <div class="flex min-w-0 flex-1 items-start gap-2 text-left sm:gap-3">
                            <span class="shrink-0 text-sm font-semibold text-purple-600">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="faq-question text-base font-semibold text-gray-900 sm:text-lg">{{ $faq->question }}</span>
                        </div>
                        <span class="mt-0.5 shrink-0 text-purple-600 group-open:hidden">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="mt-0.5 shrink-0 text-purple-600 hidden group-open:block">
                            <i class="fas fa-minus"></i>
                        </span>
                    </summary>
                    <div class="px-4 pb-4 text-left sm:px-5 sm:pb-5">
                        <p class="text-gray-600 leading-relaxed">{{ $faq->answer }}</p>
                    </div>
                </details>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section id="contact-us" class="landing-section">
        <div class="container mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-2 lg:gap-12">
                <div class="landing-contact-panel">
                    <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 mb-2">Contact</p>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 sm:text-3xl lg:text-4xl">Need help or have feedback?</h2>
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

                <div class="landing-form-panel">
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
                                   class="landing-field"
                                   placeholder="Maria Santos">
                        </div>

                        <div>
                            <label for="contactEmail" class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                            <input type="email" id="contactEmail" name="email" value="{{ old('email') }}" required
                                   class="landing-field"
                                   placeholder="you@email.com">
                        </div>

                        <div>
                            <label for="contactSubject" class="block text-sm font-semibold text-gray-700 mb-1">Subject</label>
                            <input type="text" id="contactSubject" name="subject" value="{{ old('subject') }}"
                                   class="landing-field"
                                   placeholder="Feature request, partnership...">
                        </div>

                        <div>
                            <label for="contactMessage" class="block text-sm font-semibold text-gray-700 mb-1">Message</label>
                            <textarea id="contactMessage" name="message" rows="5" required
                                      class="landing-field"
                                      placeholder="Tell us how we can help...">{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="landing-btn-purple w-full">
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
                    <div id="sponsorsCarousel" class="overflow-hidden px-8 sm:px-12">
                        <div class="flex transition-transform duration-500 ease-in-out" id="sponsorsTrack" style="transform: translateX(0%);">
                            @foreach($sponsors as $sponsor)
                            <div class="landing-sponsor-slide">
                                <div class="flex h-28 items-center justify-center rounded-lg bg-white p-4 shadow-md transition-shadow hover:shadow-lg sm:h-32 sm:p-6">
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
                    @if($sponsors->count() > 1)
                    <button type="button" id="prevSponsor" class="absolute left-0 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white p-2 shadow-md transition-colors hover:bg-gray-100 sm:p-3" aria-label="Previous sponsors">
                        <i class="fas fa-chevron-left text-purple-primary"></i>
                    </button>
                    <button type="button" id="nextSponsor" class="absolute right-0 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white p-2 shadow-md transition-colors hover:bg-gray-100 sm:p-3" aria-label="Next sponsors">
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
            let autoScrollInterval;

            function getItemsPerView() {
                if (window.innerWidth >= 1024) return Math.min(4, totalItems);
                if (window.innerWidth >= 640) return Math.min(2, totalItems);
                return 1;
            }

            function updateCarousel() {
                const itemsPerView = getItemsPerView();
                const maxIndex = Math.max(0, totalItems - itemsPerView);
                if (currentIndex > maxIndex) currentIndex = 0;
                if (currentIndex < 0) currentIndex = maxIndex;
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

                window.addEventListener('resize', () => {
                    updateCarousel();
                });

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
            const resultsSection = document.getElementById('resultsSection');

            let searchTimeout;
            let isSearching = false;

            function showResultsSection() {
                if (resultsSection) resultsSection.classList.remove('hidden');
            }

            function hideResultsSection() {
                if (resultsSection) resultsSection.classList.add('hidden');
            }

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
                    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex-1">
                            <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 mb-1">Search</p>
                            <h2 class="landing-heading">Results <span class="mt-1 block text-base font-normal text-gray-500 sm:inline sm:ml-2">for "${escapeHtml(data.query)}"</span></h2>
                            <p class="mt-2 text-sm text-gray-500"><i class="fas fa-check-circle mr-1 text-emerald-500"></i>${data.count} ${data.count === 1 ? 'match' : 'matches'} found</p>
                        </div>
                        <a href="{{ route('welcome') }}" class="landing-btn-ghost self-start" onclick="restoreOriginalContent(); return true;"><i class="fas fa-times"></i> Clear search</a>
                    </div>
                    <div class="landing-results-grid">
                `;

                data.results.forEach(report => {
                    const typeClass = report.type === 'lost' ? 'bg-pink-100 text-pink-800' : 'bg-green-100 text-green-800';
                    
                    // Process detected objects
                    let detectedObjectsHtml = '';
                    if (report.detected_objects && Array.isArray(report.detected_objects) && report.detected_objects.length > 0) {
                        // Get unique labels (by name) and limit to top 3
                        const uniqueObjects = [];
                        const seenNames = new Set();
                        report.detected_objects.forEach(obj => {
                            const objName = (obj && typeof obj === 'object' ? obj.name : obj) || '';
                            if (objName && !seenNames.has(objName.toLowerCase())) {
                                seenNames.add(objName.toLowerCase());
                                uniqueObjects.push(obj);
                            }
                        });
                        
                        const displayedObjects = uniqueObjects.slice(0, 3);
                        
                        if (displayedObjects.length > 0) {
                            const objectsDisplay = displayedObjects.map(obj => {
                                const objName = (obj && typeof obj === 'object' ? obj.name : obj) || '';
                                const objScore = (obj && typeof obj === 'object' && obj.score) ? obj.score : 0.0;
                                const confidencePercent = Math.round(objScore * 100);
                                const escapedObj = escapeHtml(objName);
                                const title = confidencePercent ? `Detected from image (${confidencePercent}% confidence)` : 'Detected from image';
                                return `<span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium" title="${title}"><i class="fas fa-eye mr-1"></i>${escapedObj}</span>`;
                            }).join('');
                            
                            detectedObjectsHtml = `
                                <div class="mb-3">
                                    <h4 class="font-semibold text-gray-900 mb-2 text-sm flex items-center">
                                        <i class="fas fa-cube mr-1 text-blue-600"></i>
                                        Detected Objects (${displayedObjects.length}):
                                    </h4>
                                    <div class="flex flex-wrap gap-2">
                                        ${objectsDisplay}
                                    </div>
                                </div>
                            `;
                        }
                    }

                    const imagePath = report.image_path || '';
                    const imageError = "this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'400\\' height=\\'300\\'%3E%3Crect fill=\\'%23e5e7eb\\' width=\\'400\\' height=\\'300\\'/%3E%3Ctext fill=\\'%239ca3af\\' font-family=\\'sans-serif\\' font-size=\\'20\\' x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\' dy=\\'.3em\\'%3ENo Image%3C/text%3E%3C/svg%3E';";
                    const showImage = imagePath && imagePath.trim() !== '';
                    
                    html += `
                        <div class="landing-card group flex h-full flex-col">
                            ${showImage ? `
                            <!-- Image Section -->
                            <div class="relative h-48 bg-gray-100 group overflow-hidden">
                                <img src="${imagePath}" 
                                     alt="${escapeHtml(report.title)}" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                     onerror="${imageError}">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <span class="absolute top-3 left-3 px-4 py-2 ${report.type === 'lost' ? 'bg-pink-100 text-pink-800' : 'bg-green-100 text-green-800'} rounded-full text-sm font-bold uppercase tracking-wide shadow-md z-10">
                                    ${report.type.charAt(0).toUpperCase() + report.type.slice(1)}
                                </span>
                            </div>
                            ` : ''}
                            <!-- Content Section -->
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4 gap-3">
                                    <h3 class="text-lg font-black text-gray-900 line-clamp-2 flex-1 leading-tight">${escapeHtml(report.title)}</h3>
                                    ${!showImage ? `
                                    <!-- Status Badge (only show if no image) -->
                                    <span class="px-4 py-2 ${report.type === 'lost' ? 'bg-pink-100 text-pink-800' : 'bg-green-100 text-green-800'} rounded-full text-sm font-bold uppercase tracking-wide shadow-md shrink-0">
                                        ${report.type.charAt(0).toUpperCase() + report.type.slice(1)}
                                    </span>
                                    ` : ''}
                                </div>
                                <div class="space-y-3 mb-4">
                                    <div class="flex items-start space-x-2 bg-purple-50 rounded-lg p-3 border border-purple-100">
                                        <i class="fas fa-map-marker-alt text-purple-600 mt-0.5 text-sm flex-shrink-0"></i>
                                        <span class="text-sm text-gray-700 font-medium line-clamp-2 leading-relaxed">${escapeHtml(report.location)}</span>
                                    </div>
                                    ${detectedObjectsHtml}
                                    <div class="flex items-center text-xs text-gray-600 pt-2 border-t border-gray-100">
                                        <i class="fas fa-clock text-purple-600 mr-2 text-sm"></i>
                                        <span class="font-semibold">${escapeHtml(report.time_ago)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
                if (resultsContainer) {
                    showResultsSection();
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
                                <i class="fas fa-arrow-left mr-1"></i>Clear search
                            </a>
                        </div>
                    </div>
                `;
                if (resultsContainer) {
                    showResultsSection();
                    resultsContainer.innerHTML = html;
                }
            }

            function restoreOriginalContent() {
                if (resultsContainer) {
                    resultsContainer.innerHTML = '';
                }
                hideResultsSection();
                if (searchInput) {
                    searchInput.value = '';
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



