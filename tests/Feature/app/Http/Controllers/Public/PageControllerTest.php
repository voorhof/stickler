<?php

use App\Http\Controllers\Public\PageController;
use App\Models\Page;
use App\Models\User;

test('page controller index method executes successfully', function () {
    $controller = new PageController;
    $response = $controller->index();

    expect($response)->toBeNull();
});

test('page controller show method executes successfully', function () {
    User::factory()->create();
    $page = Page::factory()->create();
    $controller = new PageController;
    $response = $controller->show($page);

    expect($response)->toBeNull();
});
