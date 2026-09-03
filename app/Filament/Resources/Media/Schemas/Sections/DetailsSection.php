<?php

namespace App\Filament\Resources\Media\Schemas\Sections;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Operation;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class DetailsSection
{
    public static function make(): Section
    {
        return Section::make(__('Details'))
            ->icon(Heroicon::OutlinedDocumentText)
            ->collapsible()
            ->persistCollapsed(function ($operation): bool {
                return $operation !== Operation::Create->value;
            })
            ->headerActions([
                Action::make('viewOriginalImage')
                    ->visible(fn ($record) => str_starts_with($record->mime_type, 'image/'))
                    ->label(__('Original Image'))
                    ->icon(Heroicon::OutlinedEye)
                    ->modalHeading(__('Original Image'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('Close'))
                    ->modalWidth(Width::SevenExtraLarge)
                    ->modalContent(fn ($record) => new HtmlString('<img src="'.e($record->getUrl()).'" />')),
                Action::make('viewOriginalVideo')
                    ->visible(fn ($record) => str_starts_with($record->mime_type, 'video/'))
                    ->label(__('Original Video'))
                    ->icon(Heroicon::OutlinedEye)
                    ->modalHeading(__('Original Video'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('Close'))
                    ->modalWidth(Width::SevenExtraLarge)
                    ->modalContent(fn ($record) => new HtmlString('<video controls preload="metadata" src="'.e($record->getUrl()).'" style="max-height: 75vh;" />')),
                Action::make('downloadOriginalFile')
                    ->visible(fn ($record) => str_starts_with($record->mime_type, 'application/'))
                    ->label(__('Download file'))
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->url(fn ($record) => route('filament.admin.resources.media.download', ['record' => $record]), shouldOpenInNewTab: true),
            ])
            ->schema([
                Grid::make()->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(64)
                        ->trim(),
                    TextInput::make('collection_name')
                        ->label(__('Collection'))
                        ->disabled()
                        ->required()
                        ->maxLength(32),
                    ImageEntry::make('preview_url')
                        ->label(__('Preview'))
                        ->imageSize(200)
                        ->columnSpanFull(),
                    TextEntry::make('model.title')
                        ->label(fn ($record) => $record->model_type ? __('For :model', ['model' => __($record->mediaOwnerTitle())]) : null)
                        ->color('primary')
                        ->url(fn ($record) => $record->model_type ? route($record->mediaOwnerRoute(), ['record' => $record->model]) : null),
                ]),
            ]);
    }
}
