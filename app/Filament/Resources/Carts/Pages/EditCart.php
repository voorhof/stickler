<?php

namespace App\Filament\Resources\Carts\Pages;

use App\Filament\Resources\Carts\CartResource;
use App\Filament\Traits\HasEditDefaults;
use Filament\Resources\Pages\EditRecord;

class EditCart extends EditRecord
{
    use HasEditDefaults;

    protected static string $resource = CartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form')
                ->size('sm'),
        ];
    }
}
