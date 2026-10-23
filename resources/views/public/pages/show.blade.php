<x-layouts.app>
    @push('scripts-head-bottom')
        <style>
            :root {
                --st-page-show-title-color: var(--st-body-color);
                --st-page-show-meta-color: var(--st-secondary-color);
                --st-page-show-btn-bg: var(--st-body-color);
                --st-page-show-btn-color: var(--st-body-bg);
                --st-page-show-btn-hover-bg: var(--st-link-color);
                --st-page-show-accent-width: 4px;
            }

            .c-page-show-header {
                position: relative;
                max-width: 720px;
                margin: 0 auto 2.5rem;
                padding-left: calc(var(--st-page-show-accent-width) + 1.5rem);
            }

            .c-page-show-header::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: var(--st-page-show-accent-width);
                height: 100%;
                border-radius: var(--st-border-radius);
                background-color: var(--st-link-color);
            }

            .c-page-show-title-row {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 1rem;
                margin-bottom: 0.75rem;
            }

            .c-page-show-title {
                margin: 0;
                font-weight: 700;
                font-size: clamp(1.75rem, 4vw, 2.25rem);
                line-height: 1.2;
                color: var(--st-page-show-title-color);
            }

            .c-page-show-edit-link {
                font-size: 0.85rem;
                color: var(--st-secondary-color);
                text-decoration: underline;
                text-decoration-color: var(--st-border-color);
                text-underline-offset: 3px;
                transition: color 0.2s ease;
                white-space: nowrap;
            }

            .c-page-show-edit-link:hover {
                color: var(--st-link-color);
            }

            .c-page-show-intro {
                margin: 0;
                font-size: 1.1rem;
                line-height: 1.6;
                color: var(--st-body-color);
            }

            .c-page-show-meta {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                font-size: 0.85rem;
                color: var(--st-page-show-meta-color);
                margin-top: 0.75rem;
            }

            .c-page-show-cover {
                max-width: 720px;
                margin: 0 auto 2.5rem;
                border-radius: var(--st-border-radius-lg);
                overflow: hidden;
            }

            .c-page-show-cover img {
                width: 100%;
                height: auto;
                display: block;
            }

            .c-page-show-content {
                max-width: 720px;
                margin: 0 auto;
            }

            .c-page-show-back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.9rem;
                font-weight: 500;
                padding: 0.5rem 1.25rem;
                border-radius: var(--st-border-radius);
                background-color: var(--st-page-show-btn-bg);
                color: var(--st-page-show-btn-color);
                text-decoration: none;
                transition:
                    background-color 0.2s ease,
                    color 0.2s ease;
                margin-top: 2.5rem;
            }

            .c-page-show-back-btn:hover {
                background-color: var(--st-page-show-btn-hover-bg);
                color: var(--st-body-bg);
                text-decoration: none;
            }

            .c-page-show-back-btn:focus-visible {
                outline: 2px solid var(--st-link-color);
                outline-offset: 2px;
            }
        </style>
    @endpush

    <section class="py-5">
        <div class="container-xl">
            <div class="c-page-show-header">
                <div class="c-page-show-title-row">
                    <h1 class="c-page-show-title">{{ $page->title }}</h1>

                    @can('update projects')
                        <a
                            href="{{ route('filament.admin.resources.pages.edit', $page) }}"
                            class="c-page-show-edit-link"
                        >
                            {{ __('Edit :model', ['model' => 'page']) }}
                        </a>
                    @endcan
                </div>

                @if ($page->intro)
                    <p class="c-page-show-intro">{{ $page->intro }}</p>
                @endif

                @if ($page->published_at)
                    <div class="c-page-show-meta">
                        <time datetime="{{ $page->published_at->toIso8601String() }}">
                            {{ $page->published_at->format('d M Y') }}
                        </time>
                    </div>
                @endif
            </div>

            @php
                $coverMedia = $page->getFirstMedia('cover');
            @endphp

            @if ($coverMedia)
                <div class="c-page-show-cover">{{ $coverMedia->img('webp', ['alt' => $page->title]) }}</div>
            @endif

            <div class="c-page-show-content">
                @if ($page->content)
                    <div class="c-rich-content">{!! $page->renderResponsiveRichContent() !!}</div>
                @endif

                <a href="{{ route('pages.index') }}" class="c-page-show-back-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    {{ __('Terug naar overzicht') }}
                </a>
            </div>
        </div>
    </section>
</x-layouts.app>
