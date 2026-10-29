<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            <h1 class="mb-4">{{ __('Cart') }}</h1>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($cart->isEmpty())
                <div class="alert alert-info">{{ __('Your cart is empty') }}</div>
                <a href="{{ route('products.index') }}" class="btn btn-primary"> {{ __('Browse products') }} </a>
            @else
                <div class="table-responsive">
                    <table class="table-striped table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Quantity') }}</th>
                                <th>{{ __('Unit price') }}</th>
                                <th>{{ __('Total price') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cart->items as $item)
                                <tr>
                                    <td>
                                        @if ($item->product && $item->product->getFirstMediaUrl('cover', 'thumb'))
                                            <img
                                                src="{{ $item->product->getFirstMediaUrl('cover', 'thumb') }}"
                                                alt="{{ $item->product_name }}"
                                                class="img-thumbnail"
                                                style="width: 40px; height: 40px; object-fit: cover"
                                                loading="lazy"
                                            />
                                        @endif
                                    </td>
                                    <td>
                                        <strong>
                                            @if ($item->product)
                                                <a href="{{ route('products.show', $item->product) }}">{{ $item->product_name }}</a>
                                            @else
                                                {{ $item->product_name }}
                                            @endif
                                        </strong>
                                        @if ($item->product_variant_label)
                                            <br
                                            /><small class="text-muted">{{ $item->product_variant_label }}</small>
                                        @endif
                                        @if ($item->sku)
                                            <br
                                            /><small class="text-muted">{{ __('SKU') }}: {{ $item->sku }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <form
                                            action="{{ route('cart.items.update', $item) }}"
                                            method="POST"
                                            class="d-inline"
                                        >
                                            @csrf
                                            <div class="input-group input-group-sm" style="width: 140px">
                                                <input
                                                    type="number"
                                                    name="quantity"
                                                    value="{{ $item->quantity }}"
                                                    min="1"
                                                    class="form-control"
                                                />
                                                <button type="submit" class="btn btn-outline-secondary">
                                                    {{ __('Update') }}
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                    <td>{{ number_format($item->unit_price, 2) }} €</td>
                                    <td>{{ number_format($item->total_price, 2) }} €</td>
                                    <td>
                                        <form
                                            action="{{ route('cart.items.remove', $item) }}"
                                            method="POST"
                                            class="d-inline"
                                        >
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                {{ __('Remove') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>{{ __('Subtotal') }}:</strong></td>
                                <td colspan="2"><strong>{{ number_format($cart->total, 2) }} €</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        {{ __('Continue shopping') }}
                    </a>
                    <form action="{{ route('cart.checkout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">{{ __('Proceed to checkout') }}</button>
                    </form>
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
