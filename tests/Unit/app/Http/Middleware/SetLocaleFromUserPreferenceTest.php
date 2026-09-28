<?php

/** @noinspection PhpUnusedParameterInspection, PhpUnhandledExceptionInspection */

use App\Http\Middleware\SetLocaleFromUserPreference;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

test('it sets application locale to nl when user has nl_BE locale', function () {
    $user = User::factory()->create(['locale' => 'nl_BE']);
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    $middleware = new SetLocaleFromUserPreference;

    $originalLocale = app()->getLocale();
    $originalCarbonLocale = Carbon::getLocale();

    $response = $middleware->handle($request, function (Request $req): Response {
        return response('OK');
    });

    expect(app()->getLocale())->toBe('nl')
        ->and(Carbon::getLocale())->toBe('nl_BE')
        ->and($response->getContent())->toBe('OK');

    app()->setLocale($originalLocale);
    Carbon::setLocale($originalCarbonLocale);
});

test('it sets application locale to nl when user has nl_NL locale', function () {
    $user = User::factory()->create(['locale' => 'nl_NL']);
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    $middleware = new SetLocaleFromUserPreference;

    $originalLocale = app()->getLocale();
    $originalCarbonLocale = Carbon::getLocale();

    $response = $middleware->handle($request, function (Request $req): Response {
        return response('OK');
    });

    expect(app()->getLocale())->toBe('nl')
        ->and(Carbon::getLocale())->toBe('nl_NL')
        ->and($response->getContent())->toBe('OK');

    app()->setLocale($originalLocale);
    Carbon::setLocale($originalCarbonLocale);
});

test('it sets application locale to en when user has en_US locale', function () {
    $user = User::factory()->create(['locale' => 'en_US']);
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    $middleware = new SetLocaleFromUserPreference;

    $originalLocale = app()->getLocale();
    $originalCarbonLocale = Carbon::getLocale();

    $response = $middleware->handle($request, function (Request $req): Response {
        return response('OK');
    });

    expect(app()->getLocale())->toBe('en')
        ->and(Carbon::getLocale())->toBe('en_US')
        ->and($response->getContent())->toBe('OK');

    app()->setLocale($originalLocale);
    Carbon::setLocale($originalCarbonLocale);
});

test('it sets application locale to en when user has en_UK locale', function () {
    $user = User::factory()->create(['locale' => 'en_UK']);
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    $middleware = new SetLocaleFromUserPreference;

    $originalLocale = app()->getLocale();
    $originalCarbonLocale = Carbon::getLocale();

    $response = $middleware->handle($request, function (Request $req): Response {
        return response('OK');
    });

    expect(app()->getLocale())->toBe('en')
        ->and($response->getContent())->toBe('OK');

    app()->setLocale($originalLocale);
    Carbon::setLocale($originalCarbonLocale);
});

test('it sets application locale to en when user has en_GB locale', function () {
    $user = User::factory()->create(['locale' => 'en_GB']);
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    $middleware = new SetLocaleFromUserPreference;

    $originalLocale = app()->getLocale();
    $originalCarbonLocale = Carbon::getLocale();

    $response = $middleware->handle($request, function (Request $req): Response {
        return response('OK');
    });

    expect(app()->getLocale())->toBe('en')
        ->and(Carbon::getLocale())->toBe('en_GB')
        ->and($response->getContent())->toBe('OK');

    app()->setLocale($originalLocale);
    Carbon::setLocale($originalCarbonLocale);
});

test('it does not set application locale when authenticated user has unsupported locale', function () {
    $user = User::factory()->create(['locale' => 'fr_FR']);
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    $middleware = new SetLocaleFromUserPreference;

    $originalLocale = app()->getLocale();

    $response = $middleware->handle($request, function (Request $req): Response {
        return response('OK');
    });

    expect(app()->getLocale())->toBe($originalLocale)
        ->and($response->getContent())->toBe('OK');
});

test('it does not set application locale when unauthenticated', function () {
    $request = Request::create('/');

    $middleware = new SetLocaleFromUserPreference;

    $originalLocale = app()->getLocale();

    $response = $middleware->handle($request, function (Request $req): Response {
        return response('OK');
    });

    expect(app()->getLocale())->toBe($originalLocale)
        ->and($response->getContent())->toBe('OK');
});

test('it does not set application locale when authenticated user has null locale', function () {
    $user = User::factory()->make(['locale' => null]);
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    $middleware = new SetLocaleFromUserPreference;

    $originalLocale = app()->getLocale();

    $response = $middleware->handle($request, function (Request $req): Response {
        return response('OK');
    });

    expect(app()->getLocale())->toBe($originalLocale)
        ->and($response->getContent())->toBe('OK');
});
