<?php

namespace App\Filament\Resources\ProductVariantOptionTypes\Pages;

use App\Filament\Resources\ProductVariantOptionTypes\ProductVariantOptionTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductVariantOptionType extends EditRecord
{
    protected static string $resource = ProductVariantOptionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
