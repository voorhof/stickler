<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            <h1>
                @lang('Dashboard')
            </h1>

            <div class="mt-4 mb-4">
                <a href="{{ route('account.orders.index') }}" class="btn btn-primary"> {{ __('My orders') }} </a>
            </div>

            <div class="mt-4">
                <form method="POST" action="{{ route('logout') }}">
                    <x-csrf-input />
                    <x-form.button variant="outline-secondary" :value="__('Log out')" />
                </form>
            </div>
        </div>
    </section>
</x-layouts.app>
