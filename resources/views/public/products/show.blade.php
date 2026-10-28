<x-layouts.app>
    @push('scripts-head-bottom')
        <style>
            :root {
                --st-product-show-title-color: var(--st-body-color);
                --st-product-show-meta-color: var(--st-secondary-color);
                --st-product-show-btn-bg: var(--st-body-color);
                --st-product-show-btn-color: var(--st-body-bg);
                --st-product-show-btn-hover-bg: var(--st-link-color);
                --st-product-show-price-color: var(--st-body-color);
                --st-product-show-discount-price-color: var(--st-link-color);
                --st-product-show-banner-height: 320px;
                --st-product-show-stock-color: var(--st-success-color, #198754);
                --st-product-show-out-of-stock-color: var(--st-danger-color, #dc3545);
            }

            .c-product-show-banner {
                position: relative;
                width: 100%;
                height: var(--st-product-show-banner-height);
                margin-bottom: 2rem;
                border-radius: var(--st-border-radius-lg);
                overflow: hidden;
                background-color: var(--st-border-color-translucent);
            }

            .c-product-show-banner img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            .c-product-show-banner-overlay {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                padding: 2rem;
                background: linear-gradient(transparent, rgba(0, 0, 0, 0.6));
                color: #fff;
            }

            .c-product-show-categories {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                margin-top: 0.75rem;
            }

            .c-product-show-category-badge {
                display: inline-block;
                padding: 0.2rem 0.6rem;
                font-size: 0.75rem;
                font-weight: 500;
                border-radius: var(--st-border-radius);
                background-color: rgba(255, 255, 255, 0.2);
                color: #fff;
                text-decoration: none;
                transition: background-color 0.2s ease;
            }

            .c-product-show-category-badge:hover {
                background-color: rgba(255, 255, 255, 0.35);
                color: #fff;
                text-decoration: none;
            }

            .c-product-show-title {
                margin: 0;
                font-weight: 700;
                font-size: clamp(1.5rem, 3.5vw, 2rem);
                line-height: 1.25;
                color: #fff;
                text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            }

            .c-product-show-header {
                display: flex;
                justify-content: flex-end;
                margin-bottom: 1.5rem;
            }

            .c-product-show-edit-link {
                font-size: 0.85rem;
                color: var(--st-secondary-color);
                text-decoration: underline;
                text-decoration-color: var(--st-border-color);
                text-underline-offset: 3px;
                transition: color 0.2s ease;
            }

            .c-product-show-edit-link:hover {
                color: var(--st-link-color);
            }

            .c-product-show-body {
                max-width: 720px;
                margin: 0 auto;
            }

            .c-product-show-intro {
                margin: 0 0 1.5rem;
                font-size: 1.1rem;
                line-height: 1.6;
                color: var(--st-body-color);
            }

            .c-product-show-meta {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 0.75rem;
                font-size: 0.85rem;
                color: var(--st-product-show-meta-color);
                margin-bottom: 2rem;
            }

            .c-product-show-price {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--st-product-show-price-color);
            }

            .c-product-show-discount-price {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--st-product-show-discount-price-color);
            }

            .c-product-show-original-price {
                text-decoration: line-through;
                color: var(--st-product-show-meta-color);
                font-size: 1.1rem;
                font-weight: 400;
                margin-left: 0.35rem;
            }

            .c-product-show-stock {
                display: inline-flex;
                align-items: center;
                gap: 0.3rem;
                font-size: 0.85rem;
                font-weight: 500;
            }

            .c-product-show-stock--in-stock {
                color: var(--st-product-show-stock-color);
            }

            .c-product-show-stock--out-of-stock {
                color: var(--st-product-show-out-of-stock-color);
            }

            .c-product-show-stock-dot {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background-color: currentColor;
            }

            .c-product-show-website-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                font-size: 0.85rem;
                font-weight: 500;
                padding: 0.5rem 0.85rem;
                border-radius: var(--st-border-radius);
                background-color: var(--st-body-color);
                color: var(--st-body-bg);
                text-decoration: none;
                transition: opacity 0.2s ease;
            }

            .c-product-show-website-btn:hover {
                opacity: 0.8;
                color: var(--st-body-bg);
                text-decoration: none;
            }

            .c-product-show-back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.9rem;
                font-weight: 500;
                padding: 0.5rem 1.25rem;
                border-radius: var(--st-border-radius);
                background-color: var(--st-product-show-btn-bg);
                color: var(--st-product-show-btn-color);
                text-decoration: none;
                transition:
                    background-color 0.2s ease,
                    color 0.2s ease;
                margin-top: 2.5rem;
            }

            .c-product-show-back-btn:hover {
                background-color: var(--st-product-show-btn-hover-bg);
                color: var(--st-body-bg);
                text-decoration: none;
            }

            .c-product-show-back-btn:focus-visible {
                outline: 2px solid var(--st-link-color);
                outline-offset: 2px;
            }

            .c-product-variants {
                margin-top: 2.5rem;
                padding-top: 2rem;
                border-top: 1px solid var(--st-border-color);
            }

            .c-product-variants-title {
                margin: 0 0 1.25rem;
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--st-body-color);
            }

            .c-product-variants-list {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }

            .c-product-variant-card {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 1rem;
                padding: 1rem 1.25rem;
                border: 1px solid var(--st-border-color);
                border-radius: var(--st-border-radius);
                background-color: var(--st-body-bg);
            }

            .c-product-variant-options {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
                flex: 1 1 auto;
                min-width: 200px;
            }

            .c-product-variant-option-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                padding: 0.25rem 0.65rem;
                font-size: 0.8rem;
                font-weight: 500;
                border-radius: var(--st-border-radius);
                background-color: var(--st-border-color-translucent);
                color: var(--st-body-color);
            }

            .c-product-variant-option-color-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                border: 1px solid var(--st-border-color);
            }

            .c-product-variant-meta {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 0.75rem;
                font-size: 0.85rem;
            }

            .c-product-variant-sku {
                color: var(--st-secondary-color);
                font-family: var(--st-font-mono, monospace);
            }

            .c-product-variant-price {
                font-weight: 600;
                color: var(--st-body-color);
            }

            .c-product-variant-discount-price {
                font-weight: 700;
                color: var(--st-product-show-discount-price-color);
            }

            .c-product-variant-original-price {
                text-decoration: line-through;
                color: var(--st-product-show-meta-color);
                font-size: 0.8rem;
                font-weight: 400;
                margin-left: 0.25rem;
            }

            .c-product-variant-stock {
                display: inline-flex;
                align-items: center;
                gap: 0.3rem;
                font-size: 0.8rem;
                font-weight: 500;
            }

            .c-product-variant-stock--in-stock {
                color: var(--st-product-show-stock-color);
            }

            .c-product-variant-stock--out-of-stock {
                color: var(--st-product-show-out-of-stock-color);
            }

            .c-product-variant-stock-dot {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background-color: currentColor;
            }

            @media (max-width: 767.98px) {
                :root {
                    --st-product-show-banner-height: 220px;
                }

                .c-product-show-banner-overlay {
                    padding: 1.25rem;
                }
            }
        </style>
    @endpush

    <section class="py-5">
        <div class="container-xl">
            <div class="c-product-show-header">
                @can('update products')
                    <a
                        href="{{ route('filament.admin.resources.products.edit', $product) }}"
                        class="c-product-show-edit-link"
                    >
                        {{ __('Edit :model', ['model' => __('Product')]) }}
                    </a>
                @endcan
            </div>

            @php
                $coverMedia = $product->getFirstMedia('cover');
            @endphp

            <div class="c-product-show-banner">
                @if ($coverMedia)
                    {{ $coverMedia->img('webp', ['alt' => $product->title]) }}
                @endif

                <div class="c-product-show-banner-overlay">
                    <h1 class="c-product-show-title">{{ $product->title }}</h1>

                    @if ($product->categories->isNotEmpty())
                        <div class="c-product-show-categories">
                            @foreach ($product->categories as $category)
                                <span class="c-product-show-category-badge">{{ $category->title }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="c-product-show-body">
                @if ($product->intro)
                    <p class="c-product-show-intro">{{ $product->intro }}</p>
                @endif

                <div class="c-product-show-meta">
                    @if ($product->is_discounted && $product->discount_price)
                        <span class="c-product-show-discount-price">
                            {{ number_format($product->discount_price, 2) }} €
                        </span>
                        <span class="c-product-show-original-price"> {{ number_format($product->price, 2) }} € </span>
                    @else
                        <span class="c-product-show-price"> {{ number_format($product->price, 2) }} € </span>
                    @endif

                    @if ($product->stock_quantity > 0)
                        <span class="c-product-show-stock c-product-show-stock--in-stock">
                            <span class="c-product-show-stock-dot"></span>
                            {{ __('In stock') }}
                        </span>
                    @else
                        <span class="c-product-show-stock c-product-show-stock--out-of-stock">
                            <span class="c-product-show-stock-dot"></span>
                            {{ __('Out of stock') }}
                        </span>
                    @endif

                    @if ($product->website_url)
                        <a
                            class="c-product-show-website-btn"
                            href="{{ $product->website_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {{ $product->website_title ?: __('Visit site') }}
                            <x-icons.box-arrow-up-right />
                        </a>
                    @endif
                </div>

                @if ($product->content)
                    <div class="c-rich-content">{!! $product->renderResponsiveRichContent() !!}</div>
                @endif

                @if ($product->variants->isNotEmpty())
                    <div class="c-product-variants">
                        <h2 class="c-product-variants-title">{{ __('Variants') }}</h2>
                        <div class="c-product-variants-list">
                            @foreach ($product->variants as $variant)
                                <div class="c-product-variant-card">
                                    @if ($variant->options->isNotEmpty())
                                        <div class="c-product-variant-options">
                                            @foreach ($variant->options as $option)
                                                <span class="c-product-variant-option-badge">
                                                    @if ($option->value && str_starts_with($option->value, '#'))
                                                        <span
                                                            class="c-product-variant-option-color-dot"
                                                            style="background-color: {{ $option->value }}"
                                                        ></span>
                                                    @endif
                                                    {{ $option->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="c-product-variant-meta">
                                        @if ($variant->sku)
                                            <span class="c-product-variant-sku">{{ __('SKU') }}: {{ $variant->sku }}</span>
                                        @endif

                                        @if ($variant->is_discounted && $variant->discount_price)
                                            <span class="c-product-variant-discount-price">
                                                {{ number_format($variant->discount_price, 2) }} €
                                            </span>
                                            <span class="c-product-variant-original-price">{{ number_format($variant->price, 2) }} €</span>
                                        @elseif ($variant->price)
                                            <span class="c-product-variant-price">{{ number_format($variant->price, 2) }} €</span>
                                        @endif

                                        @if ($variant->stock_quantity > 0)
                                            <span class="c-product-variant-stock c-product-variant-stock--in-stock">
                                                <span class="c-product-variant-stock-dot"></span>
                                                {{ __('In stock') }}
                                            </span>
                                        @else
                                            <span class="c-product-variant-stock c-product-variant-stock--out-of-stock">
                                                <span class="c-product-variant-stock-dot"></span>
                                                {{ __('Out of stock') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <a href="{{ route('products.index') }}" class="c-product-show-back-btn">
                    <x-icons.arrow-left />
                    {{ __('Back to overview') }}
                </a>
            </div>
        </div>
    </section>
</x-layouts.app>
