@props(['cardTitle' => __('Welcome')])

<div class="container-lg">
    <div class="row justify-content-center">
        <div class="py-lg-5 col-auto py-4">
            <div class="card bg-light-subtle border-light shadow-ring" style="width: 30rem; max-width: 100%">
                <div class="card-header bg-body-tertiary px-5 py-3 text-center">
                    <x-logo />
                </div>
                <div class="card-body">
                    <h1 class="card-title h4 mb-3">{{ $cardTitle }}</h1>

                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
