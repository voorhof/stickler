<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Filament\Resources\ProductVariantOptionTypes\Pages\CreateProductVariantOptionType;
use App\Filament\Resources\ProductVariantOptionTypes\Pages\EditProductVariantOptionType;
use App\Filament\Resources\ProductVariantOptionTypes\Pages\ListProductVariantOptionTypes;
use App\Models\Permission;
use App\Models\ProductVariantOptionType;
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

it('can list variant option types', function () {
    $types = ProductVariantOptionType::factory()->count(3)->create();

    livewire(ListProductVariantOptionTypes::class)
        ->assertOk()
        ->assertCanSeeTableRecords($types)
        ->assertCountTableRecords(3);
});

it('can search variant option types by name', function () {
    ProductVariantOptionType::factory()->create(['name' => 'Color']);
    $otherType = ProductVariantOptionType::factory()->create(['name' => 'Size']);

    livewire(ListProductVariantOptionTypes::class)
        ->searchTable('Color')
        ->assertCanSeeTableRecords(ProductVariantOptionType::where('name', 'Color')->get())
        ->assertCanNotSeeTableRecords([$otherType]);
});

it('validates required fields when creating a variant option type', function () {
    livewire(CreateProductVariantOptionType::class)
        ->fillForm([
            'name' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
        ])
        ->assertNotNotified();
});

it('can create a variant option type', function () {
    livewire(CreateProductVariantOptionType::class)
        ->fillForm([
            'name' => 'Color',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(ProductVariantOptionType::class, [
        'name' => 'Color',
    ]);
});

it('validates required fields when editing a variant option type', function () {
    $type = ProductVariantOptionType::factory()->create();

    livewire(EditProductVariantOptionType::class, ['record' => $type->getRouteKey()])
        ->fillForm([
            'name' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'required',
        ])
        ->assertNotNotified();
});

it('can edit a variant option type', function () {
    $type = ProductVariantOptionType::factory()->create(['name' => 'Old Name']);

    livewire(EditProductVariantOptionType::class, ['record' => $type->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(ProductVariantOptionType::class, [
        'id' => $type->id,
        'name' => 'Updated Name',
    ]);
});

it('can delete a variant option type', function () {
    $type = ProductVariantOptionType::factory()->create();

    livewire(EditProductVariantOptionType::class, ['record' => $type->getRouteKey()])
        ->callAction('Filament\Actions\DeleteAction')
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseMissing(ProductVariantOptionType::class, [
        'id' => $type->id,
    ]);
});

it('can bulk delete variant option types', function () {
    $types = ProductVariantOptionType::factory()->count(2)->create();

    livewire(ListProductVariantOptionTypes::class)
        ->callTableBulkAction('delete', $types)
        ->assertNotified();

    foreach ($types as $type) {
        $this->assertDatabaseMissing(ProductVariantOptionType::class, [
            'id' => $type->id,
        ]);
    }
});

it('denies listing variant option types when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListProductVariantOptionTypes::class)
        ->assertForbidden();
});

it('denies loading create variant option type page when the user does not have create permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(CreateProductVariantOptionType::class)
        ->assertForbidden();
});

it('denies loading edit variant option type page when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view products']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $type = ProductVariantOptionType::factory()->create();

    livewire(EditProductVariantOptionType::class, ['record' => $type->getRouteKey()])
        ->assertForbidden();
});
