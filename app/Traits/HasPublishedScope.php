<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasPublishedScope
{
    /**
     * Scope the query to records that are published and whose publish date has passed.
     */
    public function scopeIsPublicPublished(Builder $query): Builder
    {
        return $query
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Determine whether the record is published and its publish date has passed.
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null && ! $this->published_at->isFuture();
    }
}
