<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>{{ __('My orders') }}</h1>
                <a href="{{ route('account.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    {{ __('Back to dashboard') }}
                </a>
            </div>

            @if ($orders->isEmpty())
                <div class="alert alert-info" role="alert">{{ __('You have not placed any orders yet.') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table-striped table-hover table">
                        <thead>
                            <tr>
                                <th>{{ __('Order number') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Payment') }}</th>
                                <th class="text-end">{{ __('Total') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('account.orders.show', $order) }}">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                    <td>{{ $order->status->getLabel() }}</td>
                                    <td>{{ $order->payment_status->getLabel() }}</td>
                                    <td class="text-end">{{ number_format($order->total, 2) }} €</td>
                                    <td>
                                        <a
                                            href="{{ route('account.orders.show', $order) }}"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            {{ __('View') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">{{ $orders->links() }}</div>
            @endif
        </div>
    </section>
</x-layouts.app>
