<?php

use App\Filament\Filters\IsPublishedFilter;
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

it('has the correct default name', function () {
    $filter = IsPublishedFilter::make();

    expect($filter->getName())->toBe('isPublished');
});

it('has the correct label', function () {
    $filter = IsPublishedFilter::make();

    expect($filter->getLabel())->toBe(__('Published'));
});

it('exists in the posts table', function () {
    livewire(ListPosts::class)
        ->assertTableFilterExists('isPublished');
});

it('filters published records when enabled', function () {
    $publishedPost = Post::factory()->create(['published_at' => now()]);
    $draftPost = Post::factory()->create(['published_at' => null]);

    livewire(ListPosts::class)
        ->filterTable('isPublished')
        ->assertCanSeeTableRecords([$publishedPost])
        ->assertCanNotSeeTableRecords([$draftPost]);
});
