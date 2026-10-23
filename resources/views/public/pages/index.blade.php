<x-layouts.app>
    @push('scripts-head-bottom')
        <style>
            :root {
                --st-page-card-bg: var(--st-body-bg);
                --st-page-card-border: var(--st-border-color);
                --st-page-card-hover-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.06);
                --st-page-accent-width: 6px;
                --st-page-accent-radius: var(--st-border-radius);
                --st-page-title-color: var(--st-body-color);
                --st-page-link-color: var(--st-link-color);
                --st-page-link-hover-color: var(--st-link-hover-color);
                --st-page-meta-color: var(--st-secondary-color);
            }

            [data-theme='dark'] {
                --st-page-card-hover-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.3);
            }

            .c-pages-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
                gap: 1.25rem;
                list-style: none;
                margin: 0;
                padding: 0;
            }

            .c-page-card {
                position: relative;
                display: flex;
                flex-direction: column;
                padding: 1.5rem;
                padding-left: calc(1.5rem - 6px + var(--st-page-accent-width));
                border: 1px solid var(--st-page-card-border);
                border-radius: var(--st-border-radius-lg);
                background-color: var(--st-page-card-bg);
                text-decoration: none;
                color: var(--st-page-title-color);
                transition:
                    box-shadow 0.2s ease,
                    border-color 0.2s ease,
                    transform 0.15s ease;
                overflow: hidden;
            }

            .c-page-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: var(--st-page-accent-width);
                height: 100%;
                background-color: var(--st-page-link-color);
                opacity: 0;
                transition: opacity 0.2s ease;
            }

            .c-page-card:hover {
                box-shadow: var(--st-page-card-hover-shadow);
                border-color: var(--st-page-link-color);
                transform: translateX(2px);
                color: var(--st-page-link-hover-color);
                text-decoration: none;
            }

            .c-page-card:hover::before {
                opacity: 1;
            }

            .c-page-card:focus-visible {
                outline: 2px solid var(--st-page-link-color);
                outline-offset: 2px;
            }

            .c-page-accent-image {
                position: absolute;
                top: 0;
                left: 0;
                width: var(--st-page-accent-width);
                height: 100%;
                object-fit: cover;
                object-position: center;
            }

            .c-page-card:hover .c-page-accent-image {
                width: calc(100% + 2px);
                opacity: 0.06;
                transition:
                    width 0.3s ease,
                    opacity 0.3s ease;
            }

            .c-page-content {
                position: relative;
                z-index: 1;
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 0.4rem;
            }

            .c-page-title {
                margin: 0;
                font-weight: 600;
                font-size: 1.1rem;
                line-height: 1.35;
            }

            .c-page-intro {
                margin: 0;
                font-size: 0.9rem;
                line-height: 1.5;
                color: var(--st-body-color);
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .c-page-date {
                font-size: 0.8rem;
                color: var(--st-page-meta-color);
                margin-top: auto;
            }

            .c-pages-empty {
                text-align: center;
                padding: 3rem 1rem;
                color: var(--st-secondary-color);
            }

            @media (max-width: 575.98px) {
                .c-pages-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endpush

    <section class="py-5">
        <div class="container-xl">
            <h1 class="mb-4">
                @lang('Pages')
            </h1>

            @if ($pages->isNotEmpty())
                <ul class="c-pages-grid">
                    @foreach ($pages as $page)
                        <li>
                            <a class="c-page-card" href="{{ route('pages.show', $page) }}">
                                @php
                                    $coverMedia = $page->getFirstMedia('cover');
                                @endphp

                                @if ($coverMedia)
                                    <img
                                        class="c-page-accent-image"
                                        src="{{ $coverMedia->getUrl('preview') }}"
                                        alt=""
                                        loading="lazy"
                                        width="6"
                                        height="200"
                                    />
                                @endif

                                <div class="c-page-content">
                                    <h2 class="c-page-title">{{ $page->title }}</h2>

                                    @if ($page->intro)
                                        <p class="c-page-intro">{{ $page->intro }}</p>
                                    @endif

                                    @if ($page->published_at)
                                        <time
                                            class="c-page-date"
                                            datetime="{{ $page->published_at->toIso8601String() }}"
                                        >
                                            {{ $page->published_at->format('d M Y') }}
                                        </time>
                                    @endif
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div>
                    <hr />
                    {{ $pages->links() }}
                </div>
            @else
                <div class="c-pages-empty">
                    <p class="lead mb-0">{{ __('No pages available yet.') }}</p>
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
