<?php

namespace App\Actions\Sluggable;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\Actions\GenerateSlugAction as SpatieGenerateSlugAction;
use Spatie\Sluggable\SlugOptions;

class GenerateSlugAction extends SpatieGenerateSlugAction
{
    /**
     * Handle model updating event.
     */
    public function onUpdate(Model $model, SlugOptions $options): void
    {
        if (! $options->generateSlugsOnUpdate && ! $this->hasCustomSlugBeenUsed($model, $options) && ! $this->isExcludedSlug((string) $model->{$options->slugField})) {
            return;
        }

        if ($this->shouldSkipGeneration($model, $options)) {
            return;
        }

        $this->addSlug($model, $options);
    }

    /**
     * Check if slug generation should be skipped.
     */
    protected function shouldSkipGeneration(Model $model, SlugOptions $options): bool
    {
        if ($this->shouldSkipBasedOnSkipWhen($options)) {
            return true;
        }

        if ($this->isExcludedSlug((string) $model->{$options->slugField})) {
            return false;
        }

        if (! $options->preventOverwrite) {
            return false;
        }

        return $model->{$options->slugField} !== null;
    }

    /**
     * Make the slug unique while also respecting globally excluded/reserved slugs.
     */
    public function makeUnique(string $slug, Model $model, SlugOptions $options): string
    {
        if ($this->cannotUseSlugVariantQuery($slug, $options)) {
            return $this->makeUniqueIterative($slug, $model, $options);
        }

        $existing = $this->fetchExistingSlugVariants($slug, $model, $options);
        $originalIsTaken = in_array($slug, $existing, true) || $this->isExcludedSlug($slug);

        if (! $options->useSuffixOnFirstOccurrence) {
            if (! $originalIsTaken) {
                return $slug;
            }
        }

        $prefix = $slug.$options->slugSeparator;
        $usedSuffixes = $this->extractNumericSuffixes($existing, $prefix);

        $candidate = $options->startSlugSuffixFrom;
        while (isset($usedSuffixes[$candidate]) || $this->isExcludedSlug($prefix.$candidate)) {
            $candidate++;
        }

        return $prefix.$candidate;
    }

    /**
     * Iteratively generate a unique slug, ensuring candidate slugs are not in the excluded list.
     */
    protected function makeUniqueIterative(string $slug, Model $model, SlugOptions $options): string
    {
        $originalSlug = $slug;
        $iteration = 0;

        while (
            $slug === ''
            || $this->isExcludedSlug($slug)
            || $this->otherRecordExistsWithSlug($slug, $model, $options)
            || ($options->useSuffixOnFirstOccurrence && $iteration === 0)
        ) {
            $suffix = $this->generateSuffix($originalSlug, $iteration++, $options);
            $slug = $originalSlug.$options->slugSeparator.$suffix;
        }

        return $slug;
    }

    /**
     * Determine whether the given slug is in the excluded/reserved slugs list.
     */
    public function isExcludedSlug(string $slug): bool
    {
        $excluded = config('sluggable.excluded_from_slug', []);

        if (! is_array($excluded) || empty($excluded)) {
            return false;
        }

        return in_array(mb_strtolower($slug), array_map('strtolower', $excluded), true);
    }

    /**
     * Generate a regular expression pattern that matches valid slug strings but excludes reserved slugs.
     */
    public static function getExcludedSlugsRegexPattern(): string
    {
        $excluded = config('sluggable.excluded_from_slug', []);

        if (! is_array($excluded) || empty($excluded)) {
            return '[a-z0-9\-]+';
        }

        $quoted = array_map(static fn (string $item): string => preg_quote(mb_strtolower(mb_trim($item)), '#'), $excluded);
        $reservedPattern = implode('|', array_filter($quoted));

        return '^(?!('.$reservedPattern.')$)[a-z0-9\-]+$';
    }
}
