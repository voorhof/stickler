<?php

use App\View\Components\Layouts\App;
use Illuminate\Contracts\View\View;

test('it instantiates app component successfully', function () {
    $component = new App;

    expect($component)->toBeInstanceOf(App::class);
});

test('it renders the correct view', function () {
    $component = new App;
    $view = $component->render();

    expect($view)->toBeInstanceOf(View::class)
        ->and($view->name())->toBe('components.layouts.app');
});
