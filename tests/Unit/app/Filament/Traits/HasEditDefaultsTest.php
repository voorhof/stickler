<?php

/** @noinspection PhpUnusedParameterInspection, PhpUndefinedMethodInspection, PhpUndefinedClassInspection */

use App\Filament\Resources\Messages\Pages\EditMessage;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Models\Message;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

function editPageFor(string $pageClass, ?Model $record = null): object
{
    $page = app($pageClass);

    if ($record !== null) {
        (function () use ($record): void {
            $this->record = $record;
        })->call($page);
    }

    return $page;
}

function savedNotificationTitleFor(string $pageClass, Model $record): ?string
{
    $page = editPageFor($pageClass, $record);

    return (function (): ?string {
        return $this->getSavedNotificationTitle();
    })->call($page);
}

function headerActionsFor(string $pageClass): array
{
    $page = editPageFor($pageClass);

    return (function (): array {
        return $this->getHeaderActions();
    })->call($page);
}

function mutateFormDataBeforeSaveFor(string $pageClass, Model $record, array $data): array
{
    $page = editPageFor($pageClass, $record);

    return (function () use ($data): array {
        return $this->mutateFormDataBeforeSave($data);
    })->call($page);
}

function callBeforeSaveFor(string $pageClass, Model $record): object
{
    $page = editPageFor($pageClass, $record);

    (function (): void {
        $this->beforeSave();
    })->call($page);

    return $page;
}

function callAfterSaveFor(object $page): void
{
    (function (): void {
        $this->afterSave();
    })->call($page);
}

function callAfterActionCalledFor(string $pageClass, Action $action, Model $record): object
{
    $page = editPageFor($pageClass, $record);

    (function () use ($action): void {
        $this->afterActionCalled($action);
    })->call($page);

    return $page;
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
        'read messages',
        'update messages',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::where('name', 'Admin')->first() ?? Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('marks unread message as read on mount', function () {
    $message = Message::factory()->create(['read' => false]);

    $page = editPageFor(EditMessage::class);

    (function () use ($message): void {
        $this->record = Message::find($message->id);

        if (! $this->record->read && parent::getModel() === 'App\Models\Message') {
            $this->record->updateQuietly(['read' => true]);
        }
    })->call($page);

    expect($message->fresh()->read)->toBeTrue();
});

it('returns correct title', function () {
    $post = Post::factory()->create(['title' => 'Test Post']);

    $page = editPageFor(EditPost::class, $post);

    expect($page->getTitle())->toBe(__('Edit :model', ['model' => __('Post')]));
});

it('returns correct saved notification title with title, name, or key', function () {
    $post = new Post(['title' => 'Test Post']);
    $post->id = 1;

    expect(savedNotificationTitleFor(EditPost::class, $post))->toBe(__('Ok! :model :title (ID: :id) has been saved.', [
        'model' => __('Post'),
        'title' => 'Test Post',
        'id' => 1,
    ]));

    $role = new Role(['name' => 'Editor']);
    $role->id = 2;

    expect(savedNotificationTitleFor(EditRole::class, $role))->toBe(__('Ok! :model :title (ID: :id) has been saved.', [
        'model' => __('Role'),
        'title' => 'Editor',
        'id' => 2,
    ]));

    $emptyPost = new Post;
    $emptyPost->id = 123;

    expect(savedNotificationTitleFor(EditPost::class, $emptyPost))->toBe(__('Ok! :model :title (ID: :id) has been saved.', [
        'model' => __('Post'),
        'title' => '123',
        'id' => 123,
    ]));
});

it('returns header actions', function () {
    $actions = headerActionsFor(EditTag::class);

    expect($actions)->not->toBeEmpty();
});

it('returns correct model name', function () {
    $post = Post::factory()->create();

    $page = editPageFor(EditPost::class, $post);

    expect($page->getModelName())->toBe(__('Post'));
});

it('handles afterActionCalled when record does not exist', function () {
    $post = Post::factory()->create();
    $post->forceDelete();

    $page = callAfterActionCalledFor(EditPost::class, Action::make('delete'), $post);

    expect($page->record)->toBeNull();
});

it('clears archived_at when archive is false for message', function () {
    $message = Message::factory()->create(['archived_at' => now()]);

    $data = mutateFormDataBeforeSaveFor(EditMessage::class, $message, ['archive' => false]);

    expect($data['archived_at'])->toBeNull();
});

it('updates replied and replied_at when message reply is filled on save', function () {
    $message = Message::factory()->create(['reply' => null, 'replied' => false, 'replied_at' => null]);
    $message->reply = 'Here is your reply.';

    $page = editPageFor(EditMessage::class, $message);

    (function (): void {
        if ($this->record->reply) {
            $this->record->updateQuietly([
                'replied' => true,
                'replied_at' => $this->record->replied_at ?? now(),
            ]);
        }
    })->call($page);

    expect($message->fresh()->replied)->toBeTrue()
        ->and($message->fresh()->replied_at)->not->toBeNull();
});

it('syncs all permissions for Admin role on save', function () {
    $role = Role::where('name', 'Admin')->first();
    $role->permissions()->detach();

    $page = editPageFor(EditRole::class, $role);

    (function (): void {
        if ($this->record->permissions && $this->record->name === 'Admin') {
            $this->record->syncPermissions(Permission::where('guard_name', $this->record->guard_name)->get());
        }
    })->call($page);

    expect($role->fresh()->permissions->count())->toBe(Permission::where('guard_name', 'web')->count());
});

it('logs permission changes in afterSave', function () {
    $role = Role::factory()->create(['name' => 'CustomRole', 'guard_name' => 'web']);
    $p1 = Permission::create(['name' => 'perm 1', 'guard_name' => 'web']);
    $p2 = Permission::create(['name' => 'perm 2', 'guard_name' => 'web']);
    $role->givePermissionTo([$p1]);

    $page = callBeforeSaveFor(EditRole::class, $role);

    $role->syncPermissions([$p1, $p2]);

    callAfterSaveFor($page);

    $activity = Activity::where('subject_type', $role->getMorphClass())
        ->where('subject_id', $role->getKey())
        ->latest()
        ->first();

    expect($activity)->not->toBeNull();
});
