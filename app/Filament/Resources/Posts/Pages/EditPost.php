<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\PublishAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Actions\UnpublishAction;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Traits\HasEditDefaults;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    use HasEditDefaults;

    protected static string $resource = PostResource::class;

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
