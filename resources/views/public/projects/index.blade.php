<x-layouts.app>
    @push('scripts-head-bottom')
        <style>
            :root {
                --st-project-card-bg: var(--st-body-bg);
                --st-project-card-border: var(--st-border-color);
                --st-project-card-hover-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
                --st-project-image-height: 200px;
                --st-project-image-radius: var(--st-border-radius-lg);
                --st-project-title-color: var(--st-body-color);
                --st-project-link-color: var(--st-link-color);
                --st-project-link-hover-color: var(--st-link-hover-color);
                --st-project-meta-color: var(--st-secondary-color);
            }

            [data-theme=dark] {
                --st-project-card-hover-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.3);
            }

            .c-project-card {
                position: relative;
                display: flex;
                flex-direction: column;
                height: 100%;
                padding: 0;
                border: 1px solid var(--st-project-card-border);
                border-radius: var(--st-border-radius-lg);
                background-color: var(--st-project-card-bg);
                text-decoration: none;
                color: var(--st-project-title-color);
                transition: box-shadow 0.2s ease, border-color 0.2s ease, transform 0.15s ease;
                overflow: hidden;
            }

            .c-project-card:hover {
                box-shadow: var(--st-project-card-hover-shadow);
                border-color: var(--st-project-link-color);
                transform: translateY(-2px);
                color: var(--st-project-link-hover-color);
                text-decoration: none;
            }

            .c-project-card:focus-visible {
                outline: 2px solid var(--st-project-link-color);
                outline-offset: 2px;
            }

            .c-project-image-wrapper {
                position: relative;
                width: 100%;
                height: var(--st-project-image-height);
                background-color: var(--st-tertiary-bg);
                overflow: hidden;
            }

            .c-project-image {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3s ease;
            }

            .c-project-card:hover .c-project-image {
                transform: scale(1.03);
            }

            .c-project-body {
                flex: 1;
                display: flex;
                flex-direction: column;
                padding: 1.25rem 1.5rem;
                gap: 0.5rem;
            }

            .c-project-title {
                margin: 0;
                font-weight: 600;
                font-size: 1.15rem;
                line-height: 1.3;
            }

            .c-project-intro {
                margin: 0;
                font-size: 0.925rem;
                line-height: 1.5;
                color: var(--st-body-color);
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
                flex: 1;
            }

            .c-project-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                margin-top: 0.25rem;
            }

            .c-project-date {
                font-size: 0.825rem;
                color: var(--st-project-meta-color);
            }

            .c-projects-empty {
                text-align: center;
                padding: 3rem 1rem;
                color: var(--st-secondary-color);
            }
        </style>
    @endpush

    <section class="py-5">
        <div class="container-xl">
            <h1 class="mb-4">
                @lang('Projects')
            </h1>

            @if($projects->isNotEmpty())
                <div class="row g-4">
                    @foreach($projects as $project)
                        <div class="col-lg-4 col-md-6 col-12">
                            <a class="c-project-card" href="{{ route('projects.show', $project) }}">
                                @php
                                    $coverMedia = $project->getFirstMedia('cover');
                                @endphp

                                <div class="c-project-image-wrapper">
                                    @if($coverMedia)
                                        <img
                                            class="c-project-image"
                                            src="{{ $coverMedia->getUrl('preview') }}"
                                            alt="{{ $project->title }}"
                                            loading="lazy"
                                            width="400"
                                            height="400"
                                        />
                                    @endif
                                </div>

                                <div class="c-project-body">
                                    <h2 class="c-project-title">
                                        {{ $project->title }}
                                    </h2>

                                    @if($project->intro)
                                        <p class="c-project-intro">
                                            {{ $project->intro }}
                                        </p>
                                    @endif

                                    @if($project->published_at)
                                        <time class="c-project-date" datetime="{{ $project->published_at->toIso8601String() }}">
                                            {{ $project->published_at->format('d M Y') }}
                                        </time>
                                    @endif
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="c-projects-empty">
                    <p class="lead mb-0">{{ __('No projects available yet.') }}</p>
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
