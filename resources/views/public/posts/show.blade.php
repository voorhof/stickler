<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            @can('update projects')
                <div class="row justify-content-end mb-4">
                    <div class="col-auto">
                        <a href="{{ route('filament.admin.resources.posts.edit', $post) }}">
                            {{ __('Edit :model', ['model' => 'post']) }}
                        </a>
                    </div>
                </div>
            @endcan

            <div class="row justify-content-center">
                <div class="col-lg-10 col-xxl-9">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <h1>
                                {{ $post->title }}
                            </h1>

                            <p class="lead">
                                {{ $post->intro }}
                            </p>

                            <div class="c-rich-content mb-4">
                                {!! $post->renderResponsiveRichContent() !!}
                            </div>
                        </div>

                        <div class="col-md-6">
                            {{ $post->getFirstMedia('cover')?->img('webp', ['alt' => $post->title]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
