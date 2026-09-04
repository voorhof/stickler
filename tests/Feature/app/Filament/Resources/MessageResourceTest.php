<?php

/** @noinspection PhpParamsInspection, PhpUndefinedMethodInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Resources\Messages\Pages\EditMessage;
use App\Filament\Resources\Messages\Pages\ListMessages;
use App\Models\Message;
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

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'view messages',
        'update messages',
        'delete messages',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('can list messages', function () {
    $messages = Message::factory()->count(3)->create();

    livewire(ListMessages::class)
        ->assertOk()
        ->assertCanSeeTableRecords($messages)
        ->assertCountTableRecords(3);
});

it('can reorder messages', function () {
    $message1 = Message::factory()->create(['order_column' => 1]);
    $message2 = Message::factory()->create(['order_column' => 2]);

    livewire(ListMessages::class)
        ->call('reorderTable', [$message2->id, $message1->id]);

    expect($message1->fresh()->order_column)->toBe(2)
        ->and($message2->fresh()->order_column)->toBe(1);
});

it('shows only active messages by default in the list', function () {
    $activeMessage = Message::factory()->create(['archived_at' => null]);
    $archivedMessage = Message::factory()->create(['archived_at' => now()]);

    livewire(ListMessages::class)
        ->assertCanSeeTableRecords([$activeMessage])
        ->assertCanNotSeeTableRecords([$archivedMessage]);
});

it('can search messages by subject', function () {
    Message::factory()->create(['subject' => 'Unique Message Title', 'slug' => 'unique-message-subject']);
    $otherMessage = Message::factory()->create(['subject' => 'Something Else', 'slug' => 'something-else']);

    livewire(ListMessages::class)
        ->searchTable('Unique Message Title')
        ->assertCanSeeTableRecords(Message::where('subject', 'Unique Message Title')->get())
        ->assertCanNotSeeTableRecords([$otherMessage]);
});

it('shows the indicator badge when isArchived filter is active', function () {
    livewire(ListMessages::class)
        ->assertSee(__('Hide archived :model', ['model' => __('Messages')]));
});

it('can filter messages by trashed state on the list page table', function () {
    $activeMessage = Message::factory()->create();
    $trashedMessage = Message::factory()->softDeleted()->create();

    livewire(ListMessages::class)
        ->filterTable('trashed', false)
        ->assertCanSeeTableRecords([$trashedMessage])
        ->assertCanNotSeeTableRecords([$activeMessage])
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords([$activeMessage, $trashedMessage]);
});

it('marks the message as view when the edit page is opened', function () {
    $message = Message::factory()->create(['read' => false]);

    livewire(EditMessage::class, ['record' => $message->getRouteKey()])
        ->assertOk();

    expect($message->refresh()->read)->toBeTrue();
});

it('validates reply max length when editing a message', function () {
    $message = Message::factory()->create();

    livewire(EditMessage::class, ['record' => $message->getRouteKey()])
        ->fillForm([
            'reply' => str_repeat('a', 1001),
        ])
        ->call('save')
        ->assertHasFormErrors(['reply' => 'max'])
        ->assertNotNotified();
});

it('can edit a message reply and marks it as replied', function () {
    $message = Message::factory()->create([
        'replied' => false,
        'reply' => null,
        'replied_at' => null,
    ]);

    livewire(EditMessage::class, ['record' => $message->getRouteKey()])
        ->fillForm([
            'reply' => 'This is the admin reply.',
            'slug' => 'updated-slug',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $fresh = $message->refresh();
    expect($fresh->reply)->toBe('This is the admin reply.')
        ->and($fresh->replied)->toBeTrue()
        ->and($fresh->replied_at)->not->toBeNull()
        ->and($fresh->slug)->toBe('updated-slug');
});

it('turns the archive toggle on by default when editing an archived message', function () {
    $message = Message::factory()->archived()->create();

    livewire(EditMessage::class, ['record' => $message->getRouteKey()])
        ->assertSchemaStateSet(['archive' => true]);
});

it('leaves the archive toggle off when editing a message that is not archived', function () {
    $message = Message::factory()->create(['archived_at' => null]);

    livewire(EditMessage::class, ['record' => $message->getRouteKey()])
        ->assertSchemaStateSet(['archive' => false]);
});

it('can archive a message', function () {
    $message = Message::factory()->create();

    livewire(EditMessage::class, ['record' => $message->slug])
        ->callAction('archive')
        ->assertNotified();

    $message->refresh();
    expect($message->archived_at)->not->toBeNull();
});

it('can dearchive a message', function () {
    $message = Message::factory()->archived()->create();

    livewire(EditMessage::class, ['record' => $message->slug])
        ->callAction('dearchive')
        ->assertNotified();

    $message->refresh();
    expect($message->archived_at)->toBeNull();
});

it('can soft delete a message', function () {
    $message = Message::factory()->create();

    livewire(EditMessage::class, ['record' => $message->getRouteKey()])
        ->callAction(SoftDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertSoftDeleted(Message::class, ['id' => $message->id]);
});

it('can force delete a soft-deleted message', function () {
    $message = Message::factory()->softDeleted()->create();

    livewire(EditMessage::class, ['record' => $message->getRouteKey()])
        ->callAction(HardDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseMissing(Message::class, ['id' => $message->id]);
});

it('can restore a soft-deleted message', function () {
    $message = Message::factory()->softDeleted()->create();

    livewire(EditMessage::class, ['record' => $message->getRouteKey()])
        ->callAction(RestoreDeletedAction::class)
        ->assertNotified();

    $this->assertNotSoftDeleted(Message::class, ['id' => $message->id]);
});

it('can bulk archive messages', function () {
    $messages = Message::factory(2)->create();

    livewire(ListMessages::class)
        ->callTableBulkAction('archiveBulk', $messages)
        ->assertTableBulkActionVisible('archiveBulk');
});

it('can bulk dearchive messages', function () {
    $messages = Message::factory(2)->archived()->create();

    livewire(ListMessages::class)
        ->removeTableFilter('isArchived')
        ->callTableBulkAction('dearchiveBulk', $messages)
        ->assertTableBulkActionVisible('dearchiveBulk');
});

it('does not bulk re-archive messages that are already archived', function () {
    $archivedAt = now()->subDay();

    $messages = Message::factory(2)->archived()->create(['archived_at' => $archivedAt]);

    livewire(ListMessages::class)
        ->callTableBulkAction('archiveBulk', $messages);

    foreach ($messages as $message) {
        expect($message->refresh()->archived_at->toDateTimeString())
            ->toBe($archivedAt->toDateTimeString());
    }
});

it('does not bulk dearchive messages that are not archived', function () {
    $messages = Message::factory(2)->create(['archived_at' => null]);

    livewire(ListMessages::class)
        ->removeTableFilter('isArchived')
        ->callTableBulkAction('dearchiveBulk', $messages);

    foreach ($messages as $message) {
        expect($message->refresh()->archived_at)->toBeNull();
    }
});

it('bulk archives only unarchived messages in a mixed selection', function () {
    $unarchivedMessage = Message::factory()->create();
    $archivedAt = now()->subDay();
    $archivedMessage = Message::factory()->create(['archived_at' => $archivedAt]);

    livewire(ListMessages::class)
        ->callTableBulkAction('archiveBulk', collect([$unarchivedMessage, $archivedMessage]));

    expect($unarchivedMessage->refresh()->archived_at)->not->toBeNull()
        ->and($archivedMessage->refresh()->archived_at->toDateTimeString())->toBe($archivedAt->toDateTimeString());
});

it('bulk dearchives only archived messages in a mixed selection', function () {
    $unarchivedMessage = Message::factory()->create(['archived_at' => null]);
    $archivedAt = now()->subDay();
    $archivedMessage = Message::factory()->create(['archived_at' => $archivedAt]);

    livewire(ListMessages::class)
        ->removeTableFilter('isArchived')
        ->callTableBulkAction('dearchiveBulk', collect([$unarchivedMessage, $archivedMessage]));

    expect($unarchivedMessage->refresh()->archived_at)->toBeNull()
        ->and($archivedMessage->refresh()->archived_at)->toBeNull();
});

it('denies bulk archive action when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view messages']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    Message::factory(2)->create();

    livewire(ListMessages::class)
        ->assertTableBulkActionHidden('archiveBulk');
});

it('denies bulk dearchive action when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view messages']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    Message::factory(2)->archived()->create();

    livewire(ListMessages::class)
        ->removeTableFilter('isArchived')
        ->assertTableBulkActionHidden('archiveBulk');
});

it('can bulk delete messages', function () {
    $messages = Message::factory()->count(2)->create();

    livewire(ListMessages::class)
        ->callTableBulkAction('softDeleteBulk', $messages)
        ->assertNotified();

    foreach ($messages as $message) {
        $this->assertSoftDeleted(Message::class, ['id' => $message->id]);
    }
});

it('can bulk force delete soft-deleted messages', function () {
    $messages = Message::factory()->count(2)->softDeleted()->create();

    livewire(ListMessages::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('hardDeleteBulk', $messages)
        ->assertNotified();

    foreach ($messages as $message) {
        $this->assertDatabaseMissing(Message::class, ['id' => $message->id]);
    }
});

it('can bulk restore soft-deleted messages', function () {
    $messages = Message::factory()->count(2)->softDeleted()->create();

    livewire(ListMessages::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('restoreDeletedBulk', $messages)
        ->assertNotified();

    foreach ($messages as $message) {
        $this->assertNotSoftDeleted(Message::class, ['id' => $message->id]);
    }
});

it('denies destructive bulk actions when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view messages']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $activeMessage = Message::factory()->create();
    $trashedMessage = Message::factory()->softDeleted()->create();

    livewire(ListMessages::class)
        ->assertTableBulkActionHidden('softDeleteBulk');

    livewire(ListMessages::class)
        ->filterTable('trashed', false)
        ->assertTableBulkActionHidden('restoreDeletedBulk')
        ->assertTableBulkActionHidden('hardDeleteBulk');

    $this->assertDatabaseHas(Message::class, ['id' => $activeMessage->id, 'deleted_at' => null]);
    $this->assertSoftDeleted(Message::class, ['id' => $trashedMessage->id]);
});

it('denies listing messages when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListMessages::class)
        ->assertForbidden();
});

it('denies loading edit message page when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view messages']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $message = Message::factory()->create();

    livewire(EditMessage::class, ['record' => $message->getRouteKey()])
        ->assertForbidden();
});

it('denies deleting a message when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view messages', 'update messages']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $message = Message::factory()->create();

    livewire(EditMessage::class, ['record' => $message->getRouteKey()])
        ->assertActionHidden(SoftDeleteAction::class);

    $this->assertDatabaseHas(Message::class, ['id' => $message->id]);
});
