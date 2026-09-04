<?php

namespace App\Filament\Traits;

use Filament\Actions\CreateAction;

trait HasCreateAction
{
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->size('sm'),
        ];
    }
}
