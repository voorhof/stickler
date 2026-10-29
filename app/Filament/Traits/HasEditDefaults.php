<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection, PhpUndefinedMethodInspection, PhpMultipleClassDeclarationsInspection, PhpUndefinedFieldInspection */

namespace App\Filament\Traits;

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

trait HasEditDefaults
{
    use FormatsModelType;
    use InteractsWithRecord;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $modelClass = parent::getModel();

        // Set the ‘read’ attribute to ‘true’ for a Message model when it is viewed
        if ($modelClass === 'App\Models\Message' && ! $this->record->read) {
            $this->record->updateQuietly(['read' => true]);
        }
    }

    public function getTitle(): string
    {
        return __('Edit :model', ['model' => __(static::getModelName())]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        $title = $this->record->title
            ?? $this->record->name
            ?? $this->record->getKey();

        return __('Ok! :model :title (ID: :id) has been saved.', [
            'model' => static::getModelName(),
            'title' => $title,
            'id' => $this->record->getKey(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form')
                ->size('sm'),
            SoftDeleteAction::make(),
            RestoreDeletedAction::make(),
            HardDeleteAction::make(),
        ];
    }

    public function getModelName(): string
    {
        $type = $this->record instanceof Model
            ? $this->record->getMorphClass()
            : parent::getModel();

        return static::formatModelType($type);
    }

    protected function afterActionCalled(Action $action): void
    {
        // When a record has just been permanently deleted (e.g. force delete), the parent
        // implementation calls getRecord(), which aborts with a 404 for a missing record.
        // Guard against that so the action can still dispatch its success notification/redirect.
        if ($this->record instanceof Model && $this->record->exists) {
            parent::afterActionCalled($action);

            return;
        }

        $this->record = null;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->getTable() === 'messages') {
            if (! ($data['archive'] ?? false)) {
                $data['archived_at'] = null;
            }

            unset($data['archive']);
        }

        if ($this->record instanceof User && array_key_exists('roles', $data)) {
            $this->pendingRoleIds = $data['roles'] ? (array) $data['roles'] : [];
            unset($data['roles']);
        }

        if ($this->record instanceof Role && array_key_exists('permissions', $data)) {
            $this->pendingPermissionIds = $data['permissions'] ? (array) $data['permissions'] : [];
            unset($data['permissions']);
        }

        return $data;
    }

    public array $pendingRoleIds = [];

    public array $pendingPermissionIds = [];

    public array $oldTags = [];

    public array $oldPermissions = [];

    public array $oldRoles = [];

    public array $oldOptions = [];

    protected function beforeSave(): void
    {
        $this->oldTags = method_exists($this->record, 'tags') ? $this->record->tags()->pluck('name')->toArray() : [];
        $this->oldPermissions = method_exists($this->record, 'permissions') ? $this->record->permissions()->pluck('name')->toArray() : [];
        $this->oldRoles = method_exists($this->record, 'getRoleNames') ? $this->record->getRoleNames() : [];
        $this->oldOptions = method_exists($this->record, 'options') ? $this->record->options()->pluck('name')->toArray() : [];
    }

    protected function afterSave(): void
    {
        // Persist the standard Filament save notification to the database for the current user
        $notification = $this->getSavedNotification();

        if ($notification !== null && auth()->check()) {
            $notification->sendToDatabase(auth()->user(), isEventDispatched: true);
        }

        // Sync Spatie roles for User records (roles is a relation, not a column)
        if ($this->record instanceof User && $this->pendingRoleIds !== []) {
            $this->record->syncRoles(Role::whereIn('id', $this->pendingRoleIds)->get());
        }

        // Set replied_at when reply is filled
        if ($this->record->getTable() === 'messages' && $this->record->reply) {
            $this->record->updateQuietly([
                'replied' => true,
                'replied_at' => $this->record->replied_at ?? now(),
            ]);
        }

        // Role model actions
        if ($this->record instanceof Role && $this->pendingPermissionIds !== []) {
            $this->record->syncPermissions(Permission::whereIn('id', $this->pendingPermissionIds)->get());
        }

        if (method_exists($this->record, 'permissions')) {

            // Sync all permissions to the Admin role by default (for safety)
            if ($this->record->name === 'Admin') {
                $this->record->syncPermissions(Permission::where('guard_name', $this->record->guard_name)->get());
            }

            // Log permissions changes if any (only if NO other activity was logged)
            $newPermissions = $this->record->permissions()->pluck('name')->toArray();
            if (array_diff($this->oldPermissions, $newPermissions) || array_diff($newPermissions, $this->oldPermissions)) {
                // Check if an activity was already logged in this request for this role and event 'updated'
                $activity = Activity::where('subject_type', $this->record->getMorphClass())
                    ->where('subject_id', $this->record->getKey())
                    ->where('event', 'updated')
                    ->where('created_at', '>=', now()->subSeconds(2))
                    ->latest()
                    ->first();

                if ($activity) {
                    // Merge changes into the existing activity
                    $activity->properties = $activity->properties->put('permissions', $newPermissions);
                    $activity->save();
                } else {
                    // Log a separate activity if NO standard 'updated' activity was logged
                    activity()
                        ->performedOn($this->record)
                        ->event('updated')
                        ->withProperties(['permissions' => $newPermissions, 'old_permissions' => $this->oldPermissions])
                        ->log(__('This item has been :event', ['event' => __('updated')]));
                }
            }
        }

        // Log changes if any (only if NO other activity was logged)
        $newTags = method_exists($this->record, 'tags') ? $this->record->tags()->pluck('name')->toArray() : [];
        $newRoles = method_exists($this->record, 'getRoleNames') ? $this->record->getRoleNames() : [];
        $newOptions = method_exists($this->record, 'options') ? $this->record->options()->pluck('name')->toArray() : [];

        $tagsChanged = array_diff($this->oldTags, $newTags) || array_diff($newTags, $this->oldTags);
        $rolesChanged = array_diff($this->oldRoles, $newRoles) || array_diff($newRoles, $this->oldRoles);
        $optionsChanged = array_diff($this->oldOptions, $newOptions) || array_diff($newOptions, $this->oldOptions);

        if ($tagsChanged || $rolesChanged || $optionsChanged) {
            // Check if an activity was already logged in this request for this coachingPlan and event 'updated'
            $activity = Activity::where('subject_type', $this->record->getMorphClass())
                ->where('subject_id', $this->record->getKey())
                ->where('event', 'updated')
                ->where('created_at', '>=', now()->subSeconds(2))
                ->latest()
                ->first();

            if ($activity) {
                // Merge changes into the existing activity
                $activity->properties = $activity->properties
                    ->put('tags', $tagsChanged ? $newTags : null)
                    ->put('old_tags', $tagsChanged ? $this->oldTags : null)
                    ->put('roles', $rolesChanged ? $newRoles : null)
                    ->put('old_roles', $rolesChanged ? $this->oldRoles : null)
                    ->put('options', $optionsChanged ? $newOptions : null)
                    ->put('old_options', $optionsChanged ? $this->oldOptions : null);

                $activity->save();
            } else {
                // Log a separate activity if NO standard 'updated' activity was logged
                activity()
                    ->performedOn($this->record)
                    ->event('updated')
                    ->withProperties([
                        'tags' => $tagsChanged ? $newTags : null,
                        'old_tags' => $tagsChanged ? $this->oldTags : null,
                        'roles' => $rolesChanged ? $newRoles : null,
                        'old_roles' => $rolesChanged ? $this->oldRoles : null,
                        'options' => $optionsChanged ? $newOptions : null,
                        'old_options' => $optionsChanged ? $this->oldOptions : null,
                    ])
                    ->log(__('This item has been :event', ['event' => __('updated')]));
            }
        }
    }
}
