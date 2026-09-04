<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Filament\Resources\Messages\MessageResource;
use App\Filament\Widgets\UnreadMessagesWidget;
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
        'read messages',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('displays the correct number of unread messages', function () {
    Message::factory()->count(3)->create(['read' => false]);
    Message::factory()->count(2)->create(['read' => true]);

    livewire(UnreadMessagesWidget::class)
        ->assertOk()
        ->assertSee('3');
});

it('links to the messages list with the unread filter active and the archived filter disabled', function () {
    Message::factory()->count(2)->create(['read' => false]);

    $expectedUrl = MessageResource::getUrl('index', [
        'filters' => [
            'isUnRead' => ['isActive' => true],
            'isArchived' => ['isArchived' => false],
            'trashed' => ['value' => 1],
        ],
    ]);

    livewire(UnreadMessagesWidget::class)
        ->assertSee($expectedUrl);
});

it('sets the color to primary when there are unread messages', function () {
    expect(UnreadMessagesWidget::getStatColor(1))->toBe('primary');
});

it('sets the color to success when there are no unread messages', function () {
    expect(UnreadMessagesWidget::getStatColor(0))->toBe('success');
});
