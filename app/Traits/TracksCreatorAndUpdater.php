<?php

namespace App\Traits;

use App\Models\Media;
use App\Models\Message;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Provides created{Model} and updated{Model} HasMany relationships for tracked models.
 *
 * Replaces 16+ repetitive relationship methods with a compact trait that defines
 * each relationship using a shared helper, reducing boilerplate significantly.
 */
trait TracksCreatorAndUpdater
{
    /**
     * Get the media created by the user.
     */
    public function createdMedia(): HasMany
    {
        return $this->trackedRelationship(Media::class, 'created');
    }

    /**
     * Get the media updated by the user.
     */
    public function updatedMedia(): HasMany
    {
        return $this->trackedRelationship(Media::class, 'updated');
    }

    /**
     * Get the messages created by the user.
     */
    public function createdMessages(): HasMany
    {
        return $this->trackedRelationship(Message::class, 'created');
    }

    /**
     * Get the messages updated by the user.
     */
    public function updatedMessages(): HasMany
    {
        return $this->trackedRelationship(Message::class, 'updated');
    }

    /**
     * Get the posts created by the user.
     */
    public function createdPosts(): HasMany
    {
        return $this->trackedRelationship(Post::class, 'created');
    }

    /**
     * Get the posts updated by the user.
     */
    public function updatedPosts(): HasMany
    {
        return $this->trackedRelationship(Post::class, 'updated');
    }

    /**
     * Get the pages created by the user.
     */
    public function createdPages(): HasMany
    {
        return $this->trackedRelationship(Page::class, 'created');
    }

    /**
     * Get the pages updated by the user.
     */
    public function updatedPages(): HasMany
    {
        return $this->trackedRelationship(Page::class, 'updated');
    }

    /**
     * Get the projects created by the user.
     */
    public function createdProjects(): HasMany
    {
        return $this->trackedRelationship(Project::class, 'created');
    }

    /**
     * Get the projects updated by the user.
     */
    public function updatedProjects(): HasMany
    {
        return $this->trackedRelationship(Project::class, 'updated');
    }

    /**
     * Get the roles created by the user.
     */
    public function createdRoles(): HasMany
    {
        return $this->trackedRelationship(Role::class, 'created');
    }

    /**
     * Get the roles updated by the user.
     */
    public function updatedRoles(): HasMany
    {
        return $this->trackedRelationship(Role::class, 'updated');
    }

    /**
     * Get the tags created by the user.
     */
    public function createdTags(): HasMany
    {
        return $this->trackedRelationship(Tag::class, 'created');
    }

    /**
     * Get the tags updated by the user.
     */
    public function updatedTags(): HasMany
    {
        return $this->trackedRelationship(Tag::class, 'updated');
    }

    /**
     * Get the users created by the user.
     */
    public function createdUsers(): HasMany
    {
        return $this->trackedRelationship(User::class, 'created');
    }

    /**
     * Get the users updated by the user.
     */
    public function updatedUsers(): HasMany
    {
        return $this->trackedRelationship(User::class, 'updated');
    }

    /**
     * Build a tracked HasMany relationship with the appropriate foreign key and chaperone.
     */
    protected function trackedRelationship(string $modelClass, string $type): HasMany
    {
        $foreignKey = $type.'_by_user_id';
        $chaperoneName = $type === 'created' ? 'creator' : 'updater';

        return $this->hasMany($modelClass, $foreignKey, 'id')
            ->chaperone($chaperoneName);
    }
}
