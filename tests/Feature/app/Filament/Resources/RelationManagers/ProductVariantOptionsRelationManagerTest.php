<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Resources\ProductVariantOptionTypes\Pages\EditProductVariantOptionType;
use App\Filament\Resources\ProductVariantOptionTypes\Relations\ProductVariantOptionsRelationManager;
use App\Models\Permission;
use App\Models\ProductVariantOption;
use App\Models\ProductVariantOptionType;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'create products',
        'view products',
        'update products',
        'delete products',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('can list variant options', function () {
    $type = ProductVariantOptionType::factory()
        ->has(ProductVariantOption::factory()->count(3), 'options')
        ->create();

    livewire(ProductVariantOptionsRelationManager::class, [
        'ownerRecord' => $type,
        'pageClass' => EditProductVariantOptionType::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($type->options)
        ->assertCountTableRecords(3);
});

it('can search variant options by name', function () {
    $type = ProductVariantOptionType::factory()
        ->has(ProductVariantOption::factory()->count(2)->state(['name' => 'Red']), 'options')
        ->create();
    ProductVariantOption::factory()->create(['name' => 'Blue', 'product_variant_option_type_id' => $type->id]);

    livewire(ProductVariantOptionsRelationManager::class, [
        'ownerRecord' => $type,
        'pageClass' => EditProductVariantOptionType::class,
    ])
        ->searchTable('Red')
        ->assertCanSeeTableRecords($type->options->where('name', 'Red'))
        ->assertCanNotSeeTableRecords($type->options->where('name', 'Blue'));
});

it('validates required fields when creating a variant option', function () {
    $type = ProductVariantOptionType::factory()->create();

    livewire(ProductVariantOptionsRelationManager::class, [
        'ownerRecord' => $type,
        'pageClass' => EditProductVariantOptionType::class,
    ])
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'name' => null,
        ])
        ->assertHasFormErrors([
            'name' => 'required',
        ])
        ->assertNotNotified();
});

it('can create a variant option', function () {
    $type = ProductVariantOptionType::factory()->create();

    livewire(ProductVariantOptionsRelationManager::class, [
        'ownerRecord' => $type,
        'pageClass' => EditProductVariantOptionType::class,
    ])
        ->callAction(TestAction::make(CreateAction::class)->table(), [
            'name' => 'Red',
            'value' => '#FF0000',
        ])
        ->assertNotified();

    $this->assertDatabaseHas(ProductVariantOption::class, [
        'name' => 'Red',
        'value' => '#FF0000',
    ]);
});

it('can edit a variant option', function () {
    $type = ProductVariantOptionType::factory()
        ->has(ProductVariantOption::factory(), 'options')
        ->create();
    $option = $type->options->first();

    livewire(ProductVariantOptionsRelationManager::class, [
        'ownerRecord' => $type,
        'pageClass' => EditProductVariantOptionType::class,
    ])
        ->callTableAction('edit', $option->getRouteKey(), [
            'name' => 'Updated Name',
            'value' => '#00FF00',
        ])
        ->assertNotified();

    $this->assertDatabaseHas(ProductVariantOption::class, [
        'id' => $option->id,
        'name' => 'Updated Name',
        'value' => '#00FF00',
    ]);
});

it('can delete a variant option', function () {
    $type = ProductVariantOptionType::factory()
        ->has(ProductVariantOption::factory(), 'options')
        ->create();
    $option = $type->options->first();

    livewire(ProductVariantOptionsRelationManager::class, [
        'ownerRecord' => $type,
        'pageClass' => EditProductVariantOptionType::class,
    ])
        ->callTableAction('delete', $option->getRouteKey())
        ->assertNotified();

    $this->assertDatabaseMissing(ProductVariantOption::class, [
        'id' => $option->id,
    ]);
});

it('can bulk delete variant options', function () {
    $type = ProductVariantOptionType::factory()
        ->has(ProductVariantOption::factory()->count(3), 'options')
        ->create();

    livewire(ProductVariantOptionsRelationManager::class, [
        'ownerRecord' => $type,
        'pageClass' => EditProductVariantOptionType::class,
    ])
        ->callTableBulkAction('delete', $type->options)
        ->assertNotified();

    foreach ($type->options as $option) {
        $this->assertDatabaseMissing(ProductVariantOption::class, [
            'id' => $option->id,
        ]);
    }
});

it('denies access when user lacks permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $type = ProductVariantOptionType::factory()->create();

    livewire(ProductVariantOptionsRelationManager::class, [
        'ownerRecord' => $type,
        'pageClass' => EditProductVariantOptionType::class,
    ])
        ->assertForbidden();
});
