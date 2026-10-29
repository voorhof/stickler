<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Actions\CartCleanupAction;
use App\Filament\Resources\Carts\Pages\ListCarts;
use App\Models\Cart;
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
        'view carts',
        'delete carts',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

describe('action configuration', function () {
    it('has the correct default name', function () {
        $action = CartCleanupAction::make();

        expect($action->getName())->toBe('cartClean');
    });
});

describe('action execution', function () {
    it('deletes carts not updated within the requested days and keeps recent ones', function () {
        $oldCart = Cart::factory()->create();
        $oldCart->forceFill(['updated_at' => now()->subDays(100)])->saveQuietly();

        $recentCart = Cart::factory()->create();

        livewire(ListCarts::class)
            ->callAction('cartClean', [
                'max_days' => 60,
            ])
            ->assertNotified()
            ->assertHasNoFormErrors();

        expect(Cart::find($oldCart->id))->toBeNull()
            ->and(Cart::find($recentCart->id))->not->toBeNull();
    });
});

describe('validation', function () {
    it('requires max_days', function () {
        Cart::factory()->create();

        livewire(ListCarts::class)
            ->callAction('cartClean', [
                'max_days' => null,
            ])
            ->assertHasFormErrors(['max_days' => 'required']);
    });

    it('validates max_days range', function () {
        Cart::factory()->create();

        livewire(ListCarts::class)
            ->callAction('cartClean', [
                'max_days' => 400,
            ])
            ->assertHasFormErrors(['max_days' => 'max']);
    });
});

describe('visibility', function () {
    it('hides the action when there are no carts', function () {
        Cart::query()->delete();

        livewire(ListCarts::class)
            ->assertActionHidden('cartClean');
    });

    it('hides the action for users without delete carts permission', function () {
        Cart::factory()->create();

        $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
        $restrictedRole->givePermissionTo(['access admin', 'view carts']);
        $restrictedUser = User::factory()->create();
        $restrictedUser->assignRole($restrictedRole);
        $this->actingAs($restrictedUser);

        livewire(ListCarts::class)
            ->assertActionHidden('cartClean');
    });
});
