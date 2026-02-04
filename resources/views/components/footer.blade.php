@php
    $socialLinks = $socialLinks ?? [];
    $contactEmail = $contactEmail ?? 'fif@ifinditfast.com';
    $contactWebsite = $contactWebsite ?? 'finditfast.com';
@endphp

<footer class="bg-purple-50 py-12 sm:py-16">
    <div class="container mx-auto px-4 sm:px-6">
        <div class="max-w-none grid grid-cols-1 md:grid-cols-2 gap-12 sm:gap-24 mb-8 sm:mb-10">
            <div>
                <h3 class="text-3xl sm:text-4xl md:text-5xl font-extrabold mb-4 sm:mb-6">
                    <span class="text-purple-primary">FindIT</span>
                    <span class="text-pink-primary">Fast</span>
                </h3>
                <p class="text-gray-700 text-base sm:text-lg md:text-xl mb-6 sm:mb-8">
                    Reuniting people with their lost items—fast, easy, and smart.
                </p>

                <div class="flex space-x-5 mb-8">
                    @if(!empty($socialLinks['facebook']))
                        <a href="{{ $socialLinks['facebook'] }}" target="_blank" rel="noopener noreferrer" class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-purple-primary hover:bg-purple-primary hover:text-white transition-colors shadow-md">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    @endif
                    @if(!empty($socialLinks['instagram']))
                        <a href="{{ $socialLinks['instagram'] }}" target="_blank" rel="noopener noreferrer" class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-purple-primary hover:bg-purple-primary hover:text-white transition-colors shadow-md">
                            <i class="fab fa-instagram"></i>
                        </a>
                    @endif
                    @if(!empty($socialLinks['twitter']))
                        <a href="{{ $socialLinks['twitter'] }}" target="_blank" rel="noopener noreferrer" class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-purple-primary hover:bg-purple-primary hover:text-white transition-colors shadow-md">
                            <i class="fab fa-twitter"></i>
                        </a>
                    @endif
                    @if(!empty($socialLinks['linkedin']))
                        <a href="{{ $socialLinks['linkedin'] }}" target="_blank" rel="noopener noreferrer" class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-purple-primary hover:bg-purple-primary hover:text-white transition-colors shadow-md">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    @endif
                    @if(!empty($socialLinks['youtube']))
                        <a href="{{ $socialLinks['youtube'] }}" target="_blank" rel="noopener noreferrer" class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-purple-primary hover:bg-purple-primary hover:text-white transition-colors shadow-md">
                            <i class="fab fa-youtube"></i>
                        </a>
                    @endif
                    @if(!empty($socialLinks['tiktok']))
                        <a href="{{ $socialLinks['tiktok'] }}" target="_blank" rel="noopener noreferrer" class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-purple-primary hover:bg-purple-primary hover:text-white transition-colors shadow-md">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    @endif
                </div>

                <div class="space-y-3">
                    <div class="flex items-center text-gray-700 text-xl">
                        <i class="fas fa-envelope mr-3 text-purple-primary"></i>
                        <span>{{ $contactEmail }}</span>
                    </div>
                    <div class="flex items-center text-gray-700 text-xl">
                        <i class="fas fa-globe mr-3 text-purple-primary"></i>
                        <span>{{ $contactWebsite }}</span>
                    </div>
                </div>
            </div>

            <div class="md:justify-self-end md:text-right">
                <h4 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-pink-primary mb-4 sm:mb-6">Support</h4>
                <ul class="space-y-3 sm:space-y-4">
                    <li><a href="#faq" class="text-lg sm:text-xl md:text-2xl text-gray-700 hover:text-purple-primary transition-colors">FAQs</a></li>
                    <li><a href="#contact-us" class="text-lg sm:text-xl md:text-2xl text-gray-700 hover:text-purple-primary transition-colors">Contact Us</a></li>
                    <li><a href="/contributors" class="text-lg sm:text-xl md:text-2xl text-gray-700 hover:text-purple-primary transition-colors">Contributors</a></li>
                    <li><a href="{{ route('privacy') }}" class="text-lg sm:text-xl md:text-2xl text-gray-700 hover:text-purple-primary transition-colors">Privacy Policy</a></li>
                    <li><a href="{{ route('terms') }}" class="text-lg sm:text-xl md:text-2xl text-gray-700 hover:text-purple-primary transition-colors">Terms &amp; Conditions</a></li>
                </ul>
            </div>
        </div>

        <div class="max-w-6xl mx-auto border-t border-gray-300 pt-6 sm:pt-8 text-center">
            <p class="text-gray-700 text-base sm:text-lg md:text-2xl">
                © 2025 FindITFast — Built with care for the city we love.
            </p>
        </div>
    </div>
</footer>

