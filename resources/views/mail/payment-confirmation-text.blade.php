{{ __('Thank you for your order!') }}

{{ __('Your payment for order') }} {{ $order->order_number }} {{ __('has been confirmed.') }}

{{ __('Order total') }}: {{ number_format($order->total, 2) }} € {{ __('(incl. VAT)') }}

@foreach ($order->orderItems as $item)
{{ $item->product_name }}
@if ($item->product_variant_label)
  {{ $item->product_variant_label }}
@endif
{{ __('Quantity') }}: {{ $item->quantity }}
{{ __('Total') }}: {{ number_format($item->total_price, 2) }} €
@endforeach

{{ __('Your order will be processed shortly.') }}

{{ __('Thank you') }},
{{ config('app.name') }}
