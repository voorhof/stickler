<?php

/** @noinspection PhpParamsInspection, PhpUndefinedMethodInspection */

use App\Filament\Actions\ArchiveBulkAction;
use App\Filament\Actions\PublishBulkAction;
use App\Filament\Actions\StatusToggleBulkAction;
use App\Filament\Resources\Messages\Pages\ListMessages;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Message;
use App\Models\Permission;
use App\Models\Post;
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
        'view messages',
        'update messages',
        'view posts',
        'update posts',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

describe('base class', function () {
    it('is an abstract status toggle bulk action', function () {
        $reflection = new ReflectionClass(StatusToggleBulkAction::class);

        expect($reflection->isAbstract())->toBeTrue()
            ->and(is_subclass_of(ArchiveBulkAction::class, StatusToggleBulkAction::class))->toBeTrue()
            ->and(is_subclass_of(PublishBulkAction::class, StatusToggleBulkAction::class))->toBeTrue();
    });
});

describe('skipping records already in state', function () {
    it('leaves records that are already archived unchanged', function () {
        $archivedAt = now()->subDay()->startOfSecond();
        $archived = Message::factory()->create(['archived_at' => $archivedAt]);
        $active = Message::factory()->create(['archived_at' => null]);

        livewire(ListMessages::class)
            ->callTableBulkAction('archiveBulk', [$archived, $active])
            ->assertNotified();

        expect($archived->refresh()->archived_at->equalTo($archivedAt))->toBeTrue()
            ->and($active->refresh()->archived_at)->not->toBeNull();
    });
});

describe('records not skipped', function () {
    it('re-publishes records that are already published and updates their timestamp', function () {
        $publishedAt = now()->subDay()->startOfSecond();
        $post = Post::factory()->create(['published_at' => $publishedAt]);

        livewire(ListPosts::class)
            ->callTableBulkAction('publishBulk', [$post])
            ->assertNotified();

        expect($post->refresh()->published_at->greaterThan($publishedAt))->toBeTrue();
    });
});

describe('authorization', function () {
    it('does not run the bulk update for a user without update permission', function () {
        $restrictedUser = User::factory()->create();
        $viewerRole = Role::factory()->create(['name' => 'Viewer']);
        $viewerRole->givePermissionTo(
            Permission::firstOrCreate(['name' => 'access admin']),
            Permission::firstOrCreate(['name' => 'view messages']),
        );
        $restrictedUser->assignRole($viewerRole);

        $message = Message::factory()->create(['archived_at' => null]);

        $this->actingAs($restrictedUser);

        livewire(ListMessages::class)
            ->assertTableBulkActionHidden('archiveBulk');

        expect($message->refresh()->archived_at)->toBeNull();
    });
});
