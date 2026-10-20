<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            @can('update projects')
                <div class="row justify-content-end mb-4">
                    <div class="col-auto">
                        <a href="{{ route('filament.admin.resources.pages.edit', $page) }}">
                            {{ __('Edit :model', ['model' => 'page']) }}
                        </a>
                    </div>
                </div>
            @endcan

            <div class="row justify-content-center">
                <div class="col-lg-10 col-xxl-9">
                    <div class="row justify-content-center mb-4">
                        <div class="col-md-6">
                            <h1>
                                {{ $page->title }}
                            </h1>

                            <p class="lead">
                                {{ $page->intro }}
                            </p>
                        </div>

                        <div class="col-md-6">
                            {{ $page->getFirstMedia('cover')?->img('webp', ['alt' => $page->title]) }}
                        </div>
                    </div>

                    <div class="row justify-content-center mb-4">
                        <div class="col">
                            <div class="c-rich-content">
                                {!! $page->renderResponsiveRichContent() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
