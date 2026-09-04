<?php

/** @noinspection PhpParamsInspection, PhpUndefinedMethodInspection */

// use App\Filament\Actions\CompleteBulkAction;
// use App\Filament\Resources\Messages\Pages\ListMessages;
// use App\Models\Permission;
// use App\Models\Role;
// use App\Models\Message;
// use App\Models\User;
// use Filament\Facades\Filament;
// use Illuminate\Foundation\Testing\RefreshDatabase;

// use function Pest\Livewire\livewire;

// uses(RefreshDatabase::class);

// beforeEach(function () {
//    Filament::setCurrentPanel(Filament::getPanel('admin'));
//    Filament::bootCurrentPanel();

//    $this->adminUser = User::factory()->create();

//    $permissions = collect([
//        'access admin',
//        'view messages',
//        'update messages',
//    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

//    $adminRole = Role::factory()->create(['name' => 'Admin']);
//    $adminRole->givePermissionTo($permissions);

//    $this->adminUser->assignRole($adminRole);

//    $this->actingAs($this->adminUser);
// });

// it('has the correct default name', function () {
//    $action = CompleteBulkAction::make();

//    expect($action->getName())->toBe('completeBulk');
// });

// it('can bulk complete messages', function () {
//    $messages = Message::factory()->count(2)->create(['completed_at' => null]);

//    livewire(ListMessages::class)
//        ->callTableBulkAction('completeBulk', $messages)
//        ->assertNotified();

//    foreach ($messages as $message) {
//        $message->refresh();

//        expect($message->completed_at)->not->toBeNull();
//    }
// });

// it('does not bulk re-complete messages that are already completed', function () {
//    $completedAt = now()->subDay();

//    $messages = Message::factory(2)->create(['completed_at' => $completedAt]);

//    livewire(ListMessages::class)
//        ->callTableBulkAction('completeBulk', $messages);

//    foreach ($messages as $message) {
//        expect($message->refresh()->completed_at->toDateTimeString())
//            ->toBe($completedAt->toDateTimeString());
//    }
// });

// it('bulk comples only uncompleted messages in a mixed selection', function () {
//    $uncompletedMessage = Message::factory()->create(['completed_at' => null]);
//    $completedAt = now()->subDay();
//    $completedMessage = Message::factory()->create(['completed_at' => $completedAt]);

//    livewire(ListMessages::class)
//        ->callTableBulkAction('completeBulk', collect([$uncompletedMessage, $completedMessage]));

//    expect($uncompletedMessage->refresh()->completed_at)->not->toBeNull()
//        ->and($completedMessage->refresh()->completed_at->toDateTimeString())->toBe($completedAt->toDateTimeString());
// });

// it('denies bulk complete action when the user does not have update permission', function () {
//    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
//    $restrictedRole->givePermissionTo(['access admin', 'view messages']);
//    $restrictedUser = User::factory()->create();
//    $restrictedUser->assignRole($restrictedRole);
//    $this->actingAs($restrictedUser);

//    Message::factory(2)->create();

//    livewire(ListMessages::class)
//        ->assertTableBulkActionHidden('completeBulk');
// });
