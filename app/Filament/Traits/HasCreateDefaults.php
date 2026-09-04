<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection, PhpUndefinedMethodInspection, PhpMultipleClassDeclarationsInspection */

namespace App\Filament\Traits;

// use App\Mail\Filament\UserCreatedMail;
use App\Models\Permission;
use App\Models\User;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

trait HasCreateDefaults
{
    use FormatsModelType;

    /**
     * Plain-text random password captured before it is hashed,
     * so we can include it in the welcome email for a newly created user.
     */
    protected ?string $randomPassword = null;

    /*
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (parent::getModel() === 'App\Models\User') {
            // Generate a random password
            $this->randomPassword = Str::password(length: 12, symbols: false);

            // Add random password to the data array, it will be hashed in the database by the cast on save
            $data['password'] = $this->randomPassword;

            // Set the password_needs_update to true, so that the user will be forced to change it on first login
            $data['password_needs_update'] = true;
        }

        return $data;
    }
    */

    protected function getCreatedNotificationTitle(): ?string
    {
        $title = $this->record->title
            ?? $this->record->name
            ?? $this->record->getKey();

        return __('Woohoo! :model :title (ID: :id) has been successfully created!', [
            'model' => static::formatModelType(parent::getModel()),
            'title' => $title,
            'id' => $this->record->getKey(),
        ]);
    }

    protected function afterCreate(): void
    {
        // Persist the standard Filament notification to the database for the current user
        $notification = $this->getCreatedNotification();

        if ($notification !== null && auth()->check()) {
            $notification->sendToDatabase(auth()->user(), isEventDispatched: true);
        }

        // Role model actions
        if (parent::getModel() === 'App\Models\Role') {
            $record = $this->getRecord();

            // Sync all permissions to the Admin role by default for safety
            if ($record->name === 'Admin') {
                $record->syncPermissions(Permission::where('guard_name', $record->guard_name)->get());
            }
        }

        // User model actions
        if (parent::getModel() === 'App\Models\User') {
            // Set email_verified_at to now
            $user = User::find($this->record->id);
            $user->email_verified_at = now();
            $user->save();

            // Send mail with random temporary password to the created user
            // if ($this->randomPassword !== null && $user->email) {
            //    Mail::queue(new UserCreatedMail($user, $this->randomPassword));
            // }
        }

        // Log linked models for newly created records (merging with existing 'created' activity if possible)
        $tags = $this->record->tags ? $this->record->tags()->pluck('name')->toArray() : [];
        $permissions = $this->record->permissions ? $this->record->permissions()->pluck('name')->toArray() : [];
        $roles = $this->record->roles ? $this->record->getRoleNames() : [];

        $activity = Activity::where('subject_type', $this->record->getMorphClass())
            ->where('subject_id', $this->record->getKey())
            ->where('event', 'created')
            ->where('created_at', '>=', now()->subSeconds(2))
            ->latest()
            ->first();

        if ($activity) {
            $activity->properties = $activity->properties
                ->put('tags', $tags)
                ->put('permissions', $permissions)
                ->put('roles', $roles);
            $activity->save();
        } else {
            // Fallback: log a separate activity if for some reason the standard 'created' one wasn't logged
            activity()
                ->performedOn($this->record)
                ->event('created')
                ->withProperties([
                    'tags' => $tags,
                    'permissions' => $permissions,
                    'roles' => $roles,
                ])
                ->log(__('This item has been :event', ['event' => __('created')]));
        }
    }
}
