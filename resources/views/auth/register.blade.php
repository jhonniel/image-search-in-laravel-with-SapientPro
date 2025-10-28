<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - FindITFast</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'purple-primary': '#8B5CF6',
                        'purple-light': '#A78BFA',
                        'pink-primary': '#EC4899',
                        'pink-light': '#F472B6',
                        'blue-primary': '#3B82F6',
                        'blue-light': '#60A5FA',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white min-h-screen">
    @php
        $registerImageUrl = null;
        try {
            $allFiles = \Illuminate\Support\Facades\Storage::files('public/uploads');
            $imageFiles = array_values(array_filter($allFiles, function ($f) { return preg_match('/\.(png|jpe?g|svg)$/i', $f); }));
            if (count($imageFiles) > 0) { $registerImageUrl = \Illuminate\Support\Facades\Storage::url($imageFiles[0]); }
        } catch (\Throwable $e) { $registerImageUrl = null; }
        if (!$registerImageUrl) { $registerImageUrl = asset('images/register.png'); }
    @endphp

    <!-- Top Logo -->
    <div class="px-6 pt-8">
        <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight">
            <span class="text-purple-primary">FindIT</span><span class="text-pink-primary">Fast</span>
        </h1>
        <p class="text-gray-600 mt-2">Create your account to start finding and reporting items</p>
    </div>

    <div class="container mx-auto px-6 py-8 md:py-12 min-h-[80vh] flex items-center">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-14 md:gap-20 items-center mx-auto w-full max-w-7xl">
            <!-- Illustration -->
            <div class="hidden md:block">
                <img src="{{ $registerImageUrl }}" alt="Register Illustration" class="w-full h-auto object-contain scale-105">
            </div>

            <!-- Registration Form Card -->
            <div class="bg-[#F5F4FE] rounded-3xl shadow-xl p-10 md:p-14">
            <div class="mb-8">
                <h2 class="text-5xl md:text-6xl font-extrabold text-[#213A8F] mb-2">Sign up</h2>
                <p class="text-gray-600 text-lg">Join our community to help find lost items</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-id-card mr-2 text-purple-primary"></i>Full Name
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent transition-colors @error('name') border-red-500 @enderror"
                           placeholder="Enter your full name"
                           required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-purple-primary"></i>Username
                    </label>
                    <input type="text"
                           id="username"
                           name="username"
                           value="{{ old('username') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent transition-colors"
                           placeholder="Enter your username">
                </div>

                <!-- Email (required for notifications) -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-purple-primary"></i>Email Address
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent transition-colors @error('email') border-red-500 @enderror"
                           placeholder="Enter your email address"
                           required>
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-purple-primary"></i>Password
                    </label>
                    <div class="relative">
                        <input type="password"
                               id="password"
                               name="password"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent transition-colors @error('password') border-red-500 @enderror"
                               placeholder="Create a strong password"
                               required>
                        <button type="button"
                                onclick="togglePassword('password')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="password-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-purple-primary"></i>Confirm Password
                    </label>
                    <div class="relative">
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-primary focus:border-transparent transition-colors"
                               placeholder="Confirm your password"
                               required>
                        <button type="button"
                                onclick="togglePassword('password_confirmation')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="password_confirmation-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="flex items-start">
                    <input type="checkbox"
                           id="terms"
                           name="terms"
                           class="mt-1 text-purple-primary focus:ring-purple-primary rounded"
                           required>
                    <label for="terms" class="ml-3 text-sm text-gray-600">
                        I agree to the
                        <a href="#" class="text-purple-primary hover:text-purple-600 font-medium">Terms of Service</a>
                        and
                        <a href="#" class="text-purple-primary hover:text-purple-600 font-medium">Privacy Policy</a>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full bg-gradient-to-r from-purple-primary to-pink-primary text-white py-3 px-4 rounded-lg font-semibold hover:from-purple-600 hover:to-pink-600 transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-purple-primary focus:ring-offset-2">
                    <i class="fas fa-user-plus mr-2"></i>
                    Create Account
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-gray-700">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-pink-primary hover:text-pink-600 font-semibold transition-colors">Sign in here</a>
                </p>
            </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const eye = document.getElementById(fieldId + '-eye');

            if (field.type === 'password') {
                field.type = 'text';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    </script>
</body>
</html>
