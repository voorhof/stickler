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

            @guest
                <div class="mb-4">
                    <p class="text-muted">
                        {{ __('Already have an account?') }}
                        <a href="{{ route('login') }}">{{ __('Log in') }}</a>
                        {{ __('or') }}
                        <a href="{{ route('register') }}">{{ __('create an account to track your order') }}</a>.
                    </p>
                </div>
            @endguest

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

                        {{-- Name --}}
                        <div class="form-floating mb-3">
                            <x-form.input-text
                                id="nameInput"
                                name="name"
                                :value="old('name', $user?->name ?? $cart->customerName())"
                                placeholder="{{ __('Name') }}"
                                required
                                maxlength="255"
                                autocomplete="name"
                                class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                                :describedBy="$errors->has('name') ? 'name_error' : null"
                            />
                            <x-form.input-label for="nameInput" :value="__('Name').' *'" />
                            <x-form.input-error id="name_error" :message="$errors->first('name')" />
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                {{-- Email Address --}}
                                <div class="form-floating mb-3">
                                    <x-form.input-text
                                        type="email"
                                        id="emailInput"
                                        name="email"
                                        :value="old('email', $user?->email ?? $cart->customerEmail())"
                                        placeholder="{{ __('Email address') }}"
                                        required
                                        maxlength="255"
                                        autocomplete="email"
                                        class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                                        :describedBy="$errors->has('email') ? 'email_error' : null"
                                    />
                                    <x-form.input-label for="emailInput" :value="__('Email address').' *'" />
                                    <x-form.input-error id="email_error" :message="$errors->first('email')" />
                                </div>
                            </div>

                            <div class="col-md-6">
                                {{-- Phone --}}
                                <div class="form-floating mb-3">
                                    <x-form.input-text
                                        id="phoneInput"
                                        name="phone"
                                        :value="old('phone', $user?->phone ?? $cart->customerPhone())"
                                        placeholder="{{ __('Phone') }}"
                                        maxlength="28"
                                        autocomplete="tel"
                                        class="{{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                        :describedBy="$errors->has('phone') ? 'phone_error' : null"
                                    />
                                    <x-form.input-label for="phoneInput" :value="__('Phone')" />
                                    <x-form.input-error id="phone_error" :message="$errors->first('phone')" />
                                </div>
                            </div>
                        </div>

                        {{-- Shipping Address --}}
                        <h2 class="mb-3">{{ __('Shipping Address') }}</h2>

                        {{-- Shipping Street --}}
                        <div class="form-floating mb-3">
                            <x-form.input-text
                                id="shippingStreetInput"
                                name="shipping_street"
                                :value="old('shipping_street', optional($cart->customerShippingAddress())['street'] ?? '')"
                                placeholder="{{ __('Street') }}"
                                required
                                maxlength="255"
                                autocomplete="shipping street-address"
                                class="{{ $errors->has('shipping_street') ? 'is-invalid' : '' }}"
                                :describedBy="$errors->has('shipping_street') ? 'shipping_street_error' : null"
                            />
                            <x-form.input-label for="shippingStreetInput" :value="__('Street').' *'" />
                            <x-form.input-error
                                id="shipping_street_error"
                                :message="$errors->first('shipping_street')"
                            />
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                {{-- Shipping City --}}
                                <div class="form-floating mb-3">
                                    <x-form.input-text
                                        id="shippingCityInput"
                                        name="shipping_city"
                                        :value="old('shipping_city', optional($cart->customerShippingAddress())['city'] ?? '')"
                                        placeholder="{{ __('City') }}"
                                        required
                                        maxlength="255"
                                        autocomplete="shipping address-level2"
                                        class="{{ $errors->has('shipping_city') ? 'is-invalid' : '' }}"
                                        :describedBy="$errors->has('shipping_city') ? 'shipping_city_error' : null"
                                    />
                                    <x-form.input-label for="shippingCityInput" :value="__('City').' *'" />
                                    <x-form.input-error
                                        id="shipping_city_error"
                                        :message="$errors->first('shipping_city')"
                                    />
                                </div>
                            </div>

                            <div class="col-md-3">
                                {{-- Shipping Postal Code--}}
                                <div class="form-floating mb-3">
                                    <x-form.input-text
                                        id="shippingPostalCodeInput"
                                        name="shipping_postal_code"
                                        :value="old('shipping_postal_code', optional($cart->customerShippingAddress())['postal_code'] ?? '')"
                                        placeholder="{{ __('Postal code') }}"
                                        required
                                        maxlength="255"
                                        autocomplete="shipping postal-code"
                                        class="{{ $errors->has('shipping_postal_code') ? 'is-invalid' : '' }}"
                                        :describedBy="$errors->has('shipping_postal_code') ? 'shipping_postal_code_error' : null"
                                    />
                                    <x-form.input-label
                                        for="shippingPostalCodeInput"
                                        :value="__('Postal code').' *'"
                                    />
                                    <x-form.input-error
                                        id="shipping_postal_code_error"
                                        :message="$errors->first('shipping_postal_code')"
                                    />
                                </div>
                            </div>

                            <div class="col-md-3">
                                {{-- Shipping Country --}}
                                <div class="form-floating mb-3">
                                    <x-form.input-text
                                        id="shippingCountryInput"
                                        name="shipping_country"
                                        :value="old('shipping_country', optional($cart->customerShippingAddress())['country'] ?? '')"
                                        placeholder="{{ __('Country') }}"
                                        required
                                        maxlength="255"
                                        autocomplete="shipping country-name"
                                        class="{{ $errors->has('shipping_country') ? 'is-invalid' : '' }}"
                                        :describedBy="$errors->has('shipping_country') ? 'shipping_country_error' : null"
                                    />
                                    <x-form.input-label for="shippingCountryInput" :value="__('Country').' *'" />
                                    <x-form.input-error
                                        id="shipping_country_error"
                                        :message="$errors->first('shipping_country')"
                                    />
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

                            {{-- Billing Street --}}
                            <div class="form-floating mb-3">
                                <x-form.input-text
                                    id="billingStreetInput"
                                    name="billing_street"
                                    :value="old('billing_street', optional($cart->customerBillingAddress())['street'] ?? '')"
                                    placeholder="{{ __('Street') }}"
                                    required
                                    maxlength="255"
                                    autocomplete="billing street-address"
                                    class="{{ $errors->has('billing_street') ? 'is-invalid' : '' }}"
                                    :describedBy="$errors->has('billing_street') ? 'billing_street_error' : null"
                                />
                                <x-form.input-label for="billingStreetInput" :value="__('Street').' *'" />
                                <x-form.input-error
                                    id="billing_street_error"
                                    :message="$errors->first('billing_street')"
                                />
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    {{-- Billing City --}}
                                    <div class="form-floating mb-3">
                                        <x-form.input-text
                                            id="billingCityInput"
                                            name="billing_city"
                                            :value="old('billing_city', optional($cart->customerBillingAddress())['city'] ?? '')"
                                            placeholder="{{ __('City') }}"
                                            required
                                            maxlength="255"
                                            autocomplete="billing address-level2"
                                            class="{{ $errors->has('billing_city') ? 'is-invalid' : '' }}"
                                            :describedBy="$errors->has('billing_city') ? 'billing_city_error' : null"
                                        />
                                        <x-form.input-label for="billingCityInput" :value="__('City').' *'" />
                                        <x-form.input-error
                                            id="billing_city_error"
                                            :message="$errors->first('billing_city')"
                                        />
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    {{-- Billing Postal Code--}}
                                    <div class="form-floating mb-3">
                                        <x-form.input-text
                                            id="billingPostalCodeInput"
                                            name="billing_postal_code"
                                            :value="old('billing_postal_code', optional($cart->customerBillingAddress())['postal_code'] ?? '')"
                                            placeholder="{{ __('Postal code') }}"
                                            required
                                            maxlength="255"
                                            autocomplete="billing postal-code"
                                            class="{{ $errors->has('billing_postal_code') ? 'is-invalid' : '' }}"
                                            :describedBy="$errors->has('billing_postal_code') ? 'billing_postal_code_error' : null"
                                        />
                                        <x-form.input-label
                                            for="billingPostalCodeInput"
                                            :value="__('Postal code').' *'"
                                        />
                                        <x-form.input-error
                                            id="billing_postal_code_error"
                                            :message="$errors->first('billing_postal_code')"
                                        />
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    {{-- Billing Country --}}
                                    <div class="form-floating mb-3">
                                        <x-form.input-text
                                            id="billingCountryInput"
                                            name="billing_country"
                                            :value="old('billing_country', optional($cart->customerBillingAddress())['country'] ?? '')"
                                            placeholder="{{ __('Country') }}"
                                            required
                                            maxlength="255"
                                            autocomplete="billing country-name"
                                            class="{{ $errors->has('billing_country') ? 'is-invalid' : '' }}"
                                            :describedBy="$errors->has('billing_country') ? 'billing_country_error' : null"
                                        />
                                        <x-form.input-label for="billingCountryInput" :value="__('Country').' *'" />
                                        <x-form.input-error
                                            id="billing_country_error"
                                            :message="$errors->first('billing_country')"
                                        />
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
