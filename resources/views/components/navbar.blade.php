<nav class="c-navbar navbar">
    <div class="container-xl pe-0">
        <a class="navbar-brand js-navbrand"
           href="{{ route('welcome.index') }}">
            <x-logo-pixel />
        </a>

        <button class="navbar-toggler"
                type="button"
                data-toggle="offcanvas"
                data-target="#offcanvasNav"
                aria-controls="offcanvasNav"
                aria-label="Toggle navigation">
            <i class="ppi ppi-list"></i>
        </button>
    </div>
</nav>

@push('scripts-body-bottom')
    {{-- Navbar logo hide/show animation --}}
    <script>
        const bigLogo = document.querySelector('.js-logo-animation');
        const navBrand = document.querySelector('.js-navbrand');

        if (bigLogo && navBrand) {
            const observer = new IntersectionObserver(
                ([entry]) => {
                    if (entry.intersectionRatio === 0) {
                        navBrand.classList.add('navbar-brand-show');
                    }

                    if (entry.intersectionRatio === 1) {
                        navBrand.classList.remove('navbar-brand-show');
                    }
                },
                {
                    rootMargin: '-56px 0px 0px 0px',
                    threshold: [0, 1],
                }
            );

            observer.observe(bigLogo);

        } else if (!bigLogo && navBrand) {
            // Always show the navBrand when there is no bigLogo
            navBrand.classList.add('navbar-brand-show');
        }
    </script>

    {{-- Do not navigate, but scroll to top on the homepage when clicking the navbar-brand --}}
    @if (Route::currentRouteName() === 'welcome.index')
        <script>
            const navBrandLink = document.querySelector('.js-navbrand');

            navBrandLink.addEventListener('click', (event) => {
                event.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        </script>
    @endif
@endpush
