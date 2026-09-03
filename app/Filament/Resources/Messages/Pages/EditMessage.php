<?php

namespace App\Filament\Resources\Messages\Pages;

use App\Filament\Actions\ArchiveAction;
use App\Filament\Actions\DearchiveAction;
use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Resources\Messages\MessageResource;
use App\Filament\Traits\HasEditDefaults;
use Filament\Resources\Pages\EditRecord;

class EditMessage extends EditRecord
{
    use HasEditDefaults;

    protected static string $resource = MessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form')
                ->size('sm'),
            ArchiveAction::make(),
            DearchiveAction::make(),
            SoftDeleteAction::make(),
            RestoreDeletedAction::make(),
            HardDeleteAction::make(),
        ];
    }
}
