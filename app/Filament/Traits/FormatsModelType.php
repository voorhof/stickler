<?php

namespace App\Filament\Traits;

trait FormatsModelType
{
    /**
     * Format model type
     */
    protected static function formatModelType(?string $type): string
    {
        if (blank($type)) {
            return '-';
        }

        return __(str($type)
            ->afterLast('\\')
            ->snake()
            ->replace('_', ' ')
            ->replace('settings property', 'settings')
            ->title()
            ->toString());
    }
}
