<x-layouts.app>
    @push('scripts-head-bottom')
        <style>
            :root {
                --st-post-show-title-color: var(--st-body-color);
                --st-post-show-meta-color: var(--st-secondary-color);
                --st-post-show-btn-bg: var(--st-body-color);
                --st-post-show-btn-color: var(--st-body-bg);
                --st-post-show-btn-hover-bg: var(--st-link-color);
            }

            .c-post-show-hero {
                display: flex;
                flex-direction: column;
                gap: 1rem;
                margin-bottom: 2.5rem;
                padding-bottom: 2rem;
                border-bottom: 1px solid var(--st-border-color);
            }

            .c-post-show-title {
                margin: 0;
                font-weight: 700;
                font-size: clamp(1.75rem, 4vw, 2.5rem);
                line-height: 1.2;
                color: var(--st-post-show-title-color);
            }

            .c-post-show-intro {
                margin: 0;
                font-size: 1.15rem;
                line-height: 1.6;
                color: var(--st-body-color);
            }

            .c-post-show-meta {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                font-size: 0.85rem;
                color: var(--st-post-show-meta-color);
            }

            .c-post-show-meta-dot {
                width: 4px;
                height: 4px;
                border-radius: 50%;
                background-color: var(--st-post-show-meta-color);
            }

            .c-post-show-cover {
                width: 100%;
                max-width: 600px;
                margin: 0 auto 2.5rem;
                border-radius: var(--st-border-radius-lg);
                overflow: hidden;
            }

            .c-post-show-cover img {
                width: 100%;
                height: auto;
                display: block;
            }

            .c-post-show-content {
                max-width: 720px;
                margin: 0 auto;
            }

            .c-post-show-back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.9rem;
                font-weight: 500;
                padding: 0.5rem 1.25rem;
                border-radius: var(--st-border-radius);
                background-color: var(--st-post-show-btn-bg);
                color: var(--st-post-show-btn-color);
                text-decoration: none;
                transition: background-color 0.2s ease, color 0.2s ease;
                margin-top: 2.5rem;
            }

            .c-post-show-back-btn:hover {
                background-color: var(--st-post-show-btn-hover-bg);
                color: var(--st-body-bg);
                text-decoration: none;
            }

            .c-post-show-back-btn:focus-visible {
                outline: 2px solid var(--st-link-color);
                outline-offset: 2px;
            }

            .c-post-show-edit-link {
                font-size: 0.85rem;
                color: var(--st-secondary-color);
                text-decoration: underline;
                text-decoration-color: var(--st-border-color);
                text-underline-offset: 3px;
                transition: color 0.2s ease;
            }

            .c-post-show-edit-link:hover {
                color: var(--st-link-color);
            }
        </style>
    @endpush

    <section class="py-5">
        <div class="container-xl">
            <div class="c-post-show-hero">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <h1 class="c-post-show-title">
                        {{ $post->title }}
                    </h1>

                    @can('update projects')
                        <a href="{{ route('filament.admin.resources.posts.edit', $post) }}" class="c-post-show-edit-link">
                            {{ __('Edit :model', ['model' => 'post']) }}
                        </a>
                    @endcan
                </div>

                @if($post->intro)
                    <p class="c-post-show-intro">
                        {{ $post->intro }}
                    </p>
                @endif

                @if($post->published_at)
                    <div class="c-post-show-meta">
                        <time datetime="{{ $post->published_at->toIso8601String() }}">
                            {{ $post->published_at->format('d M Y') }}
                        </time>
                    </div>
                @endif
            </div>

            @php
                $coverMedia = $post->getFirstMedia('cover');
            @endphp

            @if($coverMedia)
                <div class="c-post-show-cover">
                    {{ $coverMedia->img('webp', ['alt' => $post->title]) }}
                </div>
            @endif

            <div class="c-post-show-content">
                @if($post->content)
                    <div class="c-rich-content">
                        {!! $post->renderResponsiveRichContent() !!}
                    </div>
                @endif

                <a href="{{ route('posts.index') }}" class="c-post-show-back-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    {{ __('Terug naar overzicht') }}
                </a>
            </div>
        </div>
    </section>
</x-layouts.app>
