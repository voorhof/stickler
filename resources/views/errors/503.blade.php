<x-layouts.app>
    <section class="bg-danger-subtle py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-auto text-center">
                    <h2 class="h1 mb-1">
                        503
                    </h2>
                    <p>
                        {{ __('Service Unavailable') }}
                    </p>
                    <a href="{{ route('welcome.index') }}" class="btn btn-sm btn-outline-dark">
                        <i class="ppi ppi-skip-backward"></i>
                        Terug naar de homepage
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
