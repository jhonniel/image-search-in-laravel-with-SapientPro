<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - FindITFast</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
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
                <p class="text-sm uppercase tracking-widest text-purple-primary font-semibold mb-3">Privacy Policy</p>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">Your privacy matters to us.</h1>
                <p class="text-base sm:text-lg text-gray-600">This policy explains what information we collect, why we collect it, and how we protect it as you use FindITFast.</p>
                <p class="text-sm text-gray-500 mt-4">Updated {{ now()->format('F d, Y') }}</p>
            </div>

            <div class="px-6 sm:px-10 py-10 space-y-10 text-base leading-relaxed text-gray-700">
                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">1. Information We Collect</h2>
                    <p class="mb-3">We collect information when you create an account, submit lost/found reports, send messages, or contact support. Typical data includes:</p>
                    <ul class="list-disc list-inside space-y-2">
                        <li>Profile details (name, email, avatar, location)</li>
                        <li>Item metadata (photos, descriptions, timestamps, geodata)</li>
                        <li>Message content and notifications</li>
                        <li>Technical diagnostics (device type, IP address, performance logs)</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">2. How We Use Your Data</h2>
                    <p class="mb-3">Data is only used to deliver and improve FindITFast services:</p>
                    <ul class="list-disc list-inside space-y-2">
                        <li>Match lost and found items via search and similarity detection</li>
                        <li>Send status updates, alerts, and safety notifications</li>
                        <li>Prevent fraud, abuse, or suspicious activity</li>
                        <li>Analyze platform performance and prioritize new features</li>
                    </ul>
                    <p class="mt-3">We never sell your personal information or share it with advertisers.</p>
                </section>

                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">3. Data Sharing & Disclosure</h2>
                    <p class="mb-3">We share data only when necessary:</p>
                    <ul class="list-disc list-inside space-y-2">
                        <li>With verified partners that run our storage, messaging, or notification services (all bound by confidentiality)</li>
                        <li>When legally required (court order, subpoena, or law enforcement request)</li>
                        <li>To protect someone’s safety or prevent fraud</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">4. Data Retention & Security</h2>
                    <p class="mb-3">We store your data while your account remains active, or for as long as needed to deliver services. Once deleted, your data is purged from backups on a scheduled basis.</p>
                    <p class="mb-3">Security protections include:</p>
                    <ul class="list-disc list-inside space-y-2">
                        <li>Encryption in transit (HTTPS/TLS) and at rest</li>
                        <li>Role-based access for staff and automated monitoring</li>
                        <li>Redundancy and automatic backups in secure regions</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-bold text-gray-900 mb-3">5. Your Rights</h2>
                    <p class="mb-3">You can request to access, update, download, or delete your personal information at any time via Support. We will respond within 15 days.</p>
                    <p>If you believe your privacy has been compromised, email <a class="text-purple-primary font-semibold" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a> and we will investigate immediately.</p>
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

