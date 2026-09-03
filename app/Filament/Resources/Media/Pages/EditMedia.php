<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Resources\Media\MediaResource;
use App\Filament\Traits\HasEditDefaults;
use Filament\Resources\Pages\EditRecord;

class EditMedia extends EditRecord
{
    use HasEditDefaults;

    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form')
                ->size('sm'),
            HardDeleteAction::make(),
        ];
    }
}
