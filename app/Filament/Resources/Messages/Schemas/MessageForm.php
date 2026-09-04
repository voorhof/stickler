<?php

namespace App\Filament\Resources\Messages\Schemas;

use App\Filament\Resources\Messages\Schemas\Sections\MessageSection;
use App\Filament\Resources\Messages\Schemas\Sections\ReplySection;
use App\Filament\Schemas\Sections\ActivityHistorySection;
use App\Filament\Schemas\Sections\DatabaseInfoSection;
use App\Filament\Schemas\Sections\SettingsSection;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class MessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->columnSpanFull()
                    ->schema([
                        MessageSection::make(),
                        ReplySection::make(),
                        SettingsSection::make(
                            hasPublished: false,
                            hasArchive: true,
                        ),
                        DatabaseInfoSection::make(),
                        ActivityHistorySection::make(),
                    ]),
            ]);
    }
}
