@php use App\Enums\PaymentStatus; @endphp
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

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($order->payment_status->value === PaymentStatus::Unpaid->value)
                        <div class="alert alert-info text-center" role="alert">
                            <strong>{{ __('Payment pending') }}</strong>
                            <p class="mb-3">{{ __('Your order will be processed once payment is confirmed.') }}</p>
                            <form action="{{ route('checkout.pay', $order) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">{{ __('Pay now') }}</button>
                            </form>
                        </div>
                    @endif

                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>{{ __('Order number') }}:</strong>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <span class="h4">{{ $order->order_number }}</span>
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
                                    <td><strong>{{ number_format($order->subtotal, 2) }} €</strong></td>
                                </tr>
                                @if ($order->shipping_amount > 0)
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>{{ __('Shipping') }}:</strong></td>
                                        <td><strong>{{ number_format($order->shipping_amount, 2) }} €</strong></td>
                                    </tr>
                                @endif
                                @if ($order->tax_amount > 0)
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>{{ __('Tax') }}:</strong></td>
                                        <td><strong>{{ number_format($order->tax_amount, 2) }} €</strong></td>
                                    </tr>
                                @endif
                                <tr class="table-active">
                                    <td colspan="3" class="text-end"><strong>{{ __('Total') }}:</strong></td>
                                    <td><strong>{{ number_format($order->total, 2) }} €</strong></td>
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
                                    {{ $order->shipping_address['postal_code'] }} {{ $order->shipping_address['city'] }}
                                    <br />
                                    {{ $order->shipping_address['country'] }}
                                </address>
                            </div>
                            @if ($order->billing_address)
                                <div class="col-md-6">
                                    <h3>{{ __('Billing Address') }}</h3>
                                    <address>
                                        {{ $order->billing_address['street'] }}<br />
                                        {{ $order->billing_address['postal_code'] }} {{ $order->billing_address['city'] }}
                                        <br />
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
