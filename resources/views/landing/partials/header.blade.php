<header class="landing-nav relative">
    <div class="landing-container flex items-center justify-between gap-3 py-3.5 sm:py-4">
        <a href="{{ route('welcome') }}" class="group flex min-w-0 items-center gap-2 sm:gap-2.5">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-purple-600 to-pink-500 text-white shadow-lg shadow-purple-500/25 transition group-hover:scale-105 sm:h-10 sm:w-10">
                <i class="fas fa-search-location text-sm"></i>
            </span>
            <span class="truncate text-lg font-bold tracking-tight sm:text-2xl">
                <span class="text-purple-600">FindIT</span><span class="text-pink-500">Fast</span>
            </span>
        </a>

        <div class="flex items-center gap-2">
            <nav class="hidden items-center gap-1 sm:flex sm:gap-2 md:gap-3">
                <a href="#faq" class="landing-btn-ghost hidden md:inline-flex">FAQ</a>
                <a href="#contact-us" class="landing-btn-ghost hidden lg:inline-flex">Contact</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="landing-btn-outline !px-3 !py-2 text-sm sm:!px-4 sm:!py-2.5">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="landing-btn-ghost hidden min-[400px]:inline-flex">Login</a>
                    <a href="{{ route('register') }}" class="landing-btn-purple !px-3 !py-2 text-sm sm:!px-4 sm:!py-2.5">Sign up</a>
                @endauth
            </nav>

            <button type="button" id="landingNavToggle" class="landing-icon-btn sm:hidden" aria-expanded="false" aria-controls="landingMobileNav" aria-label="Open menu">
                <i class="fas fa-bars text-lg" id="landingNavToggleIcon"></i>
            </button>
        </div>
    </div>

    <div id="landingMobileNav" class="landing-mobile-nav hidden sm:hidden" hidden>
        <nav class="flex flex-col gap-1">
            <a href="#faq" class="landing-btn-ghost w-full justify-start">FAQ</a>
            <a href="#contact-us" class="landing-btn-ghost w-full justify-start">Contact</a>
            @auth
                <a href="{{ route('dashboard') }}" class="landing-btn-outline w-full justify-center">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="landing-btn-ghost w-full justify-start">Login</a>
                <a href="{{ route('register') }}" class="landing-btn-purple w-full justify-center">Sign up</a>
            @endauth
        </nav>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('landingNavToggle');
        const panel = document.getElementById('landingMobileNav');
        const icon = document.getElementById('landingNavToggleIcon');
        if (!toggle || !panel) return;

        toggle.addEventListener('click', function () {
            const open = panel.classList.toggle('hidden');
            const isOpen = !open;
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            toggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
            panel.hidden = !isOpen;
            if (icon) {
                icon.classList.toggle('fa-bars', !isOpen);
                icon.classList.toggle('fa-times', isOpen);
            }
        });

        panel.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                panel.classList.add('hidden');
                panel.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
                if (icon) {
                    icon.classList.add('fa-bars');
                    icon.classList.remove('fa-times');
                }
            });
        });
    });
</script>
