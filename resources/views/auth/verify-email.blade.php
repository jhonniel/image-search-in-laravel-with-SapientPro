<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Email - FindITFast</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-white min-h-screen">
    <div class="px-6 pt-8">
        <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight">
            <span class="text-purple-primary">FindIT</span><span class="text-pink-primary">Fast</span>
        </h1>
        <p class="text-gray-600 mt-2">Confirm your email to activate your account</p>
    </div>

    <div class="container mx-auto px-6 py-8 md:py-12 min-h-[70vh] flex items-center justify-center">
        <div class="bg-[#F5F4FE] rounded-3xl shadow-xl p-10 md:p-14 w-full max-w-lg">
            <div class="mb-8 text-center md:text-left">
                <h2 class="text-4xl md:text-5xl font-extrabold text-[#213A8F] mb-2">Verify email</h2>
                <p class="text-gray-600 text-base">
                    We sent a {{ config('email_otp.length', 6) }}-digit code to
                    <strong class="text-gray-800">{{ $email }}</strong>
                </p>
            </div>

            @if(session('status'))
                <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg text-sm">
                    <i class="fas fa-info-circle mr-2"></i>{{ session('status') }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                </div>
            @endif

            @error('otp')
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                </div>
            @enderror

            <form method="POST" action="{{ route('verification.verify') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="otp" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-key mr-2 text-purple-primary"></i>Verification code
                    </label>
                    <input
                        type="text"
                        id="otp"
                        name="otp"
                        value="{{ old('otp') }}"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="8"
                        class="w-full px-4 py-4 text-center text-2xl tracking-[0.4em] font-bold border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent @error('otp') border-red-500 @enderror"
                        placeholder="••••••"
                        required
                        autofocus
                    >
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-purple-primary text-white font-semibold hover:bg-purple-600 transition-colors">
                    Verify and continue
                </button>
            </form>

            <form method="POST" action="{{ route('verification.resend') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full py-3 rounded-xl border border-purple-200 text-purple-700 font-medium hover:bg-purple-50 transition-colors">
                    Resend code
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-500">
                Wrong account?
                <a href="{{ route('logout') }}"
                   class="text-pink-primary hover:text-pink-600 font-medium"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Sign out
                </a>
            </p>
            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                @csrf
            </form>
        </div>
    </div>
</body>
</html>
