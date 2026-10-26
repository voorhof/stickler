<?php

namespace App\Traits;

use Carbon\CarbonInterface;
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
     * Scope the query to records whose publish date passed after the previous check.
     */
    public function scopeBecamePublishedBetween(Builder $query, CarbonInterface $from, CarbonInterface $until): Builder
    {
        return $query
            ->where('published_at', '>', $from)
            ->where('published_at', '<=', $until);
    }

    /**
     * Determine whether the record is published and its publish date has passed.
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null && ! $this->published_at->isFuture();
    }
}
