<?php

namespace App\Filament\Resources\Users\Schemas\Sections;

use App\Filament\Resources\Users\Schemas\Components\AvatarUpload;
use App\Filament\Resources\Users\Schemas\Components\EmailInput;
use App\Filament\Resources\Users\Schemas\Components\NameInput;
use App\Filament\Resources\Users\Schemas\Components\PasswordInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;

class DetailsSection
{
    public static function make(): Section
    {
        return Section::make(__('Details'))
            ->icon(Heroicon::OutlinedInformationCircle)
            ->collapsible()
            ->persistCollapsed(function ($operation): bool {
                return $operation !== Operation::Create->value;
            })
            ->schema([
                Grid::make()
                    ->schema([
                        NameInput::make(),
                        EmailInput::make(),
                        PasswordInput::make(),
                        AvatarUpload::make()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
