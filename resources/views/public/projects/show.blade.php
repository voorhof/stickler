<x-layouts.app>
    @push('scripts-head-bottom')
        <style>
            :root {
                --st-project-show-title-color: var(--st-body-color);
                --st-project-show-meta-color: var(--st-secondary-color);
                --st-project-show-btn-bg: var(--st-body-color);
                --st-project-show-btn-color: var(--st-body-bg);
                --st-project-show-btn-hover-bg: var(--st-link-color);
                --st-project-show-banner-height: 320px;
            }

            .c-project-show-banner {
                position: relative;
                width: 100%;
                height: var(--st-project-show-banner-height);
                margin-bottom: 2rem;
                border-radius: var(--st-border-radius-lg);
                overflow: hidden;
                background-color: var(--st-border-color-translucent);
            }

            .c-project-show-banner img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            .c-project-show-banner-overlay {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                padding: 2rem;
                background: linear-gradient(transparent, rgba(0, 0, 0, 0.6));
                color: #fff;
            }

            .c-project-show-title {
                margin: 0;
                font-weight: 700;
                font-size: clamp(1.5rem, 3.5vw, 2rem);
                line-height: 1.25;
                color: #fff;
                text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            }

            .c-project-show-header {
                display: flex;
                justify-content: flex-end;
                margin-bottom: 1.5rem;
            }

            .c-project-show-edit-link {
                font-size: 0.85rem;
                color: var(--st-secondary-color);
                text-decoration: underline;
                text-decoration-color: var(--st-border-color);
                text-underline-offset: 3px;
                transition: color 0.2s ease;
            }

            .c-project-show-edit-link:hover {
                color: var(--st-link-color);
            }

            .c-project-show-body {
                max-width: 720px;
                margin: 0 auto;
            }

            .c-project-show-intro {
                margin: 0 0 1.5rem;
                font-size: 1.1rem;
                line-height: 1.6;
                color: var(--st-body-color);
            }

            .c-project-show-meta {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                font-size: 0.85rem;
                color: var(--st-project-show-meta-color);
                margin-bottom: 2rem;
            }

            .c-project-show-website-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                font-size: 0.85rem;
                font-weight: 500;
                padding: 0.35rem 0.85rem;
                border-radius: var(--st-border-radius);
                background-color: var(--st-body-color);
                color: var(--st-body-bg);
                text-decoration: none;
                transition: opacity 0.2s ease;
            }

            .c-project-show-website-btn:hover {
                opacity: 0.8;
                color: var(--st-body-bg);
                text-decoration: none;
            }

            .c-project-show-back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.9rem;
                font-weight: 500;
                padding: 0.5rem 1.25rem;
                border-radius: var(--st-border-radius);
                background-color: var(--st-project-show-btn-bg);
                color: var(--st-project-show-btn-color);
                text-decoration: none;
                transition: background-color 0.2s ease, color 0.2s ease;
                margin-top: 2.5rem;
            }

            .c-project-show-back-btn:hover {
                background-color: var(--st-project-show-btn-hover-bg);
                color: var(--st-body-bg);
                text-decoration: none;
            }

            .c-project-show-back-btn:focus-visible {
                outline: 2px solid var(--st-link-color);
                outline-offset: 2px;
            }

            @media (max-width: 767.98px) {
                :root {
                    --st-project-show-banner-height: 220px;
                }

                .c-project-show-banner-overlay {
                    padding: 1.25rem;
                }
            }
        </style>
    @endpush

    <section class="py-5">
        <div class="container-xl">
            <div class="c-project-show-header">
                @can('update projects')
                    <a href="{{ route('filament.admin.resources.projects.edit', $project) }}" class="c-project-show-edit-link">
                        {{ __('Edit :model', ['model' => 'project']) }}
                    </a>
                @endcan
            </div>

            @php
                $coverMedia = $project->getFirstMedia('cover');
            @endphp

            <div class="c-project-show-banner">
                @if($coverMedia)
                    {{ $coverMedia->img('webp', ['alt' => $project->title]) }}
                @endif

                <div class="c-project-show-banner-overlay">
                    <h1 class="c-project-show-title">
                        {{ $project->title }}
                    </h1>
                </div>
            </div>

            <div class="c-project-show-body">
                @if($project->intro)
                    <p class="c-project-show-intro">
                        {{ $project->intro }}
                    </p>
                @endif

                <div class="c-project-show-meta">
                    @if($project->published_at)
                        <time datetime="{{ $project->published_at->toIso8601String() }}">
                            {{ $project->published_at->format('d M Y') }}
                        </time>
                    @endif

                    @if($project->website_url)
                        <a
                            class="c-project-show-website-btn"
                            href="{{ $project->website_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {{ $project->website_title ?: __('Visit site') }}
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                    @endif
                </div>

                @if($project->content)
                    <div class="c-rich-content">
                        {!! $project->renderResponsiveRichContent() !!}
                    </div>
                @endif

                <a href="{{ route('projects.index') }}" class="c-project-show-back-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    {{ __('Terug naar overzicht') }}
                </a>
            </div>
        </div>
    </section>
</x-layouts.app>
