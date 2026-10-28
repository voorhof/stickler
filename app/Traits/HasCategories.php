<?php

namespace App\Traits;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Provides categories relationships.
 *
 * @property Collection<Category> $categories
 */
trait HasCategories
{
    /**
     * Get all the categories for the model.
     */
    public function categories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable')
            ->where($this->categoryScopeKey(), true)
            ->orderBy('title')
            ->withTimestamps();
    }

    /**
     * Get the category scope key for the model.
     */
    protected function categoryScopeKey(): string
    {
        $model = mb_strtolower(class_basename(static::class));

        return "for_{$model}s";
    }
}
