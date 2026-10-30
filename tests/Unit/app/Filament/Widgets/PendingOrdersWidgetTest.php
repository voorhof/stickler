<?php

/** @noinspection PhpUndefinedMethodInspection */

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Widgets\PendingOrdersWidget;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'view orders',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

describe('widget display', function () {
    it('displays the correct number of pending orders', function () {
        Order::factory()->count(3)->create(['status' => OrderStatus::Pending]);
        Order::factory()->count(2)->create(['status' => OrderStatus::Delivered]);

        livewire(PendingOrdersWidget::class)
            ->assertOk()
            ->assertSee('3');
    });

    it('links to the orders list with the pending status filter active', function () {
        Order::factory()->count(2)->create(['status' => OrderStatus::Pending]);

        $expectedUrl = OrderResource::getUrl('index', [
            'filters' => [
                'status' => ['value' => OrderStatus::Pending->value],
            ],
        ]);

        livewire(PendingOrdersWidget::class)
            ->assertSee($expectedUrl);
    });
});

describe('color logic', function () {
    it('sets the color to warning when there are pending orders', function () {
        expect(PendingOrdersWidget::getStatColor(1))->toBe('warning');
    });

    it('sets the color to success when there are no pending orders', function () {
        expect(PendingOrdersWidget::getStatColor(0))->toBe('success');
    });
});
