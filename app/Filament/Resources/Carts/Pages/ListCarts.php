<?php

namespace App\Filament\Resources\Carts\Pages;

use App\Filament\Actions\CartCleanupAction;
use App\Filament\Resources\Carts\CartResource;
use App\Filament\Traits\CanResetTable;
use App\Filament\Traits\HasCreateAction;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCarts extends ListRecords
{
    use CanResetTable;
    use HasCreateAction;
    use HasResizableColumn;

    protected static string $resource = CartResource::class;

    protected function getTableQuery(): Builder
    {
        return static::getResource()::getEloquentQuery()->with(['cartItems'])->withCount(['cartItems']);
    }

    protected function getHeaderActions(): array
    {
        return [
            CartCleanupAction::make(),
            CreateAction::make()
                ->size('sm'),
        ];
    }
}
