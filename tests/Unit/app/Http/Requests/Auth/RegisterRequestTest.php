<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

test('it authorizes register requests', function () {
    $request = new RegisterRequest;

    expect($request->authorize())->toBeTrue();
});

test('it validates valid registration data', function () {
    $request = new RegisterRequest;

    $validator = Validator::make([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ], $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('it rejects missing or malformed registration data', function () {
    $request = new RegisterRequest;

    $validator = Validator::make([
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
        'terms' => false,
    ], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue()
        ->and($validator->errors()->has('email'))->toBeTrue()
        ->and($validator->errors()->has('password'))->toBeTrue()
        ->and($validator->errors()->has('terms'))->toBeTrue();
});

test('it rejects duplicate email addresses', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    $request = new RegisterRequest;

    $validator = Validator::make([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => true,
    ], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('email'))->toBeTrue();
});
