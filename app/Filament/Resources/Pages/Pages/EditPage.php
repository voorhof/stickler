<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\PublishAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Actions\UnpublishAction;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Traits\HasEditDefaults;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    use HasEditDefaults;

    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form')
                ->size('sm'),
            PublishAction::make(),
            UnpublishAction::make(),
            SoftDeleteAction::make(),
            RestoreDeletedAction::make(),
            HardDeleteAction::make(),
        ];
    }
}
