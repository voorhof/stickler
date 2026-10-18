<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            @can('update projects')
                <div class="row justify-content-end mb-4">
                    <div class="col-auto">
                        <a href="{{ route('filament.admin.resources.projects.edit', $project) }}">
                            {{ __('Edit :model', ['model' => 'project']) }}
                        </a>
                    </div>
                </div>
            @endcan

            <div class="row justify-content-center">
                <div class="col-lg-10 col-xxl-9">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <h1>
                                {{ $project->title }}
                            </h1>

                            <p class="lead">
                                {{ $project->intro }}
                            </p>

                            <div class="c-rich-content mb-4">
                                {!! $project->renderResponsiveRichContent() !!}
                            </div>
                        </div>

                        <div class="col-md-6">
                            {{ $project->getFirstMedia('cover')?->img('webp', ['alt' => $project->title]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
