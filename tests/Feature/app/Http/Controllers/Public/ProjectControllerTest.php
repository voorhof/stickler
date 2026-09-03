<?php

use App\Http\Controllers\Public\ProjectController;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('project controller index method executes successfully', function () {
    $controller = new ProjectController;
    $response = $controller->index();

    expect($response)->toBeNull();
});

test('project controller show method executes successfully', function () {
    User::factory()->create();
    $project = Project::factory()->create();
    $controller = new ProjectController;
    $response = $controller->show($project);

    expect($response)->toBeNull();
});
