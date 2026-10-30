<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="mb-5 text-center">
                        <h1>{{ __('Thank you for your order!') }}</h1>
                        <p class="lead text-muted">{{ __('Your order has been placed successfully.') }}</p>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>{{ __('Order number') }}:</strong>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <span class="h4">#{{ $order->id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h2 class="mb-3">{{ __('Order Summary') }}</h2>
                    <div class="table-responsive">
                        <table class="table-striped table">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Unit price') }}</th>
                                    <th>{{ __('Total price') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->orderItems as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product_name }}</strong>
                                            @if ($item->product_variant_label)
                                                <br
                                                /><small class="text-muted">{{ $item->product_variant_label }}</small>
                                            @endif
                                            @if ($item->sku)
                                                <br
                                                /><small class="text-muted">{{ __('SKU') }}: {{ $item->sku }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format($item->unit_price, 2) }} €</td>
                                        <td>{{ number_format($item->total_price, 2) }} €</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>{{ __('Subtotal') }}:</strong></td>
                                    <td><strong>{{ number_format($order->subtotal / 100, 2) }} €</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if ($order->shipping_address)
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h3>{{ __('Shipping Address') }}</h3>
                                <address>
                                    {{ $order->shipping_address['street'] }}<br />
                                    {{ $order->shipping_address['postal_code'] }} {{ $order->shipping_address['city'] }}<br />
                                    {{ $order->shipping_address['country'] }}
                                </address>
                            </div>
                            @if ($order->billing_address)
                                <div class="col-md-6">
                                    <h3>{{ __('Billing Address') }}</h3>
                                    <address>
                                        {{ $order->billing_address['street'] }}<br />
                                        {{ $order->billing_address['postal_code'] }} {{ $order->billing_address['city'] }}<br />
                                        {{ $order->billing_address['country'] }}
                                    </address>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($order->notes)
                        <div class="mt-4">
                            <h3>{{ __('Notes') }}</h3>
                            <p>{{ $order->notes }}</p>
                        </div>
                    @endif

                    <div class="mt-5 text-center">
                        <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg">
                            {{ __('Continue shopping') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
