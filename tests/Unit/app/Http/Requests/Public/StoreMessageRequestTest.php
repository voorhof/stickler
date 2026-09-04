<?php

use App\Http\Requests\Public\StoreMessageRequest;
use Illuminate\Support\Facades\Validator;

test('it authorizes store message request', function () {
    $request = new StoreMessageRequest;

    expect($request->authorize())->toBeTrue();
});

test('it validates valid store message request data', function () {
    $request = new StoreMessageRequest;

    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '1234567890',
        'subject' => 'Hello',
        'message' => 'This is a test message.',
    ];

    $validator = Validator::make($data, $request->rules(), [], $request->attributes());

    expect($validator->passes())->toBeTrue();
});

test('it fails validation when required fields are missing', function () {
    $request = new StoreMessageRequest;

    $validator = Validator::make([], $request->rules(), [], $request->attributes());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue()
        ->and($validator->errors()->has('email'))->toBeTrue()
        ->and($validator->errors()->has('phone'))->toBeTrue()
        ->and($validator->errors()->has('subject'))->toBeTrue()
        ->and($validator->errors()->has('message'))->toBeTrue();
});

test('it validates email format', function () {
    $request = new StoreMessageRequest;

    $data = [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'subject' => 'Hello',
        'message' => 'This is a test message.',
    ];

    $validator = Validator::make($data, $request->rules(), [], $request->attributes());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('email'))->toBeTrue();
});

test('it validates max lengths', function () {
    $request = new StoreMessageRequest;

    $data = [
        'name' => str_repeat('a', 256),
        'email' => 'john@ex'.str_repeat('a', 256).'mple.com',
        'phone' => str_repeat('a', 256),
        'subject' => str_repeat('a', 256),
        'message' => str_repeat('a', 2551),
    ];

    $validator = Validator::make($data, $request->rules(), [], $request->attributes());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue()
        ->and($validator->errors()->has('email'))->toBeTrue()
        ->and($validator->errors()->has('phone'))->toBeTrue()
        ->and($validator->errors()->has('subject'))->toBeTrue()
        ->and($validator->errors()->has('message'))->toBeTrue();
});

test('it has correct custom attributes', function () {
    $request = new StoreMessageRequest;

    expect($request->attributes())->toBe([
        'name' => __('Name'),
        'email' => __('Email address'),
        'phone' => __('Phone'),
        'subject' => __('Subject'),
        'message' => __('Message'),
    ]);
});
