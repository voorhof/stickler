<?php

namespace App\Filament\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait FormatsJsonState
{
    /**
     * Keys whose (long) values should be truncated in the UI.
     */
    protected static array $truncatedKeys = ['base64svg'];

    /**
     * Format JSON state - main method using recursion.
     */
    protected static function formatJsonState(mixed $state): ?string
    {
        // Normalize to an associative array (Collection cast, JSON string, or array)
        if ($state instanceof Collection) {
            $array = $state->toArray();
        } elseif (is_string($state)) {
            $array = json_decode($state, true) ?? [];
        } else {
            $array = (array) $state;
        }

        // Keep only keys that actually contain data (removes null / [] / '' / false)
        $array = array_filter($array, fn ($value) => ! blank($value) || is_bool($value));

        if (empty($array)) {
            return null;
        }

        return mb_trim(static::formatArray(array: $array, depth: 0));
    }

    /**
     * Recursively render an array to Markdown, preserving associative keys.
     */
    protected static function formatArray(array $array, int $depth): string
    {
        $lines = [];
        $indent = str_repeat('  ', $depth);

        $isList = array_is_list($array);

        foreach ($array as $key => $value) {
            if ($isList) {
                if (is_array($value)) {
                    $lines[] = static::formatArray(array: $value, depth: $depth + 1);
                } else {
                    $lines[] = "$indent- ".static::formatScalar(value: $value, key: $key);
                }

                continue;
            }

            $heading = Str::ucfirst(str_replace('_', ' ', (string) $key));

            if (is_array($value)) {
                if ($depth === 0) {
                    $lines[] = "**$heading**";
                    $lines[] = '';
                    $lines[] = static::formatArray(array: $value, depth: $depth + 1);
                    $lines[] = '';
                } else {
                    $lines[] = "$indent- **$heading:**";
                    $lines[] = static::formatArray(array: $value, depth: $depth + 1);
                }
            } else {
                $lines[] = "$indent- **$heading:** ".static::formatScalar(value: $value, key: $key);
            }
        }

        return implode(PHP_EOL, $lines);
    }

    /**
     * Format scalar value booleans to text and truncate long strings defined in the $truncatedKeys array.
     */
    protected static function formatScalar(mixed $value, int|string|null $key = null): string
    {
        if (is_bool($value)) {
            return $value ? __('Yes') : __('No');
        }

        // Only truncate designated long keys (e.g. base64svg); show everything else in full.
        if (is_string($key) && in_array($key, static::$truncatedKeys, true)) {
            return Str::limit($value, 64);
        }

        return (string) $value;
    }
}
