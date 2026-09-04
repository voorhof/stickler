<?php

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
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('preserves updated_at when reordering posts', function () {
    $post1 = Post::factory()->create(['updated_at' => now()->subDay()]);
    $post2 = Post::factory()->create(['updated_at' => now()->subDay()]);

    $b1UpdatedAt = $post1->updated_at;
    $b2UpdatedAt = $post2->updated_at;

    $order = [$post2->id, $post1->id];

    livewire(ListPosts::class)
        ->call('reorderTable', $order);

    expect($post1->fresh()->updated_at->toDateTimeString())->toBe($b1UpdatedAt->toDateTimeString())
        ->and($post2->fresh()->updated_at->toDateTimeString())->toBe($b2UpdatedAt->toDateTimeString());
});
