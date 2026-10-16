<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
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

                            <div class="mb-4">
                                {!! $post->content !!}
                            </div>
                        </div>

                        <div class="col-md-6">
                            {{ $post->getFirstMedia('cover') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
