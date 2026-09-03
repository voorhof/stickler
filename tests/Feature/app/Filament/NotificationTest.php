<?php

/** @noinspection PhpUnhandledExceptionInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Resources\Media\Pages\EditMedia;
use App\Filament\Resources\Media\Pages\ListMedia;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'create users',
        'view users',
        'update users',
        'delete users',
        'create roles',
        'view roles',
        'update roles',
        'delete roles',
        'create media',
        'view media',
        'update media',
        'delete media',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

function latestNotificationFor(User $user): object
{
    $notification = DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->where('notifiable_id', $user->id)
        ->latest()
        ->first();

    expect($notification)->not->toBeNull();

    return $notification;
}

/**
 * @throws JsonException
 */
function notificationPayload(object $notification): array
{
    return json_decode($notification->data, true, flags: JSON_THROW_ON_ERROR);
}

function notificationPayloadsFor(User $user): array
{
    return DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->where('notifiable_id', $user->id)
        ->get()
        ->map(fn (object $notification): array => notificationPayload($notification))
        ->all();
}

function createMediaRecord(array $attributes = []): Media
{
    $user = User::factory()->create();

    return Media::query()->create(array_merge([
        'model_type' => User::class,
        'model_id' => $user->id,
        'uuid' => (string) str()->uuid(),
        'collection_name' => 'avatar',
        'name' => 'Profile Avatar',
        'file_name' => 'profile-avatar.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'public',
        'conversions_disk' => 'public',
        'size' => 1024,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
        'order_column' => 1,
    ], $attributes));
}

test('it stores a database notification when a user is created in filament', function () {
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'User',
            'email' => 'notify.user@example.com',
            'password' => 'password',
        ])
        ->call('create')
        ->assertNotified();

    $createdUser = User::query()->where('email', 'notify.user@example.com')->firstOrFail();
    $payload = notificationPayload(latestNotificationFor($this->adminUser));

    expect($payload['title'] ?? null)
        ->toBe(__('Woohoo! :model :title (ID: :id) has been successfully created!', ['model' => __('User'), 'title' => $createdUser->name, 'id' => $createdUser->id]));
});

test('it stores a database notification when a role is saved in filament', function () {
    $role = Role::factory()->create(['name' => 'Manager']);

    livewire(EditRole::class, ['record' => $role->getRouteKey()])
        ->fillForm([
            'name' => 'Coach',
        ])
        ->call('save')
        ->assertNotified();

    $role->refresh();
    $payload = notificationPayload(latestNotificationFor($this->adminUser));

    expect($payload['title'] ?? null)
        ->toBe(__('Ok! :model :title (ID: :id) has been saved.', ['model' => __('Role'), 'title' => $role->name, 'id' => $role->id]));
});

test('it stores a database notification when media is deleted in filament', function () {
    $media = createMediaRecord();

    livewire(EditMedia::class, ['record' => $media->getRouteKey()])
        ->callAction(HardDeleteAction::class)
        ->assertNotified();

    $payload = notificationPayload(latestNotificationFor($this->adminUser));

    expect($payload)
        ->toHaveKey('title', __('Ooh! :model was permanently purged!', ['model' => __('Media')]))
        ->toHaveKey('status', 'danger');
});

test('it stores database notifications for user, role and media bulk deletions in filament', function () {
    $users = User::factory()->count(2)->create();
    $roles = Role::factory()->count(2)->create();
    $media = collect([
        createMediaRecord(['name' => 'Bulk Media 1', 'file_name' => 'bulk-media-1.jpg']),
        createMediaRecord(['name' => 'Bulk Media 2', 'file_name' => 'bulk-media-2.jpg']),
    ]);

    livewire(ListUsers::class)
        ->callTableBulkAction('softDeleteBulk', $users)
        ->assertNotified();

    $payloads = notificationPayloadsFor($this->adminUser);
    expect(collect($payloads)->contains(fn (array $payload): bool => ($payload['title'] ?? null) === __(':count :model items deleted', ['count' => 2, 'model' => __('User')]) && ($payload['status'] ?? null) === 'warning'))
        ->toBeTrue();

    livewire(ListRoles::class)
        ->callTableBulkAction('softDeleteBulk', $roles)
        ->assertNotified();

    $payloads = notificationPayloadsFor($this->adminUser);
    expect(collect($payloads)->contains(fn (array $payload): bool => ($payload['title'] ?? null) === __(':count :model items deleted', ['count' => 2, 'model' => __('Role')]) && ($payload['status'] ?? null) === 'warning'))
        ->toBeTrue();

    livewire(ListMedia::class)
        ->callTableBulkAction('hardDeleteBulk', $media)
        ->assertNotified();

    $payloads = notificationPayloadsFor($this->adminUser);
    expect(collect($payloads)->contains(fn (array $payload): bool => ($payload['title'] ?? null) === __(':count :model items permanently deleted', ['count' => 2, 'model' => __('Media')]) && ($payload['status'] ?? null) === 'warning'))
        ->toBeTrue();
});

test('it can create a database notification with the expected title payload', function () {
    $user = User::factory()->create();

    Notification::make()
        ->title('Unit test notification')
        ->success()
        ->sendToDatabase($user, isEventDispatched: true);

    $notification = DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->where('notifiable_id', $user->id)
        ->latest()
        ->first();

    expect($notification)->not->toBeNull();

    $payload = json_decode($notification->data, true, flags: JSON_THROW_ON_ERROR);

    expect($payload)
        ->toBeArray()
        ->toHaveKey('title', 'Unit test notification');
});

test('it can create a warning database notification', function () {
    $user = User::factory()->create();

    Notification::make()
        ->title('Warning notification')
        ->warning()
        ->sendToDatabase($user, isEventDispatched: true);

    $notification = DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->where('notifiable_id', $user->id)
        ->latest()
        ->first();

    expect($notification)->not->toBeNull();

    $payload = json_decode($notification->data, true, flags: JSON_THROW_ON_ERROR);

    expect($payload)
        ->toBeArray()
        ->toHaveKey('status', 'warning')
        ->toHaveKey('title', 'Warning notification');
});
