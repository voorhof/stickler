<x-layouts.app>
    @push('scripts-head-bottom')
        <style>
            :root {
                --st-welcome-title-color: var(--st-body-color);
                --st-welcome-card-bg: var(--st-body-bg);
                --st-welcome-card-border: var(--st-border-color);
                --st-welcome-card-hover-border: var(--st-link-color);
                --st-welcome-card-title-color: var(--st-body-color);
                --st-welcome-card-desc-color: var(--st-secondary-color);
            }

            .c-welcome {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 60vh;
                text-align: center;
            }

            .c-welcome-title {
                margin: 0 0 3rem;
                font-weight: 700;
                font-size: clamp(2rem, 5vw, 3rem);
                line-height: 1.2;
                color: var(--st-welcome-title-color);
            }

            .c-welcome-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 1.5rem;
                width: 100%;
                max-width: 800px;
            }

            .c-welcome-card {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                padding: 2rem 1.5rem;
                border: 1px solid var(--st-welcome-card-border);
                border-radius: var(--st-border-radius-lg);
                background-color: var(--st-welcome-card-bg);
                text-decoration: none;
                transition:
                    border-color 0.2s ease,
                    box-shadow 0.2s ease;
            }

            .c-welcome-card:hover {
                border-color: var(--st-welcome-card-hover-border);
                box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.075);
                text-decoration: none;
            }

            .c-welcome-card:focus-visible {
                outline: 2px solid var(--st-link-color);
                outline-offset: 2px;
            }

            .c-welcome-card-icon {
                width: 32px;
                height: 32px;
                color: var(--st-link-color);
            }

            .c-welcome-card-title {
                margin: 0;
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--st-welcome-card-title-color);
            }

            .c-welcome-card-desc {
                margin: 0;
                font-size: 0.85rem;
                color: var(--st-welcome-card-desc-color);
            }

            @media (max-width: 767.98px) {
                .c-welcome-grid {
                    grid-template-columns: 1fr;
                }
            }

            @media (prefers-color-scheme: dark) {
                .c-welcome-card:hover {
                    box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.3);
                }
            }
        </style>
    @endpush

    <section class="py-5">
        <div class="container-xl">
            <div class="c-welcome">
                <h1 class="c-welcome-title">
                    @lang('Welcome')
                </h1>

                <div class="c-welcome-grid">
                    <a href="{{ route('posts.index') }}" class="c-welcome-card">
                        <div class="fs-3"><x-icons.pen /></div>
                        <h2 class="c-welcome-card-title">{{ __('Blogposts') }}</h2>
                        <p class="c-welcome-card-desc">{{ __('Read our latest articles') }}</p>
                    </a>

                    <a href="{{ route('projects.index') }}" class="c-welcome-card">
                        <div class="fs-3"><x-icons.book /></div>
                        <h2 class="c-welcome-card-title">{{ __('Projects') }}</h2>
                        <p class="c-welcome-card-desc">{{ __('Explore our work') }}</p>
                    </a>

                    <a href="{{ route('pages.index') }}" class="c-welcome-card">
                        <div class="fs-3"><x-icons.file-earmark /></div>
                        <h2 class="c-welcome-card-title">{{ __('Pages') }}</h2>
                        <p class="c-welcome-card-desc">{{ __('Browse all pages') }}</p>
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
