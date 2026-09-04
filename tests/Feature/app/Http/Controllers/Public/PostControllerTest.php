<?php

use App\Http\Controllers\Public\PostController;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('post controller index method executes successfully', function () {
    $controller = new PostController;
    $response = $controller->index();

    expect($response)->toBeNull();
});

test('post controller show method executes successfully', function () {
    User::factory()->create();
    $post = Post::factory()->create();
    $controller = new PostController;
    $response = $controller->show($post);

    expect($response)->toBeNull();
});
