<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Actions\HardDeleteAction;
use App\Filament\Actions\PublishAction;
use App\Filament\Actions\RestoreDeletedAction;
use App\Filament\Actions\SoftDeleteAction;
use App\Filament\Actions\UnpublishAction;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Traits\HasEditDefaults;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditProduct extends EditRecord
{
    use HasEditDefaults;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form')
                ->size('sm'),
            Action::make('viewPublicPage')
                ->label(__('View'))
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->outlined()
                ->size('sm')
                ->url(fn (): string => route('products.show', $this->getRecord()))
                ->visible(fn (): bool => $this->getRecord()?->published_at <= now() && $this->getRecord()?->published_at !== null),
            PublishAction::make(),
            UnpublishAction::make(),
            SoftDeleteAction::make(),
            RestoreDeletedAction::make(),
            HardDeleteAction::make(),
        ];
    }
}
