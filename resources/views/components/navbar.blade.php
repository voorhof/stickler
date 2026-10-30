<nav class="c-navbar navbar">
    <div class="container-xl">
        <a class="navbar-brand js-home-link" href="{{ route('welcome.index') }}">
            <x-logo />
        </a>

        <div class="d-flex align-items-center">
            <a
                href="{{ route('cart.index') }}"
                class="nav-link position-relative me-4"
                aria-label="{{ __('View cart') }}"
            >
                <span class="fs-4">
                    <x-icons.cart />
                </span>
                @if ($cartItemCount > 0)
                    <span
                        class="position-absolute translate-middle badge rounded-pill bg-primary start-100"
                        style="top: 0.5rem; font-weight: 400; font-size: 11px"
                    >
                        {{ $cartItemCount }}
                    </span>
                @endif
            </a>

            <button
                class="navbar-toggler"
                type="button"
                data-toggle="offcanvas"
                data-target="#offcanvasNav"
                aria-controls="offcanvasNav"
                aria-label="Toggle offcanvas navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </div>
</nav>
