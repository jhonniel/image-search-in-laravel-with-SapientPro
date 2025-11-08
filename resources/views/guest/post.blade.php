<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a {{ $itemType === 'found' ? 'Found' : 'Lost' }} Item - FindITFast</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --purple-primary:#8B5CF6; --pink-primary:#EC4899; }
        .text-purple-primary{ color: var(--purple-primary); }
        .text-pink-primary{ color: var(--pink-primary); }
        .bg-pink-primary{ background-color: var(--pink-primary); }
    </style>
    @php
        $illustration = asset('images/register.png');
    @endphp
</head>
<body class="min-h-screen bg-white">
    <!-- Top Logo -->
    <div class="px-6 pt-8">
        <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight">
            <span class="text-purple-primary">FindIT</span><span class="text-pink-primary">Fast</span>
        </h1>
    </div>

    <div class="container mx-auto px-6 py-8 md:py-12 min-h-[80vh] flex items-center">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-14 md:gap-20 items-start mx-auto w-full max-w-7xl">
            <!-- Illustration -->
            <div class="hidden md:block">
                <img src="{{ $illustration }}" alt="Illustration" class="w-full h-auto object-contain scale-105">
            </div>

            <!-- Card -->
            <div class="bg-[#F5F4FE] rounded-3xl shadow-xl p-10 md:p-14">
                <div class="mb-8">
                    <h2 class="text-5xl md:text-6xl font-extrabold text-[#213A8F] mb-2">{{ $itemType === 'found' ? 'Found' : 'Lost' }} Item</h2>
                    <p class="text-gray-600 text-lg">Fill in the details. You'll create an account next to publish it.</p>
                </div>

                <form class="space-y-6" method="POST" action="{{ route('guest.post.submit') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="item_type" value="{{ $itemType }}">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <input type="text" name="location" required class="w-full px-4 py-4 text-lg bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400" placeholder="Where was it lost/found?" value="{{ old('location') }}">
                        @error('location')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4" required class="w-full px-4 py-4 text-lg bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400" placeholder="Describe the item and key details">{{ old('description') }}</textarea>
                        @error('description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tags (comma separated)</label>
                        <input type="text" name="tags" class="w-full px-4 py-4 text-lg bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-pink-400" placeholder="wallet, black, leather" value="{{ old('tags') }}">
                        @error('tags')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Images</label>
                        <input type="file" name="images[]" multiple accept="image/*" required class="block w-full text-sm text-gray-700">
                        @error('images')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                        @error('images.*')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="w-full bg-pink-primary text-white font-semibold py-4 rounded-xl hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-pink-300 text-lg">Continue</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>


