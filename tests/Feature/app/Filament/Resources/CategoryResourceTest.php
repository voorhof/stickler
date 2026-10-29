<?php

/** @noinspection PhpUnhandledExceptionInspection, PhpUndefinedMethodInspection, PhpPossiblePolymorphicInvocationInspection */

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'create categories',
        'view categories',
        'update categories',
        'delete categories',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('can list categories', function () {
    $categories = Category::factory()->count(3)->create();

    livewire(ListCategories::class)
        ->assertOk()
        ->assertCanSeeTableRecords($categories)
        ->assertCountTableRecords(3);
});

it('can reorder categories', function () {
    $category1 = Category::factory()->create(['order_column' => 1]);
    $category2 = Category::factory()->create(['order_column' => 2]);

    livewire(ListCategories::class)
        ->call('reorderTable', [$category2->id, $category1->id]);

    expect($category1->fresh()->order_column)->toBe(2)
        ->and($category2->fresh()->order_column)->toBe(1);
});

it('can search categories by title', function () {
    Category::factory()->create(['title' => 'Unique Category Title', 'slug' => 'unique-category-title']);
    $otherCategory = Category::factory()->create(['title' => 'Something Else', 'slug' => 'something-else']);

    livewire(ListCategories::class)
        ->searchTable('Unique Category Title')
        ->assertCanSeeTableRecords(Category::where('title', 'Unique Category Title')->get())
        ->assertCanNotSeeTableRecords([$otherCategory]);
});

it('can filter categories by for_pages', function () {
    $pagesCategory = Category::factory()->create(['for_pages' => true]);
    $otherCategory = Category::factory()->create(['for_pages' => false]);

    livewire(ListCategories::class)
        ->filterTable('for_pages', true)
        ->assertCanSeeTableRecords([$pagesCategory])
        ->assertCanNotSeeTableRecords([$otherCategory]);
});

it('can filter categories by for_posts', function () {
    $postsCategory = Category::factory()->create(['for_posts' => true]);
    $otherCategory = Category::factory()->create(['for_posts' => false]);

    livewire(ListCategories::class)
        ->filterTable('for_posts', true)
        ->assertCanSeeTableRecords([$postsCategory])
        ->assertCanNotSeeTableRecords([$otherCategory]);
});

it('can filter categories by for_products', function () {
    $productsCategory = Category::factory()->create(['for_products' => true]);
    $otherCategory = Category::factory()->create(['for_products' => false]);

    livewire(ListCategories::class)
        ->filterTable('for_products', true)
        ->assertCanSeeTableRecords([$productsCategory])
        ->assertCanNotSeeTableRecords([$otherCategory]);
});

it('can filter categories by for_projects', function () {
    $projectsCategory = Category::factory()->create(['for_projects' => true]);
    $otherCategory = Category::factory()->create(['for_projects' => false]);

    livewire(ListCategories::class)
        ->filterTable('for_projects', true)
        ->assertCanSeeTableRecords([$projectsCategory])
        ->assertCanNotSeeTableRecords([$otherCategory]);
});

it('can filter categories by trashed state on the list page table', function () {
    $activeCategory = Category::factory()->create();
    $trashedCategory = Category::factory()->softDeleted()->create();

    livewire(ListCategories::class)
        ->filterTable('trashed', false)
        ->assertCanSeeTableRecords([$trashedCategory])
        ->assertCanNotSeeTableRecords([$activeCategory])
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords([$activeCategory, $trashedCategory]);
});

it('validates required fields when creating a category', function () {
    livewire(CreateCategory::class)
        ->fillForm([
            'title' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'title' => 'required',
        ])
        ->assertNotNotified();
});

it('validates max lengths when creating a category', function () {
    livewire(CreateCategory::class)
        ->fillForm([
            'title' => str_repeat('a', 33),
        ])
        ->call('create')
        ->assertHasFormErrors([
            'title' => 'max',
        ])
        ->assertNotNotified();
});

it('can create a category', function () {
    livewire(CreateCategory::class)
        ->fillForm([
            'title' => 'My New Category',
            'for_pages' => true,
            'for_posts' => true,
            'for_products' => true,
            'for_projects' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Category::class, [
        'title' => 'My New Category',
        'slug' => 'my-new-category',
        'for_pages' => true,
        'for_posts' => true,
        'for_products' => true,
        'for_projects' => false,
    ]);
});

it('can create a category with for_model toggles', function () {
    livewire(CreateCategory::class)
        ->fillForm([
            'title' => 'Product Category',
            'for_pages' => false,
            'for_posts' => false,
            'for_products' => true,
            'for_projects' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Category::class, [
        'title' => 'Product Category',
        'for_products' => true,
    ]);
});

it('validates required fields when editing a category', function () {
    $category = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm([
            'title' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'title' => 'required',
        ])
        ->assertNotNotified();
});

it('can edit a category', function () {
    $category = Category::factory()->create(['title' => 'Old Title']);

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm([
            'title' => 'Updated Category Title',
            'slug' => 'updated-slug',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Category::class, [
        'id' => $category->id,
        'title' => 'Updated Category Title',
        'slug' => 'updated-slug',
    ]);
});

it('can edit for_model toggles on a category', function () {
    $category = Category::factory()->create([
        'for_pages' => false,
        'for_posts' => false,
        'for_products' => false,
        'for_projects' => false,
    ]);

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm([
            'for_pages' => true,
            'for_posts' => true,
            'for_products' => true,
            'for_projects' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $this->assertDatabaseHas(Category::class, [
        'id' => $category->id,
        'for_pages' => true,
        'for_posts' => true,
        'for_products' => true,
        'for_projects' => true,
    ]);
});

it('logs activity after creating a category', function () {
    livewire(CreateCategory::class)
        ->fillForm([
            'title' => 'My New Category',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $category = Category::where('title', 'My New Category')->first();
    $activity = Activity::where('subject_type', Category::class)
        ->where('subject_id', $category->id)
        ->where('event', 'created')
        ->first();

    expect($activity)->not->toBeNull();
});

it('logs activity after editing a category', function () {
    $category = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm([
            'title' => 'Updated Title',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $activity = Activity::where('subject_type', Category::class)
        ->where('subject_id', $category->id)
        ->where('event', 'updated')
        ->latest()
        ->first();

    expect($activity)->not->toBeNull();
});

it('can soft delete a category', function () {
    $category = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->callAction(SoftDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertSoftDeleted(Category::class, ['id' => $category->id]);
});

it('can force delete a soft-deleted category', function () {
    $category = Category::factory()->softDeleted()->create();

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->callAction(HardDeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseMissing(Category::class, ['id' => $category->id]);
});

it('can restore a soft-deleted category', function () {
    $category = Category::factory()->softDeleted()->create();

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->callAction(RestoreDeletedAction::class)
        ->assertNotified();

    $this->assertNotSoftDeleted(Category::class, ['id' => $category->id]);
});

it('can bulk delete categories', function () {
    $categories = Category::factory()->count(2)->create();

    livewire(ListCategories::class)
        ->callTableBulkAction('softDeleteBulk', $categories)
        ->assertNotified();

    foreach ($categories as $category) {
        $this->assertSoftDeleted(Category::class, ['id' => $category->id]);
    }
});

it('can bulk force delete soft-deleted categories', function () {
    $categories = Category::factory()->count(2)->softDeleted()->create();

    livewire(ListCategories::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('hardDeleteBulk', $categories)
        ->assertNotified();

    foreach ($categories as $category) {
        $this->assertDatabaseMissing(Category::class, ['id' => $category->id]);
    }
});

it('can bulk restore soft-deleted categories', function () {
    $categories = Category::factory()->count(2)->softDeleted()->create();

    livewire(ListCategories::class)
        ->filterTable('trashed', 'only')
        ->callTableBulkAction('restoreDeletedBulk', $categories)
        ->assertNotified();

    foreach ($categories as $category) {
        $this->assertNotSoftDeleted(Category::class, ['id' => $category->id]);
    }
});

it('denies destructive bulk actions when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view categories']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $activeCategory = Category::factory()->create();
    $trashedCategory = Category::factory()->softDeleted()->create();

    livewire(ListCategories::class)
        ->assertTableBulkActionHidden('softDeleteBulk');

    livewire(ListCategories::class)
        ->filterTable('trashed', false)
        ->assertTableBulkActionHidden('restoreDeletedBulk')
        ->assertTableBulkActionHidden('hardDeleteBulk');

    $this->assertDatabaseHas(Category::class, ['id' => $activeCategory->id, 'deleted_at' => null]);
    $this->assertSoftDeleted(Category::class, ['id' => $trashedCategory->id]);
});

it('denies listing categories when the user does not have view permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(ListCategories::class)
        ->assertForbidden();
});

it('denies loading create category page when the user does not have create permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo('access admin');
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    livewire(CreateCategory::class)
        ->assertForbidden();
});

it('denies loading edit category page when the user does not have update permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view categories']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $category = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->assertForbidden();
});

it('denies deleting a category when the user does not have delete permission', function () {
    $restrictedRole = Role::factory()->create(['name' => 'Restricted']);
    $restrictedRole->givePermissionTo(['access admin', 'view categories', 'update categories']);
    $restrictedUser = User::factory()->create();
    $restrictedUser->assignRole($restrictedRole);
    $this->actingAs($restrictedUser);

    $category = Category::factory()->create();

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->assertActionHidden(SoftDeleteAction::class);

    $this->assertDatabaseHas(Category::class, ['id' => $category->id]);
});
