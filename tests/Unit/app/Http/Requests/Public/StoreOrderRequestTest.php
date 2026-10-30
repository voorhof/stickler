<?php

use App\Http\Requests\Public\StoreOrderRequest;
use Illuminate\Support\Facades\Validator;

describe('authorization', function () {
    test('it authorizes store order request', function () {
        $request = new StoreOrderRequest;

        expect($request->authorize())->toBeTrue();
    });
});

describe('validation', function () {
    test('it validates valid store order request data with billing same as shipping', function () {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+32470123456',
            'shipping_street' => 'Main St 1',
            'shipping_city' => 'Brussels',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'Belgium',
            'billing_same_as_shipping' => 1,
        ];

        $request = new StoreOrderRequest;
        $request->merge($data);

        $validator = Validator::make($data, $request->rules(), [], $request->attributes());

        expect($validator->passes())->toBeTrue();
    });

    test('it validates valid store order request data with separate billing address', function () {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+32470123456',
            'shipping_street' => 'Main St 1',
            'shipping_city' => 'Brussels',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'Belgium',
            'billing_same_as_shipping' => 0,
            'billing_street' => 'Other St 2',
            'billing_city' => 'Antwerp',
            'billing_postal_code' => '2000',
            'billing_country' => 'Belgium',
            'notes' => 'Please leave at the door',
        ];

        $request = new StoreOrderRequest;
        $request->merge($data);

        $validator = Validator::make($data, $request->rules(), [], $request->attributes());

        expect($validator->passes())->toBeTrue();
    });

    test('it fails validation when required fields are missing', function () {
        $request = new StoreOrderRequest;

        $validator = Validator::make([], $request->rules(), [], $request->attributes());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('name'))->toBeTrue()
            ->and($validator->errors()->has('email'))->toBeTrue()
            ->and($validator->errors()->has('shipping_street'))->toBeTrue()
            ->and($validator->errors()->has('shipping_city'))->toBeTrue()
            ->and($validator->errors()->has('shipping_postal_code'))->toBeTrue()
            ->and($validator->errors()->has('shipping_country'))->toBeTrue();
    });

    test('it validates email format', function () {
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'phone' => '+32470123456',
            'shipping_street' => 'Main St 1',
            'shipping_city' => 'Brussels',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'Belgium',
            'billing_same_as_shipping' => 1,
        ];

        $request = new StoreOrderRequest;
        $request->merge($data);

        $validator = Validator::make($data, $request->rules(), [], $request->attributes());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('email'))->toBeTrue();
    });

    test('it validates max lengths', function () {
        $data = [
            'name' => str_repeat('a', 256),
            'email' => 'john@ex'.str_repeat('a', 256).'mple.com',
            'phone' => str_repeat('a', 256),
            'shipping_street' => str_repeat('a', 256),
            'shipping_city' => str_repeat('a', 256),
            'shipping_postal_code' => str_repeat('a', 256),
            'shipping_country' => str_repeat('a', 256),
            'billing_same_as_shipping' => 1,
            'notes' => str_repeat('a', 2551),
        ];

        $request = new StoreOrderRequest;
        $request->merge($data);

        $validator = Validator::make($data, $request->rules(), [], $request->attributes());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('name'))->toBeTrue()
            ->and($validator->errors()->has('email'))->toBeTrue()
            ->and($validator->errors()->has('phone'))->toBeTrue()
            ->and($validator->errors()->has('shipping_street'))->toBeTrue()
            ->and($validator->errors()->has('shipping_city'))->toBeTrue()
            ->and($validator->errors()->has('shipping_postal_code'))->toBeTrue()
            ->and($validator->errors()->has('shipping_country'))->toBeTrue()
            ->and($validator->errors()->has('notes'))->toBeTrue();
    });

    test('it allows nullable phone', function () {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'shipping_street' => 'Main St 1',
            'shipping_city' => 'Brussels',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'Belgium',
            'billing_same_as_shipping' => 1,
        ];

        $request = new StoreOrderRequest;
        $request->merge($data);

        $validator = Validator::make($data, $request->rules(), [], $request->attributes());

        expect($validator->passes())->toBeTrue();
    });

    test('it requires billing fields when billing same as shipping is false', function () {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'shipping_street' => 'Main St 1',
            'shipping_city' => 'Brussels',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'Belgium',
            'billing_same_as_shipping' => 0,
        ];

        $request = new StoreOrderRequest;
        $request->merge($data);

        $validator = Validator::make($data, $request->rules(), [], $request->attributes());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('billing_street'))->toBeTrue()
            ->and($validator->errors()->has('billing_city'))->toBeTrue()
            ->and($validator->errors()->has('billing_postal_code'))->toBeTrue()
            ->and($validator->errors()->has('billing_country'))->toBeTrue();
    });

    test('it does not require billing fields when billing same as shipping is true', function () {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+32470123456',
            'shipping_street' => 'Main St 1',
            'shipping_city' => 'Brussels',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'Belgium',
            'billing_same_as_shipping' => 1,
        ];

        $request = new StoreOrderRequest;
        $request->merge($data);

        $validator = Validator::make($data, $request->rules(), [], $request->attributes());

        expect($validator->passes())->toBeTrue();
    });

    test('it allows nullable notes', function () {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+32470123456',
            'shipping_street' => 'Main St 1',
            'shipping_city' => 'Brussels',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'Belgium',
            'billing_same_as_shipping' => 1,
            'notes' => null,
        ];

        $request = new StoreOrderRequest;
        $request->merge($data);

        $validator = Validator::make($data, $request->rules(), [], $request->attributes());

        expect($validator->passes())->toBeTrue();
    });
});

describe('attributes', function () {
    test('it has correct custom attributes', function () {
        $request = new StoreOrderRequest;

        expect($request->attributes())->toBe([
            'name' => __('Name'),
            'email' => __('Email address'),
            'phone' => __('Phone'),
            'shipping_street' => __('Street'),
            'shipping_city' => __('City'),
            'shipping_postal_code' => __('Postal code'),
            'shipping_country' => __('Country'),
            'billing_street' => __('Street'),
            'billing_city' => __('City'),
            'billing_postal_code' => __('Postal code'),
            'billing_country' => __('Country'),
            'notes' => __('Notes'),
        ]);
    });
});
