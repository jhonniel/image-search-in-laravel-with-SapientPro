<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FindITFast</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen bg-white">
    @php
        $loginImageUrl = null;
        try {
            $allFiles = \Illuminate\Support\Facades\Storage::files('public/uploads');
            $imageFiles = array_values(array_filter($allFiles, function ($f) {
                return preg_match('/\.(png|jpe?g|svg)$/i', $f);
            }));
            if (count($imageFiles) > 0) {
                $loginImageUrl = \Illuminate\Support\Facades\Storage::url($imageFiles[0]);
            }
        } catch (\Throwable $e) {
            $loginImageUrl = null;
        }
        if (!$loginImageUrl) {
            $loginImageUrl = asset('images/login.png');
        }
    @endphp

    <!-- Top Logo -->
    <div class="px-4 sm:px-6 pt-6 sm:pt-8">
        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight">
            <span class="text-purple-primary">FindIT</span><span class="text-pink-primary">Fast</span>
        </h1>
    </div>

    <div class="container mx-auto px-4 sm:px-6 py-6 sm:py-8 md:py-12 min-h-[80vh] flex items-center">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-14 md:gap-20 items-center mx-auto w-full max-w-7xl">
            <!-- Illustration -->
            <div class="hidden md:block">
                <img src="{{ $loginImageUrl }}" alt="Login Illustration" class="w-full h-auto object-contain scale-105">
            </div>

            <!-- Login Card -->
            <div class="bg-[#F5F4FE] rounded-3xl shadow-xl p-10 md:p-14">
            <!-- Logo and Header -->
            <div class="mb-10 text-center md:text-left">
                <h1 class="text-6xl md:text-7xl font-extrabold text-[#213A8F] mb-10">Login</h1>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email Field -->
                <div>
                    <label for="login" class="block text-sm font-medium text-gray-700 mb-2">Email or Username</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"><i class="fas fa-user"></i></span>
                        <input type="text"
                               id="login"
                               name="login"
                               value="{{ old('login') }}"
                               class="w-full pl-10 pr-4 py-4 text-lg bg-white border border-gray-300 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:border-transparent"
                               placeholder="Enter email or username"
                               required>
                    </div>
                    @error('login')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"><i class="fas fa-lock"></i></span>
                        <input type="password"
                               id="password"
                               name="password"
                               class="w-full pl-10 pr-12 py-4 text-lg bg-white border border-gray-300 rounded-xl text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-pink-400 focus:border-transparent"
                               placeholder="Password"
                               required>
                        <button type="button"
                                onclick="togglePassword()"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-700 transition-colors text-lg">
                            <i id="password-icon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-pink-500 border-gray-300 rounded focus:ring-pink-400">
                        <span class="ml-2 text-gray-600">Remember me?</span>
                    </label>
                    <a href="#" class="text-gray-600 hover:text-gray-900">Forgot Password?</a>
                </div>

                <!-- Login Button -->
                <button type="submit" class="w-full bg-pink-primary text-white font-semibold py-4 px-4 rounded-xl hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-pink-300 transition-all text-lg">LOGIN</button>

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="bg-red-500/20 border border-red-500/30 rounded-lg p-3">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-300 mr-2"></i>
                            <span class="text-red-300 text-sm">Invalid credentials. Please try again.</span>
                        </div>
                    </div>
                @endif
            </form>

            <!-- Footer -->
            <div class="mt-6 text-center text-sm">
                <p class="text-gray-600">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="text-pink-primary hover:text-pink-600 font-medium">Sign up here</a>
                </p>
            </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('password-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
