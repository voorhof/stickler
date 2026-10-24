<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Provides creator and updater BelongsTo relationships.
 *
 * Use this trait on models that track which user created and updated the record.
 * Override {@see creatorClass()} to change the related model class (e.g., for self-referential models).
 * Override {@see creatorWithTrashed()} to control whether soft-deleted users are included.
 */
trait HasCreatorAndUpdater
{
    /**
     * Get the user that created the record.
     */
    public function creator(): BelongsTo
    {
        $relation = $this->belongsTo($this->creatorClass(), 'created_by_user_id');

        if ($this->creatorWithTrashed()) {
            $relation->withTrashed();
        }

        return $relation->withDefault([
            'name' => 'Guest User',
        ]);
    }

    /**
     * Get the user that updated the record.
     */
    public function updater(): BelongsTo
    {
        $relation = $this->belongsTo($this->creatorClass(), 'updated_by_user_id');

        if ($this->creatorWithTrashed()) {
            $relation->withTrashed();
        }

        return $relation->withDefault([
            'name' => 'Guest User',
        ]);
    }

    /**
     * Get the class name for the creator/updater relationship.
     *
     * @return class-string<User>
     */
    protected function creatorClass(): string
    {
        return User::class;
    }

    /**
     * Determine whether to include soft-deleted users in the relationship.
     */
    protected function creatorWithTrashed(): bool
    {
        return true;
    }
}
