@php use App\Settings\GeneralSettings; @endphp
<div class="c-offcanvas offcanvas offcanvas-end"
     tabindex="-1"
     id="offcanvasNav"
     aria-labelledby="offcanvasNavLabel"
     role="navigation">
    <div class="offcanvas-header">
        <div id="offcanvasNavLabel" class="c-offcanvas__logo">
            <a href="{{ route('welcome.index') }}" class="js-home-link">
                <x-logo />
            </a>
        </div>

        <button type="button" class="btn-close js-close-offcanvas" data-bs-dismiss="offcanvas" aria-label="Close navigation"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column">
        <nav class="nav flex-column">
            <a @class([
                'nav-link link-body-emphasis mb-1 js-home-link',
                'active' => request()->routeIs('welcome.index'),
            ])
               href="{{ route('welcome.index') }}">HOME</a>

            <a @class([
                'nav-link link-body-emphasis',
                'active' => request()->routeIs('contact.index'),
            ])
               href="{{ route('contact.index') }}">CONTACT</a>

            {{-- Theme switcher --}}
            <div class="dropdown-center">
                <button class="dropdown-toggle nav-link link-body-emphasis d-flex align-items-center gap-2"
                        id="color-scheme-theme-switcher"
                        type="button"
                        aria-expanded="false"
                        data-bs-toggle="dropdown"
                        aria-label="@lang("Toggle theme")">
                    <svg class="color-scheme-theme-icon color-scheme-theme-icon-active" aria-hidden="true"><use href="#circle-half"></use></svg>
                    <span id="color-scheme-theme-switcher-text">@lang("Toggle theme")</span>
                </button>

                <ul class="dropdown-menu"
                    aria-labelledby="color-scheme-theme-switcher-text">
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
                            <svg class="color-scheme-theme-icon me-2 opacity-50" aria-hidden="true"><use href="#sun-fill"></use></svg>
                            @lang("Light")
                            <svg class="color-scheme-theme-icon ms-auto d-none" aria-hidden="true"><use href="#check2"></use></svg>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                            <svg class="color-scheme-theme-icon me-2 opacity-50" aria-hidden="true"><use href="#moon-stars-fill"></use></svg>
                            @lang("Dark")
                            <svg class="color-scheme-theme-icon ms-auto d-none" aria-hidden="true"><use href="#check2"></use></svg>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
                            <svg class="color-scheme-theme-icon me-2 opacity-50" aria-hidden="true"><use href="#circle-half"></use></svg>
                            @lang("Auto")
                            <svg class="color-scheme-theme-icon ms-auto d-none" aria-hidden="true"><use href="#check2"></use></svg>
                        </button>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="c-offcanvas__footer">
            <a href="{{ app(GeneralSettings::class)->social_facebook }}" target="_blank"
               class="h2 m-0 link-body-emphasis text-decoration-none" aria-label="Facebook">
                <i class="bi bi-facebook"></i>
            </a>

            <a href="{{ app(GeneralSettings::class)->social_instagram }}" target="_blank"
               class="h2 m-0 link-body-emphasis text-decoration-none" aria-label="Instagram">
                <i class="bi bi-instagram"></i>
            </a>

            <a href="{{ app(GeneralSettings::class)->social_linkedin }}" target="_blank"
               class="h2 m-0 link-body-emphasis text-decoration-none" aria-label="LinkedIn">
                <i class="bi bi-linkedin"></i>
            </a>

            @can('access admin')
                <a class="ms-auto"
                   href="{{ route('filament.admin.pages.dashboard') }}">ADMIN</a>
            @endcan
        </div>
    </div>
</div>

@push('scripts-body-bottom')
    {{-- Theme switcher icons --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
        <symbol id="sun-fill" viewBox="0 0 16 16"><path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"></path></symbol>
        <symbol id="check2" viewBox="0 0 16 16"><path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"></path></symbol>
        <symbol id="moon-stars-fill" viewBox="0 0 16 16"><path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"></path><path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"></path></symbol>
        <symbol id="circle-half" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"></path></symbol>
    </svg>

    {{-- Do not navigate, but scroll to top on the homepage when clicking the home links --}}
    @if (Route::currentRouteName() === 'welcome.index')
        <script>
            const homeLinks = document.querySelectorAll('.js-home-link');
            const closeOffcanvasButton = document.querySelector('.js-close-offcanvas');

            homeLinks.forEach((link) => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    window.scrollTo({ top: 0, behavior: 'smooth' });

                    // Close the offcanvas
                    if (closeOffcanvasButton) {
                        closeOffcanvasButton.click();
                    }
                });
            });
        </script>
    @endif
@endpush


