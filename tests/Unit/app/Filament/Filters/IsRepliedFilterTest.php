<?php

use App\Filament\Filters\IsRepliedFilter;
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
    $filter = IsRepliedFilter::make();

    expect($filter->getName())->toBe('isReplied');
});

it('has the correct label', function () {
    $filter = IsRepliedFilter::make();

    expect($filter->getLabel())->toBe(__('Replied'));
});

it('exists in the messages table', function () {
    livewire(ListMessages::class)
        ->assertTableFilterExists('isReplied');
});

it('filters replied records when enabled', function () {
    $repliedMessage = Message::factory()->create(['replied' => true]);
    $unrepliedMessage = Message::factory()->create(['replied' => false]);

    livewire(ListMessages::class)
        ->filterTable('isReplied')
        ->assertCanSeeTableRecords([$repliedMessage])
        ->assertCanNotSeeTableRecords([$unrepliedMessage]);
});
