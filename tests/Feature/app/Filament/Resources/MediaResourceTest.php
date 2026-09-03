<?php

/** @noinspection PhpUndefinedMethodInspection, PhpParamsInspection */

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Resources\Media\Pages\EditMedia;
use App\Filament\Resources\Media\Pages\ListMedia;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'create media',
        'view media',
        'update media',
        'delete media',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $adminUser->assignRole($adminRole);

    $this->actingAs($adminUser);
});

function createMedia(array $attributes = []): Media
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

it('can list media', function () {
    $mediaRecords = collect([
        createMedia(['name' => 'Avatar One', 'file_name' => 'avatar-one.jpg']),
        createMedia(['name' => 'Avatar Two', 'file_name' => 'avatar-two.jpg']),
        createMedia(['name' => 'Avatar Three', 'file_name' => 'avatar-three.jpg']),
    ]);

    livewire(ListMedia::class)
        ->assertOk()
        ->assertCanSeeTableRecords($mediaRecords)
        ->assertCountTableRecords(3);
});

it('can search media by name', function () {
    createMedia(['name' => 'UniqueMediaName', 'file_name' => 'UniqueMediaName.jpg']);
    $otherMedia = createMedia(['name' => 'OtherMedia', 'file_name' => 'OtherMedia.jpg']);

    livewire(ListMedia::class)
        ->searchTable('UniqueMediaName')
        ->assertCanSeeTableRecords(Media::where('name', 'UniqueMediaName')->get())
        ->assertCanNotSeeTableRecords([$otherMedia]);
});

it('can edit media', function () {
    $user = User::factory()->create();
    $mediaRecords = collect([
        createMedia(['order_column' => 1, 'model_id' => $user->id]),
        createMedia(['order_column' => 2, 'model_id' => $user->id]),
        createMedia(['order_column' => 3, 'model_id' => $user->id]),
        createMedia(['order_column' => 4, 'model_id' => $user->id]),
        createMedia(['order_column' => 5, 'model_id' => $user->id]),
        createMedia(['order_column' => 6, 'model_id' => $user->id]),
        createMedia(['order_column' => 7, 'model_id' => $user->id]),
    ]);
    $media = $mediaRecords[0];

    livewire(EditMedia::class, ['record' => $media->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Avatar',
            'order_column' => 7,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Media::class, [
        'id' => $media->id,
        'name' => 'Updated Avatar',
        'order_column' => 7,
    ]);
});

it('can delete media from the edit page', function () {
    $media = createMedia();

    livewire(EditMedia::class, ['record' => $media->getRouteKey()])
        ->callAction(HardDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseMissing(Media::class, [
        'id' => $media->id,
    ]);
});

it('can bulk delete media', function () {
    $mediaRecords = collect([
        createMedia(['name' => 'Bulk One', 'file_name' => 'bulk-one.jpg']),
        createMedia(['name' => 'Bulk Two', 'file_name' => 'bulk-two.jpg']),
    ]);

    livewire(ListMedia::class)
        ->callTableBulkAction('hardDeleteBulk', $mediaRecords)
        ->assertNotified();

    foreach ($mediaRecords as $media) {
        $this->assertDatabaseMissing(Media::class, ['id' => $media->id]);
    }
});

it('denies bulk delete when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view media']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListMedia::class)
        ->assertTableBulkActionHidden('hardDeleteBulk');
});

it('denies listing media when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListMedia::class)
        ->assertForbidden();
});

it('denies loading edit media page when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view media']);

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $media = createMedia();

    livewire(EditMedia::class, ['record' => $media->getRouteKey()])
        ->assertForbidden();
});

it('denies deleting media when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view media', 'update media']);

    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $media = createMedia();

    livewire(EditMedia::class, ['record' => $media->getRouteKey()])
        ->assertActionHidden(HardDeleteAction::class);

    $this->assertDatabaseHas(Media::class, [
        'id' => $media->id,
    ]);
});

it('shows preview_url column for all images', function () {
    createMedia(['mime_type' => 'image/jpeg', 'file_name' => 'test.jpg']);
    createMedia(['mime_type' => 'image/gif', 'file_name' => 'test.gif']);

    livewire(ListMedia::class)
        ->assertTableColumnVisible('preview_url');
});
