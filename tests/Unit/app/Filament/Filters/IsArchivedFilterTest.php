<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Filament\Filters\IsArchivedFilter;
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
    $filter = IsArchivedFilter::make();

    expect($filter->getName())->toBe('isArchived');
});

it('returns the title case plural model label from the table', function () {
    livewire(ListMessages::class)
        ->assertTableFilterExists('isArchived');

    $component = livewire(ListMessages::class);
    $table = $component->instance()->getTable();
    $filter = $table->getFilter('isArchived');

    expect($filter->getTitleCasePluralModelLabel())->toBe(__('Messages'));
});

it('filters archived records when enabled', function () {
    $activeMessage = Message::factory()->create(['archived_at' => null]);
    $archivedMessage = Message::factory()->create(['archived_at' => now()]);

    livewire(ListMessages::class)
        ->assertCanSeeTableRecords([$activeMessage])
        ->assertCanNotSeeTableRecords([$archivedMessage])
        ->filterTable('isArchived', false)
        ->assertCanSeeTableRecords([$activeMessage, $archivedMessage]);
});
