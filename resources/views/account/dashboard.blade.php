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

            @if ($latestOrders->isNotEmpty())
                <div class="card my-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h2 class="card-title mb-0">{{ __('Latest orders') }}</h2>
                            <a href="{{ route('account.orders.index') }}" class="btn btn-outline-primary btn-sm">
                                {{ __('My orders') }}
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table-striped mb-0 table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Order number') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th class="text-end">{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($latestOrders as $order)
                                        <tr>
                                            <td>{{ $order->order_number }}</td>
                                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                            <td>{{ $order->status->getLabel() }}</td>
                                            <td class="text-end">{{ number_format($order->total, 2) }} €</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card my-4">
                    <div class="card-body py-5 text-center">
                        <h2 class="card-title">{{ __('You have not placed any orders yet.') }}</h2>
                        <p class="text-muted">{{ __('Start shopping to place your first order.') }}</p>
                        @if ($hasCartItems)
                            <a href="{{ route('cart.index') }}" class="btn btn-primary"> {{ __('View cart') }} </a>
                        @else
                            <a href="{{ route('products.index') }}" class="btn btn-primary">
                                {{ __('Start shopping') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Logout modal trigger --}}
            <div class="my-4">
                <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#logoutModal">
                    {{ __('Log out') }}
                </button>
            </div>
        </div>
    </section>
</x-layouts.app>
