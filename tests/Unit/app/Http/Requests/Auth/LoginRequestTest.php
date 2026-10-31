<?php

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

test('it authorizes login requests', function () {
    $request = new LoginRequest;

    expect($request->authorize())->toBeTrue();
});

test('it validates valid login data', function () {
    $request = new LoginRequest;

    $validator = Validator::make([
        'email' => 'jane@example.com',
        'password' => 'password',
    ], $request->rules());

    expect($validator->passes())->toBeTrue();
});

test('it rejects missing or malformed login data', function () {
    $request = new LoginRequest;

    $validator = Validator::make([
        'email' => 'not-an-email',
    ], $request->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('email'))->toBeTrue()
        ->and($validator->errors()->has('password'))->toBeTrue();
});

test('it generates a normalized throttle key', function () {
    $request = LoginRequest::create('/', 'POST', [
        'email' => 'Jöhn.Doe@Example.com',
    ], [], [], [
        'REMOTE_ADDR' => '192.0.2.1',
    ]);

    expect($request->throttleKey())->toBe('john.doe@example.com|192.0.2.1');
});

test('it clears the rate limiter after successful authentication', function () {
    $request = LoginRequest::create('/', 'POST', [
        'email' => 'jane@example.com',
        'password' => 'password',
    ]);

    RateLimiter::shouldReceive('tooManyAttempts')->once()->with($request->throttleKey(), 5)->andReturnFalse();
    Auth::shouldReceive('attempt')->once()->with([
        'email' => 'jane@example.com',
        'password' => 'password',
    ], false)->andReturnTrue();
    RateLimiter::shouldReceive('clear')->once()->with($request->throttleKey());

    $request->authenticate();
});

test('it increments the rate limiter after failed authentication', function () {
    $request = LoginRequest::create('/', 'POST', [
        'email' => 'jane@example.com',
        'password' => 'wrong-password',
    ]);

    RateLimiter::shouldReceive('tooManyAttempts')->once()->with($request->throttleKey(), 5)->andReturnFalse();
    Auth::shouldReceive('attempt')->once()->with([
        'email' => 'jane@example.com',
        'password' => 'wrong-password',
    ], false)->andReturnFalse();
    RateLimiter::shouldReceive('hit')->once()->with($request->throttleKey());

    expect(fn () => $request->authenticate())->toThrow(ValidationException::class);
});

test('it dispatches a lockout event for too many attempts', function () {
    $request = LoginRequest::create('/', 'POST', [
        'email' => 'jane@example.com',
    ]);

    Event::fake([Lockout::class]);
    RateLimiter::shouldReceive('tooManyAttempts')->once()->with($request->throttleKey(), 5)->andReturnTrue();
    RateLimiter::shouldReceive('availableIn')->once()->with($request->throttleKey())->andReturn(120);

    expect(fn () => $request->ensureIsNotRateLimited())->toThrow(ValidationException::class);

    Event::assertDispatched(Lockout::class, fn (Lockout $event): bool => $event->request === $request);
});
