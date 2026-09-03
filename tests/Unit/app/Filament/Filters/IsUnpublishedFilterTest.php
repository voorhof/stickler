<?php

use App\Filament\Filters\IsUnpublishedFilter;
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
    $filter = IsUnpublishedFilter::make();

    expect($filter->getName())->toBe('isUnpublished');
});

it('has the correct label', function () {
    $filter = IsUnpublishedFilter::make();

    expect($filter->getLabel())->toBe(__('Not published'));
});

it('exists in the posts table', function () {
    livewire(ListPosts::class)
        ->assertTableFilterExists('isUnpublished');
});

it('filters unpublished records when enabled', function () {
    $unpublishedPost = Post::factory()->create(['published_at' => null]);
    $publishedPost = Post::factory()->create(['published_at' => now()]);

    livewire(ListPosts::class)
        ->filterTable('isUnpublished')
        ->assertCanSeeTableRecords([$unpublishedPost])
        ->assertCanNotSeeTableRecords([$publishedPost]);
});
