<x-layouts.app>
    <section class="bg-danger-subtle py-5">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-auto text-center">
                    <h2 class="h1 mb-1">
                        404
                    </h2>
                    <p>
                        {{ __('Not Found') }}
                    </p>
                    <a href="{{ route('welcome.index') }}" class="btn btn-sm btn-outline-dark">
                        <i class="bi bi-skip-backward"></i>
                        Terug naar de homepage
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
