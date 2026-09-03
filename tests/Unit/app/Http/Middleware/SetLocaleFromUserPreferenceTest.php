<?php

/** @noinspection PhpUnusedParameterInspection */

use App\Http\Middleware\SetLocaleFromUserPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

test('it sets application locale when authenticated user has supported locale', function () {
    $user = User::factory()->create(['locale' => 'nl_BE']);
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    $middleware = new SetLocaleFromUserPreference;

    $originalLocale = app()->getLocale();

    $response = $middleware->handle($request, function (Request $req): Response {
        return response('OK');
    });

    expect(app()->getLocale())->toBe('nl_BE')
        ->and($response->getContent())->toBe('OK');

    app()->setLocale($originalLocale);
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
