<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            <h1>
                @lang('Dashboard')
            </h1>

            <div class="card my-4">
                <div class="card-body">
                    <h2 class="card-title">{{ __('Profile') }}</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-3">{{ __('Name') }}</dt>
                        <dd class="col-sm-9">{{ auth()->user()->name }}</dd>

                        <dt class="col-sm-3">{{ __('Email address') }}</dt>
                        <dd class="col-sm-9">{{ auth()->user()->email }}</dd>
                    </dl>
                    <a href="{{ route('account.profile') }}" class="btn btn-outline-primary btn-sm mt-3">
                        {{ __('Edit profile') }}
                    </a>
                </div>
            </div>

            <div class="my-4">
                <a href="{{ route('account.orders.index') }}" class="btn btn-primary"> {{ __('My orders') }} </a>
            </div>

            <div class="my-4">
                <form method="POST" action="{{ route('logout') }}">
                    <x-csrf-input />
                    <x-form.button variant="outline-secondary" :value="__('Log out')" />
                </form>
            </div>
        </div>
    </section>
</x-layouts.app>
