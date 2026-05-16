@php
    $socialLinks = $socialLinks ?? [];
    $contactEmail = $contactEmail ?? 'fif@ifinditfast.com';
    $contactWebsite = $contactWebsite ?? 'finditfast.com';
@endphp

<footer class="border-t border-gray-200/80 bg-white">
    <div class="container mx-auto px-4 py-12 sm:px-6 sm:py-16">
        <div class="mb-10 grid grid-cols-1 gap-10 md:grid-cols-2 md:gap-16">
            <div>
                <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-purple-600 to-pink-500 text-white text-sm">
                        <i class="fas fa-search-location"></i>
                    </span>
                    <span class="text-xl font-bold tracking-tight">
                        <span class="text-purple-600">FindIT</span><span class="text-pink-500">Fast</span>
                    </span>
                </a>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-gray-600">
                    Reuniting people with their lost items — fast, easy, and smart.
                </p>

                <div class="mt-6 flex flex-wrap gap-2">
                    @if(!empty($socialLinks['facebook']))
                        <a href="{{ $socialLinks['facebook'] }}" target="_blank" rel="noopener noreferrer" class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-purple-600 transition hover:border-purple-200 hover:bg-purple-50">
                            <i class="fab fa-facebook-f text-sm"></i>
                        </a>
                    @endif
                    @if(!empty($socialLinks['instagram']))
                        <a href="{{ $socialLinks['instagram'] }}" target="_blank" rel="noopener noreferrer" class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-purple-600 transition hover:border-purple-200 hover:bg-purple-50">
                            <i class="fab fa-instagram text-sm"></i>
                        </a>
                    @endif
                    @if(!empty($socialLinks['twitter']))
                        <a href="{{ $socialLinks['twitter'] }}" target="_blank" rel="noopener noreferrer" class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-purple-600 transition hover:border-purple-200 hover:bg-purple-50">
                            <i class="fab fa-twitter text-sm"></i>
                        </a>
                    @endif
                    @if(!empty($socialLinks['linkedin']))
                        <a href="{{ $socialLinks['linkedin'] }}" target="_blank" rel="noopener noreferrer" class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-purple-600 transition hover:border-purple-200 hover:bg-purple-50">
                            <i class="fab fa-linkedin-in text-sm"></i>
                        </a>
                    @endif
                    @if(!empty($socialLinks['youtube']))
                        <a href="{{ $socialLinks['youtube'] }}" target="_blank" rel="noopener noreferrer" class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-purple-600 transition hover:border-purple-200 hover:bg-purple-50">
                            <i class="fab fa-youtube text-sm"></i>
                        </a>
                    @endif
                    @if(!empty($socialLinks['tiktok']))
                        <a href="{{ $socialLinks['tiktok'] }}" target="_blank" rel="noopener noreferrer" class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-purple-600 transition hover:border-purple-200 hover:bg-purple-50">
                            <i class="fab fa-tiktok text-sm"></i>
                        </a>
                    @endif
                </div>

                <div class="mt-6 space-y-2 text-sm text-gray-600">
                    <p class="flex items-start gap-2 break-all"><i class="fas fa-envelope mt-0.5 w-4 shrink-0 text-purple-600"></i><span>{{ $contactEmail }}</span></p>
                    <p class="flex items-center gap-2"><i class="fas fa-globe w-4 text-purple-600"></i>{{ $contactWebsite }}</p>
                </div>
            </div>

            <div class="md:justify-self-end">
                <h4 class="text-sm font-semibold uppercase tracking-wider text-gray-900">Support</h4>
                <ul class="mt-4 space-y-2.5">
                    <li><a href="#faq" class="text-sm text-gray-600 transition hover:text-purple-600">FAQs</a></li>
                    <li><a href="#contact-us" class="text-sm text-gray-600 transition hover:text-purple-600">Contact us</a></li>
                    <li><a href="/contributors" class="text-sm text-gray-600 transition hover:text-purple-600">Contributors</a></li>
                    <li><a href="{{ route('privacy') }}" class="text-sm text-gray-600 transition hover:text-purple-600">Privacy policy</a></li>
                    <li><a href="{{ route('terms') }}" class="text-sm text-gray-600 transition hover:text-purple-600">Terms &amp; conditions</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6 text-center text-sm text-gray-500">
            © {{ date('Y') }} FindITFast — Built with care for the city we love.
        </div>
    </div>
</footer>
