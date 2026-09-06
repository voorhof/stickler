<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

function createdNotificationTitleFor(string $pageClass, Model $record): ?string
{
    $page = app($pageClass);

    return (function () use ($record): ?string {
        $this->record = $record;

        return $this->getCreatedNotificationTitle();
    })->call($page);
}

function callAfterCreateFor(string $pageClass, Model $record): void
{
    $page = app($pageClass);

    (function () use ($record): void {
        $this->record = $record;

        $this->afterCreate();
    })->call($page);
}

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'create users',
        'view users',
        'create roles',
        'view roles',
        'create posts',
        'view posts',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('returns correct created notification title with title', function () {
    $post = new Post(['title' => 'Test Post Title']);
    $post->id = 1;

    expect(createdNotificationTitleFor(CreatePost::class, $post))->toBe(__('Woohoo! :model :title (ID: :id) has been successfully created!', [
        'model' => __('Post'),
        'title' => 'Test Post Title',
        'id' => 1,
    ]));
});

it('returns correct created notification title with name when title is absent', function () {
    $role = new Role(['name' => 'Editor']);
    $role->id = 2;

    expect(createdNotificationTitleFor(CreateRole::class, $role))->toBe(__('Woohoo! :model :title (ID: :id) has been successfully created!', [
        'model' => __('Role'),
        'title' => 'Editor',
        'id' => 2,
    ]));
});

it('returns correct created notification title with key when title and name are absent', function () {
    $post = new Post;
    $post->id = 123;

    expect(createdNotificationTitleFor(CreatePost::class, $post))->toBe(__('Woohoo! :model :title (ID: :id) has been successfully created!', [
        'model' => __('Post'),
        'title' => '123',
        'id' => 123,
    ]));
});

it('syncs all permissions when running afterCreate for an Admin role', function () {
    $role = Role::where('name', 'Admin')->first();
    $role->permissions()->detach();
    expect($role->permissions->count())->toBe(0);

    callAfterCreateFor(CreateRole::class, $role);

    expect($role->fresh()->permissions->count())->toBe(Permission::where('guard_name', 'web')->count());
});

it('does not sync all permissions when running afterCreate for a non-Admin role', function () {
    $role = Role::factory()->create(['name' => 'Moderator', 'guard_name' => 'web']);
    expect($role->permissions->count())->toBe(0);

    callAfterCreateFor(CreateRole::class, $role);

    expect($role->fresh()->permissions->count())->toBe(0);
});

it('sets email_verified_at when running afterCreate for a user', function () {
    $user = User::factory()->create(['email_verified_at' => null]);
    expect($user->email_verified_at)->toBeNull();

    callAfterCreateFor(CreateUser::class, $user);

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

it('logs activity with properties on afterCreate', function () {
    $post = Post::factory()->create();

    callAfterCreateFor(CreatePost::class, $post);

    $activity = Activity::where('subject_type', $post->getMorphClass())
        ->where('subject_id', $post->getKey())
        ->latest()
        ->first();

    expect($activity)->not->toBeNull();
});
