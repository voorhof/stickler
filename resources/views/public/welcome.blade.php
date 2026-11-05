<x-layouts.app>
    @push('scripts-head-bottom')
        <style>
            .c-welcome-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 1.5rem;
                width: 100%;
                max-width: 800px;
            }

            .c-welcome-grid .card {
                transition:
                    border-color 0.2s ease,
                    box-shadow 0.2s ease;
            }
            .c-welcome-grid .card:hover {
                border-color: var(--st-link-color);
                box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.075);
            }
            .c-welcome-grid .card:focus-visible {
                outline: 2px solid var(--st-link-color);
                outline-offset: 2px;
            }

            @media (max-width: 768px) {
                .c-welcome-grid {
                    grid-template-columns: 1fr;
                }
            }

            @media (prefers-color-scheme: dark) {
                .c-welcome-grid .card:hover {
                    box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.3);
                }
            }
        </style>
    @endpush

    <section class="py-5">
        <div class="container-xl">
            <div class="d-flex flex-column align-items-center justify-content-center text-center">
                <h1 class="fw-bold mb-4">
                    @lang('Welcome')
                </h1>

                <div class="c-welcome-grid">
                    <div class="card">
                        <a href="{{ route('posts.index') }}" class="card-body text-decoration-none">
                            <div class="fs-3"><x-icons.pen /></div>
                            <h2 class="card-title fs-4 mb-1">{{ __('Blogposts') }}</h2>
                            <p class="small text-body-secondary">{{ __('Read our latest articles') }}</p>
                        </a>
                    </div>

                    <div class="card">
                        <a href="{{ route('projects.index') }}" class="card-body text-decoration-none">
                            <div class="fs-3"><x-icons.book /></div>
                            <h2 class="card-title fs-4 mb-1">{{ __('Projects') }}</h2>
                            <p class="small text-body-secondary">{{ __('Explore our work') }}</p>
                        </a>
                    </div>

                    <div class="card">
                        <a href="{{ route('pages.index') }}" class="card-body text-decoration-none">
                            <div class="fs-3"><x-icons.file-earmark /></div>
                            <h2 class="card-title fs-4 mb-1">{{ __('Pages') }}</h2>
                            <p class="small text-body-secondary">{{ __('Browse all pages') }}</p>
                        </a>
                    </div>

                    <div class="card">
                        <a href="{{ route('products.index') }}" class="card-body text-decoration-none">
                            <div class="fs-3"><x-icons.handbag /></div>
                            <h2 class="card-title fs-4 mb-1">{{ __('Products') }}</h2>
                            <p class="small text-body-secondary">{{ __('Discover our products') }}</p>
                        </a>
                    </div>

                    <div class="card">
                        <a href="{{ route('cart.index') }}" class="card-body text-decoration-none">
                            <div class="fs-3"><x-icons.cart /></div>
                            <h2 class="card-title fs-4 mb-1">{{ __('Cart') }}</h2>
                            <p class="small text-body-secondary">{{ __('Cart Items') }}</p>
                        </a>
                    </div>

                    <div class="card">
                        <a href="{{ route('account.dashboard') }}" class="card-body text-decoration-none">
                            <div class="fs-3"><x-icons.person-circle /></div>
                            <h2 class="card-title fs-4 mb-1">{{ __('Account') }}</h2>
                            <p class="small text-body-secondary">{{ __('Dashboard') }}</p>
                        </a>
                    </div>

                    <div class="card">
                        <a href="{{ route('contact.index') }}" class="card-body text-decoration-none">
                            <div class="fs-3"><x-icons.envelope /></div>
                            <h2 class="card-title fs-4 mb-1">{{ __('Contact') }}</h2>
                            <p class="small text-body-secondary">{{ __('Get in touch') }}</p>
                        </a>
                    </div>

                    <div class="card">
                        <a href="{{ route('brandbook.index') }}" class="card-body text-decoration-none">
                            <div class="fs-3"><x-icons.palette2 /></div>
                            <h2 class="card-title fs-4 mb-1">{{ __('Brandbook') }}</h2>
                            <p class="small text-body-secondary">{{ __('View our style guide') }}</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
