<?php

use App\Filament\Actions\PublishAction;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('has the correct default name', function () {
    $action = PublishAction::make();
    expect($action->getName())->toBe('publish');
});

it('is hidden if the record is already publishd', function () {
    $action = PublishAction::make();
    $post = Post::factory()->create(['published_at' => now()]);

    // Set the record on the action
    $action->record($post);

    // The hidden() callback should evaluate to true
    expect($action->isHidden())->toBeTrue();
});

it('is not hidden if the record is not publishd', function () {
    $action = PublishAction::make();
    $post = Post::factory()->create(['published_at' => null]);

    // Set the record on the action
    $action->record($post);

    // The hidden() callback should evaluate to false
    expect($action->isHidden())->toBeFalse();
});
