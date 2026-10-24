<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\View\Components\Layouts\App;
use Illuminate\Contracts\View\View;

describe('component', function () {
    test('it instantiates app component successfully', function () {
        expect(new App)->toBeInstanceOf(App::class);
    });

    test('it renders the correct view', function () {
        $view = (new App)->render();

        expect($view)->toBeInstanceOf(View::class)
            ->and($view->name())->toBe('components.layouts.app');
    });
});
