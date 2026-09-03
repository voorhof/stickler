<?php

use App\Filament\Filters\IsUnReadFilter;
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
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('has the correct default name', function () {
    $filter = IsUnReadFilter::make();

    expect($filter->getName())->toBe('isUnRead');
});

it('has the correct label', function () {
    $filter = IsUnReadFilter::make();

    expect($filter->getLabel())->toBe(__('Unread'));
});

it('exists in the messages table', function () {
    livewire(ListMessages::class)
        ->assertTableFilterExists('isUnRead');
});

it('filters unread records when enabled', function () {
    $unreadMessage = Message::factory()->create(['read' => false]);
    $readMessage = Message::factory()->create(['read' => true]);

    livewire(ListMessages::class)
        ->filterTable('isUnRead')
        ->assertCanSeeTableRecords([$unreadMessage])
        ->assertCanNotSeeTableRecords([$readMessage]);
});
