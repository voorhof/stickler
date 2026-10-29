<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Traits\HasEditDefaults;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    use HasEditDefaults;

    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form')
                ->size('sm'),
            SoftDeleteAction::make(),
            RestoreDeletedAction::make(),
            HardDeleteAction::make(),
        ];
    }
}
