<?php

namespace App\Filament\Schemas\Sections;

use App\Filament\Schemas\Components\CoverImageUpload;
use App\Filament\Schemas\Components\IntroTextarea;
use App\Filament\Schemas\Components\NameInput;
use App\Filament\Schemas\Components\QuoteTextarea;
use App\Filament\Schemas\Components\TitleInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;

class ContentSection
{
    public static function make(
        bool $hasTitle = true,
        int $titleMax = 128,
        bool $hasName = false,
        bool $hasIntro = true,
        bool $hasQuote = false,
        bool $hasContent = true,
        bool $contentRequired = false,
        bool $hasWebsite = false,
        bool $hasCover = true,
    ): Section {
        return Section::make(__('Content'))
            ->icon(Heroicon::OutlinedDocumentText)
            ->collapsible()
            ->persistCollapsed(function ($operation): bool {
                return $operation !== Operation::Create->value;
            })
            ->schema([
                TitleInput::make($titleMax)
                    ->hidden(! $hasTitle)
                    ->saved($hasTitle),
                NameInput::make()
                    ->hidden(! $hasName)
                    ->saved($hasName),
                IntroTextarea::make()
                    ->hidden(! $hasIntro)
                    ->saved($hasIntro),
                QuoteTextarea::make()
                    ->hidden(! $hasQuote)
                    ->saved($hasQuote),
                RichEditor::make('content')
                    ->hidden(! $hasContent)
                    ->saved($hasContent)
                    ->label(__('Content'))
                    ->required($contentRequired),
                TextInput::make('website_url')
                    ->label(__('Website').' '.__('External URL'))
                    ->hidden(! $hasWebsite)
                    ->saved($hasWebsite)
                    ->url()
                    ->inputMode('url')
                    ->maxLength(255)
                    ->trim()
                    ->placeholder('https://example.com')
                    ->belowContent(__('Optional link to an external website.')),
                TextInput::make('website_title')
                    ->label(__('Website').' '.__('Title'))
                    ->hidden(! $hasWebsite)
                    ->saved($hasWebsite)
                    ->maxLength(128)
                    ->trim()
                    ->belowContent(__('Name of the external website.')),
                $hasCover ? CoverImageUpload::make() : null,
            ]);
    }
}
