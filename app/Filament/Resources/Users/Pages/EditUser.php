<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Actions\VerifyEmailAction;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Traits\HasEditDefaults;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use HasEditDefaults;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form')
                ->size('sm'),
            VerifyEmailAction::make(),
            SoftDeleteAction::make(),
            RestoreDeletedAction::make(),
            HardDeleteAction::make(),
        ];
    }
}
