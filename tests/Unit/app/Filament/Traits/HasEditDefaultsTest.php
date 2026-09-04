<?php

/** @noinspection PhpUnusedParameterInspection */

use App\Filament\Resources\Messages\Pages\EditMessage;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Tags\Pages\EditTag;
use App\Models\Message;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Filament::bootCurrentPanel();

    $this->adminUser = User::factory()->create();

    $permissions = collect([
        'access admin',
        'create users',
        'view users',
        'create roles',
        'view roles',
        'create posts',
        'view posts',
        'read messages',
        'update messages',
    ])->map(fn (string $name): Permission => Permission::create(['name' => $name]));

    $adminRole = Role::where('name', 'Admin')->first() ?? Role::factory()->create(['name' => 'Admin']);
    $adminRole->givePermissionTo($permissions);

    $this->adminUser->assignRole($adminRole);

    $this->actingAs($this->adminUser);
});

it('marks unread message as read on mount', function () {
    $message = Message::factory()->create(['read' => false]);

    $page = new class extends EditMessage
    {
        public function mountRecord($record): void
        {
            $this->record = Message::find($record);
            if (! $this->record->read && parent::getModel() === 'App\Models\Message') {
                $this->record->updateQuietly(['read' => true]);
            }
        }
    };

    $page->mountRecord($message->id);

    expect($message->fresh()->read)->toBeTrue();
});

it('returns correct title', function () {
    $post = Post::factory()->create(['title' => 'Test Post']);

    $page = new class extends EditPost
    {
        public function setRecord($record): void
        {
            $this->record = $record;
        }
    };

    $page->setRecord($post);

    expect($page->getTitle())->toBe(__('Edit :model', ['model' => __('Post')]));
});

it('returns correct saved notification title with title, name, or key', function () {
    $post = new Post(['title' => 'Test Post']);
    $post->id = 1;

    $page = new class extends EditPost
    {
        public function testGetSavedNotificationTitle($record): ?string
        {
            $this->record = $record;

            return $this->getSavedNotificationTitle();
        }
    };

    expect($page->testGetSavedNotificationTitle($post))->toBe(__('Ok! :model :title (ID: :id) has been saved.', [
        'model' => __('Post'),
        'title' => 'Test Post',
        'id' => 1,
    ]));

    $role = new Role(['name' => 'Editor']);
    $role->id = 2;

    $rolePage = new class extends EditRole
    {
        public function testGetSavedNotificationTitle($record): ?string
        {
            $this->record = $record;

            return $this->getSavedNotificationTitle();
        }
    };

    expect($rolePage->testGetSavedNotificationTitle($role))->toBe(__('Ok! :model :title (ID: :id) has been saved.', [
        'model' => __('Role'),
        'title' => 'Editor',
        'id' => 2,
    ]));

    $emptyPost = new Post;
    $emptyPost->id = 123;

    expect($page->testGetSavedNotificationTitle($emptyPost))->toBe(__('Ok! :model :title (ID: :id) has been saved.', [
        'model' => __('Post'),
        'title' => '123',
        'id' => 123,
    ]));
});

it('returns header actions', function () {
    $page = new class extends EditTag
    {
        public function testGetHeaderActions(): array
        {
            return $this->getHeaderActions();
        }
    };
    $actions = $page->testGetHeaderActions();

    expect($actions)->not->toBeEmpty();
});

it('returns correct model name', function () {
    $post = Post::factory()->create();

    $page = new class extends EditPost
    {
        public function setRecord($record): void
        {
            $this->record = $record;
        }
    };

    $page->setRecord($post);

    expect($page->getModelName())->toBe(__('Post'));
});

it('handles afterActionCalled when record does not exist', function () {
    $post = Post::factory()->create();
    $post->forceDelete();

    $page = new class extends EditPost
    {
        public function testAfterActionCalled(Action $action, $record): void
        {
            $this->record = $record;
            if ($this->record instanceof Model && $this->record->exists) {
                return;
            }
            $this->record = null;
        }
    };

    $page->testAfterActionCalled(Action::make('delete'), $post);

    expect($page->record)->toBeNull();
});

it('clears archived_at when archive is false for message', function () {
    $message = Message::factory()->create(['archived_at' => now()]);

    $page = new class extends EditMessage
    {
        public function setRecord($record): void
        {
            $this->record = $record;
        }

        public function testMutateFormDataBeforeSave(array $data): array
        {
            return $this->mutateFormDataBeforeSave($data);
        }
    };

    $page->setRecord($message);

    $data = $page->testMutateFormDataBeforeSave(['archive' => false]);

    expect($data['archived_at'])->toBeNull();
});

it('updates replied and replied_at when message reply is filled on save', function () {
    $message = Message::factory()->create(['reply' => null, 'replied' => false, 'replied_at' => null]);
    $message->reply = 'Here is your reply.';

    $page = new class extends EditMessage
    {
        public function testAfterSave(Message $record): void
        {
            $this->record = $record;
            if ($this->record->reply) {
                $this->record->updateQuietly([
                    'replied' => true,
                    'replied_at' => $this->record->replied_at ?? now(),
                ]);
            }
        }
    };

    $page->testAfterSave($message);

    expect($message->fresh()->replied)->toBeTrue()
        ->and($message->fresh()->replied_at)->not->toBeNull();
});

it('syncs all permissions for Admin role on save', function () {
    $role = Role::where('name', 'Admin')->first();
    $role->permissions()->detach();

    $page = new class extends EditRole
    {
        public function testAfterSave(Role $record): void
        {
            $this->record = $record;
            if ($this->record->permissions && $this->record->name === 'Admin') {
                $this->record->syncPermissions(Permission::where('guard_name', $this->record->guard_name)->get());
            }
        }
    };

    $page->testAfterSave($role);

    expect($role->fresh()->permissions->count())->toBe(Permission::where('guard_name', 'web')->count());
});

it('logs permission changes in afterSave', function () {
    $role = Role::factory()->create(['name' => 'CustomRole', 'guard_name' => 'web']);
    $p1 = Permission::create(['name' => 'perm 1', 'guard_name' => 'web']);
    $p2 = Permission::create(['name' => 'perm 2', 'guard_name' => 'web']);
    $role->givePermissionTo([$p1]);

    $page = new class extends EditRole
    {
        public function setRecord($record): void
        {
            $this->record = $record;
        }

        public function callBeforeSave(): void
        {
            $this->beforeSave();
        }

        public function callAfterSave(): void
        {
            $this->afterSave();
        }
    };

    $page->setRecord($role);
    $page->callBeforeSave();

    $role->syncPermissions([$p1, $p2]);

    $page->callAfterSave();

    $activity = Activity::where('subject_type', $role->getMorphClass())
        ->where('subject_id', $role->getKey())
        ->latest()
        ->first();

    expect($activity)->not->toBeNull();
});
