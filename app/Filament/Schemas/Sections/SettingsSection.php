<?php

namespace App\Filament\Schemas\Sections;

use App\Filament\Schemas\Components\ArchivedDatePicker;
use App\Filament\Schemas\Components\ArchiveToggle;
use App\Filament\Schemas\Components\OrderColumnInput;
use App\Filament\Schemas\Components\PublishedDatePicker;
use App\Filament\Schemas\Components\SlugInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;

class SettingsSection
{
    public static function make(
        bool $hasPublished = true,
        int $publishColumnOrder = 0,
        bool $hasOrder = true,
        bool $hasSlug = true,
        string $slugName = 'slug',
        bool $hasArchive = false,
        string $archiveLabel = '',
    ): Section {
        return Section::make(__('Settings'))
            ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
            ->collapsible()
            ->persistCollapsed(function ($operation): bool {
                return $operation !== Operation::Create->value;
            })
            ->schema([
                Grid::make()->schema([
                    PublishedDatePicker::make()
                        ->hidden(! $hasPublished)
                        ->columnOrder($publishColumnOrder),
                    OrderColumnInput::make()
                        ->hidden(! $hasOrder),
                    SlugInput::make(name: $slugName)
                        ->hidden((fn ($operation): bool => $operation === Operation::Create->value || ! $hasSlug)),
                    ArchiveToggle::make($archiveLabel)
                        ->hidden((fn ($operation): bool => $operation === Operation::Create->value || ! $hasArchive)),
                    ArchivedDatePicker::make()
                        ->hidden((fn ($operation): bool => $operation === Operation::Create->value || ! $hasArchive)),
                ]),
            ]);
    }
}
