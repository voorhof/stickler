<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Actions\SendMessageReplyMailAction;
use App\Filament\Resources\Messages\Pages\EditMessage;
use App\Mail\ContactMessageReply;
use App\Models\Message;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $adminUser = User::factory()->create();
    $adminRole = Role::factory()->create(['name' => 'Admin', 'guard_name' => 'web', 'created_by_user_id' => $adminUser->id, 'updated_by_user_id' => $adminUser->id]);

    $permissions = collect([
        'access admin',
        'view messages',
        'update messages',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole->givePermissionTo($permissions);

    $adminUser->assignRole($adminRole);

    $this->actingAs($adminUser);
});

describe('action configuration', function () {
    it('has the correct default name', function () {
        $action = SendMessageReplyMailAction::make();
        expect($action->getName())->toBe('sendMessageReply');
    });
});

describe('visibility', function () {
    it('is hidden if the record does not have a reply', function () {
        $action = SendMessageReplyMailAction::make();
        $message = Message::factory()->create(['reply' => null]);

        $action->record($message);

        expect($action->isHidden())->toBeTrue();
    });

    it('is not hidden if the record has a reply', function () {
        $action = SendMessageReplyMailAction::make();
        $message = Message::factory()->create(['reply' => 'reply text']);

        $action->record($message);

        expect($action->isHidden())->toBeFalse();
    });
});

describe('authorization', function () {
    it('does not send the reply for users without update messages permission', function () {
        Mail::fake();

        $restrictedRole = Role::factory()->create(['name' => 'Restricted', 'guard_name' => 'web']);
        $restrictedRole->givePermissionTo(['access admin', 'view messages']);
        $restrictedUser = User::factory()->create();
        $restrictedUser->assignRole($restrictedRole);
        $this->actingAs($restrictedUser);

        $message = Message::factory()->create(['reply' => 'reply text']);

        $action = SendMessageReplyMailAction::make();
        $action->record($message);

        expect($action->isAuthorized())->toBeFalse();

        Mail::assertNothingQueued();
    });
});

describe('action execution', function () {
    it('sends the reply email and notifies the user', function () {
        Mail::fake();

        $message = Message::factory()->create(['reply' => 'reply text']);

        livewire(EditMessage::class, ['record' => $message->getRouteKey()])
            ->callAction(TestAction::make('sendMessageReply')->schemaComponent())
            ->assertNotified();

        Mail::assertQueued(ContactMessageReply::class, fn (ContactMessageReply $mail): bool => $mail->hasTo($message->email));
    });
});
