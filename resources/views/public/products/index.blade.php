<x-layouts.app>
    @push('scripts-head-bottom')
        <style>
            :root {
                --st-product-card-bg: var(--st-body-bg);
                --st-product-card-border: var(--st-border-color);
                --st-product-card-hover-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
                --st-product-image-height: 200px;
                --st-product-image-radius: var(--st-border-radius-lg);
                --st-product-title-color: var(--st-body-color);
                --st-product-link-color: var(--st-link-color);
                --st-product-link-hover-color: var(--st-link-hover-color);
                --st-product-meta-color: var(--st-secondary-color);
                --st-product-price-color: var(--st-body-color);
                --st-product-discount-price-color: var(--st-link-color);
                --st-product-badge-bg: var(--st-link-color);
                --st-product-badge-color: #fff;
                --st-product-stock-bg: var(--st-success-color, #198754);
                --st-product-out-of-stock-bg: var(--st-danger-color, #dc3545);
            }

            [data-theme='dark'] {
                --st-product-card-hover-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.3);
            }

            .c-product-card {
                position: relative;
                display: flex;
                flex-direction: column;
                height: 100%;
                padding: 0;
                border: 1px solid var(--st-product-card-border);
                border-radius: var(--st-border-radius-lg);
                background-color: var(--st-product-card-bg);
                text-decoration: none;
                color: var(--st-product-title-color);
                transition:
                    box-shadow 0.2s ease,
                    border-color 0.2s ease,
                    transform 0.15s ease;
                overflow: hidden;
            }

            .c-product-card:hover {
                box-shadow: var(--st-product-card-hover-shadow);
                border-color: var(--st-product-link-color);
                transform: translateY(-2px);
                color: var(--st-product-link-hover-color);
                text-decoration: none;
            }

            .c-product-card:focus-visible {
                outline: 2px solid var(--st-product-link-color);
                outline-offset: 2px;
            }

            .c-product-image-wrapper {
                position: relative;
                width: 100%;
                height: var(--st-product-image-height);
                background-color: var(--st-tertiary-bg);
                overflow: hidden;
            }

            .c-product-image {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3s ease;
            }

            .c-product-card:hover .c-product-image {
                transform: scale(1.03);
            }

            .c-product-badges {
                position: absolute;
                top: 0.75rem;
                left: 0.75rem;
                display: flex;
                flex-wrap: wrap;
                gap: 0.35rem;
                z-index: 1;
            }

            .c-product-badge {
                display: inline-block;
                font-size: 0.7rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                padding: 0.2rem 0.5rem;
                border-radius: var(--st-border-radius);
                background-color: var(--st-product-badge-bg);
                color: var(--st-product-badge-color);
            }

            .c-product-badge--discount {
                background-color: var(--st-product-badge-bg);
            }

            .c-product-badge--stock {
                background-color: var(--st-product-stock-bg);
            }

            .c-product-badge--out-of-stock {
                background-color: var(--st-product-out-of-stock-bg);
            }

            .c-product-body {
                flex: 1;
                display: flex;
                flex-direction: column;
                padding: 1.25rem 1.5rem;
                gap: 0.5rem;
            }

            .c-product-categories {
                display: flex;
                flex-wrap: wrap;
                gap: 0.35rem;
            }

            .c-product-category-badge {
                display: inline-block;
                padding: 0.15rem 0.5rem;
                font-size: 0.7rem;
                font-weight: 500;
                border-radius: var(--st-border-radius);
                background-color: var(--st-tertiary-bg);
                color: var(--st-product-meta-color);
            }

            .c-product-title {
                margin: 0;
                font-weight: 600;
                font-size: 1.15rem;
                line-height: 1.3;
            }

            .c-product-intro {
                margin: 0;
                font-size: 0.925rem;
                line-height: 1.5;
                color: var(--st-body-color);
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
                flex: 1;
            }

            .c-product-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                margin-top: 0.25rem;
            }

            .c-product-price {
                font-weight: 700;
                font-size: 1.1rem;
                color: var(--st-product-price-color);
            }

            .c-product-discount-price {
                font-weight: 700;
                font-size: 1.1rem;
                color: var(--st-product-discount-price-color);
            }

            .c-product-original-price {
                text-decoration: line-through;
                color: var(--st-product-meta-color);
                font-size: 0.85rem;
                font-weight: 400;
                margin-left: 0.35rem;
            }

            .c-products-empty {
                text-align: center;
                padding: 3rem 1rem;
                color: var(--st-secondary-color);
            }

            @media (max-width: 767.98px) {
                :root {
                    --st-product-image-height: 180px;
                }
            }
        </style>
    @endpush

    <section class="py-5">
        <div class="container-xl">
            <h1 class="mb-4">
                @lang('Products')
            </h1>

            @if ($products->isNotEmpty())
                <div class="row g-4">
                    @foreach ($products as $product)
                        <div class="col-lg-4 col-md-6 col-12">
                            <a class="c-product-card" href="{{ route('products.show', $product) }}">
                                <div class="c-product-image-wrapper">
                                    <img
                                        class="c-product-image"
                                        src="{{ $product->getFirstMediaUrl('cover', 'preview') }}"
                                        alt="{{ $product->title }}"
                                        loading="lazy"
                                        width="400"
                                        height="400"
                                    />

                                    <div class="c-product-badges">
                                        @if ($product->is_discounted && $product->discount_price)
                                            <span class="c-product-badge c-product-badge--discount">
                                                {{ __('Sale') }}
                                            </span>
                                        @endif

                                        @if ($product->stock_quantity > 0)
                                            <span class="c-product-badge c-product-badge--stock">
                                                {{ __('In stock') }}
                                            </span>
                                        @else
                                            <span class="c-product-badge c-product-badge--out-of-stock">
                                                {{ __('Out of stock') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="c-product-body">
                                    <h2 class="c-product-title">{{ $product->title }}</h2>

                                    @if ($product->categories->isNotEmpty())
                                        <div class="c-product-categories">
                                            @foreach ($product->categories as $category)
                                                <span class="c-product-category-badge">{{ $category->title }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($product->intro)
                                        <p class="c-product-intro">{{ $product->intro }}</p>
                                    @endif

                                    <div class="c-product-footer">
                                        @if ($product->is_discounted && $product->discount_price)
                                            <span class="c-product-discount-price">
                                                {{ number_format($product->discount_price, 2) }} €
                                            </span>
                                            <span class="c-product-original-price">
                                                {{ number_format($product->price, 2) }} €
                                            </span>
                                        @else
                                            <span class="c-product-price">
                                                {{ number_format($product->price, 2) }} €
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div>
                    <hr />
                    {{ $products->links() }}
                </div>
            @else
                <div class="c-products-empty">
                    <p class="lead mb-0">{{ __('No products available yet.') }}</p>
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
