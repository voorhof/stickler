<?php

/** @noinspection PhpParamsInspection, PhpUndefinedMethodInspection */

use App\Filament\Actions\UnpublishBulkAction;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'view posts',
        'update posts',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('has the correct default name', function () {
    $action = UnpublishBulkAction::make();

    expect($action->getName())->toBe('unpublishBulk');
});

it('unpublishes selected posts by setting published_at to null', function () {
    $posts = Post::factory()->count(2)->create(['published_at' => now()]);

    livewire(ListPosts::class)
        ->callTableBulkAction('unpublishBulk', $posts->modelKeys())
        ->assertNotified();

    foreach ($posts as $post) {
        $post->refresh();

        expect($post->published_at)->toBeNull();
    }
});
