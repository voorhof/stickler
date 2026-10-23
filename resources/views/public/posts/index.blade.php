<x-layouts.app>
    @push('scripts-head-bottom')
        <style>
            :root {
                --st-post-card-bg: var(--st-body-bg);
                --st-post-card-border: var(--st-border-color);
                --st-post-card-hover-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
                --st-post-preview-width: 120px;
                --st-post-preview-height: 90px;
                --st-post-preview-radius: var(--st-border-radius);
                --st-post-title-color: var(--st-body-color);
                --st-post-link-color: var(--st-link-color);
                --st-post-link-hover-color: var(--st-link-hover-color);
                --st-post-meta-color: var(--st-secondary-color);
            }

            [data-theme='dark'] {
                --st-post-card-hover-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.3);
            }

            .c-posts-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .c-post-card {
                display: flex;
                align-items: center;
                gap: 1.5rem;
                padding: 1.25rem 1.5rem;
                margin-bottom: 0;
                border: 1px solid var(--st-post-card-border);
                border-radius: var(--st-border-radius-lg);
                background-color: var(--st-post-card-bg);
                text-decoration: none;
                color: var(--st-post-title-color);
                transition:
                    box-shadow 0.2s ease,
                    border-color 0.2s ease,
                    transform 0.15s ease;
            }

            .c-post-card:hover {
                box-shadow: var(--st-post-card-hover-shadow);
                border-color: var(--st-link-color);
                transform: translateY(-1px);
                color: var(--st-post-link-hover-color);
                text-decoration: none;
            }

            .c-post-card:focus-visible {
                outline: 2px solid var(--st-link-color);
                outline-offset: 2px;
            }

            .c-post-preview {
                flex-shrink: 0;
                width: var(--st-post-preview-width);
                height: var(--st-post-preview-height);
                border-radius: var(--st-post-preview-radius);
                object-fit: cover;
                background-color: var(--st-tertiary-bg);
            }

            .c-post-content {
                flex: 1;
                min-width: 0;
            }

            .c-post-title {
                margin: 0 0 0.25rem;
                font-weight: 600;
                font-size: 1.15rem;
                line-height: 1.3;
            }

            .c-post-intro {
                margin: 0 0 0.25rem;
                color: var(--st-body-color);
                font-size: 0.925rem;
                line-height: 1.5;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .c-post-date {
                font-size: 0.825rem;
                color: var(--st-post-meta-color);
            }

            .c-posts-empty {
                text-align: center;
                padding: 3rem 1rem;
                color: var(--st-secondary-color);
            }
        </style>
    @endpush

    <section class="py-5">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xxl-9">
                    <h1 class="mb-4">
                        @lang('Blogposts')
                    </h1>

                    @if ($posts->isNotEmpty())
                        <ul class="c-posts-list">
                            @foreach ($posts as $post)
                                <li class="mb-3">
                                    <a class="c-post-card" href="{{ route('posts.show', $post) }}">
                                        <img
                                            class="c-post-preview"
                                            src="{{ $post->getFirstMediaUrl('cover', 'preview') }}"
                                            alt="{{ $post->title }}"
                                            loading="lazy"
                                            width="120"
                                            height="90"
                                        />

                                        <div class="c-post-content">
                                            <h2 class="c-post-title">{{ $post->title }}</h2>

                                            @if ($post->intro)
                                                <p class="c-post-intro">{{ $post->intro }}</p>
                                            @endif

                                            @if ($post->published_at)
                                                <time
                                                    class="c-post-date"
                                                    datetime="{{ $post->published_at->toIso8601String() }}"
                                                >
                                                    {{ $post->published_at->format('d M Y') }}
                                                </time>
                                            @endif
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        <div>
                            <hr />
                            {{ $posts->links() }}
                        </div>
                    @else
                        <div class="c-posts-empty">
                            <p class="lead mb-0">{{ __('No blogposts available yet.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
