<x-mail::message>
# {{ __('Thank you for your order!') }}

{{ __('Your payment for order') }} **{{ $order->order_number }}** {{ __('has been confirmed.') }}

{{ __('Order total') }}: **{{ number_format($order->total, 2) }} €**

<table>
    <thead>
        <tr>
            <th>{{ __('Product') }}</th>
            <th>{{ __('Quantity') }}</th>
            <th>{{ __('Total') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($order->orderItems as $item)
            <tr>
                <td>{{ $item->product_name }}
                    @if ($item->product_variant_label)
                        <br><small>{{ $item->product_variant_label }}</small>
                    @endif
                </td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->total_price, 2) }} €</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ __('Your order will be processed shortly.') }}

{{ __('Thank you') }},
{{ config('app.name') }}

</x-mail::message>
