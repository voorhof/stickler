<x-layouts.app>
    <section class="py-5">
        <div class="container-xl">
            <h1 class="mb-4">{{ __('Checkout') }}</h1>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                {{-- Cart Summary --}}
                <div class="col-lg-5 mb-lg-0 mb-5">
                    <h2 class="mb-3">{{ __('Cart Items') }}</h2>
                    <div class="table-responsive">
                        <table class="table-striped table">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Total price') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cart->cartItems as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product_name }}</strong>
                                            @if ($item->product_variant_label)
                                                <br
                                                /><small class="text-muted">{{ $item->product_variant_label }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format($item->total_price, 2) }} €</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="text-end"><strong>{{ __('Subtotal') }}:</strong></td>
                                    <td colspan="2"><strong>{{ number_format($cart->total, 2) }} €</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Customer Info Form --}}
                <div class="col-lg-7">
                    <form action="{{ route('checkout.store') }}" method="POST">
                        <x-csrf-input />

                        {{-- Customer Info --}}
                        <h2 class="mb-3">{{ __('Customer Information') }}</h2>

                        <div class="form-floating mb-3">
                            <input
                                type="text"
                                @class(['form-control', 'is-invalid' => $errors->has('name')])
                                id="nameInput"
                                name="name"
                                value="{{ old('name', $user?->name ?? $cart->customerName()) }}"
                                placeholder="{{ __('Name') }}"
                                autocomplete="name"
                                required
                                maxlength="255"
                            />
                            <label for="nameInput">
                                {{ __('Name') }}
                                <span class="opacity-50">*</span>
                            </label>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input
                                        type="email"
                                        @class(['form-control', 'is-invalid' => $errors->has('email')])
                                        id="emailInput"
                                        name="email"
                                        value="{{ old('email', $user?->email ?? $cart->customerEmail()) }}"
                                        placeholder="{{ __('Email address') }}"
                                        autocomplete="email"
                                        required
                                        maxlength="255"
                                    />
                                    <label for="emailInput">
                                        {{ __('Email address') }}
                                        <span class="opacity-50">*</span>
                                    </label>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input
                                        type="text"
                                        @class(['form-control', 'is-invalid' => $errors->has('phone')])
                                        id="phoneInput"
                                        name="phone"
                                        value="{{ old('phone', $user?->phone ?? $cart->customerPhone()) }}"
                                        placeholder="{{ __('Phone') }}"
                                        autocomplete="tel"
                                        maxlength="255"
                                    />
                                    <label for="phoneInput"> {{ __('Phone') }} </label>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Shipping Address --}}
                        <h2 class="mb-3">{{ __('Shipping Address') }}</h2>

                        <div class="form-floating mb-3">
                            <input
                                type="text"
                                @class(['form-control', 'is-invalid' => $errors->has('shipping_street')])
                                id="shippingStreetInput"
                                name="shipping_street"
                                value="{{ old('shipping_street', optional($cart->customerShippingAddress())['street'] ?? '') }}"
                                placeholder="{{ __('Street') }}"
                                autocomplete="shipping street-address"
                                required
                                maxlength="255"
                            />
                            <label for="shippingStreetInput">
                                {{ __('Street') }}
                                <span class="opacity-50">*</span>
                            </label>
                            @error('shipping_street')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input
                                        type="text"
                                        @class(['form-control', 'is-invalid' => $errors->has('shipping_city')])
                                        id="shippingCityInput"
                                        name="shipping_city"
                                        value="{{ old('shipping_city', optional($cart->customerShippingAddress())['city'] ?? '') }}"
                                        placeholder="{{ __('City') }}"
                                        autocomplete="shipping address-level2"
                                        required
                                        maxlength="255"
                                    />
                                    <label for="shippingCityInput">
                                        {{ __('City') }}
                                        <span class="opacity-50">*</span>
                                    </label>
                                    @error('shipping_city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input
                                        type="text"
                                        @class(['form-control', 'is-invalid' => $errors->has('shipping_postal_code')])
                                        id="shippingPostalCodeInput"
                                        name="shipping_postal_code"
                                        value="{{ old('shipping_postal_code', optional($cart->customerShippingAddress())['postal_code'] ?? '') }}"
                                        placeholder="{{ __('Postal code') }}"
                                        autocomplete="shipping postal-code"
                                        required
                                        maxlength="255"
                                    />
                                    <label for="shippingPostalCodeInput">
                                        {{ __('Postal code') }}
                                        <span class="opacity-50">*</span>
                                    </label>
                                    @error('shipping_postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating mb-3">
                                    <input
                                        type="text"
                                        @class(['form-control', 'is-invalid' => $errors->has('shipping_country')])
                                        id="shippingCountryInput"
                                        name="shipping_country"
                                        value="{{ old('shipping_country', optional($cart->customerShippingAddress())['country'] ?? '') }}"
                                        placeholder="{{ __('Country') }}"
                                        autocomplete="shipping country-name"
                                        required
                                        maxlength="255"
                                    />
                                    <label for="shippingCountryInput">
                                        {{ __('Country') }}
                                        <span class="opacity-50">*</span>
                                    </label>
                                    @error('shipping_country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Billing Address --}}
                        <div class="form-check mb-3">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="billingSameAsShippingCheckbox"
                                name="billing_same_as_shipping"
                                value="1"
                                {{ old('billing_same_as_shipping', true) ? 'checked' : '' }}
                            />
                            <label class="form-check-label" for="billingSameAsShippingCheckbox">
                                {{ __('Use same address for billing') }}
                            </label>
                        </div>

                        <div
                            id="billingAddressFields"
                            class="{{ old('billing_same_as_shipping', true) ? 'd-none' : '' }}"
                        >
                            <h2 class="mb-3">{{ __('Billing Address') }}</h2>

                            <div class="form-floating mb-3">
                                <input
                                    type="text"
                                    @class(['form-control', 'is-invalid' => $errors->has('billing_street')])
                                    id="billingStreetInput"
                                    name="billing_street"
                                    value="{{ old('billing_street', optional($cart->customerBillingAddress())['street'] ?? '') }}"
                                    placeholder="{{ __('Street') }}"
                                    autocomplete="billing street-address"
                                    maxlength="255"
                                />
                                <label for="billingStreetInput">
                                    {{ __('Street') }}
                                    <span class="opacity-50">*</span>
                                </label>
                                @error('billing_street')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input
                                            type="text"
                                            @class(['form-control', 'is-invalid' => $errors->has('billing_city')])
                                            id="billingCityInput"
                                            name="billing_city"
                                            value="{{ old('billing_city', optional($cart->customerBillingAddress())['city'] ?? '') }}"
                                            placeholder="{{ __('City') }}"
                                            autocomplete="billing address-level2"
                                            maxlength="255"
                                        />
                                        <label for="billingCityInput">
                                            {{ __('City') }}
                                            <span class="opacity-50">*</span>
                                        </label>
                                        @error('billing_city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-floating mb-3">
                                        <input
                                            type="text"
                                            @class(['form-control', 'is-invalid' => $errors->has('billing_postal_code')])
                                            id="billingPostalCodeInput"
                                            name="billing_postal_code"
                                            value="{{ old('billing_postal_code', optional($cart->customerBillingAddress())['postal_code'] ?? '') }}"
                                            placeholder="{{ __('Postal code') }}"
                                            autocomplete="billing postal-code"
                                            maxlength="255"
                                        />
                                        <label for="billingPostalCodeInput">
                                            {{ __('Postal code') }}
                                            <span class="opacity-50">*</span>
                                        </label>
                                        @error('billing_postal_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-floating mb-3">
                                        <input
                                            type="text"
                                            @class(['form-control', 'is-invalid' => $errors->has('billing_country')])
                                            id="billingCountryInput"
                                            name="billing_country"
                                            value="{{ old('billing_country', optional($cart->customerBillingAddress())['country'] ?? '') }}"
                                            placeholder="{{ __('Country') }}"
                                            autocomplete="billing country-name"
                                            maxlength="255"
                                        />
                                        <label for="billingCountryInput">
                                            {{ __('Country') }}
                                            <span class="opacity-50">*</span>
                                        </label>
                                        @error('billing_country')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <h2 class="mb-3">{{ __('Notes') }}</h2>
                        <div class="form-floating mb-4">
                            <textarea
                                @class(['form-control', 'is-invalid' => $errors->has('notes')])
                                style="height: 6rem"
                                {{-- used instead of rows="3" for floating label field --}}
                                id="notesInput"
                                name="notes"
                                placeholder="{{ __('Notes') }}"
                                maxlength="2550"
                            >{{ old('notes', $cart->customerNotes()) }}</textarea>
                            <label for="notesInput">{{ __('Notes') }}</label>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('cart.index') }}" class="btn btn-secondary"> {{ __('Back to cart') }} </a>
                            <button type="submit" class="btn btn-primary">{{ __('Place order') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    @push('scripts-body-bottom')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const checkbox = document.getElementById('billingSameAsShippingCheckbox');
                const fields = document.getElementById('billingAddressFields');

                function toggleBillingFields() {
                    if (checkbox.checked) {
                        fields.classList.add('d-none');
                    } else {
                        fields.classList.remove('d-none');
                    }
                }

                checkbox.addEventListener('change', toggleBillingFields);
                toggleBillingFields();
            });
        </script>
    @endpush
</x-layouts.app>
