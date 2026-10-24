<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Abstract base policy that resolves permission strings from the class name.
 *
 * Extend this class for standard policies where each method simply delegates
 * to a Spatie Permission string like "view posts" or "update pages".
 * Override individual methods when custom logic is needed (e.g., Admin role protection).
 */
abstract class BasePolicy
{
    /**
     * Get the plural resource name derived from the policy class name.
     *
     * "PostPolicy" -> "posts"
     * "MediaPolicy" -> "media"
     * "ActivityPolicy" -> "activities"
     */
    protected function resourceName(): string
    {
        $className = class_basename(static::class);

        // Remove "Policy" suffix
        $modelName = Str::replaceLast('Policy', '', $className);

        // Convert CamelCase to snake_case and pluralize
        return Str::plural(Str::snake($modelName));
    }

    /**
     * Build the full permission string for the given action.
     *
     * @example "view posts", "create pages", "delete tags"
     */
    protected function permissionName(string $action): string
    {
        return "{$action} {$this->resourceName()}";
    }

    /**
     * Determine whether the user can view any resources.
     */
    public function viewAny(User $user): bool
    {
        return $user->can($this->permissionName('view'));
    }

    /**
     * Determine whether the user can view the resource.
     */
    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create resources.
     */
    public function create(?User $user = null): bool
    {
        return $user->can($this->permissionName('create'));
    }

    /**
     * Determine whether the user can update the resource.
     */
    public function update(?User $user = null, ?Model $model = null): bool
    {
        return $user->can($this->permissionName('update'));
    }

    /**
     * Determine whether the user can reorder resources.
     */
    public function reorder(User $user): bool
    {
        return $this->update($user);
    }

    /**
     * Determine whether the user can delete any resources.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can($this->permissionName('delete'));
    }

    /**
     * Determine whether the user can delete the resource.
     */
    public function delete(?User $user = null, ?Model $model = null): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore any resources.
     */
    public function restoreAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can restore the resource.
     */
    public function restore(?User $user = null, ?Model $model = null): bool
    {
        return $this->delete($user, $model);
    }

    /**
     * Determine whether the user can permanently delete any resources.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * Determine whether the user can permanently delete the resource.
     */
    public function forceDelete(?User $user = null, ?Model $model = null): bool
    {
        return $this->delete($user, $model);
    }
}
