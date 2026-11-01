@php use App\Enums\PaymentStatus; @endphp
<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1>{{ $order->order_number }}</h1>
                        <a href="{{ route('account.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                            {{ __('Back to orders') }}
                        </a>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <small class="text-muted">{{ __('Order number') }}</small>
                                    <div class="fw-bold">{{ $order->order_number }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">{{ __('Date') }}</small>
                                    <div>{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">{{ __('Status') }}</small>
                                    <div>{{ $order->status->getLabel() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($order->payment_status->value === PaymentStatus::Unpaid->value)
                        <div class="alert alert-info" role="alert">
                            <strong>{{ __('Payment pending') }}</strong>
                            <p class="mb-3">{{ __('Your order will be processed once payment is confirmed.') }}</p>
                            <form action="{{ route('checkout.pay', $order) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">{{ __('Pay now') }}</button>
                            </form>
                        </div>
                    @endif

                    <h2 class="mb-3">{{ __('Order Summary') }}</h2>
                    <div class="table-responsive">
                        <table class="table-striped table">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th class="text-center">{{ __('Quantity') }}</th>
                                    <th class="text-end">{{ __('Unit price') }}</th>
                                    <th class="text-end">{{ __('Total price') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->orderItems as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product_name }}</strong>
                                            @if ($item->product_variant_label)
                                                <br />
                                                <small class="text-muted">{{ $item->product_variant_label }}</small>
                                            @endif
                                            @if ($item->sku)
                                                <br />
                                                <small class="text-muted">{{ __('SKU') }}: {{ $item->sku }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">{{ number_format($item->unit_price, 2) }} €</td>
                                        <td class="text-end">{{ number_format($item->total_price, 2) }} €</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end">
                                        <strong>{{ __('Subtotal (incl. VAT)') }}:</strong>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($order->subtotal, 2) }} €</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>{{ __('Shipping') }}:</strong></td>
                                    <td class="text-end">
                                        <strong>
                                            @if ($order->shipping_amount > 0)
                                                {{ number_format($order->shipping_amount, 2) }} €
                                            @else
                                                0 €
                                            @endif
                                        </strong>
                                    </td>
                                </tr>
                                @if ($order->tax_amount > 0)
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>{{ __('VAT') }}:</strong></td>
                                        <td class="text-end">
                                            <strong>{{ number_format($order->tax_amount, 2) }} €</strong>
                                        </td>
                                    </tr>
                                @endif
                                <tr class="table-active">
                                    <td colspan="3" class="text-end">
                                        <strong>{{ __('Total (incl. VAT)') }}:</strong>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ number_format($order->total, 2) }} €</strong>
                                    </td>
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
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
