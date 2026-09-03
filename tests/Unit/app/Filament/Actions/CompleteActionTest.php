<?php

// use App\Filament\Actions\CompleteAction;
// use App\Filament\Resources\Messages\Pages\EditMessage;
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
//    $action = CompleteAction::make();
//    expect($action->getName())->toBe('complete');
// });

// it('is hidden if the record is already completed', function () {
//    $action = CompleteAction::make();
//    $message = Message::factory()->create(['completed_at' => now()]);

//    // Set the record on the action
//    $action->record($message);

//    // The hidden() callback should evaluate to true
//    expect($action->isHidden())->toBeTrue();
// });

// it('is not hidden if the record is not completed', function () {
//    $action = CompleteAction::make();
//    $message = Message::factory()->create(['completed_at' => null]);

//    // Set the record on the action
//    $action->record($message);

//    // The hidden() callback should evaluate to false
//    expect($action->isHidden())->toBeFalse();
// });

// it('turns the completed toggle on by default when editing an completed message', function () {
//    $message = Message::factory()->create(['completed_at' => now()]);

//    livewire(EditMessage::class, ['record' => $message->getRouteKey()])
//        ->assertSchemaStateSet(['completed' => true]);
// });

// it('leaves the completed toggle off when editing a message that is not completed', function () {
//    $message = Message::factory()->create(['completed_at' => null]);

//    livewire(EditMessage::class, ['record' => $message->getRouteKey()])
//        ->assertSchemaStateSet(['completed' => false]);
// });

// it('can complete a message', function () {
//    $message = Message::factory()->create(['completed_at' => null]);

//    livewire(EditMessage::class, ['record' => $message->slug])
//        ->callAction('complete')
//        ->assertNotified();

//    $message->refresh();
//    expect($message->completed_at)->not->toBeNull();
// });
