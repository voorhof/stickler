<?php

namespace App\Filament\Resources\ProductVariantOptionTypes\Pages;

use App\Filament\Resources\ProductVariantOptionTypes\ProductVariantOptionTypeResource;
use App\Filament\Traits\HasEditDefaults;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductVariantOptionType extends EditRecord
{
    use HasEditDefaults;

    protected static string $resource = ProductVariantOptionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
