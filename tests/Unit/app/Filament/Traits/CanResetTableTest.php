<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Asmit\ResizedColumn\Models\TableSetting;
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
        'create posts',
        'view posts',
        'update posts',
        'delete posts',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('has the resetColumnWidths method on list pages', function () {
    expect(method_exists(ListPosts::class, 'resetColumnWidths'))->toBeTrue();
});

it('can reset column widths from the database', function () {
    Post::factory()->create();

    TableSetting::create([
        'user_id' => $this->adminUser->id,
        'resource' => Post::class,
        'styles' => ['title' => ['width' => '200']],
    ]);

    $this->assertDatabaseHas('table_settings', [
        'user_id' => $this->adminUser->id,
        'resource' => Post::class,
    ]);

    livewire(ListPosts::class)
        ->call('resetColumnWidths')
        ->assertNotified();

    $this->assertDatabaseMissing('table_settings', [
        'user_id' => $this->adminUser->id,
        'resource' => Post::class,
    ]);
});

it('clears the session column widths', function () {
    Post::factory()->create();

    session()->put('tables.post_columns_style', ['title' => ['width' => '200']]);

    expect(session()->has('tables.post_columns_style'))->toBeTrue();

    livewire(ListPosts::class)
        ->call('resetColumnWidths')
        ->assertNotified();

    expect(session()->has('tables.post_columns_style'))->toBeFalse();
});

it('does not affect other users column settings', function () {
    Post::factory()->create();

    $otherUser = User::factory()->create();

    TableSetting::create([
        'user_id' => $this->adminUser->id,
        'resource' => Post::class,
        'styles' => ['title' => ['width' => '200']],
    ]);

    TableSetting::create([
        'user_id' => $otherUser->id,
        'resource' => Post::class,
        'styles' => ['title' => ['width' => '300']],
    ]);

    livewire(ListPosts::class)
        ->call('resetColumnWidths')
        ->assertNotified();

    $this->assertDatabaseMissing('table_settings', [
        'user_id' => $this->adminUser->id,
        'resource' => Post::class,
    ]);

    $this->assertDatabaseHas('table_settings', [
        'user_id' => $otherUser->id,
        'resource' => Post::class,
    ]);
});

it('resets the table column manager', function () {
    Post::factory()->create();

    session()->put('tables.'.md5(ListPosts::class).'_columns', [
        ['type' => 'column', 'name' => 'title', 'label' => 'Title', 'isHidden' => true, 'isToggled' => false, 'isToggleable' => true, 'isToggledHiddenByDefault' => true],
    ]);

    livewire(ListPosts::class)
        ->call('resetColumnWidths')
        ->assertNotified();

    expect(session()->get('tables.'.md5(ListPosts::class).'_columns'))
        ->not->toBe([
            ['type' => 'column', 'name' => 'title', 'label' => 'Title', 'isHidden' => true, 'isToggled' => false, 'isToggleable' => true, 'isToggledHiddenByDefault' => true],
        ]);
});
