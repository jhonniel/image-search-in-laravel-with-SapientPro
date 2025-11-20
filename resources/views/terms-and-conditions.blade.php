<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms &amp; Conditions - FindITFast</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800">
    <header class="sticky top-0 z-50 bg-white shadow-sm">
        <div class="container mx-auto px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
            <a href="{{ route('welcome') }}" class="text-2xl font-extrabold tracking-tight">
                <span class="text-purple-primary">FindIT</span>
                <span class="text-pink-primary">Fast</span>
            </a>
            <a href="{{ route('welcome') }}" class="inline-flex items-center text-sm sm:text-base text-purple-primary font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>Back to Home
            </a>
        </div>
    </header>

    <main class="container mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <div class="max-w-4xl mx-auto bg-white rounded-3xl shadow-lg border border-gray-100">
            <div class="px-6 sm:px-10 py-10 sm:py-12 border-b border-gray-100">
                <p class="text-sm uppercase tracking-widest text-purple-primary font-semibold mb-3">Terms &amp; Conditions</p>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">Guidelines for using FindITFast.</h1>
                <p class="text-base sm:text-lg text-gray-600">By accessing or using FindITFast, you agree to these terms designed to keep our community trustworthy, safe, and respectful.</p>
                <p class="text-sm text-gray-500 mt-4">Updated {{ now()->format('F d, Y') }}</p>
            </div>

            <div class="px-6 sm:px-10 py-10 space-y-10 text-base leading-relaxed text-gray-700">
                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">1. Eligibility & Accounts</h2>
                    <p>You must be at least 16 years old (or the minimum age in your location) to create an account. You are responsible for safeguarding your password and all activity under your account.</p>
                </section>

                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">2. Acceptable Use</h2>
                    <p class="mb-3">You agree not to:</p>
                    <ul class="list-disc list-inside space-y-2">
                        <li>Upload false claims, stolen goods, or misleading information</li>
                        <li>Harass, spam, or defraud other members</li>
                        <li>Attempt to access private data or interfere with platform operations</li>
                        <li>Use the service for commercial solicitation without consent</li>
                    </ul>
                    <p class="mt-3">We reserve the right to suspend or remove accounts that violate these rules.</p>
                </section>

                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">3. User Content</h2>
                    <p>By posting descriptions, photos, or messages, you grant FindITFast a non-exclusive license to display and process that content to deliver the service. You retain ownership of your submissions.</p>
                </section>

                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">4. Disclaimers</h2>
                    <p>FindITFast acts as a community platform and does not guarantee the outcome of any lost and found case. Users are responsible for verifying claims and meeting safely. We are not liable for user conduct, damages, or losses that occur offline.</p>
                </section>

                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">5. Termination</h2>
                    <p>We may suspend or terminate access for behavior that puts other members or the platform at risk. You can close your account anytime by contacting Support; archived data will be handled per our Privacy Policy.</p>
                </section>

                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">6. Updates to These Terms</h2>
                    <p>We will notify you about significant changes via email or in-app banners. Continued use after updates constitutes acceptance of the revised terms.</p>
                </section>

                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">7. Contact</h2>
                    <p>Questions about these terms? Email <a href="mailto:{{ $contactEmail }}" class="text-purple-primary font-semibold">{{ $contactEmail }}</a> and we’ll respond promptly.</p>
                </section>
            </div>
        </div>
    </main>

    @include('components.footer', [
        'socialLinks' => $socialLinks ?? [],
        'contactEmail' => $contactEmail ?? null,
        'contactWebsite' => $contactWebsite ?? null,
    ])
</body>
</html>

